<?php

namespace App\Http\Controllers\Api\Hapi\KirimAja;

use App\Core\Http\ApiResponse;
use App\Enums\ErrorCodesEnum;
use App\Enums\KirimAja\OrderPaymentMethod;
use App\Enums\KirimAja\OrderPaymentStatus;
use App\Enums\KirimAja\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\ShipmentOrder;
use App\Models\ShipmentOrderDestination;
use App\Utils\DistanceUtil;
use App\Utils\LocationUtil;
use App\Utils\PaymentMethodUtil;
use App\Utils\PriceUtil;
use App\Utils\SettingsUtil;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\File;

class ShippingOrdersApiController extends Controller
{
    /**
     * Calculate shipping fees
     */
    public function calculateFees(Request $request)
    {
        $validation = $this->validateRoutes($request);

        if ($validation !== true) {
            return $validation;
        }

        try {
            $response = new ApiResponse();
            $originLatitude = $request->get('origin')['latitude'];
            $originLongitude = $request->get('origin')['longitude'];
            $destinations = $request->get('destination');

            $totalDistance = 0;
            $lastPositionLatitude = $originLatitude;
            $lastPositionLongitude = $originLongitude;

            foreach ($destinations as $destination) {
                $query = LocationUtil::calculateDistance(
                    $lastPositionLatitude,
                    $lastPositionLongitude,
                    $destination['latitude'],
                    $destination['longitude']
                );

                if ($query === false) {
                    return $response
                        ->setStatusCode(400)
                        ->setStatus(false)
                        ->setMessage('gagal menghitung total jarak');
                }

                $totalDistance += $query['distance'];
                $lastPositionLatitude = $destination['latitude'];
                $lastPositionLongitude = $destination['longitude'];
            }

            // Calculate fee
            $fees = [
                'shipping_fee' => (float)PriceUtil::calculateDeliveryFee($totalDistance),
                'service_fee' => (float)PriceUtil::calculateServiceFee($totalDistance),
                'distance' => $totalDistance,
            ];

            return response()->json($fees);

        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    /*
     * Validate routes
     */
    private function validateRoutes(Request $request): ApiResponse|bool
    {
        // Validate request
        // TODO: Implement captcha to prevent spam
        $request->validate([
            'origin' => ['required'],
            'origin.latitude' => ['required', 'numeric'],
            'origin.longitude' => ['required', 'numeric'],
            'origin.address' => ['required', 'string'],
            'destination' => ['required', 'array'],
            'destination.*.latitude' => ['required', 'numeric'],
            'destination.*.longitude' => ['required', 'numeric'],
            'destination.*.address' => ['required', 'string'],
        ]);

        $response = new ApiResponse();

        try {
            // Get origin location
            $originLatitude = $request->get('origin')['latitude'];
            $originLongitude = $request->get('origin')['longitude'];

            // Check if sender location is under covered distance
            [$baseLatitude, $baseLongitude] = [-0.8614496277128488, 134.06200319981008];
            $maxDistance = SettingsUtil::getMaxDistanceCovered();

            // Check if origin is too far
            $distanceFromOrigin = DistanceUtil::toKilometers($baseLatitude, $baseLongitude, $originLatitude, $originLongitude);

            if ($distanceFromOrigin > $maxDistance) {
                return $response
                    ->setStatusCode(400)
                    ->setStatus(false)
                    ->setMessage('lokasi pickup terlalu jauh');
            }

            // Check destination
            // Loop thru destinations and check whether its under covered distance
            $destinations = $request->get('destination');

            foreach ($destinations as $destination) {
                // Check if destination is too far
                $distanceFromDestination = DistanceUtil::toKilometers($baseLatitude, $baseLongitude, $destination['latitude'], $destination['longitude']);

                if ($distanceFromDestination > $maxDistance) {
                    return $response
                        ->setStatusCode(400)
                        ->setStatus(false)
                        ->setMessage('lokasi pengantaran terlalu jauh');
                }
            }

            return true;

        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Submitted shipping order
     */
    public function submit(Request $request)
    {
        // Validate Routes
        $validation = $this->validateRoutes($request);

        if ($validation !== true) {
            return $validation;
        }

        // Validate request
        $request->validate([
            'sender_name' => ['required', 'string', 'max:64'],
            'sender_phone' => ['required', 'string', 'max:15'],
            'recipient_name' => ['required', 'string', 'max:64'],
            'recipient_phone' => ['required', 'string', 'max:15'],
            'item_details' => ['required', 'string', 'max:64'],
            'item_weight' => ['required', 'numeric', 'max:20'],
        ]);

        // Begin transaction
        $response = new ApiResponse();
        $operationalStatus = SettingsUtil::getOperationalStatus();
        $user = Auth::user();

        $originAddress = $request->get('origin')['address'];
        $originLatitude = $request->get('origin')['latitude'];
        $originLongitude = $request->get('origin')['longitude'];
        $destinations = $request->get('destination');

        $totalDistance = 0;
        $lastPositionLatitude = $originLatitude;
        $lastPositionLongitude = $originLongitude;

        foreach ($destinations as $destination) {
            $query = LocationUtil::calculateDistance(
                $lastPositionLatitude,
                $lastPositionLongitude,
                $destination['latitude'],
                $destination['longitude']
            );

            if ($query === false) {
                return $response
                    ->setStatusCode(400)
                    ->setStatus(false)
                    ->setMessage('gagal menghitung total jarak');
            }

            $totalDistance += $query['distance'];
            $lastPositionLatitude = $destination['latitude'];
            $lastPositionLongitude = $destination['longitude'];
        }

        // is operational status on?
        if ($operationalStatus === 'CLOSED') {
            return $response->setStatusCode(422)
                ->setMessage('Mohon maaf, kami sedang tidak beroperasi. Silahkan coba lagi nanti.');
        }

        // Validate payment method
        $paymentMethod = OrderPaymentMethod::from($request->get('payment_method'))->name;

        if (!PaymentMethodUtil::isEnabled($paymentMethod)) {
            return $response->setStatusCode(422)
                ->setMessage(ErrorCodesEnum::PAYMENT_METHOD_TEMPORARILY_DISABLED->value);
        }

        // Validate customer ongoing orders
        $maxOngoingOrders = SettingsUtil::getMaxOngoingOrders();
        $ongoingOrders = ShipmentOrder::ofStatusCategory('activeForCustomer')
            ->where('customer_id', $user->id)
            ->count();

        if ($ongoingOrders >= $maxOngoingOrders) {
            return $response->setStatusCode(422)
                ->setMessage("Order anda dibatasi");
        }

        // Calculate fee
        $deliveryFee = (float)PriceUtil::calculateDeliveryFee($totalDistance);
        $serviceFee = (float)PriceUtil::calculateServiceFee($totalDistance);

        $paymentMethod = $request->get('payment_method');
        $isCashPayment = ($paymentMethod === OrderPaymentMethod::CASH_BY_SENDER->value || $paymentMethod === OrderPaymentMethod::CASH_BY_RECIPIENT->value);

        try {
            DB::beginTransaction();

            // Create order
            $order = ShipmentOrder::create([
                'order_number' => SettingsUtil::generateKirimAjaOrderNumber(),
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'total' => $deliveryFee + $serviceFee,
                'distance' => $totalDistance,
                'status' => $isCashPayment
                    ? OrderStatus::SEARCHING_FOR_DRIVER
                    : OrderStatus::WAITING_FOR_CUSTOMER_PAYMENT,
                'payment_status' => $isCashPayment
                    ? OrderPaymentStatus::from($paymentMethod - 1)
                    : OrderPaymentStatus::WAITING_FOR_PAYMENT,
                'payment_method' => $paymentMethod,
                'sender_address' => $originAddress,
                'sender_latitude' => $originLatitude,
                'sender_longitude' => $originLongitude,
                'sender_name' => $request->get('sender_name'),
                'sender_phone' => $request->get('sender_phone'),
                'recipient_name' => $request->get('recipient_name'),
                'recipient_phone' => $request->get('recipient_phone'),
                'item_details' => $request->get('item_details'),
                'item_weight' => $request->get('item_weight'),
                'status_text' => '',
                'note_to_driver' => $request->get('note_to_driver'),
                'customer_id' => $user->id,
            ]);

            // Store destinations
            foreach ($destinations as $destination) {
                ShipmentOrderDestination::create([
                    'address' => $destination['address'],
                    'latitude' => $destination['latitude'],
                    'longitude' => $destination['longitude'],
                    'shipment_order_id' => $order->id,
                ]);
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
     * Get customer order list
     */
    public function getOrders(Request $request)
    {
        $statusCategory = $request->get('statusCategory');
        $perPage = $request->get('perPage') ?? 10;

        $user = Auth::user();

        return ShipmentOrder::ofStatusCategory($statusCategory)
            ->where('customer_id', $user->id)
            ->with('destinations')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(min($perPage, 10));
    }


    // =============================================================================

    /**
     * Get customer order
     */
    public function getOrder(Request $request, string $orderId)
    {
        $user = Auth::user();
        $order = ShipmentOrder::with([
            'destinations',
            'driver:drivers.id,code,name,plate_number,vehicle_model,photo_path'
        ])
            ->findOrFail($orderId);

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        return $order;
    }


    /**
     * Upload payment proof
     */
    public function uploadPaymentProof(Request $request, string $orderId)
    {
        $request->validate([
            'file' => ['required', File::types(['png', 'jpg', 'jpeg', 'pdf'])->max('1mb')],
        ]);

        $order = ShipmentOrder::findOrFail($orderId);
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        try {
            $file = $request->file('file');
            $extension = $file->extension();
            $path = $file->storeAs('shipment/payment_proofs', "{$orderId}_$user->id.$extension", 'public');

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
        $order = ShipmentOrder::findOrFail($orderId);

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

    /**
     * Rate order
     */
    public function rateOrder(Request $request, string $orderId)
    {
        $request->validate([
            'order_id' => ['required', 'exists:shipment_orders,id'],
            'driver_rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'driver_review' => ['nullable', 'string', 'max:160'],
        ]);

        $order = ShipmentOrder::findOrFail($orderId);
        $user = Auth::user();

        if ($order->customer_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if ($order->status !== OrderStatus::COMPLETED->value) {
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
                'order_type' => ShipmentOrder::class,
            ]);

            $order->is_rated = true;
            $order->save();

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json([], 204);
    }
}
