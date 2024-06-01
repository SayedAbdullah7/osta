<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Random\RandomException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faq>
 */
class FaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence,
            'answer' => $this->faker->paragraph(random_int(10, 30)),
            'category_id' => \App\Models\FAQCategory::inRandomOrder()->first()->id
        ];
    }
}
