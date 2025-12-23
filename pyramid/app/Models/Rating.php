<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Rating extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'model_type',
        'model_id',
        'order_type',
        'order_id',
        'rating',
        'review',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function order(): MorphTo
    {
        return $this->morphTo();
    }
}
