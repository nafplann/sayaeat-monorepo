<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Driver extends Model
{
    use HasFactory, HasUlids, HasApiTokens, Notifiable;

    protected $fillable = [
        'code',
        'name',
        'phone_number',
        'address',
        'employment_status',
        'vehicle_model',
        'plate_number',
        'map_link',
        'bank_name',
        'bank_account_holder',
        'bank_account_number',
        'photo_path',
    ];

    public function verification_codes(): HasMany
    {
        return $this->hasMany(VerificationCode::class, 'user_id', 'id');
    }

    public function fcm_tokens(): MorphMany
    {
        return $this->morphMany(FcmToken::class, 'user');
    }

    public function routeNotificationForWebhook()
    {
        return env('WHATSAPP_NOTIFICATION_URL') . "/$this->phone_number";
    }

    public function routeNotificationForFcm()
    {
        Log::info($this->fcm_tokens
            ->pluck('token')
            ->toArray());

        return $this->fcm_tokens
            ->pluck('token')
            ->toArray();
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'model');
    }

    /**
     * Get the merchant's logo
     */
    protected function photoPath(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (str_starts_with($value, 'http')) {
                    return $value;
                }
                return asset(Storage::url($value));
            },
        );
    }
}
