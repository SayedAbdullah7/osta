<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(\App\Models\FaqCategory::count() > 0) {
            return;
        }

        $categories = [
            'General',
            'Account',
            'Payment',
            'Subscription',
            'Service',
            'Provider',
            'Booking',
            'Cancellation',
            'Refund',
            'Complaint',
        ];

        foreach ($categories as $category) {
            \App\Models\FaqCategory::create([
                'name' => $category,
            ]);
        }
    }
}
