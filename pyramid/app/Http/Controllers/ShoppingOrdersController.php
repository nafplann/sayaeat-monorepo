<?php

namespace App\Http\Controllers;

use App\Core\AppFieldDef;
use App\Enums\PermissionsEnum;
use App\Models\ShoppingOrder;
use App\Utils\PriceUtil;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\Facades\DataTables;

class ShoppingOrdersController extends BaseController implements HasMiddleware
{
    public function __construct(ShoppingOrder $model)
    {
        $fieldDefs = [
            new AppFieldDef(
                column: 'customer_name',
                validationRulesForAdding: ['required', 'min:3'],
                validationRulesForEditing: ['required', 'min:3'],
            ),
            new AppFieldDef(
                column: 'customer_phone',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'pickup_location',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'drop_location',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'shopping_list',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'status',
                validationRulesForAdding: ['required', 'integer'],
            ),
            new AppFieldDef(
                column: 'distance',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'subtotal',
                validationRulesForAdding: ['required'],
                validationRulesForEditing: ['required'],
            ),
            new AppFieldDef(
                column: 'delivery_fee',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'service_fee',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'total',
                validationRulesForAdding: ['required'],
            ),
            new AppFieldDef(
                column: 'driver_id',
            ),
            new AppFieldDef(
                column: 'paid_by',
            ),
            new AppFieldDef(
                column: 'payment_method',
            ),
            new AppFieldDef(
                column: 'payment_status',
            ),
        ];

        parent::__construct(
            model: $model,
            module: 'shopping_orders',
            displayNameSingular: 'Shopping Order',
            displayNamePlural: 'Shopping Orders',
            fieldDefs: $fieldDefs,
        );

        $this->baseUrl = $baseUrl ?? url("manage/shopping-orders");
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_SHOPPING_ORDERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_SHOPPING_ORDERS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_SHOPPING_ORDERS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_SHOPPING_ORDERS->value), only: ['destroy']),
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $this->setFeesData($request);
        $request->validate($this->getValidationRulesForEditing());

        return parent::update($request, $id);
    }

    private function setFeesData(Request $request): void
    {
        $distance = $request->get('distance');
        $subtotal = $request->get('subtotal');

        $deliveryFee = PriceUtil::calculateBADeliveryFee($distance);
        $serviceFee = PriceUtil::calculateBAServiceFee($distance, $subtotal);

        // Calculate fees
        $request->request->set('delivery_fee', $deliveryFee);
        $request->request->set('service_fee', $serviceFee);
        $request->request->set('total', roundup_to_one_thousand($deliveryFee + $serviceFee + $subtotal));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->setFeesData($request);
        $request->validate($this->getValidationRulesForAdding());

        return parent::store($request);
    }

    /**
     * Calculate fees
     */
    public function fees(Request $request): JsonResponse
    {
        $distance = $request->get('distance') ?? 0;
        $subtotal = $request->get('subtotal') ?? 0;

        $deliveryFee = PriceUtil::calculateBADeliveryFee($distance);
        $serviceFee = PriceUtil::calculateBAServiceFee($distance, $subtotal);

        return response()->json([
            'delivery_fee' => display_price($deliveryFee),
            'service_fee' => display_price($serviceFee),
            'total' => display_price(roundup_to_one_thousand($deliveryFee + $serviceFee + $subtotal)),
        ]);
    }

    /**
     * Show whatsapp template
     */
    public function whatsappTemplate(Request $request)
    {
        $order = ShoppingOrder::findOrFail($request->get('id'));
        return view('shopping_orders.whatsapp_template', compact('order'));
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
            $query->ofStatusCategory($statusCategory);
        }

        if ($user->isSuperAdmin()) {
            // Handle filters
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
            ->editColumn('subtotal', function (ShoppingOrder $order) {
                return display_price($order->subtotal);
            })
            ->editColumn('delivery_fee', function (ShoppingOrder $order) {
                return display_price($order->delivery_fee);
            })
            ->editColumn('service_fee', function (ShoppingOrder $order) {
                return display_price($order->service_fee);
            })
            ->editColumn('total', function (ShoppingOrder $order) {
                return display_price($order->total);
            })
            ->editColumn('paid_by', function (ShoppingOrder $order) {
                return ucfirst(strtolower($order->paid_by));
            })
            ->editColumn('status', function (ShoppingOrder $order) {
                return match ($order->status) {
                    1 => '<span class="badge badge-primary" style="width: 100px;">ONGOING</span>',
                    2 => '<span class="badge badge-success" style="width: 100px;">COMPLETED</span>',
                    default => '<span class="badge badge-secondary" style="width: 100px;">DRAFT</span>',
                };
            })
            ->editColumn('payment_method', function (ShoppingOrder $order) {
                return match ($order->payment_method) {
                    1 => '<span class="badge badge-secondary" style="width: 100px;">Qris</span>',
                    2 => '<span class="badge badge-secondary" style="width: 100px;">Transfer</span>',
                    3 => '<span class="badge badge-secondary" style="width: 100px;">Tunai</span>',
                    default => '-',
                };
            })
            ->editColumn('payment_status', function (ShoppingOrder $order) {
                return match ($order->payment_status) {
                    0 => '<span class="badge badge-danger" style="width: 100px;">Belum Bayar</span>',
                    1 => '<span class="badge badge-success" style="width: 100px;">Lunas</span>',
                    default => '-',
                };
            })
            ->editColumn('driver', function (ShoppingOrder $order) {
                return $order->driver ? $order->driver->name : '';
            })
            ->editColumn('distance', function (ShoppingOrder $order) {
                return $order->distance . ' km';
            })
            ->editColumn('created_at', function (ShoppingOrder $order) use ($timezone) {
                $date = $order->created_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->editColumn('updated_at', function (ShoppingOrder $order) use ($timezone) {
                $date = $order->updated_at;

                if (!$date instanceof Carbon) {
                    $date = new Carbon($date);
                }

                $date->setTimezone($timezone);
                return $date->format('d-m-Y H:i:s');
            })
            ->escapeColumns([])
            ->toJson();
    }
}
