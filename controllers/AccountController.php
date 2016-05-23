<?php

namespace app\controllers;

use app\models\User;
use app\models\Account;

class AccountController extends helpers\CoreController {
    
    public function actionIndex() {
        $this->auth();
        $params = [];

        $user = \Yii::$app->user->identity;
        $params['user'] = \Yii::$app->user->identity;
        $params['countAccounts'] = count($user->getAccounts());
        return $this->render('index', $params);
    }

    public function actionAccountInfo() {
        $guest = \Yii::$app->user->identity;
        $post = $this->post;

        if($guest || count($post) == 0) {
            exit('error');
        }

        $action = $post['action'];
        $me = \Yii::$app->user->identity;
        if($action == 'change-email') {
            if(!$post['email'] || !$post['password']) {
                \Yii::$app->session->addFlash('danger', 'You did not fill in all required fields.');
                header("Location: /momenti/web/account?change-password");
                exit();
            }

            $user = User::findOne($post['danger']);
            if ($user != null) {
                \Yii::$app->session->addFlash('error', 'Email already in used.');
                header("Location: /momenti/web/account?change-password");
                exit();
            }
            if($post['password'] != $me->password) {
                \Yii::$app->session->addFlash('danger', 'Wrong password. You must provide your current password for the action to take effect.');
                header("Location: /momenti/web/account?change-password");
                exit();
            }

            $me->email = $post['email'];
            $me->save();
            \Yii::$app->session->addFlash('success', 'Account info changed successfully.');
            header("Location: /momenti/web/account");
            exit();
        } else if ($action == 'change-password') {
            if(!$post['current'] || !$post['password'] || !$post['confirmation']) {
                \Yii::$app->session->addFlash('danger', 'You did not fill in all required fields.');
                header("Location: /momenti/web/account?change-password");
                exit();
            }

            if ($post['password'] != $post['confirmation']) {
                \Yii::$app->session->addFlash('danger', 'The password did not match it\'s confirmation.');
                header("Location: /momenti/web/account?change-password");
                exit();
            }
            if($post['password'] != $me->password) {
                \Yii::$app->session->addFlash('danger', 'Wrong password. You must provide your current password for the action to take effect.');
                header("Location: /momenti/web/account?change-password");
                exit();
            }

            $me->password = $post['password'];
            $me->save();
            \Yii::$app->session->addFlash('success', 'Account info changed successfully.');
            header("Location: /momenti/web/account");
            exit();
        }
    }

    public function actionRegister() {
        $this->auth();
        $params = [];

        $guest = \Yii::$app->user->isGuest;
        if(!$guest) {
            header("Location: /momenti/web/account");
            exit();
        }

        $post = $this->post;
        
        if(count($post) > 0) {

            if(!$post['username'] || !$post['email'] || !$post['password'] || !$post['confirmation']) {
                \Yii::$app->session->addFLash('danger', 'You did not fill in all the required fields.');
                header("Location: /momenti/web/account/register");
                exit();
            }

            //check unique email
            $user = User::findOne(['email'=>$post['email']]);
            if($user != null) {
                \Yii::$app->session->addFLash('danger', 'Email already in use.');
                header("Location: /momenti/web/account/register");
                exit();
            }

            //check unique username
            $user = User::findOne(['username'=>$post['username']]);
            if($user != null) {
                \Yii::$app->session->addFLash('danger', 'Username already in use.');
                header("Location: /momenti/web/account/register");
                exit();
            }

            //check password matches confirmation
            $user = User::findOne(['email'=>$post['email']]);
            if($post['confirmation'] != $post['password']) {
                \Yii::$app->session->addFLash('danger', 'Password doesn\'t match it\'s confirmation');
                header("Location: /momenti/web/account/register");
                exit();
            }

            //TODO: password encyption
            $newUser = new User();
            $newUser->setAttributes($post, false);
            $newUser->save();

            //TODO: send confirmation email

            //login user
            \Yii::$app->user->login($newUser, 0);

            header("Location: /momenti/web/account/connect?welcome");
            exit();
        }

        return $this->render('register', $params);
    }

    public function actionLogin() {
        $this->auth();
        $params = [];

        $guest = \Yii::$app->user->isGuest;
        if(!$guest) {
            header("Location: /momenti/web/account");
            exit();
        }

        $post = $this->post;
        
        if(count($post) > 0) {

            if(!$post['identifier'] || !$post['password']) {
                \Yii::$app->session->addFLash('danger', 'You did not fill in all the required fields.');
                header("Location: /momenti/web/account/login");
                exit();
            }
            $password = $post['password'];

            //check unique email
            $user = User::findOne(['email'=>$post['identifier'], 'password'=>$password]);
            if($user == null) {
                $user = User::findOne(['username'=>$post['identifier'], 'password'=>$password]);
                if($user == null) {
                    \Yii::$app->session->addFLash('danger', 'Either your username or password is incorrect.');
                    header("Location: /momenti/web/account/login");
                    exit();
                }
            }

            //login user
            \Yii::$app->user->login($user, 0);

            header("Location: /momenti/web");
            exit();
        }

        return $this->render('login', $params);
    }

    public function actionLogout() {
        if (!\Yii::$app->user->isGuest) {
            \Yii::$app->user->logout();
        }
        header("Location: /momenti/web");
        exit();
    }

    public function actionActivate($id) {
        $params = [];

    }

    public function actionDeactivate() {
        $params = [];

    }

    public function actionConnect($network=null, $id=null) {
        $this->auth();
        $params = [];

        $guest = \Yii::$app->user->isGuest;
        if($guest) {
            \Yii::$app->session->addFLash('danger', 'You need to register before accessing your account info.');
            header("Location: /momenti/web/account/register");
            exit();
        }

        if($network != null) {
            if($id == null) {
                exit('Oops! There was an error. Please close this window.');
            }

            $sess = $_SESSION['site'];
            
            //find user
            $account = Account::findOne(['social_id'=>$id]);
            if($account != null) {
                \Yii::$app->session->addFLash('danger', 'This account is already in used.');
                header("Location: /momenti/web/account/connect");
                exit();
            } else {
                if(isset($sess['social']) && isset($sess['social']['credentials'])) {
                    $this->socialLogin($network, $id, $sess);
                    
                    \Yii::$app->session->addFLash('success', 'Account added successfully!');
                    header("Location: /momenti/web/account/connect");
                    exit();
                }
            }
        }

        $user = \Yii::$app->user->identity;
        $accounts = $user->getAccounts();

        $params['user'] = $user;
        $params['accounts'] = $accounts;

        return $this->render('connect', $params);
    }

    private function socialLogin($network, $id, $sess) {

        $account_info = [
            'user_id' => \Yii::$app->user->identity->id,
            'network' => $network,
            'social_id' => $id,
            'identifier' => strtolower(str_replace(' ','.', $sess['social']['username'])),
            'credentials' => json_encode($sess['social']['credentials'])
        ];
        $account = new Account($account_info);
        $account->save();
        
        switch($network) {
            case 'facebook':
                $a = new \app\models\social\Facebook(\Yii::$app->user->identity);
                break;
            case 'twitter':
                $a = new \app\models\social\Twitter(\Yii::$app->user->identity);
                break;
            case 'instagram':
                $a = new \app\models\social\Instagram(\Yii::$app->user->identity);
                break;
        }
        
        $a->welcome();

        $sess = null;
        unset($sess);
    }

}
