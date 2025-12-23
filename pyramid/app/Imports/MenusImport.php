<?php

namespace App\Imports;

use App\Models\Menu;
use App\Models\MenuCategory;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MenusImport implements ToModel, WithHeadingRow
{
    private string $merchant;

    public function __construct(string $merchant)
    {
        $this->merchant = $merchant;
    }

    public function model(array $row)
    {
        // TODO: Validate all required fields
        if (!$row['menu_name']) {
            return null;
        }

        // Cache this query to reduce server load
        $categoryName = $row['category'];
        $category = Cache::remember("$this->merchant/menu-category/$categoryName", 600, function () use ($categoryName) {
            return MenuCategory::where('name', $categoryName)
                ->where('merchant_id', $this->merchant)
                ->firstOrFail();
        });

        return new Menu([
            'name' => $row['menu_name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'image_path' => '',
            'status' => $row['status'] === 'AVAILABLE' ? 1 : 0,
            'status_text' => '',
            'sku' => $row['sku'],
            'type' => $row['type'],
            'category_id' => $category->id,
            'merchant_id' => $this->merchant,
        ]);
    }
}
