<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Promotion::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->where('start_date', '<=', now())
                      ->where('end_date', '>=', now());
            }
        }

        $perPage = $request->input('per_page', 15);
        $promotions = $request->boolean('paginate', true) 
            ? $query->paginate($perPage)
            : $query->get();

        return response()->json($promotions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner_path' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $promotion = Promotion::create($validated);
        return response()->json($promotion, 201);
    }

    public function show(string $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);
        return response()->json($promotion);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'banner_path' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
        ]);
        
        $promotion->update($validated);
        return response()->json($promotion);
    }

    public function destroy(string $id): JsonResponse
    {
        Promotion::findOrFail($id)->delete();
        return response()->json(['message' => 'Promotion deleted successfully']);
    }
}

