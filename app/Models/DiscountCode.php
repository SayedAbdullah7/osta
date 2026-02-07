<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DiscountCode extends Model
{
//    protected $fillable = [
//        'code', 'type', 'discount_value', 'valid_from', 'valid_to', 'is_active', 'used_at', 'used_by'
//    ];
    /**
     * IMPORTANT FOR AI - Discount Code Fields:
     * =========================================
     *
     * BEARER TYPES:
     * - 'admin': Admin bears the entire discount (provider earnings unaffected)
     * - 'both': Both admin and provider share the discount proportionally
     *
     * APPLY_TO_WARRANTY:
     * - true: Discount applies to warranty cost too
     * - false: Discount does NOT apply to warranty (default)
     */
    protected $fillable = [
        'code',
        'discount_amount',
        'discount_percentage',
        'is_active',
        'expires_at',
        'used_at',
        'used_by',
        'bearer',           // 'admin' or 'both'
        'apply_to_warranty', // true or false
    ];

    /**
     * Check if admin bears the entire discount.
     */
    public function isAdminOnlyBearer(): bool
    {
        return $this->bearer === 'admin';
    }

    /**
     * Check if discount applies to warranty.
     */
    public function appliesToWarranty(): bool
    {
        return (bool) $this->apply_to_warranty;
    }

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
     * Uses 'type' and 'value' columns from database:
     * - type = 'fixed': Returns min(value, orderAmount)
     * - type = 'percentage': Returns (value / 100) * orderAmount
     *
     * @param  float  $orderAmount
     * @return float
     */
    public function calculateDiscountAmount(float $orderAmount): float
    {
        // Use type and value columns (database schema)
        if ($this->type === 'percentage') {
            return ($this->value / 100) * $orderAmount;
        }

        // Fixed discount - don't exceed order amount
        return min((float) $this->value, $orderAmount);
    }

    /**
     * Get the expires_at attribute (return only the date).
     *
     * @param  string|Carbon  $value
     * @return string
     */
    public function getExpiresAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}
