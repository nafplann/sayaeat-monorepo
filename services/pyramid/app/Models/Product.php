<?php

namespace App\Models;

use App\Enums\MarketAja\DiscountType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Product extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'details',
        'price',
        'unit',
        'minimum_purchase_quantity',
        'image_path',
        'status',
        'status_text',
        'sorting',
        'sku',
        'barcode',
        'condition',
        'prescription_required',
        'store_id'
    ];

    protected $appends = ['discount_amount'];

    public function scopeSearchByTerm(Builder $query, ?string $term): void
    {
        if ($term) {
            $query->where('name', 'LIKE', "%$term%");
        }
    }

    public function scopeSearchByCategory(Builder $query, ?string $category): void
    {
        if ($category) {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('product_category_id', $category);
            });
        }
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class);
    }

    public function getDiscountAttribute()
    {
        return $this->discounts()
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->first();
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(ProductDiscount::class);
    }

    public function getDiscountAmountAttribute()
    {
        $activeDiscount = $this->discount;

        if (!$activeDiscount) {
            return 0;
        }

        if ($activeDiscount->discount_type === DiscountType::FIXED->value) {
            return $activeDiscount->discount_amount;
        }

        return $this->price * $activeDiscount->discount_percentage / 100;
    }

    /**
     * Get the product image
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
     * Show sell price based on the price markup
     */
    protected function sellPrice(): Attribute
    {
        return new Attribute(
            get: function () {
                $enableMarkup = $this->store->enable_product_markup;
                $this->unsetRelation('store');

                if ($enableMarkup) {
                    return $this->price + ($this->price * config('wa_aja.product.markup_percentage') / 100);
                }

                return $this->price;
            }
        );
    }
}
