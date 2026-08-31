<?php
use Cake\ORM\TableRegistry;
$this->Users 				= TableRegistry::get('Users');
$this->Judgeevaluations 	= TableRegistry::get('Judgeevaluations');
$this->Crstudentevents 		= TableRegistry::get('Crstudentevents');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php
$rowsToRender = [];
if (isset($eventsubmissionRows) && is_array($eventsubmissionRows)) {
	$rowsToRender = $eventsubmissionRows;
} elseif (isset($eventsubmissions) && !$eventsubmissions->isEmpty()) {
	foreach ($eventsubmissions as $fallbackSubmission) {
		$rowsToRender[] = [
			'record' => $fallbackSubmission,
			'submission_ids_csv' => (string)((int)$fallbackSubmission->id),
			'convention_registration_ids_csv' => (string)((int)$fallbackSubmission->conventionregistration_id),
			'display_school_name' => (string)($fallbackSubmission->Users['first_name'] ?? ''),
		];
	}
}
?>
<?php if (!empty($rowsToRender)) { ?>
	<div class="panel-body cr-times-entries-card">
        
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

			<div class="tbl-resp-listing">
				<table id="group_events_table" class="table table-striped table-bordered cr-times-table" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging">
							<?php
							if($eventD->group_event_yes_no == 1)
							{
								echo 'Group';
							}
							else
							{
								echo "Student Name";
							}
							?>
							</th>
							<th class="sorting_paging">School</th>
							<th class="sorting_paging">Submission Date</th>
							<th class="sorting_paging">Please enter time in format hh:mm:ss.ms</th>
							<th class="sorting_paging">Withdrawn</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$cntrES = 0;
						foreach($rowsToRender as $rowData)
						{
							$datarecord = $rowData['record'];
							$submissionIdsCsv = (string)($rowData['submission_ids_csv'] ?? (string)((int)$datarecord->id));
							$conventionRegIdsCsv = (string)($rowData['convention_registration_ids_csv'] ?? (string)((int)$datarecord->conventionregistration_id));
							$displaySchoolName = (string)($rowData['display_school_name'] ?? (string)($datarecord->Users['first_name'] ?? ''));
							$submissionIds = array_values(array_unique(array_filter(array_map('intval', explode(',', str_replace(' ', '', $submissionIdsCsv))))));
							$conventionRegIds = array_values(array_unique(array_filter(array_map('intval', explode(',', str_replace(' ', '', $conventionRegIdsCsv))))));
							$primarySubmissionId = !empty($submissionIds) ? (int)$submissionIds[0] : (int)$datarecord->id;
							$cntrES++;
							
						?>
						<tr>
						<td data-title="Student Name / Group" class="cr-group-cell">
						<?php
						if($eventD->group_event_yes_no == 1)
						{
							echo $datarecord->group_name.'<br>';
							
							// now fetch all name of students of this group
							$condAllUGroup = [
								'Crstudentevents.event_id' => (int)$datarecord->event_id,
								'Crstudentevents.group_name' => (string)$datarecord->group_name,
							];
							if (!empty($conventionRegIds)) {
								$condAllUGroup['Crstudentevents.conventionregistration_id IN'] = $conventionRegIds;
							} else {
								$condAllUGroup['Crstudentevents.conventionregistration_id'] = (int)$datarecord->conventionregistration_id;
							}

							$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->all();
							
							$arrNG = array();
							$arrNGMap = [];
							foreach($listAllUGroup as $datamembgroup)
							{
								$nameV = trim($datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name']);
								$nameV = preg_replace('/\s+/', ' ', $nameV ?? '');
								if ($nameV !== '' && !isset($arrNGMap[$nameV])) {
									$arrNGMap[$nameV] = 1;
									$arrNG[] = $nameV;
								}
							}
							
							echo '('.implode(", ",$arrNG).')';
						?>
						
							<a title="View all members of group" href="#" class="btn btn-warning cr-group-eye-btn" data-bs-toggle="modal" data-bs-target="#exampleModal_<?php echo $primarySubmissionId; ?>" style="width:45px;">
								  <i class="fa fa-eye"></i>
							</a>
							<div class="modal fade" id="exampleModal_<?php echo $primarySubmissionId; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
							  <div class="modal-dialog">
								<div class="modal-content">
								
								  <div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Group <?php echo $datarecord->group_name; ?></h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								  </div>
								  
								  <div class="modal-body">
									
									<table class="table">
										<thead>
											<tr>
												<th scope="col">#</th>
												<th scope="col">Name</th>
											</tr>
										</thead>
										<tbody>
											<?php
											// now fetch all students of this group
											$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->all();
											$cntrMM = 1;
											$modalNameMap = [];
											foreach($listAllUGroup as $datamembgroup)
											{
												$modalName = trim($datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name']);
												$modalName = preg_replace('/\s+/', ' ', $modalName ?? '');
												if ($modalName === '' || isset($modalNameMap[$modalName])) {
													continue;
												}
												$modalNameMap[$modalName] = 1;
											?>
											<tr>
												<td scope="row"><?php echo $cntrMM; ?></td>
												<td colspan="2"><?php echo $modalName; ?></td>
											</tr>
											<?php
											$cntrMM++;
											}
											?>
										</tbody>
									</table>
									
								  </div>
								
								</div>
							  </div>
							</div>
						
						<?php
							
							
						}
						else
						{
							echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
						}
						?>
						</td>
						<td data-title="School" class="cr-school-cell"><?php echo h($displaySchoolName); ?></td>
						<td data-title="Submission Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
						
						<td data-title="Time">
						<?php
						$judge_id 	= $this->request->session()->read("user_id");
						$formattedTime = '';
						$checkedWithdr = '';
						
						$condCheckJudging = [
							'Judgeevaluations.uploaded_by_user_id' => $judge_id,
						];
						if (!empty($submissionIds)) {
							$condCheckJudging['Judgeevaluations.eventsubmission_id IN'] = $submissionIds;
						} else {
							$condCheckJudging['Judgeevaluations.eventsubmission_id'] = (int)$datarecord->id;
						}

						$getJudgeEvalData = $this->Judgeevaluations->find()->where($condCheckJudging)->order(['Judgeevaluations.id' => 'DESC'])->first();
						if($getJudgeEvalData && $getJudgeEvalData->time_score != NULL && !empty($getJudgeEvalData->time_score))
						{
							$tScore = $getJudgeEvalData->time_score;
							$tScoreC = $tScore->format('H:i:s.u');
							
							// now remove padded zeros
							if (strpos($tScoreC, '.') !== false) {
							list($hms, $micro) = explode('.', $tScoreC);
							$micro = rtrim($micro, '0'); // remove trailing zeros

							if ($micro === '') {
								$formattedTime = $hms;
							} else {
								$formattedTime = $hms . '.' . $micro;
							}
							} else {
								$formattedTime = $tScoreC;
							}
						}
						
						// to check withdrawn
						if($getJudgeEvalData && $getJudgeEvalData->withdraw_yes_no == 1)
							$checkedWithdr = 'checked';
						else
							$checkedWithdr = '';
						?>
						<input type="text" class="cr-time-input" name="time_score_<?php echo $cntrES; ?>" id="time_score_<?php echo $cntrES; ?>" value="<?php echo $formattedTime; ?>" />
						</td>
						
						<td data-title="Withdrawn">
						<input type="checkbox" class="cr-withdraw-checkbox" name="withdrawn_<?php echo $cntrES; ?>" id="withdrawn_<?php echo $cntrES; ?>" <?php echo $checkedWithdr; ?> />
						
						<input type="hidden" name="submission_ids_<?php echo $cntrES; ?>" id="submission_ids_<?php echo $primarySubmissionId; ?>" value="<?php echo h($submissionIdsCsv); ?>" />
						</td>
					</tr>
						<?php } ?>
						
					</tbody>
					
					<tr class="cr-times-actions-row">
						<td colspan="3"><span>Enter the time, then submit this event entry.</span></td>
						<td>
						<input type="submit" value="Submit" class="btn btn-primary cr-submit-btn" />
						<input type="hidden" name="total_records" id="total_records" value="<?php echo $cntrES; ?>" />
						</td>
						<td></td>
					</tr>
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
	order: [[0, 'asc']],
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
.cr-times-entries-card {
	border: 1px solid #d6e2ee;
	border-radius: 14px;
	background: #ffffff;
	box-shadow: 0 10px 24px rgba(14, 53, 94, 0.08);
	padding: 14px;
}

.cr-times-entries-card .dataTables_wrapper .row {
	margin-left: 0;
	margin-right: 0;
}

.cr-times-entries-card .dataTables_filter label {
	font-weight: 600;
	color: #17324a;
}

.cr-times-entries-card .dataTables_filter input {
	border: 1px solid #b9ccde;
	border-radius: 10px;
	min-height: 38px;
	margin-left: 8px;
}

.cr-times-table {
	border-radius: 12px;
	overflow: hidden;
}

.cr-times-table thead th {
	background: #f3f7fb;
	color: #17324a;
	font-weight: 700;
	vertical-align: middle;
	border-bottom: 1px solid #d6e2ee;
}

.cr-times-table tbody td {
	vertical-align: top;
	border-color: #e3ebf3;
}

.cr-times-table .cr-group-cell {
	color: #17324a;
	font-weight: 500;
	line-height: 1.55;
}

.cr-times-table .cr-school-cell {
	color: #24445f;
	font-weight: 600;
	line-height: 1.45;
}

.cr-time-input {
	width: 100%;
	max-width: 240px;
	border: 1px solid #b9ccde;
	border-radius: 10px;
	min-height: 40px;
	padding: 8px 10px;
}

.cr-time-input:focus {
	border-color: #2c7db8;
	box-shadow: 0 0 0 3px rgba(44, 125, 184, 0.15);
	outline: none;
}

.cr-group-eye-btn {
	border-radius: 10px;
	border-color: #f6bb42;
	background: #f9c65e;
	color: #1e2a39;
}

.cr-group-eye-btn:hover {
	background: #efb94b;
	border-color: #efb94b;
}

.cr-withdraw-checkbox {
	width: 17px;
	height: 17px;
	margin: 5px 0 0 4px;
}

.cr-times-actions-row td {
	background: #f7fafc;
	border-top: 1px solid #d6e2ee !important;
	vertical-align: middle !important;
}

.cr-times-actions-row td:first-child {
	color: #557087;
	font-size: 13px;
}

.cr-submit-btn {
	border-radius: 10px;
	min-width: 120px;
	font-weight: 600;
	padding: 8px 14px;
}

.cr-times-entries-card .dataTables_info {
	color: #35556f;
	font-weight: 500;
}

.cr-times-entries-card .pagination .page-link {
	border-radius: 8px;
	margin-left: 4px;
}

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

<style>
.moretext {display: none;}
</style>





