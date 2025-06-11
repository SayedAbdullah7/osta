<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\WithdrawalRequest;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Exception;

class WithdrawalRequestService
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Provider requests a withdrawal.
     *
     * @param Provider $provider
     * @param float $amount
     * @param string $paymentMethod
     * @param array $paymentDetails
     * @return WithdrawalRequest
     * @throws Exception
     */
    public function requestWithdrawal(Provider $provider, float $amount, string $paymentMethod, array $paymentDetails)
    {
        DB::beginTransaction();
        try {
            // Retrieve provider's wallet via WalletService.
            $wallet = $this->walletService->getWallet($provider);

            // Ensure the provider has enough balance.
            if (!$this->walletService->checkBalance($wallet, $amount)) {
                throw new Exception('Insufficient balance.');
            }

            // Deduct the requested amount using forceWithdraw.
            $this->walletService->forceWithdraw($wallet, $amount, [
                'description' => 'Withdrawal request'
            ]);

            // Deduct the requested amount.
            $wallet->balance -= $amount;
            $wallet->save();

            // Create withdrawal request record.
            $withdrawalRequest = WithdrawalRequest::create([
                'provider_id'     => $provider->id,
                'amount'          => $amount,
                'status'          => 'pending',
                'payment_method'  => $paymentMethod,
                'payment_details' => $paymentDetails,
            ]);

            DB::commit();
            return $withdrawalRequest;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Admin updates withdrawal status (approve/reject).
     *
     * If rejected, funds are returned to the provider's wallet.
     *
     * @param int $withdrawalId
     * @param string $action  ('approved' or 'rejected')
     * @return WithdrawalRequest
     * @throws Exception
     */
    public function updateWithdrawalStatus(int $withdrawalId, string $action): WithdrawalRequest
    {
        if (!in_array($action, ['approved', 'rejected'])) {
            throw new Exception('Invalid action.');
        }

        $withdrawal = WithdrawalRequest::findOrFail($withdrawalId);

        if ($withdrawal->status !== 'pending') {
            throw new Exception('This withdrawal request has already been processed.');
        }

        DB::beginTransaction();
        try {
            $withdrawal->status = $action;
            $withdrawal->save();

            // If rejected, refund the withdrawal amount.
            if ($action === 'rejected') {
                // Retrieve provider's wallet and deposit back the amount.
                $provider = $withdrawal->provider;
                $wallet = $this->walletService->getWallet($provider);
                $this->walletService->deposit($wallet, $withdrawal->amount, [
                    'description' => 'Refund for rejected withdrawal request'
                ]);
            }

            DB::commit();
            return $withdrawal;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
