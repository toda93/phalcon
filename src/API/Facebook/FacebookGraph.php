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

        $data = $client->init()->get($this->endpoint . $id . '/comments?fields=from,message&limit=5000' . '&access_token=' . $this->access_token);

        $data = json_decode($data, true);
        
        if(empty($data['data'])){
            return [];
        }

        $result = $data['data'];

        while (!empty($data['paging']['next'])) {
            $data = $client->init()->get($data['paging']['next']);
            $data = json_decode($data, true);

            $result = array_merge($data['data'], $result);
        }
        return $result;
    }

    public static function getIdByUrl($url){
        $response = file_get_contents("http://graph.facebook.com/fql?q=select url, id, type, site from object_url where url = \"$url\"");
        $data = json_decode($response, true);

        return empty($data['data']) ? false : $data['data'][0]['id'];
    }

    public function getUserPageLikes($id){
//        $client = new HttpClient();
//
//        $data = $client->init()->get($this->endpoint . $id . '/likes?fields=from,message&limit=5000' . '&access_token=' . $this->access_token);

    }
}