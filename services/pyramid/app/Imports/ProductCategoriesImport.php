<?php

namespace App\Imports;

use App\Models\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;

class ProductCategoriesImport implements ToModel, WithUpsertColumns, WithHeadingRow
{
    private string $store;

    public function __construct(string $store)
    {
        $this->store = $store;
    }

    public function model(array $row)
    {
        // TODO: Validate all required fields
        if (!$row['category']) {
            return null;
        }

        return new ProductCategory([
            'name' => $row['category'],
            'description' => $row['description'],
            'enabled' => $row['enabled'] === 'YES' ? 1 : 0,
            'sorting' => $row['sorting'],
            'store_id' => $this->store,
        ]);
    }

    public function upsertColumns(): array
    {
        return ['name', 'store_id'];
    }
}
