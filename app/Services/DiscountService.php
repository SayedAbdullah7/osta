<?php

namespace App\Services;

use App\Filament\Resources\DiscountCodeResource;
use App\Models\DiscountCode;
use App\Models\Order;
use Exception;
use Carbon\Carbon;

/**
 * DiscountService - Handles discount code operations
 *
 * IMPORTANT FOR AI - Discount Types:
 * ===================================
 *
 * BEARER TYPES (who pays the discount):
 * - 'admin': Admin bears entire discount (provider earnings unaffected)
 * - 'both': Both admin and provider share discount proportionally
 *
 * APPLY_TO_WARRANTY:
 * - true: Discount applies to warranty cost
 * - false: Discount does NOT apply to warranty
 */
class DiscountService
{
    /**
     * Get discount code by code string.
     */
    public function getDiscountByCode(string $code): ?DiscountCode
    {
        return DiscountCode::where('code', $code)->first();
    }

    /**
     * Get discount code for an order (if applied).
     */
    public function getDiscountCodeForOrder(Order $order): ?DiscountCode
    {
        if (!$order->discount_code) {
            return null;
        }
        return $this->getDiscountByCode($order->discount_code);
    }

    public function calculateDiscountAmount(string $code, float $orderAmount): float
    {
        $discount = $this->getDiscountByCode($code);

        if (!$discount) {
            throw new Exception("Discount code not found.");
        }

        return $this->calculateDiscountValue($discount, $orderAmount);
    }

    private function calculateDiscountValue(DiscountCode $discount, float $orderAmount): float
    {
        if ($discount->type === 'fixed') {
            return min($discount->value, $orderAmount);
        } elseif ($discount->type === 'percentage') {
            return ($discount->value / 100) * $orderAmount;
        }

        return 0.0;
    }

    public function deactivateDiscountCode(DiscountCode $discountCode, $userId): void
    {
        $discountCode->update([
            'is_active' => false,
            'used_at' => now(),
            'used_by' => $userId,
        ]);
    }

    public function checkDiscountCodeValidity(string $code): array
    {
        $discount = $this->getDiscountByCode($code);

        if (!$discount) {
            return ['valid' => false, 'message' => 'Discount code not found.'];
        }

        if (!$discount->is_active) {
            return ['valid' => false, 'message' => 'Discount code is inactive.'];
        }

        if ($discount->expires_at && Carbon::now()->gt($discount->expires_at)) {
            return ['valid' => false, 'message' => 'Discount code has expired.'];
        }

        return ['valid' => true, 'discount' => $discount, 'message' => 'Discount code is valid.'];
    }

    public function applyDiscountCodeToOrder(Order $order, string $code): array
    {
        $validity = $this->checkDiscountCodeValidity($code);

        if (!$validity['valid']) {
            return $validity; // Return the error message if not valid
        }

        $discount = $validity['discount'];

        // Apply the discount code to the order
        $order->discount_code = $code;
        $order->save();

        // Deactivate the discount code
        $this->deactivateDiscountCode($discount, $order->user_id);

        return ['valid' => true, 'message' => 'Discount code applied successfully.'];
    }

    /**
     * @throws Exception
     */
    public function updateOrderWithDiscount(Order $order): void
    {
        $discountAmount = $this->calculateDiscountAmount($order->discount_code, $order->max_allowed_price);
        // Calculate the new order total after discount
        $newTotal = $order->max_allowed_price - $discountAmount;

        // Ensure the new total does not go below zero
        $order->max_allowed_price = max($newTotal, 0);
        $order->save();
    }
}
