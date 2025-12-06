<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderMetric;
use Carbon\Carbon;

class MetricUpdateService
{
    public function updateOrderMetrics(Provider $provider, float $rating = null)
    {
        $currentMonth = now()->startOfMonth();

        ProviderMetric::updateOrCreate(
            ['provider_id' => $provider->id, 'month' => $currentMonth],
            $this->calculateUpdatedMetrics($provider, $currentMonth, $rating)
        );
    }

    protected function calculateUpdatedMetrics(Provider $provider, Carbon $month, ?float $rating): array
    {
        $metrics = ProviderMetric::firstOrNew([
            'provider_id' => $provider->id,
            'month' => $month
        ]);

        $newCompletedOrders = $metrics->completed_orders + 1;

        $newRating = $rating
            ? (($metrics->average_rating * $metrics->completed_orders) + $rating) / $newCompletedOrders
            : $metrics->average_rating;

        return [
            'completed_orders' => $newCompletedOrders,
            'average_rating' => $newRating,
            // Update other metrics as needed
        ];
    }
}
