<?php
$conventionSD = $conventionSD ?? null;
$convention_slug = $convention_slug ?? '';
$convention_season_slug = $convention_season_slug ?? '';
$eventsDD = $eventsDD ?? [];
$conventionName = (isset($conventionSD) && $conventionSD) ? (string)($conventionSD->Conventions['name'] ?? '') : '';
$seasonYear = (isset($conventionSD) && $conventionSD) ? (string)($conventionSD->season_year ?? '') : '';
?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#sportsDrawForm').validate();
        $('#event_id').select2();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
                Sports &amp; Elimination Draw - [<?php echo h($conventionName); ?>]&nbsp;&nbsp;
                [<?php echo h($seasonYear); ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons', ['controller'=>'conventions', 'action'=>'seasons', $convention_slug], ['escape'=>false]);?></li>
          <li class="active">Sports &amp; Elimination Draw</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <?php echo $this->Form->create(null, ['id'=>'sportsDrawForm']); ?>
            <div class="form-horizontal">
                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Choose Event <span class="require">*</span></label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->select('Schedulingreports.event_id', $eventsDD, ['id'=>'event_id', 'label'=>false, 'div'=>false, 'class'=>'form-control required', 'autocomplete'=>'off', 'empty'=>'-- Select Event --']); ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <label class="col-sm-2 control-label">&nbsp;</label>
                    <?php echo $this->Form->button('View Draw', ['type'=>'submit', 'class'=>'btn btn-info']); ?>
                    <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action'=>'reports', $convention_season_slug], ['class'=>'btn btn-default']); ?>
                </div>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </section>
</div>