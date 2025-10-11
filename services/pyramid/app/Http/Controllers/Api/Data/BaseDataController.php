<?php

namespace App\Http\Controllers\Api\Data;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

abstract class BaseDataController extends Controller
{
    /**
     * The model class name for this controller
     */
    abstract protected function getModelClass(): string;

    /**
     * Get all records with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $modelClass = $this->getModelClass();
        $query = $modelClass::query();

        // Apply filters from request
        $this->applyFilters($query, $request);

        // Apply pagination if requested
        if ($request->has('per_page')) {
            $perPage = min($request->input('per_page', 15), 100);
            $data = $query->paginate($perPage);
        } else {
            $data = $query->get();
        }

        return response()->json($data);
    }

    /**
     * Get a single record by ID
     */
    public function show(string $id): JsonResponse
    {
        $modelClass = $this->getModelClass();
        $record = $modelClass::find($id);

        if (!$record) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        return response()->json($record);
    }

    /**
     * Create a new record
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $modelClass = $this->getModelClass();
            $data = $this->validateRequest($request);
            
            $record = $modelClass::create($data);

            return response()->json($record, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update an existing record
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $modelClass = $this->getModelClass();
            $record = $modelClass::find($id);

            if (!$record) {
                return response()->json(['error' => 'Record not found'], 404);
            }

            $data = $this->validateRequest($request);
            $record->update($data);

            return response()->json($record);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a record
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $modelClass = $this->getModelClass();
            $record = $modelClass::find($id);

            if (!$record) {
                return response()->json(['error' => 'Record not found'], 404);
            }

            $record->delete();

            return response()->json(['message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Apply filters to query based on request parameters
     */
    protected function applyFilters($query, Request $request): void
    {
        // Default implementation - can be overridden in child classes
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['page', 'per_page', 'sort', 'order'])) {
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        // Apply sorting
        if ($request->has('sort')) {
            $order = $request->input('order', 'asc');
            $query->orderBy($request->input('sort'), $order);
        }
    }

    /**
     * Validate request data - can be overridden in child classes
     */
    protected function validateRequest(Request $request): array
    {
        return $request->all();
    }
}

