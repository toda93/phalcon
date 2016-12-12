<?php

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleUrlShortener extends GoogleOAuth
{
    protected $short_endpoint = 'https://www.googleapis.com/urlshortener/v1/url';

    public function sort($url)
    {
        $client = new HttpClient();

        $res = $client->init()
            ->addHeader("Authorization: Bearer " . $this->token['access_token'])
            ->addHeader("Content-Type: application/json")
            ->post($this->short_endpoint, '{"longUrl": "' . $url . '"}', false);

        $res = json_decode($res, true);

        if (!empty($res['id'])) {
            return $res['id'];
        }
        return '';
    }
}