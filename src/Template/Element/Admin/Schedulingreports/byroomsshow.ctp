<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::get('Schedulingtimings');
$this->Users = TableRegistry::get('Users');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($schedulingTimingsList) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				<h4><?php echo $roomD->room_name; ?></h4>
				</div>  
            </div>   
			
			
            <div class="tbl-resp-listing">
                <table id="report_by_school_student" class="table table-bordered table-striped table-condensed cf">
					<?php $schoolNameById = []; ?>
                    
					<tr>
						<th class="sorting_paging" width="10%">Day</th>
						<th class="sorting_paging" width="10%">Start</th>
						<th class="sorting_paging" width="10%">Finish</th>
						<th class="sorting_paging" width="20%">Event</th>
						<th class="sorting_paging" width="21%">Match</th>
						<th class="sorting_paging" width="29%">School</th>
					</tr>
					<?php
					// now fetch scheduling for each student
					foreach ($schedulingTimingsList as $datarecord)
					{	
					?> 
						<?php $isUnscheduled = empty($datarecord->day) || empty($datarecord->start_time) || empty($datarecord->finish_time); ?>
						<tr>
							<td data-title="Day"><?php echo !empty($datarecord->day) ? $datarecord->day : '<span style="color:#c0392b;font-weight:600;">Unscheduled</span>';?></td>
							<td data-title="Start">
							<?php 
							echo $datarecord->start_time!=NULL ? date("h:i A",strtotime($datarecord->start_time)) : '<span style="color:#c0392b;font-weight:600;">Unscheduled</span>';
							?>
							</td>
							<td data-title="Finish">
							<?php 
							echo $datarecord->finish_time!=NULL ? date("h:i A",strtotime($datarecord->finish_time)) : '<span style="color:#c0392b;font-weight:600;">Unscheduled</span>';
							?>
							</td>
							<td data-title="Event"><?php echo $datarecord->Events['event_name'];?> (<?php echo $datarecord->Events['event_id_number'];?>)</td>
							
							<td data-title="Match">
							<?php
							if($datarecord->schedule_category == 1)
							{
								echo 'Group '.$datarecord->group_name;
								echo ' (<b>'.$datarecord->Users['first_name'].'</b>)';
							}
							else
							if($datarecord->schedule_category == 2)
							{
								echo 'Match-'.$datarecord->match_number.': &nbsp;';
								if($datarecord->round_number > 1)
								{
									if (!empty($datarecord->schtimeautoid1) && !empty($datarecord->schtimeautoid2)) {
										$matchOneD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord->schtimeautoid1])->first();
										$matchTwoD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord->schtimeautoid2])->first();
										if ($matchOneD && $matchTwoD) {
											echo '(Winner of Match-'.$matchOneD->match_number.')';
											echo ' <b>VS</b> ';
											echo '(Winner of Match-'.$matchTwoD->match_number.')';
										}
									}
								}
								else
								{
									if($datarecord->user_id>0 && ($datarecord->user_id_opponent == 0 || $datarecord->user_id_opponent == NULL))
									{
										echo $datarecord->Users['first_name'].' '.$datarecord->Users['middle_name'].' '.$datarecord->Users['last_name'].' (<b>BYE</b>)';
									}
									else
									{
										echo $datarecord->Users['first_name'].' '.$datarecord->Users['middle_name'].' '.$datarecord->Users['last_name'];
										echo ' <b>VS</b> ';
										echo $datarecord->Opponentuser['first_name'].' '.$datarecord->Opponentuser['middle_name'].' '.$datarecord->Opponentuser['last_name'];
									}
								}
							}
							else
							if($datarecord->schedule_category == 3)
							{
								echo 'Match-'.$datarecord->match_number.': &nbsp;';
								if($datarecord->round_number > 1)
								{
									if (!empty($datarecord->schtimeautoid1) && !empty($datarecord->schtimeautoid2)) {
										$matchOneD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord->schtimeautoid1])->first();
										$matchTwoD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord->schtimeautoid2])->first();
										if ($matchOneD && $matchTwoD) {
											echo '(Winner of Match-'.$matchOneD->match_number.')';
											echo ' <b>VS</b> ';
											echo '(Winner of Match-'.$matchTwoD->match_number.')';
										}
									}
								}
								else
								{
									if($datarecord->user_id>0 && ($datarecord->user_id_opponent == 0 || $datarecord->user_id_opponent == NULL))
									{
										echo $datarecord->Users['first_name'].' (Group-'.$datarecord->group_name.')(<b>BYE</b>)';
									}
									else
									{
										echo $datarecord->Users['first_name'].' (Group-'.$datarecord->group_name.')';
										echo ' <b>VS</b> ';
										echo $datarecord->Opponentuser['first_name'].'(Group-'.$datarecord->group_name_opponent.')';
									}
								}
							}
							else
							if($datarecord->schedule_category == 4)
							{
								echo $datarecord->Users['first_name'].' '.$datarecord->Users['middle_name'].' '.$datarecord->Users['last_name'];
							}							
							?>
							</td>
							<td data-title="School">
							<?php 
							$getSchoolName = function ($schoolId) use (&$schoolNameById) {
								$schoolId = (int)$schoolId;
								if ($schoolId <= 0) {
									return '';
								}
								if (!array_key_exists($schoolId, $schoolNameById)) {
									$schoolD = $this->Users->find()->where(["Users.id" => $schoolId])->select('first_name')->first();
									$schoolNameById[$schoolId] = $schoolD ? (string)$schoolD->first_name : '';
								}
								return $schoolNameById[$schoolId];
							};

							$eventName = strtolower((string)($datarecord->Events['event_name'] ?? ''));
							$isSinglesMatch = (strpos($eventName, 'singles') !== false)
								&& ((int)($datarecord->user_id ?? 0) > 0)
								&& ((int)($datarecord->user_id_opponent ?? 0) > 0);

							if ($isSinglesMatch) {
								$firstSchool = $getSchoolName($datarecord->Users['school_id'] ?? 0);
								$opponentSchool = $getSchoolName($datarecord->Opponentuser['school_id'] ?? 0);
								if ($firstSchool !== '' && $opponentSchool !== '') {
									echo $firstSchool . ' <b>VS</b> ' . $opponentSchool;
								} else {
									echo $firstSchool !== '' ? $firstSchool : $opponentSchool;
								}
							} else {
								echo $getSchoolName($datarecord->Users['school_id'] ?? 0);
							}
							
							?></td>
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