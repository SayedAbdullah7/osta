<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\ContractorRequestDataTable;
use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\ContractorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ContractorRequestDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.contractor-request.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'description' => 'required|string|',
            'space' => 'nullable|string',
        ]);

        $contractorRequest = ContractorRequest::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'description' => $request->description,
            'space' => $request->space,
            'user_id' => Auth::id(),
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
