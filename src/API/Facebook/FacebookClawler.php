<?php

namespace Toda\API\Facebook;

class FacebookClawler extends FacebookOAuth
{
    private $username = null;
    private $password = null;
    private $cookies = null;

    public function __construct($username, $password, $cookie_path)
    {
        $this->username = $username;
        $this->password = $password;
        $this->cookies = $cookie_path . "/facebook_{$username}.txt";
    }

    public function checkAccount()
    {
        $client = new HttpClient();

        $client->init()->setCookies(false, false)->get('https://www.facebook.com/login.php?locale=en_US');

        $response = $client->init()
            ->setCookies()
            ->getHeaderResponse()
            ->post('https://www.facebook.com/login.php?locale=en_US', [
                'email' => $this->username,
                'pass' => $this->password
            ]);

        if (preg_match('/HTTP\/1\.1 302 Found/', $response)) {

            $response = $client->init()
                ->setCookies()
                ->getHeaderResponse()
                ->get('https://www.facebook.com/me');

            if (preg_match('/Location: (.*)/', $response, $matches)) {
                if ($matches[1] == 'https://www.facebook.com/ ') {
                    return false;
                } else if (preg_match('/id=(\d+)/', $matches[1], $matches2)) {
                    return $matches2[1];
                } else {
                    return str_replace('https://www.facebook.com/', '', $matches[1]);
                }
            }
        }
        return false;
    }

    public function tryAccount()
    {

        $client = new HttpClient();

        $response = $client->init()
            ->setCookies($this->cookies)
            ->getHeaderResponse()
            ->get('https://www.facebook.com/login.php?locale=en_US');

        if (!preg_match('/HTTP\/1\.1 302 Found/', $response)) {
            $response = $client->init()
                ->setCookies($this->cookies)
                ->getHeaderResponse()
                ->post('https://www.facebook.com/login.php?locale=en_US', [
                    'email' => $this->username,
                    'pass' => $this->password
                ]);

            if (!preg_match('/Location: (.*)/', $response, $matches) && !preg_match('/checkpoint/', $matches[1])) {
                return false;
            }

        }
        return true;
    }

    public function addTester($id)
    {
        if ($this->tryAccount()) {
            $url = "https://developers.facebook.com/apps/{$this->config['client_id']}/async/roles/add/?dpr=1";

            $client = new HttpClient();

            $html = $client->init()->setCookies($this->config['cookies'])->get('https://m.facebook.com/pages/create');

            if (preg_match("/name=\"fb_dtsg\" value=\"(.*?)\"/", $html, $matches)) {

                $client->init()->setCookies($this->config['cookies'])->getHeaderResponse()->post($url, [
                    'fb_dtsg' => $matches[1],
                    'role' => 'testers',
                    'user_id_or_vanitys[0]' => $id,
                    '__user' => $this->config['facebook_id']
                ]);
                return true;
            }
        };
        return false;
    }
}