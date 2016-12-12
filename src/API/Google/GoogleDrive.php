<?php

namespace Toda\API\Google;

use Toda\Client\HttpClient;

class GoogleDrive extends GoogleOAuth
{
    public function uploadImage($file)
    {

        $client = new HttpClient();

        $data = file_get_contents($file);

        $res = $client->init()
            ->addHeader("Authorization: Bearer " . $this->token['access_token'])
            ->addHeader("Content-Type: " . mime_content_type($file))
            ->addHeader("Content-Length" . strlen($data))
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=media', $data, false);

        $res = json_decode($res, true);

        if (!empty($res['id'])) {

            $this->publicFile($res['id']);
            return $res['id'];
        }
        return '';
    }

    protected function publicFile($id)
    {
        $client = new HttpClient();

        $client->init()
            ->addHeader("Authorization: Bearer " . $this->token['access_token'])
            ->addHeader("Content-Type: application/json")
            ->post("https://www.googleapis.com/drive/v3/files/{$id}/permissions", json_encode(["role" => "reader", "type" => "anyone"]), false);
    }
}