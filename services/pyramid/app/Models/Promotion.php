<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Promotion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'banner_path',
        'start_date',
        'end_date',
    ];

    /**
     * Get the menu image
     */
    protected function bannerPath(): Attribute
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
}
