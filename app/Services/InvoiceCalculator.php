<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Setting;

/**
 * InvoiceCalculator - Centralized invoice calculations
 *
 * IMPORTANT FOR AI - Invoice Calculation Rules:
 * =============================================
 *
 * PRICE COMPONENTS:
 * -----------------
 * 1. offer_price: Base price from accepted offer
 * 2. additional_cost: Extra costs added during order
 * 3. purchases: Items bought (provider does NOT earn from this)
 * 4. preview_cost: Preview order cost (المعيناة)
 * 5. warranty: Warranty cost (provider does NOT earn from this)
 *
 * DISCOUNT RULES:
 * ---------------
 * 1. Discount NEVER applies to purchases (المشتريات)
 * 2. Discount applies to warranty ONLY if apply_to_warranty = true
 * 3. Discount bearer determines who pays:
 *    - 'admin': Admin bears full discount (provider earnings unaffected)
 *    - 'both': Discount shared proportionally between admin and provider
 *
 * EARNINGS RULES:
 * ---------------
 * 1. Provider earns from: offer_price + additional_cost
 * 2. Provider does NOT earn from: purchases, warranty, preview_cost
 * 3. Admin earns from: offer_price + additional_cost
 * 4. If discount bearer = 'admin': Provider earnings calculated BEFORE discount
 *
 * CALCULATION FLOW:
 * -----------------
 * 1. Calculate discountable_base (what discount applies to)
 * 2. Calculate discount amount
 * 3. Calculate earnings_base (what provider/admin earn from)
 * 4. Apply bearer rules to earnings
 * 5. Calculate total amount customer pays
 */
class InvoiceCalculator
{
    private Order $order;
    private ?DiscountCode $discountCode = null;
    private float $providerPercentage;
    private float $adminPercentage;

    // Price components
    private float $offerPrice = 0;
    private float $additionalCost = 0;
    private float $purchases = 0;
    private float $previewCost = 0;
    private float $warrantyCost = 0;

    // Discount details
    private float $discountAmount = 0;
    private string $discountBearer = 'both';
    private bool $discountAppliesToWarranty = false;

    // Calculated values
    private float $discountableBase = 0;
    private float $earningsBase = 0;
    private float $totalAmount = 0;
    private float $providerEarning = 0;
    private float $adminEarning = 0;

    public function __construct(Order $order, float $providerPercentage, float $adminPercentage)
    {
        $this->order = $order;
        $this->providerPercentage = $providerPercentage;
        $this->adminPercentage = $adminPercentage;
    }

    /**
     * Set discount code for calculations.
     */
    public function setDiscountCode(?DiscountCode $discountCode): self
    {
        $this->discountCode = $discountCode;
        if ($discountCode) {
            $this->discountBearer = $discountCode->bearer ?? 'both';
            $this->discountAppliesToWarranty = $discountCode->apply_to_warranty ?? false;
        }
        return $this;
    }

    /**
     * Calculate all invoice values.
     *
     * @return self
     */
    public function calculate(): self
    {
        $this->extractPriceComponents();
        $this->calculateDiscountableBase();
        $this->calculateDiscountAmount();
        $this->calculateEarningsBase();
        $this->calculateEarnings();
        $this->calculateTotalAmount();

        return $this;
    }

    /**
     * Extract price components from order.
     */
    private function extractPriceComponents(): void
    {
        $this->offerPrice = $this->order->getOfferPrice();
        $this->additionalCost = $this->order->getAdditionalCost();
        $this->purchases = $this->order->getPurchasesValue();
        $this->previewCost = $this->order->getPreviewCost();
        $this->warrantyCost = $this->order->getWarrantyCost();
    }

    /**
     * Calculate what the discount applies to.
     *
     * RULES:
     * - Discount NEVER applies to purchases
     * - Discount applies to warranty ONLY if apply_to_warranty = true
     * - Discount applies to: offer_price + additional_cost (+ warranty if enabled)
     */
    private function calculateDiscountableBase(): void
    {
        // Base: offer_price + additional_cost (NOT purchases, NOT preview_cost)
        $this->discountableBase = $this->offerPrice + $this->additionalCost;

        // Add warranty to discountable base if enabled
        if ($this->discountAppliesToWarranty) {
            $this->discountableBase += $this->warrantyCost;
        }
    }

    /**
     * Calculate discount amount based on discount code.
     */
    private function calculateDiscountAmount(): void
    {
        if (!$this->discountCode || $this->discountableBase <= 0) {
            $this->discountAmount = 0;
            return;
        }

        $this->discountAmount = $this->discountCode->calculateDiscountAmount($this->discountableBase);

        // Ensure discount doesn't exceed discountable base
        $this->discountAmount = min($this->discountAmount, $this->discountableBase);
    }

    /**
     * Calculate earnings base (what provider/admin earn from).
     *
     * RULES:
     * - Earnings from: offer_price + additional_cost
     * - NO earnings from: purchases, warranty, preview_cost
     */
    private function calculateEarningsBase(): void
    {
        $this->earningsBase = $this->offerPrice + $this->additionalCost;
    }

