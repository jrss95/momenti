<?php

namespace app\models;

class Post extends \yii\db\ActiveRecord
{
    public static function tableName() {
        return 'posts';
    }

    public function hasMedia() {
    	$media = json_decode($this->media);
    	return $media !== false && count($media) > 0;
    }
    public function getMedia() {
    	$media = json_decode($this->media);

    	$imgs = [];
        $videos = [];
        $youtube = [];
        $urls = [];
        foreach ($media as $url) {
            
            $info = pathinfo($url);
            $ext = isset($info['extension']) ? $info['extension'] : '';

            $isImage = $ext == 'jpg' || $ext == 'jpeg' || $ext == 'png';
            $isVideo = $ext == 'mp4';
            $isYoutube = strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false;

            if($isImage) {
                $imgs[] = [
                    'name' => basename($url),
                    'url' => $url
                ];
            }
            else if($isVideo) {
                $videos[] = [
                    'name' => basename($url),
                    'url' => $url
                ];
            }
            else if($isYoutube) {
                $youtube[] = $url;
            } else {
                $urls[] = $url;
            }
        }

        $return = [
        'imgs' => array_slice($imgs, 0, 3),
        'videos' => $videos,
        'youtube' => $youtube,
        'urls' => $urls
        ];

        return json_decode(json_encode($return));
    }
}
