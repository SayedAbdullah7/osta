<?php

namespace App\Services;

use App\Events\OrderPaidByUserEvent;
use App\Http\Resources\InvoiceResource;
use App\Models\Admin;
use App\Models\DiscountCode;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\Order;
use App\Models\ProviderStatistic as ProviderStatistics;
use App\Models\Setting;
use App\Services\SubscriptionService;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;

/**
 * WalletService
 *
 * IMPORTANT FOR AI - Invoice and Earnings Calculations:
 * =====================================================
 *
 * OVERVIEW:
 * ---------
 * This service handles all invoice creation, price calculations, and earnings distribution.
 * Uses InvoiceCalculator for centralized calculations.
 *
 * KEY RULES:
 * ----------
 *
 * 1. DISCOUNT RULES:
 *    - Discount NEVER applies to purchases (المشتريات)
 *    - Discount applies to warranty ONLY if apply_to_warranty = true
 *    - Discount bearer determines who pays:
 *      * 'admin': Admin bears full discount (provider earnings unaffected)
 *      * 'both': Discount shared proportionally between admin and provider
 *
 * 2. EARNINGS RULES:
 *    - Provider earns from: offer_price + additional_cost ONLY
 *    - Provider does NOT earn from: purchases, warranty, preview_cost
 *    - If discount bearer = 'admin': Provider earnings calculated BEFORE discount
 *
 * 3. INVOICE DETAILS:
 *    All calculations stored in invoice->details JSON for transparency:
 *    - Price components: offer_price, additional_cost, purchases, preview_cost, warranty
 *    - Discount info: amount, bearer, applies_to_warranty
 *    - Earnings: base, provider_earning, admin_earning, percentages
 *
 * SEE ALSO: InvoiceCalculator for detailed calculation logic
 */
class WalletService
{
    private const ADMIN_PERCENTAGE = 0.20;
    private const PROVIDER_PERCENTAGE = 0.80;
    private const ADMIN_PREVIEW_PERCENTAGE = 0.5;
    private const PROVIDER_PREVIEW_PERCENTAGE = 0.5;
    public const PREVIEW_COST = 100;

    protected DiscountService $discountService;
    protected ProviderStatisticService $providerStatisticService;

    public function __construct(DiscountService $discountService, ProviderStatisticService $providerStatisticService)
    {
        $this->discountService = $discountService;
        $this->providerStatisticService = $providerStatisticService;
    }
    /**
     * @throws ExceptionInterface
     */
    public function payCash(Order $order): array
    {
        $invoice = $this->getInvoiceData($order);

        if ($invoice->isFullyPaid()) {
            return ['message' => 'Invoice already fully paid', 'status' => false];
        }
        $amount = $invoice->unpaidAmount();

        if ($amount <= 0) {
            return ['message' => 'Invoice already fully paid', 'status' => false];
        }

        $providerWallet = $this->getWallet($order->provider);
        $adminWallet = $this->getAdminWallet();

        DB::transaction(function () use ($providerWallet, $adminWallet, $invoice, $order,$amount) {
            $this->forceWithdraw($providerWallet, $amount - $invoice->provider_earning, ['description' => 'cash payment order #' . $order->id]);
            $invoice->addPayment($amount, 'cash', ['description' => 'Cash payment for invoice']);

            $this->deposit($adminWallet, $invoice->admin_earning, ['description' => 'cash payment order #' . $order->id]);

            $this->updateInvoicePaymentDetails($invoice, 'cash', 'paid');

            $invoice->updatePaymentStatus();
            $invoice->save();
        });

        return ['message' => 'Payment successful', 'status' => true];
    }

