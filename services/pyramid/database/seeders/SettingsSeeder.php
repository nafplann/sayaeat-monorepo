<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'key' => 'delivery_fee',
            'value' => 3000,
        ]);

        Setting::create([
            'key' => 'minimum_delivery_fee',
            'value' => 10000,
        ]);

        Setting::create([
            'key' => 'minimum_service_fee',
            'value' => 1500,
        ]);

        Setting::create([
            'key' => 'service_fee',
            'value' => 300,
        ]);

        Setting::create([
            'key' => 'maximum_covered_distance',
            'value' => 50,
        ]);

        Setting::create([
            'key' => 'maximum_ongoing_orders_per_customer',
            'value' => 3,
        ]);

        // Belanja-Aja
        Setting::create([
            'key' => 'ba_delivery_fee',
            'value' => 3000,
        ]);

        Setting::create([
            'key' => 'ba_minimum_delivery_fee',
            'value' => 10000,
        ]);

        Setting::create([
            'key' => 'ba_minimum_service_fee',
            'value' => 1500,
        ]);

        Setting::create([
            'key' => 'ba_service_fee',
            'value' => 300,
        ]);

        Setting::create([
            'key' => 'ba_maximum_covered_distance',
            'value' => 50,
        ]);

        Setting::create([
            'key' => 'ba_maximum_ongoing_orders_per_customer',
            'value' => 3,
        ]);

        Setting::create([
            'key' => 'ba_profit_margin_percentage',
            'value' => 5,
        ]);
    }
}
