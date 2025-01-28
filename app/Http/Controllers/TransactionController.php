<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\TransactionDataTable;
use App\DataTables\Custom\WalletDataTable;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TransactionDataTable $dataTable, Request $request, $walletId = null): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
//        return
        if ($request->ajax()) {
            return $dataTable->handle($walletId);
        }

        // Return view with dynamic columns and filters
        return view('pages.transaction.index', [
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
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
