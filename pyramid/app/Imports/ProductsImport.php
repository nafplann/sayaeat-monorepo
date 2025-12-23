<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements WithHeadingRow, ToCollection
{
    private string $store;

    public function __construct(string $store)
    {
        $this->store = $store;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // TODO: Validate all required fields
            if (!$row['product_name']) {
                continue;
            }

            $categories = [];

            // Loop through the categories
            for ($i = 1; $i <= 5; $i++) {
                $categoryName = $row["category_$i"];

                if (!$categoryName) {
                    continue;
                }

                // Cache this query to reduce server load
                $category = Cache::remember("$this->store/menu-category/$categoryName", 600, function () use ($categoryName) {
                    return ProductCategory::where('name', $categoryName)
                        ->where('store_id', $this->store)
                        ->firstOrFail();
                });

                $categories[] = $category->id;
            }

            $product = Product::create([
                'name' => $row['product_name'],
                'description' => $row['description'],
                'details' => $row['product_detail'],
                'price' => $row['price'],
                'unit' => $row['unit'],
                'barcode' => $row['barcode'],
                'sku' => $row['sku'],
                'prescription_required' => $row['prescription_required'] === 'YES',
                'condition' => $row['condition'],
                'status' => $row['status'] === 'AVAILABLE' ? 1 : 0,
                'status_text' => '',
                'image_path' => '',
                'minimum_purchase_quantity' => $row['minimum_purchase_quantity'] ?? 1,
                'sorting' => $row['sorting'],
                'store_id' => $this->store,
            ]);

            $product->categories()->sync($categories);
        }
    }
}
