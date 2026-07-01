<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.jl-metric {
    border-radius: 4px;
    padding: 10px 12px;
    color: #fff;
    margin-bottom: 12px;
}
.jl-metric h3 { margin: 0; font-size: 24px; font-weight: 700; }
.jl-metric p  { margin: 2px 0 0 0; font-size: 13px; }
.jl-metric.blue   { background: #2980b9; }
.jl-metric.green  { background: #27ae60; }
.jl-metric.orange { background: #e67e22; }
.jl-metric.red    { background: #c0392b; }
.jl-note {
    border-left: 4px solid #3498db;
    background: #f3f8fc;
    padding: 10px 12px;
    border-radius: 3px;
    margin-bottom: 14px;
    font-size: 13px;
}
.jl-preferred { font-size: 12px; color: #888; margin-top: 3px; }
.jl-badge {
    display: inline-block;
    font-size: 11px;
    padding: 1px 7px;
    border-radius: 10px;
    font-weight: 600;
    margin-left: 4px;
}
.jl-badge.ok   { background: #d5f5e3; color: #1e8449; }
.jl-badge.warn { background: #fdebd0; color: #b9770e; }
.jl-badge.bad  { background: #fce4e4; color: #c0392b; }
#judging_list_table select { min-width: 160px; }

#judging_list_table tbody tr.jl-row-three > td {
    background: #dff0d8 !important;
}

#judging_list_table tbody tr.jl-row-two > td {
    background: #fdebd0 !important;
}

#judging_list_table tbody tr.jl-row-one > td {
    background: #ececec !important;
}

.jl-workload-wrap {
    border: 1px solid #e6e9ef;
    border-radius: 6px;
    overflow: hidden;
}

#workload_table {
    margin-bottom: 0;
}

#workload_table th,
#workload_table td {
    vertical-align: middle !important;
}

#workload_table .wl-rank {
    width: 50px;
    text-align: center;
    color: #777;
    font-weight: 600;
}

#workload_table .wl-count {
    width: 90px;
    text-align: center;
    font-weight: 700;
}

#workload_table .wl-meter {
    width: 38%;
}

.jl-load-track {
    background: #edf1f5;
    border-radius: 999px;
    height: 10px;
    overflow: hidden;
    width: 100%;
}

.jl-load-fill {
    display: block;
    height: 100%;
    transition: width .2s ease;
}

