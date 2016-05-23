<?php

use yii\bootstrap\ActiveForm;

$this->title = 'Create Feed | Momenti';
$this->params['breadcrumbs'][] = 'Create Feed';
?>
<h1>Create Feed</h1>
<?php
ActiveForm::begin(['id' => 'create-feed']);
?>
<div class="form-group">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" class="form-control" required>
</div>

<div class="form-group">
    <label for="">Network:</label>&nbsp;&nbsp;
    <input type="checkbox" name="twitter"> Twitter
    <input type="checkbox" name="instagram"> Instagram
</div>

<!-- TODO: tag input field (to add multiple keywords) -->
<div class="form-group">
    <label for="keyword">Tags</label>
    <input type="text" name="keywords" id="keywords" class="form-control">
</div>
<!-- TODO: tag input field (to add multiple authors) -->
<div class="form-group">
    <label for="authors">Authors:</label>
    <input type="text" name="authors" id="authors" placeholder="Enter Twitter/Instagram usernames" class="form-control">
</div>
<div class="form-group">
    <button type="submit" class="btn btn-primary">Submit</button>
</div>

<?php ActiveForm::end(); ?>