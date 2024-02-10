<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'min_price' => $this->faker->numberBetween(100, 500),
            'max_price' => $this->faker->numberBetween(500, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
