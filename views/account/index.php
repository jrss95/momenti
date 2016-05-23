<?php

use yii\bootstrap\ActiveForm;

$this->title = 'My Account | Momenti';
$this->params['breadcrumbs'][] = 'My Account';
?>
<div class="site-login">
    <h1>My Account</h1>

    <!-- account info -->
    <div class="add-bottom-sm">
        <b class="text-large"><?= $user->username ?></b>
    </div>
    <div class="add-bottom-sm">
        <?= $user->email ?>
        &nbsp;&nbsp;
        <button type="button" onclick="$('#change-email').removeClass('hidden');" class="btn btn-link plain inline">Change Email</button>
        <?php if ($user->status == 0): ?>
        <p class="alert alert-info add-top-sm add-bottom-sm">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fa fa-info"></i>&nbsp;&nbsp;Activate your account in order to receive emails from us. This is the only way to recover your account if you get locked out.
            <a href="/momenti/web/account/resend" target="_blank" onclick="$(this).after('<span class=\'text-success\'>Email sent.</span>'); $(this).remove();">Send email confirmation.</a>
        </p>
        <?php endif; ?>
    </div>
    <!-- change email form -->
    <?php
    ActiveForm::begin(['id'=>'change-email', 'action'=>'/momenti/web/account/account-info', 'options'=>['class'=>'hidden']]);
    ?>
    <div class="well">
        <input type="hidden" name="action" value="change-email">
        <div class="form-group">
            <label for="email">New Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="current">Current Password:</label>
            <input type="password" class="form-control" id="current" name="password" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update email</button>
            <button type="button" onclick="$('#change-email').addClass('hidden');" class="btn btn-link">Cancel</button>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    
    <div class="add-bottom-sm">
        <button type="button" onclick="$('#change-password').removeClass('hidden');" class="btn btn-link plain inline">Change Password</button>
    </div>
    <!-- change password form -->
    <?php
    ActiveForm::begin(['id'=>'change-password', 'action'=>'/momenti/web/account/account-info', 'options'=>['class'=>'hidden']]);
    ?>
    <div class="well">
        <input type="hidden" name="action" value="change-password">
        <div class="form-group">
            <label for="current">Current Password:</label>
            <input type="password" class="form-control" id="current" name="current" required>
        </div>
        <div class="form-group">
            <label for="password">New Password:</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <div class="form-group">
            <label for="confirmation">Confirm Password:</label>
            <input type="password" class="form-control" name="confirmation" id="confirmation" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update password</button>
            <button type="button" onclick="$('#change-password').addClass('hidden');" class="btn btn-link">Cancel</button>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    
    <div class="add-bottom-sm">
        You have a <b><?= $countAccounts ?></b> social accounts linked. <a href="/momenti/web/account/connect">Manage Accounts</a>
    </div>
    
    <div class="add-bottom-sm">
        TODO: Theme
    </div>
    <div class="add-bottom-sm">
        TODO: Notifications
    </div>
    
</div>
