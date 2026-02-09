<?php

namespace App\Http\Controllers;

use App\DataTables\SpaceSubServiceDataTable;
use App\DataTables\SubServiceDataTable;
use App\Http\Requests\dashboard\StoreUpdateSubServiceRequest;
use App\Models\Service;
use App\Models\Space;
use App\Models\SpaceSubService;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SpaceSubServiceDataTable $dataTable)
    {
        return $dataTable->render('pages.space.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.space.form'); // Adjust the path as necessary
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'sub_service_id' => 'required|exists:sub_services,id',
            'max_price' => 'required|numeric',
            // Custom validation to check for duplicates
            'sub_service_id' => 'unique:space_sub_service,sub_service_id,NULL,id,space_id,' . $request->space_id,
        ]);
        // Set each value in separate variables
        $spaceId = $request->input('space_id');
        $subServiceId = $request->input('sub_service_id');
        $maxPrice = $request->input('max_price');
        $description = $request->input('description');

        // Create a new entry
        $spaceSubService = new SpaceSubService();
        $spaceSubService->space_id = $spaceId;
        $spaceSubService->sub_service_id = $subServiceId;
        $spaceSubService->max_price = $maxPrice;
        $spaceSubService->description = $description;
        $spaceSubService->save(); // Save the record


        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Display the specified resource.
     */
    public function show(SpaceSubService $spaceSubService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($space_id, $sub_service_id)
    {
        // Logic to show the edit form
        $spaceSubService = SpaceSubService::where('space_id', $space_id)
            ->where('sub_service_id', $sub_service_id)
            ->firstOrFail();

// Remove the return statement here
        return view('pages.space.form', ['model' => $spaceSubService]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $space_id, $sub_service_id)
    {
        $spaceSubService = SpaceSubService::where('space_id', $space_id)
            ->where('sub_service_id', $sub_service_id)
            ->firstOrFail();

        $request->validate([
            'space_id' => 'required|exists:spaces,id',
            'sub_service_id' => 'required|exists:sub_services,id',
            'max_price' => 'required|numeric',
            // Custom validation to check for duplicates, excluding the current record
//            'sub_service_id' => 'unique:space_sub_service,sub_service_id,' . $spaceSubService->sub_service_id . ',id,space_id,' . $request->space_id,
        ]);


//        if($spaceSubService->type != $request->type || $spaceSubService->service_id != $request->service_id) {
//
//            // Check if there are any orders using this service, ignoring the global scope
//            if ($spaceSubService->orders()->withoutGlobalScope('recentOrders')->exists()) {
//                return response()->json([
//                    'status' => false,
//                    'msg' => 'لا يمكن تعديل هذه الخدمة لأن هناك طلبات مرتبطة بها.'
//                ], 400); // Return a 400 Bad Request status
//            }
//
//            // Check if there are any sub-services associated with this service
//            if ($spaceSubService->spaces()->exists()) {
//                return response()->json([
//                    'status' => false,
//                    'msg' => 'لا يمكن تعديل هذه الخدمة لأن هناك مساحات مرتبطة بها.'
//                ], 400); // Return a 400 Bad Request status
//            }
//        }

        // Set each value in separate variables
        $maxPrice = $request->input('max_price');
        $spaceId = $request->input('space_id'); // Optional: if you allow changing space_id
        $subServiceId = $request->input('sub_service_id'); // Optional: if you allow changing sub_service_id

        // Update the fields
//        $spaceSubService->max_price = $maxPrice;

        // Optionally update space_id and sub_service_id if you allow it
        // Uncomment the lines below if you want to allow changing these values
        // $spaceSubService->space_id = $spaceId;
        // $spaceSubService->sub_service_id = $subServiceId;

//        unset($spaceSubService->id);
//        $spaceSubService->save(); // Save the updated record
        $spaceSubService->update($request->only(['max_price', 'description']));
        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($space_id, $sub_service_id)    {
        // Find the existing record
        $spaceSubService = SpaceSubService::where('space_id', $space_id)
            ->where('sub_service_id', $sub_service_id)
            ->firstOrFail(); // Throws a 404 error if not found

        // Check if there are any orders using this service, ignoring the global scope
        if ($spaceSubService->orders()->withoutGlobalScope('recentOrders')->exists()) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف هذه الخدمة لأن هناك طلبات مرتبطة بها.'
            ], 400); // Return a 400 Bad Request status
        }

        // Check if there are any sub-services associated with this service
        if ($spaceSubService->spaces()->exists()) {
            return response()->json([
                'status' => false,
                'msg' => 'لا يمكن حذف هذه الخدمة لأن هناك مساحات مرتبطة بها.'
            ], 400); // Return a 400 Bad Request status
        }

        $spaceSubService->delete();

        return response()->json(['status' => true, 'msg' => 'تم الحذف بنجاح']);
    }
}
