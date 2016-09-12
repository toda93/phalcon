<?php
namespace Toda\API;

class APIManager
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function facebook($select, $token = [])
    {
        $cls = 'Toda\API\Facebook\Facebook' . $this->config->facebook->$select->type;

        if (empty($token) && !empty($this->config->facebook->$select->access_token)) {
            $token['access_token'] = $this->config->facebook->$select->access_token;
        }

        return new $cls([
            'client_id' => $this->config->facebook->$select->client_id,
            'client_secret' => $this->config->facebook->$select->client_secret,
            'callback' => $this->config->facebook->$select->callback
        ], $token);
    }

    public function google($select, $token = [])
    {
        $cls = 'Toda\API\Google\Google' . $this->config->facebook->$select->type;

        return new $cls([
            'client_id' => $this->config->facebook->$select->client_id,
            'client_secret' => $this->config->facebook->$select->client_secret,
            'callback' => $this->config->facebook->$select->callback
        ], $token);
    }
}