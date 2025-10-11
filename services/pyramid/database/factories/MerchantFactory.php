<?php

namespace Database\Factories;

use App\Enums\MerchantCategory;
use App\Enums\MerchantStatus;
use App\Utils\GeoUtil;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $latitude = -5.1796914;
        $longitude = 119.46361;

        $location = json_decode(GeoUtil::randomizeLocation($latitude,$longitude, 1, 50));

        return [
            'name' => fake()->company(),
            'description' => fake()->sentence(10),
            'category' => MerchantCategory::RESTAURANT->value,
            'address' => fake()->address(),
            'phone_number' => fake()->phoneNumber(),
            'primary_whatsapp_number' => fake()->phoneNumber(),
            'secondary_whatsapp_number' => fake()->phoneNumber(),
            'bank_name' => 'Mandiri',
            'bank_account_holder' => fake()->name(),
            'bank_account_number' => fake()->randomNumber(9),
            'qris_link' => '',
            'latitude' => $location->lat,
            'longitude' => $location->lng,
            'logo_path' => trim(get_headers('https://source.unsplash.com/random/300x300?cafe', 1)['Location']),
            'status' => MerchantStatus::OPEN->value,
            'status_text' => '',
        ];
    }
}
