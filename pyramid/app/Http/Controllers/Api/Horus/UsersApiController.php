<?php

namespace App\Http\Controllers\Api\Horus;

use App\Core\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\VerificationCode;
use App\Notifications\OtpNotification;
use App\Utils\RecaptchaUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UsersApiController extends Controller
{
    public function storeFcmToken(Request $request)
    {
        $request->validate([
            'token' => ['required'],
        ]);

        $user = Auth::user();

        try {
            $messaging = app('firebase.messaging');
            $validate = $messaging->validateRegistrationTokens([$request->get('token')]);

            if (empty($validate['valid'])) {
                return response()->json([
                    'message' => 'Invalid FCM Token'
                ], 422);
            }

            // Remove all fcm tokens
            $user->fcm_tokens()->delete();

            // Store new tokens
            $user->fcm_tokens()->create([
                'token' => $request->get('token'),
            ]);

            return response()->json([], 204);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phoneNumber' => ['required', 'starts_with:+62', 'regex:/^\S*$/u'],
            'otp' => ['required', 'min:6', 'max:6'],
        ]);

        $recaptchaResponse = $request->get('g-recaptcha-response');
        $phoneNumber = $request->get('phoneNumber');

        if (RecaptchaUtil::validate($recaptchaResponse, env('HAPI_RECAPTCHA_SECRET_KEY')) === false) {
            return response()->json([
                'message' => 'Recaptcha validation failed'
            ], 422);
        }

        // Get user
        $driver = Driver::where('phone_number', $phoneNumber)
            ->firstOrFail();

        // Validation Logic
        $verificationCode = VerificationCode::where('user_id', $driver->id)
            ->where('otp', $request->otp)
            ->first();

        $response = new ApiResponse();
        $now = Carbon::now();

        if (!$verificationCode) {
            return $response->setStatusCode(422)
                ->setStatus(false)
                ->setMessage('kode otp salah');
        }

        if ($verificationCode && $now->isAfter($verificationCode->expire_at)) {
            return $response->setStatusCode(422)
                ->setStatus(false)
                ->setMessage('kode otp telah kadaluarsa');
        }

        // Remove all OTP's
        $driver->verification_codes()->delete();

        // Remove all fcm tokens
        $driver->fcm_tokens()->delete();

        // Revoke all tokens...
        $driver->tokens()->delete();

        return $response->setStatusCode(200)
            ->setMessage('success')
            ->set('access_token', $driver->createToken($phoneNumber)->plainTextToken)
            ->set('user', $driver->toArray());
    }

    public function login(Request $request)
    {
        $request->validate([
            'phoneNumber' => ['required', 'string', 'starts_with:+62', 'regex:/^\S*$/u', 'max:15'],
        ]);

        $recaptchaResponse = $request->get('g-recaptcha-response');
        $phoneNumber = "+" . preg_replace('/[^0-9]/', '', $request->get('phoneNumber'));

        if (RecaptchaUtil::validate($recaptchaResponse, env('HAPI_RECAPTCHA_SECRET_KEY')) === false) {
            return response()->json([
                'message' => 'Recaptcha validation failed'
            ], 422);
        }

        // Get user
        $driver = Driver::where('phone_number', $phoneNumber)
            ->first();

        if (!$driver) {
            return response()->json([], 422);
        }

        # Check if user have any Existing OTP
        $verificationCode = VerificationCode::where('user_id', $driver->id)
            ->latest()
            ->first();

        $now = Carbon::now();

        if (!$verificationCode || $now->isAfter($verificationCode->expire_at)) {
            // Create a New OTP
            $verificationCode = VerificationCode::create([
                'user_id' => $driver->id,
                'otp' => rand(100000, 999999),
                'expire_at' => Carbon::now()->addSeconds(70)
            ]);
        }

        $driver->notify(new OtpNotification($verificationCode->otp));

        return response()->json([], 204);
    }

    public function logout(Request $request): Response
    {
        $response = new ApiResponse();

        // Revoke all tokens...
        $request->user()->tokens()->delete();

        return $response->setStatusCode(204)
            ->setMessage('success');
    }
}
