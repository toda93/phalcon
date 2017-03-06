<?php

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleDriveScraper
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

    public function setAuthAccount($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->opt['cookie_file'] = '';

        $this->client = new HttpClient($this->opt);
    }

    public function setAuthCookie($cookie)
    {
        $this->cookie = $cookie;

        if (!empty($cookie)) {
            if (preg_match('/\.txt/', $cookie)) {
                $this->opt['cookie'] = $cookie;
            } else {
                $this->opt['cookie_file'] = $cookie;
            }
        }
        $this->client = new HttpClient($this->opt);
    }

    public function getFileName($id){

        $res = $this->client->get("https://drive.google.com/file/d/$id/view");

        $name = '';
        if(preg_match('/<meta property=\"og:title\" content=\"(.*?)\">/', $res, $matches)){
            $name = $matches[1];
        }
        return $name;
    }

    public function getDataImage($id){
        return $this->client->follow()->get("https://drive.google.com/uc?id=$id");
    }

}