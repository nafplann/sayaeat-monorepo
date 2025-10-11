<?php

namespace App\Http\Controllers;

use App\Enums\KirimAja\OrderPaymentMethod as KirimAjaOrderPaymentMethod;
use App\Enums\KirimAja\OrderStatus;
use App\Enums\MakanAjaOrderStatus;
use App\Enums\MarketAja\OrderStatus as MarketAjaOrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Models\Driver;
use App\Models\Order;
use App\Models\ShipmentOrder;
use App\Models\ShoppingOrder;
use App\Models\StoreOrder;
use App\Utils\ReportUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DriverDailyReportController extends Controller
{
    public function index()
    {
        return view('driver_daily_report.index');
    }

    public function income(Request $request)
    {
        $timezone = 'Asia/Jayapura';
        $start = Carbon::parse($request->get('start'), $timezone)
            ->startOfDay()
            ->setTimezone('UTC');
        $end = Carbon::parse($request->get('end'), $timezone)
            ->endOfDay()
            ->setTimezone('UTC');

        $orders = Order::where('status', MakanAjaOrderStatus::COMPLETED->value)
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $shipments = ShipmentOrder::where('status', OrderStatus::COMPLETED->value)
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $shoppings = ShoppingOrder::where('status', 2)
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $storeOrders = StoreOrder::where('status', MarketAjaOrderStatus::COMPLETED->value)
            ->where(function ($q) {
                $q->whereNotNull('driver_id')->orWhere('driver_id', '<>', '');
            })
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $data = [];

        /*
         * merchant_paid_by = driver
         * payment_method = cash
         * subtotal = 50000
         * delivery_fee = 10000
         * service_fee = 2000
         * total = 62000
         */

        /*
         * customer pay 62000
         * driver get 50000
         * driver got paid 8000 for the delivery fee
         * driver need to deposit the rest 4000
         */
        foreach ($orders as $order) {
            $total = $order->total;
            $deliveryFee = $order->delivery_fee;
            $serviceFee = $order->service_fee;
            $itemsMarkup = $order->items->reduce(fn($carry, $item) => $carry + $item->markup_amount, 0);
            $subtotal = $order->subtotal - $itemsMarkup;

            $driverId = $order->driver_id;

            $driverRating = $order->ratings()
                ->where('model_type', Driver::class)
                ->first();

            if (!isset($data[$driverId])) {
                $data[$driverId] = [
                    'driver' => $order->driver,
                    'deposit' => 0,
                    'credit' => 0,
                    'income' => 0,
                    'orders' => []
                ];
            }

            $isCashPayment = $order->payment_method === OrderPaymentMethod::CASH_ON_DELIVERY->value;
            $isOrderPaidByDriver = $order->merchant_paid_by === 'Driver';
            $deliveryFeeForDriver = $deliveryFee - ($deliveryFee * (config('wa_aja.profit_sharing_percentage.delivery_fee') / 100));
            $totalDiscount = $order->delivery_fee_discount + $order->order_discount;

            if ($isCashPayment && $isOrderPaidByDriver) {
                $data[$driverId]['deposit'] += $total - $subtotal - $totalDiscount - $deliveryFeeForDriver;
            }

            if ($isCashPayment && !$isOrderPaidByDriver) {
                $data[$driverId]['deposit'] += $total - $totalDiscount - $deliveryFeeForDriver;
            }

            if (!$isCashPayment && $isOrderPaidByDriver) {
                $data[$driverId]['credit'] += $subtotal + $deliveryFeeForDriver;
            }

            if (!$isCashPayment && !$isOrderPaidByDriver) {
                $data[$driverId]['credit'] += $deliveryFeeForDriver;
            }

            // Count income
            $data[$driverId]['income'] += $deliveryFeeForDriver;

            $data[$driverId]['orders'][] = [
                'id' => $order->id,
                'order_type' => 'MAKAN-AJA',
                'items_markup' => $itemsMarkup,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'total' => $total,
                'is_cash' => $isCashPayment,
                'is_paid_by_driver' => $isOrderPaidByDriver,
                'distance' => $order->distance,
                'customer' => $order->customer->name,
                'driver_income' => $deliveryFeeForDriver,
                'rating' => $driverRating ? $driverRating->rating : '-',
                'review' => $driverRating ? $driverRating->review : '-',
                'order_date' => \App\Utils\DateUtil::toUserLocalTime($order->created_at)
            ];
        }

        foreach ($storeOrders as $order) {
            $total = $order->total;
            $deliveryFee = $order->delivery_fee;
            $serviceFee = $order->service_fee;
            $itemsMarkup = $order->items->reduce(fn($carry, $item) => $carry + $item->markup_amount, 0);
            $subtotal = $order->subtotal - $itemsMarkup;

            $driverId = $order->driver_id;

            $driverRating = $order->ratings()
                ->where('model_type', Driver::class)
                ->first();

            if (!isset($data[$driverId])) {
                $data[$driverId] = [
                    'driver' => $order->driver,
                    'deposit' => 0,
                    'credit' => 0,
                    'income' => 0,
                    'orders' => []
                ];
            }

            $isCashPayment = $order->payment_method === OrderPaymentMethod::CASH_ON_DELIVERY->value;
            $isOrderPaidByDriver = $order->store_paid_by === 'Driver';
            $deliveryFeeForDriver = $deliveryFee - ($deliveryFee * (config('wa_aja.profit_sharing_percentage.delivery_fee') / 100));
            $totalDiscount = $order->delivery_fee_discount + $order->order_discount;

            if ($isCashPayment && $isOrderPaidByDriver) {
                $data[$driverId]['deposit'] += $total - $subtotal - $totalDiscount - $deliveryFeeForDriver;
            }

            if ($isCashPayment && !$isOrderPaidByDriver) {
                $data[$driverId]['deposit'] += $total - $totalDiscount - $deliveryFeeForDriver;
            }

            if (!$isCashPayment && $isOrderPaidByDriver) {
                $data[$driverId]['credit'] += $subtotal + $deliveryFeeForDriver;
            }

            if (!$isCashPayment && !$isOrderPaidByDriver) {
                $data[$driverId]['credit'] += $deliveryFeeForDriver;
            }

            // Count income
            $data[$driverId]['income'] += $deliveryFeeForDriver;

            $data[$driverId]['orders'][] = [
                'id' => $order->id,
                'order_type' => 'MARKET-AJA',
                'items_markup' => $itemsMarkup,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'total' => $total,
                'is_cash' => $isCashPayment,
                'is_paid_by_driver' => $isOrderPaidByDriver,
                'distance' => $order->distance,
                'customer' => $order->customer->name,
                'driver_income' => $deliveryFeeForDriver,
                'rating' => $driverRating ? $driverRating->rating : '-',
                'review' => $driverRating ? $driverRating->review : '-',
                'order_date' => \App\Utils\DateUtil::toUserLocalTime($order->created_at)
            ];
        }

        foreach ($shoppings as $order) {
            $total = $order->total;
            $subtotal = $order->subtotal;
            $deliveryFee = $order->delivery_fee;
            $serviceFee = $order->service_fee;
            $driverId = $order->driver_id;

            if (!isset($data[$driverId])) {
                $data[$driverId] = [
                    'driver' => $order->driver,
                    'deposit' => 0,
                    'credit' => 0,
                    'income' => 0,
                    'orders' => []
                ];
            }

            $isCashPayment = $order->payment_method === 3; // cash
            $isOrderPaidByDriver = $order->paid_by === 'KURIR';
            $deliveryFeeForDriver = $deliveryFee - ($deliveryFee * (config('wa_aja.profit_sharing_percentage.delivery_fee') / 100));
            $bonusForDriver = $subtotal * (config('wa_aja.profit_sharing_percentage.belanja_aja') / 100);

            if ($isCashPayment && $isOrderPaidByDriver) {
                $data[$driverId]['deposit'] += $total - $subtotal - $deliveryFeeForDriver - $bonusForDriver;
            }

            if ($isCashPayment && !$isOrderPaidByDriver) {
                $data[$driverId]['deposit'] += $total - $deliveryFeeForDriver - $bonusForDriver;
            }

            if (!$isCashPayment && $isOrderPaidByDriver) {
                $data[$driverId]['credit'] += $subtotal + $deliveryFeeForDriver + $bonusForDriver;
            }

            if (!$isCashPayment && !$isOrderPaidByDriver) {
                $data[$driverId]['credit'] += $deliveryFeeForDriver + $bonusForDriver;
            }

            // Count income
            $data[$driverId]['income'] += $deliveryFeeForDriver + $bonusForDriver;

            $data[$driverId]['orders'][] = [
                'id' => $order->id,
                'order_type' => 'BELANJA-AJA',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'total' => $total,
                'is_cash' => $isCashPayment,
                'is_paid_by_driver' => $isOrderPaidByDriver,
                'customer' => $order->customer_name,
                'distance' => $order->distance,
                'driver_income' => $deliveryFeeForDriver + $bonusForDriver,
                'order_date' => \App\Utils\DateUtil::toUserLocalTime($order->created_at)
            ];
        }

        foreach ($shipments as $order) {
            $total = $order->total;
            $deliveryFee = $order->delivery_fee;
            $serviceFee = $order->service_fee;
            $driverId = $order->driver_id;

            $driverRating = $order->ratings()
                ->where('model_type', Driver::class)
                ->first();

            if (!isset($data[$driverId])) {
                $data[$driverId] = [
                    'driver' => $order->driver,
                    'deposit' => 0,
                    'credit' => 0,
                    'income' => 0,
                    'orders' => []
                ];
            }

            $isCashPayment = $order->payment_method === KirimAjaOrderPaymentMethod::CASH_BY_SENDER->value ||
                $order->payment_method === KirimAjaOrderPaymentMethod::CASH_BY_RECIPIENT->value; // cash
            $deliveryFeeForDriver = $deliveryFee - ($deliveryFee * (config('wa_aja.profit_sharing_percentage.delivery_fee') / 100));
            $totalDiscount = $order->delivery_fee_discount + $order->order_discount;

            if ($isCashPayment) {
                $data[$driverId]['deposit'] += $total - $totalDiscount - $deliveryFeeForDriver;
            }

            if (!$isCashPayment) {
                $data[$driverId]['credit'] += $deliveryFeeForDriver;
            }

            // Count income
            $data[$driverId]['income'] += $deliveryFeeForDriver;

            $data[$driverId]['orders'][] = [
                'id' => $order->id,
                'order_type' => 'KIRIM-AJA',
                'subtotal' => 0,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'total' => $total,
                'is_cash' => $isCashPayment,
                'is_paid_by_driver' => false,
                'customer' => $order->customer->name,
                'distance' => $order->distance,
                'driver_income' => $deliveryFeeForDriver,
                'rating' => $driverRating ? $driverRating->rating : '-',
                'review' => $driverRating ? $driverRating->review : '-',
                'order_date' => \App\Utils\DateUtil::toUserLocalTime($order->created_at)
            ];
        }

        return $data;
    }

    public function driverRank(Request $request)
    {
        return ReportUtil::driverRank($request);
    }
}
