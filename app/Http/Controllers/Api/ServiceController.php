<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SubServiceResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SubService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function service_index()
    {
//        $response = Cache::rememberForever('services', function () {
//            return Service::all();
//            return $this->respondWithResource(ServiceResource::collection(Service::all()), '');
            return $this->respondWithResource(ServiceResource::collection(Service::whereNot('category',OrderCategoryEnum::Other)->get()), '');

//        });
        return $response;
    }

    public function sub_service_index()
    {
        $service_id = request()->get('service_id');
        $group_by_type = request()->get('group_by_type');
        // Cache key based on service_id and grouping requirement
        $cacheKey = "sub_services_{$service_id}_" . ($group_by_type ? 'grouped' : 'ungrouped');

        // Attempt to retrieve data from cache
//        $response = Cache::remember($cacheKey, 60, function () use ($service_id, $group_by_type) {
            // Fetch the sub-services with the related spaces and apply filtering if service_id is provided
            $service = Service::find($service_id);
            $loadSpaces = false;
            if ($service && in_array($service->category, [OrderCategoryEnum::SpaceBased->value, OrderCategoryEnum::Other->value], true)) {
                $loadSpaces = true;
            }
            $subServices = SubService::with(['spaces' => function ($query) use ($loadSpaces) {
                if ($loadSpaces) {
                    $query->get();
                } else {
//                $query->get();
                    $query->whereRaw('1 = 0');
                }
            }])
                ->when($service_id, fn($query) => $query->where('service_id', $service_id))->when($loadSpaces, fn($query) => $query->has('spaces'))
                ->get();

//        if ($service && in_array($service->category, [OrderCategoryEnum::SpaceBased, OrderCategoryEnum::Other], true)) {
//
//        }

            // If grouping by type is requested, group the sub-services
            if ($group_by_type) {
                $groupedSubServices = $subServices->groupBy('type');

                return $this->apiResponse([
                    'success' => true,
                    'result' => [
                        'new' => SubServiceResource::collection($groupedSubServices->get('new', collect())),
                        'fix' => SubServiceResource::collection($groupedSubServices->get('fix', collect())),
                    ],
                    'message' => 'Sub Services fetched successfully',
                ], 200);
            }
            // Return the sub-services as a resource collection
            return $this->respondWithResource(
                SubServiceResource::collection($subServices),
                'Sub Services fetched successfully',
                200
            );
//        });
        return $response;

        $service_id = request()->service_id;
        $subServices = SubService::with('spaces')->when($service_id, function ($query, $service_id) {
            $query->where('service_id', $service_id);
        })->get();

        if (request()->group_by_type) {
            $subServices = collect(['new' => collect(), 'fix' => collect()])->merge($subServices->groupBy('type'));

            return $this->apiResponse(
                [
                    'success' => true,
                    'result' => $subServices,
                    'message' => 'Sub Services fetched successfully',
                ]
                , 200);
//            return $this->respondSuccess($subServices, '', 200);
        }
//        return $subServices;
        return $this->respondWithResource(SubServiceResource::collection($subServices), '', 200);
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
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }

    public function getSetting()
    {
        $setting = ['preview_order_cost' => (int)Setting::getSetting('preview_cost', WalletService::PREVIEW_COST)];
//        $setting = Setting::getSetting('preview_cost', WalletService::PREVIEW_COST);
        return $this->apiResponse(
            [
                'success' => true,
                'result' => $setting
                ,
                'message' => 'Setting fetched successfully',
            ]
        );
//        return $this->respondSuccess($setting, '', 200);
    }
}
