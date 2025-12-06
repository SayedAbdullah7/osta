<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
//    public function index(UserDataTable $dataTable)
//    {
//        return $dataTable->render('pages.user.index');
//    }
    public function index(\App\DataTables\Custom\UserDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.user.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    // Show the form to create a new discount code
    public function create()
    {
        return view('pages.user.form');
    }

    // Store a newly created discount code
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|string|max:15',
            'is_phone_verified' => 'boolean',
            'email' => 'nullable|email',
            'gender' => 'required|boolean',
            'date_of_birth' => 'nullable|date',
            'country_id' => 'required|exists:countries,id',
        ]);

        // Create a new User instance
        $user = new User();

        // Set each attribute one by one
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->is_phone_verified = $request->is_phone_verified ?? 0;
        $user->email = $request->email;
        $user->gender = $request->gender;
        $user->date_of_birth = $request->date_of_birth;
        $user->country_id = $request->country_id;

        // Save the user
        $user->save();

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
//        return redirect()->route('discount-code.index')->with('success', 'Discount code created successfully.');
    }

    // Show the form to edit the discount code
    public function edit( $id)
    {
        $model = User::find($id);
        return view('pages.user.form', ['model' => $model]);
    }

    // Update the discount code
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|string|max:15',
            'is_phone_verified' => 'boolean',
            'email' => 'nullable|email',
            'gender' => 'required|boolean',
            'date_of_birth' => 'nullable|date',
            'country_id' => 'required|exists:countries,id',
        ]);

        // Find the existing user
//        $user = User::findOrFail($id);

        // Update each attribute one by one
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->is_phone_verified = $request->is_phone_verified ?? 0;
        $user->email = $request->email;
        $user->gender = $request->gender;
        $user->date_of_birth = $request->date_of_birth;
        $user->country_id = $request->country_id;

        // Save the updated user
        $user->save();

            return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    // Delete the discount code
    public function destroy(User $model)
    {
        if($model->orders()->count() > 0) {
            return response()->json(['status' => false, 'msg' => ' لا يمكن حذف المستخدم لوجود طلبات خاصة به']);
        }
        $model->delete();
        return response()->json(['status' => true, 'msg' => 'تم الحذف بنجاح']);
    }
}
