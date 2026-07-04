<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>
<script>
	$(document).ready(function(){
		// Preserve server-rendered values so the plugin cannot force its default 10:00 AM.
		$('.mdtpicker').each(function(){
			$(this).attr('data-initial-time-value', $(this).val());
		});

		$('.mdtpicker').mdtimepicker(); //Initializes the time picker

		$('.mdtpicker').each(function(){
			var initialValue = $(this).attr('data-initial-time-value');
			if (typeof initialValue !== 'undefined') {
				$(this).val(initialValue);
			}

			if (!initialValue) {
				$(this).attr('data-time', '');
			}
		});
	});
</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#start_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
			minDate: '0d',
            maxDate: '+2y'
        });
    });
</script>

<?php
$hasSchedulings = is_object($schedulings);

$wizardTimeValues = isset($wizardTimeValues) && is_array($wizardTimeValues) ? $wizardTimeValues : [];
$formatWizardTimeValue = function($value) {
	if ($value === null || $value === '') {
		return '';
	}

	if ($value instanceof \DateTimeInterface) {
		return $value->format('h:i A');
	}

	$timestamp = strtotime((string)$value);
	if ($timestamp === false) {
		return '';
	}

	return date('h:i A', $timestamp);
};

$getWizardTimeValue = function($field) use ($wizardTimeValues, $hasSchedulings, $schedulings, $formatWizardTimeValue) {
	if (isset($wizardTimeValues[$field]) && $wizardTimeValues[$field] !== '') {
		return (string)$wizardTimeValues[$field];
	}

	if ($hasSchedulings && isset($schedulings->{$field}) && $schedulings->{$field} !== null && $schedulings->{$field} !== '') {
		return $formatWizardTimeValue($schedulings->{$field});
	}

	return '';
};

if($hasSchedulings && !empty($schedulings->start_date) && isset($schedulings->start_date))
{
	$schedulings->start_date = date("Y-m-d", strtotime($schedulings->start_date));
}

if($hasSchedulings && !empty($schedulings->normal_starting_time) && isset($schedulings->normal_starting_time))
{
	$schedulings->normal_starting_time= date("h:i A", strtotime($schedulings->normal_starting_time));
}
if($hasSchedulings && !empty($schedulings->normal_finish_time) && isset($schedulings->normal_finish_time))
{
	$schedulings->normal_finish_time= date("h:i A", strtotime($schedulings->normal_finish_time));
}
if($hasSchedulings && !empty($schedulings->lunch_time_start) && isset($schedulings->lunch_time_start))
{
	$schedulings->lunch_time_start= date("h:i A", strtotime($schedulings->lunch_time_start));
}
if($hasSchedulings && !empty($schedulings->lunch_time_end) && isset($schedulings->lunch_time_end))
{
	$schedulings->lunch_time_end = date("h:i A", strtotime($schedulings->lunch_time_end));
}

// to check if start on different time on first day
$box_starting_different_time_first_day_yes_no = "none";
if($hasSchedulings && !empty($schedulings->starting_different_time_first_day_yes_no))
{
	$box_starting_different_time_first_day_yes_no = "block";
	
	if(!empty($schedulings->different_first_day_start_time) && isset($schedulings->different_first_day_start_time))
	{
		$schedulings->different_first_day_start_time = date("h:i A", strtotime($schedulings->different_first_day_start_time));
	}
	if(!empty($schedulings->different_first_day_end_time) && isset($schedulings->different_first_day_end_time))
	{
		$schedulings->different_first_day_end_time = date("h:i A", strtotime($schedulings->different_first_day_end_time));
	}
}

