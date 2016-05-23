<?php

namespace app\models\social;

use app\models\User;
use app\models\Account;

class Instagram {

    public $user;
    private $access_token;

    public function __construct(User $__user) {
        $this->user = $__user;
        $account = Account::findOne(['user_id' => $this->user->id, 'network' => 'instagram']);
        if ($account == null) {
            throw new \yii\base\ErrorException('No account found!');
        }

        $credentials = json_decode($account->credentials);
        $this->access_token = $credentials[0];
    }

    public function savePosts($statuses, $interaction = null) {
        foreach ($statuses as $status) {
            $this->savePost($status);
            $this->saveAuthor($status->user);
            if ($interaction != null) {
                $this->saveInteraction($interaction, $status->user->id, $status->id);
            }
        }
    }

    public function savePost($status) {
        echo "<textarea>";
        print_r($status);
        exit();
        $post = Post::findOne(['post_id' => $status->id_str]);
        if ($post == null) {
            $post = new Post();
        }

        $postInfo = [
            'post_id' => $status->id,
            'source' => 'instagram',
            'user_id' => $this->user->id,
            'text' => $status->text,
            'author_id' => $status->user->id,
            'author' => $status->user->username,
            'created_source' => date('Y-m-d H:i:s', $status->created_time),
            'tags' => json_encode($status->tags),
            'comments' => $status->comments->count,
            'likes' => $status->likes->count,
            'type' => $status->type,
        ];
        //TODO: include users mentioned as part of the tags
        $urls = [];
        if ($status->type == 'image') {
            $urls[] = $status->images->standard_resolution->url;
        } else if ($status->type == 'video') {
            //TODO: get videos
            //$urls[] = $status->meh;
        }
        $postInfo['media'] = json_encode($urls);
        $postInfo['updated'] = date('Y-m-d H:i:s');

        $post->setAttributes($postInfo, false);
        $post->save();
    }

    public function saveAuthor($user) {
        $author = Author::findOne(['author_id' => $user->id]);
        if ($author == null) {
            $author = new Author();
        }
        $response = Instagram::request('/users/'.$user->id, ['access_token' => $this->access_token]);
        if($response->meta->code == 200) {
            $_user = $response->data;
        } else {
            $_user = null;
        }
        $authorInfo = [
            'author_id' => $user->id,
            'source' => 'instagram',
            'name' => $user->username,
            'posts' => (!isset($_user)) ? 0 : $_user->counts->media,
            'followers' => (!isset($_user)) ? 0 : $_user->counts->followed_by,
            'following' => (!isset($_user)) ? 0 : $_user->counts->follows,
            //'language' => $user->lang,
        ];
        $author->setAttributes($authorInfo, false);
        $author->save();
    }

    public function saveInteraction($a, $b, $c) {
        $sql = "SELECT * FROM interactions WHERE action=:a AND user_id=:b and object_id=:c";
        $sql_params = [":a"=>$a, ":b"=>$b, ":c"=>$c];
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

    public function updateFeed($last=null, $count=100) {
        $endpoint = '/users/self/media/recent';
        $params = ['access_token' => $this->access_token, 'count'=>$count];
        if($last != null) {
            $params['min_id'] = $last;
        }

        $statuses = Instagram::request($endpoint, $params);
        $this->savePosts($statuses->data);
    }

    public function updateMentions($last=null, $count=100) {
        //TODO: get user
        $username = 'newyork';
        $endpoint = '/tags/'.$username.'/media/recent';
        $params = ['access_token' => $this->access_token, 'count'=>$count];
        if($last != null) {
            $params['min_id'] = $last;
        }

        $statuses = Instagram::request($endpoint, $params);
        $this->savePosts($statuses->data);
    }

    public function updateLikes($last=null, $count=100) {
        $endpoint = '/users/self/media/liked';
        $params = ['access_token' => $this->access_token, 'count'=>$count];
        if($last != null) {
            $params['min_id'] = $last;
        }

        $statuses = Instagram::request($endpoint, $params);
        $this->savePosts($statuses->data);
    }

    //TODO: get my followers posts

    public function search($last, $tags, $authors) {
        //TODO update last id for pagination
        $connection = new TwitterOAuth('nRMjMfRf7aOwqt1L1rgYTUsGn', 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV', $this->access_token, $this->access_token_secret);

        $formattedTags = [];
        foreach ($tags as $tag) {
            $formattedTags[] = "#$tag";
        }
        $_tags = implode(" OR ", $formattedTags);

        $formattedAuthors = [];
        foreach ($authors as $author) {
            $formattedAuthors[] = "from:$author";
        }
        $_authors = implode(" OR ", $formattedAuthors);
        $query = "$_tags OR $_authors";
        $params = ['q' => $query];
        $statuses = $connection->get("search/tweets", $params);

        $this->savePosts($statuses->statuses);
    }

    private static function request($endpoint, $params=[]) {
        $arrContextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $base = 'https://api.instagram.com/v1';
        $query = http_build_query($params);

        $request = "$base$endpoint?$query";
        exit($request);
        $response = json_decode(file_get_contents($request, false, stream_context_create($arrContextOptions)));
        
        return $response;
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
