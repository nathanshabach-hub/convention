<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Users = TableRegistry::get('Users');
$this->Resultpositions = TableRegistry::get('Resultpositions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$showSearch = isset($showSearch) ? (bool)$showSearch : true;
?>
<script>
  $(document).ready(function () {
    $('#searchInput').on('keyup', function () {
      const value = $.trim($(this).val().toLowerCase());
      $('#clearSearch').toggle(value.length > 0);

      $('#results_table_view tbody tr.event-header').each(function () {
        const $header = $(this);
        const eventKey = $header.data('eventKey');
        const headerText = ($header.data('search') || $header.text() || '').toString().toLowerCase();
        const $rows = $('#results_table_view tbody tr.event-entry[data-event-key="' + eventKey + '"]');

        let anyRowMatches = false;
        $rows.each(function () {
          const rowText = ($(this).data('search') || $(this).text() || '').toString().toLowerCase();
          const isMatch = value === '' || rowText.indexOf(value) > -1;
          $(this).toggle(isMatch);
          if (isMatch) {
            anyRowMatches = true;
          }
        });

        const headerMatches = value === '' || headerText.indexOf(value) > -1;
        $header.toggle(headerMatches || anyRowMatches);
      });
    });

    $('#clearSearch').on('click', function () {
      $('#searchInput').val('').keyup();
      $(this).hide();
    });
  });
</script>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php //if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(NULL, ['id' => 'addresults', 'type' => 'file', 'class' => ' ']); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Overall Winners - <?php echo $conventionD->name; ?> <?php echo $conventionSD->season_year; ?></div>
            </div>   

            <div class="tbl-resp-listing">
				<?php if ($showSearch) { ?>
        <div class="search-container overallpositions-search-container">
          <input type="text" id="searchInput" class="form-control" placeholder="Search event, student/group or school...">
          <!--<span class="clear-icon" id="clearSearch">&times;</span>-->
        </div>
				<?php } ?>
				
        <table id="results_table_view" class="table table-bordered table-condensed cf overallpositions-table">
                     
					
					<?php
					if(count($arrConvSeasonEvent)>0)
					{
						// now get events list
						$arrConvSeasonEventImplode = implode(",",$arrConvSeasonEvent);
						$condEvents = array();
						$condEvents[] = "(Events.id IN ($arrConvSeasonEventImplode) )";
            $eventOrderSeedVal = isset($eventOrderSeed) ? (int)$eventOrderSeed : 0;
              $choirEventId = 871;
            if ($eventOrderSeedVal > 0) {
                $events = $this->Events->find()->where($condEvents)->order('CASE WHEN Events.id = '.$choirEventId.' THEN 1 ELSE 0 END ASC, RAND('.$eventOrderSeedVal.')')->all();
            } else {
                $events = $this->Events->find()->where($condEvents)->order('CASE WHEN Events.id = '.$choirEventId.' THEN 1 ELSE 0 END ASC, rand()')->all();
            }
					?>
					
                    <tbody>
                        <?php
						foreach($events as $event)
						{
							// to check position
              $countpositions = $this->Resultpositions->find()->where(["Resultpositions.conventionseason_id" => $conventionSD->id,"Resultpositions.event_id" => $event->id,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 3])->order(["Resultpositions.position" => "ASC"])->count();
							//print_r($overallpositions[0]->id>0);
              if($countpositions>0)
							{
          echo '<tr class="event-header" data-event-key="'.$event->id.'" data-search="'.h(strtolower($event->event_name.' '.$event->event_id_number)).'"><td colspan="3" class="overallpositions-event-title">'.$event->event_name.' ('.$event->event_id_number.')</td></tr>';
								
                $overallpositions = $this->Resultpositions->find()->where(["Resultpositions.conventionseason_id" => $conventionSD->id,"Resultpositions.event_id" => $event->id,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 3])->order(["Resultpositions.position" => "DESC"])->contain(['Users'])->all();
								
								foreach($overallpositions as $ovpos)
								{
									$showName 			= '';
									
									if($ovpos->student_id>0)
									{
										$studentD = $this->Users->find()->where(["Users.id" => $ovpos->student_id])->contain(['Schools'])->first();
										$showName = $studentD->first_name.' '.$studentD->last_name;
									}
									else
									if(!empty($ovpos->group_name))
									{
										$arrGrpStudent = array();
										$groupstudents = $this->Crstudentevents->find()->where(["Crstudentevents.conventionseason_id" => $conventionSD->id,"Crstudentevents.event_id" => $event->id,"Crstudentevents.group_name " => $ovpos->group_name,"Crstudentevents.user_id " => $ovpos->user_id])->order(["Crstudentevents.id" => "ASC"])->all();
										foreach($groupstudents as $grpstudent)
										{
											$studentDG = $this->Users->find()->where(["Users.id" => $grpstudent->student_id])->contain(['Schools'])->first();
											$grpStName = $studentDG->first_name.' '.$studentDG->last_name;
											
											$arrGrpStudent[] = $grpStName;
										}
										
										if(count($arrGrpStudent)>0)
										{
											$showName = implode(", ",$arrGrpStudent);
										}
									}
									
									
							
						?> 
              <?php $searchIndex = strtolower(trim($showName.' '.$ovpos->Users['first_name'].' '.$event->event_name.' '.$event->event_id_number)); ?>
              <tr class="event-entry" data-event-key="<?php echo $event->id; ?>" data-search="<?php echo h($searchIndex); ?>">
								<td data-title="Position" width="5%"><?php echo $ovpos->position;?></td>
                                <td data-title="Student / Group" width="45%"><?php echo $showName;?></td>
								<td data-title="School" width="50%"><?php echo $ovpos->Users['first_name'];?> </td>
                            </tr>
							
                        <?php
							}
							}
						}
						?>
						
							
                    </tbody>
					<?php
					}
					?>
					
					
                </table>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php //} else { ?> 
<?php //}
?>

<script>
$(document).ready(function() {
$('#results_table').dataTable({
    "bPaginate": true,
    "bInfo": false,
    "bLengthChange": false,
	"pageLength": 100,
	order: [[0, 'desc'],[2, 'asc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#results_table').dataTable.search(this.value).draw();
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