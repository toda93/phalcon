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

    public function init()
    {
        $this->ch = curl_init();
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, false)
            ->setOpt(CURLOPT_SSL_VERIFYPEER, false)
            ->setOpt(CURLOPT_RETURNTRANSFER, true)
            ->setOpt(CURLOPT_USERAGENT, ' Mozilla/5.0 (Windows NT 10.0; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');

        return $this;
    }

    private function close()
    {
        curl_close($this->ch);
        return $this;
    }

    private function execute()
    {
        if (!empty($this->header)) {
            $this->setOpt(CURLOPT_HTTPHEADER, $this->header);
        }
        $result = curl_exec($this->ch);

        $this->header = [];
        $this->close();
        return $result;
    }

    public function addHeader($param, $value)
    {
        array_push($this->header, "$param: $value");
        return $this;
    }

    public function setOpt($opt, $value)
    {
        curl_setopt($this->ch, $opt, $value);
        return $this;
    }

    public function setCookies($path = false, $keep = true)
    {
        if (empty($path)) {
            $path = __DIR__ . '/cookies.txt';
        }
        if(!$keep){
            file_put_contents($path, '');
        }

        return $this->setOpt(CURLOPT_COOKIEFILE, $path)
                    ->setOpt(CURLOPT_COOKIEJAR, $path);
    }

    public function getHeaderResponse()
    {
        return $this->setOpt(CURLOPT_HEADER, true);
    }

    public function noBody()
    {
        return $this->setOpt(CURLOPT_NOBODY, true);
    }

    public function post($url, $data, $build = true)
    {
        if ($build == true) {
            $data = http_build_query($data);
        }
        $this->setOpt(CURLOPT_URL, $url)
            ->setOpt(CURLOPT_POST, 1)
            ->setOpt(CURLOPT_POSTFIELDS, $data)
            ->setOpt(CURLOPT_URL, $url);
        $result = $this->execute();

        return $result;
    }

    public function put($url, $data, $build = true)
    {
        if ($build == true) {
            $data = http_build_query($data);
        }
        $this->setOpt(CURLOPT_URL, $url)
            ->setOpt(CURLOPT_POST, true)
            ->setOpt(CURLOPT_CUSTOMREQUEST, 'PUT')
            ->setOpt(CURLOPT_URL, $url);
        $result = $this->execute();

        return $result;
    }

    public function get($url)
    {
        $this->setOpt(CURLOPT_URL, $url);

        $result = $this->execute();
        return $result;
    }
}