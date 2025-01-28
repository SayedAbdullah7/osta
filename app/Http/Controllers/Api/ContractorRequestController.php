<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\ContractorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorRequestController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        return $this->respondSuccess('Contractor request created successfully');
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
