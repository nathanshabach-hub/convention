<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::get('Schedulingtimings');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper admin-sched-category-page">
    <section class="content-header">
      <h1>
        Schedule Category - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Schedule Category </li>
      </ol>
    </section>

	<section class="content">
	 <div class="box box-info admin-data-box">
            <div class="box-header with-border">
				<h3 class="box-title admin-sched-note">
				Note: Schedulings for all categories will be done when you press "Start Scheduling" button.
				<br /><br />
				If this button is not visible, it might be possible that there is no event found in any of these below 4 categories.
				<br /><br />
				After pressing "Start Scheduling" button, please sit back and relax. Scheduling process took some time.
				<br /><br />
				After pressing "Start Scheduling" button, all previous scheduling will reset and start from scratch for this convention season.
				</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			
			
			<div class="admin-sched-category-summary">
				<h2>Schedule Category
				 
				<?php
				if(count($arrEventsC1) > 0 && count($arrEventsC2) > 0 && count($arrEventsC3) > 0 && count($arrEventsC4) > 0)
				{
					echo $this->Html->link('Start Scheduling', ['controller'=>'schedulingtimings', 'action' => 'startschedulec1',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le admin-sched-btn', 'confirm' => 'Are you sure you want to start scheduling?']);
				}
				?>
				</h2> 
				<table class="table table-bordered admin-sched-category-table">
					<tr>
						<th>#</th>
						<th>Needs Schedule</th>
						<th>Group Event</th>
						<th>Event Kind ID</th>
						<th>Has To Be Consecutive</th>
						<th>Number of events found</th>
						<th>View Scheduling</th>
					</tr>
					
					<tr>
						<td>1.</td>
						<td>Yes</td>
						<td>Yes</td>
						<td>Sequential</td>
						<td>Yes</td>
						<td><?php echo count($arrEventsC1); ?></td>
						<td>
							<?php
							if(count($arrEventsC1) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 1,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,1], ['class'=>'btn btn-success canlcel_le admin-sched-btn']);
								}
								else
								{
									echo '<span class="precheck-empty">Schedulings not yet done</span>';
								}
							}
							?>
						</td>
					</tr>
					
					<tr>
						<td>2.</td>
						<td>Yes</td>
						<td>No</td>
						<td>Elimination</td>
						<td>No</td>
						<td><?php echo count($arrEventsC2); ?></td>
						<td>
							<?php
							if(count($arrEventsC2) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,2], ['class'=>'btn btn-success canlcel_le admin-sched-btn']);
								}
								else
								{
									echo '<span class="precheck-empty">Schedulings not yet done</span>';
								}
							}
							?>
						</td>
					</tr>
					
					
					<!-- Category 3 is similar to category 2 -->
					<tr>
						<td>3.</td>
						<td>Yes</td>
						<td>Yes</td>
						<td>Elimination</td>
						<td>No</td>
						<td><?php echo count($arrEventsC3); ?></td>
						<td>
							<?php
							if(count($arrEventsC3) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,3], ['class'=>'btn btn-success canlcel_le admin-sched-btn']);
								}
								else
								{
									echo '<span class="precheck-empty">Schedulings not yet done</span>';
								}
							}
							?>
						</td>
					</tr>
					
					
					
					<!-- Category 4 is similar to category 1 -->
					<tr>
						<td>4.</td>
						<td>Yes</td>
						<td>No</td>
						<td>Sequential</td>
						<td>Yes</td>
						<td><?php echo count($arrEventsC4); ?></td>
						<td>
							<?php
							if(count($arrEventsC4) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 4,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,4], ['class'=>'btn btn-success canlcel_le admin-sched-btn']);
								}
								else
								{
									echo '<span class="precheck-empty">Schedulings not yet done</span>';
								}
							}
							?>
						</td>
					</tr>
					
					<?php
						/* echo $this->Html->link('Remove Overlapping', ['controller'=>'schedulingtimings', 'action' => 'removeoverlapping',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le', 'confirm' => 'Are you sure you want to start process to remove overlapping?']); */
						?>
					
					
					
					
				</table>
			</div>
			
			
			
             
                <div class="form-horizontal">
					<div class="box-body">
					<div class="box-footer admin-sched-category-actions">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
						
						<?php
						echo $this->Html->link('<< Back To Pre-check', ['controller'=>'schedulings', 'action' => 'precheck',$convention_season_slug], ['class'=>'btn btn-default canlcel_le admin-sched-btn']);
						?>
						
						
                    </div>
                  </div>
                </div> 
          </div>
    </section>
  </div>