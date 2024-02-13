<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ProviderResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\City;
use App\Models\Country;

class CountryController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function country_index()
    {
        $countries = Country::all();
        return $this->respondWithResource(CountryResource::collection($countries), '', 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function city_index()
    {
        $country_id = request()->country_id;
        $cities = City::when($country_id, function ($query, $country_id) {
            $query->where('country_id', $country_id);
        })->get();
        return $this->respondWithResource(CityResource::collection($cities), '', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Country $country)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Country $country)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, Country $country)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country)
    {
        //
    }
}
