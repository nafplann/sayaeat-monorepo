<?php

namespace App\Models;

use App\Enums\MakanAjaOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Order extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'order_number',
        'delivery_fee',
        'service_fee',
        'subtotal',
        'total',
        'distance',
        'status',
        'payment_status',
        'payment_method',
        'service',
        'status_text',
        'payment_status_text',
        'address_label',
        'address_detail',
        'address_latitude',
        'address_longitude',
        'payment_proof_path',
        'paid_at',
        'payment_confirmed_by',
        'note_to_driver',
        'merchant_id',
        'customer_id',
        'driver_id',
        'merchant_paid_by',
        'completed_at',
        'canceled_from',
        'canceled_by',
        'canceled_reason',
        'canceled_at',
        'is_rated',
        'merchant_discount_amount',
        'platform_discount_amount',
        'coupon',
        'delivery_fee_discount',
        'order_discount'
    ];

    protected $casts = [
        'canceled_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = ['total_after_discount'];

    /**
     * Scope a query to only include active orders.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', '!=', MakanAjaOrderStatus::CANCELED->value)
            ->where('status', '!=', MakanAjaOrderStatus::COMPLETED->value);
    }

    /**
     * Scope a query to only include completed orders.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', MakanAjaOrderStatus::COMPLETED->value);
    }

    /**
     * Scope a query to only include specific orders.
     */
    public function scopeOfStatusCategory(Builder $query, string $statusCategory): void
    {
        switch ($statusCategory) {
            case 'activeForAdmin':
            case 'activeForCustomer':
                $query->where('status', '>', MakanAjaOrderStatus::CANCELED->value)
                    ->where('status', '<', MakanAjaOrderStatus::COMPLETED->value);
                break;
            case 'activeForMerchant':
                $query->where('status', '>', MakanAjaOrderStatus::WAITING_FOR_PAYMENT_VERIFICATION->value)
                    ->where('status', '<', MakanAjaOrderStatus::COMPLETED->value);
                break;
            case 'completed':
                $query->where('status', MakanAjaOrderStatus::COMPLETED->value);
                break;
            case 'canceled':
                $query->where('status', MakanAjaOrderStatus::CANCELED->value);
                break;
        }
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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

