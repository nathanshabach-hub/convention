<?php
use Cake\ORM\TableRegistry;
$this->Divisions = TableRegistry::get('Divisions');
$this->Users = TableRegistry::get('Users');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php //if (!$conventionseasonevents->isEmpty()) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

        <?php if (!empty($award247Eligible)): ?>
        <section class="lstng-section" style="margin-bottom:30px;">
            <div class="topn"><div class="topn_left">🏅 Southern Cross Educational Enterprises 24/7 Award — Eligible Students</div></div>
            <p style="font-size:12px;color:#555;margin:6px 0 12px;">
                Must meet all: Bible Memorization placement · Individual placement in Academic, Physical Education, Music &amp; Platform · ≥2 placed Exhibits events · ≥2 team event entries.<br>
                Winner = highest total points. In case of tie, count-back applies (Scripture awards → individual places → team places).
            </p>
            <?php
                $winnerIds = !empty($award247WinnerIds) ? array_map('intval', $award247WinnerIds) : [];
                $winnerLookup = array_fill_keys($winnerIds, true);
                $winnerLabel = count($winnerIds) > 1 ? 'Winner (Director Decision Required)' : 'Winner';
            ?>
            <div class="tbl-resp-listing">
                <table class="table table-bordered table-condensed" style="font-size:13px;">
                    <thead style="background:#1c2452;color:#fff;">
                        <tr>
                            <th>#</th><th>Student</th><th>School</th><th>Total Pts</th>
                            <th>Result</th>
                            <th title="Bible Memorization placement">📖 Bible</th>
                            <th title="Academic individual placement">Acad</th>
                            <th title="Phys Ed individual placement">PhysEd</th>
                            <th title="Music individual placement">Music</th>
                            <th title="Platform individual placement">Platform</th>
                            <th title="2+ Exhibits placements">Exhibits</th>
                            <th title="2+ team event entries">Teams</th>
                            <th>Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rank = 1;
                    foreach ($award247Eligible as $sid => $pts):
                        $studentD = $this->Users->find()->where(['Users.id' => $sid])->contain(['Schools'])->first();
                        if (!$studentD) continue;
                        $name   = h(trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name));
                        $school = h($studentD->Schools['first_name'] ?? '-');
                        $c = $award247Criteria[$sid] ?? [];
                        $isWinner = !empty($winnerLookup[(int)$sid]);
                        $tick  = '<span style="color:#27ae60;font-weight:700;">&#10003;</span>';
                        $cross = '<span style="color:#c0392b;font-weight:700;">&#10007;</span>';
                    ?>
                        <tr style="background:<?php echo $isWinner ? '#f0f7f0' : '#fff'; ?>">
                            <td style="font-weight:700;"><?php echo $rank; ?></td>
                            <td><?php echo $name; ?></td>
                            <td><?php echo $school; ?></td>
                            <td style="font-weight:700;color:#1c2452;"><?php echo $pts; ?></td>
                            <td style="text-align:center;">
                                <?php if($isWinner): ?>
                                    <span style="background:#27ae60;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;"><?php echo h($winnerLabel); ?></span>
                                <?php else: ?>
                                    <span style="color:#aaa;font-size:11px;">&#8212;</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;"><?php echo !empty($c['bible'])    ? $tick : $cross; ?></td>
                            <td style="text-align:center;"><?php echo !empty($c['academic']) ? $tick : $cross; ?></td>
                            <td style="text-align:center;"><?php echo !empty($c['physed'])   ? $tick : $cross; ?></td>
                            <td style="text-align:center;"><?php echo !empty($c['music'])    ? $tick : $cross; ?></td>
                            <td style="text-align:center;"><?php echo !empty($c['platform']) ? $tick : $cross; ?></td>
                            <td style="text-align:center;"><?php $ex = $c['exhibits'] ?? 0; echo ($ex >= 2 ? $tick : $cross).'('.$ex.')'; ?></td>
                            <td style="text-align:center;"><?php $tm = $c['teams'] ?? 0; echo ($tm >= 2 ? $tick : $cross).'('.$tm.')'; ?></td>
                            <td>
                                <?php echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>', ['controller' => 'results', 'action' => 'certificate24by7pdf',$slug_convention_season,$studentD->slug,$pts], ['escape' => false, 'title' => 'Generate 24/7 Certificate', 'target'=>'_blank']); ?>
                                <?php echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'results', 'action' => 'certificate24by7plainprint',$slug_convention_season,$studentD->slug,$pts], ['escape' => false, 'title' => 'Generate 24/7 Plain Print Certificate', 'target'=>'_blank', 'style' => 'margin-left:8px;']); ?>
                            </td>
                        </tr>
                    <?php
                        $rank++;
                    endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php else: ?>
        <section class="lstng-section" style="margin-bottom:30px;">
            <div class="topn"><div class="topn_left">🏅 Southern Cross Educational Enterprises 24/7 Award</div></div>
            <p style="font-size:12px;color:#555;margin:10px 0 0;">
                No student currently meets all 24/7 award criteria for this convention season.
            </p>
        </section>
        <?php endif; ?>

        <?php echo $this->Form->create(NULL, ['id' => 'addresults', 'type' => 'file', 'class' => ' ']); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn"><div class="topn_left"> View Overall Points</div></div>

            <div class="tbl-resp-listing">
                <table id="results_table" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Points</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Student Name</th>
                            <th class="sorting_paging">Gender</th>
                            <th class="sorting_paging">Events Placed</th>
                            <th class="sorting_paging">24/7 Eligible?</th>
                            <th class="sorting_paging">24/7 Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($arrAllResults as $student_id => $pointsS): ?>
                            <?php $studentD = $this->Users->find()->where(["Users.id" => $student_id])->contain(['Schools'])->first(); ?>
                            <?php
                                $eligible247 = isset($award247Eligible[$student_id]);
                                $isOverallWinner = !empty($maxKeys) && in_array((int)$student_id, array_map('intval', (array)$maxKeys), true);
                                $show247Certificate = $eligible247;
                                $studentPlacements = $eventsPlacedByStudent[$student_id] ?? [];
                            ?>
                            <tr>
                                <td data-title="Points"><?php echo $pointsS; ?></td>
                                <td data-title="School"><?php echo $studentD->Schools['first_name']; ?></td>
                                <td data-title="Student Name"><?php echo $studentD->first_name; ?> <?php echo $studentD->middle_name; ?> <?php echo $studentD->last_name; ?> (#<?php echo $student_id; ?>)</td>
                                <td data-title="Gender"><?php echo $studentD->gender; ?></td>
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
                                <?php
                                    $eligibleSort = 0;
                                    if ($eligible247) {
                                        $eligibleSort = 2;
                                    }
                                ?>
                                <td data-title="24/7 Eligible?" data-order="<?php echo $eligibleSort; ?>" style="text-align:center;">
                                    <?php if($eligible247): ?>
                                        <span style="background:#27ae60;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;">&#10003; Eligible</span>
                                    <?php else: ?>
                                        <span style="color:#aaa;font-size:11px;">&#8212;</span>
                                    <?php endif; ?>
                                </td>
                                <td data-title="24/7 Certificate">
                                    <?php if($show247Certificate): ?>
                                        <?php echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>', ['controller' => 'results', 'action' => 'certificate24by7pdf',$slug_convention_season,$studentD->slug,$pointsS], ['escape' => false, 'title' => 'Generate 24/7 Certificate', 'target'=>'_blank']); ?>
                                        <?php echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'results', 'action' => 'certificate24by7plainprint',$slug_convention_season,$studentD->slug,$pointsS], ['escape' => false, 'title' => 'Generate 24/7 Plain Print Certificate', 'target'=>'_blank', 'style' => 'margin-left:8px;']); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php echo $this->Form->end(); ?>

    </div>
<?php //} else { ?>
<?php //} ?>

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
    order: [[5, 'desc'], [0, 'desc'], [2, 'asc']],
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
});
</script>

<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
    .page-link { color: #1c2452 !important; background-color: #fff !important; }
    .active>.page-link { background-color: #1c2452 !important; border-color: #1c2452 !important; color: #fff !important; }
    .pagination { border-radius: 0rem !important; }
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
