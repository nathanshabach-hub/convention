<script type="text/javascript">
    $(document).ready(function() {
        if ($.isFunction($.fn.validate)) {
            // Keep plugin validation rules but do not intercept native submit.
            $("#schedulingWizardForm").validate({ onsubmit: false });
        }
    });
</script>
<script>
	$(document).ready(function(){
        if ($.isFunction($.fn.mdtimepicker)) {
            $('.mdtpicker').mdtimepicker(); // Initializes the time picker when plugin is available.
        }
        // Fallback: keep field editable so form can be submitted even if picker script fails.
        $('.mdtpicker').prop('readonly', false);

    $('#overwrite_btn').on('click', function(e) {
      e.preventDefault();

      var eventId = $.trim($('#event_id').val());
      var overwriteDate = $.trim($('#overwrite_date').val());
      var overwriteTime = $.trim($('input[name="Schedulings[overwrite_time]"]').val());
      var maxStudents = $.trim($('#max_students').val());

      if (!eventId || !overwriteDate || !overwriteTime || !maxStudents) {
        alert('Please fill all required fields before clicking Overwrite.');
        return;
      }

      var postForm = document.createElement('form');
      postForm.method = 'POST';
      postForm.action = window.location.href;
      postForm.style.display = 'none';

      var fields = {
        'Schedulings[event_id]': eventId,
        'Schedulings[overwrite_date]': overwriteDate,
        'Schedulings[overwrite_time]': overwriteTime,
        'Schedulings[max_students]': maxStudents
      };

      $.each(fields, function(name, value) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        postForm.appendChild(input);
      });

      document.body.appendChild(postForm);
      postForm.submit();
    });
	});
</script>


<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Overwrite Timings - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Overwrite Timings </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($schedulings ?? $schedulingD ?? null, ['id'=>'schedulingWizardForm', 'type' => 'file', 'autocomplete' => 'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					
					<!-- Convention Days Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Event <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Schedulings.event_id', $finalEventArr, ['id' => 'event_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose Event']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Date <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <input id="overwrite_date" name="Schedulings[overwrite_date]" type="date" class="form-control required" value="<?php echo date('Y-m-d'); ?>" />
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Time <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.overwrite_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Choose Time']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Max Students <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.max_students', ['id'=>'max_students', 'label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Max Students', 'min'=>'1']); ?>
                      </div>
                    </div>
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Overwrite', ['type'=>'button', 'id' => 'overwrite_btn', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'precheck', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>