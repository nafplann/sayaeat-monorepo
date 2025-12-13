<?php

namespace SayaEat\Shared\Utils;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MapboxUtil
{

    /**
     * lat-lng coordinate @param array $origin
     * lat-lng coordinate @param array $destination
     */
    public static function getDirectionMatrix(array $origin, array $destination)
    {
        $profile = 'mapbox/driving';
        $client = new Client();
        $originString = $origin['longitude'] . ',' . $origin['latitude'];
        $destinationString = $destination['longitude'] . ',' . $destination['latitude'];
        $accessToken = env('MAPBOX_ACCESS_TOKEN');

        try {
            $request = $client->request(
                'GET',
                env('MAPBOX_DIRECTIONS_MATRIX_URL') . "{$profile}/{$originString};{$destinationString}?sources=0&annotations=distance,duration&access_token={$accessToken}",
                [
                    'headers' => [
                        'Referer' => env('APP_URL')
                    ]
                ]
            );
            $response = $request->getBody()->getContents();
            return json_decode($response);
        } catch (\Exception $e) {
            Log::error('MapboxUtil::getDirectionMatrix error: ' . $e->getMessage());
            return null;
        }
    }

}
