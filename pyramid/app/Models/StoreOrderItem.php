<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreOrderItem extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'remark',
        'quantity',
        'price',
        'total',
        'addons',
        'markup_amount',
        'discount',
        'addon_ids',
        'order_id',
        'product_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
