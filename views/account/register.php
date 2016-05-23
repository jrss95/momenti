<?php

use yii\bootstrap\ActiveForm;

$this->title = 'Register | Momenti';
$this->params['breadcrumbs'][] = 'Register';
?>
<div class="site-login">
    <h1>Register</h1>

    <p>Please fill out the following fields to register:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'register-form',
    ]); ?>

        <div class="form-group">
            <label for="username">
                Username:
            </label>
            <input class="form-control" type="text" name="username" placeholder="Enter username" id="username" required>
        </div>

        <div class="form-group">
            <label for="email">
                Email:
            </label>
            <input class="form-control" type="email" name="email" placeholder="Enter email" id="email" required>
        </div>
        
        <p class="alert alert-info">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fa fa-info"></i>&nbsp;&nbsp;Activate your account in order to receive emails from us. This is the only way to recover your account if you get locked out.
        </p>

        <div class="form-group">
            <label for="password">
                Password:
            </label>
            <input class="form-control" type="password" name="password" placeholder="Enter password" id="password" required>
        </div>

        <div class="form-group">
            <label for="confirmation">
                Confirm Password:
            </label>
            <input class="form-control" type="password" name="confirmation" placeholder="Re-enter password" id="confirmation" required>
        </div>

        <div class="form-group">
            <button class="btn btn-primary" type="submit" id="submit-form">Join Now!</button>
            &nbsp;&nbsp;&nbsp;Already a member? <a href="/momenti/web/login">Login</a>.
        </div>

    <?php ActiveForm::end(); ?>
</div>
