<?php

namespace App\Http\Controllers;

use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\AccountDeletionRequest;
use App\Services\AccountDeletionService;
use Illuminate\Http\Request;

class AccountDeletionRequestController extends Controller
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
    public function show(AccountDeletionRequest $accountDeletionRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountDeletionRequest $accountDeletionRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountDeletionRequest $accountDeletionRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        AccountDeletionService::requestDeletion();
        return $this->respondSuccess('');
    }
}
