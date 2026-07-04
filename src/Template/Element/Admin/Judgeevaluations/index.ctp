<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo HTTP_IMAGE ?>/loading.gif',
            closeImage: '<?php echo HTTP_IMAGE ?>/close.png'
        })
    })            
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Evaluationquestions = TableRegistry::get('Evaluationquestions');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$judgeevaluations->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Judge Evaluations</div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="judge_eval_table" class="table table-bordered table-striped table-condensed cf judge-evaluations-table">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Event Number</th>
                            <th class="sorting_paging">Event Name</th>
                            <th class="sorting_paging">Group Event?</th>
                            <th class="sorting_paging">Group</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Judge</th>
                            <th class="sorting_paging">Marks/Score</th>
                            <th class="sorting_paging">Withdrawn</th>
                            <th class="sorting_paging">Submitted</th>
                            <th class="sorting_paging">File</th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cntrS = 0;
                        foreach ($judgeevaluations as $datarecord)
                        {
                            $cntrS++;
                        ?>
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
                                <td data-title="Event Name"><?php echo $datarecord->Events['event_name'];?></td>
                                <td data-title="Group Event?"><?php echo ($datarecord->Events['group_event_yes_no'] == 1) ? "Yes" : "No"; ?></td>
                                <td data-title="Group">
                                <?php
                                if(!empty($datarecord->Eventsubmissions['group_name'])) {
                                    echo "Group ".$datarecord->Eventsubmissions['group_name'];
                                } else {
                                    echo '-';
                                }
                                ?>
                                </td>
                                <td data-title="Student">
                                <?php
                                if($datarecord->Eventsubmissions['student_id'] > 0) {
                                    echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
                                } else {
                                    echo '-';
                                }
                                ?>
                                </td>
                                <td data-title="School"><?php echo $datarecord->Schools['first_name']; ?></td>
                                    <td data-title="Judge">
                                    <?php
                                        $judgeName = '-';
                                        if (!empty($datarecord->uploaded_by_user_id) && !empty($judgeUsersById[$datarecord->uploaded_by_user_id])) {
                                            $judgeName = $judgeUsersById[$datarecord->uploaded_by_user_id];
                                        } elseif (!empty($datarecord->Judge)) {
                                            $judgeName = trim(($datarecord->Judge['first_name'] ?? '') . ' ' . ($datarecord->Judge['last_name'] ?? '')) ?: '-';
                                        }
                                        echo $judgeName;
                                    ?>
                                    </td>
                                <td data-title="Marks">
                                <?php
                                if($datarecord->Events['event_judging_type'] == 'times') {
                                    if($datarecord->time_score != NULL && !empty($datarecord->time_score)) {
                                        $tScore = $datarecord->time_score;
                                        $tScoreC = $tScore->format('H:i:s.u');
                                        if (strpos($tScoreC, '.') !== false) {
                                            list($hms, $micro) = explode('.', $tScoreC);
                                            $micro = rtrim($micro, '0');
                                            $formattedTime = ($micro === '') ? $hms : $hms . '.' . $micro;
                                        } else {
                                            $formattedTime = $tScoreC;
                                        }
                                        echo $formattedTime;
                                    }
                                } else if($datarecord->Events['event_judging_type'] == 'distances') {
                                    echo $datarecord->distance_score;
                                } else if($datarecord->Events['event_judging_type'] == 'scores') {
                                    echo $datarecord->all_pos_score;
                                } else if($datarecord->Events['event_judging_type'] == 'soccer_kick') {
                                    echo $datarecord->soccer_kick_best_kick.'m';
                                } else if($datarecord->Events['event_judging_type'] == 'spellings') {
                                    echo $datarecord->spelling_score;
                                } else if($datarecord->did_not_attend == 0) {
                                    echo "$datarecord->total_marks_obtained/$datarecord->total_marks_possible";
                                } else {
                                    echo "Did not attend";
                                }
                                ?>
                                </td>
                                <td data-title="Withdrawn"><?php echo ($datarecord->withdraw_yes_no == 1) ? 'W' : ''; ?></td>
                                <td data-title="Submitted Date"><?php echo $datarecord->created->format('M d, Y'); ?></td>
                                <td data-title="File">
                                <?php
                                    $files = [
                                        'mediafile_file_system_name' => $datarecord->Events['upload_type'],
                                        'report' => "Report",
                                        'score_sheet' => "Score Sheet",
                                        'additional_documents' => "Additional Documents"
                                    ];
                                    foreach($files as $key => $title) {
                                        $img = $datarecord->Eventsubmissions[$key];
                                        if(!empty($img) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$img)) {
                                            echo '<a class="btn btn-info btn-xs" target="_blank" title="'.$title.'" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$img.'"><i class="fa fa-cloud-download"></i></a> ';
                                        }
                                    }
                                ?>
                                </td>
                                <td data-title="Action">
                                    <?php if($datarecord->Events['event_judging_type'] == 'times') {
                                        echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'judgeevaluations', 'action' => 'timesscoreedit',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit Times Score', 'class'=>'btn btn-primary btn-xs']);
                                    } else { ?>
                                        <a href="#info<?php echo $datarecord->id; ?>" rel="facebox" title="View Evaluation Questions" class="btn btn-info btn-xs eyee"><i class="fa fa-eye "></i></a>
                                    <?php }
                                    echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'judgeevaluations', 'action' => 'removejudgeevaluation',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove', 'class'=>'btn btn-info btn-xs', 'confirm' => 'Are you sure you want to remove this judge evaluation?']); ?>
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
    <div class="admin_no_record">No record found.</div>
