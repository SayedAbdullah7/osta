<?php

namespace App\Services;

use App\Models\ProviderReviewStatistics;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryInterface;
//use App\Exceptions\OrderNotOwnedByUserException;
//use App\Exceptions\ReviewAlreadyExistsException;
//use App\DTOs\ReviewData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    protected $reviewRepository;
    protected $orderRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository, OrderRepository $orderRepository)
    {
        $this->reviewRepository = $reviewRepository;
        $this->orderRepository = $orderRepository;
    }

    public function addReview($reviewData,$userId)
    {

//        if (empty($reviewData->order_Id)) {
//            throw new \InvalidArgumentException('An order ID  is required.');
////            throw ValidationException::withMessages([
////                'order_id' => 'An order ID or provider ID is required.',
////                'provider_id' => 'An order ID or provider ID is required.',
////            ]);
//        }
//        if ($reviewData->order_Id) {
            $order = $this->orderRepository->findOrderByIdAndUserId($reviewData['order_id'], $userId);

//            if (!$order) {
//                throw new OrderNotOwnedByUserException();
//            }
            if (!$order) {
                throw ValidationException::withMessages([
                    'order_id' => 'The specified order does not belong to the authenticated user.',
                ]);
            }
        if (!$order->isDone()) {
            throw ValidationException::withMessages([
                'order_id' => 'The specified order is not done.',
            ]);
        }
            $orderId = $order->id;
            $providerId = $order->provider_id;

            $existingReview = $this->reviewRepository->findByUserIdAndOrderId($userId, $orderId);

//            if ($existingReview) {
//                throw new ReviewAlreadyExistsException();
//            }
            if ($existingReview) {
                throw ValidationException::withMessages([
                    'review' => 'You have already reviewed this order.',
                ]);
            }
//        }



//        return $this->reviewRepository->createReview([
//            'user_id' => $reviewData->userId,
//            'order_id' => $reviewData->orderId,
//            'provider_id' => $reviewData->providerId,
//            'comment' => $reviewData->comment,
//            'rating' => $reviewData->rating,
//        ]);
//        return $reviewData;
        $reviewData = [
            'user_id' => $userId,
            'order_id' => $orderId,
            'provider_id' => $providerId,
            'comment' => $reviewData['comment']??null,
            'rating' => $reviewData['rating'],
        ];
        // Create the review
        $review = $this->reviewRepository->createReview($reviewData);

        // Update provider review statistics
        $this->updateProviderReviewStatistics($providerId);

        return $review;

    }

    public function getProviderReviews(int $providerId): \Illuminate\Support\Collection
    {
        return $this->reviewRepository->getReviewsByProviderId($providerId);
    }

    public function getOrderReviews(int $orderId): array
    {
        return $this->reviewRepository->getReviewsByOrderId($orderId);
    }

    protected function updateProviderReviewStatistics(int $providerId)
    {
        $reviews = $this->reviewRepository->getReviewsByProviderId($providerId);

        $totalReviews = count($reviews);
        $averageRating = collect($reviews)->avg('rating');

        ProviderReviewStatistics::updateOrCreate(
            ['provider_id' => $providerId],
            [
                'total_reviews' => $totalReviews,
                'average_rating' => $averageRating,
            ]
        );
    }

}
