<?php

namespace App\Http\Controllers;

use App\Enums\MakanAjaOrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;
use App\Enums\PermissionsEnum;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Setting;
use App\Utils\MakanAjaOrderUtil;
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

class MakanAjaOrdersController extends BaseController implements HasMiddleware
{
    public function __construct(Order $model)
    {
        parent::__construct(
            model: $model,
            module: 'makan_aja_orders',
            displayNameSingular: 'Makan Aja Order',
            displayNamePlural: 'Makan Aja Orders',
            fieldDefs: [],
        );

        $this->baseUrl = $baseUrl ?? url("manage/makan-aja");
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
        $order = Order::findOrFail($id);
        $action = $request->get('action');
        $user = Auth::user();

        try {
            if ($action === 'payment-verified') {
                $order->status = MakanAjaOrderStatus::WAITING_FOR_MERCHANT_CONFIRMATION->value;
                $order->payment_status = OrderPaymentStatus::PAYMENT_RECEIVED->value;
                $order->payment_confirmed_by = $user->id;
                $order->save();
            }

            if ($action === 'driver-found') {
                $driver = Driver::findOrFail($request->get('driver_id'));
                $order->status = MakanAjaOrderStatus::MERCHANT_PREPARING_ORDER->value;
                $order->driver_id = $driver->id;
                $order->merchant_paid_by = $request->get('merchant_paid_by');
                $order->save();
            }

            if ($action === 'status-update') {
                if (!$request->get('driver_id')) throw new \Exception('Driver is required.');
                if (!$request->get('merchant_paid_by')) throw new \Exception('Merchant paid by is required.');
                if (!$request->get('status')) throw new \Exception('Status is required.');
                if (!$request->get('distance')) throw new \Exception('Distance is required.');
                if (!$request->get('payment_method')) throw new \Exception('Metode pembayaran tidak boleh kosong.');

                $distance = $request->get('distance');

                // Recalculate fee only if distance is updated
                if ($distance != $order->distance) {
                    $deliveryFee = (float)PriceUtil::calculateDeliveryFee($distance);
                    $serviceFee = (float)PriceUtil::calculateServiceFee($distance);
                    $order->distance = $distance;
                    $order->delivery_fee = $deliveryFee;
                    $order->service_fee = $serviceFee;
                    $order->total = $order->subtotal + $serviceFee + $deliveryFee;
                }

                $order->driver_id = $request->get('driver_id');
                $order->merchant_paid_by = $request->get('merchant_paid_by');
                $order->status = MakanAjaOrderStatus::from($request->get('status'))->value;
                $order->payment_method = OrderPaymentMethod::from($request->get('payment_method'));

                if ($order->status === MakanAjaOrderStatus::COMPLETED->value) {
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
            ->editColumn('merchant', function (Order $order) {
                return $order->merchant ? $order->merchant->name : '';
            })
            ->editColumn('customer', function (Order $order) {
                return $order->customer ? $order->customer->name : '';
            })
            ->editColumn('driver', function (Order $order) {
                return $order->driver ? $order->driver->name : '';
            })
            ->editColumn('menu_markup', function (Order $order) {
                return display_price($order->items->sum('markup_amount'));
            })
            ->editColumn('subtotal', function (Order $order) {
                return display_price($order->subtotal - $order->items->sum('markup_amount'));
            })
            ->editColumn('delivery_fee', function (Order $order) {
                return display_price($order->delivery_fee);
            })
            ->editColumn('service_fee', function (Order $order) {
                return display_price($order->service_fee);
            })
            ->editColumn('total', function (Order $order) {
                return display_price($order->total);
            })
//            ->editColumn('paid_by', function (Order $order) {
//                return ucfirst(strtolower($order->paid_by));
//            })
            ->editColumn('status_text', function (Order $order) {
                return MakanAjaOrderUtil::getStatusText(MakanAjaOrderStatus::from($order->status));
            })
            ->editColumn('payment_method', function (Order $order) {
                return MakanAjaOrderUtil::getPaymentMethodText(OrderPaymentMethod::from($order->payment_method));
            })
            ->editColumn('payment_status_text', function (Order $order) {
                return MakanAjaOrderUtil::getPaymentStatusText(OrderPaymentStatus::from($order->payment_status));
            })
            ->editColumn('distance', function (Order $order) {
                return $order->distance . ' km';
            })
            ->editColumn('created_at', function (Order $order) use ($timezone) {
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
        $order = Order::findOrFail($orderId);
        return view('makan_aja_orders.details', compact('order'));
    }

    /**
     * Process order
     */
    public function process(Request $request, string $orderId)
    {
        $order = Order::findOrFail($orderId);
        $orderStatus = MakanAjaOrderStatus::from($order->status);

        $response = ['title' => '', 'body' => ''];

        switch ($orderStatus) {
            case MakanAjaOrderStatus::WAITING_FOR_PAYMENT_VERIFICATION:
                $response['title'] = 'Verifikasi Pembayaran';
                $response['body'] = view('makan_aja_orders.verify_payment', compact('order'))->render();
                break;
            case MakanAjaOrderStatus::SEARCHING_FOR_DRIVER:
                $response['title'] = 'Mencari Driver Tersedia';
                $response['body'] = view('makan_aja_orders.searching_drivers', compact('order'))->render();
                break;
            default:
                $response['title'] = 'Update Status Order';
                $response['body'] = view('makan_aja_orders.status_update', compact('order'))->render();
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

        return view('makan_aja_orders.settings', ['settings' => (object)$settings]);
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
        $order = Order::findOrFail($id);
        $reason = $request->get('reason');

        Gate::authorize('update', $order);

        if ($order->status === MakanAjaOrderStatus::COMPLETED->value) {
            return response()->json([
                'status' => false,
                'message' => 'Order sudah selesai, tidak bisa dibatalkan.'
            ], 400);
        }

        try {
            $order->status = MakanAjaOrderStatus::CANCELED->value;
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
