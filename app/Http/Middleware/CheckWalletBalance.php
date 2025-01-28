<?php

namespace App\Http\Middleware;

use App\Exceptions\InsufficientBalanceException;
use App\Models\Setting;
use App\Services\WalletService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckWalletBalance
{
    protected $walletService;

    public function __construct()
//    public function __construct(WalletService $walletService)
    {
//        $this->walletService = $walletService;
        $walletService = app(WalletService::class);
        $this->walletService = $walletService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @throws InsufficientBalanceException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('provider')->check()) {
            $user = Auth::guard('provider')->user();

            $minBalance = Setting::getSetting('min_wallet_balance', 0);
            // Fetch the wallet balance (use a service for better separation of concerns)
//            $walletBalance = $this->walletService->getWalletBalance($user);
            $walletBalance = 0;
            // Check if the balance is sufficient
//            if ($walletBalance < $minBalance) {
//                throw new InsufficientBalanceException($minBalance);
//            }
        }

        return $next($request);
    }
}
