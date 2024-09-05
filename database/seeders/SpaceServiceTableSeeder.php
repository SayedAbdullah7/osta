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
        if (\App\Models\SpaceSubService::count() > 0) {
            return;
        }
        $spaces = \App\Models\Space::all();
        $services = \App\Models\Service::where('category', OrderCategoryEnum::SpaceBased->value)->get();

        // Seed the pivot table with relationships
//        $spaces->each(fn($space) => $space->services()->attach(
//            $services->random(rand(1, 3))->pluck('id')->toArray(),
//            ['max_price' => rand(50, 200)] // Adjust max_price as needed
//        ));
        foreach ($services as $service) {
            foreach ($service->subServices as $subService) {
                $spaces->each(fn($space) => $space->subServices()->attach(
                    [$subService->id],
                    ['max_price' => rand(50, 200)] // Adjust max_price as needed
                ));
            }

        }
//        $spaces->each(fn($space) => $space->subServices()->attach(
//            $services->random(rand(1, 3))->pluck('id')->toArray(),
//            ['max_price' => rand(50, 200)] // Adjust max_price as needed
//        ));
    }
}
