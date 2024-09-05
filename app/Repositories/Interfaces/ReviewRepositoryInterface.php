<?php

namespace App\Repositories\Interfaces;

use App\Models\Review;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function createReview(array $data): Review;
    public function findByUserIdAndOrderId(int $userId, int $orderId): ?Review;
    public function findByUserIdAndProviderId(int $userId, int $providerId): ?Review;
    public function getReviewsByProviderId(int $providerId): Collection;
    public function getReviewsByOrderId(int $orderId): array;
}
