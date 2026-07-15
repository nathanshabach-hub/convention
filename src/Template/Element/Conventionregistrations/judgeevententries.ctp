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
					<th class="sorting_paging">Total Score</th>
					<?php if(!empty($isBiblePlacingEvent)) { ?>
					<th class="sorting_paging">Place</th>
					<?php } ?>
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
				
				<td data-title="Total Score" style="text-align:center;">
				<?php
				$saveScoreUrl = $this->Url->build(['controller' => 'conventionregistrations', 'action' => 'savejudgetotalscore', $conv_reg_slug, $datarecord->slug]);
				$totalScore = '';

				if($getScores)
				{
					if (isset($getScores->total_marks_obtained) && $getScores->total_marks_obtained !== '' && $getScores->total_marks_obtained !== null)
					{
						$totalScore = $getScores->total_marks_obtained;
					}
					else if (isset($getScores->all_pos_score) && $getScores->all_pos_score !== '' && $getScores->all_pos_score !== null)
					{
						$totalScore = $getScores->all_pos_score;
					}
					else if (isset($getScores->spelling_score) && $getScores->spelling_score !== '' && $getScores->spelling_score !== null)
					{
						$totalScore = $getScores->spelling_score;
					}
					else if (isset($getScores->soccer_kick_best_kick) && $getScores->soccer_kick_best_kick !== '' && $getScores->soccer_kick_best_kick !== null)
					{
						$totalScore = $getScores->soccer_kick_best_kick;
					}
				}

				if(!$getScores)
				{
					echo '<form method="post" action="'.$saveScoreUrl.'" class="acp-offline-update-form" data-update-type="total_score" data-submission-slug="'.h($datarecord->slug).'" style="display:flex;gap:6px;align-items:center;justify-content:center;margin:0;">';
					echo '<input type="number" step="0.01" min="0" name="total_score" value="" style="width:72px;padding:4px 6px;border:1px solid #ccc;border-radius:6px;font-size:12px;" />';
					echo '<button type="submit" class="btn btn-sm btn-primary" style="font-size:11px;padding:4px 8px;">Save</button>';
					echo '</form>';
				}
				else if($getScores->did_not_attend == 0)
				{
					echo '<form method="post" action="'.$saveScoreUrl.'" class="acp-offline-update-form" data-update-type="total_score" data-submission-slug="'.h($datarecord->slug).'" style="display:flex;gap:6px;align-items:center;justify-content:center;margin:0;">';
					echo '<input type="number" step="0.01" min="0" name="total_score" value="'.h((string)$totalScore).'" style="width:72px;padding:4px 6px;border:1px solid #ccc;border-radius:6px;font-size:12px;" />';
					echo '<button type="submit" class="btn btn-sm btn-primary" style="font-size:11px;padding:4px 8px;">Save</button>';
					echo '</form>';
				}
				else
				{
					echo '<span style="font-size:11px;color:#999;">Did not attend</span>';
						}
						?>
						</td>
						
						<?php if(!empty($isBiblePlacingEvent)) {
							$placeUrl = $this->Url->build(['controller' => 'conventionregistrations', 'action' => 'savebibleplace', $conv_reg_slug, $datarecord->slug]);
							$currentPlace = isset($existingPlaces[$datarecord->id]) ? $existingPlaces[$datarecord->id] : '';
						?>
						<td data-title="Place">
							<form method="post" action="<?php echo $placeUrl; ?>" class="acp-offline-update-form" data-update-type="place" data-submission-slug="<?php echo h($datarecord->slug); ?>" style="display:flex;gap:6px;align-items:center;margin:0;">
								<input type="number" name="place" min="1" value="<?php echo h($currentPlace); ?>" placeholder="Place" style="width:64px;padding:4px 6px;border:1px solid #ccc;border-radius:6px;font-size:12px;" />
								<button type="submit" class="btn btn-sm btn-primary" style="font-size:11px;padding:4px 9px;">Save</button>
							</form>
						</td>
						<?php } ?>
						
						<td data-title="Action">
						<div class="jee-action-group" data-submission-slug="<?php echo h($datarecord->slug); ?>">
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