    /**
     * Calculate provider and admin earnings.
     *
     * IMPORTANT RULES:
     * ================
     * 1. Provider earns: (earningsBase * providerPercentage) + ALL purchases
     *    - Purchases go 100% to provider (he bought them with his money)
     * 2. Admin earns: earningsBase * adminPercentage
     *    - Admin does NOT earn from purchases
     *
     * BEARER RULES:
     * - 'admin': Provider earnings calculated on full earningsBase (no discount effect)
     *            Admin bears the entire discount
     * - 'both': Discount is applied to earningsBase, then split proportionally
     */
    private function calculateEarnings(): void
    {
        if ($this->order->isPreview()) {
            $this->calculatePreviewEarnings();
            return;
        }

        if ($this->discountBearer === 'admin') {
            // Admin bears entire discount - provider earnings unaffected
            $baseProviderEarning = $this->earningsBase * $this->providerPercentage;
            $this->adminEarning = ($this->earningsBase * $this->adminPercentage) - $this->discountAmount;

            // Ensure admin earning doesn't go negative
            if ($this->adminEarning < 0) {
                $this->adminEarning = 0;
            }
        } else {
            // Both share the discount proportionally
            $earningsAfterDiscount = $this->earningsBase - $this->discountAmount;
            if ($earningsAfterDiscount < 0) {
                $earningsAfterDiscount = 0;
            }

            $baseProviderEarning = $earningsAfterDiscount * $this->providerPercentage;
            $this->adminEarning = $earningsAfterDiscount * $this->adminPercentage;
        }

        // CRITICAL: Add ALL purchases to provider earnings
        // Provider bought these items with his own money, so he gets 100% back
        $this->providerEarning = $baseProviderEarning + $this->purchases;
    }

    /**
     * Calculate earnings for preview orders.
     *
     * RULES:
     * - Provider gets: (preview_cost * providerPreviewPercentage) + ALL purchases
     * - Admin gets: preview_cost * adminPreviewPercentage
     */
    private function calculatePreviewEarnings(): void
    {
        // Preview orders: earnings based on preview_cost only
        $previewEarningsBase = $this->previewCost - $this->discountAmount;
        if ($previewEarningsBase < 0) {
            $previewEarningsBase = 0;
        }

        $adminPreviewPercentage = Setting::getAdminPreviewPercentage();
        $providerPreviewPercentage = Setting::getProviderPreviewPercentage();

        $this->adminEarning = $previewEarningsBase * $adminPreviewPercentage;

        // Provider gets his percentage of preview_cost + ALL purchases
        $baseProviderEarning = $previewEarningsBase * $providerPreviewPercentage;
        $this->providerEarning = $baseProviderEarning + $this->purchases;
    }

    /**
     * Calculate total amount customer pays.
     *
     * FORMULA:
     * total = offer_price + additional_cost + purchases + preview_cost + warranty - discount
     */
    private function calculateTotalAmount(): void
    {
        $subtotal = $this->offerPrice + $this->additionalCost + $this->purchases + $this->previewCost;
        $this->totalAmount = $subtotal + $this->warrantyCost - $this->discountAmount;

        if ($this->totalAmount < 0) {
            $this->totalAmount = 0;
        }
    }

    // ==================== GETTERS ====================

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getProviderEarning(): float
    {
        return $this->providerEarning;
    }

    public function getAdminEarning(): float
    {
        return $this->adminEarning;
    }

    public function getDiscountAmount(): float
    {
        return $this->discountAmount;
    }

    /**
     * Get all details for invoice storage.
     *
     * @return array
     */
    public function getInvoiceDetails(): array
    {
        return [
            // Price components
            'offer_price' => round($this->offerPrice, 2),
            'additional_cost' => round($this->additionalCost, 2),
            'purchases' => round($this->purchases, 2),
            'preview_cost' => round($this->previewCost, 2),
            'warranty' => round($this->warrantyCost, 2),

            // Discount details
            'discount_amount' => round($this->discountAmount, 2),
            'discount_bearer' => $this->discountBearer,
            'discount_applies_to_warranty' => $this->discountAppliesToWarranty,
            'discountable_base' => round($this->discountableBase, 2),

            // Earnings breakdown
            'earnings_base' => round($this->earningsBase, 2),
            'provider_earning' => round($this->providerEarning, 2),
            'admin_earning' => round($this->adminEarning, 2),
            'provider_percentage' => round($this->providerPercentage, 4),
            'admin_percentage' => round($this->adminPercentage, 4),

            // Totals
            'subtotal_before_discount' => round($this->offerPrice + $this->additionalCost + $this->purchases + $this->previewCost + $this->warrantyCost, 2),
            'total_amount' => round($this->totalAmount, 2),
        ];
    }


    /**
     * Get summary for debugging/logging.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'order_id' => $this->order->id,
            'is_preview' => $this->order->isPreview(),
            'components' => [
                'offer_price' => $this->offerPrice,
                'additional_cost' => $this->additionalCost,
                'purchases' => $this->purchases,
                'preview_cost' => $this->previewCost,
                'warranty' => $this->warrantyCost,
            ],
            'discount' => [
                'amount' => $this->discountAmount,
                'bearer' => $this->discountBearer,
                'applies_to_warranty' => $this->discountAppliesToWarranty,
            ],
            'earnings' => [
                'base' => $this->earningsBase,
                'provider' => $this->providerEarning,
                'admin' => $this->adminEarning,
            ],
            'total' => $this->totalAmount,
        ];
    }
}



