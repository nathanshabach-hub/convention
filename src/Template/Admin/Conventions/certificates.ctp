<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Generate Certificate - <?php echo $conventionD->name; ?> (<?php echo $conventionSD->season_year; ?>)
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span>', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false]);?></li>
          <li class="active">Generate Certificate - <?php echo $conventionD->name; ?></li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create(null, ['url' => ['controller' => 'conventions', 'action' => 'certificatespdf', $slug_convention_season, $slug_convention], 'id' => 'adminForm']); ?>
                <div class="form-horizontal">
                    <div class="box-body">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Certificate Type <span class="require">*</span></label>
                            <div class="col-sm-10">
                                <?php echo $this->Form->select('Certificates.cert_type', $certTypes, ['id' => 'cert_type', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose Certificate Type', 'style' => 'margin-bottom:2px;']); ?>
                                <script>
                                    $(document).ready(function() {
                                        $('#cert_type').select2();
                                    });
                                </script>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Recipient Name <span class="require">*</span></label>
                            <div class="col-sm-10">
                                <?php echo $this->Form->input('Certificates.name', ['id' => 'name', 'label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'Enter recipient name', 'autocomplete' => 'off']); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Description / Achievement <span class="require">*</span></label>
                            <div class="col-sm-10">
                                <?php echo $this->Form->input('Certificates.description', ['id' => 'description', 'label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'e.g. 1st Place in Bible Reading, Outstanding Performance...', 'autocomplete' => 'off']); ?>
                            </div>
                        </div>

                        <div class="box-footer">
                            <label class="col-sm-2 control-label">&nbsp;</label>
                            <?php echo $this->Form->button('Generate Certificate', ['type' => 'submit', 'class' => 'btn btn-info', 'div' => false]); ?>
                            <?php echo $this->Html->link('Cancel', ['controller' => 'conventions', 'action' => 'seasons', $slug_convention], ['class' => 'btn btn-default canlcel_le']); ?>
                        </div>

                    </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>