$box_judging_breaks_yes_no = "none";
if($hasSchedulings && !empty($schedulings->judging_breaks_yes_no))
{
	$box_judging_breaks_yes_no = "block";
	
	if(!empty($schedulings->judging_breaks_morning_break_starting_time) && isset($schedulings->judging_breaks_morning_break_starting_time))
	{
		$schedulings->judging_breaks_morning_break_starting_time= date("h:i A", strtotime($schedulings->judging_breaks_morning_break_starting_time));
	}
	if(!empty($schedulings->judging_breaks_morning_break_finish_time) && isset($schedulings->judging_breaks_morning_break_finish_time))
	{
		$schedulings->judging_breaks_morning_break_finish_time= date("h:i A", strtotime($schedulings->judging_breaks_morning_break_finish_time));
	}
	if(!empty($schedulings->judging_breaks_afternoon_break_start_time) && isset($schedulings->judging_breaks_afternoon_break_start_time))
	{
		$schedulings->judging_breaks_afternoon_break_start_time= date("h:i A", strtotime($schedulings->judging_breaks_afternoon_break_start_time));
	}
	if(!empty($schedulings->judging_breaks_afternoon_break_finish_time) && isset($schedulings->judging_breaks_afternoon_break_finish_time))
	{
		$schedulings->judging_breaks_afternoon_break_finish_time= date("h:i A", strtotime($schedulings->judging_breaks_afternoon_break_finish_time));
	}
}


$box_sports_day_yes_no = "none";
if($hasSchedulings && !empty($schedulings->sports_day_yes_no))
{
	$box_sports_day_yes_no = "block";
	
	if(!empty($schedulings->sports_day_starting_time) && isset($schedulings->sports_day_starting_time))
	{
		$schedulings->sports_day_starting_time= date("h:i A", strtotime($schedulings->sports_day_starting_time));
	}
	if(!empty($schedulings->sports_day_finish_time) && isset($schedulings->sports_day_finish_time))
	{
		$schedulings->sports_day_finish_time= date("h:i A", strtotime($schedulings->sports_day_finish_time));
	}
}

$box_sports_day_having_events_after_sport_yes_no = "none";

if($hasSchedulings && !empty($schedulings->sports_day_having_events_after_sport_yes_no))
{
	$box_sports_day_having_events_after_sport_yes_no = "block";
	
	if(!empty($schedulings->sports_day_other_starting_time) && isset($schedulings->sports_day_other_starting_time))
	{
		$schedulings->sports_day_other_starting_time= date("h:i A", strtotime($schedulings->sports_day_other_starting_time));
	}
	if(!empty($schedulings->sports_day_other_finish_time) && isset($schedulings->sports_day_other_finish_time))
	{
		$schedulings->sports_day_other_finish_time= date("h:i A", strtotime($schedulings->sports_day_other_finish_time));
	}
}

$normalStartingTimeValue = $getWizardTimeValue('normal_starting_time');
$normalFinishTimeValue = $getWizardTimeValue('normal_finish_time');
$lunchStartTimeValue = $getWizardTimeValue('lunch_time_start');
$lunchEndTimeValue = $getWizardTimeValue('lunch_time_end');
$firstDayStartTimeValue = $getWizardTimeValue('different_first_day_start_time');
$firstDayEndTimeValue = $getWizardTimeValue('different_first_day_end_time');
$morningBreakStartTimeValue = $getWizardTimeValue('judging_breaks_morning_break_starting_time');
$morningBreakEndTimeValue = $getWizardTimeValue('judging_breaks_morning_break_finish_time');
$afternoonBreakStartTimeValue = $getWizardTimeValue('judging_breaks_afternoon_break_start_time');
$afternoonBreakEndTimeValue = $getWizardTimeValue('judging_breaks_afternoon_break_finish_time');
$sportsDayStartTimeValue = $getWizardTimeValue('sports_day_starting_time');
$sportsDayEndTimeValue = $getWizardTimeValue('sports_day_finish_time');
$sportsOtherStartTimeValue = $getWizardTimeValue('sports_day_other_starting_time');
$sportsOtherEndTimeValue = $getWizardTimeValue('sports_day_other_finish_time');

