<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Malhal\Geographical\Geographical;

class Merchant extends AuditableModel
{
    use HasFactory, HasUlids, Geographical, Notifiable;

    public static $sensitiveFields = [
        'phone_number',
        'primary_whatsapp_number',
        'secondary_whatsapp_number',
        'bank_name',
        'bank_account_holder',
        'bank_account_number',
        'qris_link',
        'owner_id',
        'enable_menu_markup'
    ];

    protected static $kilometers = true;

    protected $fillable = [
        'name',
        'category',
        'phone_number',
        'address',
        'primary_whatsapp_number',
        'secondary_whatsapp_number',
        'bank_name',
        'bank_account_holder',
        'bank_account_number',
        'qris_link',
        'latitude',
        'longitude',
        'logo_path',
        'operating_hours',
        'status',
        'status_text',
        'owner_id',
        'banner_image',
        'slug'
    ];

    protected $hidden = [
        'operating_hours',
    ];

    protected $casts = [
        'operating_hours' => 'array'
    ];

    protected $appends = ['is_operating', 'operating_hour'];

    public static function openingAtDay(int $day, array $operatingHours): array
    {
        if (!$operatingHours || in_array(null, $operatingHours[$day])) {
            return [
                'isOpen' => false,
                'hours' => []
            ];
        }

        return [
            'isOpen' => true,
            'hours' => $operatingHours[$day],
        ];
    }

    public function getIsOperatingAttribute()
    {
        $user = auth()->user();
        $timezone = $user->timezone ?? 'Asia/Jayapura';
        $dayOfWeek = Carbon::now($timezone)->dayOfWeekIso;

        if (!$this->operating_hours) {
            return false;
        }

        [$open, $close] = $this->operating_hours[$dayOfWeek];

        if (!$open || !$close) {
            return false;
        }

        return Carbon::now($timezone)->between(
            Carbon::parse($open, $timezone),
            Carbon::parse($close, $timezone)
        );
    }

    public function getOperatingHourAttribute()
    {
        if (!$this->operating_hours) {
            return '-';
        }

        $user = auth()->user();
        $timezone = $user->timezone ?? 'Asia/Jayapura';
        $dayOfWeek = Carbon::now($timezone)->dayOfWeekIso;
        [$open, $close] = $this->operating_hours[$dayOfWeek];

        return $open && $close ? "$open - $close" : '-';
    }

    public function scopeHavingMenus(Builder $query, string $term): void
    {
        $query->where('name', 'LIKE', "%$term%")
            ->orWhereHas('menus', function ($q) use ($term) {
                $q->where('status', 1)
                    ->where('name', 'LIKE', "%$term%");
            })
            ->with('menus', function ($q) use ($term) {
                $q->where('status', 1)
                    ->where('name', 'LIKE', "%$term%")
                    ->limit(3);
            });
    }

    public function scopeOpensToday(Builder $query): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }

        $timezone = $user->timezone;
        $dayOfWeek = Carbon::now($timezone)->dayOfWeekIso;

        $query->whereRaw("JSON_VALUE(operating_hours, '$.\"$dayOfWeek\"[0]') IS NOT NULL")
            ->whereRaw("JSON_VALUE(operating_hours, '$.\"$dayOfWeek\"[1]') IS NOT NULL")
            ->whereRaw("TIME(JSON_VALUE(operating_hours, '$.\"$dayOfWeek\"[0]')) <= TIME(?)", [Carbon::now($timezone)->format('H:i')])
            ->whereRaw("TIME(JSON_VALUE(operating_hours, '$.\"$dayOfWeek\"[1]')) >= TIME(?)", [Carbon::now($timezone)->format('H:i')]);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function menu_categories(): HasMany
    {
        return $this->hasMany(MenuCategory::class);
    }

    public function routeNotificationForWebhook()
    {
        return env('WHATSAPP_NOTIFICATION_URL') . "/$this->phone_number";
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'model');
    }

    protected function logoPath(): Attribute
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

    protected function bannerImage(): Attribute
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
