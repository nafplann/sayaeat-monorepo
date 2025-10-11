<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Core\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Utils\LocationUtil;
use App\Utils\PriceUtil;
use App\Utils\SettingsUtil;
use Exception;
use Illuminate\Http\Request;

class MerchantsApiController extends Controller
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
            $merchant = Merchant::where('status', 1)
                ->where('slug', $slug)
                ->with(['menus' => function ($q) {
                    $q->where('status', 1);
                }, 'menus.addonCategories.addons', 'menu_categories'])
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->first();

            // Merchant is not found or too far away
            if (!$merchant) {
                return $response
                    ->setStatusCode(400)
                    ->setStatus(false)
                    ->setMessage('merchant is too far away');
            }

            $merchant->makeHidden(Merchant::$sensitiveFields);

            $merchant->menus->each(function ($menu) {
                $menu->append('sell_price');
            });

            return $response->setMessage('success')
                ->set('merchant', $merchant->toArray());

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
            $nearbyMerchants = Merchant::where('status', 1)
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->orderBy('distance', 'ASC')
                ->simplePaginate(min($perPage, 10));
            $nearbyMerchants->getCollection()->makeHidden(Merchant::$sensitiveFields);

            return $response->setMessage('success')
                ->set('merchants', $nearbyMerchants->toArray());
        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Calculate user distance to merchant
     */
    public function getDistance(Request $request, $slug)
    {
        [$latitude, $longitude] = get_user_coordinate();
        $maxDistance = SettingsUtil::getMaxDistanceCovered();

        $response = new ApiResponse();

        try {
            $merchant = Merchant::where('slug', $slug)
                ->with('menus.addonCategories.addons')
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->first();

            // Merchant is not found or is too far away
            if (!$merchant) {
                return $response
                    ->setStatusCode(400)
                    ->setStatus(false)
                    ->setMessage('merchant is too far away');
            }

            // Calculate distance
            $location = LocationUtil::getUserDistanceFromMerchant($merchant);

            if ($location === false) {
                throw new Exception('cannot get user distance from merchant');
            }

            // Calculate fee
            $fees = [
                'delivery_fee' => (float)PriceUtil::calculateDeliveryFee($location['distance']),
                'service_fee' => (float)PriceUtil::calculateServiceFee($location['distance']),
            ];

            return $response->setMessage('success')
                ->set('data', array_merge($fees, $location));

        } catch (Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }
}
