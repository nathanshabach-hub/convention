<?php
use Cake\ORM\TableRegistry;
$this->Users 				= TableRegistry::get('Users');
$this->Judgeevaluations 	= TableRegistry::get('Judgeevaluations');
$this->Crstudentevents 		= TableRegistry::get('Crstudentevents');
?>

<!-- Mark Command Performance Modal -->
<div id="commandModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:32px;width:360px;max-width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <h5 style="margin:0;font-size:1.1em;font-weight:600;">Mark Command Performance</h5>
      <span onclick="closeCommandModal()" style="cursor:pointer;font-size:1.4em;line-height:1;color:#888;">&times;</span>
    </div>
    <p style="margin-bottom:12px;font-size:0.95em;color:#444;">Please explain why you are marking this entry as command performance:</p>
    <textarea id="commandReason" placeholder="Enter reason..." style="width:100%;height:100px;padding:10px;border:1px solid #ccc;border-radius:6px;font-size:0.9em;resize:vertical;"></textarea>
    <div style="display:flex;justify-content:center;gap:10px;margin-top:16px;">
      <button onclick="closeCommandModal()" style="padding:8px 18px;border-radius:6px;border:1px solid #ccc;background:#333;color:#fff;cursor:pointer;">Cancel</button>
      <button onclick="submitCommand()" style="padding:8px 18px;border-radius:6px;border:none;background:#27ae60;color:#fff;cursor:pointer;">Submit Command</button>
    </div>
  </div>
</div>
<script>
var _commandUrl = '';
function openCommandModal(url) {
  _commandUrl = url;
  document.getElementById('commandReason').value = '';
  document.getElementById('commandModal').style.display = 'flex';
}
function closeCommandModal() {
  document.getElementById('commandModal').style.display = 'none';
}
function submitCommand() {
  var reason = document.getElementById('commandReason').value.trim();
  var form = document.createElement('form');
  form.method = 'POST';
  form.action = _commandUrl;
  var inp = document.createElement('input');
  inp.type = 'hidden'; inp.name = 'command_reason'; inp.value = reason;
  form.appendChild(inp);
  var tok = document.createElement('input');
  tok.type = 'hidden'; tok.name = '_method'; tok.value = 'POST';
  form.appendChild(tok);
  document.body.appendChild(form);
  form.submit();
}
</script>

<!-- Mark Guideline Breach Modal -->
<div id="breachModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:12px;padding:32px;width:360px;max-width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <h5 style="margin:0;font-size:1.1em;font-weight:600;">Mark Guideline Breach</h5>
      <span onclick="closeBreachModal()" style="cursor:pointer;font-size:1.4em;line-height:1;color:#888;">&times;</span>
    </div>
    <p style="margin-bottom:12px;font-size:0.95em;color:#444;">Please explain why you are marking this entry as a guideline breach:</p>
    <textarea id="breachReason" placeholder="Enter reason for breach..." style="width:100%;height:100px;padding:10px;border:1px solid #ccc;border-radius:6px;font-size:0.9em;resize:vertical;"></textarea>
    <div style="display:flex;justify-content:center;gap:10px;margin-top:16px;">
      <button onclick="closeBreachModal()" style="padding:8px 18px;border-radius:6px;border:1px solid #ccc;background:#333;color:#fff;cursor:pointer;">Cancel</button>
      <button onclick="submitBreach()" style="padding:8px 18px;border-radius:6px;border:none;background:#c0392b;color:#fff;cursor:pointer;">Submit Breach</button>
    </div>
  </div>
