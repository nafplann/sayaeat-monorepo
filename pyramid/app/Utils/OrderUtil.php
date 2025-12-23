<?php

namespace App\Utils;

use App\Enums\OrderPaymentMethod;
use App\Enums\OrderPaymentStatus;

class OrderUtil
{
    public static function getPaymentMethodText(OrderPaymentMethod $method): string
    {
        return match ($method) {
            OrderPaymentMethod::WALLET => 'Wallet',
            OrderPaymentMethod::CASH_ON_DELIVERY => 'Cash On Delivery',
            OrderPaymentMethod::BANK_TRANSFER => 'Transfer Bank',
            OrderPaymentMethod::QRIS => 'QRIS',
            default => '',
        };
    }

    public static function getPaymentStatusText(OrderPaymentStatus $status): string
    {
        return match ($status) {
            OrderPaymentStatus::WAITING_FOR_PAYMENT => 'Menunggu Pembayaran',
            OrderPaymentStatus::VERIFYING_PAYMENT => 'Verifikasi Pembayaran',
            OrderPaymentStatus::COD_PAYMENT => 'Cash On Delivery',
            OrderPaymentStatus::PAYMENT_RECEIVED => 'Pembayaran Diterima',
            default => '',
        };
    }
}
