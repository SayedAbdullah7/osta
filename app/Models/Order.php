<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;

/**
 * Order Model
 *
 * IMPORTANT FOR AI - Preview Orders Business Logic:
 * ==================================================
 *
 * CRITICAL RULES:
 * ---------------
 * 1. Preview Orders (unknown_problem = 1):
 *    - offer_price is ALWAYS 0
 *    - Has preview_cost (المعيناة) from Setting::getPreviewCost()
 *    - Price = offer_price(0) + additional_cost + purchases + preview_cost
 *
 * 2. Offer Orders (unknown_problem = 0):
 *    - Has offer_price from accepted offer
 *    - NO preview_cost
 *    - Price = offer_price + additional_cost + purchases
 *
 * 3. CRITICAL: When accepting offer for preview order:
 *    - If offer->price = 0: Set order->price = Setting::getPreviewCost() directly
 *    - If offer->price > 0: Use calculatePrice() method
 *    - See UserOfferService::updateOrderWithAcceptedOffer()
 *
 * 4. CRITICAL: When adding additional_cost to preview order:
 *    - Automatically converts preview to offer order
 *    - Removes preview_cost OrderDetail
 *    - Keeps offer->price = 0 (does NOT set it to additional_cost value)
 *    - additional_cost is stored separately in OrderDetail
 *
 * 5. CRITICAL: When adding purchases to preview order:
 *    - Does NOT convert to offer order
 *    - Keeps preview_cost
 *
 * IMPORTANT METHODS:
 * ------------------
 * - convertToPreview(): Converts offer → preview (removes additional_cost, adds preview_cost)
 * - convertToOffer($price): Converts preview → offer (removes preview_cost, sets offer price)
 * - addAdditionalCost($value): Adds cost, converts preview to offer if needed
 * - addPurchases($value): Adds purchases, keeps preview status
 * - calculatePrice(): Calculates total = offer_price + additional_cost + purchases + preview_cost
 *
 * ORDER DETAILS:
 * --------------
 * - ACTION_CONVERT_TO_PREVIEW: Stores preview_cost (المعيناة) value
 * - ACTION_ADDITIONAL_COST: Stores additional cost value
 * - PURCHASES: Stores purchases value
 */