    /**
     */
    public function payByWallet(Order $order): array
    {
        $invoice = $this->getInvoiceData($order);

        if (!$this->checkBalance($order->user, $invoice->total)) {
            return ['message' => 'Insufficient funds', 'status' => false];
        }

        $userWallet = $this->getWallet($order->user);
        $providerWallet = $this->getWallet($order->provider);
        $adminWallet = $this->getAdminWallet();

        DB::transaction(function () use ($userWallet, $providerWallet, $adminWallet, $invoice, $order) {
            $this->forceWithdraw($userWallet, $invoice->total, ['description' => 'payment order #' . $order->id]);
            $this->deposit($providerWallet, $invoice->provider_earning, ['description' => 'payment order #' . $order->id]);
            $this->deposit($adminWallet, $invoice->admin_earning, ['description' => 'payment order #' . $order->id]);

            $this->updateInvoicePaymentDetails($invoice, 'wallet', 'paid');
        });

        return ['message' => 'Payment successful', 'status' => true];
    }

    /**
     * Pay an amount for the given invoice.
     *
     * @param Invoice $invoice
     * @param WalletInterface $wallet
     * @param float $amount
     * @return array
     * @throws ExceptionInterface
     */
    public function payInvoice(Invoice $invoice, WalletInterface $wallet, float $amount): array
    {
        if ($amount <= 0) {
            return ['message' => 'Invalid payment amount', 'status' => false];
        }

        if (!$this->checkBalance($wallet, $amount)) {
            return ['message' => 'Insufficient funds', 'status' => false];
        }

        DB::transaction(function () use ($wallet, $invoice, $amount) {
            $this->forceWithdraw($wallet, $amount, ['description' => 'Payment for invoice #' . $invoice->id]);
            $invoice->payment_status = 'partial';
            $invoice->total_paid = ($invoice->total_paid ?? 0) + $amount;

            if ($invoice->total_paid >= $invoice->total) {
                $invoice->payment_status = 'paid';
            }

            $invoice->save();
        });

        return ['message' => 'Payment successful', 'status' => true];
    }
//    public function payInvoiceByWallet(Invoice $invoice, WalletInterface $userWallet, float $amount): array
    public function payOrderByWallet(Order $order, float $amount = 0): array
    {
        $invoice = $this->getInvoiceData($order);
        $userWallet = $this->getWallet($order->user);
        if ($amount == 0){
            $amount = $invoice->unpaidAmount();
        }

//        if (!$this->checkBalance($order->user, $invoice->total)) {
        if (!$this->checkBalance($userWallet, $amount)) {
            return ['message' => 'Insufficient funds', 'status' => false];
        }

        if ($invoice->isFullyPaid()) {
            return ['message' => 'Invoice already fully paid', 'status' => false];
        }

        if ($amount <= 0) {
            return ['message' => 'Invalid payment amount', 'status' => false];
        }


        DB::transaction(function () use ($invoice, $userWallet, $amount) {
            // Withdraw from user wallet
            $this->forceWithdraw($userWallet, $amount, [
                'description' => 'Payment for invoice #' . $invoice->id
            ]);

            // Store transaction in the invoice
//            $invoice->transactions()->create([
//                'amount' => $amount,
//                'payment_method' => 'wallet',
//                'meta' => ['description' => 'Wallet payment for invoice'],
//            ]);
            $invoice->addPayment($amount, 'wallet', ['description' => 'Wallet payment for invoice']);

            // Update the paid amount in the invoice
//            $invoice->increment('paid', $amount);

            // Update payment status
            $invoice->updatePaymentStatus();
            $invoice->save();
        });
        $invoice->refresh();
        if ($invoice->isFullyPaid()) {
            event(new OrderPaidByUserEvent($order, $order->user));
        }
        $socketService = new SocketService();
        $data = new InvoiceResource($invoice);
        $event = 'invoice_updated';
        $msg = 'payment added to order #' . $order->id;
        $provider_id = $order->provider_id;
        $socketService->push('provider',$data,[$provider_id], $event, $msg);

            return ['message' => 'Payment successful', 'status' => true];
    }


