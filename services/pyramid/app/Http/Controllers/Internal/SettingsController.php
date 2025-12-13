<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Get all settings from cache/config
        $settings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'platform_fee' => config('app.platform_fee', 0),
            'delivery_fee' => config('app.delivery_fee', 0),
            // Add more settings as needed
        ];

        return response()->json($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        // Store setting in cache or database
        Cache::forever('setting_' . $validated['key'], $validated['value']);

        return response()->json([
            'message' => 'Setting updated successfully',
            'key' => $validated['key'],
            'value' => $validated['value']
        ]);
    }

    public function get(string $key): JsonResponse
    {
        $value = Cache::get('setting_' . $key, config('app.' . $key));

        return response()->json([
            'key' => $key,
            'value' => $value
        ]);
    }
}

