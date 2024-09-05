<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponseTrait;
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
//    public function store(Request $request)
//    {
//        //
//    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }

    public function store(Request $request)
    {
        $data = $request->validate([
//            'user_id' => 'required|exists:users,id',
            'order_id' => 'required',
//            'provider_id' => 'nullable|exists:providers,id',
            'comment' => 'nullable|string|max:255',
            'rating' => 'required|integer|between:1,5',
        ]);

        $review = $this->reviewService->addReview($data, $request->user()->id);
        return new ReviewResource($review);

    }
    public function getProviderReviews($providerId)
    {
        $reviews = $this->reviewService->getProviderReviews($providerId);
        return $this->respondWithResource(ReviewResource::collection($reviews));
    }

    public function getOrderReviews($orderId)
    {
        $reviews = $this->reviewService->getOrderReviews($orderId);
        return ReviewResource::collection($reviews);
    }
}
