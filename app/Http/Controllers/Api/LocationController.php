<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $locations = $user->locations()->get();
        return $this->respondWithResource(LocationResource::collection($locations), '');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate incoming request data
        $validatedData = $request->validate([
            'id' => 'nullable|integer|exists:locations,id',
            'name' => ['required', 'string', 'max:255'],
//            'street' => 'required|string|max:255',
//            'apartment_number' => 'required|string|max:255',
//            'floor_number' => 'required|string|max:255',
            'latitude' => 'required|max:255',
            'longitude' => 'required|max:255',
            'desc' => 'max:255',
//            'city_id' => [
//                'required',
//                Rule::exists('cities', 'id')->where(function ($query) use ($request,$user) {
//                    $query->where('country_id', $user->country_id);
//                }),
//            ],
        ]);

        if (isset($validatedData['id']) && $validatedData['id']) {
            // Update existing location by ID only
            $location = $user->locations()->find($validatedData['id']);
            if (!$location) {
                return $this->respondNotFound('Location not found');
            }

            $location->name = $validatedData['name'];
            $location->latitude = $validatedData['latitude'];
            $location->longitude = $validatedData['longitude'];
            $location->desc = $validatedData['desc'] ?? null;
        } else {
            // Create a new location (allow multiple locations with same name)
            $location = new Location();
            $location->user_id = $user->id;
            $location->name = $validatedData['name'];
            $location->latitude = $validatedData['latitude'];
            $location->longitude = $validatedData['longitude'];
            $location->desc = $validatedData['desc'] ?? null;
        }


        // Save the location
        $location->save();
//        if ($location->isDefault()){
//            $user->locations()->default()->where('id','!=',$location->id)->update(['is_default'=>0]);
//        }
//        $locations = $user->locations()->with('city')->get();
        $locations = $user->locations()->get();

        return $this->respondWithResource(LocationResource::collection($locations), 'location created successfully');
//        return $this->respondWithResource(new LocationResource($location),'location created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($locationId)
    {
        $user = auth()->user();
        $location = $user->locations()->find($locationId);
        if (!$location) {
            return $this->respondNotFound('Location not found');
        }
        $location->delete();
            $locations = $user->locations()->get();
        return $this->respondWithResource(LocationResource::collection($locations), 'location deleted successfully');
    }
}
