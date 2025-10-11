<?php

namespace App\Enums\KirimAja;

enum OrderPaymentMethod: int
{
    case WALLET = 1;
    case BANK_TRANSFER = 2;
    case CASH_BY_SENDER = 3;
    case CASH_BY_RECIPIENT = 4;
    case QRIS = 5;
}
