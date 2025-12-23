<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Core\Http\ApiResponse;
use App\Enums\ErrorCodesEnum;
use App\Enums\MakanAjaOrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;
use App\Enums\ServiceEnum;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuAddon;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use App\Utils\CouponUtil;
use App\Utils\LocationUtil;
use App\Utils\PaymentMethodUtil;
use App\Utils\PriceUtil;
use App\Utils\SettingsUtil;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\File;

class OrdersApiController extends Controller
{
    /**
     * Get customer order
     */
    public function getOrder(Request $request, string $orderId)
    {
        $user = Auth::user();
        $order = Order::with([
            'merchant:merchants.id,name,logo_path',
            'items.order_addons',
            'items.menu:menus.id,image_path',
            'driver:drivers.id,code,name,plate_number,vehicle_model,photo_path'
        ])
            ->findOrFail($orderId);

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        return $order;
    }


    /**
     * Get customer order list
     */
    public function getOrders(Request $request)
    {
        $statusCategory = $request->get('statusCategory');
        $perPage = $request->get('perPage') ?? 10;

        $user = Auth::user();

        return Order::ofStatusCategory($statusCategory)
            ->where('customer_id', $user->id)
            ->with([
                'items:order_id,name,quantity',
                'merchant:merchants.id,name,logo_path',
            ])
            ->orderBy('created_at', 'desc')
            ->simplePaginate(min($perPage, 10));
    }