    public function payInvoiceByWallet(Invoice $invoice, float $amount = 0): array
    {
        $userWallet = $this->getWallet($invoice->user); // Assuming the invoice has a relationship to the user

        if ($amount == 0) {
            $amount = $invoice->unpaidAmount();
        }

        if (!$this->checkBalance($userWallet, $amount)) {
            return ['message' => 'Insufficient funds', 'status' => false];
        }

        if ($invoice->isFullyPaid()) {
            return ['message' => 'Invoice already fully paid', 'status' => false];
        }

        if ($amount <= 0) {
            return ['message' => 'Invalid payment amount', 'status' => false];
        }

        DB::transaction(function () use ($invoice, $userWallet, $amount) {
            // Withdraw from user wallet
            $this->forceWithdraw($userWallet, $amount, [
                'description' => 'Payment for invoice #' . $invoice->id
            ]);

            // Add payment to the invoice
            $invoice->addPayment($amount, 'wallet', ['description' => 'Wallet payment for invoice']);

            // Update payment status
            $invoice->updatePaymentStatus();
        });

        return ['message' => 'Payment successful', 'status' => true];
    }
    public function payInvoiceByAdminWallet(Invoice $invoice, $adminId, float $amount = 0): array
    {
        $userWallet = $this->getAdminWallet();
        if ($amount == 0) {
            $amount = $invoice->unpaidAmount();
        }

        if (!$this->checkBalance($userWallet, $amount)) {
            return ['message' => 'Insufficient funds', 'status' => false];
        }

        if ($invoice->isFullyPaid()) {
            return ['message' => 'Invoice already fully paid', 'status' => false];
        }

        if ($amount <= 0) {
            return ['message' => 'Invalid payment amount', 'status' => false];
        }

        DB::transaction(function () use ($invoice, $userWallet, $amount, $adminId) {
            // Withdraw from user wallet
            $this->forceWithdraw($userWallet, $amount, [
                'description' => 'Payment for invoice #' . $invoice->id
            ]);

            // Add payment to the invoice
            $invoice->addPayment($amount, 'wallet', ['description' => 'Wallet payment for invoice'],$adminId);

            // Update payment status
            $invoice->updatePaymentStatus();
        });

        return ['message' => 'Payment successful', 'status' => true];
    }

    /**
     * Distribute funds to the technician and admin upon order completion.
     *
     * @param Order $order
     * @return array
     * @throws ExceptionInterface
     */
    public function distributeFunds(Order $order): array
    {
        $invoice = $order->invoice;

        if (!$invoice || $invoice->payment_status !== 'paid') {
            throw new \Exception('Cannot distribute funds. Invoice is not fully paid.');
        }

        $providerWallet = $this->getWallet($order->provider);
        $adminWallet = $this->getAdminWallet();

        DB::transaction(function () use ($providerWallet, $adminWallet, $invoice, $order) {
            $this->deposit($providerWallet, $invoice->provider_earning, ['description' => 'Earnings for order #' . $order->id]);
            $this->deposit($adminWallet, $invoice->admin_earning, ['description' => 'Admin earnings for order #' . $order->id]);
        });

        return ['message' => 'funds distributed', 'status' => true];

    }


    /**
     * Calculate earnings for an order using InvoiceCalculator.
     *
     * @param Order $order
     * @return InvoiceCalculator
     */
    private function calculateEarnings(Order $order): InvoiceCalculator
    {
        // Get provider/admin percentages
        list($providerPercentage, $adminPercentage) = $this->getPercentage($order->provider_id);

        // Get discount code if applied
        $discountCode = $this->discountService->getDiscountCodeForOrder($order);

        // Use InvoiceCalculator for all calculations
        $calculator = new InvoiceCalculator($order, $providerPercentage, $adminPercentage);
        $calculator->setDiscountCode($discountCode)->calculate();

        return $calculator;
    }

