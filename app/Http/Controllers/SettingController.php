<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\App\DataTables\Custom\SettingDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.setting.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    public function seed(){
        $setting = new SettingSeeder();
        $setting->run();
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
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        return view('pages.setting.form', ['model' => $setting]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        $setting->value = $request->input('value');
        $setting->save();
        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
