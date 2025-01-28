<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProductDataTable $dataTable)
    {
        return $dataTable->render('pages.product.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $route = route('product.store');
        return view('pages.product.form',['route'=>$route]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = new Product();
        $product->name = $request->name;
        $product->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);

    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $route = route('product.update',$product->id);
        return view('pages.product.form',['model'=>$product,'route'=>$route]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->name = $request->name;
        $product->save();
        return response()->json(['status'=>true,'msg'=>'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
