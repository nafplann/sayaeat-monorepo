<?php

namespace App\Http\Controllers;

use App\Enums\PermissionsEnum;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class OngoingOrdersController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(PermissionsEnum::READ_ONGOING_ORDERS->value)),
        ];
    }

    public function index()
    {
        return view('ongoing_orders.index');
    }
}
