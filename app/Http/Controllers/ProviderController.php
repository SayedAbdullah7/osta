<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\ProviderDataTable;
use App\Models\Provider;
use App\Models\Country;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param ProviderDataTable $dataTable
     * @param Request $request
     * @return JsonResponse|View
     */
    public function index(\App\DataTables\Custom\ProviderDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.provider.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }
//    public function index(ProviderDataTable $dataTable)
//    {
//        return $dataTable->render('pages.provider.index');
//    }

    /**
     * Show the form for creating a new provider.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = Country::all();
        $cities = City::all();
        return view('pages.provider.form', compact('countries', 'cities'));
    }

    /**
     * Store a newly created provider in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
//            'first_name' => 'required|string|max:15',
//            'last_name' => 'required|string|max:15',
            'phone' => 'required|string|max:15|unique:providers,phone',
            'email' => 'nullable|email|unique:providers,email',
            'gender' => 'required|boolean',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        $provider = new Provider();
        $provider->name = $validated['name'];
//        $provider->first_name = $validated['first_name'];
//        $provider->last_name = $validated['last_name'];
        $provider->phone = $validated['phone'];
        $provider->email = $validated['email'] ?? null;
        $provider->gender = $validated['gender'];
        $provider->country_id = $validated['country_id'];
        $provider->city_id = $validated['city_id'];
        $provider->save();

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Show the form for editing the specified provider.
     *
     * @param Provider $provider
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Provider $provider)
    {
        $countries = Country::all();
        $cities = City::all();
        $model = $provider;
        return view('pages.provider.form', compact('model','provider', 'countries', 'cities'));
    }

    /**
     * Update the specified provider in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Provider $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
//            'first_name' => 'required|string|max:15',
//            'last_name' => 'required|string|max:15',
            'phone' => 'required|string|max:15|unique:providers,phone,' . $provider->id,
            'email' => 'nullable|email|unique:providers,email,' . $provider->id,
            'gender' => 'required|boolean',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'is_approved' => 'required|boolean',
        ]);


        $provider->name = $validated['name'];
//        $provider->first_name = $validated['first_name'];
//        $provider->last_name = $validated['last_name'];
        $provider->phone = $validated['phone'];
        $provider->email = $validated['email'] ?? null;
        $provider->gender = $validated['gender'];
        $provider->country_id = $validated['country_id'];
        $provider->city_id = $validated['city_id'];
        $provider->is_approved = $validated['is_approved'];
        $provider->save();

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified provider from storage.
     *
     * @param Provider $provider
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Provider $provider)
    {
        if($provider->orders()->count() > 0) {
            return response()->json(['status' => false, 'msg' => ' لا يمكن حذف الفني لوجود طلبات خاصة به']);
        }
        if ($provider->offers()->count() > 0) {
            return response()->json(['status' => false, 'msg' => ' لا يمكن حذف الفني لوجود عروض خاصة به']);
        }
        $provider->delete();

        return response()->json(['status' => true, 'msg' => 'تم الحذف بنجاح']);
    }
}