class Order extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
//    protected static function booted()
//    {
//        $timeInHours = 24*7;
////        static::addGlobalScope('recent', function (Builder $builder) {
////            $builder->where('created_at', '>=', now()->subHours(48));
////        });
//    }
protected $guarded = ['id'];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    // Static property to control the duration (default 24 hours)
    public static $recentDurationHours = 24*30*12;

    // Static property to control whether the global scope is applied
    public static $applyRecentScope = true;

    protected static function booted()
    {
        // Apply the global scope within the booted method
        static::addGlobalScope('recentOrders', function ($builder) {
            if (static::$applyRecentScope) {
                // Use the static property for dynamic hours condition
                $builder->where('created_at', '>=', Carbon::now()->subHours(static::$recentDurationHours));
            }
        });
    }

    // Optional method to disable the global scope
    public static function withoutRecentScope()
    {
        static::$applyRecentScope = false;
        return new static;
    }


    public function subServices()
    {
        return $this->belongsToMany(SubService::class)->withPivot('quantity','space_id');
    }

    public function orderSubServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderSubService::class);
    }

    public function spaces()
    {
        return $this->belongsToMany(Space::class, 'order_sub_service')
            ->withPivot('sub_service_id', 'quantity','space_id')
            ->withTimestamps();
    }
    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function location(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function offers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function cancellationProviders(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'order_cancellations');
    }

    public function scopePending($query): void
    {
        $query->where('status', OrderStatusEnum::PENDING);
    }

    public function scopeAvailableToAccept($query): void
    {
        $query->where('status', OrderStatusEnum::PENDING)->where('provider_id', null);
    }
    public function scopeAvailableForConversation($query): void
    {
        $query->where('status', OrderStatusEnum::ACCEPTED);
    }

    /**
     * Calculate the Haversine distance between two geographical coordinates.
     *
     * @param float $providerLatitude Latitude of the provider
     * @param float $providerLongitude Longitude of the provider
     * @return Expression
     */
    public static function calculateHaversineDistance(float $providerLatitude, float $providerLongitude): Expression
    {
        return DB::raw('ROUND((6371 * acos(cos(radians(' . $providerLatitude . ')) * cos(radians(location_latitude)) * cos(radians(location_longitude) - radians(' . $providerLongitude . ')) + sin(radians(' . $providerLatitude . ')) * sin(radians(location_latitude)))), 2) AS distance');
    }

    public function isAvailableToAccept(): bool
    {
        return $this->status === OrderStatusEnum::PENDING;
    }

    public function isAvailableToCancel(): bool
    {
        return (!$this->provider_id && $this->status === OrderStatusEnum::PENDING);
    }

    public function isAvailableToSendOffer(): bool
    {
        return $this->status === OrderStatusEnum::PENDING;
    }

    public function isDone()
    {
        return $this->status === OrderStatusEnum::DONE;
    }
    public function maxAllowedOfferPrice(): float|int
    {
        // TODO: neeed more optimization

        if ($this->subServices->isEmpty()) {
            return 0;
        }

        $max = 0;

        foreach ($this->subServices as $subService) {
            $quantity = $subService->pivot->quantity;
            $spaceMaxPrice = 0;
            $maxForSubService = 0;
            Log::channel('test')->info('subservice',[$quantity]);
            Log::channel('test')->info('pivot',[$subService->pivot]);
            // Check if space_id is set in the pivot data
            if ($subService->pivot->space_id) {
                // Find the max price for the space if it exists
                $space = $subService->spaces
                    ->where('id', $subService->pivot->space_id)
                    ->first();

                // Use the max_price from the space's pivot table if available
                $spaceMaxPrice = $space?->pivot->max_price ?? 0;
                Log::channel('test')->info('space',['subService'=>$subService, 'quantity'=>$quantity,'spaceMaxPrice'=>$spaceMaxPrice]);
                $maxForSubService = ($quantity * $spaceMaxPrice);
                $max += ($maxForSubService);

            }else{
                $maxForSubService = ($quantity * $subService->max_price);
                $max += ($maxForSubService);
//                $max += ($quantity * $subService->max_price);
            }
            $subService->pivot->max_price = $maxForSubService;
            $subService->pivot->save();
        }

        return $max;
    }

    public function conversation(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Conversation::class, 'model');
    }

    /**
     * Check if order is a preview order (unknown_problem = 1).
     *
     * @return bool True if unknown_problem = 1
     */
    public function isPreview(): bool
    {
        return $this->unknown_problem == 1;
    }

    /**
     * Scope to filter preview orders (unknown_problem = 1).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePreview(Builder $query): Builder
    {
        return $query->where('unknown_problem', 1);
    }

    /**
     * Scope to filter offer orders (unknown_problem = 0).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOffer(Builder $query): Builder
    {
        return $query->where('unknown_problem', 0);
    }


    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function scopeWithRelationsInProvider($query):mixed
    {
        return $query->with('location', 'orderSubServices', 'service','user');
    }

    /**
     * Scope a query to filter orders by provider ID.
     *
     * @param Builder $query
     * @param mixed $providerId
     * @return Builder
     */
    public function scopeForProvider($query, $providerId): Builder
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Get the warranty associated with the order (optional).
     */
    public function warranty(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function calculateTotal($orderPrice): float
    {
        $warrantyCost = $this->warranty ? $this->warranty->calculateCost($orderPrice) : 0;
        return $orderPrice + $warrantyCost;
    }

    /**
     * Calculate warranty cost based on offer_price + additional_cost only.
     * Does NOT include preview_cost or purchases.
     *
     * @return float Warranty cost (0 if no warranty)
     */
    public function getWarrantyCost(): float
    {
        if (!$this->warranty) {
            return 0.0;
        }

        // Warranty is calculated on: offer_price + additional_cost
        // NOT on: preview_cost or purchases
        $warrantyBase = $this->getOfferPrice() + $this->getAdditionalCost();

        return $this->warranty->calculateCost($warrantyBase);
    }

    /**
     * Get all reviews for the order (one from user and one from provider).
     */
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the review made by the user for this order.
     */
    public function userReview(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class)
            ->where('reviewable_type', User::class)
            ->where('reviewed_type', Provider::class);
    }

    /**
     * Get the review made by the provider for this order.
     */
    public function providerReview(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class)
            ->where('reviewable_type', Provider::class)
            ->where('reviewed_type', User::class);
    }
    public function orderDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
    /**
     * Get the price from the accepted offer (if any).
     */
    public function getOfferPrice(): float
    {
        $offer = $this->offers()->accepted()->first();
        return $offer ?(float) $offer->price : 0.0;
    }

    /**
     * Get the additional cost (if any).
     */
    public function getAdditionalCost(): float
    {
        $additionalCost = $this->orderDetails()->additionalCost()->first();
        return $additionalCost ? $additionalCost->value : 0.0;
    }

    /**
     * Get the total value of the purchases (if any).
     */
    public function getPurchasesValue(): float
    {
        $purchases = $this->orderDetails()->purchases()->first();
        return $purchases ? $purchases->value : 0.0;
    }

    /**
     * Get the preview cost (المعيناة) value when unknown_problem = 1.
     * See class-level documentation for preview orders business logic.
     *
     * @return float The preview cost value (0 if unknown_problem != 1)
     */
    public function getPreviewCost(): float
    {
        if ($this->unknown_problem == 1) {
            // Always query from database to ensure we get the latest value
            $previewDetail = OrderDetail::where('order_id', $this->id)
                ->where('name', Message::ACTION_CONVERT_TO_PREVIEW)
                ->first();
            if ($previewDetail) {
                return (float) $previewDetail->value;
            }
            // If OrderDetail doesn't exist, return the value from Setting
            return \App\Models\Setting::getPreviewCost();
        }
        return 0.0;
    }
    /**
     * Calculate the total order price.
     * Sums: offer_price + additional_cost + purchases + preview_cost (المعيناة)
     * See class-level documentation for preview orders business logic.
     *
     * @return void
     */
    public function calculatePrice(): void
    {
        $price = 0;

        // Add price from the accepted offer (0 for preview orders)
        $price += $this->getOfferPrice();

        // Add any additional costs
        $price += $this->getAdditionalCost();

        // Add value from purchases
        $price += $this->getPurchasesValue();

        // Add preview cost (المعيناة) when unknown_problem = 1
        // This ensures preview orders have a minimum cost even when offer_price = 0
        $price += $this->getPreviewCost();

        // Set the calculated price on the order
        $this->price = $price;
    }

    public function statusText()
    {
        return $this->status?->value;
    }


    /**
     * Convert offer order to preview order.
     * Removes additional_cost OrderDetail, sets unknown_problem = 1, creates preview_cost OrderDetail.
     *
     * @return self
     */
    public function convertToPreview(): self
    {
        // Remove additional_cost OrderDetail when converting to preview
        $this->orderDetails()->additionalCost()->delete();

        // Set order as preview
        $this->price = null;
        $this->unknown_problem = 1;
        $this->save();

        // Set offer price to null
        $offer = $this->offers()->accepted()->first();
        if ($offer) {
            $offer->price = null;
            $offer->save();
        }

        // Create preview_cost OrderDetail
        $this->orderDetails()->updateOrCreate(
            ['name' => Message::ACTION_CONVERT_TO_PREVIEW],
            ['value' => \App\Models\Setting::getPreviewCost()]
        );

        // Recalculate price
        $this->refresh();
        $this->calculatePrice();
        $this->save();

        return $this;
    }

    /**
     * Convert preview order to offer order with specified price.
     * Removes preview_cost OrderDetail, sets unknown_problem = 0, sets offer price.
     *
     * @param float|int $price The offer price
     * @return self
     */
    public function convertToOffer($price): self
    {
        // Remove preview_cost OrderDetail when converting to offer
        $this->orderDetails()->previewCost()->delete();

        // Set order as offer
        $this->unknown_problem = 0;
        $this->save();

        // Update or create offer with the new price
        $offer = $this->offers()->accepted()->first();
        if (!$offer) {
            $offer = new \App\Models\Offer();
            $offer->order_id = $this->id;
            $offer->provider_id = $this->provider_id;
            $offer->status = \App\Enums\OfferStatusEnum::ACCEPTED;
            $offer->deleted_at = null;
        }
        $offer->price = $price;
        $offer->save();

        // Recalculate price: offer_price + additional_cost + purchases (no preview_cost)
        $this->calculatePrice();
        $this->save();

        return $this;
    }

    /**
     * Add additional cost to order.
     * If order is preview, converts it to offer order automatically.
     *
     * CRITICAL: When converting preview to offer, offer->price stays 0.
     * The additional_cost is stored separately and NOT set as offer->price.
     *
     * CRITICAL: If value is 0 or null, removes additional_cost and converts back to preview
     * if offer->price = 0 (meaning it was originally a preview order).
     *
     * @param float|int|null $value The additional cost value (0 or null removes it)
     * @param string $description Optional description
     * @return self
     */
    public function addAdditionalCost($value, $description = ''): self
    {
        // If value is 0 or null, remove additional_cost and check if should convert back to preview
        if (empty($value) || $value == 0) {
            $this->orderDetails()->additionalCost()->delete();

            // Check if should convert back to preview (offer->price = 0 means it was originally preview)
            $offer = $this->offers()->accepted()->first();
            if ($offer && ($offer->price === null || $offer->price == 0)) {
                // Convert back to preview order
                $this->unknown_problem = 1;
                $this->save();

                // Set offer->price to null
                $offer->price = null;
                $offer->save();

                // Create preview_cost OrderDetail
                $this->orderDetails()->updateOrCreate(
                    ['name' => Message::ACTION_CONVERT_TO_PREVIEW],
                    ['value' => \App\Models\Setting::getPreviewCost(), 'description' => $description]
                );

                $this->refresh();
            }

            // Recalculate price
            $this->calculatePrice();
            $this->save();

            return $this;
        }

        // Create or update additional cost OrderDetail
        $this->orderDetails()->updateOrCreate(
            ['name' => Message::ACTION_ADDITIONAL_COST],
            ['value' => $value, 'description' => $description]
        );

        // If order is preview, convert it to offer order
        if ($this->unknown_problem == 1) {
            // CRITICAL: Remove preview_cost OrderDetail when converting to offer
            // This ensures preview_cost is not included in price calculation
            $this->orderDetails()->previewCost()->delete();

            // Convert to offer order
            $this->unknown_problem = 0;
            $this->save(); // Save to ensure unknown_problem is updated before calculatePrice()

            // CRITICAL: Keep offer->price = 0 (do NOT set it to additional_cost value)
            // The additional_cost is stored separately in OrderDetail
            $offer = $this->offers()->accepted()->first();
            if ($offer) {
                // Keep offer->price = 0 (or null) - do NOT change it
                // offer->price should remain 0 for orders converted from preview
                if ($offer->price === null) {
                    $offer->price = 0;
                    $offer->save();
                }
            }

            // Refresh to ensure orderDetails relationship is updated
            $this->refresh();
        }

        // Recalculate price: offer_price(0) + additional_cost + purchases (no preview_cost)
        // getPreviewCost() will return 0 because unknown_problem = 0
        $this->calculatePrice();
        $this->save();

        return $this;
    }

    /**
     * Add purchases to order.
     * Does NOT convert preview order to offer order.
     *
     * @param float|int $value The purchases value
     * @param string $description Optional description
     * @return OrderDetail The created/updated OrderDetail instance
     */
    public function addPurchases($value, $description = ''): OrderDetail
    {
        // Create or update purchases OrderDetail
        $orderDetail = $this->orderDetails()->updateOrCreate(
            ['name' => Message::PURCHASES],
            ['value' => $value, 'description' => $description]
        );

        // Recalculate price to include purchases
        $this->calculatePrice();
        $this->save();

        return $orderDetail;
    }

    /**
     * Remove additional cost from order.
     * If offer->price = 0, converts back to preview order.
     *
     * @return self
     */
    public function removeAdditionalCost(): self
    {
        $this->orderDetails()->additionalCost()->delete();

        // Check if should convert back to preview (offer->price = 0 means it was originally preview)
        $offer = $this->offers()->accepted()->first();
        if ($offer && ($offer->price === null || $offer->price == 0)) {
            // Convert back to preview order
            $this->unknown_problem = 1;
            $this->save();

            // Set offer->price to null
            $offer->price = null;
            $offer->save();

            // Create preview_cost OrderDetail if it doesn't exist
            if (!$this->orderDetails()->previewCost()->exists()) {
                $this->orderDetails()->updateOrCreate(
                    ['name' => Message::ACTION_CONVERT_TO_PREVIEW],
                    ['value' => \App\Models\Setting::getPreviewCost()]
                );
            }

            $this->refresh();
        }

        $this->calculatePrice();
        $this->save();
        return $this;
    }

    /**
     * Remove purchases from order.
     *
     * @return self
     */
    public function removePurchases(): self
    {
        $this->orderDetails()->purchases()->delete();
        $this->calculatePrice();
        $this->save();
        return $this;
    }

    /**
     * Remove preview cost from order.
     *
     * @return self
     */
    public function removePreviewCost(): self
    {
        $this->orderDetails()->previewCost()->delete();
        $this->calculatePrice();
        $this->save();
        return $this;
    }

}
