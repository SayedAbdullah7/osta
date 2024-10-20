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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => OrderStatusEnum::class,
    ];

    // Static property to control the duration (default 24 hours)
    public static $recentDurationHours = 24*15;

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
//        if ($this->subServices->count() > 0) {
//            $max = 0;
//            foreach ($this->subServices as $subService) {
//                $max += ($subService->pivot->quantity * $subService->max_price);
//            }
//            return $max;
//        }
//        return INF;
    }

    public function conversation(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(Conversation::class, 'model');
    }

    public function isPreview(): bool
    {
        return $this->price == 0 || $this->price == null;
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
    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function calculateTotal($orderPrice): float
    {
        $warrantyCost = $this->warranty ? $this->warranty->calculateCost($orderPrice) : 0;
        return $orderPrice + $warrantyCost;
    }

}
