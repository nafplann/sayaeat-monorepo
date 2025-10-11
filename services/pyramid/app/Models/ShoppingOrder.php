<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingOrder extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'customer_name',
        'customer_phone',
        'shopping_list',
        'delivery_fee',
        'service_fee',
        'subtotal',
        'total',
        'distance',
        'status',
        'status_text',
        'payment_status',
        'payment_status_text',
        'payment_method',
        'paid_by',
        'pickup_location',
        'drop_location',
        'driver_id',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Scope a query to only include specific orders.
     */
    public function scopeOfStatusCategory(Builder $query, string $statusCategory): void
    {
        if ($statusCategory === 'activeForAdmin') {
            $query->where('status', '<>', 2);
        }
    }
}
