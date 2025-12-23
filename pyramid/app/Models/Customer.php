<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends AuditableModel
{
    use HasFactory, HasUlids, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'phone_number',
        'whatsapp_number',
        'address',
        'occupation',
        'latitude',
        'longitude',
        'map_link',
        'facebook_link',
        'instagram_link',
        'has_complete_profile',
        'firebase_uid',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function verification_codes(): HasMany
    {
        return $this->hasMany(VerificationCode::class, 'user_id', 'id');
    }

    public function routeNotificationForWebhook()
    {
        return env('WHATSAPP_NOTIFICATION_URL') . "/$this->phone_number";
    }
}
