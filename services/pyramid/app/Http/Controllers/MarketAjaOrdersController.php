<?php

namespace App\Http\Controllers;

use App\Enums\MarketAja\OrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;
use App\Enums\PermissionsEnum;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\StoreOrder;
use App\Utils\MarketAjaOrderUtil;
use App\Utils\PriceUtil;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class MarketAjaOrdersController extends BaseController implements HasMiddleware
{
    public function __construct(StoreOrder $model)
    {
        parent::__construct(
            model: $model,
            module: 'market_aja_orders',
            displayNameSingular: 'Market Aja Order',
            displayNamePlural: 'Market Aja Orders',
            fieldDefs: [],
        );

        $this->baseUrl = $baseUrl ?? url("manage/market-aja");
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_MAKAN_AJA_ORDERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::UPDATE_MAKAN_AJA_ORDERS->value), only: ['update', 'calculateFees']),
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $order = StoreOrder::findOrFail($id);
        $action = $request->get('action');
        $user = Auth::user();

        try {
            if ($action === 'payment-verified') {
                $order->status = OrderStatus::WAITING_FOR_STORE_CONFIRMATION->value;
                $order->payment_status = OrderPaymentStatus::PAYMENT_RECEIVED->value;
                $order->payment_confirmed_by = $user->id;
                $order->save();
            }

            if ($action === 'driver-found') {
                $driver = Driver::findOrFail($request->get('driver_id'));
                $order->status = OrderStatus::STORE_PREPARING_ORDER->value;
                $order->driver_id = $driver->id;
                $order->store_paid_by = $request->get('store_paid_by');
                $order->save();
            }

            if ($action === 'status-update') {
                if (!$request->get('status')) throw new \Exception('Status is required.');
                if (!$request->get('distance')) throw new \Exception('Distance is required.');
                if (!$request->get('payment_method')) throw new \Exception('Metode pembayaran tidak boleh kosong.');

                // Recalculate fee
                $distance = $request->get('distance');
                $deliveryFee = (float)PriceUtil::calculateDeliveryFee($distance);
                $serviceFee = (float)PriceUtil::calculateServiceFee($distance);

                $order->status = OrderStatus::from($request->get('status'))->value;
                $order->distance = $distance;
                $order->delivery_fee = $deliveryFee;
                $order->service_fee = $serviceFee;
                $order->total = $order->subtotal + $serviceFee + $deliveryFee;
                $order->payment_method = OrderPaymentMethod::from($request->get('payment_method'));

                if ($order->status === OrderStatus::COMPLETED->value) {
                    $order->completed_at = Carbon::now()->toDateTimeString();
                }

                $order->save();
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order has been updated successfully.'
        ]);
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function datatable(Request $request): JsonResponse
    {
        $user = Auth::user();
        $timezone = $user->timezone;
        $query = $this->model::query();
        $statusCategory = $request->get('statusCategory');

        if ($statusCategory) {
            $query->whereBetween('created_at', [
                Carbon::today($timezone)->startOfDay()->setTimezone('UTC'),
                Carbon::today($timezone)->endOfDay()->setTimezone('UTC')
            ]);
            $query->where('status', '<>', 0);
            $query->ofStatusCategory($statusCategory);
        }

        // Handle filters
        if ($user->isSuperAdmin()) {
            $status = $request->get('status');
            $paymentMethod = $request->get('payment_method');
            $paymentStatus = $request->get('payment_status');
            $dateRange = $request->get('date_range');

            if ($status) {
                $query->whereIn('status', $status);
            }

            if ($paymentMethod) {
                $query->whereIn('payment_method', $paymentMethod);
            }

            if ($paymentStatus) {
                $query->whereIn('payment_status', $paymentStatus);
            }

            if ($dateRange) {
                [$start, $end] = explode(';', $dateRange);
                $query->whereBetween('created_at', [
                    Carbon::parse("$start 00:00:00"),
                    Carbon::parse("$end 23:59:59")
                ]);
            }
        }

        return DataTables::eloquent($query)
            ->editColumn('store', function (StoreOrder $order) {
                return $order->store ? $order->store->name : '';
            })
            ->editColumn('customer', function (StoreOrder $order) {
                return $order->customer ? $order->customer->name : '';
            })
            ->editColumn('driver', function (StoreOrder $order) {
                return $order->driver ? $order->driver->name : '';
            })
            ->editColumn('product_markup', function (StoreOrder $order) {
                return display_price($order->items->sum('markup_amount'));
            })
            ->editColumn('subtotal', function (StoreOrder $order) {
                return display_price($order->subtotal - $order->items->sum('markup_amount'));
            })
            ->editColumn('delivery_fee', function (StoreOrder $order) {
                return display_price($order->delivery_fee);
            })
            ->editColumn('service_fee', function (StoreOrder $order) {
                return display_price($order->service_fee);
            })
            ->editColumn('total', function (StoreOrder $order) {
                return display_price($order->total);
            })
//            ->editColumn('paid_by', function (StoreOrder $order) {
//                return ucfirst(strtolower($order->paid_by));
//            })
            ->editColumn('status_text', function (StoreOrder $order) {
                return MarketAjaOrderUtil::getStatusText(OrderStatus::from($order->status));
            })
            ->editColumn('payment_method', function (StoreOrder $order) {
                return MarketAjaOrderUtil::getPaymentMethodText(OrderPaymentMethod::from($order->payment_method));
            })
            ->editColumn('payment_status_text', function (StoreOrder $order) {
                return MarketAjaOrderUtil::getPaymentStatusText(OrderPaymentStatus::from($order->payment_status));
            })
            ->editColumn('distance', function (StoreOrder $order) {
                return $order->distance . ' km';
            })
            ->editColumn('created_at', function (StoreOrder $order) use ($timezone) {
                $date = $order->created_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->escapeColumns([])
            ->toJson();
    }

    /**
     * Get order details
     */
    public function details(Request $request, string $orderId)
    {
        $order = StoreOrder::findOrFail($orderId);
        return view('market_aja_orders.details', compact('order'));
    }

    /**
     * Process order
     */
    public function process(Request $request, string $orderId)
    {
        $order = StoreOrder::findOrFail($orderId);
        $orderStatus = OrderStatus::from($order->status);

        $response = ['title' => '', 'body' => ''];

        switch ($orderStatus) {
            case OrderStatus::WAITING_FOR_PAYMENT_VERIFICATION:
                $response['title'] = 'Verifikasi Pembayaran';
                $response['body'] = view('market_aja_orders.verify_payment', compact('order'))->render();
                break;
            case OrderStatus::SEARCHING_FOR_DRIVER:
                $response['title'] = 'Mencari Driver Tersedia';
                $response['body'] = view('market_aja_orders.searching_drivers', compact('order'))->render();
                break;
            default:
                $response['title'] = 'Update Status Order';
                $response['body'] = view('market_aja_orders.status_update', compact('order'))->render();
                break;
        }

        return $response;
    }

    /*
     * Settings
     */
    public function settings(Request $request)
    {
        $data = Setting::all();
        $settings = [];

        foreach ($data as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        return view('market_aja_orders.settings', ['settings' => (object)$settings]);
    }

    /*
     * Calculate fees
     */
    public function calculateFees(Request $request)
    {
        $distance = $request->get('distance');
        $subtotal = $request->get('subtotal');
        $deliveryFee = (float)PriceUtil::calculateDeliveryFee($distance);
        $serviceFee = (float)PriceUtil::calculateServiceFee($distance);

        return [
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'total' => $subtotal + $deliveryFee + $serviceFee
        ];
    }

    /**
     * Cancel the order
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $order = StoreOrder::findOrFail($id);
        $reason = $request->get('reason');

        Gate::authorize('update', $order);

        if ($order->status === OrderStatus::COMPLETED->value) {
            return response()->json([
                'status' => false,
                'message' => 'Order sudah selesai, tidak bisa dibatalkan.'
            ], 400);
        }

        try {
            $order->status = OrderStatus::CANCELED->value;
            $order->canceled_from = 'ADMIN';
            $order->canceled_by = $user->id;
            $order->canceled_reason = $reason;
            $order->canceled_at = Carbon::now()->toDateTimeString();
            $order->save();
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order berhasil dibatalkan.'
        ]);
    }
}
