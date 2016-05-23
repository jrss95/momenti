<?php
$c = \Yii::$app->controller;
$this->title = ucwords($feed->name) . ' | Momenti';

$mediaMetrics = $feed->getMediaMetrics();
$tagMetrics = $feed->getTagMetrics();
$authorMetrics = $feed->getAuthorMetrics();
?>
<h1><?= ucwords($feed->name); ?></h1>
<?php if ($feed->id > -1): ?>
    <a href="/momenti/web/feed/<?= $feed->id ?>/delete">Delete Feed</a> TODO: Double verification
<?php endif; ?>
<?php if ($feed->id != -1): ?>
    <div id="feed-config" class="row add-bottom">
        <?php
        $params = json_decode($feed->params);
        $fTags = $params->tags;
        $fAuthors = $params->authors;
        ?>
        <div class="col-sm-12 add-bottom">
            <div class="add-bottom-xs">
                <b>Tags:</b>
                <?php foreach ($fTags as $fTag): ?>
                    <span class="label label-danger"><i class="fa fa-tag"></i> <?= $fTag ?></span>
                <?php endforeach; ?>
            </div>
            <div>
                <b>Authors:</b>
                <?php foreach ($fAuthors as $fAuthor): ?>
                    <span class="label label-primary"><i class="fa fa-user"></i> <?= $fAuthor ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-sm-6 add-bottom">
            <div>
                <select id="sort" class="form-control">
                    <option value="created_source" data-direc="DESC"<?php if (isset($_GET['direc']) && $_GET['direc'] == 'DESC'): ?> selected<?php endif; ?>>Most Recent</option>
                    <option value="created_source" data-direc="ASC"<?php if (isset($_GET['direc']) && $_GET['direc'] == 'ASC'): ?> selected<?php endif; ?>>Oldest</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div id="filter" class="btn-group input-group" role="group">
                <span class="input-group-addon">Filter by media type:</span>
                <?php foreach ($mediaMetrics as $metric): ?>
                    <?php
                    $media_type = \Yii::$app->controller->get('media_type', '');
                    $type_ = $metric['media_type'];
                    $checked = in_array($type_, explode(',', $media_type));
                    ?>
                    <button type="button" class="btn btn-default btn-media<?php if ($checked): ?> active<?php endif; ?>" data-media="<?= $metric['media_type'] ?>"><?= ucwords($metric['media_type']) ?> (<?= $metric['count'] ?>)</button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="btn-group input-group" role="group">
                <a href="#tags" data-toggle="modal" class='btn btn-default'>Filter  by tags</a>
                <a href="#authors" data-toggle="modal" class='btn btn-default'>Filter  by authors</a>
            </div>
        </div>
    </div>
<?php endif; ?>
<div id="feed">
    <h2>There <?= $count > 1 ? 'are' : 'is' ?> <?= $count ?> posts total</h2>
    <?= $this->render('content/posts', ['posts' => $posts, 'feed'=>$feed]); ?>
</div>

<div id="tags" class="modal filter-modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body justified">
                <?php
                arsort($tagMetrics['individuals']);
                $individuals = array_slice($tagMetrics['individuals'], 0, 50);
                $remove_tags = \Yii::$app->controller->get('remove_tags', '');
                foreach ($individuals as $tag => $count):
                    $removed = in_array($tag, explode(',', $remove_tags));
                    ?>
                    <span class="label label-primary"><input<?php if (!$removed): ?> checked<?php endif; ?> type="checkbox" value="<?= $tag ?>"> <?= $tag ?> (<?= $count ?>)</span>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <span class="pull-left text-muted">Showing top 50 tags</span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-update-tags">Save changes</button>
            </div>
        </div>
    </div>
</div>
<div id="authors" class="modal filter-modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body justified">
                <?php
                $authors = array_slice($authorMetrics['authors'], 0, 50);
                $remove_authors = \Yii::$app->controller->get('remove_authors', '');
                foreach ($authors as $author):
                    $removed = in_array($author->id, explode(',', $remove_authors));
                    ?>
                    <span class="label label-primary"><input<?php if (!$removed): ?> checked<?php endif; ?> type="checkbox" value="<?= $author->name ?>"> <?= $author->name ?> (<?= $author->postCount ?>)</span>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <span class="pull-left text-muted">Showing top 50 authors</span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-update-authors">Save changes</button>
            </div>
        </div>
    </div>
