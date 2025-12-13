<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SayaEat\Shared\Contracts\PyramidClientInterface;

class DriverDailyReportController extends Controller
{
    public function __construct(
        protected PyramidClientInterface $pyramidClient
    ) {}

    public function index(): View
    {
        return view('driver-daily-report.index');
    }

    public function income(Request $request): JsonResponse
    {
        try {
            $filters = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'driver_id' => $request->input('driver_id'),
            ];
            
            $response = $this->pyramidClient->get('internal/driver-reports/income', $filters);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function driverRank(Request $request): JsonResponse
    {
        try {
            $filters = [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
            ];
            
            $response = $this->pyramidClient->get('internal/driver-reports/rank', $filters);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

