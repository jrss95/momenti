<?php

namespace Social;

require dirname(dirname(dirname(__FILE__))).'/vendor/Twitter/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;

session_start();
if (!isset($_SESSION['site'])) {
    $_SESSION['site'] = [];
}
$sess = $_SESSION['site'];

if (isset($_GET['f'])) {
    $func = $_GET['f'];

    switch ($func) {
        case 'auth':
            Twitter::auth();
            break;
        case 'token':
            Twitter::token();
            break;
    }
}

class Twitter {

    private static $consumer_key = 'nRMjMfRf7aOwqt1L1rgYTUsGn';
    private static $consumer_secret = 'NjdtilruR08ssAwed3qXqrYQqkN16qfMvCcgbPn9oYgeYHNiLV';
    private static $redirect_url = 'http://localhost/momenti/web/social/twitter.php?f=token';

    public static function auth() {
        global $sess;
        if (isset($_GET['action'])) {
            $sess['action'] = 'login';
        }
        $connection = new TwitterOAuth(Twitter::$consumer_key, Twitter::$consumer_secret);
        $request = $connection->oauth('oauth/request_token', ['oauth_callback' => Twitter::$redirect_url . $red]);

        $sess['oauth_token'] = $request['oauth_token'];
        $sess['oauth_token_secret'] = $request['oauth_token_secret'];
        $_SESSION['site'] = $sess;

        header("Location: https://api.twitter.com/oauth/authenticate?oauth_token=" . $request['oauth_token']);
        exit();
    }

    public static function token() {
        global $sess;

        $connection = new TwitterOAuth(Twitter::$consumer_key, Twitter::$consumer_secret, $sess['oauth_token'], $sess['oauth_token_secret']);
        $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));

        $userConnection = new TwitterOAuth(Twitter::$consumer_key, Twitter::$consumer_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $me = $userConnection->get("account/verify_credentials");

        $sess['social'] = [];
        $sess['social']['username'] = $me->screen_name;
        $sess['social']['credentials'] = [$access_token['oauth_token'], $access_token['oauth_token_secret']];

        $_SESSION['site'] = $sess;
        header("Location: /momenti/web/account/connect/twitter/" . $me->id);
        exit();
    }

}

?>