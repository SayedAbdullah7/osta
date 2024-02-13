<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countries = Country::all();
        return [
            'name' => $this->faker->city,
            'country_id' => $countries->random(1)->first()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
