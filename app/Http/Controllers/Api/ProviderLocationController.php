<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\ProviderLocation;
use Illuminate\Http\Request;

class ProviderLocationController extends Controller
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
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $providerId = auth()->id();

        // Update or create the location for the provider
        ProviderLocation::updateOrCreate(
            ['provider_id' => $providerId],  // Find by provider_id
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'tracked_at' => now(),
            ]
        );
        return $this->respondSuccess('Location updated successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProviderLocation $providerLocation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProviderLocation $providerLocation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProviderLocation $providerLocation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProviderLocation $providerLocation)
    {
        //
    }
}
