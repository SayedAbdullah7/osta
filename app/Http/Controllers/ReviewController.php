<?php

namespace App\Http\Controllers;

use App\DataTables\ReviewDataTable;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ReviewDataTable $dataTable)
    {
        return $dataTable->render('pages.review.index');
    }
    // Show the form to create a new discount code
    public function create()
    {
        return view('pages.review.form');
    }

    // Store a newly created discount code
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:discount_codes|max:255',
            'value' => 'required|numeric|min:0.01',
            'type' => 'required|in:fixed,percentage',
            'is_active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
            'used_by' => 'nullable|exists:reviews,id',
            'used_at' => 'nullable|date',
        ]);

//        $validated['code'] = $this->genereviewUniqueCode(); // Genereview unique code
//        Review::create($validated);

        // Instantiate a new Review model
        $model = new Review();

        // Set the attributes one by one
        $model->code = $validated['code'];
        $model->value = $validated['value'];
        $model->type = $validated['type'];
        $model->is_active = $validated['is_active'] ?? false; // Default to true if not provided
        $model->expires_at = $validated['expires_at'] ?? null;

        $model->save();

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
//        return redirect()->route('discount-code.index')->with('success', 'Discount code created successfully.');
    }

    // Show the form to edit the discount code
    public function edit($id)
    {
        $model  = Review::findorFail($id);
        return view('pages.review.form', ['model' => $model]);
    }

    // Update the discount code
    public function update(Request $request, $id)
    {
        $model  = Review::findorFail($id);
        $model->is_approved = $request->is_approved;
        $model->save();
        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    // Delete the discount code
    public function destroy(Review $model)
    {
        $model->delete();
        return response()->json(['status' => true, 'msg' => 'تم الحذف بنجاح']);
    }
}
