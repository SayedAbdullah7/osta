<?php

namespace App\Repositories;
use App\Models\Level;
use App\Models\ProviderStatistic as ProviderStatistics;
use Carbon\Carbon;
class ProviderStatisticRepository
{
    public function getStatisticsByProvider($providerId)
    {
        return ProviderStatistics::where('provider_id', $providerId)->first();
    }
    // Add repository methods here
    public function getCurrentMonthStatistics($providerId)
    {
        $month = Carbon::now()->startOfMonth();
        return ProviderStatistics::firstOrCreate(
            ['provider_id' => $providerId, 'month' => $month],
            ['orders_done_count' => 0, 'level' => 1, 'orders_remaining_for_next_level' => $this->getOrdersRequiredForNextLevel(1)]
        );
    }

    public function incrementOrderCount(ProviderStatistics $statistics)
    {
        $statistics->orders_done_count++;
        $statistics->orders_remaining_for_next_level--;

        if ($statistics->orders_remaining_for_next_level <= 0) {
            $this->levelUp($statistics);
        }

        $statistics->save();
    }

    private function levelUp(ProviderStatistics $statistics)
    {
        $currentLevel = $statistics->level;
        $nextLevel = Level::where('level', $currentLevel)->first()->nextLevel;

        if ($nextLevel) {
            $statistics->level = $nextLevel->level;
            $statistics->orders_remaining_for_next_level = $nextLevel->orders_required;
        } else {
            $statistics->orders_remaining_for_next_level = 0; // No more levels
        }
    }

    private function getOrdersRequiredForNextLevel($currentLevel)
    {
        $level = Level::where('level', $currentLevel)->first();
        return $level ? $level->orders_required : 0;
    }
    public function recalculateLevel(ProviderStatistics $statistics)
    {
        $currentLevel = $statistics->level;
        $ordersDone = $statistics->orders_done_count;

        // Fetch the highest level based on orders done
        $level = \App\Models\Level::where('orders_required', '<=', $ordersDone)
            ->orderBy('level', 'desc')
            ->first();

        if ($level && $level->level != $currentLevel) {
            $statistics->level = $level->level;
            $nextLevel = $this->getNextLevel($statistics->level);

            if ($nextLevel) {
                $statistics->orders_remaining_for_next_level = $nextLevel->orders_required - $ordersDone;
            } else {
                // If there's no next level, set remaining orders to 0
                $statistics->orders_remaining_for_next_level = 0;
            }

            $statistics->save();
        }
    }


    public function recalculateOrdersDoneCount($providerId): void
    {
        $completedOrdersCount = \App\Models\Order::where('provider_id', $providerId)
            ->where('status', 'completed')
            ->count();

        $statistics = $this->getCurrentMonthStatistics($providerId);
        $statistics->orders_done_count = $completedOrdersCount;

        // After recalculating orders done, recalculate the level
        $this->recalculateLevel($statistics);
    }

    public function getNextLevel($currentLevel)
    {
        return \App\Models\Level::where('level', '>', $currentLevel)->orderBy('level')->first();
    }
}