    /**
     * Get provider/admin percentage split based on provider level or active subscription.
     *
     * IMPORTANT:
     * - commission_rate is in level->benefits JSON (e.g., 0.9 = 90% for provider)
     * - fee_percentage in subscription is stored as percentage (e.g., 85 = 85%)
     * - If provider has active subscription, we compare both percentages and use the higher one (best for provider)
     *
     * @param int $providerId
     * @return array [providerPercentage, adminPercentage]
     */
    private function getPercentage($providerId): array
    {
        $providerStatistic = $this->providerStatisticService->getProviderStatistics($providerId);
        $level = Level::where('level', $providerStatistic->level)->first();

        // Get commission_rate from level benefits (e.g., 0.9 = 90% for provider)
        // Default: provider gets 80%, admin gets 20%
        $levelPercentage = $level->benefits['commission_rate'] ?? 0.80;

        // Check if provider has active subscription
        $subscriptionService = app(SubscriptionService::class);
        $activeSubscription = $subscriptionService->getProviderLastValidSubscription($providerId);

        $providerPercentage = $levelPercentage;

        if ($activeSubscription && $activeSubscription->subscription) {
            // Get fee_percentage from subscription
            // fee_percentage is stored as percentage (e.g., 85 = 85%), so convert to decimal (0.85)
            $subscriptionFeePercentage = $activeSubscription->subscription->fee_percentage ?? null;

            if ($subscriptionFeePercentage !== null) {
                // Convert from percentage to decimal if needed
                // If fee_percentage > 1, it's already in percentage form (85), convert to decimal (0.85)
                // If fee_percentage <= 1, it's already in decimal form (0.85)
                $subscriptionPercentageDecimal = $subscriptionFeePercentage > 1
                    ? $subscriptionFeePercentage / 100
                    : $subscriptionFeePercentage;

                // Use the higher percentage (best for provider)
                $providerPercentage = max($levelPercentage, $subscriptionPercentageDecimal);
            }
        }

        $adminPercentage = 1 - $providerPercentage;

        return [$providerPercentage, $adminPercentage];
    }

    /**
     * Store a new invoice for an order using InvoiceCalculator.
     *
     * DETAILS STORED:
     * - Price components: offer_price, additional_cost, purchases, preview_cost, warranty
     * - Discount info: amount, bearer, applies_to_warranty, discountable_base
     * - Earnings: base, provider_earning, admin_earning, percentages
     * - Totals: subtotal_before_discount, total_amount
     *
     * @param Order $order
     * @param InvoiceCalculator $calculator
     * @param float $tax Tax amount
     * @param string|null $paymentMethod Payment method
     * @return Invoice
     */
    public function storeInvoice(Order $order, InvoiceCalculator $calculator, float $tax = 0, string $paymentMethod = null): Invoice
    {
        $invoice = new Invoice();
        $invoice->order_id = $order->id;
        $invoice->total = $calculator->getTotalAmount();
        $invoice->admin_earning = $calculator->getAdminEarning();
        $invoice->provider_earning = $calculator->getProviderEarning();
        $invoice->discount = $calculator->getDiscountAmount();
        $invoice->tax = $tax;
        $invoice->payment_method = $paymentMethod;
        $invoice->sub_total = $calculator->getTotalAmount() + $tax; // Total already has discount applied
        $invoice->status = 'unpaid';
        $invoice->payment_status = 'unpaid';
        $invoice->uuid = Str::uuid();

        // Store ALL calculation details in invoice->details
        // This provides full transparency and audit trail
        $details = $calculator->getInvoiceDetails();
        $details['service'] = $order->service->name ?? '';
        $details['worker'] = $order->provider->name ?? '';
        $details['working_in_minutes'] = '';
        $details['order_created_at'] = $order->created_at;

        $invoice->details = $details;
        $invoice->save();

        return $invoice;
    }

