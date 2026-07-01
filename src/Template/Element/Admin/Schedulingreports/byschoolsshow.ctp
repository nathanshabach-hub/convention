<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::get('Schedulingtimings');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($schedulingTimingsList) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				<h4><?php echo $schoolD->first_name; ?></h4>
				</div>  
            </div>   
			
			
            <div class="tbl-resp-listing">
                <table id="report_by_school_student" class="table table-bordered table-striped table-condensed cf">
                    
					<tr>
						<th class="sorting_paging" width="15%">Day</th>
						<th class="sorting_paging" width="15%">Start</th>
						<th class="sorting_paging" width="20%">Event</th>
						<th class="sorting_paging" width="20%">Location</th>
						<th class="sorting_paging" width="20%">Match</th>
					</tr>
					<?php
					// Collect all records into an array first so we can append projected path rows
					$arrSchoolSchedule = [];
					$projectedCollected = [];
					foreach ($schedulingTimingsList as $datarecord)
					{
						$arrSch = [];
						$arrSch['sch_date_time']      = $datarecord->sch_date_time;
						$arrSch['day']                = $datarecord->day;
						$arrSch['start_time']         = $datarecord->start_time != NULL ? date("h:i A", strtotime($datarecord->start_time)) : '';
						$arrSch['event_name']         = $datarecord->Events['event_name'].' ('.$datarecord->Events['event_id_number'].')';
						$arrSch['room_name']          = $datarecord->Conventionrooms['room_name'];
						$arrSch['db_id']              = $datarecord->id;
						$arrSch['is_bye']             = $datarecord->is_bye;
						$arrSch['schedule_category']  = $datarecord->schedule_category;
						$arrSch['round_number']       = $datarecord->round_number;
						$arrSch['match_number']       = $datarecord->match_number;
						$arrSch['group_name']         = $datarecord->group_name;
						$arrSch['group_name_opponent']= $datarecord->group_name_opponent;
						$arrSch['user_id']            = $datarecord->user_id;
						$arrSch['user_id_opponent']   = $datarecord->user_id_opponent;
						$arrSch['schtimeautoid1']     = $datarecord->schtimeautoid1;
						$arrSch['schtimeautoid2']     = $datarecord->schtimeautoid2;
						$arrSch['Users']              = $datarecord->Users;
						$arrSch['Opponentuser']       = $datarecord->Opponentuser;
						$arrSch['is_projected']       = false;
						$arrSchoolSchedule[] = $arrSch;
					}

					// Append projected path rows for elimination events (category 2 or 3)
					foreach ($arrSchoolSchedule as $studentsch)
					{
						if (
							!empty($studentsch['is_bye']) ||
							!in_array($studentsch['schedule_category'], [2, 3])
						) continue;

						$nextBaseId = (int)$studentsch['db_id'];
						for ($projStep = 1; $projStep <= 2; $projStep++) {
							$condNext = [];
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
							$arrSch = [];
							$arrSch['sch_date_time']     = $nextMatch->sch_date_time;
							$arrSch['day']               = !empty($nextMatch->day) ? $nextMatch->day : 'TBD';
							$arrSch['start_time']        = $nextMatch->start_time != NULL ? date("h:i A", strtotime($nextMatch->start_time)) : 'TBD';
							$arrSch['event_name']        = $nextMatch->Events['event_name'].' ('.$nextMatch->Events['event_id_number'].')';
							$arrSch['room_name']         = !empty($nextMatch->Conventionrooms['room_name']) ? $nextMatch->Conventionrooms['room_name'] : 'TBD';
							$arrSch['db_id']             = $nextMatch->id;
							$arrSch['is_bye']            = 0;
							$arrSch['schedule_category'] = $nextMatch->schedule_category;
							$arrSch['round_number']      = $nextMatch->round_number;
							$arrSch['match_number']      = $nextMatch->match_number;
							$arrSch['group_name']        = '';
							$arrSch['group_name_opponent']= '';
							$arrSch['user_id']           = 0;
							$arrSch['user_id_opponent']  = 0;
							$arrSch['schtimeautoid1']    = $nextMatch->schtimeautoid1;
							$arrSch['schtimeautoid2']    = $nextMatch->schtimeautoid2;
							$arrSch['Users']             = null;
							$arrSch['Opponentuser']      = null;
							$arrSch['proj_label']        = ($projStep === 1 ? 'If Win' : 'If Win Again').': Match-'.$nextMatch->match_number;
							$arrSch['is_projected']      = true;
							$arrSchoolSchedule[] = $arrSch;
							$nextBaseId = (int)$nextMatch->id;
						}
					}

					// Sort chronologically
					usort($arrSchoolSchedule, function ($a, $b) {
						return $a['sch_date_time'] <=> $b['sch_date_time'];
					});

					// Render rows
					foreach ($arrSchoolSchedule as $datarecord)
					{
						if ($datarecord['is_bye'] == 1) continue;
						$rowStyle = !empty($datarecord['is_projected']) ? ' style="background:#ffffcc;"' : '';
					?>
						<tr<?php echo $rowStyle; ?>>
							<td data-title="Day" width="15%"><?php echo $datarecord['day'];?></td>
							<td data-title="Start" width="15%"><?php echo $datarecord['start_time'];?></td>
							<td data-title="Event" width="20%">
								<?php echo $datarecord['event_name'];?>
								<?php if (!empty($datarecord['is_projected'])): ?>
									<br><span style="color:#b9770e; font-size:12px;">Projected path</span>
								<?php endif; ?>
							</td>
							<td data-title="Location" width="20%"><?php echo $datarecord['room_name'];?></td>
							<td data-title="Match" width="20%">
							<?php
							if (!empty($datarecord['is_projected']))
							{
								echo $datarecord['proj_label'];
							}
							else if($datarecord['schedule_category'] == 1)
							{
								echo 'Group '.$datarecord['group_name'];
								echo ' (<b>'.$datarecord['Users']['first_name'].'</b>)';
							}
							else if($datarecord['schedule_category'] == 2)
							{
								echo 'Match-'.$datarecord['match_number'].': &nbsp;';
								if($datarecord['round_number'] > 1)
								{
									if (!empty($datarecord['schtimeautoid1']) && !empty($datarecord['schtimeautoid2'])) {
										$matchOneD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord['schtimeautoid1']])->first();
										$matchTwoD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord['schtimeautoid2']])->first();
										if ($matchOneD && $matchTwoD) {
											echo '(Winner of Match-'.$matchOneD->match_number.')';
											echo ' <b>VS</b> ';
											echo '(Winner of Match-'.$matchTwoD->match_number.')';
										}
									}
								}
								else
								{
									if($datarecord['user_id']>0 && ($datarecord['user_id_opponent'] == 0 || $datarecord['user_id_opponent'] == NULL))
									{
										echo $datarecord['Users']['first_name'].' '.$datarecord['Users']['middle_name'].' '.$datarecord['Users']['last_name'].' (<b>BYE</b>)';
									}
									else
									{
										echo $datarecord['Users']['first_name'].' '.$datarecord['Users']['middle_name'].' '.$datarecord['Users']['last_name'];
										echo ' <b>VS</b> ';
										echo $datarecord['Opponentuser']['first_name'].' '.$datarecord['Opponentuser']['middle_name'].' '.$datarecord['Opponentuser']['last_name'];
									}
								}
							}
							else if($datarecord['schedule_category'] == 3)
							{
								echo 'Match-'.$datarecord['match_number'].': &nbsp;';
								if($datarecord['round_number'] > 1)
								{
									if (!empty($datarecord['schtimeautoid1']) && !empty($datarecord['schtimeautoid2'])) {
										$matchOneD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord['schtimeautoid1']])->first();
										$matchTwoD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord['schtimeautoid2']])->first();
										if ($matchOneD && $matchTwoD) {
											echo '(Winner of Match-'.$matchOneD->match_number.')';
											echo ' <b>VS</b> ';
											echo '(Winner of Match-'.$matchTwoD->match_number.')';
										}
									}
								}
								else
								{
									if($datarecord['user_id']>0 && ($datarecord['user_id_opponent'] == 0 || $datarecord['user_id_opponent'] == NULL))
									{
										echo $datarecord['Users']['first_name'].' (Group-'.$datarecord['group_name'].')(<b>BYE</b>)';
									}
									else
									{
										echo $datarecord['Users']['first_name'].' (Group-'.$datarecord['group_name'].')';
										echo ' <b>VS</b> ';
										echo $datarecord['Opponentuser']['first_name'].'(Group-'.$datarecord['group_name_opponent'].')';
									}
								}
							}
							else if($datarecord['schedule_category'] == 4)
							{
								echo $datarecord['Users']['first_name'].' '.$datarecord['Users']['middle_name'].' '.$datarecord['Users']['last_name'];
							}
							?>
							</td>
						</tr>
					<?php }?>
					
                </table>
            </div>
			<div class="pagebreakafter"></div>
			
			
        </section>

         
        
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>