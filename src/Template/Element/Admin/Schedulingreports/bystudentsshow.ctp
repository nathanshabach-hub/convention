<?php
use Cake\ORM\TableRegistry;
$arrStudentSorted = $arrStudentSorted ?? [];
$arrStudentNames = $arrStudentNames ?? [];
$conventionSD = $conventionSD ?? null;
$schoolD = $schoolD ?? null;
$conventionSeasonId = is_object($conventionSD) ? (string)$conventionSD->id : '';
$conventionId = is_object($conventionSD) ? (string)$conventionSD->convention_id : '';
$seasonId = is_object($conventionSD) ? (string)$conventionSD->season_id : '';
$seasonYear = is_object($conventionSD) ? (string)$conventionSD->season_year : '';
$schoolName = is_object($schoolD) ? (string)$schoolD->first_name : '';
$schoolId = is_object($schoolD) ? (string)$schoolD->id : '';
$this->Schedulingtimings = TableRegistry::get('Schedulingtimings');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($arrStudentSorted) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
					<h4><?php echo h($schoolName); ?></h4>
				</div>  
            </div>   
			
			<?php
			// to run through each student name
			$studentCount = count($arrStudentSorted);
			$studentIndex = 0;
			foreach($arrStudentSorted as $student_id_sorted)
			{
				$arrStudentSchedule = array();
			?>
            <div class="tbl-resp-listing">
				<div class="student-header">
					<span class="student-name"><?php echo h($arrStudentNames[$student_id_sorted]); ?></span>
					<span class="school-name"><?php echo h($schoolName); ?></span>
				</div>
                <table id="report_by_school_student" class="table table-bordered table-striped table-condensed cf">
                    
					<tr>
						<th class="sorting_paging" width="15%"><b>Day</b></th>
						<th class="sorting_paging" width="15%"><b>Start</b></th>
						<th class="sorting_paging" width="35%"><b>Event</b></th>
						<th class="sorting_paging" width="35%"><b>Location</b></th>
					</tr>
					<?php
					// now fetch scheduling for this student
					$condSch = array();
					$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSeasonId."' AND 
					Schedulingtimings.convention_id = '".$conventionId."' AND 
					Schedulingtimings.season_id = '".$seasonId."' AND 
					Schedulingtimings.season_year = '".$seasonYear."')";
					$condSch[] = "(Schedulingtimings.user_id = '".$student_id_sorted."' OR Schedulingtimings.user_id_opponent = '".$student_id_sorted."')";
					
					//$condSch[] = "(Schedulingtimings.is_bye != 1 OR Schedulingtimings.is_bye = NULL)";
					
					$schedulingTimingsList = $this->Schedulingtimings->find()
						->where($condSch)
						->contain(["Events","Users","Opponentuser","Conventionrooms"])
						->order(["Schedulingtimings.sch_date_time" => "ASC"])
						->all();
					foreach ($schedulingTimingsList as $datarecord)
					{
						$arrSch		= array();
						$arrSch['sch_date_time']	= $datarecord->sch_date_time;
						$arrSch['day']	= $datarecord->day;
						$arrSch['start_time']	= $datarecord->start_time!=NULL ? date("h:i A",strtotime($datarecord->start_time)) : '';
						
						$arrSch['event_name']	= $datarecord->Events['event_name'].' ('.$datarecord->Events['event_id_number'].')';
						
						$arrSch['room_name']	= $datarecord->Conventionrooms['room_name'];
						$arrSch['db_id']		= $datarecord->id;
						$arrSch['is_bye']		= $datarecord->is_bye;
						$arrSch['schedule_category'] = $datarecord->schedule_category;
						$arrSch['round_number'] = $datarecord->round_number;
						
						$arrStudentSchedule[] = $arrSch;
						
					}
					
					// Here we need to show any group events of this student
					// First lets find if this student is in any group for this convention season
					$condSG = array();
					$condSG[] = "(Crstudentevents.conventionseason_id = '".$conventionSeasonId."' AND 
					Crstudentevents.convention_id = '".$conventionId."' AND 
					Crstudentevents.season_id = '".$seasonId."' AND 
					Crstudentevents.season_year = '".$seasonYear."')";
					$condSG[] = "(Crstudentevents.student_id = '".$student_id_sorted."')";
					$condSG[] = "(Crstudentevents.group_name != '')";
					$studentGroups = $this->Crstudentevents->find()
						->where($condSG)
						->order(["Crstudentevents.id" => "ASC"])
						->all();
					//print_r($studentGroups);exit;
						
					if($studentGroups)
					{
						foreach($studentGroups as $studentgrprec)
						{ 
							// First try WITH group_name filter (performing arts groups: Choir, Dramatic Dialogue, etc.)
							$condSchSG = array();
							$condSchSG[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSeasonId."' AND 
							Schedulingtimings.convention_id = '".$conventionId."' AND 
							Schedulingtimings.season_id = '".$seasonId."' AND 
							Schedulingtimings.season_year = '".$seasonYear."')";
							$condSchSG[] = "(Schedulingtimings.user_id = '".$schoolId."' OR Schedulingtimings.user_id_opponent = '".$schoolId."')";
							$condSchSG[] = "(Schedulingtimings.event_id = '".$studentgrprec->event_id."' AND Schedulingtimings.event_id_number = '".$studentgrprec->event_id_number."' AND Schedulingtimings.group_name = '".$studentgrprec->group_name."')";
							
							$schedulingStGrp = $this->Schedulingtimings->find()
								->where($condSchSG)
								->contain(["Events","Users","Opponentuser","Conventionrooms"])
								->order(["Schedulingtimings.sch_date_time" => "ASC"])
								->all();

							// If no results (team sports: group_name in crstudentevents is team# not pool#),
							// fall back without the group_name filter
							if ($schedulingStGrp->count() === 0) {
								$condSchSGFb = array();
								$condSchSGFb[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSeasonId."' AND 
								Schedulingtimings.convention_id = '".$conventionId."' AND 
								Schedulingtimings.season_id = '".$seasonId."' AND 
								Schedulingtimings.season_year = '".$seasonYear."')";
								$condSchSGFb[] = "(Schedulingtimings.user_id = '".$schoolId."' OR Schedulingtimings.user_id_opponent = '".$schoolId."')";
								$condSchSGFb[] = "(Schedulingtimings.event_id = '".$studentgrprec->event_id."' AND Schedulingtimings.event_id_number = '".$studentgrprec->event_id_number."')";
								$schedulingStGrp = $this->Schedulingtimings->find()
									->where($condSchSGFb)
									->contain(["Events","Users","Opponentuser","Conventionrooms"])
									->order(["Schedulingtimings.sch_date_time" => "ASC"])
									->all();
							}

							foreach($schedulingStGrp as $schstudgrprec)
							{
								$arrSch		= array();
								$arrSch['sch_date_time']	= $schstudgrprec->sch_date_time;
								$arrSch['day']	= $schstudgrprec->day;
								$arrSch['start_time']	= $schstudgrprec->start_time!=NULL ? date("h:i A",strtotime($schstudgrprec->start_time)) : '';
								
								$arrSch['event_name']	= $schstudgrprec->Events['event_name'].' ('.$schstudgrprec->Events['event_id_number'].')';
								
								$arrSch['room_name']	= $schstudgrprec->Conventionrooms['room_name'].' (Group: '.$schstudgrprec->group_name.')';
								
								$arrSch['db_id']		= $schstudgrprec->id;
								$arrSch['is_bye']		= $schstudgrprec->is_bye;
								$arrSch['schedule_category'] = $schstudgrprec->schedule_category;
								$arrSch['round_number'] = $schstudgrprec->round_number;
								
								$arrStudentSchedule[] = $arrSch;
								
							} // end foreach($schedulingStGrp as $schstudgrprec)
					
					
						} //end foreach($studentGroups as $studentgrprec)
					}

					// Query 3: school-level events (no group_name) — e.g. team sports, athletics
					// Find all Crstudentevents for this student with empty group_name
					$condSNG = array();
					$condSNG[] = "(Crstudentevents.conventionseason_id = '".$conventionSeasonId."' AND Crstudentevents.convention_id = '".$conventionId."' AND Crstudentevents.season_id = '".$seasonId."' AND Crstudentevents.season_year = '".$seasonYear."')";
					$condSNG[] = "(Crstudentevents.student_id = '".$student_id_sorted."')";
					$condSNG[] = "(Crstudentevents.group_name = '' OR Crstudentevents.group_name IS NULL)";
					$studentNoGroupEvents = $this->Crstudentevents->find()
						->where($condSNG)
						->order(["Crstudentevents.id" => "ASC"])
						->all();

					if ($studentNoGroupEvents) {
						foreach ($studentNoGroupEvents as $ngEvent) {
							$condSchNG = array();
							$condSchNG[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSeasonId."' AND Schedulingtimings.convention_id = '".$conventionId."' AND Schedulingtimings.season_id = '".$seasonId."' AND Schedulingtimings.season_year = '".$seasonYear."')";
							$condSchNG[] = "(Schedulingtimings.user_id = '".$schoolId."' OR Schedulingtimings.user_id_opponent = '".$schoolId."')";
							$condSchNG[] = "(Schedulingtimings.event_id = '".$ngEvent->event_id."' AND Schedulingtimings.event_id_number = '".$ngEvent->event_id_number."')";
							$condSchNG[] = "(Schedulingtimings.group_name = '' OR Schedulingtimings.group_name IS NULL)";

							$schedulingNoGrp = $this->Schedulingtimings->find()
								->where($condSchNG)
								->contain(["Events","Users","Opponentuser","Conventionrooms"])
								->order(["Schedulingtimings.sch_date_time" => "ASC"])
								->all();

							foreach ($schedulingNoGrp as $ngRec) {
								// Avoid duplicates already added by query 1
								$alreadyAdded = false;
								foreach ($arrStudentSchedule as $existing) {
									if ((int)($existing['db_id'] ?? 0) === (int)$ngRec->id) {
										$alreadyAdded = true;
										break;
									}
								}
								if ($alreadyAdded) continue;

								$arrSch = array();
								$arrSch['sch_date_time']   = $ngRec->sch_date_time;
								$arrSch['day']             = $ngRec->day;
								$arrSch['start_time']      = $ngRec->start_time != NULL ? date("h:i A", strtotime($ngRec->start_time)) : '';
								$arrSch['event_name']      = $ngRec->Events['event_name'].' ('.$ngRec->Events['event_id_number'].')';
								$arrSch['room_name']       = $ngRec->Conventionrooms['room_name'];
								$arrSch['db_id']           = $ngRec->id;
								$arrSch['is_bye']          = $ngRec->is_bye;
								$arrSch['schedule_category'] = $ngRec->schedule_category;
								$arrSch['round_number']    = $ngRec->round_number;
								$arrStudentSchedule[] = $arrSch;
							}
						}
					}

					usort($arrStudentSchedule, function ($a, $b) {
						return $a['sch_date_time'] <=> $b['sch_date_time'];
					});

					// Collect projected path rows into the array so they sort correctly
					$projectedCollected = array();
					foreach ($arrStudentSchedule as $studentsch) {
						if (
							((int)($studentsch['schedule_category'] ?? 0) === 2 || (int)($studentsch['schedule_category'] ?? 0) === 3)
							&& (int)($studentsch['round_number'] ?? 0) === 1
						) {
							$nextBaseId = (int)$studentsch['db_id'];
							for ($projStep = 1; $projStep <= 2; $projStep++) {
								$condNext = array();
								$condNext[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSeasonId."' AND Schedulingtimings.convention_id = '".$conventionId."' AND Schedulingtimings.season_id = '".$seasonId."' AND Schedulingtimings.season_year = '".$seasonYear."')";
								$condNext[] = "(Schedulingtimings.schtimeautoid1 = '".$nextBaseId."' OR Schedulingtimings.schtimeautoid2 = '".$nextBaseId."')";
								$nextMatch = $this->Schedulingtimings->find()
									->where($condNext)
									->contain(["Events","Conventionrooms"])
									->order(["Schedulingtimings.round_number" => "ASC", "Schedulingtimings.match_number" => "ASC", "Schedulingtimings.id" => "ASC"])
									->first();
								if (!$nextMatch) break;
								if (isset($projectedCollected[(int)$nextMatch->id])) {
									$nextBaseId = (int)$nextMatch->id;
									continue;
								}
								$projectedCollected[(int)$nextMatch->id] = 1;
								$arrSch = array();
								$arrSch['sch_date_time']     = $nextMatch->sch_date_time;
								$arrSch['day']               = !empty($nextMatch->day) ? $nextMatch->day : 'TBD';
								$arrSch['start_time']        = $nextMatch->start_time != NULL ? date("h:i A", strtotime($nextMatch->start_time)) : 'TBD';
								$arrSch['event_name']        = $nextMatch->Events['event_name'].' ('.$nextMatch->Events['event_id_number'].') - '.($projStep === 1 ? 'If Win' : 'If Win Again').': Match-'.$nextMatch->match_number;
								$arrSch['room_name']         = !empty($nextMatch->Conventionrooms['room_name']) ? $nextMatch->Conventionrooms['room_name'] : 'TBD';
								$arrSch['db_id']             = $nextMatch->id;
								$arrSch['is_bye']            = 0;
								$arrSch['schedule_category'] = $nextMatch->schedule_category;
								$arrSch['round_number']      = $nextMatch->round_number;
								$arrSch['is_projected']      = true;
								$arrStudentSchedule[] = $arrSch;
								$nextBaseId = (int)$nextMatch->id;
							}
						}
					}

					// Re-sort with projected rows in correct chronological position
					usort($arrStudentSchedule, function ($a, $b) {
						return $a['sch_date_time'] <=> $b['sch_date_time'];
					});

					// Now render
					foreach($arrStudentSchedule as $studentsch)
					{
						if($studentsch['is_bye'] != 1)
						{
							if (!empty($studentsch['is_projected'])) {
					?>
						<tr class="projected-path-row" style="background:#ffffcc;">
							<td data-title="Day" width="15%"><?php echo $studentsch['day'];?></td>
							<td data-title="Start" width="15%"><?php echo $studentsch['start_time'];?></td>
							<td data-title="Event" width="35%"><?php echo $studentsch['event_name'];?><br><span style="color:#b9770e; font-size: 12px;">Projected path</span></td>
							<td data-title="Location" width="35%"><?php echo $studentsch['room_name'];?></td>
						</tr>
					<?php
							} else {
					?>
						<tr>
							<td data-title="Day" width="15%"><?php echo $studentsch['day'];?></td>
							<td data-title="Start" width="15%"><?php echo $studentsch['start_time'];?></td>
							<td data-title="Event" width="35%"><?php echo $studentsch['event_name'];?></td>
							<td data-title="Location" width="35%"><?php echo $studentsch['room_name'];?></td>
						</tr>
					<?php
							}
						}
					}
					
					?>
					
                </table>
            </div>
			<?php
			$studentIndex++;
			// Insert a page break after every 2nd student (but not after the last)
			if ($studentIndex % 2 === 0 && $studentIndex < $studentCount) { ?>
				<div class="page-break-after"></div>
			<?php } elseif ($studentIndex % 2 === 1 && $studentIndex < $studentCount) { ?>
				<hr class="student-divider">
			<?php } ?>
			
			<?php }?>
        </section>

         
        
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>