.jl-load-fill.success { background: #27ae60; }
.jl-load-fill.warning { background: #f39c12; }
.jl-load-fill.danger  { background: #e74c3c; }
.jl-load-fill.default { background: #95a5a6; }

.wl-balance {
    width: 130px;
    text-align: center;
    font-weight: 700;
}

.wl-balance.pos { color: #c0392b; }
.wl-balance.neg { color: #1e8449; }
.wl-balance.neu { color: #7f8c8d; }

.jl-workload-note {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Judging List
            <small><?php echo h($convSeasonD->slug); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]); ?></li>
            <li class="active">Judging List</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-check-square-o"></i>&nbsp; Judging Panel Assignments</h3>
                <div class="box-tools pull-right">
                    <?php echo $this->Html->link('<i class="fa fa-download"></i> Export CSV', ['controller'=>'conventionregistrations', 'action'=>'judginglistcsv'], ['class'=>'btn btn-sm btn-success', 'escape'=>false]); ?>
                    &nbsp;
                    <?php echo $this->Html->link('<i class="fa fa-user"></i> Manage Judges', ['controller'=>'conventionregistrations', 'action'=>'alljudges'], ['class'=>'btn btn-sm btn-default', 'escape'=>false]); ?>
                </div>
            </div>
            <div class="panel-body" style="padding:14px 14px 0;">
                <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

                <div class="jl-note">
                    Assign up to 3 judges per event. The <em>Preferred Judges</em> column shows who selected this event. Use the dropdowns to confirm or override the panel, then click <strong>Save Assignments</strong>.
                </div>

                <div class="row">
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric blue">
                            <h3><?php echo count($eventJudgeRows); ?></h3>
                            <p>Events In Season</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric green">
                            <h3><?php echo $totalJudgesInPool; ?></h3>
                            <p>Judges In Pool</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric orange">
                            <h3><?php echo $eventsWithUnderTwo; ?></h3>
                            <p>Events &lt; Minimum 2 Judges</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric red">
                            <h3><?php echo $eventsWithUnderThree; ?></h3>
                            <p>Events &lt; Maximum 3 Judges</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($eventJudgeRows)) { ?>
                <?php echo $this->Form->create(null, ['id'=>'judgingAssignForm', 'method'=>'post', 'url'=>['action'=>'judginglist']]); ?>
                <section id="no-more-tables" class="lstng-section">
                    <div class="tbl-resp-listing">
                        <table id="judging_list_table" class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf ajshort">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Code</th>
                                    <th>Event Name</th>
                                    <th>Preferred Judges</th>
                                    <th>Judge 1</th>
                                    <th>Judge 2</th>
                                    <th>Judge 3</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($eventJudgeRows as $row) {
                                $eid = (int)$row['event_id'];
                                $saved = isset($savedAssignments[$eid]) ? $savedAssignments[$eid] : ['judge1'=>null,'judge2'=>null,'judge3'=>null];

                                // Auto-suggest from preferred names if nothing is saved yet
                                $allSaved = $saved['judge1'] || $saved['judge2'] || $saved['judge3'];
                                if(!$allSaved)
                                {
                                    $judgeFlip = array_flip($judgeDD);
                                    $panel = $row['panel_three_names'];
                                    $saved['judge1'] = isset($panel[0], $judgeFlip[$panel[0]]) ? $judgeFlip[$panel[0]] : null;
                                    $saved['judge2'] = isset($panel[1], $judgeFlip[$panel[1]]) ? $judgeFlip[$panel[1]] : null;
                                    $saved['judge3'] = isset($panel[2], $judgeFlip[$panel[2]]) ? $judgeFlip[$panel[2]] : null;
                                }

                                $assignedCount = count(array_filter([$saved['judge1'],$saved['judge2'],$saved['judge3']]));
                                if($assignedCount >= 3)     { $badge = '<span class="jl-badge ok">3 judges</span>'; }
                                elseif($assignedCount == 2) { $badge = '<span class="jl-badge warn">2 judges</span>'; }
                                elseif($assignedCount == 1) { $badge = '<span class="jl-badge bad">1 judge</span>'; }
                                else                        { $badge = '<span class="jl-badge bad">unassigned</span>'; }

                                $rowClass = '';
                                if($assignedCount >= 3) {
                                    $rowClass = 'jl-row-three';
                                } elseif($assignedCount == 2) {
                                    $rowClass = 'jl-row-two';
                                } elseif($assignedCount == 1) {
                                    $rowClass = 'jl-row-one';
                                }
                            ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td><?php echo $eid; ?></td>
                                    <td><?php echo h($row['event_id_number']); ?></td>
                                    <td>
                                        <?php echo h($row['event_name']); ?>
                                        <?php echo $badge; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['preferred_names'])) {
                                            echo '<small class="jl-preferred">'.implode(', ', array_map('htmlspecialchars', $row['preferred_names'])).'</small>';
                                        } else {
                                            echo '<small class="jl-preferred" style="color:#ccc;">None selected</small>';
                                        } ?>
                                    </td>
                                    <td>
                                        <select name="assignments[<?php echo $eid; ?>][judge1]" class="form-control input-sm">
                                            <?php foreach($judgeDD as $uid => $uname) {
                                                $sel = ($uid !== '' && (int)$uid === (int)$saved['judge1']) ? ' selected' : '';
                                                echo '<option value="'.h($uid).'"'.$sel.'>'.h($uname).'</option>';
                                            } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="assignments[<?php echo $eid; ?>][judge2]" class="form-control input-sm">
                                            <?php foreach($judgeDD as $uid => $uname) {
                                                $sel = ($uid !== '' && (int)$uid === (int)$saved['judge2']) ? ' selected' : '';
                                                echo '<option value="'.h($uid).'"'.$sel.'>'.h($uname).'</option>';
                                            } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="assignments[<?php echo $eid; ?>][judge3]" class="form-control input-sm">
                                            <?php foreach($judgeDD as $uid => $uname) {
                                                $sel = ($uid !== '' && (int)$uid === (int)$saved['judge3']) ? ' selected' : '';
                                                echo '<option value="'.h($uid).'"'.$sel.'>'.h($uname).'</option>';
                                            } ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php echo $this->Form->end(); ?>
                <?php } else { ?>
                    <div class="alert alert-warning">No season events found. Add events to this season first.</div>
                <?php } ?>
            </div>
            <div class="box-footer">
                <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['class'=>'btn btn-default', 'escape'=>false]); ?>
                <?php if (!empty($eventJudgeRows)) { ?>
                <button type="submit" form="judgingAssignForm" class="btn btn-success">
                    <i class="fa fa-save"></i> Save Assignments
                </button>
                <?php } ?>
            </div>
        </div>

        <?php if (!empty($workloadData)) { ?>
        <div class="box box-default" style="margin-top:18px;">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-bar-chart"></i>&nbsp; Judge Workload Balance <small id="wl-avg-label">(saved avg: <?php echo $avgLoad; ?> events/judge)</small></h3>
                <div class="box-tools pull-right">
                    <small class="text-muted" style="font-size:12px;"><i class="fa fa-refresh"></i> Updates live as you change dropdowns</small>
                </div>
            </div>
            <div class="box-body" style="padding:10px;">
                <div class="jl-workload-note">Tip: closer to the average means better balance. Positive delta means overloaded.</div>
                <?php $maxSavedLoad = 0; foreach($workloadData as $wdx){ $maxSavedLoad = max($maxSavedLoad, (int)$wdx['count']); } ?>
                <div class="jl-workload-wrap">
                    <table id="workload_table" class="table table-condensed table-bordered">
                        <thead>
                            <tr>
                                <th class="wl-rank">#</th>
                                <th>Judge</th>
                                <th class="wl-count">Events</th>
                                <th class="wl-meter">Load Meter</th>
                                <th class="wl-balance">Delta vs Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $rank = 0; foreach($workloadData as $wd) { $rank++; ?>
                            <?php
                            $cnt = (int)$wd['count'];
                            if($avgLoad <= 0) { $cls = 'default'; }
                            elseif($cnt <= ceil($avgLoad)) { $cls = 'success'; }
                            elseif($cnt <= ceil($avgLoad) + 2) { $cls = 'warning'; }
                            else { $cls = 'danger'; }
                            $pct = $maxSavedLoad > 0 ? (int)round(($cnt / $maxSavedLoad) * 100) : 0;
                            $delta = $cnt - $avgLoad;
                            $deltaText = $delta > 0 ? '+' . number_format($delta, 1) : number_format($delta, 1);
                            $deltaCls = abs($delta) < 0.05 ? 'neu' : ($delta > 0 ? 'pos' : 'neg');
                            ?>
                            <tr class="wl-row" data-uid="<?php echo (int)$wd['user_id']; ?>">
                                <td class="wl-rank"><?php echo $rank; ?></td>
                                <td><?php echo h($wd['name']); ?></td>
                                <td class="wl-count"><?php echo $cnt; ?></td>
                                <td class="wl-meter">
                                    <div class="jl-load-track"><span class="jl-load-fill <?php echo $cls; ?>" style="width:<?php echo $pct; ?>%"></span></div>
                                </td>
                                <td class="wl-balance <?php echo $deltaCls; ?>"><?php echo $deltaText; ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php } ?>

    </section>
</div>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script>
$(document).ready(function() {
    $('#judging_list_table').dataTable({
        bPaginate: true,
        bLengthChange: false,
        pageLength: 100,
        order: [[0,'asc']],
        columnDefs: [{ orderable: false, targets: [3,4,5,6] }]
    });

    function updateRowHighlights() {
        $('#judging_list_table tbody tr').each(function() {
            var filled = 0;
            $(this).find('select').each(function() {
                var val = $(this).val();
                if(val && val !== '') {
                    filled++;
                }
            });

            $(this).removeClass('jl-row-three jl-row-two jl-row-one');
            if(filled >= 3) {
                $(this).addClass('jl-row-three');
            } else if(filled === 2) {
                $(this).addClass('jl-row-two');
            } else if(filled === 1) {
                $(this).addClass('jl-row-one');
            }
        });
    }

    // Live workload recalculation when any judge dropdown changes
    function recalcWorkload() {
        var counts = {};
        $('#judging_list_table select').each(function() {
            var val = $(this).val();
            if(val && val !== '') {
                counts[val] = (counts[val] || 0) + 1;
            }
        });
        var total = 0, numJudges = 0;
        $('#workload_table tbody tr').each(function() {
            var uid = $(this).data('uid').toString();
            var cnt = counts[uid] || 0;
            total += cnt; numJudges++;
            $(this).find('.wl-count').text(cnt);
        });
        var avg = numJudges > 0 ? total / numJudges : 0;
        $('#wl-avg-label').text('(live avg: ' + avg.toFixed(1) + ' events/judge)');

        var max = 0;
        $('#workload_table tbody tr .wl-count').each(function() {
            var v = parseInt($(this).text(), 10) || 0;
            if (v > max) max = v;
        });

        $('#workload_table tbody tr').each(function() {
            var uid = $(this).data('uid').toString();
            var cnt = counts[uid] || 0;
            var cls;
            if(avg <= 0)                        { cls = 'default'; }
            else if(cnt <= Math.ceil(avg))      { cls = 'success'; }
            else if(cnt <= Math.ceil(avg) + 2)  { cls = 'warning'; }
            else                                { cls = 'danger'; }

            var pct = max > 0 ? Math.round((cnt / max) * 100) : 0;
            var delta = cnt - avg;
            var deltaText = (delta > 0 ? '+' : '') + delta.toFixed(1);
            var deltaCls = Math.abs(delta) < 0.05 ? 'neu' : (delta > 0 ? 'pos' : 'neg');

            $(this).find('.jl-load-fill')
                .removeClass('success warning danger default')
                .addClass(cls)
                .css('width', pct + '%');

            $(this).find('.wl-balance')
                .removeClass('pos neg neu')
                .addClass(deltaCls)
                .text(deltaText);
        });

        // Keep highest-load judges at top for quick balancing.
        var rows = $('#workload_table tbody tr').get();
        rows.sort(function(a, b) {
            var av = parseInt($(a).find('.wl-count').text(), 10) || 0;
            var bv = parseInt($(b).find('.wl-count').text(), 10) || 0;
            return bv - av;
        });
        $.each(rows, function(i, row) {
            $('#workload_table tbody').append(row);
            $(row).find('.wl-rank').text(i + 1);
        });
    }
    $('#judging_list_table').on('change', 'select', function() {
        recalcWorkload();
        updateRowHighlights();
    });

    $('#judging_list_table').on('draw.dt', updateRowHighlights);
    updateRowHighlights();
});
</script>
