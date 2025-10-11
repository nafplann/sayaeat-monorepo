<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Core\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Merchant;
use App\Utils\SettingsUtil;
use Illuminate\Http\Request;

class MenusApiController extends Controller
{
    /**
     * Return list of menu resource
     */
    public function searchByTerm(Request $request)
    {
        [$latitude, $longitude] = get_user_coordinate();
        $perPage = $request->get('perPage') ?? 10;
        $term = $request->get('term');
        $maxDistance = SettingsUtil::getMaxDistanceCovered();

        $response = new ApiResponse();

        try {
            $merchantHavingMenus = Merchant::where('status', 1)
                ->geofence($latitude, $longitude, 0, $maxDistance)
                ->havingMenus($term)
                ->orderBy('distance', 'ASC')
                ->simplePaginate(min($perPage, 10));

            $merchantHavingMenus->getCollection()
                ->makeHidden(Merchant::$sensitiveFields);

            $merchantHavingMenus->each(function ($merchant) {
                $merchant->menus->each(function ($menu) {
                    $menu->append('sell_price');
                });
            });

            return $merchantHavingMenus;

        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }

    // TODO: removed soon
    public function randomize()
    {
        $response = new ApiResponse();

        try {
            $menus = Menu::where('status', 1)
                ->where('image_path', '<>', '')
                ->whereHas('merchant', function ($q) {
                    $q->where('status', 1);
                })
                ->inRandomOrder()
                ->simplePaginate(10);

            $menus->each(function ($menu) {
                $menu->append('sell_price');
                $menu->append('merchant_slug');
            });

            return response()->json($menus);
        } catch (\Exception $e) {
            return $response->setStatusCode(400)
                ->setStatus(false)
                ->setMessage($e->getMessage());
        }
    }
}
