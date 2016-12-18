<?php

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleDrive extends GoogleOAuth
{
    public function uploadImage($image, $folder = '')
    {
        $file_type = mime_content_type($image);
        $delimiter = uniqid();

        $data = '--' . $delimiter . "\r\n";
        $data .= 'Content-Type: application/json; charset=UTF-8' . "\r\n\r\n";

        $data .= json_encode([
                "name" => basename($image),
                'parents' => [$folder]
            ]) . "\r\n";

        $data .= '--' . $delimiter . "\r\n";
        $data .= 'Content-Type: ' . $file_type . "\r\n\r\n";

        $data .= file_get_contents($image) . "\r\n";
        $data .= '--' . $delimiter . "--\r\n";

        $client = new HttpClient();

        $res = $client->init()
            ->addHeader("Authorization: Bearer " . $this->token['access_token'])
            ->addHeader("Content-Type: multipart/related; boundary=$delimiter")
            ->addHeader("Content-Length: " . strlen($data))
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', $data, false);

        $res = json_decode($res, true);

        if (!empty($res['id'])) {

            $this->publicFile($res['id']);
            return $res['id'];
        }
        return '';
    }

    public function getImagesOnFolder($folder = 'root', $sub = false)
    {
        $url = "https://www.googleapis.com/drive/v3/files?q=mimeType+contains+'image'+and+'$folder'+in+parents+and+trashed=false&pageSize=1000";

        $client = new HttpClient();

        $result = [];

        $page_token = '';

        do {
            $temp_url = $url;
            if (!empty($page_token)) {
                $temp_url .= '&pageToken=' . $page_token;
            }

            $res = $client->init()->addHeader("Authorization: Bearer " . $this->token['access_token'])->get($temp_url);

            $res = json_decode($res, true);

            $result = empty($res['files']) ? $result : array_merge($result, $res['files']);

            $page_token = empty($res['nextPageToken']) ? '' : $res['nextPageToken'];

        } while (!empty($page_token));

        if($sub){
            $url = "https://www.googleapis.com/drive/v3/files?q=mimeType='application/vnd.google-apps.folder'+and+'$folder'+in+parents+and+trashed=false&pageSize=1000";

            $res = $client->init()->addHeader("Authorization: Bearer " . $this->token['access_token'])->get($url);

            $res = json_decode($res, true);

            if(!empty($res['files'])){
                foreach($res['files'] as $folder){
                    $result = array_merge($result, $this->getImagesOnFolder($folder['id'], $sub));
                }
            }
        }
        return $result;
    }

    public function publicFile($id)
    {
        $client = new HttpClient();

        $client->init()
            ->addHeader("Authorization: Bearer " . $this->token['access_token'])
            ->addHeader("Content-Type: application/json")
            ->post("https://www.googleapis.com/drive/v3/files/{$id}/permissions", json_encode(["role" => "reader", "type" => "anyone"]), false);
    }
}