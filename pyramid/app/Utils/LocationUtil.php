<?php

namespace App\Utils;

use App\Models\Merchant;
use App\Models\Store;

class LocationUtil
{
    public static function getUserDistanceFromMerchant(Merchant|Store $merchant): false|array
    {
        [$userLatitude, $userLongitude] = get_user_coordinate();

        $results = MapboxUtil::getDirectionMatrix([
            'latitude' => $merchant->latitude,
            'longitude' => $merchant->longitude,
        ], [
            'latitude' => $userLatitude,
            'longitude' => $userLongitude
        ]);

        if ($results === null) {
            return false;
        }

        return [
            'distance' => round($results->distances[0][1] / 1000, 1, PHP_ROUND_HALF_UP), // in km
            'duration' => ceil($results->durations[0][1] / 60), // in minute
        ];
    }

    public static function calculateDistance(float $fromLat, float $fromLon, float $toLat, float $toLon): false|array
    {
        $results = MapboxUtil::getDirectionMatrix([
            'latitude' => $fromLat,
            'longitude' => $fromLon,
        ], [
            'latitude' => $toLat,
            'longitude' => $toLon
        ]);

        if ($results === null) {
            return false;
        }

        return [
            'distance' => round($results->distances[0][1] / 1000, 1, PHP_ROUND_HALF_UP), // in km
            'duration' => ceil($results->durations[0][1] / 60), // in minute
        ];
    }
}