</div>
<div id="authorAnalysis" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h3><span class="authorName"></span> Analysis</h3></div>
            <div class="modal-body justified">
                <div class="row add-bottom-sm">
                    <div class="col-sm-6">Posts: <span id="authorCount">'count'</span> in <span class="authorTime">'time'</span> hour(s)</div>
                    <div class="col-sm-6">Post Ratio: <span id="authorPostsRatio">'posts ratio'</span> per hour</div>
                </div>
                <div class="row add-bottom-sm">
                    <div class="col-sm-6">Likes: <span id="authorLikes">'likes'</span> in <span class="authorTime">'time'</span> hour(s)</div>
                    <div class="col-sm-6">Likes Ratio: <span id="authorLikesRatio">'likes ratio'</span> per hour</div>
                </div>
                <div class="row add-bottom-sm">
                    <div class="col-sm-4 text-muted">Time: <span class="authorTime">'time'</span> hour(s)</div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="pull-left text-muted"><a id="authorMoreBy" href="#">More by <span class="authorName">'author'</span></a></span>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
    
<form id="sort-form" class="hide">
    <input type="hidden" name="order" value="<?= $c->get('order', 'created_source') ?>">
    <input type="hidden" name="direc" value="<?= $c->get('direc', 'DESC') ?>">
    <input type="hidden" name="remove_tags" value="<?= $c->get('remove_tags') ?>">
    <input type="hidden" name="remove_authors" value="<?= $c->get('remove_authors') ?>">
    <input type="hidden" name="media_type" value="<?= $c->get('media_type') ?>">
    <input type="hidden" name="page" value="0">
</form>
<script type="text/javascript">
    var url = window.location;
    var page = '<?= $page ?>';
    var sourceUrls = {
        'twitter': 'https://twitter.com/',
        'instagram': 'https://instagram.com/'
    };

    var loading = function (id) {
        var ancher = $("#" + id);
        ancher.append('<div id="loading" class="text-center add-top add-bottom"><i class="fa fa-spinner fa-spin"></i></div>');
    };

    var lazyload = function () {
        var windowY = $(window).scrollTop();
        var contentTop = $("#feed").offset().top;
        var contentEnd = contentTop + $("#feed").height();
        if (windowY > contentEnd - 1000) {
            $(window).unbind('scroll', lazyload);
            var l = new String(window.location.href);
            var base = l.split("?")[0];
            try {
                var params = l.split("?")[1];
                var pairs = params.split("&");
            } catch (err) {
                var pairs = ['page=' + page];
            }

            var key, value;
            var __newparams = 'lazyload=true';
            for (var i = 0; i < pairs.length; i++) {
                key = pairs[i].split("=")[0];
                value = pairs[i].split("=")[0];
                if (key == 'page') {
                    __newparams += "&page=" + (page + 1);
                } else {
                    __newparams += "&" + key + "=" + value;
                }
            }

            loading();
            $.ajax({
                url: base,
                data: __newparams,
                success: function (data) {
                    $("#loading").remove();
                    if (data != 'no-more') {
                        $(window).unbind('scroll').bind('scroll', lazyload);
                    }
                    $("#feed").append(data);
                    page++;
                }
            });
        }
    };

    $(document).ready(function () {
        $(window).unbind('scroll').bind('scroll', lazyload);

        $("#sort").change(function () {
            var order = $(this).val();
            var direc = $(this).find('option:selected').attr('data-direc');

            $("#sort-form input[name=order]").val(order);
            $("#sort-form input[name=direc]").val(direc);

            //submit form
            $("#sort-form").get(0).submit();
        });
        $("#filter button.btn-media").click(function () {
            var arr = [];
            $(this).toggleClass('active');
            $("#filter button.btn-media.active").each(function () {
                var a = $(this).attr('data-media');
                arr.push(a);
            });
            var media = arr.toString();
            $("#sort-form input[name=media_type]").val(media);

            //submit form
            $("#sort-form").get(0).submit();
        });
        $("#tags button.btn-update-tags").click(function () {
            var arr = [];
            $("#tags .label input").each(function () {
                if (!$(this).is(":checked")) {
                    var a = $(this).val();
                    arr.push(a);
                }
            });
            var tags = arr.toString();
            $("#sort-form input[name=remove_tags]").val(tags);

            //submit form
            $("#sort-form").get(0).submit();
        });
        $("#authors button.btn-update-authors").click(function () {
            var arr = [];
            $("#authors .label input").each(function () {
                if (!$(this).is(":checked")) {
                    var a = $(this).val();
                    arr.push(a);
                }
            });
            var authors = arr.toString();
            $("#sort-form input[name=remove_authors]").val(authors);

            //submit form
            $("#sort-form").get(0).submit();
        });
    });
</script>