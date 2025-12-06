<?php

namespace App\Services;

use App\Models\Level;
use App\Models\Provider;
use App\Models\ProviderMetric;
use App\Models\ProviderLevel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LevelEvaluationService
{
    public function evaluateProvider(Provider $provider)
    {
        // new logic to handle grace period for orders
//        echo $provider->getCurrentLevel();
//        return $provider->getCurrentLevel();
        $currentLevel = $provider->getCurrentLevel();
        $providerLevel = $provider->currentProviderLevel;
        $currentMetrics = $this->calculateCurrentMetrics($provider);
        $previousMonthMetrics = $this->getPreviousMonthMetrics($provider);

        // Check if in grace period for orders
        $inGracePeriod = $currentLevel && $this->isInGracePeriod($provider, $currentLevel);

        if ($inGracePeriod) {
            // Combine current and previous month's orders
            $effectiveMetrics = $this->getGracePeriodMetrics(
                $currentMetrics,
                $previousMonthMetrics,
                $currentLevel
            );

            $potentialLevel = $this->findHighestQualifiedLevel($effectiveMetrics);
        } else {
            $potentialLevel = $this->findHighestQualifiedLevel($currentMetrics);
        }
        // end of new logic

        // Rest of your evaluation logic...

//        $currentMetrics = $this->calculateCurrentMetrics($provider);
//        $currentLevel = $provider->currentLevel();
//
//        $potentialLevel = $this->findHighestQualifiedLevel($currentMetrics);
//        echo 'potentialLevel' .$potentialLevel;

        if ($potentialLevel && (!$currentLevel || $potentialLevel->level > $currentLevel->level)) {
            $this->promoteProvider($provider, $potentialLevel);
            return;
        }

        if ($currentLevel && $this->shouldDemoteProvider($provider, $currentLevel, $currentMetrics)) {
            $this->demoteProvider($provider, $currentLevel);
        }
    }

    public function calculateCurrentMetrics(Provider $provider): array
    {
        $currentMonth = now()->startOfMonth();
        $metrics = ProviderMetric::firstOrCreate(
            ['provider_id' => $provider->id, 'month' => $currentMonth],
            $this->calculateFreshMetrics($provider, $currentMonth)
        );

        return [
            'completed_orders' => $metrics->completed_orders,
            'average_rating' => $metrics->average_rating,
            'cancellation_rate' => $metrics->cancellation_rate,
            'completion_rate' => $metrics->completion_rate,
            'repeat_customers' => $metrics->repeat_customers,
            'response_time_avg' => $metrics->response_time_avg
        ];
    }

    protected function calculateFreshMetrics(Provider $provider, Carbon $month): array
    {
        // Implement your actual metric calculations here
        return [
            'completed_orders' => $provider->orders()
                ->whereBetween('updated_at', [$month, $month->copy()->endOfMonth()])
                ->count(),
            'average_rating' => 0,
//            'average_rating' => $provider->orders()
//                    ->whereBetween('updated_at', [$month, $month->copy()->endOfMonth()])
//                    ->avg('rating') ?? 0,
            // Add other metric calculations
        ];
    }

    public function findHighestQualifiedLevel(array $metrics): ?Level
    {
        $levels = Level::active()
            ->orderByDesc('level')
            ->get();

        foreach ($levels as $level) {
            if ($this->meetsRequirements($metrics, $level->requirements)) {
                return $level;
            }
        }
        return null;
    }

    protected function meetsRequirements(array $metrics, array $requirements): bool
    {
        foreach ($requirements['metrics'] ?? [] as $metric => $requirement) {
            if (is_array($requirement)) {
                if (isset($requirement['min']) && $metrics[$metric] < $requirement['min']) {
                    return false;
                }
                if (isset($requirement['max']) && $metrics[$metric] > $requirement['max']) {
                    return false;
                }
            } elseif ($metrics[$metric] < $requirement) {
                return false;
            }
        }
        return true;
    }

    public function promoteProvider(Provider $provider, Level $newLevel)
    {
        DB::transaction(function () use ($provider, $newLevel) {
            if ($provider->currentProviderLevel){
                $provider->levels()->updateExistingPivot(
                    $provider->currentLevel->id,
                    ['is_current' => false]
                );
            }


            $provider->levels()->attach($newLevel->id, [
                'achieved_at' => now(),
                'is_current' => true,
                'valid_until' => $this->calculateLevelExpiry($newLevel)
            ]);

            $this->applyLevelBenefits($provider, $newLevel);
        });
    }

    protected function calculateLevelExpiry(Level $level): ?Carbon
    {
        $duration = $level->requirements['duration'] ?? null;
        return $duration ? now()->add($duration) : null;
    }

    protected function applyLevelBenefits(Provider $provider, Level $level)
    {
        // Implement benefit application logic
        // For example, update provider's commission rate
//        $provider->update([
//            'commission_rate' => $level->benefits['commission_rate'] ?? $provider->commission_rate
//        ]);
    }

    protected function shouldDemoteProvider(Provider $provider, Level $currentLevel, array $metrics): bool
    {
        if (!$currentLevel->requirements['duration']) {
            return false;
        }

        if (now()->lt($currentLevel->pivot->valid_until)) {
            return false;
        }

        $previousLevel = $currentLevel->previousLevel();

        if (!$previousLevel) {
            return false;
        }

        return !$this->meetsRequirements($metrics, $previousLevel->requirements);
    }

    protected function demoteProvider(Provider $provider, Level $currentLevel)
    {
        $previousLevel = $currentLevel->previousLevel();

        DB::transaction(function () use ($provider, $currentLevel, $previousLevel) {
            $provider->levels()->updateExistingPivot(
                $currentLevel->id,
                ['is_current' => false]
            );

            if ($previousLevel) {
                $provider->levels()->attach($previousLevel->id, [
                    'achieved_at' => now(),
                    'is_current' => true,
                    'valid_until' => $this->calculateLevelExpiry($previousLevel)
                ]);
            }
        });
    }

//5. Usage Examples
//When an order is completed:
//php
//
//$provider = Provider::find($providerId);
//$rating = $order->rating;
//
//// Update metrics
//app(MetricUpdateService::class)->updateOrderMetrics($provider, $rating);
//
//// Evaluate level
//app(LevelEvaluationService::class)->evaluateProvider($provider);
//
//Monthly evaluation (cron job):
//php
//
//$providers = Provider::all();
//
//foreach ($providers as $provider) {
//app(LevelEvaluationService::class)->evaluateProvider($provider);
//}
//
//Get provider's current level benefits:
//php
//
//$benefits = $provider->currentLevel()->benefits;


    // new logic to handle grace period for orders
    public function isInGracePeriod(Provider $provider, Level $level): bool
    {
        if (!$level->hasGracePeriod()) return false;

        $graceEnd = $level->getGracePeriodEndDate(
            $provider->getCurrentLevel()->pivot->achieved_at
        );

        return now()->lte($graceEnd);
    }

    protected function getGracePeriodMetrics(
        array $currentMetrics,
        array $previousMetrics,
        Level $level
    ): array {
        if (!$level->grace_period_applies_to_orders_only) {
            return array_merge_recursive($currentMetrics, $previousMetrics);
        }

        // Only combine order counts
        return [
            'completed_orders' =>
                $currentMetrics['completed_orders'] +
                $previousMetrics['completed_orders'],
            // Other metrics remain current
            'average_rating' => $currentMetrics['average_rating'],
            'cancellation_rate' => $currentMetrics['cancellation_rate'],
            // ...
        ];
    }

    protected function getPreviousMonthMetrics(Provider $provider): array
    {
        $lastMonth = now()->subMonth()->startOfMonth();
        $metrics = ProviderMetric::where([
            'provider_id' => $provider->id,
            'month' => $lastMonth
        ])->first();

        return $metrics ? $metrics->toArray() : [
            'completed_orders' => 0,
            'average_rating' => 0,
            // ... other defaults
        ];
    }

    public function ensureProviderHasLevel(Provider $provider): void
    {
        // Check if provider has any level record
        if (!$provider->levels()->exists()) {
            $this->initializeProviderLevel($provider);
        }

        // Check if provider has current month metrics
        if (!$provider->currentMonthMetrics) {
            $this->initializeProviderMetrics($provider);
        }
    }

    protected function initializeProviderLevel(Provider $provider): void
    {
        $defaultLevel = Level::orderBy('level')->first();
        $this->evaluateProvider($provider);
        if ($defaultLevel) {
            ProviderLevel::create([
                'provider_id' => $provider->id,
                'level_id' => $defaultLevel->id,
                'achieved_at' => now(),
                'is_current' => true
            ]);
        }
    }

    protected function initializeProviderMetrics(Provider $provider): void
    {
        ProviderMetric::create([
            'provider_id' => $provider->id,
            'month' => now()->startOfMonth(),
            'completed_orders' => 0,
            'average_rating' => 0,
            'repeat_customers' => 0,
            'completion_rate' => 0,
            'cancellation_rate' => 0,
            'response_time_avg' => 0
        ]);
    }
}
