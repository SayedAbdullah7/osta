<?php

namespace Database\Seeders;

use App\Models\Warranty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarrantySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        if(Warranty::count() > 0) {
            return;
        }
        Warranty::insert([
            [
                'name' => 'Standard Warranty',
                'description' => 'Covers manufacturing defects for 12 months.',
                'duration_months' => 12,
                'percentage_cost' => 5.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Extended Warranty',
                'description' => 'Covers manufacturing defects and accidental damage for 24 months.',
                'duration_months' => 24,
                'percentage_cost' => 10.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium Warranty',
                'description' => 'Comprehensive coverage for 36 months including wear and tear.',
                'duration_months' => 36,
                'percentage_cost' => 15.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
