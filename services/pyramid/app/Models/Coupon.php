<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'code',
        'description',
        'max_per_customer',
        'total_quantity',
        'redeemed_quantity',
        'minimum_purchase',
        'discount_amount',
        'max_discount_amount',
        'discount_percentage',
        'valid_from',
        'valid_until',
        'is_platform_promotion',
        'is_enabled',
        'merchant_id',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedeem::class);
    }

    public function scopeValid(Builder $query, string $code): void
    {
        $query->where([
            'code' => $code,
            'is_enabled' => 1
        ])
            ->whereDate('valid_from', '<=', Carbon::today())
            ->whereDate('valid_until', '>=', Carbon::today());
    }
}
