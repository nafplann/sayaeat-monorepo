<?php

namespace App\Utils;

use App\Models\Setting;

class PriceUtil
{
    public static function calculateDeliveryFee(float $distance)
    {
        $deliveryFee = Setting::where('key', 'delivery_fee')->first();
        $minimumDeliveryFee = Setting::where('key', 'minimum_delivery_fee')->first();

        $fee = $distance * $deliveryFee->value;
        return max($minimumDeliveryFee->value, $fee);
    }

    public static function calculateServiceFee(float $distance)
    {
        $serviceFee = Setting::where('key', 'service_fee')->first();
        $minimumServiceFee = Setting::where('key', 'minimum_service_fee')->first();

        $fee = $distance * $serviceFee->value;
        return max($minimumServiceFee->value, $fee);
    }

    public static function calculateBADeliveryFee(float $distance)
    {
        $deliveryFee = Setting::where('key', 'ba_delivery_fee')->first();
        $minimumDeliveryFee = Setting::where('key', 'ba_minimum_delivery_fee')->first();

        $fee = $distance * $deliveryFee->value;
        return max($minimumDeliveryFee->value, $fee);
    }

    public static function calculateBAServiceFee(float $distance, float $subtotal)
    {
        $serviceFee = (float)Setting::where('key', 'ba_service_fee')
            ->first()
            ->value;
        $minimumServiceFee = (float)Setting::where('key', 'ba_minimum_service_fee')
            ->first()
            ->value;
        $profitMarginPercentage = (float)Setting::where('key', 'ba_profit_margin_percentage')
            ->first()
            ->value;

        $fee = ($subtotal * ($profitMarginPercentage / 100)) + ($distance * $serviceFee);

        return max($minimumServiceFee, $fee);
    }
}
