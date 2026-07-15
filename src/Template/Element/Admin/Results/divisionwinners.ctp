<?php
use Cake\ORM\TableRegistry;
$this->Divisions = TableRegistry::get('Divisions');
$this->Users = TableRegistry::get('Users');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php //if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(NULL, ['id' => 'addresults', 'type' => 'file', 'class' => ' ']); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left"> View Points By Divisions
				</div>  
            </div>   

            <div class="tbl-resp-listing">
                <table id="results_table" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Division</th>
							<th class="sorting_paging">Category</th>
							<th class="sorting_paging">Points</th>
							<th class="sorting_paging">School</th>
                            <th class="sorting_paging">Student Name</th>
                            <th class="sorting_paging">Gender</th>
                            <th class="sorting_paging">Events Placed</th>
                            <th class="sorting_paging">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach($divisions as $divisionrecord)
						{
                            $divId = (int)$divisionrecord->id;
                            if (empty($trophyWinners[$divId])) {
                                continue;
                            }

                            if (in_array($divId, $genderSplitDivs)) {
                                foreach (['Male', 'Female'] as $category) {
                                    if (empty($trophyWinners[$divId][$category]['students'])) {
                                        continue;
                                    }
                                    $winnerStudentId = (int)reset($trophyWinners[$divId][$category]['students']);
                                    $maxValue = (int)$trophyWinners[$divId][$category]['points'];
                                    $studentD = $this->Users->find()->where(["Users.id" => $winnerStudentId])->contain(['Schools'])->first();
                                    $studentPlacementsAll = $eventsPlacedByStudent[$winnerStudentId] ?? [];
                                    $studentPlacements = array_values(array_filter($studentPlacementsAll, function ($placement) use ($divId) {
                                        return (int)($placement['division_id'] ?? 0) === (int)$divId;
                                    }));
                                    if (!$studentD) {
                                        continue;
                                    }
                        ?>
                            <tr>
                                <td data-title="Division"><?php echo h($divisionrecord->name);?></td>
                                <td data-title="Category"><?php echo h($category);?></td>
                                <td data-title="Points"><?php echo $maxValue;?></td>
                                <td data-title="School"><?php echo h($studentD->Schools['first_name'] ?? '');?></td>
                                <td data-title="Student Name"><?php echo h(trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name));?> (#<?php echo $winnerStudentId;?>)</td>
                                <td data-title="Gender"><?php echo h($studentD->gender);?></td>
                                <td data-title="Events Placed" style="text-align:center;">
                                    <?php if(!empty($studentPlacements)): ?>
                                        <a href="javascript:void(0);"
                                           class="js-view-events-placed"
                                           title="View event placings"
                                           data-student-name="<?php echo h(trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name)); ?>"
                                           data-events='<?php echo h(json_encode($studentPlacements)); ?>'>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#aaa;font-size:11px;">&#8212;</span>
                                    <?php endif; ?>
                                </td>
                                <td data-title="Division Winner Certificate">
                                    <?php
                                    echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>', ['controller' => 'results', 'action' => 'divisionwinnercertificatepdf',$slug_convention_season,$divisionrecord->slug,$studentD->slug], [ 'escape' => false, 'title' => 'Generate Division Winner Certificate', 'target'=>'_blank']);
                                    echo ' ';
                                    echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'results', 'action' => 'divisionwinnercertificateplainprint',$slug_convention_season,$divisionrecord->slug,$studentD->slug], [ 'escape' => false, 'title' => 'Print Plain Certificate (No Background)', 'target'=>'_blank']);
                                    ?>
                                </td>
                            </tr>
                        <?php
                                }
                            } else {
                                $winnerStudentId = (int)reset($trophyWinners[$divId]['students']);
                                $maxValue = (int)$trophyWinners[$divId]['points'];
                                $studentD = $this->Users->find()->where(["Users.id" => $winnerStudentId])->contain(['Schools'])->first();
                                $studentPlacementsAll = $eventsPlacedByStudent[$winnerStudentId] ?? [];
                                $studentPlacements = array_values(array_filter($studentPlacementsAll, function ($placement) use ($divId) {
                                    return (int)($placement['division_id'] ?? 0) === (int)$divId;
                                }));
                                if (!$studentD) {
                                    continue;
                                }
                        ?>
                            <tr>
                                <td data-title="Division"><?php echo h($divisionrecord->name);?></td>
                                <td data-title="Category">Overall</td>
                                <td data-title="Points"><?php echo $maxValue;?></td>
                                <td data-title="School"><?php echo h($studentD->Schools['first_name'] ?? '');?></td>
                                <td data-title="Student Name"><?php echo h(trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name));?> (#<?php echo $winnerStudentId;?>)</td>
                                <td data-title="Gender"><?php echo h($studentD->gender);?></td>
                                <td data-title="Events Placed" style="text-align:center;">
                                    <?php if(!empty($studentPlacements)): ?>
                                        <a href="javascript:void(0);"
                                           class="js-view-events-placed"
                                           title="View event placings"
                                           data-student-name="<?php echo h(trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name)); ?>"
                                           data-events='<?php echo h(json_encode($studentPlacements)); ?>'>
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#aaa;font-size:11px;">&#8212;</span>
                                    <?php endif; ?>
                                </td>
                                <td data-title="Division Winner Certificate">
                                    <?php
                                    echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>', ['controller' => 'results', 'action' => 'divisionwinnercertificatepdf',$slug_convention_season,$divisionrecord->slug,$studentD->slug], [ 'escape' => false, 'title' => 'Generate Division Winner Certificate', 'target'=>'_blank']);
                                    echo ' ';
                                    echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'results', 'action' => 'divisionwinnercertificateplainprint',$slug_convention_season,$divisionrecord->slug,$studentD->slug], [ 'escape' => false, 'title' => 'Print Plain Certificate (No Background)', 'target'=>'_blank']);
                                    ?>
                                </td>
                            </tr>
                        <?php
                            }
						}
						?>
						
							
                    </tbody>
                </table>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php //} else { ?> 
