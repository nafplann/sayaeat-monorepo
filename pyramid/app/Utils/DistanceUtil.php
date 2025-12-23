<?php

namespace App\Utils;

const EARTH_RADIUS = 6371;
const KILOMETER_TO_MILE = 0.621371192;

class DistanceUtil
{
    /**
     * @param float $fromLat
     * @param float $fromLon
     * @param float $toLat
     * @param float $toLon
     *
     * @return float
     */
    public static function toKilometers($fromLat, $fromLon, $toLat, $toLon)
    {
        return self::calculate($fromLat, $fromLon, $toLat, $toLon);
    }

    /**
     * @param float $fromLat
     * @param float $fromLon
     * @param float $toLat
     * @param float $toLon
     *
     * @return float
     */
    private static function calculate($fromLat, $fromLon, $toLat, $toLon)
    {
        $dLat = deg2rad($toLat - $fromLat);
        $dLon = deg2rad($toLon - $fromLon);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        $d = EARTH_RADIUS * $c;

        return round($d);
    }

    /**
     * @param float $fromLat
     * @param float $fromLon
     * @param float $toLat
     * @param float $toLon
     *
     * @return float
     */
    public static function toMiles($fromLat, $fromLon, $toLat, $toLon)
    {
        $distance = self::calculate($fromLat, $fromLon, $toLat, $toLon);

        return round($distance * KILOMETER_TO_MILE);
    }
}
