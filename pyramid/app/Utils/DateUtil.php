<?php

namespace App\Utils;

use Carbon\Carbon;

class DateUtil
{
    public static function toUserLocalTime(Carbon|string $date, string $timezone = 'Asia/Jayapura'): string
    {
        if (!$date instanceof Carbon) {
            $date = new Carbon($date);
        }

        return $date->setTimezone($timezone)
            ->format('d-m-Y H:i:s');
    }
}
