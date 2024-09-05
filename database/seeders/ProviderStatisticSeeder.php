<?php

namespace Database\Seeders;

use App\Models\ProviderStatistic;
use App\Models\Provider;
use App\Services\ProviderStatisticService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ProviderStatisticSeeder extends Seeder
{
    //        $providerStatistics = ProviderStatistic::factory()
//            ->count(10)
//            ->create();
//        foreach ($providerStatistics as $providerStatistic) {
//            $providerStatisticService = app(ProviderStatisticService::class);
//
//            $providerStatisticService->recalculateProviderLevel($providerStatistic->provider_id);
//        }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $providerStatisticService = app(ProviderStatisticService::class);

        $maxAttempts = 10; // Set a maximum number of attempts to create ProviderStatistics
        $loopCount = 0;    // Initialize a counter for loop iterations

        for ($i = 0; $i < $maxAttempts; $i++) {
            $provider = Provider::doesntHave('currentMonthStatistic')->inRandomOrder()->first();

            // If no provider is available, break the loop
            if (!$provider) {
                // Optionally, log or handle the scenario where no provider is available
                break;
            }

            // Create a ProviderStatistic entry
            $providerStatistic = ProviderStatistic::create([
                'provider_id' => $provider->id,
                'month' => Carbon::now()->startOfMonth(),
                'orders_done_count' => $faker->numberBetween(0, 40),
                'level' => 1,
                'orders_remaining_for_next_level' => 0,
            ]);

            // Recalculate provider level
            $providerStatisticService->recalculateProviderLevel($provider->id);

            $loopCount++; // Increment the loop counter
        }

        // Optionally, you can handle the result of the loop count here, e.g., logging it
        // Log::info("ProviderStatisticSeeder executed $loopCount times before stopping.");
    }

}

