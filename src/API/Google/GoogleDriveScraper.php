<?php

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleDriveScraper
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