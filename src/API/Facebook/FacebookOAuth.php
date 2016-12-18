<?php

namespace Toda\API\Facebook;

use Toda\Client\HttpClient;

class FacebookOAuth
{
    protected $scope = 'email,user_hometown,user_religion_politics,publish_actions,user_likes,user_status,user_about_me,user_location,user_tagged_places,user_birthday,user_photos,user_videos,user_education_history,user_posts,user_website,user_friends,user_relationship_details,user_work_history,user_games_activity,user_relationships,ads_management,pages_messaging,read_page_mailboxes,ads_read,rsvp_event,business_management,pages_messaging_phone_number,user_events,manage_pages,pages_messaging_subscriptions,user_managed_groups,pages_manage_cta,pages_show_list,pages_manage_instant_articles,publish_pages,user_actions.books,user_actions.music,user_actions.video,user_actions.fitness,user_actions.news,read_audience_network_insights,read_custom_friendlists,read_insights';

    protected $token = [];

    protected $config = [];

    public function __construct($config, $token = [])
    {
        $this->config = $config;
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getUrlAuthCode()
    {
        $url = 'https://www.facebook.com/v2.7/dialog/oauth?' . http_build_query([
                'return_scopes' => 'true',
                'scope' => $this->scope,
                'response_type' => 'code',
                'auth_type' => 'rerequest',
                'client_id' => $this->config['client_id'],
                'redirect_uri' => $this->config['callback']
            ]);
        return $url;
    }

    public function refreshToken()
    {
        if (!empty($this->token['expired']) && $this->token['expired'] <= time()) {

            $client = new HttpClient();

            $res = $client->init()->get('https://graph.facebook.com/oauth/access_token?' . http_build_query([
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                    'fb_exchange_token' => $this->token['access_token']
                ]));
            $token = json_decode($res, true);

            if (!empty($token['access_token'])) {
                $this->token['access_token'] = $token['access_token'];
            }
        }
    }

    public function getTokenByCode($code)
    {
        $client = new HttpClient();
        $res = $client->init()->post('https://graph.facebook.com/v2.7/oauth/access_token', [
            'code' => $code,
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'redirect_uri' => $this->config['callback']
        ]);

        $token = json_decode($res, true);

        $result = [];
        if (!array_key_exists('error', $token)) {
            $result['access_token'] = $token['access_token'];
            $result['token_type'] = $token['token_type'];
            $result['expired'] = (int)$token['expires_in'] + time();

            if (!empty($token['refresh_token'])) {
                $result['refresh_token'] = $token['refresh_token'];
            }
        }
        return $result;
    }
}
