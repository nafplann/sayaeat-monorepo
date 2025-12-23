<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PromotionsApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $promotions = Promotion::whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->get();

        return response()->json($promotions);
    }
}
