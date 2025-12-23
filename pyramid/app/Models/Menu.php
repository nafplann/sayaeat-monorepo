<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Menu extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image_path',
        'status',
        'status_text',
        'sorting',
        'category_id',
        'merchant_id'
    ];

    public function scopeSearchByTerm(Builder $query, string $term): void
    {
        $query->where('name', 'LIKE', "%$term%");
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class);
    }

    public function addonCategories(): BelongsToMany
    {
        return $this->belongsToMany(MenuAddonCategory::class);
    }

    /**
     * Get the menu image
     */
    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (str_starts_with($value, 'http')) {
                    return $value;
                }

                if ($value) {
                    return asset(Storage::url($value));
                }

                return '';
            },
        );
    }

    /**
     * Show sell price based on the merchant markup
     */
    protected function sellPrice(): Attribute
    {
        return new Attribute(
            get: function () {
                $menuPrice = $this->price;
                return $menuPrice + $this->markup_amount;
            }
        );
    }

    /**
     * Show sell price based on the merchant markup
     */
    protected function markupAmount(): Attribute
    {
        return new Attribute(
            get: function () {
                $menuPrice = $this->price;
                $enableMenuMarkup = $this->merchant->enable_menu_markup;
                $markupAmount = config('menu.markup_amount');

                $this->unsetRelation('merchant');

                if (!$enableMenuMarkup) {
                    return 0;
                }

                if ($menuPrice <= 5_000) {
                    return min(1_000, $markupAmount);
                } else if ($menuPrice <= 10_000) {
                    return $markupAmount;
                } else if ($menuPrice <= 25_000) {
                    return $markupAmount * 2;
                } else if ($menuPrice <= 40_000) {
                    return $markupAmount * 3;
                } else if ($menuPrice <= 55_000) {
                    return $markupAmount * 4;
                } else if ($menuPrice <= 70_000) {
                    return $markupAmount * 5;
                } else if ($menuPrice <= 85_000) {
                    return $markupAmount * 6;
                } else {
                    return $markupAmount * 7;
                }
            }
        );
    }

    /**
     * Append merchant slug
     */
    protected function merchantSlug(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->merchant->slug;
            }
        );
    }
}
