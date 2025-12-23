<?php

namespace App\Models;

use App\Enums\KirimAja\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ShipmentOrder extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'id',
        'order_number',
        'delivery_fee',
        'service_fee',
        'total',
        'distance',
        'status',
        'status_text',
        'payment_status',
        'payment_status_text',
        'payment_method',
        'customer_id',
        'sender_address',
        'sender_latitude',
        'sender_longitude',
        'sender_name',
        'sender_phone',
        'recipient_name',
        'recipient_phone',
        'item_details',
        'item_weight',
        'driver_id',
        'item_image_path',
        'payment_proof_path',
        'payment_confirmed_by',
        'note_to_driver',
        'canceled_from',
        'canceled_reason',
        'canceled_by',
        'paid_at',
        'canceled_at',
        'completed_at',
        'is_rated',
        'merchant_discount_amount',
        'platform_discount_amount',
        'coupon',
        'delivery_fee_discount',
        'order_discount'
    ];

    protected $appends = ['total_after_discount'];

    public function destinations(): HasMany
    {
        return $this->hasMany(ShipmentOrderDestination::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope a query to only include canceled orders.
     */
    public function scopeOfStatusCategory(Builder $query, string $statusCategory): void
    {
        switch ($statusCategory) {
            case 'activeForAdmin':
            case 'activeForCustomer':
                $query->where('status', '>', OrderStatus::CANCELED->value)
                    ->where('status', '<', OrderStatus::COMPLETED->value);
                break;
            case 'completed':
                $query->where('status', OrderStatus::COMPLETED->value);
                break;
            case 'canceled':
                $query->where('status', OrderStatus::CANCELED->value);
                break;
        }
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'order');
    }

    protected function totalAfterDiscount(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->total - $this->delivery_fee_discount - $this->order_discount;
            }
        );
    }
}

