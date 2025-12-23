<?php

namespace Database\Seeders;

use App\Enums\MenuStatus;
use App\Enums\MerchantCategory;
use App\Enums\MerchantStatus;
use App\Models\Menu;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('email', 'owner@gmail.com')
            ->first();

        $merchant = Merchant::create([
            'name' => 'Nasi Goreng Mas Daeng',
            'description' => 'Best nasi goreng in town',
            'category' => MerchantCategory::RESTAURANT->value,
            'address' => 'Jl Pettarani, Makassar 90232',
            'phone_number' => '+628123456',
            'primary_whatsapp_number' => '+628123456',
            'secondary_whatsapp_number' => '+62888888',
            'bank_name' => 'Mandiri',
            'bank_account_holder' => 'Mas Daeng',
            'bank_account_number' => '123123123',
            'qris_link' => '',
            'latitude' => -5.195300,
            'longitude' => 119.476956,
            'status' => MerchantStatus::OPEN->value,
            'status_text' => '',
            'owner_id' => $owner->id,
        ]);

        $menu1 = Menu::create([
            'name' => 'Nasi Goreng Gila',
            'description' => '',
            'price' => 25000,
            'status' => MenuStatus::AVAILABLE->value,
            'status_text' => '',
            'merchant_id' => $merchant->id,
        ]);

        $menu2 = Menu::create([
            'name' => 'Nasi Goreng Waras',
            'description' => '',
            'price' => 35000,
            'status' => MenuStatus::AVAILABLE->value,
            'status_text' => '',
            'merchant_id' => $merchant->id,
        ]);

//        // Random makassar merchant
//        Merchant::factory()
//            ->has(Menu::factory()->count(5))
//            ->count(50)
//            ->create();
    }
}
