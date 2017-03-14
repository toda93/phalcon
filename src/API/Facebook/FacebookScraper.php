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
        $this->opt['cookie'] = $cookie;

        $this->client = new HttpClient($this->opt);
        return $this;
    }

    public function createAccount($config)
    {
        $res = $this->client->follow()->get('https://m.facebook.com/reg?locale=en_US');

        $config['helper'] = '';

        $query = http_build_query($config);

        preg_match_all('/<input type=\"hidden\" name=\"(lsd|ccp|reg_instance|submission_request|helper|field_names\[\])\" value=\"(.*?)\"/', $res, $matches);

        foreach ($matches[1] as $key => $match) {
            $query .= '&' . urlencode($match) . '=' . urlencode($matches[2][$key]);
        }

        $res = $this->client->responseHeader()
            ->timeout(10)
            ->addHeader('Referer: https://m.facebook.com/reg?locale=en_US')
            ->addHeader('Content-Type: application/x-www-form-urlencoded')
            ->post('https://m.facebook.com/reg?locale=en_US', $query, false);

        $result['status'] = -1;

        if (empty($res)) {
            $result['message'] = 'proxy failed';
        } else {
            preg_match_all('/^Set-Cookie:\s*((c_user|xs)[^;]*)/mi', $res, $matches);
            $cookie = implode('; ', $matches[1]);

            if (preg_match('/Please enter the text below/i', $res)) {
                $result['message'] = 'captcha';
            } else if (preg_match('/The page you requested cannot be displayed right now/', $res)) {
                $result['message'] = 'content not display';
            } else if (preg_match('/To personalize content, tailor and measure ads, and provide a safer experience, we use cookies/i', $res)) {
                $result['message'] = 'proxy no cookie';
            } else if (preg_match('/HTTP\/1\.1 403 Forbidden/i', $res)) {
                $result['message'] = 'proxy forbidden';
            } else if (preg_match('/We need to confirm your identity before you can log in/i', $res)) {
                $result['message'] = 'proxy not trusted';
            } else if (preg_match('/It looks like you\'re trying to create an account for a business|We require everyone to use the name they use in everyday life|Using your real name makes it easier for friends to recognize you/i', $res)) {
                $result['status'] = 2;
            } else if (preg_match('/Please use an email address or mobile number that is not already in use by a registered account/i', $res)) {
                echo 'Phone used: ' . $config['reg_email__'];
            } else if (preg_match('/c_user=(\d+);/', $cookie, $matches)) {
                $data = $this->changeToEmail($cookie);

                $result['step'] = $data['step'];
                if ($data['status'] == 1) {
                    $result['email'] = $data['email'];
                    $result['status'] = 1;
                    $result['cookie'] = $cookie;
                    $result['uid'] = $matches[1];
                } else {
                    $result['status'] = -1;
                    $result['message'] = 'proxy not trusted';

                    if (!empty($data['email'])) {
                        unlink(RESOURCE_PATH . "cookie/" . $data['email'] . ".txt");
                    }
                }


            } else {

                echo 'Unkown' . PHP_EOL;
                $result['message'] = 'Unkown';
                echo $res;
                echo PHP_EOL;
            }
        }

        return $result;
    }

    protected function changeToEmail($cookie)
    {
        $res = $this->setAuthCookie($cookie)->client->follow()->get('https://m.facebook.com/changeemail');

        $status = 0;
        $step = 1;
        $email = '';
        if (preg_match('/Add email address/i', $res)) {

            $email = $this->randomEmail();

            preg_match_all('/<input type=\"hidden\" name=\"(next|fb_dtsg|reg_instance|old_email)\" value=\"(.*?)\"/', $res, $matches);

            $query = 'submit=Add&new=' . $email;

            foreach ($matches[1] as $key => $match) {
                $query .= '&' . urlencode($match) . '=' . urlencode($matches[2][$key]);
            }

            $res = $this->client->responseHeader()
                ->timeout(30)
                ->addHeader('Referer: https://m.facebook.com/changeemail')
                ->addHeader('Content-Type: application/x-www-form-urlencoded')
                ->post('https://m.facebook.com/setemail', $query, false);

            if (preg_match('/Enter confirmation code/', $res)) {
                $link = '';
                $count = 0;
                while (empty($link) && $count < 5) {
                    sleep(10);
                    $link = $this->getVerifyLink($email);
                    $count++;
                }

                echo $link . PHP_EOL;


                if (!empty($link)) {

                    $res = $this->client->follow()->responseHeader()
                        ->get($link);

                    $status = 1;

                    echo $res;

                }
            }

        }
        if (preg_match('/Confirm Your Number/i', $res)) {
            $step = -2;
        }
        if (preg_match('/We need to confirm your identity before you can log in/i', $res)) {
            $step = -1;
        }
        return [
            'status' => $status,
            'step' => $step,
            'email' => $email
        ];
    }

    protected function randomEmail($count = 1)
    {
        $rand = uniqid();

        $client = new \Toda\Client\HttpClient([
            'cookie_file' => RESOURCE_PATH . "cookie/$rand.txt",
            'verify_host' => 2,
        ]);

        $res = $client->get('https://temp-mail.org/');

        unset($client);

        if (preg_match('/class=\"mail opentip\" value=\"(.*?)\" data-placement=\"bottom\"/', $res, $matches)) {
            $email = $matches[1];
        }

        if (!empty($email) && file_exists(RESOURCE_PATH . "cookie/$rand.txt")) {
            rename(RESOURCE_PATH . "cookie/$rand.txt", RESOURCE_PATH . "cookie/$email.txt");
            return $email;
        }

        if (file_exists(RESOURCE_PATH . "cookie/$rand.txt")) {
            unlink(RESOURCE_PATH . "cookie/$rand.txt");
        }

        if ($count < 5) {
            return $this->randomEmail($count++);
        } else {
            return '';
        }
    }

    protected function getVerifyLink($email)
    {
        $client = new \Toda\Client\HttpClient([
            'cookie_file' => RESOURCE_PATH . "cookie/$email.com.txt",
            'verify_host' => 2,
        ]);

        $res = $client->get('https://temp-mail.org/');

        if (preg_match('/<a href=\"(.*?)\" title=\"(.*?)Facebook(.*?)\">/', $res, $matches)) {
            $res = $client->get($matches[1]);

            if (preg_match('/<a href=\"(https:\/\/www.facebook.com\/n\/\?confirmemail\.php(.*?)|https:\/\/www\.facebook\.com\/confirmcontact\.php(.*?))\"/', $res, $matches)) {
                return $matches[1];
            }
        }

        return '';
    }
}