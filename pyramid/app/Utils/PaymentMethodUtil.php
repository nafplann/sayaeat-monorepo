<?php

namespace App\Utils;

use App\Models\Setting;

class PaymentMethodUtil
{
    public static function isEnabled(string $paymentMethod): bool
    {
        $method = Setting::where('key', 'payment_method')
            ->first();

        $enabledPaymentMethod = collect(json_decode($method->value))
            ->filter(function ($enabled) {
                return $enabled;
            })
            ->keys()
            ->toArray();
        
        return in_array($paymentMethod, $enabledPaymentMethod);
    }
}

