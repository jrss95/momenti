<?php

namespace app\models\social;

require dirname(dirname(dirname(__FILE__))) . '/vendor/Twitter/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use app\models\User;
use app\models\Author;
use app\models\Post;
use app\models\Account;

class Twitter {

    public $user;
    private $access_token = [];
    private $access_token_secret = [];

    public function __construct(User $__user) {
        $this->user = $__user;
        $accounts = Account::find()->where(['user_id' => $this->user->id, 'network' => 'twitter'])->all();
        if (count($accounts) == 0) {
            throw new \yii\base\ErrorException('No account found!');
        }

        foreach ($accounts as $account) {
            $credentials = json_decode($account->credentials);
            $this->access_token[] = $credentials[0];
            $this->access_token_secret[] = $credentials[1];
        }
    }

    public function savePosts($statuses, $interaction = null) {
        $count = count($statuses);
        foreach ($statuses as $status) {
            $this->savePost($status);
            $this->saveAuthor($status->user);
            if ($interaction != null) {
                $this->saveInteraction($interaction, $status->user->id, $status->id);
            }
        }
    }

    public function savePost($status) {
        if ($status->id_str == '711230553400303616') {
            print_r($status);
            exit();
        }
        $post = Post::findOne(['post_id' => $status->id_str]);
        if ($post == null) {
            $post = new Post();
        }

        $postInfo = [
            'post_id' => $status->id_str,
            'source' => 'twitter',
            'user_id' => $this->user->id,
            'text' => $status->text,
            'author_id' => $status->user->id,
            'author' => $status->user->screen_name,
            'created_source' => date('Y-m-d H:i:s', strtotime($status->created_at))
            ];
        if (isset($status->retweeted_status)) {
            $reweeted = $status->retweeted_status;
            $postInfo['likes'] = $reweeted->favorite_count;
            $postInfo['shares'] = $reweeted->retweet_count;
        } else {
            $postInfo['likes'] = $status->favorite_count;
            $postInfo['shares'] = $status->retweet_count;
        }
        //get tags
        $raw_tags = $status->entities->hashtags;
        $tags = [];
        foreach ($raw_tags as $tag) {
            $tags[] = $tag->text;
        }
        $postInfo['tags'] = json_encode($tags);

        //get media type/media urls
        $type = 'text';
        $entities = $status->entities;
        $extended = isset($status->extended_entities) ? $status->extended_entities : [];

        $urls = [];
        $ids = [];

        if (count($extended) > 0 && property_exists($entities, 'media')) {
            foreach ($extended->media as $key => $_media) {
                $type = $_media->type;
                if ($type == 'video') {
                    $urls[] = $_media->video_info->variants[0]->url;
                } else {
                    $urls[] = $_media->media_url;
                }

                $ids[] = $_media->id;
            }
        }
        if (property_exists($entities, 'media') && count($entities->media) > 0) {
            $_media = $entities->media[0];
            $type = $type != 'video' ? $_media->type : $type;

            foreach ($entities->media as $_media) {
                if (!in_array($_media->id, $ids)) {
                    $urls[] = $_media->media_url;
                }
            }
        }
        if (property_exists($entities, 'urls') && count($entities->urls) > 0) {
            $type = ($type == 'text') ? 'link' : $type;
            foreach ($entities->urls as $_url) {
                $urls[] = $_url->expanded_url;
            }
        }

        $postInfo['media'] = json_encode($urls);
        $postInfo['media_type'] = $type;
        $postInfo['updated'] = date('Y-m-d H:i:s');

        $post->setAttributes($postInfo, false);
        $a = $post->save();
    }

    public function saveAuthor($user) {
        $author = Author::findOne(['author_id' => $user->id]);
        if ($author == null) {
            $author = new Author();
        }
        $authorInfo = [
            'author_id' => $user->id,
            'source' => 'twitter',
            'name' => $user->screen_name,
            'posts' => $user->statuses_count,
            'followers' => $user->followers_count,
            'following' => $user->friends_count,
            'likes' => $user->favourites_count,
            'language' => $user->lang,
        ];
        $author->setAttributes($authorInfo, false);
        $author->save();
    }

    public function saveInteraction($a, $b, $c) {
        $sql = "SELECT * FROM interactions WHERE action=:a AND user_id=:b and object_id=:c";
        $sql_params = [":a" => $a, ":b" => $b, ":c" => $c];
        $record = \Yii::$app->db->createCommand($sql, $sql_params)->queryOne();
        if ($record === false) {
            $data = [
                'action' => $a,
                'user_id' => $b,
                'object_id' => $c,
            ];
            \Yii::$app->db->createCommand()->insert('interactions', $data)->execute();
        }
    }

