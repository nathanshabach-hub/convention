<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();

        var $list = $('#video-links-list');
        var $count = $('#video-link-count');

        function buildRow(value) {
            var index = $list.find('.video-link-row').length + 1;
            var $row = $('<div class="video-link-row"></div>');
            var $header = $('<div class="video-link-row-header"></div>');
            var $label = $('<div class="video-link-row-title"></div>').text('Video ' + index);
            var $badge = $('<span class="video-link-row-badge"></span>').text('Link');
            var $body = $('<div class="video-link-row-body"></div>');
            var $input = $('<input type="text" class="form-control video-link-input" name="Settings[video_links][]" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">');
            var $remove = $('<button type="button" class="btn btn-link video-link-remove"><i class="fa fa-trash"></i> Remove</button>');

            if (value) {
                $input.val(value);
            }

            $body.append($input).append($remove);
            $header.append($label).append($badge);
            $row.append($header).append($body);
            return $row;
        }

        function refreshCount() {
            $count.text($list.find('.video-link-row').length);
        }

        $('#add-video-link').on('click', function() {
            $list.append(buildRow(''));
            refreshCount();
        });

        $list.on('click', '.video-link-remove', function() {
            $(this).closest('.video-link-row').remove();
            $list.find('.video-link-row').each(function(idx) {
                $(this).find('.video-link-row-title').text('Video ' + (idx + 1));
            });
            refreshCount();
        });

        refreshCount();
    });
</script>

<style>
.admin-videos-shell {
    background: linear-gradient(180deg, #f7f9fc 0%, #eef4fb 100%);
    padding-bottom: 25px;
}

.admin-videos-card {
    border: 1px solid #d7e1ee;
    border-radius: 14px;
    box-shadow: 0 12px 28px rgba(16, 33, 66, 0.08);
    overflow: hidden;
}

.admin-videos-card .box-header {
    background: linear-gradient(90deg, #1b5fa7 0%, #275d92 100%);
    color: #fff;
    padding: 18px 20px;
    border-bottom: 0;
}

.admin-videos-card .box-title {
    font-size: 18px;
    font-weight: 700;
}

.admin-videos-intro {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: center;
    background: #fff;
    border: 1px solid #dce6f2;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 18px;
}

.admin-videos-intro p {
    margin: 0;
    color: #314664;
    line-height: 1.55;
}

.admin-videos-count {
    min-width: 110px;
    text-align: center;
    border-radius: 999px;
    padding: 8px 12px;
    background: #e7f1ff;
    color: #1b5fa7;
    font-weight: 700;
    white-space: nowrap;
}

.video-link-row {
    background: #fff;
    border: 1px solid #dce6f2;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 12px;
    box-shadow: 0 4px 14px rgba(16, 33, 66, 0.05);
}

.video-link-row-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.video-link-row-title {
    font-size: 14px;
    font-weight: 700;
    color: #18314f;
}

.video-link-row-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #1b5fa7;
    background: #e8f2ff;
    border-radius: 999px;
    padding: 4px 10px;
}

.video-link-row-body {
    display: flex;
    align-items: center;
    gap: 10px;
}

.video-link-input {
    flex: 1;
    border-radius: 10px;
    border-color: #cdd9e6;
    box-shadow: none;
    height: 42px;
}

.video-link-input:focus {
    border-color: #1b5fa7;
    box-shadow: 0 0 0 3px rgba(27, 95, 167, 0.12);
}

.video-link-remove {
    white-space: nowrap;
    color: #b23a3a;
    padding: 0;
    text-decoration: none;
    font-weight: 600;
}

.video-link-remove:hover,
.video-link-remove:focus {
    color: #8d2424;
    text-decoration: none;
}

.videos-action-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    margin-top: 18px;
    padding-top: 18px;
    border-top: 1px solid #e1e9f3;
}

.videos-action-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.videos-action-note {
    color: #6c7b8f;
    font-size: 13px;
    margin: 0;
}

@media (max-width: 767px) {
    .admin-videos-intro,
    .video-link-row-body,
    .videos-action-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .video-link-remove {
        align-self: flex-start;
    }
}
</style>

<div class="content-wrapper admin-videos-shell">
    <section class="content-header">
      <h1>
         Dashboard Videos
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller' => 'admins', 'action' => 'dashboard'], ['escape' => false]); ?></li>
          <li><a href="javascript:void(0);"><i class="fa fa-cogs"></i> Configuration</a></li>
          <li class="active">Videos</li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info admin-videos-card">
            <div class="box-header with-border">
                <h3 class="box-title">Manage User Dashboard Video Links</h3>
            </div>
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>
            <?php echo $this->Form->create(null, ['id' => 'adminForm', 'autocomplete' => 'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
                        <div class="admin-videos-intro">
                            <p><strong>Paste a full YouTube URL</strong> (watch/share/embed) or a YouTube video ID. Leave blank to hide a slot.</p>
                            <div class="admin-videos-count"><?php echo count($videoLinks); ?> links</div>
                        </div>

                        <div id="video-links-list">
                        <?php if (!empty($videoLinks)) : ?>
                            <?php foreach ($videoLinks as $index => $videoLink) : ?>
                            <div class="video-link-row">
                                <div class="video-link-row-header">
                                    <div class="video-link-row-title">Video <?php echo (int)$index + 1; ?></div>
                                    <span class="video-link-row-badge">Link</span>
                                </div>
                                <div class="video-link-row-body">
                                    <input type="text" class="form-control video-link-input" name="Settings[video_links][]" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off" value="<?php echo h($videoLink); ?>" />
                                    <button type="button" class="btn btn-link video-link-remove"><i class="fa fa-trash"></i> Remove</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="video-link-row">
                                <div class="video-link-row-header">
                                    <div class="video-link-row-title">Video 1</div>
                                    <span class="video-link-row-badge">Link</span>
                                </div>
                                <div class="video-link-row-body">
                                    <input type="text" class="form-control video-link-input" name="Settings[video_links][]" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off" />
                                </div>
                            </div>
                        <?php endif; ?>
                        </div>

                        <div class="videos-action-bar">
                            <div class="videos-action-left">
                                <button type="button" id="add-video-link" class="btn btn-default"><i class="fa fa-plus"></i> Add New Link</button>
                                <p class="videos-action-note">You can keep adding more links as needed.</p>
                            </div>
                            <div>
                                <?php echo $this->Form->button('Save Video Links', ['type' => 'submit', 'class' => 'btn btn-info', 'div' => false]); ?>
                                <?php echo $this->Html->link('Cancel', ['action' => 'dashboard'], ['class' => 'btn btn-default canlcel_le']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>