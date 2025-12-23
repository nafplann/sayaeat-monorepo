<?php

namespace App\Http\Controllers;

use App\Enums\KirimAja\OrderStatus as KirimAjaOrderStatus;
use App\Enums\MakanAjaOrderStatus;
use App\Enums\MarketAja\OrderStatus as MarketAjaOrderStatus;
use App\Enums\PermissionsEnum;
use App\Models\Order;
use App\Models\Setting;
use App\Models\ShipmentOrder;
use App\Models\ShoppingOrder;
use App\Models\StoreOrder;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\PermissionMiddleware;

;

class DashboardController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::READ_DASHBOARD->value), only: ['index']),
        ];
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->isOwner()) {
            return view('dashboard.owner');
        }

        return view('dashboard.index');
    }

    public function getData(Request $request)
    {
        $model = '\\App\\Models\\' . ucfirst($request->model);
        $start = new DateTime();
        $start->modify('-14 day');
        $end = new DateTime();
        $data = [];

        $query = $model::where([
            ['created_at', '>=', $start],
            ['created_at', '<=', $end],
        ])->get();

        while ($start < $end) {
            $date = $start->format('Y-m-d');
            $data[$date] = 0;
            $start->modify('+1 day');
        }

        foreach ($query as $row) {
            $createdAt = $row->created_at->format('Y-m-d');
            $data[$createdAt]++;
        }

        return [
            'count' => $model::count(),
            'history' => implode(',', array_values($data))
        ];
    }

    public function belanjaAja(Request $request)
    {
        return view('dashboard.belanja_aja');
    }

    public function getDataV2(Request $request)
    {
        $model = '\\App\\Models\\' . ucfirst($request->model);

        $start = new DateTime();
        $start->modify('-14 day');
        $end = new DateTime();
        $data = [];

        $query = $model::where([
            ['created_at', '>=', $start],
            ['created_at', '<=', $end],
        ])->get();

        while ($start < $end) {
            $date = $start->format('Y-m-d');
            $data[$date] = 0;
            $start->modify('+1 day');
        }

        foreach ($query as $row) {
            $createdAt = $row->created_at->format('Y-m-d');
            $data[$createdAt]++;
        }

        return [
            'count' => $model::count(),
            'history' => implode(',', array_values($data))
        ];
    }

    public function dailyRevenue(Request $request)
    {
        try {

            $user = Auth::user();
            $timezone = $user->timezone;
            $profitMarginPercentage = (float)Setting::where('key', 'ba_profit_margin_percentage')
                ->first()
                ->value;

            $start = Carbon::parse($request->get('start'), $timezone)->startOfDay();
            $end = Carbon::parse($request->get('end'), $timezone)->endOfDay();

            $dayDiff = $start->diffInDays($end);
            $range = [];
            $results = [];

            // Max data to pull is 3months
            if ($start->diffInMonths() > 3) {
                abort(422, 'Maximum data to query is 3 months');
            }

            while ($start->isBefore($end)) {
                $range[$start->format('d-m-Y')] = [
                    'delivery_fees' => 0,
                    'service_fees' => 0,
                    'items_profit' => 0,
                ];
                $start->addDay();
            }

            // Reset start and end date
            $start = Carbon::parse($request->get('start'), $timezone)
                ->startOfDay()
                ->setTimezone('UTC');
            $end = Carbon::parse($request->get('end'), $timezone)
                ->endOfDay()
                ->setTimezone('UTC');

            $orders = Order::where('status', MakanAjaOrderStatus::COMPLETED->value)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $storeOrders = StoreOrder::where('status', MarketAjaOrderStatus::COMPLETED->value)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $shipmentOrders = ShipmentOrder::where('status', KirimAjaOrderStatus::COMPLETED->value)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $shopping_orders = ShoppingOrder::where('status', 2)
                ->whereBetween('created_at', [$start, $end])
                ->get();

            foreach ($orders as $order) {
                $orderDate = $order->created_at
                    ->setTimezone($timezone)
                    ->format('d-m-Y');
                $deliveryFees = $order->delivery_fee - ($order->delivery_fee * (80 / 100)) - $order->delivery_fee_discount;

                $range[$orderDate]['delivery_fees'] += $deliveryFees;
                $range[$orderDate]['service_fees'] += $order->service_fee;
                $range[$orderDate]['items_profit'] += $order->items->sum('markup_amount');
            }

            foreach ($storeOrders as $order) {
                $orderDate = $order->created_at
                    ->setTimezone($timezone)
                    ->format('d-m-Y');
                $deliveryFees = $order->delivery_fee - ($order->delivery_fee * (80 / 100)) - $order->delivery_fee_discount;

                $range[$orderDate]['delivery_fees'] += $deliveryFees;
                $range[$orderDate]['service_fees'] += $order->service_fee;
                $range[$orderDate]['items_profit'] += $order->items->sum('markup_amount');
            }

            foreach ($shipmentOrders as $order) {
                $orderDate = $order->created_at
                    ->setTimezone($timezone)
                    ->format('d-m-Y');
                $deliveryFees = $order->delivery_fee - ($order->delivery_fee * (80 / 100)) - $order->delivery_fee_discount;

                $range[$orderDate]['delivery_fees'] += $deliveryFees;
                $range[$orderDate]['service_fees'] += $order->service_fee;
            }

            foreach ($shopping_orders as $order) {
                $orderDate = $order->created_at
                    ->setTimezone($timezone)
                    ->format('d-m-Y');
                $deliveryFees = $order->delivery_fee - ($order->delivery_fee * (80 / 100));
                $serviceFees = $order->service_fee - ($order->subtotal * ($profitMarginPercentage / 100));

                $range[$orderDate]['delivery_fees'] += $deliveryFees;
                $range[$orderDate]['service_fees'] += $serviceFees;
            }

            foreach ($range as $date => $value) {
                $results[] = [
                    'label' => $date,
                    'delivery_fees' => $value['delivery_fees'],
                    'service_fees' => $value['service_fees'],
                    'items_profit' => $value['items_profit'],
                ];
            }

            return $results;
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
