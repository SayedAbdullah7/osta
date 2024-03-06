<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\SubService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SubService::count() < 1) {

            SubService::factory()->count(60)->create(); // Adjust the count as needed
        }
    }
}
