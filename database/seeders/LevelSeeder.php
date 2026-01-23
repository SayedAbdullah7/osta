<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    public function run()
    {
        // Clear existing levels

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Level::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Bronze Level (Level 1)
        $bronze = Level::create([
            'name' => 'Bronze',
            'slug' => 'bronze',
            'level' => 1,
            'badge_image' => 'badges/bronze.png',
            'requirements' => [
                'metrics' => [
                    'completed_orders' => 20,
                    'average_rating' => 4.0
                ],
                'duration' => 'P1M' // 1 month evaluation period
            ],
            'benefits' => [
                'commission_rate' => 0.80, // 20% platform fee
//                'features' => ['basic_support'],
                'badge' => 'bronze',
                'priority' => 1
            ],
            'grace_period_months' => 1,
            'grace_period_applies_to_orders_only' => true,
            'is_active' => true
        ]);

        // Silver Level (Level 2)
        $silver = Level::create([
            'name' => 'Silver',
            'slug' => 'silver',
            'level' => 2,
            'badge_image' => 'badges/silver.png',
            'requirements' => [
                'metrics' => [
                    'completed_orders' => 50,
                    'average_rating' => 4.3
                ],
                'duration' => 'P1M'
            ],
            'benefits' => [
                'commission_rate' => 0.85, // 15% platform fee
//                'features' => ['priority_support', 'faster_payouts'],
                'badge' => 'silver',
                'priority' => 2
            ],
            'grace_period_months' => 1,
            'grace_period_applies_to_orders_only' => true,
            'is_active' => true
        ]);

        // Gold Level (Level 3)
        $gold = Level::create([
            'name' => 'Gold',
            'slug' => 'gold',
            'level' => 3,
            'badge_image' => 'badges/gold.png',
            'requirements' => [
                'metrics' => [
                    'completed_orders' => 100,
                    'average_rating' => 4.5
                ],
                'duration' => 'P1M'
            ],
            'benefits' => [
                'commission_rate' => 0.90, // 10% platform fee
//                'features' => ['premium_support', 'instant_payouts', 'featured_listing'],
                'badge' => 'gold',
                'priority' => 3
            ],
            'grace_period_months' => 1,
            'grace_period_applies_to_orders_only' => true,
            'is_active' => true
        ]);

        // Set up level progression chain
//        $bronze->update(['next_level_id' => $silver->id]);
//        $silver->update(['next_level_id' => $gold->id]);

        $this->command->info('Successfully seeded 3 levels: Bronze, Silver, Gold');
    }
}
