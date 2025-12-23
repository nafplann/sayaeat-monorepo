<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Sentry\State\Scope;
use function Sentry\configureScope;

class AuthController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout'])
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function login(): View
    {
        return view('auth.login');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     */
    public function loginRequest(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'g-recaptcha-response' => 'recaptcha',
        ]);

        if (Auth::attempt($request->only(['email', 'password']), true)) {
            $request->session()->regenerate();

            AuditLog::logAuth('login');

            configureScope(function (Scope $scope): void {
                $scope->setUser(['email' => auth()->user()->email]);
            });

            return response()->json([
                'status' => true,
                'user' => auth()->user(),
                'redirectTo' => Redirect::intended('manage/dashboard')
                    ->getTargetUrl()
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Incorrect email or password'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        AuditLog::logAuth('logout');

        configureScope(function (Scope $scope): void {
            $scope->removeUser();
        });

        auth()->logout();
        return redirect('/');
    }
}
