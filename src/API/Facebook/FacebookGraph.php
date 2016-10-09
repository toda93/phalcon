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

    public function getPostInfo($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $post_id . '?fields=from&access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);

        return $data;
    }

    public function getPostImage($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $post_id . '?fields=full_picture&access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);


        if (empty($data['full_picture'])) {
            return false;
        }

        return $data['full_picture'];
    }

    public function countPost($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $post_id . '?fields=shares,comments.limit(0).summary(1),reactions.limit(0).summary(1)&access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);


        if (empty($data['shares'])) {
            return false;
        }

        return [
            'count_share' => $data['shares']['count'],
            'count_like' => $data['reactions']['summary']['total_count'],
            'count_comment' => $data['comments']['summary']['total_count'],
        ];
    }


    public function getComments($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $post_id . '/comments?fields=from,message&limit=5000' . '&access_token=' . $this->token['access_token']);

        $data = json_decode($data, true);

        if (empty($data['data'])) {
            return [];
        }
        return $data['data'];
    }

    public function getAllComments($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $post_id . '/comments?fields=from,message&limit=5000' . '&access_token=' . $this->token['access_token']);

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

    public function replyComment($fanpage_id, $comment_id, $message)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->post($this->graph_endpoint . $comment_id . '/comments?access_token=' . $this->getPageToken($fanpage_id), [
            'message' => $message
        ]);

        $data = json_decode($data, true);

        if (empty($data['error'])) {
            return true;
        }

        return false;
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

    public function getMessengerName($fanpage_id, $user_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $user_id . '?access_token=' . $this->getPageToken($fanpage_id));


        $data = json_decode($data, true);

        if (empty($data['first_name'])) {
            return '';
        } else {
            return $data['first_name'] . ' ' . $data['last_name'];
        }
    }

    public function getPagePosts($fanpage_id, $since)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $fanpage_id . '/posts?fields=link,description,created_time,full_picture,status_type,message,type,source&since=' . $since . '&limit=100&access_token=' . $this->token['access_token']);

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

    public function getPageToken($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        if (empty($this->fanpage_token)) {
            $client = new HttpClient();

            $data = $client->init()->get($this->graph_endpoint . $fanpage_id . '?fields=access_token&access_token=' . $this->token['access_token']);

            $data = json_decode($data, true);

            if (!empty($data['access_token'])) {
                $this->fanpage_token = $data['access_token'];
            }
        }
        return $this->fanpage_token;
    }

    public function getPageInfo($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $fanpage_id . '?fields=about,fan_count,link,name,picture{url},cover,category,description_html&access_token=' . $this->token['access_token']);

        return json_decode($data, true);
    }

    public function isManagePage($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        return !empty($this->getPageToken($fanpage_id));
    }

    public function getPagePicture($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $fanpage_id . '/picture?redirect=false&height=350&width=350&access_token=' . $this->token['access_token']);

        return json_decode($data, true);

    }

    public function getPageVideoSource($fanpage_id, $video_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->get($this->graph_endpoint . $video_id . '?fields=source&access_token=' . $this->getPageToken($fanpage_id));

        $data = json_decode($data, true);

        if (!empty($data['source'])) {
            return $data['source'];
        }
        return false;
    }

    public function uploadPagePicture($fanpage_id, $url)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->post($this->graph_endpoint . $fanpage_id . '/picture?access_token=' . $this->getPageToken($fanpage_id), [
            'picture' => $url
        ]);

        $data = json_decode($data, true);

        if (!empty($data['success'])) {
            return true;
        }
        return false;
    }

    public function uploadPagePhoto($fanpage_id, $fields)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->post($this->graph_endpoint . $fanpage_id . '/photos?access_token=' . $this->getPageToken($fanpage_id), $fields);

        $data = json_decode($data, true);

        if (!empty($data['id'])) {
            return $data['id'];
        }
        return false;
    }

    public function uploadPageCover($fanpage_id, $url)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $photo_id = $this->uploadPagePhoto($fanpage_id, [
            'url' => $url
        ]);

        if (!empty($photo_id)) {

            $data = $client->init()->post($this->graph_endpoint . $fanpage_id . '?access_token=' . $this->getPageToken($fanpage_id), [
                'cover' => $photo_id
            ]);

            $data = json_decode($data, true);

            if (!empty($data['success'])) {
                return true;
            }
        }
        return false;
    }

    public function changePageInfo($fanpage_id, $fields)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->post($this->graph_endpoint . $fanpage_id . '?access_token=' . $this->getPageToken($fanpage_id), $fields);

        $data = json_decode($data, true);

        if (!empty($data['success'])) {
            return true;
        }

        return false;
    }

    public function postPageFeed($fanpage_id, $fields)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $client = new HttpClient();

        $data = $client->init()->post($this->graph_endpoint . $fanpage_id . '/feed?access_token=' . $this->getPageToken($fanpage_id), $fields);

        $data = json_decode($data, true);

        if (!empty($data['success'])) {
            return true;
        }

        return false;
    }

    protected function trimFacebookId($id)
    {
        if (preg_match('/facebook\.com\/(.*?)\//', $id, $matches)) {

            $id = $matches[1];

            if (preg_match('/id=(\d+)/', $id, $matches)) {
                $id = $matches[1];
            }
        }
        return $id;
    }

    protected function trimPostId($id)
    {
        $id = preg_replace('/\?(.*?)$/', '', $id);

        $id = rtrim($id, '/');

        if (preg_match('/(\d+)$/', $id, $matches)) {

            $fanpage_id = $this->trimFacebookId($id);

            $id = $matches[1];

            if ($fanpage_id != $id) {

                $page_info = $this->getPageInfo($fanpage_id);

                if (!empty($page_info['id'])) {
                    $id = $page_info['id'] . '_' . $id;
                }
            }
        }
        return $id;
    }
}