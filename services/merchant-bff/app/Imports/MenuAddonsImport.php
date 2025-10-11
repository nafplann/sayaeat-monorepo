<?php

namespace App\Imports;

use App\Models\Menu;
use App\Models\MenuAddon;
use App\Models\MenuAddonCategory;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MenuAddonsImport implements ToModel, WithHeadingRow
{
    private string $merchant;

    public function __construct(string $merchant)
    {
        $this->merchant = $merchant;
    }

    public function model(array $row)
    {
        // TODO: Validate all required fields
        if (!$row['addon_name']) {
            return null;
        }

        // Cache this query to reduce server load
        $addonCategoryName = $row['addon_category'];
        $addonCategory = Cache::remember(
            "$this->merchant/menu-addon-category/$addonCategoryName", 600, function () use ($addonCategoryName) {
            return MenuAddonCategory::where('name', $addonCategoryName)
                ->where('merchant_id', $this->merchant)
                ->firstOrFail();
        });

        return new MenuAddon([
            'name' => $row['addon_name'],
            'price' => $row['price'],
            'sku' => $row['sku'],
            'enabled' => $row['enabled'] === 'YES' ? 1 : 0,
            'sorting' => $row['sorting'],
            'category_id' => $addonCategory->id,
        ]);
    }
}
