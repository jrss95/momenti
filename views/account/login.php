<?php

use yii\bootstrap\ActiveForm;

$this->title = 'Login | Momenti';
$this->params['breadcrumbs'][] = 'Login';
?>
<div class="site-login">
    <h1>Login</h1>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
    ]); ?>

        <div class="form-group">
            <label for="username">
                Username or Email:
            </label>
            <input class="form-control" type="text" name="identifier" placeholder="Enter username or email" id="username" required>
        </div>

        <div class="form-group">
            <label for="password">
                Password:
            </label>
            <input class="form-control" type="password" name="password" placeholder="Enter password" id="password" required>
        </div>

        <div class="form-group">
            <button class="btn btn-primary" type="submit" id="submit-form">Log in</button>
            &nbsp;&nbsp;&nbsp;Not a member? <a href="/momenti/web/register">Join today</a>.
        </div>

    <?php ActiveForm::end(); ?>
</div>