    public function updateFeed($since = null, $count = 200) {
        for ($i = 0; $i < count($this->access_token); $i++) {
            $connection = new TwitterOAuth('nRMjMfRf7aOwqt1L1rgYTUsGn', 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV', $this->access_token[$i], $this->access_token_secret[$i]);

            $params = ["exclude_replies" => true, 'count' => $count];
            if ($since != null) {
                $params['since_id'] = $since;
            }
            $statuses = $connection->get("statuses/user_timeline", $params);
            $this->savePosts($statuses);
        }
    }

    public function updateMentions($since = null, $count = 200) {
        for ($i = 0; $i < count($this->access_token); $i++) {
            $connection = new TwitterOAuth('nRMjMfRf7aOwqt1L1rgYTUsGn', 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV', $this->access_token[$i], $this->access_token_secret[$i]);

            $params = ["exclude_replies" => true, 'count' => $count];
            if ($since != null) {
                $params['since_id'] = $since;
            }
            $statuses = $connection->get("statuses/mentions_timeline", $params);
            $this->savePosts($statuses, 'mentioned');
        }
    }

    public function updateLikes($since = null, $count = 200) {
        for ($i = 0; $i < count($this->access_token); $i++) {
            $connection = new TwitterOAuth('nRMjMfRf7aOwqt1L1rgYTUsGn', 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV', $this->access_token[$i], $this->access_token_secret[$i]);

            $params = ["exclude_replies" => true, 'count' => $count];
            if ($since != null) {
                $params['since_id'] = $since;
            }
            $statuses = $connection->get("favorites/list", $params);
            $this->savePosts($statuses, 'liked');
        }
    }

    public function updateRetweets() {
        for ($i = 0; $i < count($this->access_token); $i++) {
            $connection = new TwitterOAuth('nRMjMfRf7aOwqt1L1rgYTUsGn', 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV', $this->access_token[$i], $this->access_token_secret[$i]);

            $statuses = $connection->get("statuses/retweets_of_me");
            $this->savePosts($statuses);
        }
    }

    //TODO: get my followers tweets

    public function search($feed_id, $last, $tags, $authors, $extra=[]) {
        if (count($tags) == 1 && $tags[0] == '') {
            $tags = [];
        }
        if (count($authors) == 1 && $authors[0] == '') {
            $authors = [];
        }
        
        //for ($i = 0; $i < count($this->access_token); $i++) {
        for ($i = 0; $i < 1; $i++) {
            $connection = new TwitterOAuth('nRMjMfRf7aOwqt1L1rgYTUsGn', 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV', $this->access_token[$i], $this->access_token_secret[$i]);

            $countTags = count($tags) ;
            $countAuthors = count($authors);
            if ($countTags > 0) {
                $formattedTags = [];
                foreach ($tags as $tag) {
                    $formattedTags[] = "#$tag";
                }
                $_tags = implode(" OR ", $formattedTags);
            }
            if ($countAuthors > 0) {
                $formattedAuthors = [];
                foreach ($authors as $author) {
                    $formattedAuthors[] = "from:$author";
                }
                $_authors = implode(" OR ", $formattedAuthors);
            }
            if ($countTags > 0 && $countAuthors > 0) {
                $query = "$_tags OR $_authors";
            }
            else {
                if ($countTags > 0) {
                    $query = $_tags;
                }
                if ($countAuthors > 0) {
                    $query = $_authors;
                }
            }
            
            $params_ = ['q' => $query, 'result_type' => 'recent', 'count' => 50, 'since_id'=>$last];
            $params = array_merge($params_, $extra);
            $statuses = $connection->get("search/tweets", $params);
            $update = [
                'feed_id' => $feed_id,
                'action' => $statuses->search_metadata->refresh_url
            ];
            \Yii::$app->db->createCommand()->insert('updates', $update)->execute();
            $newLast = $statuses->search_metadata->max_id_str;
            $this->savePosts($statuses->statuses);
        }
        
        return $newLast;
    }

    public function update($action) {
        $connection = new TwitterOAuth('nRMjMfRf7aOwqt1L1rgYTUsGn', 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV', $this->access_token[$i], $this->access_token_secret[$i]);
        parse_str(substr($action, 1), $params);
        $statuses = $connection->get("search/tweets", $params);
        $this->savePosts($statuses->statuses);
    }

    public function welcome() {
        $this->updateFeed(null, 50);
        $this->updateMentions(null, 50);
        $this->updateLikes(null, 50);

        \Yii::$app->session->addFlash('info', 'Your account has been added. We got some of your info including your posts, posts that mentioned you and posts you liked! Now you can create your own custom feeds.');
        header("Location: /momenti/web");
        exit();
    }

}
