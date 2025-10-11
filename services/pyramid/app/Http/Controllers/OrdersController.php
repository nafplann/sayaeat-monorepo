<?php

namespace App\Http\Controllers;

use App\Enums\MakanAjaOrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;
use App\Enums\PermissionsEnum;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class OrdersController extends BaseController implements HasMiddleware
{
    public function __construct(Order $model)
    {
        parent::__construct(
            model: $model,
            module: 'orders',
            displayNameSingular: 'Order',
            displayNamePlural: 'Orders',
            fieldDefs: [],
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_ORDERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_ORDERS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_ORDERS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_ORDERS->value), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        $view = 'orders.browse';

        if ($user->isOwner() || $user->isAdmin()) {
            $view = 'orders.owner_browse';
        }

        return view($view, [
            'module' => $this->displayNamePlural,
            'baseUrl' => $this->baseUrl,
            'fieldDefs' => $this->fieldDefs,
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

        $merchantIds = request()->get('merchant_ids');

        if ($user->isOwner()) {
            $ownerMerchantIds = $user->merchants
                ->pluck('id')
                ->toArray();
            $appliedMerchantIds = $merchantIds ? array_intersect($merchantIds, $ownerMerchantIds) : $ownerMerchantIds;
            $query->whereIn('merchant_id', $appliedMerchantIds);
        } else {
            if ($merchantIds) {
                $query->whereIn('merchant_id', $merchantIds);
            }
        }

        return DataTables::eloquent($query)
            ->editColumn('merchant', function (Order $order) {
                return $order->merchant->name;
            })
            ->editColumn('subtotal', function (Order $order) {
                return display_price($order->subtotal);
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
            ->editColumn('distance', function (Order $order) {
                return $order->distance . ' km';
            })
            ->editColumn('status', function (Order $order) {
                return MakanAjaOrderStatus::from($order->status)->name;
            })
            ->editColumn('payment_method', function (Order $order) {
                return OrderPaymentMethod::from($order->payment_method)->name;
            })
            ->editColumn('payment_status', function (Order $order) {
                return OrderPaymentStatus::from($order->payment_status)->name;
            })
            ->editColumn('created_at', function (Order $order) use ($timezone) {
                $date = $order->created_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->editColumn('updated_at', function (Order $order) use ($timezone) {
                $date = $order->updated_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->toJson();
    }

    /**
     * Return order list by status category
     */
    public function list(Request $request): JsonResponse
    {
        $user = Auth::user();
        $statusCategory = $request->get('statusCategory');
        $orders = Order::with(['items', 'merchant']);

        if ($user->isOwner()) {
            $merchantIds = $user->merchants->pluck('id')->toArray();
            $orders->whereIn('merchant_id', $merchantIds);
        }

        $result = $orders->ofStatusCategory($statusCategory)
            ->orderBy('created_at', 'desc')
            ->simplePaginate(50);

        $result->each(function ($order) {
            $enableMenuMarkup = $order->merchant->enable_menu_markup;

            if ($enableMenuMarkup) {
                $totalMarkup = $order->items->sum('markup_amount');
                $order->subtotal = $order->subtotal - $totalMarkup;
            }
        });


        return response()->json($result);
    }

    /**
     * Process the order
     */
    public function process(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $action = $request->get('action');

        Gate::authorize('update', $order);

        try {
            if ($action === 'order-accepted') {
                $order->status = MakanAjaOrderStatus::SEARCHING_FOR_DRIVER->value;
                $order->save();
            }

            if ($action === 'order-ready') {
                $order->status = MakanAjaOrderStatus::READY_TO_PICKUP->value;
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
            'message' => 'Order berhasil di update.'
        ]);
    }

    /**
     * Cancel the order
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $order = Order::findOrFail($id);
        $reason = $request->get('reason');

        Gate::authorize('update', $order);

        try {
            $order->status = MakanAjaOrderStatus::CANCELED->value;
            $order->canceled_from = 'MERCHANT';
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
