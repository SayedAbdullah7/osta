<?php

namespace App\Models;

use App\Enums\OfferStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $guarded = [];

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
        return $query->where('status', OfferStatusEnum::REJECTED);
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
