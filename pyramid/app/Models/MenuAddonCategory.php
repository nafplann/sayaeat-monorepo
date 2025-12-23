<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuAddonCategory extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'is_mandatory',
        'max_selection',
        'sorting',
        'merchant_id',
    ];

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(MenuAddon::class, 'category_id');
    }
}
