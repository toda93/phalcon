<?php

namespace Toda\API\Facebook;


class FacebookGraph extends FacebookOAuth
{
    protected $graph_endpoint = 'https://graph.facebook.com/v2.7/';

    protected $fanpage_token = null;


    public function getUserInfo($uid = 'me')
    {
        $res = $this->client->get($this->graph_endpoint . $uid . '?access_token=' . $this->token['access_token']);

        $res = json_decode($res, true);

        return $res;
    }

    public function getPostInfo($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $res = $this->client->get($this->graph_endpoint . $post_id . '?fields=from&access_token=' . $this->token['access_token']);

        $res = json_decode($res, true);

        return $res;
    }

    public function getPostImage($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $res = $this->client->get($this->graph_endpoint . $post_id . '?fields=full_picture&access_token=' . $this->token['access_token']);

        $res = json_decode($res, true);


        if (empty($res['full_picture'])) {
            return '';
        }
        return $res['full_picture'];
    }

    public function countPost($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $res = $this->client->get($this->graph_endpoint . $post_id . '?fields=shares,comments.limit(0).summary(1),reactions.limit(0).summary(1)&access_token=' . $this->token['access_token']);

        $res = json_decode($res, true);


        if (empty($res['shares'])) {
            return [];
        }

        return [
            'count_share' => $res['shares']['count'],
            'count_like' => $res['reactions']['summary']['total_count'],
            'count_comment' => $res['comments']['summary']['total_count'],
        ];
    }

    public function getComments($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $res = $this->client->get($this->graph_endpoint . $post_id . '/comments?fields=from,message&limit=5000' . '&access_token=' . $this->token['access_token']);

        $res = json_decode($res, true);

        if (empty($res['data'])) {
            return [];
        }
        return $res['data'];
    }

    public function getAllComments($post_id)
    {
        $post_id = $this->trimPostId($post_id);

        $res = $this->client->get($this->graph_endpoint . $post_id . '/comments?fields=from,message&limit=5000' . '&access_token=' . $this->token['access_token']);

        $res = json_decode($res, true);

        if (empty($res['data'])) {
            return [];
        }

        $result = $res['data'];

        while (!empty($res['paging']['next'])) {
            $res = $this->client->get($res['paging']['next']);
            $res = json_decode($res, true);

            $result = array_merge($res['data'], $result);
        }
        return $result;
    }

    public function replyComment($fanpage_id, $comment_id, $message)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->post($this->graph_endpoint . $comment_id . '/comments?access_token=' . $this->getPageToken($fanpage_id), [
            'message' => $message
        ]);

        $res = json_decode($res, true);

        if (empty($res['error'])) {
            return true;
        }

        return false;
    }

    /* ** fan page ** */

    public function getMessengerName($fanpage_id, $user_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->get($this->graph_endpoint . $user_id . '?access_token=' . $this->getPageToken($fanpage_id));

        $res = json_decode($res, true);

        if (empty($res['first_name'])) {
            return '';
        } else {
            return $res['first_name'] . ' ' . $res['last_name'];
        }
    }

    public function getPagePosts($fanpage_id, $since)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->get($this->graph_endpoint . $fanpage_id . '/posts?fields=link,description,created_time,full_picture,status_type,message,type,source&since=' . $since . '&limit=100&access_token=' . $this->token['access_token']);

        $res = json_decode($res, true);

        if (empty($res['data'])) {
            return [];
        }

        $result = $res['data'];

        while (!empty($res['paging']['next'])) {
            $res = $this->client->get($res['paging']['next']);
            $res = json_decode($res, true);

            $result = array_merge($result, $res['data']);
        }
        return $result;

    }

    public function getPageToken($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        if (empty($this->fanpage_token)) {

            $res = $this->client->get($this->graph_endpoint . $fanpage_id . '?fields=access_token&access_token=' . $this->token['access_token']);

            $res = json_decode($res, true);

            if (!empty($res['access_token'])) {
                $this->fanpage_token = $res['access_token'];
            }
        }
        return $this->fanpage_token;
    }

    public function getPageInfo($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->get($this->graph_endpoint . $fanpage_id . '?fields=about,fan_count,link,name,picture{url},cover,category,description_html&access_token=' . $this->token['access_token']);

        return json_decode($res, true);
    }

    public function isManagePage($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        return !empty($this->getPageToken($fanpage_id));
    }

    public function getPagePicture($fanpage_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->get($this->graph_endpoint . $fanpage_id . '/picture?redirect=false&height=350&width=350&access_token=' . $this->token['access_token']);

        return json_decode($res, true);

    }

    public function getPageVideoSource($fanpage_id, $video_id)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->get($this->graph_endpoint . $video_id . '?fields=source&access_token=' . $this->getPageToken($fanpage_id));

        $res = json_decode($res, true);

        if (!empty($res['source'])) {
            return $res['source'];
        }
        return false;
    }

    public function uploadPagePicture($fanpage_id, $url)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->post($this->graph_endpoint . $fanpage_id . '/picture?access_token=' . $this->getPageToken($fanpage_id), [
            'picture' => $url
        ]);

        $res = json_decode($res, true);

        if (!empty($res['success'])) {
            return true;
        }
        return false;
    }

    public function uploadPagePhoto($fanpage_id, $fields)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->post($this->graph_endpoint . $fanpage_id . '/photos?access_token=' . $this->getPageToken($fanpage_id), $fields);

        $res = json_decode($res, true);

        if (!empty($res['id'])) {
            return $res['id'];
        }
        return '';
    }

    public function uploadPageCover($fanpage_id, $url)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $photo_id = $this->uploadPagePhoto($fanpage_id, [
            'url' => $url
        ]);

        if (!empty($photo_id)) {

            $res = $this->client->post($this->graph_endpoint . $fanpage_id . '?access_token=' . $this->getPageToken($fanpage_id), [
                'cover' => $photo_id
            ]);

            $res = json_decode($res, true);

            if (!empty($res['success'])) {
                return true;
            }
        }
        return false;
    }

    public function changePageInfo($fanpage_id, $fields)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->post($this->graph_endpoint . $fanpage_id . '?access_token=' . $this->getPageToken($fanpage_id), $fields);

        $res = json_decode($res, true);

        if (!empty($res['success'])) {
            return true;
        }

        return false;
    }

    public function postPageFeed($fanpage_id, $fields)
    {
        $fanpage_id = $this->trimFacebookId($fanpage_id);

        $res = $this->client->post($this->graph_endpoint . $fanpage_id . '/feed?access_token=' . $this->getPageToken($fanpage_id), $fields);

        $res = json_decode($res, true);

        if (!empty($res['success'])) {
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