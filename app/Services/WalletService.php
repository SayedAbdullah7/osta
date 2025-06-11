<?php

namespace App\Services;

use App\Events\OrderPaidByUserEvent;
use App\Http\Resources\InvoiceResource;
use App\Models\Admin;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\Order;
use App\Models\ProviderStatistic as ProviderStatistics;
use App\Models\Setting;
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
    public const PREVIEW_COST = 100;


    protected $discountService;
    protected $providerStatisticService;

    public function __construct(DiscountService $discountService,ProviderStatisticService $providerStatisticService)
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
    public function payInvoiceByAdminWallet(Invoice $invoice, float $amount = 0, $adminId): array
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


    private function calculateEarnings(Order $order): array
    {
        $discount = $this->getDiscountAmount($order);
        return $order->isPreview()
            ? $this->calculatePreviewEarnings($order, $discount)
            : $this->calculateOrderEarnings($order, $discount);
    }

    private function calculatePreviewEarnings(Order $order, float $discount = 0): array
    {
        $previewCost = Setting::getSetting('preview_cost', self::PREVIEW_COST);
        $totalAmount = $previewCost - $discount;
        $adminEarning = $totalAmount * self::ADMIN_PREVIEW_PERCENTAGE;
        $providerEarning = $totalAmount * self::PROVIDER_PREVIEW_PERCENTAGE;
        return [$totalAmount, $adminEarning, $providerEarning, $discount];
    }

    private function getPercentage($providerId)
    {
        $providerStatistic= $this->providerStatisticService->getProviderStatistics($providerId);
        $level = Level::where('level',$providerStatistic->level)->first();
//        $providerEarning = $level->percentage/100;
//        $adminEarning = 1 - $providerEarning;
        $adminEarning = $level->percentage/100;
        $providerEarning = 1 - $adminEarning;

        return [$providerEarning, $adminEarning];
    }
    private function calculateOrderEarnings(Order $order, float $discount = 0): array
    {
        $totalAmount = $order->price - $discount;
        list($providerPercentage, $adminPercentage) = $this->getPercentage($order->provider_id);

        $adminEarning = $totalAmount * $adminPercentage;
        $providerEarning = $totalAmount * $providerPercentage;

        return [$totalAmount, $adminEarning, $providerEarning, $discount];
    }

    public function storeInvoice(Order $order, float $total, float $adminEarning, float $providerEarning, float $discount = 0, float $tax = 0, string $paymentMethod = null): Invoice
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
//        $invoice->status = 'pending';
//        $invoice->payment_status = 'pending';
        $invoice->status = 'unpaid';
        $invoice->payment_status = 'unpaid';
        $invoice->uuid = Str::uuid();
        $invoice->details = [
            'service' => $order->service->name,
            'worker' => $order->provider->name,
//            'worker' => $order->provider->first_name . ' ' . $order->provider->last_name,
            'working_in_minutes' => '',
            'order_created_at' => $order->created_at,
            'offer_price' => $order->getOfferPrice(),
            'additional_cost' => $order->getAdditionalCost(),
            'purchases' => $order->getPurchasesValue()
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
        $invoice->updatePaymentStatus();
        $details = $invoice->details;
        $details['offer_price'] = $invoice->order->getOfferPrice();
        $details['additional_cost'] = $invoice->order->getAdditionalCost();
        $details['purchases'] = $invoice->order->getPurchasesValue();
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
