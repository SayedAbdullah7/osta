<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\WalletDataTable;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(WalletDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.wallet.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Wallet $wallet)
    {
        return view('pages.wallet.form', ['model' => $wallet]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$walletId)
    {
        // Validate the request
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',  // Ensure the amount is a number and not negative
            'operation' => 'required|in:+,-',      // Ensure the operation is either '+' or '-'
            'description' => 'required|string|max:255',
        ]);

        // Find the wallet by ID if it exists, otherwise create a new one
        $wallet = Wallet::findOrFail($walletId);


        // Get the current balance (before the operation)
        $originalBalance = $wallet->balance;

        // Determine the amount and operation
        $amount = $validated['amount'];
        $operation = $validated['operation'];

        // Update the balance using deposit or withdraw methods
        if ($operation === '+') {
            $wallet->deposit($amount, ['description' => $validated['description']]);  // Add to the wallet balance
        } elseif ($operation === '-') {
            $wallet->withdraw($amount, ['description' => $validated['description']]);  // Subtract from the wallet balance
        }

        return response()->json([
            'status' => true,
            'msg' => 'تم تحديث الرصيد بنجاح!',
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wallet $wallet)
    {
        //
    }
}
