<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Level::count() < 1) {
            // Define the data for levels with next_level_id initially set to null
            $levels = [
                [
                    'level' => 1,
                    'orders_required' => 10,
                    'next_level_id' => null, // Level 1 has no next level
                ],
                [
                    'level' => 2,
                    'orders_required' => 20,
                    'next_level_id' => null, // Temporarily set to null
                ],
                [
                    'level' => 3,
                    'orders_required' => 30,
                    'next_level_id' => null, // Level 3 is the last level
                ],
            ];

            // Create each level with next_level_id initially set to null
            foreach ($levels as $levelData) {
                Level::create(array_merge($levelData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // Fetch all levels to update next_level_id
            $allLevels = Level::all()->keyBy('level');

            foreach ($levels as $levelData) {
                $currentLevel = $allLevels->get($levelData['level']);
                $nextLevelId = $levelData['next_level_id'];

                if ($currentLevel && $nextLevelId) {
                    $currentLevel->update(['next_level_id' => $nextLevelId]);
                }
            }
        }
    }
}
