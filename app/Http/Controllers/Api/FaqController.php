<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqCategoryResource;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\Request;
use App\Http\Traits\Helpers\ApiResponseTrait;

class FaqController extends Controller
{
       use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     * make search by categor and search by question if there search
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id',FaqCategory::first()?->id);

        $faqs = Faq::with('category')
            ->when($search, function ($query) use ($search) {
                return $query->where('question', 'like', '%' . $search . '%');
            })
            ->where('category_id', $categoryId)->get();


        return $this->respondWithResource(FaqResource::collection($faqs), 'FAQs retrieved successfully');
    }

    public function categories(): \Illuminate\Http\JsonResponse
    {
        $categories = FaqCategory::all();
        return $this->respondWithResource(FaqCategoryResource::collection($categories), 'Categories retrieved successfully');
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
    public function show(Faq $faq)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Faq $faq)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {
        //
    }
}
