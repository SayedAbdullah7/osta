<?php

namespace App\Http\Controllers;

use App\DataTables\ServiceDataTable;
use App\Events\ServiceCreated;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\HandlesImageUpload;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    use HandlesImageUpload;
    /**
     * Display a listing of the resource.
     */
    public function index(ServiceDataTable $dataTable)
    {

        return $dataTable->render('pages.service.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $route = route('service.store');
        return view('pages.service.form',['route'=>$route]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request)
    {

        // Create a new service instance
        $service = new Service();
//        $service->name = $request->name;
        $service->category = $request->category;

        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->name[$locale])) {
                $service->translateOrNew($locale)->name = $request->name[$locale];
            }
        }

        // Save the service data
        $service->save();

        $imageUploaded = $this->handleImageUpload($request, $service);
        if (!$imageUploaded) {
            return response()->json(['status' => false, 'msg' => 'الصورة غير موجودة في المسار المحدد.']);
        }
        event(new ServiceCreated($service));

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
//        return view('pages.service.show',compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $route = route('service.update',$service->id);
        return view('pages.service.form',['model'=>$service,'route'=>$route]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service)
    {
        if($service->category != $request->category) {
            // Check if there are any orders using this service, ignoring the global scope
            if ($service->orders()->withoutGlobalScope('recentOrders')->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'لا يمكن تعديل هذه الخدمة لأن هناك طلبات مرتبطة بها.'
                ], 400); // Return a 400 Bad Request status
            }

            // Check if there are any sub-services associated with this service
            if ($service->subServices()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'لا يمكن تعديل هذه الخدمة لأن هناك خدمات فرعية مرتبطة بها.'
                ], 400); // Return a 400 Bad Request status
            }
        }
        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->name[$locale])) {
                $service->translateOrNew($locale)->name = $request->name[$locale];
            }
        }
//        $service->name = $request->name;
        $service->category = $request->category;

        // Save the service data
        $service->save();

        $this->handleImageUpload($request, $service);

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        // Check if there are any orders using this service, ignoring the global scope
        if ($service->orders()->withoutGlobalScope('recentOrders')->exists()) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف هذه الخدمة لأن هناك طلبات مرتبطة بها.'
            ], 400); // Return a 400 Bad Request status
        }

        // Check if there are any sub-services associated with this service
        if ($service->subServices()->exists()) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف هذه الخدمة لأن هناك خدمات فرعية مرتبطة بها.'
            ], 400); // Return a 400 Bad Request status
        }


        // Delete the service
        $service->delete();

        return response()->json([
            'status' => true,
            'msg' => 'تم الحذف بنجاح'
        ]);
    }

    public function uploadImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $file = $request->file('image');
        $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads'), $filename);

        return response()->json(['filename' => $filename]);
    }


}

