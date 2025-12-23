<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemAddon extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'price',
        'order_item_id',
        'menu_addon_id',
        'menu_addon_category_id'
    ];

    public function order_item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
