<?php

namespace Toda\API\Facebook;

use Toda\Client\HttpClient;

class FacebookGraph
{
    private $access_token;

    protected $endpoint = 'https://graph.facebook.com/';

    public function __construct()
    {
        $this->access_token = page('facebook_oauth_client_id') . '|' . page('facebook_oauth_client_secret');
    }

    public function getComments($id)
    {
        $client = new HttpClient();

        $data = $client->init()->get($this->endpoint . $id . '/comments?fields=from,message&limit=1000' . '&access_token=' . $this->access_token);

        $data = json_decode($data, true);
        $result = $data['data'];

        while (!empty($data['paging']['next'])) {
            $data = $client->init()->get($data['paging']['next']);
            $data = json_decode($data, true);

            $result = array_merge($data['data'], $result);
        }

        return $result;
    }

    public function getUserPageLikes($id){
        $client = new HttpClient();

        $data = $client->init()->get($this->endpoint . $id . '/likes?fields=from,message&limit=1000' . '&access_token=' . $this->access_token);

        var_dump($data); exit;
    }
}