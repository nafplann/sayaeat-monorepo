<?php

namespace App\Imports;

use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StoreProductsImport implements WithMultipleSheets
{
    private string $store;

    public function __construct(string $store)
    {
        $this->store = $store;

        // Clear cache
        Cache::flush();
    }

    public function sheets(): array
    {
        return [
            'Product Category' => new ProductCategoriesImport($this->store),
            'Product' => new ProductsImport($this->store),
        ];
    }
}
