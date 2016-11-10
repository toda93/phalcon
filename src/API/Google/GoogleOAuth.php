<?php

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleOAuth
{
    protected $scope = 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/urlshortener';


    protected $token = [];

    protected $config = [];

    public function __construct($config, $token = [])
    {
        $this->config = $config;
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getUrlAuthCode()
    {
        $url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
                'response_type' => 'code',
                'client_id' => $this->config['client_id'],
                'redirect_uri' => $this->config['callback'],
                'scope' => $this->scope,
                'state' => 'request_token',
                'approval_prompt' => 'force',
                'access_type' => 'offline',
                'include_granted_scopes' => 'true'
            ]);
        return $url;
    }

    public function refreshToken()
    {
        if ($this->token['expired'] <= time()) {

            $client = new HttpClient();
            $token = $client->init()->get('https://www.googleapis.com/oauth2/v4/token?' . http_build_query([
                    'refresh_token' => $this->token['refresh_token'],
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'grant_type' => 'refresh_token'
                ]));

            $token = json_decode($token, true);

            if (!empty($token['access_token'])) {
                $this->token['access_token'] = $token['access_token'];
                $this->token['token_type'] = $token['token_type'];
                $this->token['expired'] = (int)$token['expires_in'] + time();
            }
        }
    }

    public function getTokenByCode($code)
    {
        $client = new HttpClient();
        $response = $client->init()->post('https://www.googleapis.com/oauth2/v4/token', [
            'code' => $code,
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'redirect_uri' => $this->config['callback'],
            'grant_type' => 'authorization_code',
        ]);

        $token = json_decode($response, true);

        $result = [];
        if (!array_key_exists('error', $token)) {
            $result['access_token'] = $token['access_token'];
            $result['token_type'] = $token['token_type'];
            $result['auth_type'] = $token['auth_type'];
            $result['expired'] = (int)$token['expires_in'] + time();

            if (!empty($token['refresh_token'])) {
                $result['refresh_token'] = $token['refresh_token'];
            }
        }
        return $result;
    }
}
