<?php

namespace App\Models;

use Carbon\Carbon;
use OwenIt\Auditing\Models\Audit;

class AuditLog extends Audit
{
    public static function logAuth($type)
    {
        if (!auth()->user()) {
            return;
        }

        self::create([
            'auditable_id' => auth()->user()->id,
            'auditable_type' => User::class,
            'user_type' => User::class,
            'event' => $type,
            'url' => request()->fullUrl(),
            'ip_address' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'old_values' => [],
            'new_values' => [],
            'user_id' => auth()->user()->id,
        ]);
    }
}