</div>
<script>
var _breachUrl = '';
function openBreachModal(url) {
  _breachUrl = url;
  document.getElementById('breachReason').value = '';
  document.getElementById('breachModal').style.display = 'flex';
}
function closeBreachModal() {
  document.getElementById('breachModal').style.display = 'none';
}
function submitBreach() {
  var reason = document.getElementById('breachReason').value.trim();
  var form = document.createElement('form');
  form.method = 'POST';
  form.action = _breachUrl;
  var inp = document.createElement('input');
  inp.type = 'hidden'; inp.name = 'breach_reason'; inp.value = reason;
  form.appendChild(inp);
  var tok = document.createElement('input');
  tok.type = 'hidden'; tok.name = '_method'; tok.value = 'POST';
  form.appendChild(tok);
  document.body.appendChild(form);
  form.submit();
}
</script>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <div style="padding:0;">
        
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

            <div class="tbl-resp-listing" style="border:0;border-radius:0;">
                <table id="group_events_table" class="table table-striped table-bordered" style="width:100%">
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
							<th class="sorting_paging">Context</th>
							<th class="sorting_paging">Submitted File</th>
							<th class="sorting_paging">Submission Date</th>
					<th class="sorting_paging">Breach</th>
					<th class="sorting_paging">Command</th>
					<th class="sorting_paging">Done</th>
					<th class="sorting_paging">Score</th>
					<th class="sorting_paging">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($eventsubmissions as $datarecord) { ?>
						<tr>
						<td data-title="Student Name / Group">
						
						<?php
						if($eventD->group_event_yes_no == 1)
						{
							echo $datarecord->group_name.'<br>';
							
							// now fetch all name of students of this group
							$condAllUGroup = array();
							$condAllUGroup[] = "(Crstudentevents.conventionregistration_id = '".$datarecord->conventionregistration_id."' AND Crstudentevents.event_id = '".$datarecord->event_id."' AND Crstudentevents.group_name = '".$datarecord->group_name."')";
							
							$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->limit(4)->all();
							
							$arrNG = array();
							foreach($listAllUGroup as $datamembgroup)
							{
								$nameV =  $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name'];
								$arrNG[] = $nameV;
							}
							
							echo '('.implode(", ",$arrNG).')';
							
						?>
							<a title="View all members of group" href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal_<?php echo $datarecord->id; ?>" style="width:45px;">
								  <i class="fa fa-eye"></i>
							</a>
								
							<div class="modal fade" id="exampleModal_<?php echo $datarecord->id; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
											foreach($listAllUGroup as $datamembgroup)
											{
											?>
											<tr>
												<td scope="row"><?php echo $cntrMM; ?></td>
												<td colspan="2"><?php echo $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name']; ?></td>
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
						<td data-title="School"><?php echo $datarecord->Users['first_name']; ?></td>
						<td data-title="Context"><?php echo ($datarecord->context_box) ? $datarecord->context_box : "N/A"; ?></td>
						<td data-title="Submitted File">
						<?php
							$imgToShow = $datarecord->mediafile_file_system_name;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<a target="_blank" title="'.$eventD->upload_type.'" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->report;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Report" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->score_sheet;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Score Sheet" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->additional_documents;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Additional Documents" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						</td>
						<td data-title="Submission Date">
							<?php
							$submissionDate = 'N/A';
							$timestamp = false;

							if (!empty($datarecord->created)) {
								$createdValue = $datarecord->created;
								if ($createdValue instanceof \DateTimeInterface) {
									$timestamp = $createdValue->getTimestamp();
								} elseif (is_numeric($createdValue)) {
									$timestamp = (int)$createdValue;
								} else {
									$timestamp = strtotime((string)$createdValue);
								}
							}

							if (($timestamp === false || $timestamp <= 0) && !empty($datarecord->modified)) {
								$modifiedValue = $datarecord->modified;
								if ($modifiedValue instanceof \DateTimeInterface) {
									$timestamp = $modifiedValue->getTimestamp();
								} elseif (is_numeric($modifiedValue)) {
									$timestamp = (int)$modifiedValue;
								} else {
									$timestamp = strtotime((string)$modifiedValue);
								}
							}

							if (($timestamp === false || $timestamp <= 0) && !empty($datarecord->slug)) {
								$slugParts = explode('-', (string)$datarecord->slug);
								if (count($slugParts) >= 4 && ctype_digit($slugParts[3])) {
									$timestamp = (int)$slugParts[3];
								}
							}

							if ($timestamp !== false && $timestamp > 0) {
								$submissionDate = date('M d, Y', $timestamp);
							}

							echo $submissionDate;
							?>
						</td>
						
<td data-title="Breach">
					<?php
					if($datarecord->guideline_breach == 0)
					{
						$breachUrl = $this->Url->build(['controller' => 'judgeevaluations', 'action' => 'markbreach', $datarecord->slug]);
						echo '<button type="button" onclick="openBreachModal(\'' . $breachUrl . '\')" class="btn btn-sm btn-outline-danger" style="font-size:11px;padding:4px 8px;">Mark Breach</button>';
					}
					else if($datarecord->guideline_breach == 1)
					{
						echo '<span style="font-size:11px;color:#9c6a16;">Awaiting admin</span>';
					}
					else if($datarecord->guideline_breach == 2)
					{
						echo '<span style="font-size:11px;color:#1f7a45;">Approved</span>';
					}
					?>
				</td>
				
				<td data-title="Command">
					<?php
					if($datarecord->guideline_breach == 0)
					{
						if($datarecord->command_performance == 1)
						{
							echo '<span style="font-size:11px;color:#1f7a45;font-weight:600;">Yes</span>';
						}
						else
						{
							$commandUrl = $this->Url->build(['controller' => 'judgeevaluations', 'action' => 'markcommand', $datarecord->slug]);
							echo '<button type="button" onclick="openCommandModal(\'' . $commandUrl . '\')" class="btn btn-sm btn-outline-success" style="font-size:11px;padding:4px 8px;">Mark Command</button>';
						}
					}
					?>
				</td>
				
				<td data-title="Done" style="text-align:center;">
					<?php
					$condCheckJudging = array();
					$condCheckJudging[] = "(Judgeevaluations.eventsubmission_id = '".$datarecord->id."')";
					$condCheckJudging[] = "(Judgeevaluations.uploaded_by_user_id = '".$this->request->session()->read("user_id")."')";
					$getScores = $this->Judgeevaluations->find()->where($condCheckJudging)->first();
					if($getScores)
					{
						echo '<span class="jee-judged-chip" title="Judged by you"><i class="fa fa-check"></i> Done</span>';
					}
					else { echo '<span style="color:#aaa;font-size:12px;">—</span>'; }
					?>
				</td>
				
				<td data-title="Score" style="text-align:center;">
				<?php
				if(!$getScores)
				{
					echo '<span style="color:#aaa;">—</span>';
				}
				else if($getScores->did_not_attend == 0)
				{
					echo '<span class="jee-score-badge">'.$getScores->total_marks_obtained.'/'.$getScores->total_marks_possible.'</span>';
				}
				else
				{
					echo '<span style="font-size:11px;color:#999;">Did not attend</span>';
						}
						?>
						</td>
						
						
						
						<td data-title="Action">
						<div class="jee-action-group">
						<?php
						if(empty($hasEvaluationForm))
						{
							echo '<span style="font-size:11px;color:#aaa;">N/A</span>';
						}
						else if($datarecord->guideline_breach != 2)
						{
							echo $this->Html->link('<i class="fa fa-pencil-square-o"></i> Evaluate', ['controller' => 'judgeevaluations', 'action' => 'addnew',$conv_reg_slug,$datarecord->slug], [ 'escape' => false, 'title' => 'Submit Evaluation', 'class'=>'btn btn-sm btn-primary', 'style' => 'font-size:11px;padding:4px 9px;', 'confirm' => 'Are you sure you want to submit evaluation for this entry?']);
						}
						echo $this->Html->link('<i class="fa fa-times-circle-o"></i>', ['controller' => 'judgeevaluations', 'action' => 'markdidnotattend',$conv_reg_slug,$datarecord->slug], [ 'escape' => false, 'title' => 'Mark As Did Not Attend', 'class'=>'btn btn-sm btn-outline-secondary', 'style' => 'font-size:11px;padding:4px 8px;display:inline-flex;align-items:center;justify-content:center;width:auto;background:transparent;color:#6c757d;border:1px solid #6c757d;', 'confirm' => 'Are you sure you want to mark this submission as did not attend?']);
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





