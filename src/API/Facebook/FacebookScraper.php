<?php

namespace Toda\API\Facebook;

use Toda\Client\HttpClient;

class FacebookScraper
{
    private $username = null;
    private $password = null;
    private $cookies = null;

    private $client = null;

    public function __construct()
    {
        $this->client = new HttpClient();
    }

    public function setAuth($username = '', $password = '', $cookie_path = '', $proxy = '')
    {
        $this->username = $username;
        $this->password = $password;

        $this->client = new HttpClient([
            'cookie_file' => empty($cookie_path) ? '' : $cookie_path . "/facebook_{$username}.txt",
            'proxy' => $proxy
        ]);
    }

//    public function checkAccount()
//    {
//
//        $response = $client->init()
//            ->useCookie()
//            ->responseHeader()
//            ->post('https://www.facebook.com/login.php?locale=en_US', [
//                'email' => $this->username,
//                'pass' => $this->password
//            ]);
//
//        if (preg_match('/HTTP\/1\.1 302 Found/', $response)) {
//
//            $response = $client->init()
//                ->useCookie()
//                ->responseHeader()
//                ->get('https://www.facebook.com/me');
//
//            if (preg_match('/Location: (.*)/', $response, $matches)) {
//                if ($matches[1] == 'https://www.facebook.com/ ') {
//                    return false;
//                } else if (preg_match('/id=(\d+)/', $matches[1], $matches2)) {
//                    return $matches2[1];
//                } else {
//                    return str_replace('https://www.facebook.com/', '', $matches[1]);
//                }
//            }
//        }
//        return false;
//    }
//
//    public function tryAccount()
//    {
//        $client = new HttpClient();
//
//        $response = $client->init()
//            ->useCookie($this->cookies)
//            ->responseHeader()
//            ->get('https://www.facebook.com/login.php?locale=en_US');
//
//        if (!preg_match('/HTTP\/1\.1 302 Found/', $response)) {
//            $response = $client->init()
//                ->useCookie($this->cookies)
//                ->responseHeader()
//                ->post('https://www.facebook.com/login.php?locale=en_US', [
//                    'email' => $this->username,
//                    'pass' => $this->password
//                ]);
//
//            if (!preg_match('/Location: (.*)/', $response, $matches) && !preg_match('/checkpoint/', $matches[1])) {
//                return false;
//            }
//
//        }
//        return true;
//    }
//
//    public function addTester($app_id, $id)
//    {
//        if ($this->tryAccount()) {
//            $url = "https://developers.facebook.com/apps/{$app_id}/async/roles/add/?dpr=1";
//
//            $client = new HttpClient();
//
//            $html = $client->init()->useCookie($this->cookies)->get('https://m.facebook.com/pages/create');
//
//            if (preg_match("/name=\"fb_dtsg\" value=\"(.*?)\"/", $html, $matches)) {
//
//                $client->init()->useCookie($this->config['cookies'])->responseHeader()->post($url, [
//                    'fb_dtsg' => $matches[1],
//                    'role' => 'testers',
//                    'user_id_or_vanitys[0]' => $id,
//                    '__user' => $this->config['facebook_id']
//                ]);
//                return [
//                    'status' => 0,
//                    'message' => 'Success'
//                ];
//            }
//        };
//        return [
//            'status' => 0,
//            'message' => 'Login Failed'
//        ];
//    }
//
//    public function findUser($str)
//    {
//        if ($this->tryAccount()) {
//            $client = new HttpClient();
//
//            $html = $client->init()
//                ->useCookie($this->cookies)
//                ->get("https://m.facebook.com/search/people/?q=$str");
//
//            echo $html; exit;
//
//
//
//            return [
//                'status' => 1,
//                'message' => 'Success',
//                'data' => ''
//            ];
//        }
//        return [
//            'status' => 0,
//            'message' => 'Login Failed'
//        ];
//    }
}