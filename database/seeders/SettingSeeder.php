<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(Setting::count() == 0) {
            $settings = [
               [ 'key' => 'min_wallet_balance', 'value' => -50],
               [ 'key' => 'preview_cost', 'value' => 100],
            ];
            Setting::insert($settings);
        }
    }
}
