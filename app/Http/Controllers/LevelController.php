<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\LevelDataTable;
use App\Models\Level;
use App\Models\Order;
use App\Models\ProviderStatistic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LevelDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.level.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // You can pass necessary data to the view if needed (e.g. levels for next_level_id)
        $levels = Level::all(); // To populate the 'next_level_id' dropdown
        return view('pages.level.form', compact('levels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255|unique:levels,name',
//            'level' => 'required|integer',
            'orders_required' => 'required|integer',
            'is_paid' => 'nullable|boolean',
            'percentage' => 'nullable|integer|min:0|max:100',
//            'next_level_id' => 'nullable|exists:levels,id',
        ]);

        // Create a new level instance
        $level = new Level();

        // Manually assign values to the attributes
        $level->name = $request->input('name');
        $level->level = $request->input('level',0);
        $level->orders_required = $request->input('orders_required');
//        $level->is_paid = $request->input('is_paid', 0);
        $level->percentage = $request->input('percentage', 1);
        $level->next_level_id = $request->input('next_level_id');

        // Save the new level record to the database
        $level->save();

        // Return a response
        return response()->json([
            'status' => true,
            'msg' => 'تم إنشاء المستوى بنجاح!',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Level $level)
    {
        return view('pages.level.form',['model' => $level]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Level $level)
    {
        // Validate the input data
        $request->validate([
            'name' => 'required|string|max:255|unique:levels,name,' . $level->id, // Ensure unique name except the current level
//            'level' => 'required|integer',
            'orders_required' => 'required|integer',
//            'is_paid' => 'nullable|boolean',
            'percentage' => 'nullable|integer|min:0|max:100',
//            'next_level_id' => 'nullable|exists:levels,id',
        ]);


        // Manually assign values to the attributes
        $level->name = $request->input('name');
//        $level->level = $request->input('level');
        $level->orders_required = $request->input('orders_required');
//        $level->is_paid = $request->input('is_paid', 0);
        $level->percentage = $request->input('percentage', 1);
//        $level->next_level_id = $request->input('next_level_id');

        // Save the updated level record
        $level->save();

        // Return a response with Arabic message
        return response()->json([
            'status' => true,
            'msg' => 'تم تحديث المستوى بنجاح!',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Level $level)
    {
        // Check if any orders are linked to this level
        $month = Carbon::now()->startOfMonth();
        $statistics = ProviderStatistic::where('month',$month) // Start of the current month
    ->where('level', $level->level)
    ->exists();


        // If there are orders linked to the level, prevent the delete
        if ($statistics) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف المستوى لأن هناك فنيني مرتبطين به مرتبطة به.',
            ]);
        }

        // Delete the level if no orders are linked
        $level->delete();

        // Return a response with Arabic message
        return response()->json([
            'status' => true,
            'msg' => 'تم حذف المستوى بنجاح!',
        ]);
    }
}
