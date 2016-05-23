<?php foreach ($posts as $post): ?>
    <?= $this->render('post', ['post' => $post, 'feed'=>$feed]); ?>
<?php endforeach; ?>
<?php if (count($posts) == 0): ?>
    Nothing to see here.
<?php endif; ?>