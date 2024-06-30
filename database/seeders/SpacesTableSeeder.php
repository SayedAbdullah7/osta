<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpacesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Space::count() > 0) {
            return;
        }
        $spaces = [
            [
                'name' => '0m - 20m',
            ],
            [
                'name' => '20m - 60m',
            ],
            [
                'name' => '60m - 100m',
            ],
            [
                'name' => '100m - 150m',
            ],
            [
                'name' => '150m - 220m',
            ],
            [
                'name' => '220m - 300m',
            ],
            [
                'name' => '300m - 400m',
            ],
            [
                'name' => '400m - 500m',
            ],
            [
                'name' => '500m - 600m',
            ],
            [
                'name' => '600m - 700m',
            ],
            [
                'name' => '700m - 800m',
            ],
        ];
        Space::insert($spaces);
    }
}
