<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriversController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Driver::query();

        if ($request->has('employment_status')) {
            $query->where('employment_status', $request->input('employment_status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $drivers = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($drivers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:drivers,code|max:255',
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address' => 'nullable|string',
            'employment_status' => 'required|string',
            'vehicle_model' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'map_link' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account_holder' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            'photo_path' => 'nullable|string',
        ]);

        $driver = Driver::create($validated);
        return response()->json($driver, 201);
    }

    public function show(string $id): JsonResponse
    {
        $driver = Driver::findOrFail($id);
        return response()->json($driver);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $driver = Driver::findOrFail($id);
        
        $validated = $request->validate([
            'code' => 'sometimes|string|max:255|unique:drivers,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'address' => 'nullable|string',
            'employment_status' => 'sometimes|string',
            'vehicle_model' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'map_link' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'bank_account_holder' => 'nullable|string',
            'bank_account_number' => 'nullable|string',
            'photo_path' => 'nullable|string',
        ]);
        
        $driver->update($validated);
        return response()->json($driver);
    }

    public function destroy(string $id): JsonResponse
    {
        Driver::findOrFail($id)->delete();
        return response()->json(['message' => 'Driver deleted successfully']);
    }
}

