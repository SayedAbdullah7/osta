<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewStatistic extends Model
{
    use HasFactory;
    protected $fillable = [
        'provider_id',
        'total_reviews',
        'average_rating',
    ];
    public function reviewable()
    {
        return $this->morphTo();
    }

    /**
     * Get the count of completed orders with status 'done' for the reviewable model.
     */
    public function getCompletedOrdersAttribute()
    {
        return $this->reviewable
            ->orders()
            ->where('status', OrderStatusEnum::DONE)
            ->count();
    }
}
