<?php

namespace Toda\API\Facebook;

use Toda\Client\HttpClient;

class FacebookGraph extends FacebookOAuth
{
    protected $graph_endpoint = 'https://graph.facebook.com/v2.6/';

    protected $scope = 'email,user_hometown,user_religion_politics,publish_actions,user_likes,user_status,user_about_me,user_location,user_tagged_places,user_birthday,user_photos,user_videos,user_education_history,user_posts,user_website,user_friends,user_relationship_details,user_work_history,user_games_activity,user_relationships,ads_management,pages_manage_instant_articles,publish_pages,ads_read,pages_messaging,read_page_mailboxes,business_management,pages_messaging_phone_number,rsvp_event,manage_pages,pages_messaging_subscriptions,user_events,pages_manage_cta,pages_show_list,user_managed_groups,user_actions.books,user_actions.music,user_actions.video,user_actions.fitness,user_actions.news,read_audience_network_insights,read_custom_friendlists,read_insights';

    public function __construct($config, $token = [])
    {
        parent::__construct($config, $token);

        if(empty($token)){
            $this->token['access_token'] = $this->config['client_id'] . '|' . $this->config['client_secret'];
        } else {
            $this->refreshToken();
        }
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

    public function getPageInfoByUrl($url)
    {

        $client = new HttpClient();

        $url = rtrim($url, '/');

        $name = substr(strrchr($url, '/'), 1);

        if (preg_match('/id=(\d+)/', $name, $matches)) {
            $name = $matches[1];
        } else if (preg_match('/-(\d+)$/', $name, $matches)) {
            $name = $matches[1];
        }
        $data = $client->init()->get($this->graph_endpoint . $name . '?fields=fan_count&access_token=' . $this->token['access_token']);

        return json_decode($data, true);
    }
}