    /**
     * Update invoice price and earnings using InvoiceCalculator.
     *
     * @param Invoice $invoice
     * @param InvoiceCalculator $calculator
     * @param float $tax Tax amount
     * @return Invoice
     */
    public function updateInvoicePrice(Invoice $invoice, InvoiceCalculator $calculator, float $tax = 0): Invoice
    {
        $invoice->total = $calculator->getTotalAmount();
        $invoice->admin_earning = $calculator->getAdminEarning();
        $invoice->provider_earning = $calculator->getProviderEarning();
        $invoice->discount = $calculator->getDiscountAmount();
        $invoice->tax = $tax;
        $invoice->sub_total = $calculator->getTotalAmount() + $tax;
        $invoice->updatePaymentStatus();

        // Update ALL calculation details
        $details = $calculator->getInvoiceDetails();
        $details['service'] = $invoice->order->service->name ?? '';
        $details['worker'] = $invoice->order->provider->name ?? '';
        $details['working_in_minutes'] = $invoice->details['working_in_minutes'] ?? '';
        $details['order_created_at'] = $invoice->order->created_at;

        $invoice->details = $details;
        $invoice->save();

        return $invoice;
    }

    public function updateInvoicePaymentDetails(Invoice $invoice, string $paymentMethod, string $paymentStatus): Invoice
    {
        $invoice->payment_method = $paymentMethod;
        $invoice->payment_status = $paymentStatus;

        $details = $invoice->details ?? [];
        $details['working_in_minutes'] = Carbon::now()->diffInMinutes($invoice->created_at);
        $invoice->details = $details;
        $invoice->save();

        return $invoice;
    }

    /**
     * Update invoice when additional costs, purchases, or other price components change.
     *
     * Uses InvoiceCalculator to recalculate all values and update invoice details.
     *
     * @param Invoice $invoice
     * @param Order $order
     * @return Invoice
     */
    public function updateInvoiceAdditionalCost(Invoice $invoice, Order $order): Invoice
    {
        $calculator = $this->calculateEarnings($order);
        return $this->updateInvoicePrice($invoice, $calculator);
    }

    /**
     * Create a new invoice for an order.
     *
     * Uses InvoiceCalculator to calculate all values and store full details.
     *
     * @param Order $order
     * @return Invoice
     */
    public function createInvoice(Order $order): Invoice
    {
        $calculator = $this->calculateEarnings($order);
        return $this->storeInvoice($order, $calculator);
    }

    public function getInvoiceData(Order $order): Invoice
    {
        return $order->invoice ?? $this->createInvoice($order);
    }

    private function getDiscount(Order $order): float
    {
        // here get and apply discount
        return 0.0;
    }

    public function checkBalance(WalletInterface $wallet, float $amount): bool
    {
        return $this->getBalance($wallet) >= $amount;
    }

    public function getAdminWallet(): ?Wallet
    {
        $wallet = Wallet::where('holder_type', Admin::class)->first();
        return $wallet ?? $this->createAdminWallet();
    }

    public function createAdminWallet(): Wallet
    {
        $admin = Admin::first();
        return $admin->wallet;
    }

    public function getWallet(WalletInterface $user): Wallet
    {
        return $user->wallet;
    }

    public function getBalance(WalletInterface $user): string
    {
        return $this->getWallet($user)->balance;
    }

    public function getSimpleLatestPaginatedTransactions(WalletInterface $user, int $perPage = 10): Paginator
    {
        return $user->transactions()->latest()->simplePaginate($perPage);
    }

    public function getLatestPaginatedTransactions(WalletInterface $user, $page, int $perPage = 10): Paginator
    {
        return $user->transactions()->latest()->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @throws ExceptionInterface
     */
    public function deposit(WalletInterface $wallet, float $amount, array $meta = null): Transaction
    {
        return $wallet->deposit($amount, $meta);
    }

    /**
     * @throws ExceptionInterface
     */
    public function withdraw(WalletInterface $wallet, float $amount, array $meta = null): Transaction
    {
        return $wallet->withdraw($amount, $meta);
    }

    /**
     * @throws ExceptionInterface
     */
    public function forceWithdraw(WalletInterface $wallet, float $amount, array $meta = null): Transaction
    {
        return $wallet->forceWithdraw($amount, $meta);
    }

    /**
     * @throws ExceptionInterface
     */
    public function transfer(WalletInterface $from, WalletInterface $to, float $amount): \Bavix\Wallet\Models\Transfer
    {
        return $from->transfer($to, $amount);
    }
}
