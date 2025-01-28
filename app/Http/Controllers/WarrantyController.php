<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\WarrantyDataTable;
use App\Models\Order;
use App\Models\Warranty;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{/**
     * Display a listing of the resource.
     */
    public function index(WarrantyDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.warranty.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('pages.warranty.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the input data
        $request->validate([
//            'name' => 'required|string|max:255|unique:warranties,name',
//            'description' => 'required|string',
            'duration_months' => 'required|integer',
            'percentage_cost' => 'required|numeric|min:0|max:100',
        ]);

        // Create a new warranty instance
        $warranty = new Warranty();

        // Manually assign values to the attributes
//        $warranty->name = $request->input('name');
//        $warranty->description = $request->input('description');
        $warranty->duration_months = $request->input('duration_months');
        $warranty->percentage_cost = $request->input('percentage_cost');
        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->name[$locale])) {
                $warranty->translateOrNew($locale)->name = $request->name[$locale];
            }
        }
        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->description[$locale])) {
                $warranty->translateOrNew($locale)->description = $request->description[$locale];
            }
        }

        // Save the new warranty record to the database
        $warranty->save();

        // Return a response with Arabic message
        return response()->json([
            'status' => true,
            'msg' => 'تم إنشاء الضمان بنجاح!',
        ]);
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
        return view('pages.warranty.form',[
            'model' => $warranty
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Warranty $warranty)
    {
        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255|unique:warranties,name,' . $warranty->id, // Ensure unique name except the current warranty
            'description' => 'required|string',
            'duration_months' => 'required|integer',
            'percentage_cost' => 'required|numeric|min:0|max:100',
        ]);

        // Find the warranty record by ID
//        $warranty = Warranty::findOrFail($id);

        // Check if any orders exist that are linked to this warranty
        $orders = Order::where('warranty_id', $warranty->id)->exists();

        // If there are orders linked to the warranty, prevent the update
        if ($orders) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن تحديث الضمان لأن هناك طلبات مرتبطة به.',
            ]); // Return error if orders are linked
        }



        // Manually assign values to the attributes
//        $warranty->name = $request->input('name');
//        $warranty->description = $request->input('description');
        $warranty->duration_months = $request->input('duration_months');
        $warranty->percentage_cost = $request->input('percentage_cost');
        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->name[$locale])) {
                $warranty->translateOrNew($locale)->name = $request->name[$locale];
            }
        }
        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->description[$locale])) {
                $warranty->translateOrNew($locale)->description = $request->description[$locale];
            }
        }

        // Save the updated warranty record
        $warranty->save();

        // Return a response with Arabic message
        return response()->json([
            'status' => true,
            'msg' => 'تم تحديث الضمان بنجاح!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Warranty $warranty)
    {
        // Find the warranty record by ID
//        $warranty = Warranty::findOrFail($id);

        // Check if any orders are linked to this warranty
        $orders = Order::where('warranty_id', $warranty->id)->exists();

        // If there are orders linked to the warranty, prevent the delete
        if ($orders) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف الضمان لأن هناك طلبات مرتبطة به.',
            ]); // Return error if orders are linked
        }

        // Delete the warranty if no orders are linked
        $warranty->delete();

        // Return a response with Arabic message
        return response()->json([
            'status' => true,
            'msg' => 'تم حذف الضمان بنجاح!',
        ]); // Return success message after deletion
    }
}