$normalizeCoreTimeValue = function($value, $defaultValue) {
	$trimmed = trim((string)$value);
	if ($trimmed === '' || $trimmed === '10:00 AM' || $trimmed === '10:00') {
		return '';
	}
	return $trimmed;
};

$normalStartingTimeValue = $normalizeCoreTimeValue($normalStartingTimeValue, '');
$normalFinishTimeValue = $normalizeCoreTimeValue($normalFinishTimeValue, '');
$lunchStartTimeValue = $normalizeCoreTimeValue($lunchStartTimeValue, '');
$lunchEndTimeValue = $normalizeCoreTimeValue($lunchEndTimeValue, '');
$sportsDayStartTimeValue = $normalizeCoreTimeValue($sportsDayStartTimeValue, '');
$sportsDayEndTimeValue = $normalizeCoreTimeValue($sportsDayEndTimeValue, '');
$sportsOtherStartTimeValue = $normalizeCoreTimeValue($sportsOtherStartTimeValue, '');
$sportsOtherEndTimeValue = $normalizeCoreTimeValue($sportsOtherEndTimeValue, '');

$forceTimeValuesJson = json_encode([
	'schedulings-normal-starting-time' => $normalStartingTimeValue,
	'schedulings-normal-finish-time' => $normalFinishTimeValue,
	'schedulings-lunch-time-start' => $lunchStartTimeValue,
	'schedulings-lunch-time-end' => $lunchEndTimeValue,
	'schedulings-different-first-day-start-time' => $firstDayStartTimeValue,
	'schedulings-different-first-day-end-time' => $firstDayEndTimeValue,
	'schedulings-judging-breaks-morning-break-starting-time' => $morningBreakStartTimeValue,
	'schedulings-judging-breaks-morning-break-finish-time' => $morningBreakEndTimeValue,
	'schedulings-judging-breaks-afternoon-break-start-time' => $afternoonBreakStartTimeValue,
	'schedulings-judging-breaks-afternoon-break-finish-time' => $afternoonBreakEndTimeValue,
	'schedulings-sports-day-starting-time' => $sportsDayStartTimeValue,
	'schedulings-sports-day-finish-time' => $sportsDayEndTimeValue,
	'schedulings-sports-day-other-starting-time' => $sportsOtherStartTimeValue,
	'schedulings-sports-day-other-finish-time' => $sportsOtherEndTimeValue,
]);
?>

<!-- wizard-build: <?php echo isset($wizardBuild) ? h($wizardBuild) : 'unknown'; ?> -->

