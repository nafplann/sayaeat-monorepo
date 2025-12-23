<?php

namespace Database\Factories;

use App\Enums\MenuCategory;
use App\Enums\MenuStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create();
        $faker->addProvider(new \FakerRestaurant\Provider\id_ID\Restaurant($faker));

        $name = $faker->foodName();

        return [
            'name' => $name,
            'description' => $name,
            'category' => MenuCategory::FOOD->value,
            'price' => array_rand(range(5000, 50000, 500)),
            'image_path' => trim(get_headers("https://source.unsplash.com/random/300x300?".rawurlencode($name), 1)['Location']),
            'status' => MenuStatus::AVAILABLE->value,
            'status_text' => '',
        ];
    }
}
