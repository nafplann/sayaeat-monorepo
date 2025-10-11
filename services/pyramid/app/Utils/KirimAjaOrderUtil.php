<?php

namespace App\Utils;

use App\Enums\KirimAja\OrderPaymentMethod;
use App\Enums\KirimAja\OrderPaymentStatus;
use App\Enums\KirimAja\OrderStatus;

class KirimAjaOrderUtil
{
    public static function getPaymentMethodText(OrderPaymentMethod $method): string
    {
        return match ($method) {
            OrderPaymentMethod::WALLET => 'Wallet',
            OrderPaymentMethod::BANK_TRANSFER => 'Transfer Bank',
            OrderPaymentMethod::CASH_BY_SENDER => 'Cash dari Pengirim',
            OrderPaymentMethod::CASH_BY_RECIPIENT => 'Cash dari Penerima',
            OrderPaymentMethod::QRIS => 'QRIS',
            default => '',
        };
    }

    public static function getPaymentStatusText(OrderPaymentStatus $status): string
    {
        return match ($status) {
            OrderPaymentStatus::WAITING_FOR_PAYMENT => 'Menunggu Pembayaran',
            OrderPaymentStatus::VERIFYING_PAYMENT => 'Verifikasi Pembayaran',
            OrderPaymentStatus::CASH_BY_SENDER => 'Cash dari Pengirim',
            OrderPaymentStatus::CASH_BY_RECIPIENT => 'Cash dari Penerima',
            OrderPaymentStatus::PAYMENT_RECEIVED => 'Pembayaran Diterima',
            default => '',
        };
    }

    public static function getStatusText(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::CANCELED => 'Dibatalkan',
            OrderStatus::WAITING_FOR_CUSTOMER_PAYMENT => 'Menunggu Pembayaran',
            OrderStatus::WAITING_FOR_PAYMENT_VERIFICATION => 'Verifikasi Pembayaran',
            OrderStatus::SEARCHING_FOR_DRIVER => 'Mencari Driver',
            OrderStatus::DRIVER_GOING_TO_PICKUP_LOCATION => 'Driver Menuju Lokasi Pengambilan Paket',
            OrderStatus::ORDER_RECEIVED_BY_DRIVER => 'Paket Diterima Driver',
            OrderStatus::ON_DELIVERY => 'Paket Sedang Diantar',
            OrderStatus::COMPLETED => 'Pesanan Selesai',
            default => '',
        };
    }
}
