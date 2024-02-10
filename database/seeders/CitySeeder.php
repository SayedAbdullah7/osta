<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (City::count() < 1){
            City::factory()->count(50)->create(); // Adjust the count as needed
//            City::create(['name' => 'cairo','country_id'=>'1']);
        }
    }
}
