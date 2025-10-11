<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class PaymentMethodsApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $method = Setting::where('key', 'payment_method')
            ->first();

        return response()->json(json_decode($method->value));
    }
}
