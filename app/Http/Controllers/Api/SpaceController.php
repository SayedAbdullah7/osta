<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpaceResource;
use App\Http\Resources\SpaceSubServiceResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Space;
use App\Models\SpaceSubService;
use App\Models\SubService;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $service_id = request()->service_id;
        $sub_service_id = request()->sub_service_id;
//         $subService = SubService::find(4);
        $spaceSubServices = SpaceSubService::with('space')->when($sub_service_id, function ($query) use ($sub_service_id) {
            return $query->whereHas('subService', function ($query) use ($sub_service_id) {
                $query->where('id', $sub_service_id);
            });
        })->get();
        return  $this->respondWithResource(SpaceSubServiceResource::collection($spaceSubServices),'');

        return $subService->spaces;
        $spaces = Space::when($service_id, function ($query) use ($service_id) {
            return $query->whereHas('services', function ($query) use ($service_id) {
                $query->where('id', $service_id);
            });
        })->when($sub_service_id, function ($query) use ($sub_service_id) {
            return $query->whereHas('subServices', function ($query) use ($sub_service_id) {
                $query->where('id', $sub_service_id);
            });
        })->get();
        return $this->respondWithResource(SpaceResource::collection($spaces),'');

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
    public function show(Space $space)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Space $space)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Space $space)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Space $space)
    {
        //
    }
}
