<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiscountCode extends Model
{
//    protected $fillable = [
//        'code', 'type', 'discount_value', 'valid_from', 'valid_to', 'is_active', 'used_at', 'used_by'
//    ];
    protected $fillable = [
        'code',
        'discount_amount',
        'discount_percentage',
        'is_active',
        'expires_at',
        'used_at',
        'used_by',
    ];

    protected $dates = [
        'expires_at',
        'used_at',
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically generate a unique discount code if not provided
        static::creating(function ($discountCode) {
            if (empty($discountCode->code)) {
                $discountCode->code = self::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique discount code.
     *
     * @return string
     */
    protected static function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Scope a query to only include active discount codes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            });
    }

    /**
     * Scope a query to only include valid discount codes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid(Builder $query)
    {
        return $query->active()
            ->whereNull('used_at');
    }

    /**
     * Get the user who used the discount code.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    /**
     * Check if the discount code is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Apply the discount code to an order.
     *
     * @param  Order  $order
     * @return float  The discount amount applied
     */
    public function applyToOrder(Order $order): float
    {
        $discountAmount = $this->calculateDiscountAmount($order->price);

        // Mark the discount code as used
        $this->is_active = false;
        $this->used_at = Carbon::now();
        $this->used_by = $order->user_id; // Assuming `user_id` is the field in the `orders` table that links to the user
        $this->save();

        return $discountAmount;
    }

    /**
     * Calculate the discount amount based on the discount code.
     *
     * @param  float  $orderAmount
     * @return float
     */
    public function calculateDiscountAmount(float $orderAmount): float
    {
        if ($this->discount_percentage) {
            return ($this->discount_percentage / 100) * $orderAmount;
        }

        return $this->discount_amount;
    }
}
