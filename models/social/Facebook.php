<?php
namespace app\models\social;

require dirname(dirname(dirname(__FILE__))).'/vendor/Facebook/autoload.php';

use app\models\User;
use app\models\Account;
use Facebook\Facebook as FB;

class Facebook {
	
	public $user;
	private $access_token;

	public function __construct(User $__user) {
		$this->user = $__user;
		$account = Account::findOne(['user_id'=>$this->user->id, 'network'=>'facebook']);
		if($account == null) {
			throw new \yii\base\ErrorException('No account found!');
		}

		$credentials = json_decode($account->credentials);
		$this->access_token = $credentials[0];
	}

	public function updateFeed() {

		$fb = new FB([
			'app_id' => '1262161033810813',
			'app_secret' => 'c85cd02483a058e1b2cbfc4959c4408c',
			'default_graph_version' => 'v2.5',
			'default_access_token' => $this->access_token
			]);

		try {
			$response = $fb->get('/me/home');
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$feed = $response->getGraphEdge();
		foreach ($feed as $key => $status) {
			echo "$key. $status->message<br><br>";
		}
		exit();
	}


}