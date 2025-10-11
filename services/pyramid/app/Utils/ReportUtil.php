<?php

namespace App\Utils;

use App\Enums\KirimAja\OrderStatus as KirimAjaOrderStatus;
use App\Enums\MakanAjaOrderStatus;
use App\Enums\MarketAja\OrderStatus as MarketAjaOrderStatus;
use App\Models\Order;
use App\Models\Setting;
use App\Models\ShipmentOrder;
use App\Models\ShoppingOrder;
use App\Models\StoreOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportUtil
{
    public static function driverRank(Request $request)
    {
        $user = Auth::user();
        $timezone = $user->timezone ?? 'Asia/Jayapura';
        $profitMarginPercentage = (float)Setting::where('key', 'ba_profit_margin_percentage')
            ->first()
            ->value;
        $start = Carbon::parse($request->get('start'), $timezone)
            ->startOfDay()
            ->setTimezone('UTC');
        $end = Carbon::parse($request->get('end'), $timezone)
            ->endOfDay()
            ->setTimezone('UTC');

        // Makan-Aja
        $orders = Order::selectRaw("driver_id, count(*) as total_orders, SUM(delivery_fee) as revenue")
            ->where('status', MakanAjaOrderStatus::COMPLETED->value)
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('driver_id')
            ->orderBy('total_orders', 'desc')
            ->get();

        // Market-Aja
        $storeOrders = StoreOrder::selectRaw("driver_id, count(*) as total_orders, SUM(delivery_fee) as revenue")
            ->where('status', MarketAjaOrderStatus::COMPLETED->value)
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('driver_id')
            ->orderBy('total_orders', 'desc')
            ->get();

        // Belanja-Aja
        $shopping_orders = ShoppingOrder::selectRaw('driver_id, count(*) as total_orders, SUM(delivery_fee) as delivery_fees, SUM(subtotal) as subtotal')
            ->where('status', 2) // Completed Status
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('driver_id')
            ->orderBy('total_orders', 'desc')
            ->get();

        // Kirim-Aja
        $shipmentOrders = ShipmentOrder::selectRaw("driver_id, count(*) as total_orders, SUM(delivery_fee) as revenue")
            ->where('status', KirimAjaOrderStatus::COMPLETED->value)
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('driver_id')
            ->orderBy('total_orders', 'desc')
            ->get();

        $results = [];
        $drivers = [];

        foreach ($orders as $order) {
            $driver = $order->driver;

            if (!isset($drivers[$driver->id])) {
                $drivers[$driver->id] = [
                    'name' => $driver->name,
                    'total_orders' => 0,
                    'revenue' => 0,
                ];
            }

            $drivers[$driver->id]['total_orders'] += $order->total_orders;
            $drivers[$driver->id]['revenue'] += $order->revenue - ($order->revenue * (20 / 100));
        }

        foreach ($storeOrders as $order) {
            $driver = $order->driver;

            if (!isset($drivers[$driver->id])) {
                $drivers[$driver->id] = [
                    'name' => $driver->name,
                    'total_orders' => 0,
                    'revenue' => 0,
                ];
            }

            $drivers[$driver->id]['total_orders'] += $order->total_orders;
            $drivers[$driver->id]['revenue'] += $order->revenue - ($order->revenue * (20 / 100));
        }

        foreach ($shopping_orders as $order) {
            $driver = $order->driver;

            if (!isset($drivers[$driver->id])) {
                $drivers[$driver->id] = [
                    'name' => $driver->name,
                    'total_orders' => 0,
                    'revenue' => 0,
                ];
            }

            $fees = $order->delivery_fees - ($order->delivery_fees * (20 / 100));
            $drivers[$driver->id]['total_orders'] += $order->total_orders;
            $drivers[$driver->id]['revenue'] += ($fees + ($order->subtotal * ($profitMarginPercentage / 100)));
        }

        foreach ($shipmentOrders as $order) {
            $driver = $order->driver;

            if (!isset($drivers[$driver->id])) {
                $drivers[$driver->id] = [
                    'name' => $driver->name,
                    'total_orders' => 0,
                    'revenue' => 0,
                ];
            }

            $drivers[$driver->id]['total_orders'] += $order->total_orders;
            $drivers[$driver->id]['revenue'] += $order->revenue - ($order->revenue * (20 / 100));
        }

        foreach ($drivers as $key => $driver) {
            $results[] = [
                'label' => $driver['name'],
                'revenue' => $driver['revenue'],
                'total_orders' => $driver['total_orders'],
            ];
        }

        return $results;
    }
}
