<?php

namespace App\Http\Controllers;

use App\Models\ProviderStatistic;
use Illuminate\Http\Request;
use App\Services\ProviderStatisticService as ProviderStatisticsService;

class ProviderStatisticController extends Controller
{
    protected $providerStatisticsService;

    public function __construct(ProviderStatisticsService $providerStatisticsService)
    {
        $this->providerStatisticsService = $providerStatisticsService;
    }

    public function recalculateProviderLevel($providerId)
    {
        $this->providerStatisticsService->recalculateProviderLevel($providerId);
        return response()->json(['message' => 'Provider level recalculated successfully.']);
    }

    public function recalculateProviderOrdersDoneCount($providerId)
    {
        $this->providerStatisticsService->recalculateProviderOrdersDoneCount($providerId);
        return response()->json(['message' => 'Provider orders done count recalculated successfully.']);
    }
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
    public function show(ProviderStatistic $providerStatistic)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProviderStatistic $providerStatistic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProviderStatistic $providerStatistic)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProviderStatistic $providerStatistic)
    {
        //
    }
}
