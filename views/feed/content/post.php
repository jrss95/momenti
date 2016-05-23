<?php
$source = [
    'twitter' => 'https://twitter.com/',
    'instagram' => 'https://instagram.com/',
];
?>
<div class="post <?= $post->source ?>">
    <div class="post-info header">
        <i class="fa fa-<?= $post->source ?>"></i>
        <a href="<?= $source[$post->source] ?><?= $post->author ?>"><?= $post->author ?></a>
        <span class="pull-right text-right"><?= date('M j, Y \a\t g:ia', strtotime($post->created_source)) ?></span>
    </div>
    <?php
    if ($post->hasMedia()) {
        $media = $post->getMedia();
        ?>
        <div class="post-info media">
            <?php
            $countImages = count($media->imgs);
            if (count($countImages)) {
                ?>
                <div class="row no-margin add-bottom-xs">
                    <?php foreach ($media->imgs as $image): ?>
                        <div class="no-padding col col-sm-<?= (12 / $countImages) ?>">
                            <div class="valign wrap-outer">
                                <div  class="wrap-inner">
                                    <img src="<?= $image->url ?>">
                                </div>
                            </div>
                            <div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
            }
            ?>
            <?php foreach ($media->videos as $video): ?>
                <div class="add-bottom-xs">
                    <a href="<?= $video->url ?>" target="_blank">Watch Video</a>
                </div>
            <?php endforeach; ?>
            <?php foreach ($media->youtube as $video): ?>
                <div class="add-bottom-xs">
                    <a href="<?= $video ?>" target="_blank"><i class="fa fa-youtube"></i> Watch on Youtube</a>
                </div>
            <?php endforeach; ?>
            <?php foreach ($media->urls as $url): ?>
                <div class="add-bottom-xs">
                    <a href="<?= $url ?>" target="_blank"><?= $url ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php }
    ?>
    <?php if ($post->text != '' && $post->text != null): ?>
        <div class="post-info text">
            <?= $post->text; ?>
        </div>
    <?php endif; ?>
    <div class="post-info footer no-border no-bottom">
        <div class="add-bottom-xs">
            <?php
            $tags = json_decode($post->tags);
            foreach ($tags as $tag):
                ?>
                <span class="label label-danger"><?= $tag ?></span>
            <?php endforeach; ?>
        </div>
        <div class="add-bottom-xs">
            <?php if ($post->source == 'instagram'): ?>
                <i class="fa fa-comments"></i> <?= $post->comments; ?>
            <?php endif; ?>
            <i class="fa fa-thumbs-up"></i> <?= $post->likes; ?>
            <?php if ($post->source == 'twitter'): ?>
                <i class="fa fa-share"></i> <?= $post->shares; ?>
            <?php endif; ?>
            <div class="pull-right dropdown">
                <button type="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle"><i class="fa fa-ellipsis-h"></i></button>
                <ul class="dropdown-menu">
                    <li><a href="/momenti/web/feed/<?= $feed->id ?>/<?= $post->author ?>">More from <?= $post->author ?></a></li>
                    <li><a href="javascript:void(0);" onclick="authorAnalysis('<?=$feed->id ?>', '<?= $post->author_id ?>', '<?= $post->source ?>')">Author Analysis</a></li>
                    <li><a href="<?= $source[$post->source] ?>/<?= $post->author ?>/status/<?= $post->post_id ?>" target="_blank">View in <?= ucwords($post->source) ?></a></li>
                </ul>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
function authorAnalysis(feedId, authorId, source) {
    var url = '/momenti/web/author-analysis';
    var data = {
        feed_id: feedId,
        author_id: authorId,
        source: source
    };
    
    $.ajax({
        url: url,
        data: data,
        method: 'post',
        success: function (data) {
            var json = JSON.parse(data);
            $("#authorAnalysis span.authorName").text(json.name);
            $("#authorCount").text(json.count);
            $("#authorLikes").text(json.likes);
            $("#authorPostsRatio").text(json.postsRatio);
            $("#authorLikesRatio").text(json.likesRatio);
            var timeElapsed = json.timeElapsed; //todo: better format this number (in D days and H hours)
            $("#authorAnalysis span.authorTime").text(timeElapsed);
            var url_ = '/momenti/web/feed/'+feedId+'/'+json.name;
            $("#authorMoreBy").attr('href', url_)
            $("#authorAnalysis").modal('show');
        },
        error: function (a, b, c) {
            console.log(a.responseText);
        }
    });
}
</script>