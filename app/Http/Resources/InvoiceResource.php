<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * InvoiceResource
 *
 * IMPORTANT FOR AI - Invoice Details Structure:
 * =============================================
 *
 * The invoice->details JSON contains all calculation data:
 *
 * PRICE COMPONENTS:
 * - offer_price: Original offer price
 * - additional_cost: Extra costs added
 * - purchases: Items bought (provider does NOT earn from this)
 * - preview_cost: Preview order cost (المعيناة)
 * - warranty: Warranty cost (provider does NOT earn from this)
 *
 * DISCOUNT INFO:
 * - discount_amount: How much discount applied
 * - discount_bearer: 'admin' or 'both' (who pays the discount)
 * - discount_applies_to_warranty: true/false
 * - discountable_base: What the discount was calculated on
 *
 * EARNINGS INFO:
 * - earnings_base: What provider/admin earn from (offer_price + additional_cost)
 * - provider_earning: Provider's earnings
 * - admin_earning: Admin's earnings
 * - provider_percentage: Provider's percentage rate
 * - admin_percentage: Admin's percentage rate
 *
 * TOTALS:
 * - subtotal_before_discount: Sum before discount
 * - total_amount: Final amount customer pays
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $details = $this->details ?? [];

        // Get all price components
        $offer_price = (float) ($details['offer_price'] ?? 0);
        $additional_cost = (float) ($details['additional_cost'] ?? 0);
        $purchases = (float) ($details['purchases'] ?? 0);
        $preview_cost = (float) ($details['preview_cost'] ?? 0);
        $warranty = (float) ($details['warranty'] ?? 0);

        // Get discount info
        $discount_amount = (float) ($details['discount_amount'] ?? $this->discount ?? 0);
        $discount_bearer = $details['discount_bearer'] ?? 'both';
        $discount_applies_to_warranty = (bool) ($details['discount_applies_to_warranty'] ?? false);

        // Get earnings info
        $earnings_base = (float) ($details['earnings_base'] ?? 0);
        $provider_percentage = (float) ($details['provider_percentage'] ?? 0);
        $admin_percentage = (float) ($details['admin_percentage'] ?? 0);

        // Fallback for preview_cost if not set
        if ($offer_price == 0 && $preview_cost == 0 && $this->order && $this->order->isPreview()) {
            $preview_cost = Setting::getPreviewCost();
        }

        // Calculate warranty if not stored but order has warranty
        if ($warranty == 0 && $this->order && $this->order->warranty) {
            $warranty = $this->order->getWarrantyCost();
        }

        return [
            'id' => $this->id,
            'invoice_number' => $this->uuid,
            'status' => $this->status,
            'provider_cost' => (string) $this->provider_earning,
            'app_fess' => (string) $this->admin_earning,
            'sub_total' => (string) $this->sub_total,
            'tax' => (string) $this->tax,
            'total' => (string) $this->total,
            'discount' => (string) $discount_amount,
            'paid' => (string) $this->paid,
            'unpaid' => (string) $this->unpaidAmount(),
            'payment_status' => $this->payment_status,
            'order_id' => $this->order_id,
            'payment_method' => $this->payment_method,

            // Complete price breakdown
            'price_details' => [
                'offer_price' => (string) $offer_price,
                'additional_cost' => (string) $additional_cost,
                'purchases' => (string) $purchases,
                'preview_cost' => (string) $preview_cost,
                'warranty' => (string) $warranty,
            ],

            // Discount details
            'discount_details' => [
                'amount' => (string) $discount_amount,
                'bearer' => $discount_bearer, // 'admin' = admin only, 'both' = shared
                'applies_to_warranty' => $discount_applies_to_warranty,
            ],

            // Earnings breakdown
            'earnings_details' => [
                'base' => (string) $earnings_base, // What earnings calculated on
                'provider_earning' => (string) $this->provider_earning,
                'admin_earning' => (string) $this->admin_earning,
                'provider_percentage' => (string) round($provider_percentage * 100, 2) . '%',
                'admin_percentage' => (string) round($admin_percentage * 100, 2) . '%',
            ],

            // Keep backward compatibility
            'offer_price' => (string) $offer_price,
            'additional_cost' => (string) $additional_cost,
            'purchases' => (string) $purchases,
            'preview_cost' => (string) $preview_cost,
            'warranty' => (string) $warranty,

            'purchase_info' => $this->purchases ? [[
                'description' => $this->purchases->description,
                'value' => $this->purchases->value,
            ]] : [],
            'qr_code_content' => $this->uuid,
            'is_sent' => (int) $this->is_sent,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'order' => new OrderResource($this->whenLoaded('order')),
        ];
    }
}
