<?php
$conventionSD = $conventionSD ?? null;
$convention_slug = $convention_slug ?? '';
$convention_season_slug = $convention_season_slug ?? '';
$sponsorsDD = $sponsorsDD ?? [];
$conventionName = (isset($conventionSD) && $conventionSD) ? (string)($conventionSD->Conventions['name'] ?? '') : '';
$seasonYear = (isset($conventionSD) && $conventionSD) ? (string)($conventionSD->season_year ?? '') : '';
?>

<script type="text/javascript">
    $(document).ready(function() {
        $('#schedulingWizardForm').validate();
        $('#sponsor_id').select2();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Scheduling Reports By Sponsors - [Convention - <?php echo h($conventionName); ?>]&nbsp;&nbsp;&nbsp;&nbsp;
          [Season Year - <?php echo h($seasonYear); ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Reports By Sponsors</li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create(NULL, ['id'=>'schedulingWizardForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Choose Sponsor <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Schedulingreports.sponsor_id', $sponsorsDD, ['id' => 'sponsor_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose Sponsor']); ?>
                      </div>
                    </div>

                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Generate Report', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action'=>'reports', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                        <?php echo $this->Html->link('<i class="fa fa-print"></i> Print All Sponsors', ['controller'=>'schedulingreports', 'action'=>'bysponsorsallprint', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-success', 'target'=>'_blank', 'style'=>'margin-left:10px;']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
</div>