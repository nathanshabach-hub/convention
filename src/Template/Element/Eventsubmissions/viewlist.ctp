<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
	<div class="panel-body cr-es-panel">
        
		<?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

			<div class="tbl-resp-listing cr-es-table-wrap">
				<table id="group_events_table" class="table table-striped table-bordered cr-es-table" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging">#Sub. ID</th>
							<th class="sorting_paging">Student</th>
							<th class="sorting_paging">Event No.</th>
							<th class="sorting_paging">Event Name</th>
							<th class="sorting_paging">Group Event?</th>
							<th class="sorting_paging">Group</th>
							
							<th class="sorting_paging">Context</th>
							<th class="sorting_paging">File</th>
							<th class="sorting_paging"><i class=" fa fa-gavel"></i> Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($eventsubmissions as $datarecord) { ?>
						<tr>
						<td data-title="#Submission ID"><?php echo $datarecord->id;?></td>
						<td data-title="Submitted For Student">
						<?php
						if($datarecord->student_id > 0)
						{
							echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
						}
						else
						{
							echo '-';
						}
						?>
						</td>
						<td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
						<td data-title="Event Name"><?php echo $datarecord->Events['event_name'];?></td>
						<td data-title="Group Event?">
							<?php if($datarecord->Events['group_event_yes_no'] == 1){ ?>
								<span class="cr-es-badge is-group">Group</span>
							<?php } else { ?>
								<span class="cr-es-badge is-individual">Individual</span>
							<?php } ?>
						</td>
						<td data-title="Submitted For Group">
						<?php
						if(!empty($datarecord->group_name))
						{
							echo "Group ".$datarecord->group_name;
						}
						else
						{
							echo '-';
						}
						?>
						</td>
						
						<td data-title="Context">
						<?php echo ($datarecord->context_box) ? $datarecord->context_box : "N/A"; ?>
						</td>
						<td data-title="Submitted File" class="cr-es-files-cell">
						<div class="cr-es-files-wrap">
						<?php $hasUploadedFile = false; ?>
						<?php
							$imgToShow = $datarecord->mediafile_file_system_name;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								$hasUploadedFile = true;
								echo '<a target="_blank" title="'.$datarecord->Events['upload_type'].'" class="cr-es-file-chip" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'">'.h(basename($imgToShow)).'</a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->report;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								$hasUploadedFile = true;
								echo '<a class="cr-es-file-chip" target="_blank" title="Report" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'">'.h(basename($imgToShow)).'</a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->score_sheet;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								$hasUploadedFile = true;
								echo '<a class="cr-es-file-chip" target="_blank" title="Score Sheet" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'">'.h(basename($imgToShow)).'</a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->additional_documents;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								$hasUploadedFile = true;
								echo '<a class="cr-es-file-chip" target="_blank" title="Additional Documents" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'">'.h(basename($imgToShow)).'</a>';
							}
						?>
						<?php if(!$hasUploadedFile){ echo '<span class="cr-es-file-empty">No file uploaded</span>'; } ?>
						</div>
						</td>
						
						<td data-title="Action" class="cr-es-action-cell">
							<div class="cr-es-action-wrap">
							<?php
							if($userDetails->user_type == "School")
							{
								// to check if this submission has been evaluated or not
								$countEvaluations = $this->Judgeevaluations->find()->where(["Judgeevaluations.eventsubmission_id" => $datarecord->id])->count();
								if($countEvaluations>0 && $results_release == 1)
								{
									echo $this->Html->link('View Evaluation', ['controller' => 'judgeevaluations', 'action' => 'evaluationslist',$datarecord->slug], [ 'escape' => false, 'title' => 'View Judges Evaluations', 'class'=>'cr-es-action-btn is-view']);
									
									if($datarecord->event_judging_type == 'general')
									{
										echo $this->Html->link('Print', ['controller' => 'judgeevaluations', 'action' => 'evaluationslistprint',$datarecord->slug], [ 'escape' => false, 'title' => 'Print Judges Evaluations', 'target'=>'_blank', 'class'=>'cr-es-action-btn is-print']);
									}
									
								}
								else
								{
									echo $this->Html->link('Remove', ['controller' => 'eventsubmissions', 'action' => 'removesubmission',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove', 'class'=>'cr-es-action-btn is-remove', 'confirm' => 'Are you sure you want to remove this submission?']);
								}
							}
							?> 
							</div>
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
	order: [[6, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#group_events_table').dataTable.search(this.value).draw();
    }); */
});
</script>

<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
    .page-link {
        color: #1c2452 !important;
        background-color: #fff !important;
    }

    .active>.page-link,
    .page-link.active {
        background-color: #1c2452 !important;
        border-color: #1c2452 !important;
        color: #fff !important;
    }

    .pagination {
        border-radius: 0rem !important;
    }
</style>





