<?php

namespace App\Http\Controllers;

use App\DataTables\DiscountCodeDataTable;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscountCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(DiscountCodeDataTable $dataTable)
    {
        return $dataTable->render('pages.discount-code.index');
    }
    // Show the form to create a new discount code
    public function create()
    {
        return view('pages.discount-code.form',['code'=>$this->generateUniqueCode()]);
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
            'used_by' => 'nullable|exists:users,id',
            'used_at' => 'nullable|date',
        ]);

//        $validated['code'] = $this->generateUniqueCode(); // Generate unique code
//        DiscountCode::create($validated);

        // Instantiate a new DiscountCode model
        $discountCode = new DiscountCode();

        // Set the attributes one by one
        $discountCode->code = $validated['code'];
        $discountCode->value = $validated['value'];
        $discountCode->type = $validated['type'];
        $discountCode->is_active = $validated['is_active'] ?? false; // Default to true if not provided
        $discountCode->expires_at = $validated['expires_at'] ?? null;

        $discountCode->save();

        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
//        return redirect()->route('discount-code.index')->with('success', 'Discount code created successfully.');
    }

    // Show the form to edit the discount code
    public function edit(DiscountCode $discountCode)
    {
        return view('pages.discount-code.form', ['model' => $discountCode]);
    }

    // Update the discount code
    public function update(Request $request, DiscountCode $discountCode)
    {
        $validated = $request->validate([
            'code' => 'required|max:255|unique:discount_codes,code,' . $discountCode->id,
            'value' => 'required|numeric|min:0.01',
            'type' => 'required|in:fixed,percentage',
            'is_active' => 'required|boolean',
            'expires_at' => 'nullable|date',
            'used_by' => 'nullable|exists:users,id',
            'used_at' => 'nullable|date',
        ]);

//        $discountCode->update($validated);

        // Set the attributes one by one
        $discountCode->code = $validated['code'];
        $discountCode->value = $validated['value']; // if 'value' is for percentage, change accordingly
        $discountCode->type = $validated['type'];
        $discountCode->is_active = $validated['is_active'];
        $discountCode->expires_at = $validated['expires_at'];

        // Save the updated model
        $discountCode->save();


        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
//        return redirect()->route('discount-code.index')->with('success', 'Discount code updated successfully.');
    }

    // Delete the discount code
    public function destroy(DiscountCode $discountCode)
    {
        $discountCode->delete();
        return response()->json(['status' => true, 'msg' => 'تم الحذف بنجاح']);
        return redirect()->route('discount-code.index')->with('success', 'Discount code deleted successfully.');
    }

    // Generate unique discount code
    private function generateUniqueCode(): string
    {
        do {
            $code = 'CODE-' . strtoupper(Str::random(6)); // Shorter random code
        } while (DiscountCode::where('code', $code)->exists());

        return $code;
    }
}
