<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Core\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\VerificationCode;
use App\Notifications\OtpNotification;
use App\Utils\RecaptchaUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerAuthApiController extends Controller
{
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

        $response = new ApiResponse();
        $now = Carbon::now();

        // Get user
        $customer = Customer::where('phone_number', $phoneNumber)
            ->firstOrFail();

        // Test user
        if ($phoneNumber === '+6288888888888') {
            return $this->loginSuccess($customer);
        }

        // Validation Logic
        $verificationCode = VerificationCode::where('user_id', $customer->id)
            ->where('otp', $request->otp)
            ->first();

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

        return $this->loginSuccess($customer);
    }

    private function loginSuccess(Customer $customer): Response
    {
        $response = new ApiResponse();

        // Remove all OTP's
        $customer->verification_codes()->delete();

        // Revoke all tokens...
        $customer->tokens()->delete();

        return $response->setStatusCode(200)
            ->setMessage('success')
            ->set('access_token', $customer->createToken($customer->phone_number)->plainTextToken)
            ->set('user', $customer->toArray());
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
        $customer = Customer::where('phone_number', $phoneNumber)
            ->first();

        if (!$customer) {
            $customer = Customer::create([
                'name' => '',
                'address' => '',
                'phone_number' => $phoneNumber,
                'latitude' => 0,
                'longitude' => 0
            ]);
        }

        # User Does not Have Any Existing OTP
        $verificationCode = VerificationCode::where('user_id', $customer->id)
            ->latest()
            ->first();

        $now = Carbon::now();

        if (!$verificationCode || $now->isAfter($verificationCode->expire_at)) {
            // Create a New OTP
            $verificationCode = VerificationCode::create([
                'user_id' => $customer->id,
                'otp' => rand(100000, 999999),
                'expire_at' => Carbon::now()->addSeconds(70)
            ]);
        }

        $customer->notify(new OtpNotification($verificationCode->otp));

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
