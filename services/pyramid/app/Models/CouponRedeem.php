<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CouponRedeem extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'customer_id',
        'coupon_id',
        'order_id',
        'order_type'
    ];

    public function order(): MorphTo
    {
        return $this->morphTo();
    }
}
