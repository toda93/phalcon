<?php

namespace Toda\API\Facebook;

use Toda\Client\HttpClient;

abstract class FacebookOAuth
{
    protected $oauth_endpoint = 'https://www.facebook.com/v2.6/dialog/oauth';
    protected $oauth_token_endpoint = 'https://graph.facebook.com/v2.6/oauth/access_token';

    protected $scope = '';

    private $token = [];

    public function __construct($token = [])
    {
        $this->setToken($token);
    }

    protected function setToken($token)
    {
        $this->token = $token;
    }

    protected function getToken()
    {
        return $this->token;
    }

    public function getUrlAuthCode()
    {
        $params = array(
            'scope' => $this->scope,
            'response_type' => 'code',
            'auth_type' => 'rerequest',
            'client_id' => page('facebook_oauth_client_id'),
            'redirect_uri' => page('facebook_oauth_callback'),
        );

        $url = $this->oauth_endpoint . "?" . http_build_query($params);
        return $url;
    }

    public function getTokenByCode($code)
    {
        $client = new HttpClient();
        $response = $client->init()->post($this->oauth_token_endpoint, [
            'code' => $code,
            'client_id' => page('facebook_oauth_client_id'),
            'client_secret' => page('facebook_oauth_client_secret'),
            'redirect_uri' => page('facebook_oauth_callback'),
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