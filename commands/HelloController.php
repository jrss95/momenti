<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\models\Feed;
use app\models\User;
use app\models\social\Twitter;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller {

    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world') {
        echo $message . "\n";
    }

    public function actionMiner() {
        //TODO: catch exceptions
        while (true) {
            echo 'Getting feeds' . PHP_EOL;
            $feeds = Feed::find()->all();
            $count = count($feeds);
            echo "there are $count feeds" . PHP_EOL;
            $start = time();
            foreach ($feeds as $feed) {
                echo 'processing ' . $feed->name . PHP_EOL;
                $user = User::findOne($feed->user_id);
                $_params = json_decode($feed['params']);

                $networks = explode(",", $feed['network']);
                $tags = $_params->tags;
                $authors = $_params->authors;

                $lastUpdate = json_decode($feed->last_update);
                if (in_array('twitter', $networks)) {
                    $twitter = new Twitter($user);
                    $lastUpdate->twitter = $twitter->search($feed->id, $lastUpdate->twitter, $tags, $authors);
                }
                $feed->last_update = json_encode($lastUpdate);
                $feed->save();
                echo "updated $feed->name by $user->username";
                echo PHP_EOL;
            }
            $end = time();
            $elapsed = $end - $start;
            echo "took $elapsed seconds to process $count feeds." . PHP_EOL;
            $wait = 300 - $elapsed;
            echo "waiting $wait seconds" . PHP_EOL;
            sleep($wait);
        }
    }

    public function actionUpdate() {
        //TODO: catch exceptions
        while (true) {
            echo 'Getting feeds' . PHP_EOL;
            $feeds = Feed::find()->all();
            $count = count($feeds);
            echo "there are $count feeds" . PHP_EOL;
            $start = time();
            foreach ($feeds as $feed) {
                echo 'processing ' . $feed->name . PHP_EOL;
                $sql = "SELECT * FROM updates WHERE feed_id=:id ORDER BY updated ASC LIMIT 2";
                $sql_params = [':id' => $feed->id];
                $updates = \Yii::$app->db->createCommand($sql, $sql_params)->queryAll();
                foreach ($updates as $update) {
                    $user = User::findOne($feed->user_id);
                    $networks = explode(",", $feed['network']);
                    if (in_array('twitter', $networks)) {
                        $twitter = new Twitter($user);
                        $twitter->update($update['action']);
                    }
                    $update['updated'] = date('Y-m-d H:i:s');
                    \Yii::$app->db->createCommand()->update('updates', $update, ['id' => $update['id']])->execute();
                }
            }
            $end = time();
            $elapsed = $end - $start;
            echo "took $elapsed seconds to process $count feeds." . PHP_EOL;
            $wait = 180 - $elapsed;
            echo "waiting $wait seconds" . PHP_EOL;
            sleep($wait);
        }
    }

}
