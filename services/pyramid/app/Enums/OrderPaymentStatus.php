<?php

namespace App\Enums;

enum OrderPaymentStatus: int
{
    case WAITING_FOR_PAYMENT = 0;
    case VERIFYING_PAYMENT = 1;
    case COD_PAYMENT = 2;
    case PAYMENT_RECEIVED = 3;
}
