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
    private $opt;

    public function __construct($opt = [])
    {
        $opt = array_merge([
            'verify_host' => false,
            'verify_peer' => false,
            'return_transfer' => true,
            'agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0',
            'proxy' => null
        ], $opt);

        $this->ch = curl_init();

        $this->opt = $opt;
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    protected function init()
    {
        $this->addOption(CURLOPT_SSL_VERIFYHOST, $this->opt['verify_host'])
            ->addOption(CURLOPT_SSL_VERIFYPEER, $this->opt['verify_peer'])
            ->addOption(CURLOPT_RETURNTRANSFER, $this->opt['return_transfer'])
            ->addOption(CURLOPT_USERAGENT, $this->opt['agent'])
            ->addOption(CURLOPT_PROXY, $this->opt['proxy']);

        if (!empty($this->opt['cookie'])) {
            $this->addOption(CURLOPT_COOKIE, $this->opt['cookies']);
        }
        if (isset($this->opt['cookie_file'])) {
            if (empty($this->opt['cookie_file'])) {
                $this->opt['cookie_file'] = __DIR__ . '/cookies.txt';
                file_put_contents($this->opt['cookie_file'], '');
            }

            $this->addOption(CURLOPT_COOKIEFILE, $this->opt['cookie_file'])
                ->addOption(CURLOPT_COOKIEJAR, $this->opt['cookie_file']);
        }
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


    public function responseHeader($body = false)
    {
        return $this->addOption(CURLOPT_NOBODY, !$body)
            ->addOption(CURLOPT_HEADER, true);
    }

    public function follow()
    {
        return $this->addOption(CURLOPT_FOLLOWLOCATION, true);
    }


    private function execute()
    {
        $this->init();

        if (!empty($this->header)) {
            $this->addOption(CURLOPT_HTTPHEADER, $this->header);
        }
        $result = curl_exec($this->ch);

        curl_reset($this->ch);

        return $result;
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