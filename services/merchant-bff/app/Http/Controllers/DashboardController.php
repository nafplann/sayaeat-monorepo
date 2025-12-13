<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index(): View
    {
        $user = Session::get('user');

        // TODO: Check if user is owner and show different view
        // if ($user['role'] === 'owner') {
        //     return view('dashboard.owner');
        // }

        return view('dashboard.index', [
            'user' => $user
        ]);
    }

    /**
     * Get dashboard data (overview stats)
     * TODO: Implement via Pyramid API
     */
    public function getData(Request $request)
    {
        // TODO: Call Pyramid internal API for dashboard stats
        // For now, return empty data
        return response()->json([
            'data' => []
        ]);
    }

    /**
     * Get users location data
     * TODO: Implement via Pyramid API
     */
    public function getUsersLocation()
    {
        // TODO: Call Pyramid internal API for user locations
        return response()->json([
            'locations' => []
        ]);
    }

    /**
     * Get daily revenue data
     * TODO: Implement via Pyramid API
     */
    public function dailyRevenue()
    {
        // TODO: Call Pyramid internal API for revenue data
        return response()->json([
            'revenue' => []
        ]);
    }

    /**
     * Get Belanja Aja stats
     * TODO: Implement via Pyramid API
     */
    public function belanjaAja()
    {
        // TODO: Call Pyramid internal API for Belanja Aja stats
        return response()->json([
            'stats' => []
        ]);
    }
}