<div class="content-wrapper admin-sched-wizard-page">
    <section class="content-header">
      <h1>
        Scheduling Wizard - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Wizard </li>
      </ol>
    </section>

	<section class="content">
	 <div class="box box-info admin-data-box">
            <div class="box-header with-border">
				<h3 class="box-title">Scheduling Configuration</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($schedulings, ['id'=>'schedulingWizardForm', 'type' => 'file', 'autocomplete' => 'off']); ?>
				<div class="form-horizontal admin-sched-wizard-form">
                    <div class="box-body">
					
					
					<!-- Convention Days Starts -->
					<div class="form-group wizard-section-row">
                      <label class="col-sm-2 control-label"><h3>Convention Days </h3><span class="require"></span></label>
                      <div class="col-sm-10">
                          &nbsp;
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Start Date <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.start_date', ['id'=>'start_date', 'label'=>false, 'type'=>'test',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Start Date']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">First Day <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Schedulings.first_day', $weekDays, ['id' => 'first_day', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Number of Days <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.number_of_days', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required number', 'placeholder'=>'Number of Days']); ?>
                      </div>
                    </div>

					<div class="form-group">
					  <label class="col-sm-2 control-label">Distribution</label>
					  <div class="col-sm-10">
							<?php echo $this->Form->checkbox('Schedulings.round_robin_day_distribution_yes_no', ['value'=>'1','id'=>'round_robin_day_distribution_yes_no']); ?>
							Enable day-by-day round robin distribution (rotate slots across convention days)
					  </div>
					</div>
					<!-- Convention Days Ends -->
					
					
					
					<!-- Times Starts -->
					<div class="form-group wizard-section-row">
                      <label class="col-sm-2 control-label"><h3>Times </h3><span class="require"></span></label>
                      <div class="col-sm-10">
                          &nbsp;
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Normal Starting Time <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <input
							  type="text"
							  id="schedulings-normal-starting-time"
							  name="Schedulings[normal_starting_time]"
							  class="form-control required mdtpicker"
							  placeholder="Normal Starting Time"
							  value="<?php echo h($normalStartingTimeValue); ?>"
						  />
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Normal Finish Time <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <input
							  type="text"
							  id="schedulings-normal-finish-time"
							  name="Schedulings[normal_finish_time]"
							  class="form-control required mdtpicker"
							  placeholder="Normal Finish Time"
							  value="<?php echo h($normalFinishTimeValue); ?>"
						  />
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Lunch Time Start <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <input
							  type="text"
							  id="schedulings-lunch-time-start"
							  name="Schedulings[lunch_time_start]"
							  class="form-control required mdtpicker"
							  placeholder="Lunch Time Start"
							  value="<?php echo h($lunchStartTimeValue); ?>"
						  />
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Lunch Time End <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <input
							  type="text"
							  id="schedulings-lunch-time-end"
							  name="Schedulings[lunch_time_end]"
							  class="form-control required mdtpicker"
							  placeholder="Lunch Time End"
							  value="<?php echo h($lunchEndTimeValue); ?>"
						  />
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
							<?php echo $this->Form->checkbox('Schedulings.starting_different_time_first_day_yes_no', ['value'=>'1','id'=>'starting_different_time_first_day_yes_no']); ?>
							We are starting at a different time on the first day
                      </div>
                    </div>
					
					<div id="box_starting_different_time_first_day_yes_no" class="wizard-conditional-box" style="display:<?php echo $box_starting_different_time_first_day_yes_no; ?>;">
						<div class="form-group">
						  <label class="col-sm-2 control-label">First Day Start Time <span class="require">*</span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.different_first_day_start_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'First Day Start Time', 'value' => $firstDayStartTimeValue]); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">First Day End Time <span class="require">*</span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.different_first_day_end_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'First Day End Time', 'value' => $firstDayEndTimeValue]); ?>
						  </div>
						</div>
                    </div>
					<!-- Times Ends -->
					
					
					
					
					<!-- Judging Breaks Starts -->
					<div class="form-group wizard-section-row">
                      <label class="col-sm-2 control-label"><h3>Judging Breaks </h3><span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:30px;">
                          Check the box if you want to schedule breaks for music and platform judges. (They need it but the schedule might be so tight they can't fit one in). We recommend trying to generate the schedule with breaks first and take them out if it can't be done.
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.judging_breaks_yes_no', ['value'=>'1','id'=>'judging_breaks_yes_no']); ?> 						
						Yes we are having judging breaks
                      </div>
                    </div>
					
					<div id="box_judging_breaks_yes_no" class="wizard-conditional-box" style="display:<?php echo $box_judging_breaks_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Morning Break Starting Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_morning_break_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Morning Break Starting Time', 'value' => $morningBreakStartTimeValue]); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Morning Break Finish Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_morning_break_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Morning Break Finish Time', 'value' => $morningBreakEndTimeValue]); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Afternoon Break Start Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_afternoon_break_start_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Afternoon Break Start Time', 'value' => $afternoonBreakStartTimeValue]); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Afternoon Break Finish Time<span class="require">*</span></label>
						  <div class="col-sm-10">
							<?php echo $this->Form->input('Schedulings.judging_breaks_afternoon_break_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Afternoon Break Finish Time', 'value' => $afternoonBreakEndTimeValue]); ?>
						  </div>
						</div>
					
					</div>
					<!-- Judging Breaks Ends -->
					
					
					
					
					
					<!-- Sports Day Starts -->
					<div class="form-group wizard-section-row">
                      <label class="col-sm-2 control-label"><h3>Sports Day </h3><span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:30px;">
                          Check the box if you are having sports day (for track and field). And then choose the day from the list. If you're only having half the day for sport and you want other events in the afternoon then check the box and fill the times for other event.
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.sports_day_yes_no', ['value'=>'1','id'=>'sports_day_yes_no']); ?> 						
						Yes we are having a Sports Day
                      </div>
                    </div>
					
					<div id="box_sports_day_yes_no" class="wizard-conditional-box" style="display:<?php echo $box_sports_day_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Sports Day <span class="require"></span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->select('Schedulings.sports_day', $weekDays, ['id' => 'sports_day', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Starting Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Starting Time', 'value' => $sportsDayStartTimeValue]); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Finish Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Finish Time', 'value' => $sportsDayEndTimeValue]); ?>
						  </div>
						</div>
					
                    </div>
					
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.sports_day_having_events_after_sport_yes_no', ['value'=>'1','id'=>'sports_day_having_events_after_sport_yes_no']); ?> 
						We are having more events after sport
                      </div>
                    </div>
					
					<div id="box_sports_day_having_events_after_sport_yes_no" class="wizard-conditional-box" style="display:<?php echo $box_sports_day_having_events_after_sport_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Starting Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_other_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Starting Time', 'value' => $sportsOtherStartTimeValue]); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Finish Time <span class="require"></span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.sports_day_other_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Finish Time', 'value' => $sportsOtherEndTimeValue]); ?>
						  </div>
						</div>
					
					</div>
					
					<div class="form-group wizard-warning-row">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10" style="color:red;">
							Don't forget to allow travel time between the sports venue and the convention site.
                      </div>
                    </div>
					<!-- Sports Day Ends -->
					
					
					
					
					
					
					<div class="box-footer admin-wizard-actions">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Schedulings.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'precheck', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#starting_different_time_first_day_yes_no").click(function() {
			
			if($("#starting_different_time_first_day_yes_no").prop('checked') == true)
			{
				$("#box_starting_different_time_first_day_yes_no").css("display", "block");
			}
			else
			{
				$("#box_starting_different_time_first_day_yes_no").css("display", "none");
			}
		});
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
		var forcedTimes = <?php echo $forceTimeValuesJson ? $forceTimeValuesJson : '{}'; ?>;

		var enforceTimeValues = function() {
			$.each(forcedTimes, function(inputId, preferredValue) {
				var $input = $('#' + inputId);

				if (!$input.length) {
					return;
				}

				var currentValue = $.trim($input.val());
				if (currentValue === '' || currentValue === '10:00 AM' || currentValue === '10:00') {
					$input.val(preferredValue).attr('value', preferredValue);
				}
			});
		};

		enforceTimeValues();
		setTimeout(enforceTimeValues, 0);
		setTimeout(enforceTimeValues, 150);
		setTimeout(enforceTimeValues, 600);
		setTimeout(enforceTimeValues, 1500);
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#judging_breaks_yes_no").click(function() {
			
			if($("#judging_breaks_yes_no").prop('checked') == true)
			{
				$("#box_judging_breaks_yes_no").css("display", "block");
			}
			else
			{
				$("#box_judging_breaks_yes_no").css("display", "none");
			}
		});
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#sports_day_yes_no").click(function() {
			
			if($("#sports_day_yes_no").prop('checked') == true)
			{
				$("#box_sports_day_yes_no").css("display", "block");
			}
			else
			{
				$("#box_sports_day_yes_no").css("display", "none");
			}
		});
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#sports_day_having_events_after_sport_yes_no").click(function() {
			
			if($("#sports_day_having_events_after_sport_yes_no").prop('checked') == true)
			{
				$("#box_sports_day_having_events_after_sport_yes_no").css("display", "block");
			}
			else
			{
				$("#box_sports_day_having_events_after_sport_yes_no").css("display", "none");
			}
		});
    });
</script>

  