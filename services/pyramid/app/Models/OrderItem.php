<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends AuditableModel
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
        'addon_ids',
        'order_id',
        'menu_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function order_addons(): HasMany
    {
        return $this->hasMany(OrderItemAddon::class);
    }
}
