<?php

namespace app\models;

class Author extends \yii\db\ActiveRecord {

    public $postCount = 0;

    public static function tableName() {
        return 'authors';
    }

    public function getFeedMetrics($id, $a = null, $b = null) {
        $feed = Feed::findOne($id);
        $feedSql = $feed->feedSql();

        $query = Post::find()->where($feedSql)->andWhere(['author_id' => $this->author_id, 'source' => $this->source]);
        if ($a != null) {
            $query->andWhere("posts.created_source >= $a");
        }
        if ($b != null) {
            $query->andWhere("posts.created_source <= $b");
        }
        $posts_ = $query->orderBy("posts.created_source DESC")->all();
        $count = count($posts_);
        $likes = 0;
        foreach ($posts_ as $post) {
            $likes+=$post->likes;
        }
        
        if ($a != null) {
            $start = strtotime($a);
        } else {
            $start = strtotime($posts_[$count - 1]->created_source);
        }
        if ($b != null) {
            $end = strtotime($b);
        } else {
            $end = strtotime($posts_[0]->created_source);
        }
        $time = round(($end - $start) / (60 * 60), 2); //hour difference
        if ($time == 0) {
            $time = 1;
        }

        return [
            'name' => $this->name,
            'allTimeCount' => $this->posts,
            'followers' => $this->followers,
            'following' => $this->following,
            'count' => $count,
            'likes' => $likes,
            'allTimeCount' => $this->posts,
            'postsRatio' => round($count / $time, 2),
            'likesRatio' => round($likes / $time, 2),
            'timeElapsed' => $time,
            'rawTimeElapsed' => $end - $start,
            'start' => $a,
            'end' => $b
        ];
    }

}
