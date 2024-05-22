<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Interfaces\Wallet as WalletInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletService
{
    // pay order by wallet from user to provider
    // provider earn 80% of order amount and Admin earn 20% of order amount

//     implement method for pay cash case and method to pay by wallet case by pass order parmter in two case

    //  pay cash
    //  the provider receive the full amount of order in cash case (admin eraning + provider earning)
    // the provider will be become  indebted to the admin for the admin earning amount

    /**
     * @throws ExceptionInterface
     */
    const ADMIN_PERCENTAGE = 0.20;
    const PROVIDER_PERCENTAGE = 0.80;
    const ADMIN_PREVIEW_PERCENTAGE = 0.5;
    const PROVIDER_PREVIEW_PERCENTAGE = 0.5;
    const PREVIEW_COST = 50;

//    protected OrderRepository $orderRepository;
//
//    public function __construct(OrderRepository $orderRepository)
//    {
//        $this->orderRepository = $orderRepository;
//    }

    /**
     * @throws ExceptionInterface
     */
    public function payCash($order): array
    {
        list($totalAmount, $adminEarning, $providerEarning, $deductedAmount) = $this->calculateEarnings($order);

        $providerWallet = $this->getWallet($order->provider);
        $adminWallet = $this->getAdminWallet();

        DB::transaction(function () use ($providerWallet, $adminWallet, $totalAmount, $providerEarning, $deductedAmount, $order) {
            $this->forceWithdraw($providerWallet, $deductedAmount, ['description' => 'cash payment order #' . $order->id]);
            $this->deposit($adminWallet, $deductedAmount, ['description' => 'cash payment order #' . $order->id]);
//            (new OrderRepository())->updateOrderToDone($order);
        });

        return ['message' => 'Payment successful','status' => true];
    }

    /**
     */
    public function payByWallet($order): array
    {

        list($totalAmount, $adminEarning, $providerEarning, $deductedAmount) = $this->calculateEarnings($order);

        if($this->checkBalance($order->user, $totalAmount)){
            return ['message' => 'Insufficient funds','status' => false];
        }

        $userWallet = $this->getWallet($order->user);
        $providerWallet = $this->getWallet($order->provider);
        $adminWallet = $this->getAdminWallet();

        DB::transaction(function () use ($userWallet, $providerWallet, $adminWallet, $totalAmount, $providerEarning, $deductedAmount, $order) {
            $this->forceWithdraw($userWallet, $totalAmount, ['description' => 'payment order #' . $order->id]);
            $this->deposit($providerWallet, $providerEarning, ['description' => 'payment order #' . $order->id]);
            $this->deposit($adminWallet, $deductedAmount, ['description' => 'payment order #' . $order->id]);

//            (new OrderRepository())->updateOrderToDone($order);
        });

        return ['message' => 'Payment successful','status' => true];
    }

    private function calculateEarnings($order): array
    {
        if($order->isPreview()){
            return $this->calculatePreviewEarnings($order);
        } else {
            return $this->calculateOrderEarnings($order);
        }
    }

    private function calculatePreviewEarnings($order): array
    {
        $totalAmount = self::PREVIEW_COST;
        $adminEarning = $totalAmount * self::ADMIN_PREVIEW_PERCENTAGE;
        $providerEarning = $totalAmount * self::PROVIDER_PREVIEW_PERCENTAGE;
        $deductedAmount = $totalAmount - $providerEarning;

        return [$totalAmount, $adminEarning, $providerEarning, $deductedAmount];
    }

    private function calculateOrderEarnings($order): array
    {
        $totalAmount = $order->price;
        $adminEarning = $totalAmount * self::ADMIN_PERCENTAGE;
        $providerEarning = $totalAmount * self::PROVIDER_PERCENTAGE;
        $deductedAmount = $totalAmount - $providerEarning;

        return [$totalAmount, $adminEarning, $providerEarning, $deductedAmount];
    }

    public function checkBalance($wallet, $amount): bool
    {
        return $this->getBalance($wallet) >= $amount;
    }

    public function getAdminWallet(): ?Wallet
    {
        $wallet = Wallet::where('holder_type', 'App\Models\Admin')->first();
        if (!$wallet) {
            return $this->createAdminWallet();
        }
        return $wallet;
    }

    public function createAdminWallet(): Wallet
    {
        $admin = Admin::first();
        return $admin->wallet;
//        return $admin->createWallet(['name' => 'Admin Wallet']);
    }

    public function getWallet($user): Wallet
    {
        return $user->wallet;
    }

    public function getBalance($user): string
    {
        return $this->getWallet($user)->balance;
    }

    public function getSimpleLatestPaginatedTransactions($user, $perPage = 10): \Illuminate\Contracts\Pagination\Paginator
    {
        return $user->transactions()->latest()->simplePaginate($perPage);
    }
    /**
     * @throws ExceptionInterface
     */
    public function deposit(WalletInterface $wallet, $amount, array $meta = null): Transaction
    {
        return $wallet->deposit($amount, $meta); // Transaction::class
    }

    /**
     * @throws ExceptionInterface
     */
    public function withdraw(WalletInterface $wallet, $amount, array $meta = null): Transaction
    {
        return $wallet->withdraw($amount, $meta); // Transaction::class
    }

    /**
     * @throws ExceptionInterface
     */
    public function forceWithdraw(WalletInterface $wallet, $amount, array $meta = null): Transaction
    {
        return $wallet->forceWithdraw($amount, $meta);
    }

    /**
     * @throws ExceptionInterface
     */
    public function transfer(WalletInterface $from, WalletInterface $to, $amount): \Bavix\Wallet\Models\Transfer
    {
        return $from->transfer($to, $amount); // Transaction::class
    }
}
