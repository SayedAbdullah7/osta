<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TransactionResource;
use App\Http\Resources\WalletResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    use ApiResponseTrait;
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * @return JsonResponse
     */
    public function transactions(): JsonResponse
    {
        $user = Auth::user();
        $page = request()->get('page', 1);
        $perPage = request()->get('per_page', 10);
//        $transactions = $this->walletService->getSimpleLatestPaginatedTransactions($user);
        $transactions = $this->walletService->getLatestPaginatedTransactions($user, $page, $perPage);
        return $this->respondWithResourceCollection(TransactionResource::collection($transactions));
    }

    public function show(): JsonResponse
    {
        $user = Auth::user();
        $wallet  = $this->walletService->getWallet($user);
        $parPage = request()->get('per_page', 5);
        $transactions = $this->walletService->getSimpleLatestPaginatedTransactions($user,$parPage);

//        return $this->respondWithResource(new WalletResource($wallet));
        return $this->apiResponse(
            [
                'success' => true,
                'result' => [
                    'wallet' => new WalletResource($wallet),
                    'last_transactions' => TransactionResource::collection($transactions),
                ],
                'message' => ''
            ], 200
        );
    }

    public function deposit(Request $request)
    {
        $wallet = Auth::user()->wallet;
        $transaction = $this->walletService->deposit($wallet, $request->amount);
        return response()->json(['transaction' => $transaction, 'balance' => $this->walletService->getBalance($wallet)]);
    }

    public function withdraw(Request $request)
    {
        $wallet = Auth::user()->wallet;
        $transaction = $this->walletService->withdraw($wallet, $request->amount);
        return response()->json(['transaction' => $transaction, 'balance' => $this->walletService->getBalance($wallet)]);
    }

    public function transfer(Request $request)
    {
        $from = Auth::user()->wallet;
        $to = User::find($request->to_user_id)->wallet; // Assuming the user model has the HasWallet trait
        $transaction = $this->walletService->transfer($from, $to, $request->amount);
        return response()->json(['transaction' => $transaction]);
    }
}
