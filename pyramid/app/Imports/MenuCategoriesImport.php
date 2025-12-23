<?php

namespace App\Imports;

use App\Models\Menu;
use App\Models\MenuCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;

class MenuCategoriesImport implements ToModel, WithUpsertColumns, WithHeadingRow
{
    private string $merchant;

    public function __construct(string $merchant)
    {
        $this->merchant = $merchant;
    }

    public function model(array $row)
    {
        // TODO: Validate all required fields
        if (!$row['category']) {
            return null;
        }

        return new MenuCategory([
            'name' => $row['category'],
            'description' => $row['description'],
            'enabled' => $row['enabled'] === 'YES' ? 1 : 0,
            'sorting' => $row['sorting'],
            'merchant_id' => $this->merchant,
        ]);
    }

    public function upsertColumns(): array
    {
        return ['name', 'merchant_id'];
    }
}
