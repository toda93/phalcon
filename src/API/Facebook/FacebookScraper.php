<?php

namespace Toda\API\Facebook;

use Toda\Client\HttpClient;

class FacebookScraper
{
    private $username = null;
    private $password = null;
    private $cookie = null;

    private $client = null;
    private $opt = [];

    public function __construct($opt = [])
    {
        $this->opt = $opt;
        if (!isset($this->opt['cookie_file'])) {
            $this->opt['cookie_file'] = '';
        }
        $this->client = new HttpClient($this->opt);
    }

    public function client()
    {
        return $this->client;
    }

    public function setAuthAccount($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->opt['cookie_file'] = '';

        $this->client = new HttpClient($this->opt);
        return $this;
    }

    public function setAuthCookie($cookie)
    {
        $this->cookie = $cookie;

        if (!empty($cookie)) {
            if (preg_match('/\.txt/', $cookie)) {
                $this->opt['cookie_file'] = $cookie;
            } else {
                $this->opt['cookie'] = $cookie;
                unset($this->opt['cookie_file']);
            }
        }

        $this->client = new HttpClient($this->opt);
        return $this;
    }

    public function createAccount($config)
    {
        $res = $this->client->follow()->get('https://m.facebook.com/reg?locale=en_US');

        $config['helper'] = '';

        $config = http_build_query($config);

        preg_match_all('/<input type=\"hidden\" name=\"(lsd|ccp|reg_instance|submission_request|helper|field_names\[\])\" value=\"(.*?)\"/', $res, $matches);

        foreach ($matches[1] as $key => $match) {
            $config .= '&' . urlencode($match) . '=' . urlencode($matches[2][$key]);
        }

        $res = $this->client->responseHeader()
            ->addHeader('Referer: https://m.facebook.com/reg?locale=en_US')
            ->addHeader('Content-Type: application/x-www-form-urlencoded')
            ->post('https://m.facebook.com/reg?locale=en_US', $config, false);

        preg_match_all('/^Set-Cookie:\s*((c_user|xs)[^;]*)/mi', $res, $matches);
        return implode('; ', $matches[1]);
    }

    public function checkCreated()
    {
        $res = $this->client->follow()->get('https://m.facebook.com/');

        if (preg_match('/Your account has been disabled|Please verify your account/', $res)) {
            return -1;
        } else if (preg_match('/Add your mobile number to get updates|Please enter your phone number|Confirm your number/', $res)) {
            return 1;
        } else if (preg_match('/Confirm your email address/', $res)) {
            return 4;
        }
        return 3;
    }

    public function verifyEmail($link)
    {
        $res = $this->client->follow()->get(htmlspecialchars_decode($link));

        if (preg_match('/Invalid confirmation code/', $res)) {

            preg_match('/<input type=\"hidden\" name=\"fb_dtsg\" value=\"(.*?)\"/', $res, $matches);

            $this->client->post('https:/m.facebook.com/confirmemail.php?resend&amp;didnt_get_code=1', [
                'fb_dtsg' => $matches[1]
            ]);

            return false;
        }

        $res = $this->client->follow()->get('https://m.facebook.com/settings/email/');

        if (preg_match('/Pending Email/', $res)) {
            return false;
        }

        return true;
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