<?php

namespace app\controllers\helpers;

use yii\web\Controller;
use app\models\Account;

class CoreController extends Controller {

    public $get;
    public $post;

    public function init() {
        parent::init();
        
        $get = \Yii::$app->request->get();
        $post = \Yii::$app->request->post();

        foreach ($get as $key => $value) {
            $get[$key] = $this->clean_input($value);
        }
        foreach ($post as $key => $value) {
            $post[$key] = $this->clean_input($value);
        }

        $this->get = $get;
        $this->post = $post;
    }
    
    public function get($term, $default=null) {
        if (isset($this->get[$term])) {
            return $this->get[$term];
        } else {
            return $default;
        }
    }
    
    public function post($term, $default=null) {
        if (isset($this->post[$term])) {
            return $this->post[$term];
        } else {
            return $default;
        }
    }

    public function clean_input($data) {
        if (is_array($data)) {
            $arr = [];
            foreach ($data as $key => $value) {
                $arr[$key] = $this->clean_input($value);
            }

            return $arr;
        } else {
            $v = trim($data);
            $v = stripslashes($v);
            $v = htmlspecialchars($v);
            //$v = addslashes($v);
            return $v;
        }
    }

    public function urlize($string) {
        $noSpaces = str_replace('  ', ' ', $string);
        $left = str_replace('- ', '-', $noSpaces);
        $right = str_replace(' -', '-', $left);
        $alphanum = preg_replace("/[^A-Za-z0-9 ]/", '', $right);
        $trimmed = trim($alphanum);
        $hyphened = str_replace(' ', '-', $trimmed);
        $lowered = strtolower($hyphened);
        $noDups = str_replace('--', '-', str_replace('---', '-', $lowered));

        return $noDups;
    }
    
    public function auth() {
        $action = $this->action->id;
        
        $guest = \Yii::$app->user->isGuest;
        $loginOrRegister = $action == 'login' || $action == 'register';
        
        if($guest && !$loginOrRegister) {
            \Yii::$app->session->addFlash('danger', 'You must be logged in before you continue.');
            header("Location: /momenti/web/login");
            exit();
        } else if (!$guest && $loginOrRegister) {
            header("Location: /momenti/web");
            exit();
        }
        else if (!$guest && $action != 'connect') {
            $account = Account::findOne(['user_id'=>\Yii::$app->user->identity->id]);
            if($account == null) {
                \Yii::$app->session->addFlash('info', 'Conect a social network before you continue.');
                header("Location: /momenti/web/account/connect");
                exit();
            }
        }
    }

}
