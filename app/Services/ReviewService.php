<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\Provider;
use App\Models\ReviewStatistic;
use App\Models\UserAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewService
{

    public function skip($order_id)
    {
        $auth = request()->user();
        $is_user= $auth instanceof User;
        $is_provider= $auth instanceof Provider;
        if ($is_user) {
            Log::channel('test')->info('skip user', ['user_id' => $auth->id, 'order_id' => $order_id]);
            UserAction::hideRateLastOrderForUser($auth->id, $order_id);
        }
        if ($is_provider) {
            UserAction::hideRateLastOrderForProvider($auth->id, $order_id);
        }
    }
    /**
     * Create a new review for the given order.
     *
     * @param int $orderId
     * @param string $reviewableType ('user' or 'provider')
     * @param int $rating
     * @param string|null $comment
     * @return Review|null
     */
    public function addReview(int $orderId, string $reviewableType, int $rating, ?string $comment = null): ?Review
    {
        $order = Order::findOrFail($orderId);

        // Ensure only the user or provider in the order can review
        if (!$this->canReview($order, $reviewableType)) {
            return null;
        }
        $review = new Review([
            'rating' => $rating,
            'comment' => $comment,
            'order_id' => $orderId,
        ]);

        // Set the review relationships based on the reviewable type
        if ($reviewableType === 'user') {
            $review->reviewable()->associate(Auth::user());
            $review->reviewed()->associate($order->provider);
        } else {
            $review->reviewable()->associate($order->provider);
            $review->reviewed()->associate($order->user);
        }

        DB::transaction(function () use ($review, $orderId) {
            $review->save();
            $this->updateReviewStatistics($review->reviewed, $review->rating);
            $this->skip($orderId);
        });

        return $review;

    }

    /**
     * Check if the current user or provider is eligible to review based on the order,
     * and ensure they haven't already reviewed the order.
     *
     * @param Order $order
     * @param string $reviewableType
     * @return bool
     */
    protected function canReview(Order $order, string $reviewableType): bool
    {
        $reviewer = Auth::user();

        // Check if a review by the user or provider already exists for this order
        $existingReview = Review::where('order_id', $order->id)
            ->where('reviewable_type', $reviewableType === 'user' ? User::class : Provider::class)
            ->where('reviewable_id', $reviewer->id)
            ->exists();

        if ($existingReview) {
            return false;
        }

        // Confirm that the authenticated user matches either the user or provider for the order
        if ($reviewableType === 'user' && $reviewer->id === $order->user_id) {
            return true;
        } elseif ($reviewableType === 'provider' && $reviewer->id === $order->provider_id) {
            return true;
        }

        return false;
    }

    /**
     * Update the review statistics for the reviewed entity (user or provider).
     *
     * @param $reviewed
     * @param int $newRating
     * @return void
     */
    protected function updateReviewStatistics($reviewed, int $newRating): void
    {
        $reviewStatistics = $reviewed->reviewStatistics()->firstOrCreate([
            'reviewable_type' => get_class($reviewed),
            'reviewable_id' => $reviewed->id,
        ]);
        // Update statistics
        $reviewStatistics->total_reviews += 1;
//        $reviewStatistics->completed_orders += 1;
        $reviewStatistics->average_rating = (($reviewStatistics->average_rating * ($reviewStatistics->total_reviews - 1)) + $newRating) / $reviewStatistics->total_reviews;
        $reviewStatistics->save();
    }

    /**
     * Get all reviews for a specific user by their ID.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReviewsForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Review::with('reviewable')->where('reviewed_type', User::class)
            ->where('reviewed_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get all reviews for a specific provider by their ID.
     *
     * @param int $providerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReviewsForProvider(int $providerId): \Illuminate\Database\Eloquent\Collection
    {
        return Review::with('reviewable')->where('reviewed_type', Provider::class)
            ->where('reviewed_id', $providerId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get all reviews for a specific provider by their ID.
     *
     * @param int $providerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMyReviews(): \Illuminate\Database\Eloquent\Collection
    {
        $auh = request()->user();
        return Review::with('reviewable')->approved()->where('reviewed_type', get_class($auh))
            ->where('reviewed_id', $auh->id)
            ->orderBy('id', 'desc')
            ->get();
    }
}
