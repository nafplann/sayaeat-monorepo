<?php

namespace App\Enums\KirimAja;

enum OrderPaymentStatus: int
{
    case WAITING_FOR_PAYMENT = 0;
    case VERIFYING_PAYMENT = 1;
    case CASH_BY_SENDER = 2;
    case CASH_BY_RECIPIENT = 3;
    case PAYMENT_RECEIVED = 4;
}
