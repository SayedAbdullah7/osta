<?php

namespace Database\Factories;

use App\Services\ProviderStatisticService;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProviderStatistic;
use App\Models\Provider;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProviderStatistic>
 */
class ProviderStatisticFactory extends Factory
{
    protected $model = ProviderStatistic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Fetch a random provider that doesn't have a current month statistic
        $provider = Provider::doesntHave('currentMonthStatistic')->inRandomOrder()->first();

        // Fallback if no provider is found (should ideally not happen in most cases)
        if (!$provider) {
//            $provider = Provider::factory()->create();
            return [];
        }

        return [
            'provider_id' => $provider->id,
            'month' => Carbon::now()->startOfMonth(),
            'orders_done_count' => $this->faker->numberBetween(0, 100),
//            'level' => $this->faker->numberBetween(1, 10),
//            'orders_remaining_for_next_level' => $this->faker->numberBetween(0, 50),
            'level' => 1,
            'orders_remaining_for_next_level' => 0,
        ];
    }
}
