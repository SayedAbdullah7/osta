<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderMetric;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MetricUpdateService
{
    /**
     * Update metrics when an order is completed (incremental update).
     * Increments completed_orders and optionally updates average_rating if rating is provided.
     *
     * @param Provider $provider
     * @param float|null $rating Optional rating for the order (must be between 0 and 5)
     * @throws \InvalidArgumentException If rating is invalid
     */
    public function updateOrderMetrics(Provider $provider, float $rating = null)
    {
        if ($rating !== null && ($rating < 0 || $rating > 5)) {
            throw new \InvalidArgumentException('Rating must be between 0 and 5');
        }

        if (!$provider->exists) {
            throw new \InvalidArgumentException('Provider must exist');
        }

        try {
            $currentMonth = now()->startOfMonth();

            ProviderMetric::updateOrCreate(
                ['provider_id' => $provider->id, 'month' => $currentMonth],
                $this->calculateUpdatedMetrics($provider, $currentMonth, $rating, true)
            );
        } catch (\Exception $e) {
            Log::error('Failed to update order metrics', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update only the rating metric when a review is added (incremental update).
     * Does NOT increment completed_orders (order was already counted).
     *
     * @param Provider $provider
     * @param float $rating The new rating value (must be between 0 and 5)
     * @throws \InvalidArgumentException If rating is invalid
     */
    public function updateRatingMetrics(Provider $provider, float $rating)
    {
        if ($rating < 0 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 0 and 5');
        }

        if (!$provider->exists) {
            throw new \InvalidArgumentException('Provider must exist');
        }

        try {
            $currentMonth = now()->startOfMonth();

            ProviderMetric::updateOrCreate(
                ['provider_id' => $provider->id, 'month' => $currentMonth],
                $this->calculateUpdatedMetrics($provider, $currentMonth, $rating, false)
            );
        } catch (\Exception $e) {
            Log::error('Failed to update rating metrics', [
                'provider_id' => $provider->id,
                'rating' => $rating,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate updated metrics incrementally (avoiding full recalculation for performance).
     *
     * @param Provider $provider
     * @param Carbon $month
     * @param float|null $rating
     * @param bool $incrementOrders Whether to increment completed_orders (true for new order, false for rating update only)
     * @return array
     */
    protected function calculateUpdatedMetrics(Provider $provider, Carbon $month, ?float $rating, bool $incrementOrders = true): array
    {
        $metrics = ProviderMetric::firstOrNew([
            'provider_id' => $provider->id,
            'month' => $month
        ]);

        $currentOrderCount = (int)($metrics->completed_orders ?? 0);
        $newCompletedOrders = $incrementOrders
            ? ($currentOrderCount + 1)
            : $currentOrderCount;

        // Calculate new average rating incrementally
        // Formula: new_avg = (old_avg * old_count + new_rating) / new_count
        // When incrementOrders=false (rating update only), we use currentOrderCount + 1
        // because the rating is for an order that was already counted
        $newRating = $rating !== null
            ? (($metrics->average_rating * $currentOrderCount) + $rating) / max($incrementOrders ? $newCompletedOrders : ($currentOrderCount + 1), 1)
            : $metrics->average_rating;

        return [
            'completed_orders' => $newCompletedOrders,
            'average_rating' => $newRating,
            // Update other metrics as needed
        ];
    }
}
