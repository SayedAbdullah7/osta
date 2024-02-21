<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $locations = $user->locations()->with('city')->get();
        return $this->respondWithResource(LocationResource::collection($locations),'');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'apartment_number' => 'required|string|max:255',
            'floor_number' => 'required|string|max:255',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'desc' => 'required|string|max:255',
            'city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function ($query) use ($request,$user) {
                    $query->where('country_id', $user->country_id);
                }),
            ],
        ]);

        // Create a new location instance
        $location = new Location();

        $location->name = $validatedData['name'];
        $location->street = $validatedData['street'];
        $location->apartment_number = $validatedData['apartment_number'];
        $location->floor_number = $validatedData['floor_number'];
        $location->latitude = $validatedData['latitude'];
        $location->longitude = $validatedData['longitude'];
        $location->desc = $validatedData['desc'];
        $location->is_default = (bool)$request->is_default;
        $location->city_id = $validatedData['city_id'];
        $location->user_id = $user->id;

        // Save the location
        $location->save();
        if ($location->isDefault()){
            $user->locations()->default()->where('id','!=',$location->id)->update(['is_default'=>0]);
        }
        return $this->respondWithResource(new LocationResource($location),'location created successfully');

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
    public function destroy(Location $location)
    {
        //
    }
}
