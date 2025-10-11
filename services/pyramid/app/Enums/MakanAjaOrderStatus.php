<?php

namespace App\Enums;

enum MakanAjaOrderStatus: int
{
    case CANCELED = 0;
    case WAITING_FOR_CUSTOMER_PAYMENT = 1;
    case WAITING_FOR_PAYMENT_VERIFICATION = 2;
    case WAITING_FOR_MERCHANT_CONFIRMATION = 3;
    case SEARCHING_FOR_DRIVER = 4;
    case MERCHANT_PREPARING_ORDER = 5;
    case READY_TO_PICKUP = 6;
    case ORDER_RECEIVED_BY_DRIVER = 7;
    case ON_DELIVERY = 8;
    case COMPLETED = 9;
}
