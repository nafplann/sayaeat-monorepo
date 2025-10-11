<?php

namespace App\Enums;

enum RolesEnum : string
{
    case SUPER_ADMIN = 'super admin';
    case ADMIN = 'admin';
    case OWNER = 'owner';

    // extra helper to allow for greater customization of displayed values, without disclosing the name/value data directly
    public function label(): string
    {
        return match ($this) {
            static::SUPER_ADMIN => 'Super Admin',
            static::ADMIN => 'Admin',
            static::OWNER => 'Owner',
        };
    }
}
