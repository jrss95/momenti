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
            Instagram::auth();
            break;
        case 'token':
            Instagram::token();
            break;
    }
}

class Instagram {

    private static $app_id = 'fec3db428b5d4945a1ef17b9c1544e47';
    private static $app_secret = '9291f4cd2d5943ae934ce392877df6ca';
    private static $redirect_url = 'http://localhost/momenti/web/social/instagram.php?f=token';

    public static function auth() {
        $url = 'https://api.instagram.com/oauth/authorize/?';
        $url .= 'client_id=' . Instagram::$app_id;
        $url .= '&redirect_uri=' . Instagram::$redirect_url;
        $url .= '&response_type=code&scope=public_content';
        header('Location: ' . $url);
        exit();
    }

    public static function token() {
        if (isset($_GET['code'])) {

            $c = $_GET['code'];

            $headerData = array('Accept: application/json');
            $url = 'https://api.instagram.com/oauth/access_token?';
            $fields = array(
                'client_id' => urlencode(Instagram::$app_id),
                'client_secret' => urlencode(Instagram::$app_secret),
                'grant_type' => urlencode('authorization_code'),
                'redirect_uri' => urlencode(Instagram::$redirect_url),
                'code' => urlencode($c)
            );
            $fields_string = '';
            //url-ify the data for the POST
            foreach ($fields as $key => $value) {
                $fields_string .= $key . '=' . $value . '&';
            }
            rtrim($fields_string, '&');

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerData);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);
            $data = json_decode($result);

            $sess['social'] = [];
            $sess['social']['username'] = $data->user->username;
            $sess['social']['credentials'] = [$data->access_token];

            $_SESSION['site'] = $sess;
            header("Location: /momenti/web/account/connect/instagram/" . $data->user->id);
            exit();
        }
    }

}

?>