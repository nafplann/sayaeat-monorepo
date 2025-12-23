<?php

namespace App\Models;

use App\Enums\RolesEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements Auditable
{
    use HasUlids, HasFactory, Notifiable, HasRoles, HasApiTokens, \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'account_id',
        'client_secret',
        'timezone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RolesEnum::SUPER_ADMIN->value);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(RolesEnum::ADMIN->value);
    }

    public function isOwner(): bool
    {
        return $this->hasRole(RolesEnum::OWNER->value);
    }

    public function merchants(): HasMany
    {
        return $this->hasMany(Merchant::class, 'owner_id');
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    public function fcm_tokens(): MorphMany
    {
        return $this->morphMany(FcmToken::class, 'user');
    }

    public function routeNotificationForOneSignal()
    {
        return ['include_external_user_ids' => $this->id];
    }

    public function routeNotificationForWebhook()
    {
        return env('WHATSAPP_NOTIFICATION_URL') . "/$this->phone_number";
    }
}
