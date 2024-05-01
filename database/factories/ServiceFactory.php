<?php

namespace Database\Factories;

use App\Enums\OrderCategoryEnum;
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
            'category' => $this->faker->randomElement([OrderCategoryEnum::Basic->value, OrderCategoryEnum::SpaceBased->value, OrderCategoryEnum::Other->value, OrderCategoryEnum::Technical->value]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
