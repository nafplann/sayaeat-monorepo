<?php

namespace SayaEat\Shared\Utils;

use SayaEat\Shared\Enums\MakanAjaOrderStatus;
use SayaEat\Shared\Enums\OrderPaymentMethod;
use SayaEat\Shared\Enums\OrderPaymentStatus;

class MakanAjaOrderUtil
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

    public static function getStatusText(MakanAjaOrderStatus $status): string
    {
        return match ($status) {
            MakanAjaOrderStatus::CANCELED => 'Dibatalkan',
            MakanAjaOrderStatus::WAITING_FOR_CUSTOMER_PAYMENT => 'Menunggu Pembayaran',
            MakanAjaOrderStatus::WAITING_FOR_PAYMENT_VERIFICATION => 'Verifikasi Pembayaran',
            MakanAjaOrderStatus::WAITING_FOR_MERCHANT_CONFIRMATION => 'Menunggu Konfirmasi Restoran',
            MakanAjaOrderStatus::SEARCHING_FOR_DRIVER => 'Mencari Driver',
            MakanAjaOrderStatus::MERCHANT_PREPARING_ORDER => 'Pesanan Sedang Disiapkan',
            MakanAjaOrderStatus::READY_TO_PICKUP => 'Pesanan Siap Diambil',
            MakanAjaOrderStatus::ORDER_RECEIVED_BY_DRIVER => 'Pesanan Diterima Driver',
            MakanAjaOrderStatus::ON_DELIVERY => 'Pesanan Sedang Diantar',
            MakanAjaOrderStatus::COMPLETED => 'Pesanan Selesai',
            default => '',
        };
    }
}
