<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LevelResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\ProviderStatistic;
use App\Services\LevelService;
use Illuminate\Http\Request;
use App\Services\ProviderStatisticService as ProviderStatisticsService;

class ProviderStatisticController extends Controller
{
    use ApiResponseTrait;
    protected $providerStatisticsService;

    protected $levelService;

    public function __construct(ProviderStatisticsService $providerStatisticsService, LevelService $levelService)
    {
        $this->providerStatisticsService = $providerStatisticsService;
        $this->levelService = $levelService;
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

    public function level_index()
    {
        $levels =$this->levelService->getAllLevels();
//        $currentLevel= $this->providerStatisticsService->getCurrentLevel(auth()->user()->id);
//        $currentLevel = ProviderStatistic::where('provider_id', $providerId)
//            ->whereMonth('month', now()->month)
//            ->whereYear('month', now()->year)
//            ->value('level');
        $authProvider = auth()->user();
        $providerId = $authProvider->id;
      $currentLevel = $this->providerStatisticsService->getProviderStatistics($providerId)?->level;

        $levels = $levels->map(function ($level) use ($currentLevel) {
            $level->is_current_level = $level->level == $currentLevel;
            return $level;
        });
        return $this->respondWithResource(LevelResource::collection($levels));

        return $this->respondWithResource($this->levelService->getAllLevels());
        return response()->json($this->levelService->getAllLevels());
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authProvider = auth()->user();
        $providerId = $authProvider->id;
        return response()->json($this->providerStatisticsService->getProviderStatistics($providerId));
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
