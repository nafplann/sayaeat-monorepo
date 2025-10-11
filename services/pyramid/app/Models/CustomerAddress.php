<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'label',
        'address',
        'latitude',
        'longitude',
        'default',
        'customer_id'
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