<?php //}
?>

<div id="eventsPlacedOverlay" class="events-placed-overlay" style="display:none;">
    <div class="events-placed-modal">
        <div class="events-placed-header">
            <strong id="eventsPlacedTitle">Events Placed</strong>
            <button type="button" class="events-placed-close" id="eventsPlacedCloseBtn">&times;</button>
        </div>
        <div class="events-placed-body" id="eventsPlacedBody"></div>
    </div>
</div>

<script>
$(document).ready(function() {
$('#results_table').dataTable({
    "bPaginate": true,
    "bInfo": false,
    "bLengthChange": false,
	"pageLength": 100,
	order: [[0, 'asc'],[1, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});

    function positionLabel(place) {
        var n = parseInt(place, 10);
        if (isNaN(n) || n <= 0) {
            return '-';
        }
        if (n % 100 >= 11 && n % 100 <= 13) {
            return n + 'th';
        }
        switch (n % 10) {
            case 1: return n + 'st';
            case 2: return n + 'nd';
            case 3: return n + 'rd';
            default: return n + 'th';
        }
    }

    $(document).on('click', '.js-view-events-placed', function(e) {
        e.preventDefault();
        var eventsRaw = $(this).attr('data-events') || '[]';
        var studentName = $(this).attr('data-student-name') || 'Student';
        var events = [];
        try {
            events = JSON.parse(eventsRaw);
        } catch (err) {
            events = [];
        }

        var html = '';
        if (!events.length) {
            html = '<p style="margin:0;color:#666;">No placed events found.</p>';
        } else {
            var grouped = {};
            var categoryOrder = [];

            for (var i = 0; i < events.length; i++) {
                var item = events[i] || {};
                var category = (item.category || 'Other').toString();
                if (!grouped[category]) {
                    grouped[category] = [];
                    categoryOrder.push(category);
                }
                grouped[category].push(item);
            }

            for (var c = 0; c < categoryOrder.length; c++) {
                var categoryName = categoryOrder[c];
                var items = grouped[categoryName] || [];
                html += '<div class="events-category-title">' + $('<div/>').text(categoryName).html() + '</div>';
                html += '<table class="table table-bordered table-condensed events-category-table">';
                html += '<thead><tr><th>Event</th><th style="width:110px;">Placing</th></tr></thead><tbody>';

                for (var j = 0; j < items.length; j++) {
                    var groupedItem = items[j] || {};
                    var eventName = (groupedItem.event_name || '').toString();
                    var placing = positionLabel(groupedItem.position);
                    var teamTag = groupedItem.is_team ? ' <span style="color:#777;font-size:11px;">(Team)</span>' : '';
                    html += '<tr><td>' + $('<div/>').text(eventName).html() + teamTag + '</td><td>' + placing + '</td></tr>';
                }

                html += '</tbody></table>';
            }
        }

        $('#eventsPlacedTitle').text('Events Placed - ' + studentName);
        $('#eventsPlacedBody').html(html);
        $('#eventsPlacedOverlay').fadeIn(120);
    });

    $('#eventsPlacedCloseBtn, #eventsPlacedOverlay').on('click', function(e) {
        if (e.target.id === 'eventsPlacedOverlay' || e.target.id === 'eventsPlacedCloseBtn') {
            $('#eventsPlacedOverlay').fadeOut(120);
        }
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

    .events-placed-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 9999;
        padding: 30px 15px;
    }
    .events-placed-modal {
        max-width: 700px;
        margin: 0 auto;
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }
    .events-placed-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: #1c2452;
        color: #fff;
    }
    .events-placed-close {
        border: 0;
        background: transparent;
        color: #fff;
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
    }
    .events-placed-body {
        padding: 15px;
        max-height: 70vh;
        overflow-y: auto;
    }
    .events-category-title {
        margin: 14px 0 8px;
        font-weight: 700;
        color: #1c2452;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.4px;
    }
    .events-category-table {
        margin-bottom: 10px;
    }
    .events-category-title:first-child {
        margin-top: 0;
    }
</style>