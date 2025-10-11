<?php

namespace App\Http\Controllers\Api\Hapi\MarketAja;

use App\Core\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Utils\LocationUtil;
use App\Utils\PriceUtil;
use App\Utils\SettingsUtil;
use Exception;
use Illuminate\Http\Request;

class StoresApiController extends Controller
{
    /**
     * Show resource by id
     */
    public function show(Request $request, string $slug)
    {
        [$latitude, $longitude] = get_user_coordinate();
        $maxDistance = SettingsUtil::getMaxDistanceCovered();

        $response = new ApiResponse();

        try {
            $store = Store::where([
                'status' => 1,
                'slug' => $slug
            ])
                ->with(['product_categories' => function ($query) {
                    $query
                        ->select('id', 'name', 'sorting', 'store_id')
                        ->where('enabled', 1)
                        ->orderBy('sorting', 'ASC');
                }])
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->first();

            // Store is too far
            if (!$store) {
                return $response
                    ->setStatusCode(400)
                    ->setStatus(false)
                    ->setMessage('store is too far away');
            }

            $store->makeHidden(Store::$sensitiveFields);
            return response()->json($store);

        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    public function sale(Request $request, string $slug)
    {
        [$latitude, $longitude] = get_user_coordinate();
        $maxDistance = SettingsUtil::getMaxDistanceCovered();

        $response = new ApiResponse();

        try {
            $store = Store::where([
                'status' => 1,
                'slug' => $slug
            ])
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->first();

            // Store is too far
            if (!$store) {
                return $response
                    ->setStatusCode(400)
                    ->setStatus(false)
                    ->setMessage('store is too far away');
            }

            $discounts = $store->discounts()
                ->with('products')
                ->get();

            $discounts->each(function ($discount) {
                $discount->products->append('sell_price');
            });

            $result = [];

            foreach ($discounts as $discount) {
                if (!array_key_exists($discount->name, $result)) {
                    $result[$discount->name] = $discount->toArray();
                    $result[$discount->name]['products'] = [];
                }

                $result[$discount->name]['products'] = array_merge($result[$discount->name]['products'], $discount->products->toArray());
            }

            return response()->json(array_values($result));

        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function nearby(Request $request)
    {
        $user = $request->user();

        $latitude = $user->latitude;
        $longitude = $user->longitude;
        $maxDistance = SettingsUtil::getMaxDistanceCovered();
        $perPage = $request->get('perPage') ?? 10;

        $response = new ApiResponse();

        try {
            $nearbyStores = Store::where('status', 1)
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->orderBy('distance', 'ASC')
                ->simplePaginate(min($perPage, 10));
            $nearbyStores->getCollection()->makeHidden(Store::$sensitiveFields);

            return $response->setMessage('success')
                ->set('stores', $nearbyStores->toArray());
        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Calculate user distance to store
     */
    public function getDistance(Request $request, $slug)
    {
        [$latitude, $longitude] = get_user_coordinate();
        $maxDistance = SettingsUtil::getMaxDistanceCovered();

        $response = new ApiResponse();

        try {
            $store = Store::where('slug', $slug)
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->first();

            // Store is too far
            if (!$store) {
                return $response
                    ->setStatusCode(400)
                    ->setStatus(false)
                    ->setMessage('store is too far away');
            }

            // Calculate distance
            $location = LocationUtil::getUserDistanceFromMerchant($store);

            if ($location === false) {
                throw new Exception('cannot get user distance from store');
            }

            // Calculate fee
            $fees = [
                'delivery_fee' => (float)PriceUtil::calculateDeliveryFee($location['distance']),
                'service_fee' => (float)PriceUtil::calculateServiceFee($location['distance']),
            ];

            return response()->json(array_merge($fees, $location));

        } catch (Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }
}
