<?php
use Cake\ORM\TableRegistry;
$this->Conventionregistrations = TableRegistry::getTableLocator()->get('Conventionregistrations');
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
$this->Judgeevaluations = TableRegistry::getTableLocator()->get('Judgeevaluations');
$this->Conventionseasonroomevents = TableRegistry::getTableLocator()->get('Conventionseasonroomevents');
$closeOnly = ((string)$this->request->getQuery('close_only') === '1');
$eventsToCloseCount = 0;
foreach ($conventionseasonevents as $eventRow) {
	if (empty($eventRow->Events) || (int)$eventRow->judging_ends === 1) continue;

	// Count entries (same logic as the table rows below)
	$cntCond = [
		"(Eventsubmissions.conventionseason_id = '{$eventRow->conventionseasons_id}')",
		"(Eventsubmissions.convention_id = '{$eventRow->convention_id}')",
		"(Eventsubmissions.season_id = '{$eventRow->season_id}')",
		"(Eventsubmissions.event_id = '{$eventRow->Events['id']}')",
	];
	$entryCount = $this->Eventsubmissions->find()->where($cntCond)->count();
	if ($entryCount === 0) continue; // skip No Entries

	// Must have at least one judge assigned
	$judgeCheck = $this->Conventionregistrations->find()->where([
		"(Conventionregistrations.conventionseason_id = '{$eventRow->conventionseasons_id}')",
		"(Conventionregistrations.convention_id = '{$eventRow->convention_id}')",
		"(Conventionregistrations.season_id = '{$eventRow->season_id}')",
		"(Conventionregistrations.status = '1')",
	])->all();
	$hasJudge = false;
	foreach ($judgeCheck as $cr) {
		if (!empty($cr->judges_event_ids)) {
			$jids = explode(',', $cr->judges_event_ids);
			if (in_array((string)$eventRow->event_id, $jids)) { $hasJudge = true; break; }
		}
	}
	if (!$hasJudge) continue; // skip No Judges

	$eventsToCloseCount++;
}
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="events-toolbar">
                <div class="events-toolbar__left">
                    <div class="events-toolbar__count">
                        <span class="events-toolbar__count-num"><?php echo (int)$totalEventsConventions; ?></span>
                        <span class="events-toolbar__count-label">event<?php echo ($totalEventsConventions == 1 ? '' : 's'); ?> selected</span>
                    </div>
                </div>
                <div class="events-toolbar__right">
                    <?php echo $this->Html->link('<i class="fa fa-toggle-left"></i> Back to seasons', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false, 'class'=>'btn btn-default btn-sm']);?>
					<?php
					if ($closeOnly) {
						echo $this->Html->link('<i class="fa fa-list"></i> All Events', ['controller'=>'conventions', 'action'=>'events',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default btn-sm']);
					} else {
						echo $this->Html->link('<i class="fa fa-close"></i> Events to Close ('.number_format($eventsToCloseCount).')', ['controller'=>'conventions', 'action'=>'events',$slug_convention_season,$slug_convention,'?' => ['close_only' => 1]], ['escape'=>false, 'class'=>'btn btn-warning btn-sm']);
					}
					?>
                    <?php echo $this->Html->link('<i class="fa fa-refresh"></i> Reset Event List', ['controller'=>'conventions', 'action' => 'reseteventlist',$slug_convention_season,$slug_convention], ['class'=>'btn btn-success btn-sm', 'escape' => false, 'confirm' => 'Are you sure you want to reset event list for this convention? This will delete all events for this convention & selected season ?']); ?>
                </div>
            </div>   

            <div class="tbl-resp-listing events-table-wrap">
                <table id="convention_events" class="table table-bordered table-striped table-condensed cf events-table">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging col-id">#ID</th>
                            <th class="sorting_paging col-id col-id-secondary">#DB</th>
                            <th class="sorting_paging col-num">Event #</th>
                            <th class="sorting_paging">Event Name</th>
                            <th class="sorting_paging">Event Type</th>
                            <th class="sorting_paging">Rooms Allocated</th>
                            <th class="sorting_paging col-num">Entries</th>
                            <th class="sorting_paging col-judges">Judge(s) &amp; Progress</th>
                            <th class="sorting_paging col-status">Status</th>
                            <th class="sorting_paging col-action">Judging?</th>
                            <th class="sorting_paging col-action">Qualifying Score/Time</th>
                            <th class="sorting_paging col-action">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($conventionseasonevents as $datarecord)
						{
							// Skip rows whose underlying Event has been deleted
							if (empty($datarecord->Events)) {
								continue;
							}

							// In "Events to Close" mode, show only events that are not closed yet.
							if ($closeOnly && (int)$datarecord->judging_ends === 1) {
								continue;
							}
							// Here check room ids alocated for this Event
							$condRoomCS = array();
							
							$eventIDCS = $datarecord->Events['id'];
							
							$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
							$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$eventIDCS."' OR 
											Conventionseasonroomevents.event_ids LIKE '".$eventIDCS.",%' OR 
											Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS.",%' OR 
											Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS."')";
							$roomCSEvent = $this->Conventionseasonroomevents->find()->where($condRoomCS)->contain(['Conventionrooms'])->all();
							$roomArrCSEvent = array();
							foreach($roomCSEvent as $roomeventcs)
							{
								$roomArrCSEvent[] = $roomeventcs->Conventionrooms['room_name']." (".$roomeventcs->room_id.")";
							}
						?> 
                            <tr>
                                <td data-title="#ID" class="col-id"><?php echo $datarecord->id;?></td>
                                <td data-title="#DB" class="col-id col-id-secondary"><?php echo $datarecord->Events['id'];?></td>
                                <td data-title="Event #" class="col-num"><strong><?php echo $datarecord->Events['event_id_number'];?></strong></td>
                                <td data-title="Event Name">
                                    <div class="event-name"><?php echo h($datarecord->Events['event_name']);?></div>
                                    <div class="event-judging-type text-muted"><?php echo h($datarecord->Events['event_judging_type']);?></div>
                                </td>
                                <td data-title="Event Type"><?php echo isset($eventTypeDD[$datarecord->Events['event_type']]) ? h($eventTypeDD[$datarecord->Events['event_type']]) : 'Unknown ('.h($datarecord->Events['event_type']).')';?></td>

                                <td data-title="Rooms Allocated">
                                <?php
                                if(count($roomArrCSEvent))
                                {
                                    echo h(implode(", ",$roomArrCSEvent));
                                }
                                else
                                {
                                    echo '<span class="text-muted">&mdash;</span>';
                                }
                                ?></td>

                                <td data-title="Entries" class="col-num">
								<?php
								// to count entries for this event
								$condTotalEntries = array();
								$condTotalEntries[] = "(Eventsubmissions.conventionseason_id = '".$datarecord->conventionseasons_id."')";
								$condTotalEntries[] = "(Eventsubmissions.convention_id = '".$datarecord->convention_id."')";
								$condTotalEntries[] = "(Eventsubmissions.season_id = '".$datarecord->season_id."')";
								$condTotalEntries[] = "(Eventsubmissions.event_id = '".$datarecord->Events['id']."')";
								$totalEntriesEvent = $this->Eventsubmissions->find()->where($condTotalEntries)->count();
								$effectiveEntriesEvent = $this->Eventsubmissions->find()
									->where($condTotalEntries)
									->where(['Eventsubmissions.guideline_breach !=' => 2])
									->count();
								$pendingGuidelineBreaches = $this->Eventsubmissions->find()
									->where($condTotalEntries)
									->where(['Eventsubmissions.guideline_breach' => 1])
									->count();
								$approvedGuidelineBreaches = $this->Eventsubmissions->find()
									->where($condTotalEntries)
									->where(['Eventsubmissions.guideline_breach' => 2])
									->count();
								echo $totalEntriesEvent;
								?>
								</td>
								
                                <td data-title="Judges & Progress" class="col-judges">
								<?php
								// to get judges register for this
								$judgeNamesArr = array();
								$judgeUserIdsArr = array();
								$judgeNameById = array();
								
								// first to get all registrations for this convseason, conv and season
								$condConvreg = array();
								$condConvreg[] = "(Conventionregistrations.conventionseason_id = '".$datarecord->conventionseasons_id."')";
								$condConvreg[] = "(Conventionregistrations.convention_id = '".$datarecord->convention_id."')";
								$condConvreg[] = "(Conventionregistrations.season_id = '".$datarecord->season_id."')";
								$condConvreg[] = "(Conventionregistrations.status = '1')";
								$allConvReg = $this->Conventionregistrations->find()->where($condConvreg)->contain(['Users'])->all();
								foreach($allConvReg as $convreg)
								{
									if(!empty($convreg->judges_event_ids) && !empty($convreg->Users))
									{
										$judges_event_ids_explode = explode(",",$convreg->judges_event_ids);
										if(in_array($datarecord->event_id,$judges_event_ids_explode))
										{
											$judgeFullName = $convreg->Users['first_name'].' '.$convreg->Users['last_name'];
											$judgeNamesArr[] = $judgeFullName;
											$judgeUserIdsArr[] = (int)$convreg->user_id;
											$judgeNameById[(int)$convreg->user_id] = $judgeFullName;
										}
									}
								}
								$judgeUserIdsArr = array_values(array_unique($judgeUserIdsArr));
								$totalAssignedJudges = count($judgeUserIdsArr);

								// Per-judge submitted evaluation counts (distinct submissions evaluated by each judge)
								$perJudgeSubmittedCount = array();
								foreach($judgeUserIdsArr as $jUid)
								{
									$perJudgeSubmittedCount[$jUid] = 0;
								}

								$pendingEntries = 0;
								$completedEntries = 0;
								if($effectiveEntriesEvent > 0 && $totalAssignedJudges > 0)
								{
									$eventSubmissionIds = $this->Eventsubmissions->find()
										->select(['id'])
										->where($condTotalEntries)
										->where(['Eventsubmissions.guideline_breach !=' => 2])
										->enableHydration(false)
										->extract('id')
										->toList();

									$approvedBreachSubmissionCount = $this->Eventsubmissions->find()
										->where($condTotalEntries)
										->where(['Eventsubmissions.guideline_breach' => 2])
										->count();

									if(count($eventSubmissionIds))
									{
										$distinctEvalPairs = $this->Judgeevaluations->find()
											->select(['Judgeevaluations.eventsubmission_id', 'Judgeevaluations.uploaded_by_user_id'])
											->where([
												'Judgeevaluations.eventsubmission_id IN' => $eventSubmissionIds,
												'Judgeevaluations.uploaded_by_user_id IN' => $judgeUserIdsArr
											])
											->distinct(['Judgeevaluations.eventsubmission_id', 'Judgeevaluations.uploaded_by_user_id'])
											->enableHydration(false)
											->toArray();

										$submissionJudgeCounts = array();
										foreach($distinctEvalPairs as $evalPair)
										{
											$submissionId = (int)$evalPair['eventsubmission_id'];
											$evalJudgeId = (int)$evalPair['uploaded_by_user_id'];
											if(!isset($submissionJudgeCounts[$submissionId]))
											{
												$submissionJudgeCounts[$submissionId] = 0;
											}
											$submissionJudgeCounts[$submissionId]++;

											if(isset($perJudgeSubmittedCount[$evalJudgeId]))
											{
												$perJudgeSubmittedCount[$evalJudgeId]++;
											}
										}

										foreach($submissionJudgeCounts as $judgeCountPerSubmission)
										{
											if($judgeCountPerSubmission >= $totalAssignedJudges)
											{
												$completedEntries++;
											}
										}
									}

									if($approvedBreachSubmissionCount > 0)
									{
										$completedEntries += $approvedBreachSubmissionCount;
										if($completedEntries > $effectiveEntriesEvent)
										{
											$completedEntries = $effectiveEntriesEvent;
										}

										foreach($judgeUserIdsArr as $jUid)
										{
											$perJudgeSubmittedCount[$jUid] += $approvedBreachSubmissionCount;
										}
									}

									$pendingEntries = ($effectiveEntriesEvent - $completedEntries);
									if($pendingEntries < 0)
									{
										$pendingEntries = 0;
									}
								}
								
								if(count($judgeUserIdsArr))
								{
									echo '<ul class="judge-progress-list">';
									foreach($judgeUserIdsArr as $jUid)
									{
										$jName = isset($judgeNameById[$jUid]) ? $judgeNameById[$jUid] : ('User #'.$jUid);
										$jSubmitted = isset($perJudgeSubmittedCount[$jUid]) ? (int)$perJudgeSubmittedCount[$jUid] : 0;
										if($jSubmitted > $effectiveEntriesEvent)
										{
											$jSubmitted = $effectiveEntriesEvent;
										}

										$pct = $effectiveEntriesEvent > 0 ? min(100, round(($jSubmitted / $effectiveEntriesEvent) * 100)) : 0;

										if($effectiveEntriesEvent > 0 && $jSubmitted >= $effectiveEntriesEvent)
										{
											$statusClass = 'is-complete';
											$statusLabel = 'Completed';
										}
										else if($effectiveEntriesEvent == 0)
										{
											$statusClass = 'is-none';
											$statusLabel = 'No Entries';
										}
										else if($jSubmitted == 0)
										{
											$statusClass = 'is-notstarted';
											$statusLabel = 'Not Started';
										}
										else
										{
											$statusClass = 'is-progress';
											$statusLabel = 'In Progress';
										}

										echo '<li class="judge-progress-item '.$statusClass.'">';
										echo '<span class="judge-progress-name">'.h($jName).'</span>';
										echo '<span class="judge-progress-meta">';
										echo '<span class="judge-progress-count">'.$jSubmitted.'/'.$effectiveEntriesEvent.'</span>';
										echo '<span class="judge-progress-badge">'.$statusLabel.'</span>';
										echo '</span>';
										if($effectiveEntriesEvent > 0)
										{
											echo '<span class="judge-progress-bar"><span class="judge-progress-bar__fill" style="width:'.$pct.'%;"></span></span>';
										}
										echo '</li>';
									}
									echo '</ul>';
								}
								else
								{
									echo '<span class="text-muted"><em>No judges assigned</em></span>';
								}
								?>
								</td>
								<td data-title="Status" class="col-status">
									<?php
									if($datarecord->judging_ends == 1)
									{
										echo '<span class="status-pill status-pill--closed">Closed</span>';
									}
									else if($effectiveEntriesEvent == 0)
									{
										echo '<span class="status-pill status-pill--neutral">No Entries</span>';
									}
									else if($totalAssignedJudges == 0)
									{
										echo '<span class="status-pill status-pill--neutral">No Judges</span>';
									}
									else if($pendingGuidelineBreaches > 0)
									{
										echo '<span class="status-pill status-pill--pending">Pending Breach</span>';
									}
									else if($pendingEntries > 0)
									{
										echo '<span class="status-pill status-pill--pending">'.$pendingEntries.' remaining</span>';
										if($approvedGuidelineBreaches > 0)
										{
											echo '<br /><small class="text-muted">'.$approvedGuidelineBreaches.' breach approved</small>';
										}
									}
									else
									{
										echo '<span class="status-pill status-pill--ready">Ready to Close</span>';
										if($approvedGuidelineBreaches > 0)
										{
											echo '<br /><small class="text-muted">'.$approvedGuidelineBreaches.' breach approved</small>';
										}
									}
									?>
								</td>
								
								<td data-title="Judging" class="col-action">
									<?php
									if($totalEntriesEvent>0 && count($judgeNamesArr) >0)
									{
										if($datarecord->judging_ends == 1)
										{
											echo '<span class="status-pill status-pill--closed">Closed</span>';
											echo '<br /><br />';
											echo $this->Html->link('<i class="fa fa-unlock"></i> Open', ['controller' => 'results', 'action' => 'openjudging',$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'Reopen Judging', 'class'=>'btn btn-info btn-xs', 'confirm' => 'Are you sure you want to reopen judging for this event?']);
										}
										else if($pendingEntries > 0)
										{
											echo '<span class="status-pill status-pill--pending">Pending ('.$pendingEntries.' left)</span>';
											echo '<br /><br />';

											if($datarecord->Events['event_judging_type'] == 'times')
											{
												$actionClose = 'closejudgingtimes';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'distances')
											{
												$actionClose = 'closejudgingdistances';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'scores')
											{
												$actionClose = 'closejudgingscores';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'soccer_kick')
											{
												$actionClose = 'closejudgingsoccerkick';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'spellings')
											{
												$actionClose = 'closejudgingspellings';
											}
											else
											{
												$actionClose = 'closejudging';
											}

											echo $this->Html->link('<i class="fa fa-close"></i> Close', ['controller' => 'results', 'action' => $actionClose,$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'Close Judging', 'class'=>'btn btn-danger btn-xs', 'confirm' => 'This event still has '.$pendingEntries.' entries pending. Are you sure you want to force close judging for this event?']);
										}
										else
										{
											echo '<span class="status-pill status-pill--ready">Open</span>';
											echo '<br /><br />';
											
											if($datarecord->Events['event_judging_type'] == 'times')
											{
												$actionClose = 'closejudgingtimes';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'distances')
											{
												$actionClose = 'closejudgingdistances';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'scores')
											{
												$actionClose = 'closejudgingscores';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'soccer_kick')
											{
												$actionClose = 'closejudgingsoccerkick';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'spellings')
											{
												$actionClose = 'closejudgingspellings';
											}
											else
											{
												$actionClose = 'closejudging';
											}
											
											echo $this->Html->link('<i class="fa fa-close"></i> Close', ['controller' => 'results', 'action' => $actionClose,$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'Close Judging', 'class'=>'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to close judging for this event? This action cannot be undone ?']);
											
											
										}
									}
									?>
								</td>
								
								<td data-title="Qualifying Score/Time" class="col-action">
								<?php
								//echo $datarecord->Events['event_judging_type'];
								if($datarecord->Events['event_judging_type'] == 'scores' || $datarecord->Events['event_judging_type'] == 'times' || $datarecord->Events['event_judging_type'] == 'distances')
								{
									// Allow to enter qualifying data
									echo $this->Html->link('<i class="fa fa-arrows"></i> Qualifying Data', ['controller' => 'conventions', 'action' => 'qualifyingdata',$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'Qualifying data', 'class'=>'btn btn-primary btn-xs']);
								}
								?>
								</td>
								
								<td data-title="Result" class="col-action">
									<?php
									if($datarecord->judging_ends == 1)
									{
										if($totalEntriesEvent>0 && count($judgeNamesArr) >0)
										{
											if($datarecord->Events['event_judging_type'] == 'times')
											{
												$actionResults = 'resulttimes';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'distances')
											{
												$actionResults = 'resultdistances';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'scores')
											{
												$actionResults = 'resultscores';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'soccer_kick')
											{
												$actionResults = 'resultsoccerkick';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'spellings')
											{
												$actionResults = 'resultspellings';
											}
											else
											{
												$actionResults = 'index';
											}
											
											
											echo $this->Html->link('<i class="fa fa-pencil"></i> Result', ['controller' => 'results', 'action' => $actionResults,$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'View Results', 'class'=>'btn btn-primary btn-xs']);
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

        <div class="search_frm" style="display:none;">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
                //'Delete' => "Delete",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Divisions.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<script>
$(document).ready(function() {
	$('#convention_events').dataTable({
		"bPaginate": true,
		//"bInfo": false,
		"bLengthChange": false,
		"pageLength": 100,
		order: [[0, 'asc']],
		//"bFilter": true,
		//"bInfo": false,
		//"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#convention_events').dataTable.search(this.value).draw();
    }); */
});
</script>

<!--
<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
-->
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

    /* ===== Events listing — refreshed layout ===== */
    .events-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #f7f9fc;
        border: 1px solid #e3e8ef;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }
    .events-toolbar__count {
        display: flex;
        align-items: baseline;
        gap: 8px;
    }
    .events-toolbar__count-num {
        font-size: 22px;
        font-weight: 700;
        color: #1c2452;
        line-height: 1;
    }
    .events-toolbar__count-label {
        color: #5b6b80;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .events-toolbar__right {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .events-table-wrap {
        background: #fff;
        border: 1px solid #e3e8ef;
        border-radius: 6px;
        overflow: hidden;
    }
    table.events-table {
        margin-bottom: 0 !important;
        font-size: 12.5px;
    }
    table.events-table thead th {
        background: #1c2452 !important;
        color: #fff !important;
        font-weight: 600 !important;
        font-size: 11px !important;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-color: #1c2452 !important;
        vertical-align: middle !important;
        padding: 10px 8px !important;
        white-space: nowrap;
    }
    table.events-table tbody td {
        vertical-align: middle !important;
        padding: 10px 8px !important;
    }
    table.events-table tbody tr:hover {
        background-color: #f3f6fb !important;
    }
    table.events-table .col-id { width: 60px; text-align: center; color: #6b7a8f; }
    table.events-table .col-id-secondary { color: #9aa6b8; font-size: 11px; }
    table.events-table .col-num { text-align: center; }
    table.events-table .col-judges { min-width: 280px; }
    table.events-table .col-status { width: 130px; text-align: center; }
    table.events-table .col-action { width: 130px; text-align: center; }

    .event-name { font-weight: 600; color: #1c2452; }
    .event-judging-type {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #8a96a8;
        margin-top: 2px;
    }

    /* Status pills */
    .status-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        color: #fff;
        line-height: 1;
        white-space: nowrap;
    }
    .status-pill--ready    { background-color: #28a745; }
    .status-pill--closed   { background-color: #6c757d; }
    .status-pill--pending  { background-color: #dc3545; }
    .status-pill--neutral  { background-color: #adb5bd; }

    /* Judge progress list */
    .judge-progress-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .judge-progress-item {
        display: grid;
        grid-template-columns: 1fr auto;
        grid-template-areas:
            "name meta"
            "bar  bar";
        align-items: center;
        gap: 4px 10px;
        padding: 6px 8px;
        margin-bottom: 6px;
        border-radius: 4px;
        background: #f7f9fc;
        border-left: 3px solid #adb5bd;
    }
    .judge-progress-item:last-child { margin-bottom: 0; }
    .judge-progress-item.is-complete    { border-left-color: #28a745; background: #eaf7ed; }
    .judge-progress-item.is-progress    { border-left-color: #f0ad4e; background: #fdf6ea; }
    .judge-progress-item.is-notstarted  { border-left-color: #dc3545; background: #fbecec; }
    .judge-progress-item.is-none        { border-left-color: #adb5bd; background: #f1f3f5; }

    .judge-progress-name {
        grid-area: name;
        font-weight: 600;
        color: #1c2452;
        font-size: 12.5px;
    }
    .judge-progress-meta {
        grid-area: meta;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .judge-progress-count {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        font-size: 11.5px;
        color: #1c2452;
    }
    .judge-progress-badge {
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #5b6b80;
        font-weight: 600;
    }
    .judge-progress-item.is-complete   .judge-progress-badge { color: #1d7a36; }
    .judge-progress-item.is-progress   .judge-progress-badge { color: #b97206; }
    .judge-progress-item.is-notstarted .judge-progress-badge { color: #b3261e; }

    .judge-progress-bar {
        grid-area: bar;
        display: block;
        height: 4px;
        width: 100%;
        background: #e3e8ef;
        border-radius: 999px;
        overflow: hidden;
    }
    .judge-progress-bar__fill {
        display: block;
        height: 100%;
        background: #adb5bd;
        transition: width 0.3s ease;
    }
    .judge-progress-item.is-complete   .judge-progress-bar__fill { background: #28a745; }
    .judge-progress-item.is-progress   .judge-progress-bar__fill { background: #f0ad4e; }
    .judge-progress-item.is-notstarted .judge-progress-bar__fill { background: #dc3545; }
</style>