<script>
/* Phase 3d: Show per-submission pending-sync badge from IndexedDB */
(function () {
  if (!window.indexedDB) return;

  var openReq = indexedDB.open('acp_offline', 1);
  openReq.onsuccess = function (ev) {
    var db = ev.target.result;
    if (!db.objectStoreNames.contains('pending_evaluations')) { db.close(); return; }

    var tx = db.transaction('pending_evaluations', 'readonly');
    var all = [];
    tx.objectStore('pending_evaluations').openCursor().onsuccess = function (e) {
      var cursor = e.target.result;
      if (cursor) { all.push(cursor.value); cursor.continue(); }
      else {
        db.close();
        if (all.length === 0) return;

        all.forEach(function (evalRecord) {
          /* Match the submission slug from the stored URL */
          var urlMatch = (evalRecord.url || '').match(/\/addnew\/[^/]+\/([^/?#]+)/);
          if (!urlMatch) return;
          var slug = urlMatch[1];

          /* Find the action cell with that slug and inject the badge */
          var actionGroups = document.querySelectorAll('.jee-action-group[data-submission-slug="' + slug + '"]');
          actionGroups.forEach(function (group) {
            if (group.querySelector('.acp-pending-badge')) return; /* already added */
            var badge = document.createElement('span');
            badge.className = 'acp-pending-badge';
            badge.title = 'This evaluation is queued and will sync when internet is restored';
            badge.style.cssText = [
              'display:inline-flex', 'align-items:center', 'gap:4px',
              'background:#fff5db', 'color:#8a6400',
              'border:1px solid #e5c847', 'border-radius:999px',
              'padding:3px 9px', 'font-size:11px', 'font-weight:700',
              'margin-left:6px', 'vertical-align:middle', 'cursor:default'
            ].join(';');
            badge.innerHTML = '&#8987; Pending sync';
            group.appendChild(badge);
          });
        });
      }
    };
  };
})();
</script>

<script>
/* Phase 3e: Offline queue for score/place updates on event entries */
(function () {
	if (!window.indexedDB) return;

	var DB_NAME = 'acp_offline';
	var DB_VERSION = 2;
	var STORE_NAME = 'pending_score_updates';

	function openDb(callback) {
		var req = indexedDB.open(DB_NAME, DB_VERSION);
		req.onupgradeneeded = function (ev) {
			var db = ev.target.result;
			if (!db.objectStoreNames.contains('pending_evaluations')) {
				var evalStore = db.createObjectStore('pending_evaluations', { keyPath: 'id', autoIncrement: true });
				evalStore.createIndex('queuedAt', 'queuedAt', { unique: false });
			}
			if (!db.objectStoreNames.contains('judge_cache')) {
				db.createObjectStore('judge_cache', { keyPath: 'key' });
			}
			if (!db.objectStoreNames.contains(STORE_NAME)) {
				var updateStore = db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
				updateStore.createIndex('queuedAt', 'queuedAt', { unique: false });
			}
		};
		req.onsuccess = function (ev) { callback(null, ev.target.result); };
		req.onerror = function () { callback(new Error('Failed to open IndexedDB')); };
	}

	function queueUpdate(form) {
		var formData = new FormData(form);
		var payload = {};
		formData.forEach(function (value, key) {
			payload[key] = value;
		});

		var submitBtn = form.querySelector('button[type="submit"]');
		var originalLabel = submitBtn ? submitBtn.textContent : '';
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.textContent = 'Queued';
		}

		openDb(function (err, db) {
			if (err || !db.objectStoreNames.contains(STORE_NAME)) {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = originalLabel;
				}
				alert('Offline queue unavailable. Please reconnect and try again.');
				return;
			}

			var tx = db.transaction(STORE_NAME, 'readwrite');
			tx.objectStore(STORE_NAME).add({
				url: form.getAttribute('action') || window.location.href,
				method: (form.getAttribute('method') || 'POST').toUpperCase(),
				payload: payload,
				updateType: form.getAttribute('data-update-type') || 'unknown',
				submissionSlug: form.getAttribute('data-submission-slug') || '',
				queuedAt: new Date().toISOString()
			});

			tx.oncomplete = function () {
				db.close();
				addPendingBadge(form.getAttribute('data-submission-slug') || '');
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = originalLabel;
				}
				notify('Saved offline. This update will sync when internet is restored.');
			};

			tx.onerror = function () {
				db.close();
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = originalLabel;
				}
				alert('Could not queue offline update. Please reconnect and try again.');
			};
		});
	}

	function flushQueuedUpdates() {
		if (!navigator.onLine) return;

		openDb(function (err, db) {
			if (err || !db.objectStoreNames.contains(STORE_NAME)) return;

			var tx = db.transaction(STORE_NAME, 'readonly');
			var getAllReq = tx.objectStore(STORE_NAME).getAll();
			getAllReq.onsuccess = function () {
				var queued = getAllReq.result || [];
				db.close();
				if (queued.length === 0) return;

				var synced = 0;
				var failed = 0;

				var chain = Promise.resolve();
				queued.forEach(function (item) {
					chain = chain.then(function () {
						var body = new URLSearchParams();
						Object.keys(item.payload || {}).forEach(function (key) {
							body.append(key, item.payload[key]);
						});

						return fetch(item.url, {
							method: item.method || 'POST',
							credentials: 'same-origin',
							headers: {
								'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
								'X-Requested-With': 'XMLHttpRequest'
							},
							body: body.toString()
						}).then(function (resp) {
							if (!resp.ok) throw new Error('HTTP ' + resp.status);
							synced++;
							removeQueuedUpdate(item.id, item.submissionSlug);
						}).catch(function () {
							failed++;
						});
					});
				});

				chain.then(function () {
					if (synced > 0) notify(synced + ' queued update' + (synced > 1 ? 's' : '') + ' synced.', true);
					if (failed > 0) notify(failed + ' queued update' + (failed > 1 ? 's' : '') + ' failed and will retry.');
				});
			};
		});
	}

	function removeQueuedUpdate(id, slug) {
		openDb(function (err, db) {
			if (err || !db.objectStoreNames.contains(STORE_NAME)) return;
			var tx = db.transaction(STORE_NAME, 'readwrite');
			tx.objectStore(STORE_NAME).delete(id);
			tx.oncomplete = function () {
				db.close();
				refreshPendingBadge(slug || '');
			};
		});
	}

	function refreshPendingBadge(slug) {
		if (!slug) return;
		openDb(function (err, db) {
			if (err || !db.objectStoreNames.contains(STORE_NAME)) return;
			var tx = db.transaction(STORE_NAME, 'readonly');
			var req = tx.objectStore(STORE_NAME).getAll();
			req.onsuccess = function () {
				var list = req.result || [];
				var hasPending = list.some(function (entry) { return entry.submissionSlug === slug; });
				db.close();
				if (!hasPending) {
					var group = document.querySelector('.jee-action-group[data-submission-slug="' + slug + '"]');
					if (!group) return;
					var badge = group.querySelector('.acp-score-pending-badge');
					if (badge) badge.remove();
				}
			};
		});
	}

	function addPendingBadge(slug) {
		if (!slug) return;
		var group = document.querySelector('.jee-action-group[data-submission-slug="' + slug + '"]');
		if (!group) return;
		if (group.querySelector('.acp-score-pending-badge')) return;

		var badge = document.createElement('span');
		badge.className = 'acp-score-pending-badge';
		badge.title = 'Score/place update queued offline';
		badge.style.cssText = [
			'display:inline-flex', 'align-items:center', 'gap:4px',
			'background:#e7f2ff', 'color:#1a4f8a',
			'border:1px solid #97bdf0', 'border-radius:999px',
			'padding:3px 9px', 'font-size:11px', 'font-weight:700',
			'margin-left:6px', 'vertical-align:middle', 'cursor:default'
		].join(';');
		badge.innerHTML = '&#8987; Score pending';
		group.appendChild(badge);
	}

	function hydratePendingBadges() {
		openDb(function (err, db) {
			if (err || !db.objectStoreNames.contains(STORE_NAME)) return;
			var tx = db.transaction(STORE_NAME, 'readonly');
			var req = tx.objectStore(STORE_NAME).getAll();
			req.onsuccess = function () {
				var list = req.result || [];
				db.close();
				list.forEach(function (entry) {
					if (entry.submissionSlug) addPendingBadge(entry.submissionSlug);
				});
			};
		});
	}

	function notify(msg, success) {
		if (typeof window.jQuery !== 'undefined' && window.jQuery.fn && window.jQuery.fn.toast) {
			// No bootstrap toasts here; fallback to alert-like small hint.
		}
		var el = document.createElement('div');
		el.style.cssText = [
			'position:fixed', 'bottom:18px', 'left:50%', 'transform:translateX(-50%)',
			'background:' + (success ? '#2a7a4f' : '#1c2452'), 'color:#fff',
			'padding:10px 16px', 'border-radius:9px', 'z-index:99999',
			'font-size:13px', 'font-weight:600', 'box-shadow:0 5px 16px rgba(0,0,0,0.2)'
		].join(';');
		el.textContent = msg;
		document.body.appendChild(el);
		setTimeout(function () {
			el.style.opacity = '0';
			el.style.transition = 'opacity .35s ease';
			setTimeout(function () { el.remove(); }, 360);
		}, 2600);
	}

	document.querySelectorAll('form.acp-offline-update-form').forEach(function (form) {
		form.addEventListener('submit', function (e) {
			if (navigator.onLine) return;
			e.preventDefault();
			queueUpdate(form);
		});
	});

	window.addEventListener('online', flushQueuedUpdates);
	hydratePendingBadges();
	if (navigator.onLine) flushQueuedUpdates();
})();
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





