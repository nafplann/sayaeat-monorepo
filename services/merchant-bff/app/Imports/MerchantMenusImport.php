<?php

namespace App\Imports;

use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MerchantMenusImport implements WithMultipleSheets
{
    private string $merchant;

    public function __construct(string $merchant)
    {
        $this->merchant = $merchant;

        // Clear cache
        Cache::flush();
    }

    public function sheets(): array
    {
        return [
            'Menu Category' => new MenuCategoriesImport($this->merchant),
            'Menus' => new MenusImport($this->merchant),
            'Addon Category' => new AddonCategoriesImport($this->merchant),
            'Addon' => new MenuAddonsImport($this->merchant),
            'Menu Addon Link' => new MenuAddonLinksImport($this->merchant),
        ];
    }
}
