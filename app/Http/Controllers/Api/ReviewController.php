<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Provider;
use App\Models\Review;
use App\Models\User;
use App\Models\UserAction;
use App\Services\OldReviewService;
use App\Services\ReviewService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

    public function skip(Request $request)
    {
        $order_id= $request->order_id;

        $this->reviewService->skip($order_id);
        return $this->respondSuccess('Review skipped successfully');

//        UserAction::getShowRateLastOrderForUser(
    }

    public function store(Request $request)
    {
        $auth = Auth::user();
        $authClass = get_class($auth);
        $reviewable_type = $authClass === User::class ? 'user' : 'provider';
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
//            'reviewable_type' => 'required|in:user,provider',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:255',
        ]);
//            $validated['reviewable_type'],
//        $reviewable_type = 'user';
        try {
            $review = $this->reviewService->addReview(
                (int)$validated['order_id'],
                (string)$reviewable_type,
                (int)$validated['rating'],
                $validated['comment']??null
            );

            if ($review) {
                return $this->respondWithResource(new ReviewResource($review), 'Review added successfully');
//            return response()->json(['message' => 'Review added successfully', 'review' => $review], 201);
            }
            return $this->respondNotFound('you are not allowed to review this order');
        } catch (Exception $e) {
            Log::debug('error in review', [$e]);
            // Handle any exception that occurs during the review process
            return $this->respondNotFound('you are not allowed to review this order..');
        }
//        return response()->json(['message' => 'You are not allowed to review this order'], 403);


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
        $reviews = $this->reviewService->getReviewsForProvider($providerId);
        return $this->respondWithResource(ReviewResource::collection($reviews));
    }

    public function getUserReviews($providerId)
    {
        $reviews = $this->reviewService->getReviewsForUser($providerId);
        return $this->respondWithResource(ReviewResource::collection($reviews));
    }

    public function myReviews()
    {
        $reviews = $this->reviewService->getMyReviews();
        return $this->respondWithResource(ReviewResource::collection($reviews));
    }

    public function getOrderReviews($orderId)
    {
        $reviews = $this->reviewService->getOrderReviews($orderId);
        return ReviewResource::collection($reviews);
    }
}
