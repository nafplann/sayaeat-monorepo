<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('123123'),
            'phone_number' => '+6281334444444',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123123'),
            'phone_number' => '+6211111111',
        ]);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@gmail.com',
            'password' => Hash::make('123123'),
            'phone_number' => '+6222222222',
        ]);

        $superadmin->assignRole(RolesEnum::SUPER_ADMIN->value);
        $admin->assignRole(RolesEnum::ADMIN->value);
        $owner->assignRole(RolesEnum::OWNER->value);
    }
}
