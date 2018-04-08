<?php
/**
 * Created by PhpStorm.
 * User: thanhtong
 * Date: 4/27/16
 * Time: 10:15 AM
 */

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleOAuth2
{
    public static function getUrlAuthCode($config)
    {
        $params = array(
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => $config['scope'],
            'state' => 'request_token',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
        );
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

    }

    public static function getTokenByCode($code, $config)
    {
        $client = new HttpClient();

        $data = json_decode($client->post('https://www.googleapis.com/oauth2/v4/token', [
            'code' => $code,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'grant_type' => 'authorization_code',
            'redirect_uri' => $config['redirect_uri'],
        ]), true);
        if (!empty($data['expires_in'])) {
            $data['expires_at'] = time() + intval($data['expires_in']);

        }
        return $data;

    }

    public static function refreshToken($refresh_token, $config)
    {
        $client = new HttpClient();

        $data = json_decode($client->init()->post('https://www.googleapis.com/oauth2/v4/token', [
            'refresh_token' => $refresh_token,
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'grant_type' => 'refresh_token',
        ]), true);

        if (!empty($data['expires_in'])) {
            $data['expires_at'] = time() + intval($data['expires_in']);
        }
        return $data;

    }
}

