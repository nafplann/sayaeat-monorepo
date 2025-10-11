<?php

namespace App\Imports;

use App\Models\Menu;
use App\Models\MenuAddon;
use App\Models\MenuAddonCategory;
use App\Models\MenuCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use Maatwebsite\Excel\Concerns\PersistRelations;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MenuAddonLinksImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    private string $merchant;

    public function __construct(string $merchant)
    {
        $this->merchant = $merchant;
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row)
        {
            if (!$name = $row['name']) {
                continue;
            }

            $menu = Menu::where('name', $name)
                ->where('merchant_id', $this->merchant)
                ->firstOrFail();

            for ($i = 1; $i <= 10; $i++) {
                $addonCategoryName = $row["addon_category_$i"];

                if (!$addonCategoryName) {
                    continue;
                }

                $addonCategory = MenuAddonCategory::where('name', $addonCategoryName)
                    ->where('merchant_id', $this->merchant)
                    ->firstOrFail();

                $addonCategory->menus()->attach($menu);
            }
        }
    }
}
