<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAddressesApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Auth::user()->addresses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'label' => ['required', 'string'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $user = Auth::user();

        if ($user->addresses->count() >= 10) {
            return response()->json(['status' => false, 'message' => 'Maksimal 10 alamat tersimpan'], 400);
        }

        try {
            CustomerAddress::create([
                ...$request->only(['label', 'address', 'latitude', 'longitude']),
                'default' => false,
                'customer_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json(['status' => true, 'message' => "Berhasil menambahkan alamat."]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $data = CustomerAddress::findOrFail($id);

        if ($user->id !== $data->customer_id) {
            return response()->json(['status' => false, 'message' => 'Gagal menghapus data'], 403);
        }

        try {
            $data->delete();
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json(['status' => true, 'message' => "Berhasil menghapus alamat"]);
    }

    /**
     * Set default address
     */
    public function default(Request $request, $id)
    {
        $user = Auth::user();
        $data = CustomerAddress::findOrFail($id);

        if ($user->id !== $data->customer_id) {
            return response()->json(['status' => false, 'message' => 'Gagal mengubah data'], 403);
        }

        try {
            CustomerAddress::where('customer_id', $user->id)
                ->update(['default' => false]);

            $data->update(['default' => true]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json(['status' => true, 'message' => "Berhasil mengubah data"]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'label' => ['required', 'string'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $user = Auth::user();
        $data = CustomerAddress::findOrFail($id);

        if ($user->id !== $data->customer_id) {
            return response()->json(['status' => false, 'message' => 'Gagal mengupdate data'], 403);
        }

        try {
            $data->update($request->only(['label', 'address', 'latitude', 'longitude']));
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['status' => true, 'message' => "Berhasil mengupdate alamat"]);
    }
}
