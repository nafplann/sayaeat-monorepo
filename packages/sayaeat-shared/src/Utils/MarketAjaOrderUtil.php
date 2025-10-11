<?php

namespace SayaEat\Shared\Utils;

use SayaEat\Shared\Enums\MarketAja\OrderStatus;
use SayaEat\Shared\Enums\OrderPaymentMethod;
use SayaEat\Shared\Enums\OrderPaymentStatus;

class MarketAjaOrderUtil
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

    public static function getStatusText(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::CANCELED => 'Dibatalkan',
            OrderStatus::WAITING_FOR_CUSTOMER_PAYMENT => 'Menunggu Pembayaran',
            OrderStatus::WAITING_FOR_PAYMENT_VERIFICATION => 'Verifikasi Pembayaran',
            OrderStatus::WAITING_FOR_STORE_CONFIRMATION => 'Menunggu Konfirmasi Toko',
            OrderStatus::SEARCHING_FOR_DRIVER => 'Mencari Driver',
            OrderStatus::STORE_PREPARING_ORDER => 'Pesanan Sedang Disiapkan',
            OrderStatus::READY_TO_PICKUP => 'Pesanan Siap Diambil',
            OrderStatus::ORDER_RECEIVED_BY_DRIVER => 'Pesanan Diterima Driver',
            OrderStatus::ON_DELIVERY => 'Pesanan Sedang Diantar',
            OrderStatus::COMPLETED => 'Pesanan Selesai',
            default => '',
        };
    }
}
