<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\SubServiceResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Service;
use App\Models\SubService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function service_index()
    {
        return $this->respondWithResource(ServiceResource::collection(Service::all()),'');
    }
    public function sub_service_index()
    {
        $service_id = request()->service_id;
        $subServices = SubService::when($service_id, function ($query, $service_id) {
            $query->where('service_id', $service_id);
        })->get();

        if(request()->group_by_type){
            $subServices = collect(['new' => collect(), 'fix' => collect()])->merge($subServices->groupBy('type'));
            return $this->respondSuccess($subServices, '', 200);
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
}
