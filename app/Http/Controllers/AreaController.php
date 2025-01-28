<?php

namespace App\Http\Controllers;

use App\DataTables\ServiceDataTable;
use App\Models\Area;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\DataTables\AreasDataTable;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ServiceDataTable $dataTable)
    {
        return $dataTable->render('pages.area.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $route = route('area.store');
        return view('pages.area.form',['route'=>$route]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAreaRequest $request)
    {
        $area = new Area();
        $area->name = $request->name;
        $area->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Area $area)
    {
        $route = route('area.update',$area->id);
        return view('pages.area.form',['model'=>$area,'route'=>$route]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAreaRequest $request, Area $area)
    {
        $area->name = $request->name;
        $area->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        //
    }
}