    /**
     * Submitted order from hapi
     */
    public function submit(Request $request)
    {
        // Validate request
        $request->validate([
            'merchant' => ['required', 'exists:merchants,id'],
            'menus' => ['required', 'array'],
            'menus.*.id' => ['required', 'exists:menus'],
            'menus.*.addons' => ['array'],
            'menus.*.addons.*' => ['exists:menu_addons,id'],
            'paymentMethod' => ['required', 'integer'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        // Update user location based on request
        $address = get_user_default_address();
        $address->latitude = $request->get('latitude');
        $address->longitude = $request->get('longitude');
        $address->save();

        // Begin transaction
        $response = new ApiResponse();
        $user = $request->user();
        $latitude = $address->latitude;
        $longitude = $address->longitude;
        $maxDistance = SettingsUtil::getMaxDistanceCovered();
        $operationalStatus = SettingsUtil::getOperationalStatus();

        // is operational status on?
        if ($operationalStatus === 'CLOSED') {
            return $response->setStatusCode(422)
                ->setMessage('Mohon maaf, kami sedang tidak beroperasi. Silahkan coba lagi nanti.');
        }

        // Validate payment method
        $paymentMethod = OrderPaymentMethod::from($request->get('paymentMethod'))->name;

        if (!PaymentMethodUtil::isEnabled($paymentMethod)) {
            return $response->setStatusCode(422)
                ->setMessage(ErrorCodesEnum::PAYMENT_METHOD_TEMPORARILY_DISABLED->value);
        }

        // is merchant under covered distance?
        $merchant = Merchant::where('id', $request->get('merchant'))
            ->with('menus.addonCategories.addons')
            ->geofence($latitude, $longitude, 0, $maxDistance)
            ->firstOrFail();

        // is merchant closed?
        if ($merchant->status !== 1 || !$merchant->is_operating) {
            return $response->setStatusCode(422)
                ->setMessage('merchant sedang tutup');
        }

        // is all submitted menus belongs to merchant
        $menus = Menu::whereIn('id', collect($request->get('menus'))
            ->pluck('id'))
            ->get();

        $uniqueMerchants = array_unique([
            ...$menus->pluck('merchant_id')->toArray(),
            $merchant->id
        ]);

        if (count($uniqueMerchants) > 1) {
            return $response->setStatusCode(422)
                ->setMessage('menu tidak valid');
        }

        // is menu available?
        foreach ($menus as $menu) {
            if ($menu->status === 0) {
                return $response->setStatusCode(422)
                    ->setMessage("{$menu->name} sedang tidak tersedia");
            }
        }

        // Validate customer ongoing orders
        $maxOngoingOrders = SettingsUtil::getMaxOngoingOrders();
        $ongoingOrders = Order::ofStatusCategory('activeForCustomer')
            ->where('customer_id', $user->id)
            ->count();

        if ($ongoingOrders >= $maxOngoingOrders) {
            return $response->setStatusCode(422)
                ->setMessage("order anda dibatasi");
        }

        // Recalculate distance
        $location = LocationUtil::getUserDistanceFromMerchant($merchant);

        if ($location === false) {
            throw new Exception('cannot get user distance from merchant');
        }

        // Calculate fee
        $deliveryFee = (float)PriceUtil::calculateDeliveryFee($location['distance']);
        $serviceFee = (float)PriceUtil::calculateServiceFee($location['distance']);

        // Subtotal (item price + applied addons)
        $subtotal = 0;

        foreach ($request->get('menus') as $menuRequest) {
            $menu = $menus->firstWhere('id', $menuRequest['id']);
            $menu->append(['markup_amount', 'sell_price']);

            $addons = MenuAddon::whereIn('id', $menuRequest['addons'])
                ->sum('price');
            $subtotal += (($menu->sell_price + $addons) * $menuRequest['quantity']);
        }

        // Check coupon
        $couponCode = $request->get('coupon_code');
        $deliveryFeeDiscount = 0;
        $orderDiscount = 0;
        $coupon = null;

        if ($couponCode) {
            $coupon = CouponUtil::validate($couponCode);

            if (!$coupon) {
                return $response->setStatusCode(422)
                    ->setMessage("Kupon tidak valid");
            }

            $deliveryFeeDiscount = CouponUtil::calculateDeliveryFeeDiscount($coupon, $deliveryFee);
            $orderDiscount = CouponUtil::calculateOrderDiscount($coupon, $subtotal);
        }

        $totalDiscountFromCoupon = $deliveryFeeDiscount + $orderDiscount;

        // optional: is menu price changed?
        // optional: is addon available?
        // optional: is addon price changed?

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'order_number' => SettingsUtil::generateOrderNumber(),
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'subtotal' => $subtotal,
                'total' => ($subtotal + $deliveryFee + $serviceFee),
                'distance' => $location['distance'],
                'status' => $request->get('paymentMethod') === OrderPaymentMethod::CASH_ON_DELIVERY->value
                    ? MakanAjaOrderStatus::WAITING_FOR_MERCHANT_CONFIRMATION
                    : MakanAjaOrderStatus::WAITING_FOR_CUSTOMER_PAYMENT,
                'payment_status' => $request->get('paymentMethod') === OrderPaymentMethod::CASH_ON_DELIVERY->value
                    ? OrderPaymentStatus::COD_PAYMENT
                    : OrderPaymentStatus::WAITING_FOR_PAYMENT,
                'payment_method' => $request->get('paymentMethod'),
                'address_label' => $address->label,
                'address_detail' => $address->address,
                'address_latitude' => $latitude,
                'address_longitude' => $longitude,
                'service' => ServiceEnum::MAKAN_AJA,
                'status_text' => '',
                'note_to_driver' => $request->get('noteToDriver'),
                'merchant_id' => $merchant->id,
                'customer_id' => $user->id,
                'merchant_discount_amount' => $coupon && $coupon->is_platform_promotion ? 0 : $totalDiscountFromCoupon,
                'platform_discount_amount' => $coupon && $coupon->is_platform_promotion ? $totalDiscountFromCoupon : 0,
                'coupon' => $coupon ? $coupon->code : null,
                'delivery_fee_discount' => $deliveryFeeDiscount,
                'order_discount' => $orderDiscount,
            ]);

            // Create order items
            foreach ($request->get('menus') as $menuRequest) {
                $menu = $menus->firstWhere('id', $menuRequest['id']);
                $addons = MenuAddon::whereIn('id', $menuRequest['addons'])
                    ->get();
                $markupAmount = $merchant->enable_menu_markup
                    ? $menu->markup_amount * $menuRequest['quantity']
                    : 0;
                $total = (($menu->price + $addons->sum('price')) * $menuRequest['quantity']) + $markupAmount;

                $orderItem = OrderItem::create([
                    'name' => $menu->name,
                    'remark' => $menuRequest['remark'],
                    'quantity' => $menuRequest['quantity'],
                    'price' => $menu->price,
                    'total' => $total,
                    'addons' => implode(', ', $addons->pluck('name')->toArray()),
                    'addon_ids' => implode(', ', $addons->pluck('id')->toArray()),
                    'markup_amount' => $markupAmount,
                    'order_id' => $order->id,
                    'menu_id' => $menu->id,
                ]);

                // Create order item addons
                foreach ($addons as $addon) {
                    $addonItem = OrderItemAddon::create([
                        'name' => $addon->name,
                        'price' => $addon->price,
                        'order_item_id' => $orderItem->id,
                        'menu_addon_id' => $addon->id,
                        'menu_addon_category_id' => $addon->category_id
                    ]);
                }
            }

            // Increment Coupon
            if ($coupon) {
                $coupon->redemptions()->create([
                    'customer_id' => $user->id,
                    'order_id' => $order->id,
                    'order_type' => Order::class,
                ]);

                $coupon->increment('redeemed_quantity');
            }

            // All good, save db
            DB::commit();

            // Success
            return $response->setStatusCode(200)
                ->setStatus(true)
                ->setMessage('Order has been created');

        } catch (\Exception $e) {
            DB::rollBack();

            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Upload payment proof
     */
    public function uploadPaymentProof(Request $request, string $orderId)
    {
        $request->validate([
            'file' => ['required', File::types(['png', 'jpg', 'jpeg', 'pdf'])->max('1mb')],
        ]);

        $order = Order::findOrFail($orderId);
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $file = $request->file('file');
            $extension = $file->extension();
            $path = $file->storeAs('payment_proofs', "{$orderId}_$user->id.$extension", 'public');

            $order->payment_proof_path = $path;
            $order->status = MakanAjaOrderStatus::WAITING_FOR_PAYMENT_VERIFICATION->value;
            $order->paid_at = Carbon::now()->toDateTimeString();
            $order->payment_status = OrderPaymentStatus::VERIFYING_PAYMENT->value;
            $order->save();
        } catch (\Exception $e) {
            return response(400)->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json([]);
    }

    /**
     * Get customer order
     */
    public function cancelOrder(Request $request, string $orderId): JsonResponse
    {
        $user = Auth::user();
        $order = Order::findOrFail($orderId);

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== MakanAjaOrderStatus::WAITING_FOR_CUSTOMER_PAYMENT->value) {
            return response(400)->json(['status' => false, 'message' => 'Order cannot be canceled']);
        }

        try {
            $order->status = MakanAjaOrderStatus::CANCELED->value;
            $order->canceled_from = 'CUSTOMER';
            $order->canceled_by = $user->id;
            $order->canceled_at = Date::now();
            $order->save();
        } catch (\Exception $e) {
            return response(400)->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json([], 204);
    }

    /**
     * Rate order
     */
    public function rateOrder(Request $request, string $orderId)
    {
        $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'driver_rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'driver_review' => ['nullable', 'string', 'max:160'],
            'merchant_rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'merchant_review' => ['nullable', 'string', 'max:160'],
        ]);

        $order = Order::findOrFail($orderId);
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== MakanAjaOrderStatus::COMPLETED->value) {
            return response()->json(['status' => false, 'message' => 'Order belum selesai'], 400);
        }

        if ($order->is_rated) {
            return response()->json(['status' => false, 'message' => 'Order telah direview'], 400);
        }

        try {
            $order->driver->ratings()->create([
                'rating' => $request->get('driver_rating'),
                'review' => $request->get('driver_review'),
                'order_id' => $order->id,
                'order_type' => Order::class,
            ]);

            $order->merchant->ratings()->create([
                'rating' => $request->get('merchant_rating'),
                'review' => $request->get('merchant_review'),
                'order_id' => $order->id,
                'order_type' => Order::class,
            ]);

            $order->is_rated = true;
            $order->save();

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([], 204);
    }
}
