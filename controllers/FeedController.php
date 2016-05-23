<?php

namespace app\controllers;

include '../vendor/phpInsight/autoload.php';

use app\models\Account;
use app\models\Post;
use app\models\Feed;
use app\models\social\Twitter;
use app\models\social\Instagram;
use PHPInsight\Sentiment;
use app\models\Author;

class FeedController extends helpers\CoreController {

    public function actionIndex($id = null) {
        $this->auth();
        if($id == null) {
            header("Location: /momenti/web");
        }
        $a = is_numeric($id);
        if(!$a) {
            return $this->$id();
        }
        
        $feed = Feed::findOne($id);
        
        if(!$this->hasAccess($feed->user_id)) {
            throw new \yii\web\NotFoundHttpException('Page not found.');
        }
        $user = \Yii::$app->user->identity;
        $_params = json_decode($feed['params']);
        
        $networks = explode(",", $feed['network']);
        $tags = $_params->tags;
        $authors = $_params->authors;
        
        $lastUpdate = json_decode($feed->last_update);
        $diff = time()-$lastUpdate->time;
        
        if($diff > 300) {
            if(in_array('twitter', $networks)) {
                $twitter = new Twitter($user);
                $lastUpdate->twitter = $twitter->search($feed->id, $lastUpdate->twitter, $tags, $authors);
            }
            $lastUpdate->time = time();
            $feed->last_update = json_encode($lastUpdate);
            $feed->save();
        }
        
        $page = $this->get('page', 0);
        $order = $this->get('order', 'created_source');
        $direc = $this->get('direc', 'DESC');
        
        $filters = [];
        $filters['remove_tags'] = $this->get('remove_tags', '');
        $filters['remove_authors'] = $this->get('remove_authors', '');
        $filters['media_type'] = $this->get('media_type', '');
        
        
        $posts = $feed->findPosts($page, $order, $direc, $filters);
        
        if (isset($_GET['lazyload'])) {
            if (count($posts) == 0) {
                exit('no-more');
            }
            $this->layout = 'blank';
            return $this->render('content/posts', ['posts'=>$posts, 'feed'=>$feed]);
        }
        $params = [
            'feed'=>$feed,
            'posts'=>$posts,
            'count' => $feed->postCount($filters),
            'page' => $page,
            'order' => $order,
            'direc' => $direc
        ];
        return $this->render('index', $params);
    }
    
    public function actionByAuthor($id, $author) {
        $this->auth();
        if($id == null) {
            header("Location: /momenti/web");
            exit();
        }
        $feed = Feed::findOne($id);
        if(!$this->hasAccess($feed->user_id)) {
            throw new \yii\web\NotFoundHttpException('Page not found.');
        }
        
        $page = $this->get('page', 0);
        $order = $this->get('order', 'created_source');
        $direc = $this->get('direc', 'DESC');
        
        $filters = [];
        $filters['author'] = $author;
        $filters['remove_tags'] = $this->get('remove_tags', '');
        $filters['media_type'] = $this->get('media_type', '');
        
        $posts = $feed->findPosts($page, $order, $direc, $filters);
        
        if (isset($_GET['lazyload'])) {
            if (count($posts) == 0) {
                exit('no-more');
            }
            $this->layout = 'blank';
            return $this->render('content/posts', ['posts'=>$posts]);
        }
        $params = [
            'feed'=>$feed,
            'posts'=>$posts,
            'count' => $feed->postCount($filters),
            'page' => $page,
            'order' => $order,
            'direc' => $direc
        ];
        return $this->render('index', $params);
    }

    public function actionAdd() {
        $this->auth();
        $params = [];

        $post = $this->post;
        if (count($post) > 0) {
            $feedInfo = [
                'name' => $post['name'],
                'user_id' => \Yii::$app->user->identity->id
            ];
            //networks
            $networks = [];
            if(isset($post['twitter'])) {
                $networks[] = 'twitter';
            }
            if(isset($post['instagram'])) {
                $networks[] =  'instagram';
            }
            $feedInfo['network'] = implode(',', $networks);
            //params
            $_params = [
                'tags'=>explode(" ", $post['keywords']),
                'authors'=>explode(" ", $post['authors'])
            ];
            $feedInfo['params'] = json_encode($_params);
            $feed = new Feed();
            $feed->setAttributes($feedInfo, false);
            $feed->save();
            $url = $feed->getUrl();
            header("Location: $url");
            exit();
        }

        $params['user'] = \Yii::$app->user->identity;
        return $this->render("add", $params);
    }

