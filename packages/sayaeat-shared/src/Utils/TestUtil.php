<?php

namespace SayaEat\Shared\Utils;

class TestUtil
{
    public static function test()
    {
        $driver = \App\Models\Driver::find('01j0bvf46tes69xm34csxrt82n');
        $order = \App\Models\Order::first();

        $driver->notify(new \App\Notifications\Horus\NewOrderOffer($order, $driver));
    }
}
