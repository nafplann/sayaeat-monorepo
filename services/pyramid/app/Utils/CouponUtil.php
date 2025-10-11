<?php

namespace App\Utils;

use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

class CouponUtil
{
    public static function validate(string $code)
    {
        $user = Auth::user();

        $coupon = Coupon::valid($code)
            ->firstOrFail();

        if ($coupon->redeemed_quantity >= $coupon->total_quantity) {
            return null;
        }

        // Check max_per_customer
        $redeemed = $coupon->redemptions()
            ->where('customer_id', $user->id)
            ->count();

        if ($redeemed >= $coupon->max_per_customer) {
            return null;
        }

        return $coupon;
    }

    public static function calculateDeliveryFeeDiscount(Coupon $coupon, float $deliveryFee)
    {
        if ($coupon->discount_type !== 1) {
            return 0;
        }

        if ($coupon->discount_amount) {
            return $coupon->discount_amount;
        }

        if (!$coupon->discount_percentage) {
            return 0;
        }

        $discount = $deliveryFee * ($coupon->discount_percentage / 100);
        return min($coupon->max_discount_amount, $discount);
    }

    public static function calculateOrderDiscount(Coupon $coupon, float $subtotal)
    {
        if ($coupon->discount_type !== 2) {
            return 0;
        }

        if ($coupon->discount_amount) {
            return $coupon->discount_amount;
        }

        if (!$coupon->discount_percentage) {
            return 0;
        }

        $discount = $subtotal * ($coupon->discount_percentage / 100);
        return min($coupon->max_discount_amount, $discount);
    }
}
