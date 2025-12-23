<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentOrderDestination extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'address',
        'latitude',
        'longitude',
        'shipment_order_id',
    ];

    public function shipment_order(): BelongsTo
    {
        return $this->belongsTo(ShipmentOrder::class);
    }

}
