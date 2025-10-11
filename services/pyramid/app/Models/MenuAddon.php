<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAddon extends AuditableModel
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'price',
        'sku',
        'enabled',
        'sorting',
        'category_id',
    ];
}
