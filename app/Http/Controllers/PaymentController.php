<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\PaymentDataTable;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(PaymentDataTable $dataTable, Request $request, $walletId = null): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle($walletId);
        }

        // Return view with dynamic columns and filters
        return view('pages.transaction.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

}
