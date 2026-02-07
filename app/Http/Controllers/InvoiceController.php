<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\InvoiceDataTable;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(InvoiceDataTable $dataTable, Request $request, $orderId = null): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle($orderId);
        }

        // Return view with dynamic columns and filters
        return view('pages.invoice.index', [
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        return view('pages.invoice.form',['model' => $invoice]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:0',  // Ensures that amount is a positive number or zero
                function ($attribute, $value, $fail) use ($invoice) {
                    // Custom validation: Ensure the amount is not greater than the available uppaid
                    if ($value > $invoice->unpaidAmount()) {
                        $fail('The amount cannot be greater than the available balance (uppaid).');
                    }
                },
            ],
//            'description' => 'required|string|max:255',
        ]);
        $walletService = app(WalletService::class);
//        $admin = auth('admin')->user();
        if (!Auth::check()) {
            Auth::loginUsingId(1); // Assuming admin ID 1 is a valid admin
        }
        $admin = auth()->user();
        $respone = $walletService->payInvoiceByAdminWallet($invoice, $admin->id, $validated['amount']);

        if ($respone['status']) {
            return response()->json([
                'status' => true,
                'msg' => 'تم الدفع بنجاح!',
            ]);
        }else{
            return response()->json([
                'status' => false,
                'msg' =>$respone['message']
            ]);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
