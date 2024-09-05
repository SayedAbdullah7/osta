<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Invoice;
use App\Models\Order;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Str;

class WalletService
{
    private const ADMIN_PERCENTAGE = 0.20;
    private const PROVIDER_PERCENTAGE = 0.80;
    private const ADMIN_PREVIEW_PERCENTAGE = 0.5;
    private const PROVIDER_PREVIEW_PERCENTAGE = 0.5;
    private const PREVIEW_COST = 50;


    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }
    /**
     * @throws ExceptionInterface
     */
    public function payCash(Order $order): array
    {
        $invoice = $this->getInvoiceData($order);
        $providerWallet = $this->getWallet($order->provider);
        $adminWallet = $this->getAdminWallet();

        DB::transaction(function () use ($providerWallet, $adminWallet, $invoice, $order) {
            $this->forceWithdraw($providerWallet, $invoice->total, ['description' => 'cash payment order #' . $order->id]);
            $this->deposit($adminWallet, $invoice->admin_earning, ['description' => 'cash payment order #' . $order->id]);

            $this->updateInvoicePaymentDetails($invoice, 'cash', 'paid');
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


    private function calculateEarnings(Order $order): array
    {
        $discount = $this->getDiscountAmount($order);
        return $order->isPreview()
            ? $this->calculatePreviewEarnings($order, $discount)
            : $this->calculateOrderEarnings($order, $discount);
    }

    private function calculatePreviewEarnings(Order $order, float $discount = 0): array
    {
        $totalAmount = self::PREVIEW_COST - $discount;
        $adminEarning = $totalAmount * self::ADMIN_PREVIEW_PERCENTAGE;
        $providerEarning = $totalAmount * self::PROVIDER_PREVIEW_PERCENTAGE;

        return [$totalAmount, $adminEarning, $providerEarning, $discount];
    }

    private function calculateOrderEarnings(Order $order, float $discount = 0): array
    {
        $totalAmount = $order->price - $discount;
        $adminEarning = $totalAmount * self::ADMIN_PERCENTAGE;
        $providerEarning = $totalAmount * self::PROVIDER_PERCENTAGE;

        return [$totalAmount, $adminEarning, $providerEarning, $discount];
    }

    public function storeInvoice(Order $order, float $total, float $adminEarning, float $providerEarning, float $discount = 0, float $tax = 0, string $paymentMethod = 'wallet'): Invoice
    {
        $invoice = new Invoice();
        $invoice->order_id = $order->id;
        $invoice->total = $total;
        $invoice->admin_earning = $adminEarning;
        $invoice->provider_earning = $providerEarning;
        $invoice->discount = $discount;
        $invoice->tax = $tax;
        $invoice->payment_method = $paymentMethod;
        $invoice->sub_total = $total - $discount + $tax;
        $invoice->status = 'pending';
        $invoice->payment_status = 'pending';
        $invoice->uuid = Str::uuid();
        $invoice->details = [
            'service' => $order->service->name,
            'worker' => $order->provider->first_name . ' ' . $order->provider->last_name,
            'working_in_minutes' => '',
            'order_created_at' => $order->created_at
        ];
        $invoice->save();

        return $invoice;
    }

    public function updateInvoicePrice(Invoice $invoice, float $total, float $adminEarning, float $providerEarning, float $discount = 0, float $tax = 0): Invoice
    {
        $invoice->total = $total;
        $invoice->admin_earning = $adminEarning;
        $invoice->provider_earning = $providerEarning;
        $invoice->discount = $discount;
        $invoice->tax = $tax;
        $invoice->sub_total = $total - $discount + $tax;
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

    public function updateInvoiceAdditionalCost(Invoice $invoice, Order $order): Invoice
    {
        [$totalAmount, $adminEarning, $providerEarning, $discount] = $this->calculateEarnings($order);
        return $this->updateInvoicePrice($invoice, $totalAmount, $adminEarning, $providerEarning, $discount);
    }

    public function createInvoice(Order $order): Invoice
    {
        [$totalAmount, $adminEarning, $providerEarning, $discount] = $this->calculateEarnings($order);
        return $this->storeInvoice($order, $totalAmount, $adminEarning, $providerEarning, $discount);
    }

    /**
     * @throws \Exception
     */
    private function getDiscountAmount(Order $order): float
    {
        if ($order->discount_code) {
            return $this->discountService->calculateDiscountAmount($order->discount_code, $order->price);
        }

        return 0.0;
    }

    public function getInvoiceData(Order $order): Invoice
    {
        return $order->invoice ?? $this->createInvoice($order);
    }

    private function getDiscount(Order $order): float
    {
        // here get and apply discount
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
