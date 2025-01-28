<?php

namespace App\Http\Controllers;

use App\DataTables\CategoryDataTable;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CategoryDataTable $dataTable)
    {
//        return Category::with('section')->get();
        return $dataTable->render('pages.category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $route = route('category.store');
        return view('pages.category.form',['route'=>$route]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $category = new Category();
        $category->name = $request->name;
        $section->section_id = $request->section_id;
        $category->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $route = route('category.update',$category->id);
        return view('pages.category.form',['model'=>$category,'route'=>$route]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->name = $request->name;
        $category->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
