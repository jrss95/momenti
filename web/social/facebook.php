<?php

namespace Social;

session_start();
if (!isset($_SESSION['site'])) {
    $_SESSION['site'] = [];
}
$sess = $_SESSION['site'];

if (isset($_GET['f'])) {
    $func = $_GET['f'];

    switch ($func) {
        case 'auth':
        Facebook::auth();
        break;
        case 'token':
        Facebook::token();
        break;
    }
}

class Facebook {

    private static $app_id = '1262161033810813';
    private static $app_secret = 'c85cd02483a058e1b2cbfc4959c4408c';
    private static $redirect_url = 'http://localhost/momenti/web/social/facebook.php?f=token';

    public static function auth() {
        global $sess;

        $url = 'https://www.facebook.com/dialog/oauth?';
        $url .= 'client_id=' . Facebook::$app_id;
        $url .= '&redirect_uri=' . Facebook::$redirect_url;
        if (isset($_GET['action'])) {
            $url .= '&action=login';
            $sess['action'] = 'login';
        }

        $_SESSION['site'] = $sess;
        header('Location: ' . $url);
        exit();
    }

    public static function token() {
        global $sess;

        if (isset($_GET['code'])) {

            $arrContextOptions= [
                "ssl"=> [
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                    ],
                ];

            $code = $_GET['code'];

            $url = 'https://graph.facebook.com/oauth/access_token?';
            $url .= 'client_id=' . Facebook::$app_id;
            $url .= '&redirect_uri=' . Facebook::$redirect_url . $red;
            if (isset($_GET['action'])) {
                $url .= '&action=login';
            }
            $url .= '&client_secret=' . Facebook::$app_secret;
            $url .= '&code=' . $code;

            $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
            
            $arr = explode('&', $response);
            $token = explode('=', $arr[0]);
            $expires = explode('=', $arr[1]);

            $me = json_decode(file_get_contents('https://graph.facebook.com/me?access_token=' . $token[1], false, stream_context_create($arrContextOptions)));

            $sess['social'] = [];
            $sess['social']['username'] = $me->name;
            $sess['social']['credentials'] = [$token[1]];

            $_SESSION['site'] = $sess;
            header("Location: /momenti/web/account/connect/facebook/" . $me->id);
            exit();
        }
    }

}

?>