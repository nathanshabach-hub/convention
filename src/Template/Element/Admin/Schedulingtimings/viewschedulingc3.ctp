<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Schedulingtimings = TableRegistry::get('Schedulingtimings');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$schedulingTimingsList->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				Schedule Category :: 3 (Needs Schedule=Yes || Group Event=Yes || Event Kind ID=Elimination || Has To Be Consecutive=No)
				</div>  
            </div> 

            <div class="tbl-resp-listing">
				<table id="convention_events" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#DB ID</th>
                            <th class="sorting_paging">Room</th>
                            <th class="sorting_paging">Day</th>
                            <th class="sorting_paging">Start</th>
							<th class="sorting_paging">Finish</th>
							<th class="sorting_paging">Event</th>
							<th class="sorting_paging">Round</th>
							<th class="sorting_paging">Match No.</th>
							<th class="sorting_paging">Match</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						$byTimingId = [];
						foreach ($schedulingTimingsList as $timingRow) {
							$byTimingId[(int)$timingRow->id] = $timingRow;
						}

						$winnerLabelForMatch = function ($matchRow) {
							if (!$matchRow) {
								return '(Winner of Match-?)';
							}

							$matchNo = !empty($matchRow->match_number) ? $matchRow->match_number : '?';
							if ((int)$matchRow->is_bye !== 1) {
								return '(Winner of Match-'.$matchNo.')';
							}

							$userName = (!empty($matchRow->Users) && !empty($matchRow->Users->first_name)) ? $matchRow->Users->first_name : 'Unknown';
							$groupName = !empty($matchRow->group_name) ? $matchRow->group_name : '?';
							return $userName.' (Group-'.$groupName.')';
						};


						foreach ($schedulingTimingsList as $datarecord)
						{	
							$roomName = (!empty($datarecord->Conventionrooms) && !empty($datarecord->Conventionrooms->room_name)) ? $datarecord->Conventionrooms->room_name : '';
							$eventName = (!empty($datarecord->Events) && !empty($datarecord->Events->event_name)) ? $datarecord->Events->event_name : '';
							$eventNumber = (!empty($datarecord->Events) && !empty($datarecord->Events->event_id_number)) ? $datarecord->Events->event_id_number : '';
							$userFirstName = (!empty($datarecord->Users) && !empty($datarecord->Users->first_name)) ? $datarecord->Users->first_name : 'Unknown';
							$opponentFirstName = (!empty($datarecord->Opponentuser) && !empty($datarecord->Opponentuser->first_name)) ? $datarecord->Opponentuser->first_name : 'TBD';
						?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="DB ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Room"><?php echo $roomName;?></td>
                                <td data-title="Day"><?php echo $datarecord->day;?></td>
                                <td data-title="Start">
								<?php 
								echo $datarecord->start_time != NULL
									? (
										(is_object($datarecord->start_time) && method_exists($datarecord->start_time, 'format'))
											? $datarecord->start_time->format('H:i A')
											: date("H:i A", strtotime((string)$datarecord->start_time))
									)
									: '';
								?>
								</td>
                                <td data-title="Finish">
								<?php 
								echo $datarecord->finish_time != NULL
									? (
										(is_object($datarecord->finish_time) && method_exists($datarecord->finish_time, 'format'))
											? $datarecord->finish_time->format('H:i A')
											: date("H:i A", strtotime((string)$datarecord->finish_time))
									)
									: '';
								?>
								</td>
	                                <td data-title="Event"><?php echo $eventName;?> (<?php echo $eventNumber;?>)</td>
								<td data-title="Round No.">Round-<?php echo $datarecord->round_number;?></td>
								<td data-title="Match No.">Match-<?php echo $datarecord->match_number;?></td>
                                <td data-title="Match">
								<?php
								
								if($datarecord->round_number > 1)
								{
									// to get match details
									$matchOneId = (int)$datarecord->schtimeautoid1;
									$matchTwoId = (int)$datarecord->schtimeautoid2;
									$matchOneD = $matchOneId > 0 && isset($byTimingId[$matchOneId]) ? $byTimingId[$matchOneId] : $this->Schedulingtimings->find()->contain(["Users"])->where(["Schedulingtimings.id" => $matchOneId])->first();
									$matchTwoD = $matchTwoId > 0 && isset($byTimingId[$matchTwoId]) ? $byTimingId[$matchTwoId] : $this->Schedulingtimings->find()->contain(["Users"])->where(["Schedulingtimings.id" => $matchTwoId])->first();

									echo $winnerLabelForMatch($matchOneD);
									echo ' <b>VS</b> ';
									echo $winnerLabelForMatch($matchTwoD);
								}
								else
								{
									if($datarecord->user_id>0 && ($datarecord->user_id_opponent == 0 || $datarecord->user_id_opponent == NULL))
									{
										echo $userFirstName.' (Group-'.$datarecord->group_name.')(<b>BYE</b>)';
									}
									else
									{
										echo $userFirstName.' (Group-'.$datarecord->group_name.')';
										echo ' <b>VS</b> ';
										echo $opponentFirstName.'(Group-'.$datarecord->group_name_opponent.')';
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
	order: [],
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
</style>