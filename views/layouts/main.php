<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
$guest = \Yii::$app->user->isGuest;

if (!$guest) {
    $user = \Yii::$app->user->identity;
    $feeds = $user->getFeeds();
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>

        <div class="wrap">
            <nav id="header" class="navbar-inverse navbar-fixed-top navbar" role="navigation">
                <div class="container">
                    <div class="navbar-header text-center full-width">
                        <?php if (!$guest): ?>
                            <button type="button" class="btn btn-link pull-left" onclick="$('#feeds').toggleClass('hidden');">
                                <i class="fa fa-2x fa-bars"></i>
                            </button>
                        <?php endif; ?>
                        <a class="brand" href="/momenti/web/">
                            Momenti <span class="text-small">alpha</span>
                        </a>
                        <?php if (!$guest): ?>
                            <a class="pull-right btn btn-link" href="/momenti/web/account">
                                <i class="fa fa-2x fa-user"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!$guest): ?>
                        <div id="feeds" class="hidden clear text-large">
                            <ul class="list-group no-border add-bottom-sm">
                                <li class="list-group-item no-border">
                                    <a href="/momenti/web/add"><i class="fa fa-plus"></i> New Feed</a>
                                </li>
                                <?php foreach ($feeds as $feed): ?>
                                    <li class="list-group-item no-border">
                                        <?php $feed->printLink(); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="container">
                <?=
                Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ])
                ?>

                <div>
                    <?php foreach (Yii::$app->session->getAllFlashes() as $key => $messages): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="alert alert-<?= $key ?>">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <p><?php print_r($message) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <?= $content ?>
            </div>

            <footer class="footer add-top">
                <div class="container">
                    <p class="pull-left">&copy; Momenti <?= date('Y') ?></p>
                </div>
            </footer>

            <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
