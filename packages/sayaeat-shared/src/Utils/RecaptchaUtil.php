<?php

namespace SayaEat\Shared\Utils;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class RecaptchaUtil
{
    public static function validate(string $recaptchaResponse, string $secret)
    {
        $client = new Client();

        try {
            $request = $client->request(
                'POST',
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'form_params' => [
                        'secret' => $secret,
                        'response' => $recaptchaResponse
                    ]
                ]
            );
            $response = $request->getBody()->getContents();
            return json_decode($response)->success;
        } catch (\Exception $e) {
            Log::error('RecaptchaUtil::validate error: ' . $e->getMessage());
            return false;
        }
    }

}
