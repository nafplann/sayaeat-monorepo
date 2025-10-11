<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuCategory extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'enabled',
        'sorting',
        'merchant_id'
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
