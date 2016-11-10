<?php

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleUrlShortener extends GoogleOAuth
{
    protected $short_endpoint = 'https://www.googleapis.com/urlshortener/v1/url';


    public function __construct($config, $token = [])
    {

        parent::__construct($config, $token);

        if (empty($token)) {
            $this->token['access_token'] = $config['access_token'];
            $this->token['refresh_token'] = $config['refresh_token'];
            $this->token['expired'] = $config['expired'];
        }

        $this->refreshToken();
    }

    public function sort($url)
    {
        $client = new HttpClient();




        $data = $client->init()
            ->addHeader('Authorization', 'Bearer ' . $this->token['access_token'])
            ->addHeader('Content-Type', 'application/json')
            ->post($this->short_endpoint, '{"longUrl": "' . $url . '"}', false);

        $data = json_decode($data, true);

        if (!empty($data['id'])) {
            return $data['id'];
        }
        return '';
    }
}