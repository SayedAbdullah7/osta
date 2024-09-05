<?php

namespace App\Repositories;

use App\Models\Review;
use App\Repositories\Interfaces\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function createReview(array $data): Review
    {
        return Review::create($data);
    }

    public function findByUserIdAndOrderId(int $userId, int $orderId): ?Review
    {
        return Review::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->first();
    }

    public function findByUserIdAndProviderId(int $userId, int $providerId): ?Review
    {
        return Review::where('user_id', $userId)
            ->where('provider_id', $providerId)
            ->first();
    }

    public function getReviewsByProviderId(int $providerId): \Illuminate\Support\Collection
    {
        return Review::where('provider_id', $providerId)
            ->get();
    }

    public function getReviewsByOrderId(int $orderId): array
    {
        return Review::where('order_id', $orderId)
            ->get()
            ->toArray();
    }
}
