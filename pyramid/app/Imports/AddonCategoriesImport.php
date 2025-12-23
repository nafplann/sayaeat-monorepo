<?php

namespace App\Imports;

use App\Models\Menu;
use App\Models\MenuAddonCategory;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AddonCategoriesImport implements ToModel, WithHeadingRow
{
    private string $merchant;

    public function __construct(string $merchant)
    {
        $this->merchant = $merchant;
    }

    public function model(array $row)
    {
        // TODO: Validate all required fields
        if (!$row['addon_category_name']) {
            return null;
        }

        return new MenuAddonCategory([
            'name' => $row['addon_category_name'],
            'description' => $row['description'],
            'is_mandatory' => $row['mandatory'] === 'YES' ? 1 : 0,
            'max_selection' => $row['max_selection'],
            'sorting' => $row['sorting'],
            'merchant_id' => $this->merchant,
        ]);
    }
}
