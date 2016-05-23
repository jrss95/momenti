<?php

use yii\bootstrap\ActiveForm;

$this->title = 'Connect Your Social Network | Momenti';
$this->params['breadcrumbs'][] = ['label' => 'My Account', 'url' => '/momenti/web/account'];
$this->params['breadcrumbs'][] = 'Connect Account';
?>
<div class="site-login">
    <h1>Connect Your Social Networks</h1>

    <?php foreach ($accounts as $account): ?>
        <div class="row add-bottom">
            <div class="col-sm-3">
                <i class="fa fa-<?= $account['network'] ?>"></i>
                <?= ucwords($account['network']) ?>
            </div>
            <div class="col-sm-3">
                <?= $account['identifier'] ?>
            </div>
            <div class="col-sm-6">
                Added on: <?= date('M d, Y', strtotime($account['registered'])) ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="row add-bottom">
        <div class="col-sm-6">
            <div class="add-bottom">
                <button class="btn btn-lg btn-primary" type="button" onclick="window.location = '/momenti/web/social/twitter.php?f=auth';">
                    <i class="fa fa-twitter"></i> Connect Twitter
                </button>
            </div>
        </div>
    </div>
</div>
