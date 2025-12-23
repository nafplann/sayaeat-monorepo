<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class SettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::BROWSE_DRIVERS->value), only: ['index', 'datatable']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::ADD_DRIVERS->value), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::EDIT_DRIVERS->value), only: ['edit', 'update']),
            new Middleware(PermissionMiddleware::using(PermissionsEnum::DELETE_DRIVERS->value), only: ['destroy']),
        ];
    }

    /**
     * Return list of resource consumed by datatables library
     */
    public function index(): View
    {
        $data = Setting::all();
        $settings = [];

        foreach ($data as $setting) {
            $settings[$setting->key] = match ($setting->key) {
                'payment_method' => json_decode($setting->value),
                default => $setting->value,
            };
        }

        return view('settings.index', ['settings' => (object)$settings]);
    }

    public function update(Request $request)
    {
        foreach ($request->keys() as $key) {
            $setting = Setting::where('key', $key)->firstOrFail();

            $setting->value = match ($key) {
                'payment_method' => json_encode(collect($request->get($key))->map(function ($value) {
                    return (bool)$value;
                })),
                default => $request->get($key),
            };

            $setting->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Settings updated'
        ]);
    }
}
