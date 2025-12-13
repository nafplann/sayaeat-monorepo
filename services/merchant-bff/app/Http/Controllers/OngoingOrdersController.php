<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OngoingOrdersController extends Controller
{
    public function index(): View
    {
        return view('ongoing-orders.index');
    }
}

