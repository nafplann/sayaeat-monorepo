<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionAndRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = Role::create(['name' => RolesEnum::SUPER_ADMIN]);
        $admin = Role::create(['name' => RolesEnum::ADMIN]);
        $owner = Role::create(['name' => RolesEnum::OWNER]);

        foreach (PermissionsEnum::cases() as $case) {
            $permission = Permission::create(['name' => $case->value]);
            $superadmin->givePermissionTo($permission);
        }

        $adminPermissions = Permission::where('name', 'LIKE', '%merchants%')
            ->orWhere('name', 'LIKE', '%menus%')
            ->orWhere('name', 'LIKE', '%orders%')
            ->orWhere('name', 'LIKE', '%menu categories%')
            ->orWhere('name', 'LIKE', '%shopping orders%')
            ->get();
        $admin->givePermissionTo($adminPermissions);
        $admin->givePermissionTo(PermissionsEnum::READ_DASHBOARD);

        $ownerPermissions = Permission::where('name', 'LIKE', '%merchants%')
            ->orWhere('name', 'LIKE', '%menus%')
            ->orWhere('name', 'LIKE', '%orders%')
            ->orWhere('name', 'LIKE', '%menu categories%')
            ->get();
        $owner->givePermissionTo($ownerPermissions);
        $owner->givePermissionTo(PermissionsEnum::READ_DASHBOARD);
    }
}

//foreach (PermissionsEnum::cases() as $case) {
//    if (str_contains($case->value, 'shopping orders')) {
//        $permission = Permission::create(['name' => $case->value]);
//    }
//    $superadmin->givePermissionTo($permission);
//}
