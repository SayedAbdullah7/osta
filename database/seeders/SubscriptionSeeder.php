<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the subscriptions table is empty
        if (Subscription::count() > 0) {
            // If not empty, stop the seeder
//            $this->command->info('Subscriptions table is already seeded.');
            return;
        }
        $faker = Faker::create();

        // Generate example subscriptions
        foreach (range(1, 10) as $index) {
            Subscription::create([
                'name' => $faker->word . ' Subscription',
                'description' => $faker->text,
                'price' => $faker->randomFloat(2, 10, 100),
                'price_before_discount' => $faker->optional()->randomFloat(2, 100, 200),
                'discount_expiration_date' => $faker->optional()->date,
//                'level_id' => $faker->optional()->numberBetween(1, 5),
                'fee_percentage' => $faker->randomFloat(2, 1, 10),
                'number_of_days' => $faker->numberBetween(20, 30),
                'is_available' => $faker->boolean,
            ]);
        }
    }
}
