<?php

namespace App\Http\Controllers\Api\Hapi\MarketAja;

use App\Core\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Utils\SettingsUtil;
use Illuminate\Http\Request;

class ProductsApiController extends Controller
{
    private static $perPageMax = 50;

    /**
     * Return list of product resource by store
     */
    public function searchStoreProducts(Request $request, $storeSlug)
    {
        [$latitude, $longitude] = get_user_coordinate();
        $perPage = $request->get('perPage') ?? self::$perPageMax;
        $term = $request->get('term');
        $category = $request->get('category');
        $maxDistance = SettingsUtil::getMaxDistanceCovered();

        $response = new ApiResponse();

        try {
            $store = Store::where('status', 1)
                ->where('slug', $storeSlug)
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->firstOrFail();

            $products = Product::where('status', 1)
                ->where('store_id', $store->id)
                ->searchByCategory($category)
                ->searchByTerm($term)
                ->inRandomOrder()
                ->simplePaginate(min($perPage, self::$perPageMax));

            $products->each(function ($product) {
                $product->append('sell_price');
            });

            return $products;

        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    /**
     * Return list of product resource
     */
    public function searchByTerm(Request $request)
    {
        [$latitude, $longitude] = get_user_coordinate();
        $perPage = $request->get('perPage') ?? self::$perPageMax;
        $term = $request->get('term');
        $maxDistance = SettingsUtil::getMaxDistanceCovered();

        $response = new ApiResponse();

        try {
            $storeHavingProducts = Store::where('status', 1)
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->havingProducts($term)
                ->orderBy('distance', 'ASC')
                ->simplePaginate(min($perPage, self::$perPageMax));

            $storeHavingProducts->getCollection()
                ->makeHidden(Store::$sensitiveFields);

            $storeHavingProducts->each(function ($store) {
                $store->products->each(function ($product) {
                    $product->append('sell_price');
                });
            });

            return $storeHavingProducts;

        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }
}
