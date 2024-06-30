<?php

namespace Database\Seeders;

use App\Enums\OrderCategoryEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpaceServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spaces = \App\Models\Space::all();
        $services = \App\Models\Service::where('category', OrderCategoryEnum::SpaceBased->value)->get();

        if ($spaces->isEmpty() || $services->isEmpty()) {
            return;
        }
        // Seed the pivot table with relationships
        $services->each(static fn($service) =>
            $spaces->each(static fn($space) =>
                $space->services()->attach(
                    $service->id,
                    ['max_price' => $space->id * $service->id * 10]
                )
            )
        );
    ;
    }
}
