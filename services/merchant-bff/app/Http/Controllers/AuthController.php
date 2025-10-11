<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AuthController extends Controller implements HasMiddleware
{
    public function __construct(
        protected AuthService $authService
    ) {}

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
     * Show the login form
     */
    public function login(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function loginRequest(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            // TODO: Add recaptcha validation if needed
            // 'g-recaptcha-response' => 'recaptcha',
        ]);

        $user = $this->authService->validateCredentials(
            $request->input('email'),
            $request->input('password')
        );

        if ($user) {
            // Regenerate session
            $request->session()->regenerate();

            // Store user data in session
            Session::put('user_id', $user['id']);
            Session::put('user', $user);

            // TODO: Log authentication event to Pyramid
            // $this->logAuthEvent('login', $user['id']);

            return response()->json([
                'status' => true,
                'user' => $user,
                'redirectTo' => Redirect::intended('manage/dashboard')
                    ->getTargetUrl()
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Incorrect email or password'
        ], 401);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Session::get('user_id');

        // TODO: Log authentication event to Pyramid
        // if ($userId) {
        //     $this->logAuthEvent('logout', $userId);
        // }

        // Clear session
        Session::forget('user_id');
        Session::forget('user');
        Session::flush();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get current authenticated user
     */
    public function user(Request $request): JsonResponse
    {
        $user = Session::get('user');

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'user' => $user
        ]);
    }
}

