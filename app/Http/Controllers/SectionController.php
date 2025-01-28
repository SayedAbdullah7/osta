<?php

namespace App\Http\Controllers;

use App\DataTables\SectionDataTable;
use App\Models\Section;
use App\Http\Requests\StoreSectionRequest;
use App\Http\Requests\UpdateSectionRequest;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SectionDataTable $dataTable)
    {
        return $dataTable->render('pages.section.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $route = route('section.store');
        return view('pages.section.form',['route'=>$route]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSectionRequest $request)
    {
        $section = new Section();
        $section->name = $request->name;
        $section->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section)
    {
        $route = route('section.update',$section->id);
        return view('pages.section.form',['model'=>$section,'route'=>$route]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSectionRequest $request, Section $section)
    {
        $section->name = $request->name;
        $section->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        //
    }
}
