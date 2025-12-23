<?php

namespace App\Http\Controllers\Api\Hapi;

use App\Core\Http\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class ProfileApiController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): Response
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string'],
            'whatsapp_number' => ['required', 'string'],
            'address' => ['required', 'string'],
            'latitude' => ['required'],
            'longitude' => ['required'],
        ]);

        $user = $request->user();
        $response = new ApiResponse();

        try {
            if ($user->addresses->count() === 0) {
                $user->addresses()->create([
                    'label' => 'Rumah Saya',
                    'address' => $request->get('address'),
                    'latitude' => $request->get('latitude'),
                    'longitude' => $request->get('longitude'),
                    'default' => true,
                ]);
            }

            $user->update([
                ...$request->all(),
                'has_complete_profile' => 1
            ]);
        } catch (FailedToVerifyToken $e) {
            return $response->setStatusCode(401)
                ->setStatus(false)
                ->setMessage('failed to update profile: ' . $e->getMessage());
        }

        return $response->setStatusCode(200)
            ->setMessage('success')
            ->set('user', $user->toArray());
    }
}