<?php } ?>

<?php foreach ($judgeevaluations as $datarecord) { ?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
                    <?php 
                    if($datarecord->Eventsubmissions['student_id'] > 0) { echo 'Student: '.$datarecord->Students['first_name'].' '.$datarecord->Students['last_name']; }
                    else if(!empty($datarecord->Eventsubmissions['group_name'])) { echo "Group ".$datarecord->Eventsubmissions['group_name']; }
                    ?>
                    [Event: <?php echo $datarecord->Events['event_name']; ?> (<?php echo $datarecord->Events['event_id_number']; ?>)]
                </legend>
                <div class="drt">
                    <?php if($datarecord->Events['event_judging_type'] == 'general') { ?>
                    <table class="table table-bordered">
                        <tr><td colspan="4">Comments: <?php echo $datarecord->comments ?: 'N/A'; ?></td></tr>
                        <tr><td>#</td><td>Question</td><td>Max</td><td>Obtained</td></tr>
                        <?php $cntrQ = 1; foreach($datarecord->Judgeevaluationmarks as $judgevalmark) { 
                            $questionD = $this->Evaluationquestions->find()->where(["Evaluationquestions.id" => $judgevalmark->question_id])->first(); ?>
                        <tr>
                            <td><?php echo $cntrQ++; ?></td>
                            <td><?php echo $questionD->question; ?></td>
                            <td><?php echo $judgevalmark->question_marks_possible; ?></td>
                            <td><?php echo $judgevalmark->question_marks_obtained; ?></td>
                        </tr>
                        <?php } ?>
                        <tr><td colspan="2">&nbsp;</td><td><b><?php echo $datarecord->total_marks_possible; ?></b></td><td><b><?php echo $datarecord->total_marks_obtained; ?></b></td></tr>
                    </table>
                    <?php } else if($datarecord->Events['event_judging_type'] == 'distances') { ?>
                    <table class="table table-bordered">
                        <tr><td>1st Attempt</td><td>2nd Attempt</td><td>3rd Attempt</td><td><b>Best Score</b></td></tr>
                        <tr><td><?php echo $datarecord->distance_attempt_1 ?></td><td><?php echo $datarecord->distance_attempt_2 ?></td><td><?php echo $datarecord->distance_attempt_3 ?></td><td><b><?php echo $datarecord->distance_score ?></b></td></tr>
                    </table>
                    <?php } ?>
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>

<script>
$(document).ready(function() {
    if ($.fn.dataTable.isDataTable('#judge_eval_table')) {
        $('#judge_eval_table').DataTable().destroy();
    }

    var table = $('#judge_eval_table').DataTable({
        "bPaginate": true,
        "bLengthChange": false,
        "autoWidth": false,
        "scrollX": false,
        "scrollY": false,
        "scrollCollapse": false,
        "pageLength": 100,
        "deferRender": true,
        order: [[11, 'desc']],
    });

    function refreshJudgeEvalColumns() {
        table.columns.adjust().draw(false);
    }

    $(window).on('resize', function() {
        refreshJudgeEvalColumns();
    });

    $('.sidebar-toggle').on('click', function() {
        setTimeout(refreshJudgeEvalColumns, 320);
    });

    function normalizeText(val) {
        return (val || '').toString().replace(/\s+/g, ' ').trim().toLowerCase();
    }

    var filterConvention = '';
    var filterSeason = '';
    var filterEvent = '';

    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (!settings || !settings.nTable || settings.nTable.id !== 'judge_eval_table') {
            return true;
        }

        var rowConvention = normalizeText(data[0]);
        var rowSeason = normalizeText(data[1]);
        var rowEventName = normalizeText(data[3]);

        var passesConvention = !filterConvention || rowConvention === filterConvention;
        var passesSeason = !filterSeason || rowSeason === filterSeason;
        var passesEvent = !filterEvent || rowEventName.indexOf(filterEvent) !== -1;

        return passesConvention && passesSeason && passesEvent;
    });

    function applyJudgeEvalFilters() {
        filterConvention = normalizeText($('#convention_id option:selected').text());
        filterSeason = normalizeText($('#season_year').val());

        var eventLabel = $('#event_id option:selected').text();
        if (normalizeText($('#event_id').val()) === '') {
            eventLabel = '';
        }
        eventLabel = eventLabel.replace(/^\s*\d+\s*[-:|]\s*/i, '');
        filterEvent = normalizeText(eventLabel);

        table.search($('#judgeeval_keyword').val() || '').draw();
    }

    $('#judgeeval_apply').on('click', function(e) {
        e.preventDefault();
        applyJudgeEvalFilters();
    });

    $('#convention_id, #season_year, #event_id').on('change', function() {
        applyJudgeEvalFilters();
    });

    $('#judgeeval_reset').on('click', function(e) {
        e.preventDefault();
        $('#convention_id').val('');
        $('#season_year').val('');
        $('#event_id').val('');
        $('#judgeeval_keyword').val('');
        filterConvention = '';
        filterSeason = '';
        filterEvent = '';
        table.search('').draw();
    });

    $('#judgeeval_keyword').on('keyup', function() {
        table.search($(this).val() || '').draw();
    });

    $('#adminSearch').on('submit', function(e) {
        e.preventDefault();
    });
});
</script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style type="text/css">
    .judge-evaluations-table {
        width: 100% !important;
    }
    .page-link { color: #1c2452 !important; background-color: #fff !important; }
    .active>.page-link, .page-link.active { background-color: #1c2452 !important; border-color: #1c2452 !important; color: #fff !important; }
    .pagination { border-radius: 0rem !important; }
</style>