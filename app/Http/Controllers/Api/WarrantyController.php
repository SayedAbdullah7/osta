<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WarrantyResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warranties = Warranty::all();
//        $warranties = [
//            1 => [
//                'id' => 1,
//                'name' => 'Warranty 1',
//                'description' => 'Warranty 1 description',
//            ],
//            2 => [
//                'id' => 2,
//                'name' => 'Warranty 2',
//                'description' => 'Warranty 2 description',
//            ],
//            3 => [
//                'id' => 3,
//                'name' => 'Warranty 3',
//                'description' => 'Warranty 3 description',
//            ],
//        ];
        return $this->respondWithResource(WarrantyResource::collection($warranties));
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
    public function show(Warranty $warranty)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Warranty $warranty)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warranty $warranty)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warranty $warranty)
    {
        //
    }
}
