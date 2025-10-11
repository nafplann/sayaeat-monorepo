<?php

namespace App\Http\Controllers;

use App\Enums\MarketAja\OrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;
use App\Enums\PermissionsEnum;
use App\Models\StoreOrder;
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

class StoreOrdersController extends BaseController implements HasMiddleware
{
    public function __construct(StoreOrder $model)
    {
        parent::__construct(
            model: $model,
            module: 'store_orders',
            displayNameSingular: 'Store Order',
            displayNamePlural: 'Store Orders',
            fieldDefs: [],
        );
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_STORE_ORDERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_STORE_ORDERS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_STORE_ORDERS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_STORE_ORDERS->value), only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        $view = 'store_orders.browse';

        if ($user->isOwner() || $user->isAdmin()) {
            $view = 'store_orders.owner_browse';
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

        $storeIds = request()->get('store_ids');

        if ($user->isOwner()) {
            $ownerStoreIds = $user->stores
                ->pluck('id')
                ->toArray();
            $appliedStoreIds = $storeIds ? array_intersect($storeIds, $ownerStoreIds) : $ownerStoreIds;
            $query->whereIn('store_id', $appliedStoreIds);
        } else {
            if ($storeIds) {
                $query->whereIn('store_id', $storeIds);
            }
        }

        return DataTables::eloquent($query)
            ->editColumn('store', function (StoreOrder $order) {
                return $order->store->name;
            })
            ->editColumn('subtotal', function (StoreOrder $order) {
                return display_price($order->subtotal);
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
            ->editColumn('distance', function (StoreOrder $order) {
                return $order->distance . ' km';
            })
            ->editColumn('status', function (StoreOrder $order) {
                return OrderStatus::from($order->status)->name;
            })
            ->editColumn('payment_method', function (StoreOrder $order) {
                return OrderPaymentMethod::from($order->payment_method)->name;
            })
            ->editColumn('payment_status', function (StoreOrder $order) {
                return OrderPaymentStatus::from($order->payment_status)->name;
            })
            ->editColumn('created_at', function (StoreOrder $order) use ($timezone) {
                $date = $order->created_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->editColumn('updated_at', function (StoreOrder $order) use ($timezone) {
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
        $orders = StoreOrder::with(['items', 'store']);

        if ($user->isOwner()) {
            $storeIds = $user->stores->pluck('id')->toArray();
            $orders->whereIn('store_id', $storeIds);
        }

        $result = $orders->ofStatusCategory($statusCategory)
            ->orderBy('created_at', 'desc')
            ->simplePaginate(50);

        $result->each(function ($order) {
            $enableMenuMarkup = $order->store->enable_menu_markup;

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
        $order = StoreOrder::findOrFail($id);
        $action = $request->get('action');

        Gate::authorize('update', $order);

        try {
            if ($action === 'order-accepted') {
                $order->status = OrderStatus::SEARCHING_FOR_DRIVER->value;
                $order->save();
            }

            if ($action === 'order-ready') {
                $order->status = OrderStatus::READY_TO_PICKUP->value;
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
        $order = StoreOrder::findOrFail($id);
        $reason = $request->get('reason');

        Gate::authorize('update', $order);

        try {
            $order->status = OrderStatus::CANCELED->value;
            $order->canceled_from = 'STORE';
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
