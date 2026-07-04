<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$conventionSD = $conventionSD ?? (object)['Conventions' => ['name' => ''], 'season_year' => ''];
$convention_slug = $convention_slug ?? '';
$convention_season_slug = $convention_season_slug ?? '';
$schedulingD = $schedulingD ?? (object)[
	'precheck_events' => 0,
	'total_events_found' => null,
	'precheck_locations' => 0,
	'total_locations_found' => null,
	'precheck_registrations' => 0,
	'total_registrations_found' => null,
	'precheck_students' => 0,
	'total_students_found' => null,
];
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper admin-sched-precheck-page">
    <section class="content-header">
      <h1>
        Scheduling Pre-check - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Pre-check </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info admin-data-box">
            <div class="box-header with-border">
                <h3 class="box-title">Pre-check Overview</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			
			
			<div class="admin-precheck-summary">
				<h2>Scheduling Pre-check</h2>
				<table class="table table-bordered admin-precheck-table">
					<tr>
						<th>#</th>
						<th>Pre-Check Status</th>
						<th>Data Found</th>
						<th>Action</th>
					</tr>
					
					<tr>
						<td>Events</td>
						<td>
							<?php
							if($schedulingD->precheck_events>0)
							{
								echo '<span class="precheck-status precheck-status-ok"><i class="fa fa-check"></i> Ready</span>';
							}
							else
							{
								echo '<span class="precheck-status precheck-status-pending">Pending</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_events>0)
							{
								echo 'Total event(s) found: '.$schedulingD->total_events_found;
							}
							else
							{
								echo '<span class="precheck-empty">-</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_events>0)
							{
								echo $this->Html->link('Re-Check Events', ['controller'=>'schedulings', 'action' => 'precheckevents',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to re-check events ?']);
							}
							else
							{
								echo $this->Html->link('Check Events', ['controller'=>'schedulings', 'action' => 'precheckevents',$convention_season_slug], ['class'=>'btn btn-default canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to check events ?']);
							}
							?>
						</td>
					</tr>
					
					<tr>
						<td>Locations</td>
						<td>
							<?php
							if($schedulingD->precheck_locations>0)
							{
								echo '<span class="precheck-status precheck-status-ok"><i class="fa fa-check"></i> Ready</span>';
							}
							else
							{
								echo '<span class="precheck-status precheck-status-pending">Pending</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_locations>0)
							{
								echo 'Total location(s) found: '.$schedulingD->total_locations_found;
							}
							else
							{
								echo '<span class="precheck-empty">-</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_locations>0)
							{
								echo $this->Html->link('Re-Check Locations', ['controller'=>'schedulings', 'action' => 'prechecklocations',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to re-check locations ?']);
							}
							else
							{
								echo $this->Html->link('Check Locations', ['controller'=>'schedulings', 'action' => 'prechecklocations',$convention_season_slug], ['class'=>'btn btn-default canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to check locations ?']);
							}
							?>
						</td>
					</tr>
					
					<tr>
						<td>Registrations</td>
						<td>
							<?php
							if($schedulingD->precheck_registrations>0)
							{
								echo '<span class="precheck-status precheck-status-ok"><i class="fa fa-check"></i> Ready</span>';
							}
							else
							{
								echo '<span class="precheck-status precheck-status-pending">Pending</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_registrations>0)
							{
								echo 'Total registration(s) found: '.$schedulingD->total_registrations_found;
							}
							else
							{
								echo '<span class="precheck-empty">-</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_registrations>0)
							{
								echo $this->Html->link('Re-Check Registrations', ['controller'=>'schedulings', 'action' => 'precheckregistrations',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to re-check registrations ?']);
							}
							else
							{
								echo $this->Html->link('Check Registrations', ['controller'=>'schedulings', 'action' => 'precheckregistrations',$convention_season_slug], ['class'=>'btn btn-default canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to check registrations ?']);
							}
							?>
						</td>
					</tr>
					
					
					<tr>
						<td>Students</td>
						<td>
							<?php
							if($schedulingD->precheck_students>0)
							{
								echo '<span class="precheck-status precheck-status-ok"><i class="fa fa-check"></i> Ready</span>';
							}
							else
							{
								echo '<span class="precheck-status precheck-status-pending">Pending</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_students>0)
							{
								echo 'Total student(s) found: '.$schedulingD->total_students_found;
							}
							else
							{
								echo '<span class="precheck-empty">-</span>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_students>0)
							{
								echo $this->Html->link('Re-Check Students', ['controller'=>'schedulings', 'action' => 'precheckstudents',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to re-check students ?']);
							}
							else
							{
								echo $this->Html->link('Check Students', ['controller'=>'schedulings', 'action' => 'precheckstudents',$convention_season_slug], ['class'=>'btn btn-default canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to check students ?']);
							}
							?>
						</td>
					</tr>
					
				</table>
			</div>
			
			
			
			<?php echo $this->Form->create(null, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
					<div class="box-body">
                    <div class="box-footer admin-precheck-actions">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
						
						<?php
						echo $this->Html->link('<< Back To Seasons', ['controller'=>'conventions', 'action' => 'seasons',$convention_slug], ['class'=>'btn btn-default canlcel_le admin-precheck-btn']);
						
						echo $this->Html->link('Reset All Pre-check', ['controller'=>'schedulings', 'action' => 'resetallprecheck',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le admin-precheck-btn', 'confirm' => 'Are you sure you want to reset all pre-checks ?']);
						
						echo $this->Html->link('Scheduling Wizard', ['controller'=>'schedulings', 'action' => 'wizard',$convention_season_slug], ['class'=>'btn btn-success canlcel_le admin-precheck-btn','title'=>'Scheduling Wizard']);

						echo $this->Html->link('Scheduling Tweaks', ['controller'=>'schedulingtweaks', 'action' => 'index',$convention_season_slug], ['class'=>'btn btn-info canlcel_le admin-precheck-btn','title'=>'Scheduling Tweaks']);
						
						echo $this->Html->link('View/Start Scheduling', ['controller'=>'schedulings', 'action' => 'schedulecategory',$convention_season_slug], ['class'=>'btn btn-success canlcel_le admin-precheck-btn','title'=>'View/Start Scheduling']);
						
						echo $this->Html->link('Overwrite Timings', ['controller'=>'schedulings', 'action' => 'overwritetimings',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le admin-precheck-btn','title'=>'Overwrite Timings']);
						
						echo $this->Html->link('Reports', ['controller'=>'schedulings', 'action' => 'reports',$convention_season_slug], ['class'=>'btn btn-info canlcel_le admin-precheck-btn','title'=>'Generate Reports']);
						
						
						?>
						
						
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
			
			
			
			
          </div>
		  
		  
			
			
    </section>
  </div>