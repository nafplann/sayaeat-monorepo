<?php

namespace App\Utils;

use App\Models\Order;
use App\Models\Setting;
use App\Models\ShipmentOrder;
use App\Models\StoreOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SettingsUtil
{
    public static function getOperationalStatus()
    {
        $status = Setting::where('key', 'operational_status')->first();
        return $status->value;
    }

    public static function getMaxDistanceCovered()
    {
        $maxDistance = Setting::where('key', 'maximum_covered_distance')->first();
        return (double)$maxDistance->value;
    }

    public static function getMaxOngoingOrders()
    {
        $maxOngoingOrders = Setting::where('key', 'maximum_ongoing_orders_per_customer')->first();
        return $maxOngoingOrders->value;
    }

    public static function generateOrderNumber(): string
    {
        return Cache::lock('ma-order-number', 10)->get(function () {
            $todaysOrderCount = Order::whereBetween('created_at', [
                Carbon::today('Asia/Jayapura')->startOfDay(),
                Carbon::today('Asia/Jayapura')->endOfDay()
            ])
                ->count();

            return 'MA-' . str_pad($todaysOrderCount + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public static function generateMarketAjaOrderNumber(): string
    {
        return Cache::lock('mr-order-number', 10)->get(function () {
            $todaysOrderCount = StoreOrder::whereBetween('created_at', [
                Carbon::today('Asia/Jayapura')->startOfDay(),
                Carbon::today('Asia/Jayapura')->endOfDay()
            ])
                ->count();

            return 'MR-' . str_pad($todaysOrderCount + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public static function generateKirimAjaOrderNumber(): string
    {
        return Cache::lock('ka-order-number', 10)->get(function () {
            $todaysOrderCount = ShipmentOrder::whereBetween('created_at', [
                Carbon::today('Asia/Jayapura')->startOfDay(),
                Carbon::today('Asia/Jayapura')->endOfDay()
            ])
                ->count();

            return 'KA-' . str_pad($todaysOrderCount + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
