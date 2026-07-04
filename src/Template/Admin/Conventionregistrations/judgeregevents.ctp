<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    $('#judges_event_ids').select2({
      placeholder: 'Select one or more events',
      width: '100%'
    });
    });
</script>
<?php
if(!empty($CRDetails->judges_event_ids))
{
	$alreadyChooseEvents = explode(",",$CRDetails->judges_event_ids);
}
else
{
	$alreadyChooseEvents = array();
}
?>
<div class="content-wrapper admin-list-page admin-judge-events-page">
    <section class="content-header">
      <h1>Judge Events :: <?php echo $CRDetails->Users['first_name'].' '.$CRDetails->Users['last_name']; ?></h1>
      <p class="admin-judge-events-subtitle">Manage assigned judging events for this registration.</p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> Convention Registrations ', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Judge Events </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info admin-judge-events-card">
            <div class="box-header with-border">
                <h3 class="box-title">Registration Details</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($users, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group admin-judge-events-row">
                      <label class="col-sm-2 control-label">Convention <span class="require"></span></label>
					  <div class="col-sm-10" style="padding-top:6px;">
						  <div class="admin-judge-events-value"><?php echo $CRDetails->Conventions['name']; ?></div>
                      </div>
                    </div>
					
					<div class="form-group admin-judge-events-row">
                      <label class="col-sm-2 control-label">Events <span class="require"></span></label>
					  <div class="col-sm-10">
						  <div class="admin-judge-events-select-wrap">
						  <?php echo $this->Form->select('Conventionregistrations.judges_event_ids', $eventNameIDDD, ['id' => 'judges_event_ids', 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'multiple' => 'multiple', 'value' => $alreadyChooseEvents]); ?>
						  </div>
						  <p class="admin-judge-events-hint">Tip: start typing event code or name to filter quickly.</p>
                      </div>
                    </div>
					
					<div class="form-group admin-judge-events-row">
                      <label class="col-sm-2 control-label">Send Email Notification <span class="require"></span></label>
					  <div class="col-sm-10" style="margin-top:7px;">
						  <label class="admin-judge-events-checkbox">
							  <input class="" type="checkbox" name="send_email_notification" id="send_email_notification" value="1" />
							  <span>Notify this judge by email about updated event assignments</span>
						  </label>
                      </div>
                    </div>
					
                   
                    <div class="box-footer admin-judge-events-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Users.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'conventionregistrations', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>