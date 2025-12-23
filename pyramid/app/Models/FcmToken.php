<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FcmToken extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'token',
        'user_type',
        'user_id',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }
}
