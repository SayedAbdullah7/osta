<?php

namespace App\Http\Controllers;

use App\DataTables\ServiceDataTable;
use App\DataTables\SubServiceDataTable;
use App\Http\Requests\dashboard\StoreUpdateSubServiceRequest;
use App\Models\Service;
use App\Models\SubService;
use Illuminate\Http\Request;

class SubServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SubServiceDataTable $dataTable)
    {
        return $dataTable->render('pages.sub-service.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.sub-service.form',['services' => Service::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUpdateSubServiceRequest $request)
    {
        // Create a new SubService instance
        $subService = new SubService();

        // Set each attribute individually
//        $subService->name = $request->input('name');
        $subService->max_price = $request->input('max_price');
        $subService->type = $request->input('type');
        $subService->service_id = $request->input('service_id');
        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->name[$locale])) {
                $subService->translateOrNew($locale)->name = $request->name[$locale];
            }
        }
        // Save the model
        $subService->save();

        // Handle image upload
        $imageUploaded = $this->handleImageUpload($request, $subService);

        if (!$imageUploaded) {
            return response()->json(['status' => false, 'msg' => 'الصورة غير موجودة في المسار المحدد.']);
        }

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

        /**
     * Display the specified resource.
     */
    public function show(SubService $subService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubService $subService)
    {
        return view('pages.sub-service.form',['model' => $subService,'services' => Service::all()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUpdateSubServiceRequest $request, SubService $subService)
    {
        if($subService->type != $request->type || $subService->service_id != $request->service_id) {

            // Check if there are any orders using this service, ignoring the global scope
            if ($subService->orders()->withoutGlobalScope('recentOrders')->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'لا يمكن تعديل هذه الخدمة لأن هناك طلبات مرتبطة بها.'
                ], 400); // Return a 400 Bad Request status
            }

            // Check if there are any sub-services associated with this service
            if ($subService->spaces()->exists()) {
                return response()->json([
                    'status' => false,
                    'msg' => 'لا يمكن تعديل هذه الخدمة لأن هناك مساحات مرتبطة بها.'
                ], 400); // Return a 400 Bad Request status
            }
        }
        // Set each attribute individually
        $subService->name = $request->input('name');
        $subService->max_price = $request->input('max_price');
        $subService->type = $request->input('type');
        $subService->service_id = $request->input('service_id');
        foreach (config('app.locales') as $locale => $language) {
            if (isset($request->name[$locale])) {
                $subService->translateOrNew($locale)->name = $request->name[$locale];
            }
        }
        // Save the model
        $subService->save();

        // Handle image upload
        $this->handleImageUpload($request, $subService);


        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubService $subService)
    {
        // Check if there are any orders using this service, ignoring the global scope
        if ($subService->orders()->withoutGlobalScope('recentOrders')->exists()) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف هذه الخدمة لأن هناك طلبات مرتبطة بها.'
            ], 400); // Return a 400 Bad Request status
        }

        // Check if there are any sub-services associated with this service
        if ($subService->spaces()->exists()) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف هذه الخدمة لأن هناك مساحات مرتبطة بها.'
            ], 400); // Return a 400 Bad Request status
        }

        $subService->delete();

        return response()->json(['status' => true, 'msg' => 'تم الحذف بنجاح']);
    }


    public function handleImageUpload(Request $request, $subService)
    {
        // Check if the 'uploaded_images' field exists and is not empty
        if (!empty($request->uploaded_images) && isset($request->uploaded_images[0])) {
            // Get the first image filename from the uploaded_images array
            $imageName = $request->uploaded_images[0];

            // Define the full path where the image is stored (assuming it's already uploaded in the public 'uploads' folder)
            $pathToMedia = public_path('uploads/' . $imageName);

            // Check if the image file exists at the specified path
            if (file_exists($pathToMedia)) {
                // If the subService is provided, clear the existing image from the media collection (for update)
                if ($subService->hasMedia('default')) {
                    $subService->clearMediaCollection('default'); // Remove the old image
                }

                // Add the new image to the media collection (assuming 'default' is the media collection name)
                $subService->addMedia($pathToMedia)->toMediaCollection('default');

                // Return true if the image is successfully processed
                return true;
            } else {
                // Return false if the image doesn't exist at the specified path
                return false;
            }
        }

        // Return false if no image is provided
        return false;
    }

}
