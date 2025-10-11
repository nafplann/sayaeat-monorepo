<?php

namespace App\Http\Controllers\Api\Hapi\MarketAja;

use App\Core\Http\ApiResponse;
use App\Enums\ErrorCodesEnum;
use App\Enums\MarketAja\OrderStatus;
use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreOrder;
use App\Models\StoreOrderItem;
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

class StoreOrdersApiController extends Controller
{
    /**
     * Get customer order
     */
    public function getOrder(Request $request, string $orderId)
    {
        $user = Auth::user();
        $order = StoreOrder::with([
            'store:stores.id,name,logo_path',
            'items.product:products.id,image_path',
            'driver:drivers.id,code,name,plate_number,vehicle_model'
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

        return StoreOrder::ofStatusCategory($statusCategory)
            ->where('customer_id', $user->id)
            ->with([
                'items:order_id,name,quantity',
                'store:stores.id,name,logo_path',
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
            'store' => ['required', 'exists:stores,id'],
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'exists:products'],
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

        // is store under covered distance?
        $store = Store::where('id', $request->get('store'))
            ->geofence($latitude, $longitude, 0, $maxDistance)
            ->firstOrFail();

        // is store closed?
        if ($store->status !== 1 || !$store->is_operating) {
            return $response->setStatusCode(422)
                ->setMessage('Toko sedang tutup');
        }

        $submittedProducts = collect($request->get('products'));

        // is all submitted products belongs to store
        $products = Product::whereIn('id', $submittedProducts->pluck('id'))
            ->get();

        $uniqueStores = array_unique([
            ...$products->pluck('store_id')->toArray(),
            $store->id
        ]);

        if (count($uniqueStores) > 1) {
            return $response->setStatusCode(422)
                ->setMessage('Salah satu produk tidak valid');
        }

        foreach ($products as $product) {
            // is product available?
            if ($product->status === 0) {
                return $response->setStatusCode(422)
                    ->setMessage("{$product->name} sedang tidak tersedia");
            }

            // min purchase quantity
            $submittedProductQuantity = $submittedProducts->firstWhere('id', $product->id)['quantity'];
            $minimumPurchaseQuantity = $product->minimum_purchase_quantity;
            $productUnit = $product->unit;

            if ($submittedProductQuantity < $minimumPurchaseQuantity) {
                return $response->setStatusCode(422)
                    ->setMessage("Pembelian minimum {$product->name} adalah $minimumPurchaseQuantity $productUnit");
            }
        }

        // Validate customer ongoing orders
        $maxOngoingOrders = SettingsUtil::getMaxOngoingOrders();
        $ongoingOrders = StoreOrder::ofStatusCategory('activeForCustomer')
            ->where('customer_id', $user->id)
            ->count();

        if ($ongoingOrders >= $maxOngoingOrders) {
            return $response->setStatusCode(422)
                ->setMessage("order anda dibatasi");
        }

        // Recalculate distance
        $location = LocationUtil::getUserDistanceFromMerchant($store);

        if ($location === false) {
            throw new Exception('Cannot get user distance from store');
        }

        // Calculate fee
        $deliveryFee = (float)PriceUtil::calculateDeliveryFee($location['distance']);
        $serviceFee = (float)PriceUtil::calculateServiceFee($location['distance']);

        // Subtotal (item price + applied addons)
        $subtotal = 0;
        $totalDiscount = 0;

        foreach ($request->get('products') as $productRequest) {
            $product = $products->firstWhere('id', $productRequest['id']);
            $product->append('sell_price');
            $totalDiscount += $product->discount_amount * $productRequest['quantity'];
            $subtotal += ($product->sell_price - $product->discount_amount) * $productRequest['quantity'];
        }

        // Validate max subtotal on payment method
        if ($request->get('paymentMethod') === OrderPaymentMethod::CASH_ON_DELIVERY->value) {
            $maxSubtotal = 300_000;

            if ($subtotal > $maxSubtotal) {
                return $response->setStatusCode(422)
                    ->setMessage('Total order melebihi batas maksimal COD');
            }
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

        // tbd: is product price changed?

        try {
            DB::beginTransaction();

            // Create order
            $order = StoreOrder::create([
                'order_number' => SettingsUtil::generateMarketAjaOrderNumber(),
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'discount_amount' => $totalDiscount,
                'subtotal' => $subtotal,
                'total' => ($subtotal + $deliveryFee + $serviceFee),
                'distance' => $location['distance'],
                'status' => $request->get('paymentMethod') === OrderPaymentMethod::CASH_ON_DELIVERY->value
                    ? OrderStatus::WAITING_FOR_STORE_CONFIRMATION
                    : OrderStatus::WAITING_FOR_CUSTOMER_PAYMENT,
                'payment_status' => $request->get('paymentMethod') === OrderPaymentMethod::CASH_ON_DELIVERY->value
                    ? OrderPaymentStatus::COD_PAYMENT
                    : OrderPaymentStatus::WAITING_FOR_PAYMENT,
                'payment_method' => $request->get('paymentMethod'),
                'address_label' => $address->label,
                'address_detail' => $address->address,
                'address_latitude' => $latitude,
                'address_longitude' => $longitude,
                'note_to_driver' => $request->get('noteToDriver'),
                'store_id' => $store->id,
                'customer_id' => $user->id,
                'merchant_discount_amount' => $coupon && $coupon->is_platform_promotion ? 0 : $totalDiscountFromCoupon,
                'platform_discount_amount' => $coupon && $coupon->is_platform_promotion ? $totalDiscountFromCoupon : 0,
                'coupon' => $coupon ? $coupon->code : null,
                'delivery_fee_discount' => $deliveryFeeDiscount,
                'order_discount' => $orderDiscount,
            ]);

            // Create order items
            foreach ($request->get('products') as $productRequest) {
                $product = $products->firstWhere('id', $productRequest['id']);
                $quantity = $productRequest['quantity'];
                $markupPercentage = config('wa_aja.product.markup_percentage') / 100;
                $markupAmount = $store->enable_product_markup
                    ? ($markupPercentage * $product->price * $quantity)
                    : 0;
                $total = ($product->price - $product->discount_amount + $markupAmount) * $quantity;

                StoreOrderItem::create([
                    'name' => $product->name,
                    'remark' => $productRequest['remark'],
                    'quantity' => $productRequest['quantity'],
                    'price' => $product->price,
                    'total' => $total,
                    'markup_amount' => $markupAmount,
                    'discount' => $product->discount_amount,
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ]);
            }

            // Increment Coupon
            if ($coupon) {
                $coupon->redemptions()->create([
                    'customer_id' => $user->id,
                    'order_id' => $order->id,
                    'order_type' => StoreOrder::class,
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

        $order = StoreOrder::findOrFail($orderId);
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $file = $request->file('file');
            $extension = $file->extension();
            $path = $file->storeAs('payment_proofs', "{$orderId}_$user->id.$extension", 'public');

            $order->payment_proof_path = $path;
            $order->status = OrderStatus::WAITING_FOR_PAYMENT_VERIFICATION->value;
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
        $order = StoreOrder::findOrFail($orderId);

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== OrderStatus::WAITING_FOR_CUSTOMER_PAYMENT->value) {
            return response(400)->json(['status' => false, 'message' => 'Order cannot be canceled']);
        }

        try {
            $order->status = OrderStatus::CANCELED->value;
            $order->canceled_from = 'CUSTOMER';
            $order->canceled_by = $user->id;
            $order->canceled_at = Date::now();
            $order->save();
        } catch (\Exception $e) {
            return response(400)->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json([], 204);
    }
}
