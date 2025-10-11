<?php

namespace SayaEat\Shared\Enums\KirimAja;

enum OrderStatus: int
{
    case CANCELED = 0;
    case WAITING_FOR_CUSTOMER_PAYMENT = 1;
    case WAITING_FOR_PAYMENT_VERIFICATION = 2;
    case SEARCHING_FOR_DRIVER = 3;
    case DRIVER_GOING_TO_PICKUP_LOCATION = 4;
    case ORDER_RECEIVED_BY_DRIVER = 5;
    case ON_DELIVERY = 6;
    case COMPLETED = 7;
}
