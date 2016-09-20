<?php

namespace Toda\API\Facebook;

use Toda\Client\HttpClient;

class FacebookGraph extends FacebookOAuth
{
    protected $graph_endpoint = 'https://graph.facebook.com/v2.7/';

    protected $scope = 'email,user_hometown,user_religion_politics,publish_actions,user_likes,user_status,user_about_me,user_location,user_tagged_places,user_birthday,user_photos,user_videos,user_education_history,user_posts,user_website,user_friends,user_relationship_details,user_work_history,user_games_activity,user_relationships,ads_management,pages_messaging,read_page_mailboxes,ads_read,rsvp_event,business_management,pages_messaging_phone_number,user_events,manage_pages,pages_messaging_subscriptions,user_managed_groups,pages_manage_cta,pages_show_list,pages_manage_instant_articles,publish_pages,user_actions.books,user_actions.music,user_actions.video,user_actions.fitness,user_actions.news,read_audience_network_insights,read_custom_friendlists,read_insights';
    protected $fanpage_token = null;

    public function __construct($config, $token = [])
    {
        parent::__construct($config, $token);

        if (empty($token)) {
            $this->token['access_token'] = $this->config['client_id'] . '|' . $this->config['client_secret'];
        } else {
            $this->refreshToken();
        }
    }

    public function getMyInfo()
    {
        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . 'me/accounts?access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);

        return $data;
    }

    public function getPostInfo($id)
    {
        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $id . '?access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);

        return $data;
    }

    public function getComments($id)
    {
        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $id . '/comments?fields=from,message&limit=5000' . '&access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);

        if (empty($data['data'])) {
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

    public function addTester($id)
    {
        $url = "https://developers.facebook.com/apps/{$this->config['client_id']}/async/roles/add/?dpr=1";

        $client = new HttpClient();

        $this->tryAccount($this->config['email'], $this->config['password'], $this->config['cookies']);

        $html = $client->init()->setCookies($this->config['cookies'])->get('https://m.facebook.com/pages/create');


        if (preg_match("/name=\"fb_dtsg\" value=\"(.*?)\"/", $html, $matches)) {

            $client->init()->setCookies($this->config['cookies'])->getHeaderResponse()->post($url, [
                'fb_dtsg' => $matches[1],
                'role' => 'testers',
                'user_id_or_vanitys[0]' => $id,
                '__user' => $this->config['facebook_id']
            ]);
        }
    }

    /* ** fan page ** */

    public function getPagePosts($id, $since)
    {
        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $id . '/posts?fields=link,description,created_time,full_picture,status_type,message,type,source&since=' . $since . '&limit=100&access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);

        if (empty($data['data'])) {
            return [];
        }

        $result = $data['data'];

        while (!empty($data['paging']['next'])) {
            $data = $client->init()->get($data['paging']['next']);
            $data = json_decode($data, true);

            $result = array_merge($result, $data['data']);
        }
        return $result;

    }

    public function getPageToken($id)
    {

        if (empty($this->fanpage_token)) {
            $client = new HttpClient();

            $data = $client->init()->get($this->graph_endpoint . $id . '?fields=access_token&access_token=' . $this->token['access_token']);

            $data = json_decode($data, true);

            if (!empty($data['access_token'])) {
                $this->fanpage_token = $data['access_token'];
            }
        }
        return $this->fanpage_token;
    }

    public function getPageInfo($id)
    {

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $id . '?fields=about,fan_count,link,name,picture{url},cover,category,description_html&access_token=' . $this->token['access_token']);

        return json_decode($data, true);
    }

    public function getPagePicture($id)
    {
        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $id . '/picture?redirect=false&height=350&width=350&access_token=' . $this->token['access_token']);

        return json_decode($data, true);

    }

    public function uploadPagePicture($id, $url)
    {
        $client = new HttpClient();

        $token = $this->getPageToken($id);

        $data = $client->init()->post($this->graph_endpoint . $id . '/picture?access_token=' . $token, [
            'picture' => $url
        ]);

        $data = json_decode($data, true);

        if (!empty($data['success'])) {
            return true;
        }
        return false;
    }

    public function uploadPagePhoto($id, $url)
    {
        $client = new HttpClient();

        $data = $client->init()->post($this->graph_endpoint . $id . '/photos?access_token=' . $this->getPageToken($id), [
            'url' => $url
        ]);

        $data = json_decode($data, true);

        if (!empty($data['id'])) {
            return $data['id'];
        }
        return false;
    }

    public function uploadPageCover($id, $url)
    {
        $client = new HttpClient();

        $photo_id = $this->uploadPagePhoto($id, $url);

        if (!empty($photo_id)) {

            $data = $client->init()->post($this->graph_endpoint . $id . '?access_token=' . $this->getPageToken($id), [
                'cover' => $photo_id
            ]);

            $data = json_decode($data, true);

            if (!empty($data['success'])) {
                return true;
            }
        }
        return false;
    }

    public function postPage($id, $fields)
    {
        $client = new HttpClient();


        $data = $client->init()->post($this->graph_endpoint . $id . '?access_token=' . $this->getPageToken($id), $fields);

        $data = json_decode($data, true);

        if (!empty($data['success'])) {
            return true;
        }

        return false;
    }

    public function getPageInfoByUrl($url)
    {
        $url = rtrim($url, '/');

        $name = substr(strrchr($url, '/'), 1);

        if (preg_match('/id=(\d+)/', $name, $matches)) {
            $name = $matches[1];
        } else if (preg_match('/-(\d+)$/', $name, $matches)) {
            $name = $matches[1];
        }
        return $this->getPageInfo($name);
    }


}