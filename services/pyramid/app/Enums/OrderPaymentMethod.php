<?php

namespace App\Enums;

enum OrderPaymentMethod: int
{
    case WALLET = 1;
    case CASH_ON_DELIVERY = 2;
    case BANK_TRANSFER = 3;
    case QRIS = 4;
}
