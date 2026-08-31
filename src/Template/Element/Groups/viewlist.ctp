<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$events->isEmpty()) { ?> 
	<div class="panel-body cr-grouping-panel">
        
		<?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

			<div class="tbl-resp-listing cr-grouping-table-wrap">
				<table id="group_events_table" class="table table-striped table-bordered cr-grouping-table" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging">Event Number</th>
							<th class="sorting_paging">Event Name</th>
							<th class="sorting_paging">Group Event?</th>
							<th class="sorting_paging">Entry Type</th>
							<th class="sorting_paging">Min</th>
							<th class="sorting_paging">Max</th>
							<th class="sorting_paging">Total Students</th>
							<th class="sorting_paging">Students Not Grouped</th>
							<th class="sorting_paging">Status</th>
							<th class="sorting_paging"><i class=" fa fa-gavel"></i> Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($events as $datarecord) { ?>
						<tr>
						<td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
						<td data-title="Event Name"><?php echo $datarecord->event_name;?></td>
						<td data-title="Group Event?">
							<span class="cr-badge <?php echo ($datarecord->group_event_yes_no == 1) ? 'is-yes' : 'is-no'; ?>"><?php echo ($datarecord->group_event_yes_no == 1) ? "Yes" : "No"; ?></span>
						</td>
						<td data-title="Entry Type">
							<?php
							$eventMin = (int)$datarecord->min_no;
							$eventMax = (int)$datarecord->max_no;
							if ($eventMin <= 1 && $eventMax <= 2) {
								$entryType = 'Variable group';
						} elseif ((int)$datarecord->team_event === 1 && $eventMax >= 8) {
								$entryType = 'Large team';
						} elseif ($eventMin > 1 && $eventMin === $eventMax) {
								$entryType = 'Fixed team';
						} else {
								$entryType = 'Flexible team';
							}
							echo '<span class="cr-badge is-info">'.h($entryType).'</span>';
							?>
						</td>
						<td data-title="Min"><?php echo $datarecord->min_no;?></td>
						<td data-title="Max"><?php echo $datarecord->max_no;?></td>
						<td data-title="Total Students">
						<?php
						$condTS = array();
						$condTS[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."')";
						$condTS[] = "(Crstudentevents.convention_id = '".$conventionRegD->convention_id."')";
						$condTS[] = "(Crstudentevents.season_id = '".$conventionRegD->season_id."')";
						$condTS[] = "(Crstudentevents.season_year = '".$conventionRegD->season_year."')";
						$condTS[] = "(Crstudentevents.event_id = '".$datarecord->id."')";
						
						$totalStudentsEvent = $this->Crstudentevents->find()->where($condTS)->count();
						echo $totalStudentsEvent;
						?>
						</td>
						
						<td data-title="Total Students">
						<?php
						$condTS[] = "(Crstudentevents.group_name = '' OR Crstudentevents.group_name IS NULL)";
						$studentNotGrouped = $this->Crstudentevents->find()->where($condTS)->count();
						$groupedStudents = $this->Crstudentevents->find()->where(array_slice($condTS, 0, 5) + ['Crstudentevents.group_name !=' => ''])->all();
						$groupCounts = [];
						foreach ($groupedStudents as $groupedStudent) {
							$groupName = trim((string)$groupedStudent->group_name);
							if ($groupName !== '') {
								$groupCounts[$groupName] = ($groupCounts[$groupName] ?? 0) + 1;
							}
						}
						$incompleteGroupDeficit = 0;
						foreach ($groupCounts as $groupCount) {
							if ($groupCount < (int)$datarecord->min_no) {
								$incompleteGroupDeficit = max($incompleteGroupDeficit, (int)$datarecord->min_no - $groupCount);
							}
						}
						
						if($datarecord->group_event_yes_no == 1)
						{
							echo '<span class="cr-badge '.(($studentNotGrouped > 0) ? 'is-warning' : 'is-good').'">'.$studentNotGrouped.'</span>';
						}
						else
						{
							echo '<span class="cr-badge is-muted">-</span>';
						}
						?>
						</td>

						<td data-title="Status">
						<?php
						if ($datarecord->group_event_yes_no != 1) {
							echo '<span class="cr-badge is-muted">Not applicable</span>';
						} elseif ($totalStudentsEvent < $datarecord->min_no) {
							$studentsNeeded = $datarecord->min_no - $totalStudentsEvent;
							echo '<span class="cr-badge is-warning">Needs '.$studentsNeeded.' more</span>';
						} elseif ($studentNotGrouped > 0) {
							echo '<span class="cr-badge is-warning">Ready to group</span>';
						} elseif ($incompleteGroupDeficit > 0) {
							echo '<span class="cr-badge is-warning">Needs '.$incompleteGroupDeficit.' more</span>';
						} else {
							echo '<span class="cr-badge is-good">Complete</span>';
						}
						?>
						</td>
						
						<td data-title="Action">
							
							<?php
							if($userDetails->user_type == "School")
							{
								// to check if its a group event and min/max students are in range..
								//.. then create group list icon
								
								if($datarecord->group_event_yes_no == 1)
								{
									if($totalStudentsEvent > 0)
									{
										echo $this->Html->link('<i class="fa fa-list"></i> Manage', ['controller' => 'groups', 'action' => 'eventgroups',$datarecord->slug], [ 'escape' => false, 'title' => 'Event groups', 'class'=>'cr-action-link']);
									}
									else
									{
										echo '<span class="cr-action-icon is-disabled" title="Min/max students criteria does not match. Add/remove more students in this event then you can create groups."><i class="fa fa-question-circle"></i></span>';
									}
									
									// to check that if all students assigned to a group or not
									
									if($studentNotGrouped >0)
									{
										echo '<span class="cr-action-icon is-alert" title="Some of the students might not assigned to groups."><i class="fa fa-info-circle"></i></span>';
									}
								}
								else
								{
									echo '<span class="cr-badge is-muted">-</span>';
								}
								
							}
							?>
							
						</td>
						
					</tr>
						<?php } ?>
					</tbody>
				</table>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<script>
$(document).ready(function() {
$('#group_events_table').dataTable({
    //"bPaginate": false,
    "bLengthChange": false,
	"pageLength": 50,
	order: [[8, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#group_events_table').dataTable.search(this.value).draw();
    }); */
});
</script>
<?php echo $this->element("jquery_datatable_code"); ?>





