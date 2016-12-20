<?php
/**
 * Created by PhpStorm.
 * User: thanhtong
 * Date: 4/27/16
 * Time: 10:15 AM
 */

namespace Toda\Client;

class HttpClient
{
    private $ch = null;
    private $header = [];

    public function init($agent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0')
    {
        $this->ch = curl_init();
        $this->addOption(CURLOPT_SSL_VERIFYHOST, false)
            ->addOption(CURLOPT_SSL_VERIFYPEER, false)
            ->addOption(CURLOPT_RETURNTRANSFER, true)
            ->addOption(CURLOPT_USERAGENT, $agent);

        return $this;
    }

    private function execute()
    {
        if (!empty($this->header)) {
            $this->addOption(CURLOPT_HTTPHEADER, $this->header);
        }
        $result = curl_exec($this->ch);

        $this->header = [];
        curl_close($this->ch);
        return $result;
    }

    public function addHeader($header)
    {
        array_push($this->header, $header);
        return $this;
    }

    public function addOption($opt, $value)
    {

        if(!is_null($value)){
            curl_setopt($this->ch, $opt, $value);
        }
        return $this;
    }

    public function useCookie($path = false, $keep = true)
    {
        if (empty($path)) {
            $path = __DIR__ . '/cookies.txt';
        }
        if (!$keep) {
            file_put_contents($path, '');
        }

        return $this->addOption(CURLOPT_COOKIEFILE, $path)
            ->addOption(CURLOPT_COOKIEJAR, $path);
    }

    public function setCookie($cookies)
    {
        return $this->addOption(CURLOPT_COOKIE, $cookies);
    }
    public function setProxy($proxy)
    {
        return $this->addOption(CURLOPT_PROXY, $proxy);
    }

    public function responseHeader($body = false)
    {
        return $this->addOption(CURLOPT_NOBODY, !$body)
            ->addOption(CURLOPT_HEADER, true);
    }

    public function follow()
    {
        return $this->addOption(CURLOPT_FOLLOWLOCATION, true);
    }

    public function get($url)
    {
        $this->addOption(CURLOPT_URL, $url);

        $result = $this->execute();
        return $result;
    }

    public function post($url, $data, $build = true)
    {

        $this->addOption(CURLOPT_POST, 1)
            ->addOption(CURLOPT_POSTFIELDS, $build ? http_build_query($data) : $data)
            ->addOption(CURLOPT_URL, $url);

        return $this->get($url);
    }

    public function put($url, $data, $build = true)
    {
        return $this->addOption(CURLOPT_CUSTOMREQUEST, 'PUT')
            ->post($url, $data, $build);
    }

//    public function put($url, $data, $build = true)
//    {
//        if ($build == true) {
//            $data = http_build_query($data);
//        }
//
//        $f = fopen(__DIR__ . '/requests.txt', 'w');
//
//        $this->addOption(CURLOPT_URL, $url)
//            ->addOption(CURLOPT_POST, 1)
//            ->addOption(CURLOPT_CUSTOMREQUEST, 'PUT')
//            ->addOption(CURLOPT_POSTFIELDS, $data)
//            ->addOption(CURLOPT_URL, $url)
//            ->addOption(CURLOPT_VERBOSE, true)
//            ->addOption(CURLOPT_STDERR, $f);
//
//        $result = $this->execute();
//
//        fclose($f);
//
//        return $result;
//    }
}