    public function actionDelete($id) {
        $feed = Feed::findOne($id);
        if(!$this->hasAccess($feed->user_id)) {
            throw new \yii\web\NotFoundHttpException('Page not found.');
        }
        
        $feed->delete();
        \Yii::$app->session->addFlash('success', 'Feed has been deleted successfully.');
        header("Location: /momenti/web");
        exit();
    }
    
    //ajax function
    public function actionAuthorAnalysis() {
        $post = $this->post;
        if (count($post) > 0) {
            $author = Author::findOne(['author_id'=>$post['author_id'], 'source'=>$post['source']]);
            $return = $author->getFeedMetrics($post['feed_id'], $this->post('start', null), $this->post('end', null));
            echo json_encode($return);
            exit();
        }
        exit('error');
    }

    private function getAccount($source) {
        $where = [
            'user_id' => \Yii::$app->user->identity->id,
            'network' => $source
        ];
        $account = Account::find()->where($where)->orderBy('registered DESC')->one();

        return $account;
    }

    private function mine() {
        $feed = new Feed();
        $feed->name = 'My Stuff';
        $feed->id = -1;
        $twitterAccount = $this->getAccount('twitter');
        $instagramAccount = $this->getAccount('instagram');
        
        $twID = -1;
        $igID = -1;
        if($twitterAccount != null) {
            $twitter = new Twitter(\Yii::$app->user->identity);
            $twID = $twitterAccount->social_id;
            $twitter->updateFeed();
        }
        if($instagramAccount != null) {
            $instagram = new Instagram($instagramAccount);
            $igID = $instagramAccount->social_id;
            $instagram->updateFeed();
        }
        
        $where = "(source='twitter' AND author_id='$twID') or (source='instagram' AND author_id='$igID')";
        $posts = Post::find()->where($where)->orderBy('created_source DESC')->limit(20)->all();
        
        return $this->render('index', ['feed'=>$feed, 'posts'=>$posts]);
    }

    private function mentions() {
        $feed = new Feed();
        $feed->name = 'Mentions of Me';
        $feed->id = -1;
        $posts = $this->feedByInteraction('mentioned');
        return $this->render('index', ['feed'=>$feed, 'posts'=>$posts]);
    }

    private function likes() {
        $feed = new Feed();
        $feed->name = 'Things I Liked';
        $feed->id = -1;
        $posts = $this->feedByInteraction('liked');
        return $this->render('index', ['feed'=>$feed, 'posts'=>$posts]);
    }

    private function following() {
       //TODO posts by people I follow
    }
    
    private function feedByInteraction($action) {
        $twitterAccount = $this->getAccount('twitter');
        $instagramAccount = $this->getAccount('instagram');
        
        $in = [];
        if($twitterAccount != null) {
            $twitter = new Twitter(\Yii::$app->user->identity);
            $in[] = "'$twitterAccount->social_id'";
            $twitter->updateMentions();
        }
        if($instagramAccount != null) {
            $instagram = new Instagram($instagramAccount);
            $in[] = "'$twitterAccount->social_id'";
            $instagram->updateMentions();
        }
        
        $ids = implode(',', $in);
        $where = "interactions.action='$action' AND interactions.user_id IN ($ids)";
        $posts = Post::find()->join('INNER JOIN', 'interactions', 'posts.author_id=interactions.user_id')->where($where)->orderBy('created_source DESC')->limit(20)->all();
        
        return $posts;
    }
    
    private function hasAccess($id) {
        $guest = \Yii::$app->user->isGuest;
        return !$guest && $id == \Yii::$app->user->identity->id;
    }
}