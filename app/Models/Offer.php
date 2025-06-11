<?php

namespace App\Models;

use App\Enums\OfferStatusEnum;
use App\Enums\OrderCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Scopes\CustomSoftDeletingScope;
use App\Services\ProviderOfferService;
use App\Traits\NewSoftDeletes;
use App\Traits\SoftDeletesWithFutureDeletion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory;
//    use SoftDeletes;
//    use SoftDeletesWithFutureDeletion;
//    use SoftDeletes;

//    use NewSoftDeletes;

//    protected static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope(new CustomSoftDeletingScope);
//    }
    protected $guarded = [];
//    public const LIFETIME = 2; // in minutes
    protected static function boot()
    {
        parent::boot();

        static::creating(static function ($model) {
//            if ($model->deleted_at === null) {
//                $model->deleted_at = Carbon::now()->addMinutes(ProviderOfferService::MAX_OFFER_TIME);
//            }
        });
//        static::addGlobalScope('hasOrders', function (Builder $builder) {
//            $builder->has('order');
//        });
    }

//    protected static function booted()
//    {
//        // Apply the global scope within the booted method
//        static::addGlobalScope('offersWithVisibleOrders', static function ($builder) {
//            if (Order::$applyRecentScope) {
//                // Apply the condition based on the Order model's static properties
//                $builder->whereHas('order', function ($query) {
//                    $query->where('created_at', '>=', Carbon::now()->subHours(Order::$recentDurationHours));
//                });
//            }
//        });
//    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function scopePending($query): void
    {
        $query->where('status', OfferStatusEnum::PENDING);
    }

    /**
     * Scope a query to filter offers by order ID.
     *
     * @param Builder $query
     * @param int $orderId
     * @return Builder
     */
    public function scopeForOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope a query to filter offers by provider ID.
     *
     * @param Builder $query
     * @param int $providerId
     * @return Builder
     */
    public function scopeForProvider(Builder $query, int $providerId): Builder
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope a query to filter offers by multiple statuses.
     *
     * @param Builder $query
     * @param array $statuses
     * @return Builder
     */
    public function scopeWhereInStatuses(Builder $query, array $statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * Scope a query to filter offers by excluding certain statuses.
     *
     * @param Builder $query
     * @param array $statuses
     * @return Builder
     */
    public function scopeWhereNotInStatuses(Builder $query, array $statuses): Builder
    {
        return $query->whereNotIn('status', $statuses);
    }


    /**
     * Scope a query to filter orders with a status of REJECTED.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::REJECTED);
    }

    /**
     * Scope a query to filter orders where is_second is 1.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsSecond($query): Builder
    {
        return $query->where('is_second', 1);
    }

    /**
     * Scope a query to filter orders where is_second is 1.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsFirst($query): Builder
    {
        return $query->where('is_second', 0);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', OfferStatusEnum::ACCEPTED);
    }


}
