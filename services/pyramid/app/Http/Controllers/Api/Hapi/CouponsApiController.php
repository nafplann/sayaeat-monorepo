<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouponsApiController extends Controller
{
    /**
     * Show all resources
     */
    public function getAll(Request $request)
    {
        $user = Auth::user();
        $timezone = $user->timezone;

        $coupon = Coupon::where([
            'is_enabled' => 1
        ])
            ->where('redeemed_quantity', '<', DB::raw('total_quantity'))
            ->whereDate('valid_from', '<=', Carbon::today($timezone))
            ->whereDate('valid_until', '>=', Carbon::today($timezone))
            ->get();

        return response()->json($coupon);
    }

    /**
     * Show resource by code
     */
    public function show(Request $request, string $code)
    {
        $user = Auth::user();
        $timezone = $user->timezone;

        $coupon = Coupon::where([
            'code' => $code,
            'is_enabled' => 1
        ])
            ->whereDate('valid_from', '<=', Carbon::today($timezone))
            ->whereDate('valid_until', '>=', Carbon::today($timezone))
            ->firstOrFail();

        // Check redeemed
        if ($coupon->redeemed_quantity >= $coupon->total_quantity) {
            return response()->json([
                'message' => 'Batas penukaran kupon telah tercapai.'
            ], 403);
        }

        // Check max_per_customer
        $redeemed = $coupon->redemptions()
            ->where('customer_id', $user->id)
            ->count();

        if ($redeemed >= $coupon->max_per_customer) {
            return response()->json([
                'message' => 'Batas penukaran kupon telah tercapai.'
            ], 403);
        }

        return response()->json($coupon);
    }
}
