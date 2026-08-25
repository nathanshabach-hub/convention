<?php
$exceptionCount = count($scheduleExceptionItems);
$schedulingWarnings = $schedulingWarnings ?? [];
$ungroupedCount = 0;
$missingUploadCount = 0;
foreach ($scheduleExceptionItems as $scheduleExceptionItem) {
    if (strpos((string)$scheduleExceptionItem['reason'], 'Students not grouped') !== false) {
        $ungroupedCount++;
    }
    if (strpos((string)$scheduleExceptionItem['reason'], 'Missing matching upload') !== false) {
        $missingUploadCount++;
    }
}
?>
<style>
.unscheduled-page .content-header { padding-bottom: 4px; }
.unscheduled-page .unscheduled-hero { background: #b12525; color: #fff; padding: 20px 24px; margin-bottom: 18px; border-radius: 4px; display: flex; align-items: center; justify-content: space-between; }
.unscheduled-page .unscheduled-hero h2 { margin: 0; font-size: 22px; font-weight: 600; }
.unscheduled-page .unscheduled-hero p { margin: 4px 0 0; opacity: .9; }
.unscheduled-page .unscheduled-hero-icon { font-size: 36px; opacity: .7; }
.unscheduled-page .unscheduled-summary { display: flex; gap: 12px; margin-bottom: 18px; }
.unscheduled-page .unscheduled-summary-item { flex: 1; border: 1px solid #e5e8ed; border-top: 3px solid #b12525; padding: 12px 16px; background: #fff; }
.unscheduled-page .unscheduled-summary-item.is-warning { border-top-color: #d2851c; }
.unscheduled-page .unscheduled-summary-item.is-info { border-top-color: #2c78b9; }
.unscheduled-page .unscheduled-summary-count { display: block; font-size: 24px; font-weight: 700; line-height: 1; }
.unscheduled-page .unscheduled-summary-label { display: block; margin-top: 5px; color: #667080; font-size: 12px; text-transform: uppercase; }
.unscheduled-page .unscheduled-event-code { color: #a32828; font-weight: 700; margin-right: 5px; }
.unscheduled-page .reason-tag { display: inline-block; margin: 2px 4px 2px 0; padding: 2px 7px; border-radius: 2px; font-size: 11px; font-weight: 600; }
.unscheduled-page .reason-tag-group { background: #fff1d8; color: #916019; }
.unscheduled-page .reason-tag-upload { background: #f9dfdf; color: #982b2b; }
.unscheduled-page .participant-info { width: 30px; height: 26px; padding: 0; border: 0; border-radius: 3px; color: #286090; background: #e2eff9; }
.unscheduled-page .participant-info:hover, .unscheduled-page .participant-info:focus { color: #fff; background: #286090; }
.unscheduled-page .school-cell { background: #f7f9fb; border-right: 2px solid #dfe6ee !important; vertical-align: middle !important; min-width: 200px; }
.unscheduled-page .school-group-start > td { border-top: 3px solid #c8d4e2 !important; }
.unscheduled-page .school-group-start:first-child > td { border-top-width: 1px !important; }
.unscheduled-page .unscheduled-empty { padding: 36px 24px; text-align: center; color: #478847; }
.unscheduled-page .unscheduled-empty i { display: block; font-size: 34px; margin-bottom: 10px; }
.unscheduled-page .scheduling-warnings { margin-bottom: 18px; border-left: 4px solid #d2851c; background: #fff8e8; padding: 12px 16px; }
.unscheduled-page .scheduling-warnings h3 { margin: 0 0 7px; font-size: 15px; color: #805815; }
.unscheduled-page .scheduling-warnings ul { margin: 0; padding-left: 18px; }
.unscheduled-page .scheduling-warnings li { margin: 5px 0; }
.unscheduled-page .student-modal-backdrop { display: none; position: fixed; z-index: 10050; inset: 0; background: rgba(24, 35, 49, .5); }
.unscheduled-page .student-modal { width: calc(100% - 32px); max-width: 420px; margin: 12vh auto 0; background: #fff; border-radius: 4px; box-shadow: 0 12px 32px rgba(0, 0, 0, .3); overflow: hidden; }
.unscheduled-page .student-modal-header { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px; background: #286090; color: #fff; }
.unscheduled-page .student-modal-header h4 { margin: 0; font-size: 16px; font-weight: 600; }
.unscheduled-page .student-modal-close { padding: 0; border: 0; color: #fff; background: transparent; font-size: 24px; line-height: 20px; }
.unscheduled-page .student-modal-body { padding: 8px 16px 16px; }
.unscheduled-page .student-modal-list { margin: 0; padding-left: 20px; }
.unscheduled-page .student-modal-list li { padding: 7px 0; border-bottom: 1px solid #edf0f3; }
.unscheduled-page .student-modal-list li:last-child { border-bottom: 0; }
@media (max-width: 767px) {
    .unscheduled-page .unscheduled-summary { display: block; }
    .unscheduled-page .unscheduled-summary-item { margin-bottom: 8px; }
    .unscheduled-page .unscheduled-hero { padding: 16px; }
}
</style>
<div class="content-wrapper unscheduled-page">
    <section class="content-header">
        <h1>Unscheduled Events <small>Category 1 exceptions</small></h1>
        <ol class="breadcrumb">
            <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard', ['controller' => 'admins', 'action' => 'dashboard'], ['escape' => false]); ?></li>
            <li class="active">Unscheduled Events</li>
        </ol>
    </section>

    <section class="content">
        <div class="unscheduled-hero">
            <div>
                <h2>Scheduling Exceptions</h2>
                <p>Group-event entries that cannot be placed until their registration details are corrected.</p>
            </div>
            <i class="fa fa-exclamation-triangle unscheduled-hero-icon" aria-hidden="true"></i>
        </div>

        <div class="unscheduled-summary">
            <div class="unscheduled-summary-item is-warning">
                <span class="unscheduled-summary-count"><?php echo $ungroupedCount; ?></span>
                <span class="unscheduled-summary-label">Need to be Grouped</span>
            </div>
            <div class="unscheduled-summary-item is-info">
                <span class="unscheduled-summary-count"><?php echo $missingUploadCount; ?></span>
                <span class="unscheduled-summary-label">Need to be Uploaded</span>
            </div>
        </div>

        <?php if (!empty($schedulingWarnings)) { ?>
            <div class="scheduling-warnings">
                <h3><i class="fa fa-exclamation-circle"></i> Latest Scheduling Warnings</h3>
                <ul>
                    <?php foreach ($schedulingWarnings as $schedulingWarning) { ?>
                        <li><?php echo h($schedulingWarning); ?></li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Entries Requiring Attention</h3>
                <div class="box-tools pull-right"><span class="label label-danger"><?php echo $exceptionCount; ?></span></div>
            </div>
            <div class="box-body no-padding">
                <?php if (empty($scheduleExceptionItems)) { ?>
                    <div class="unscheduled-empty"><i class="fa fa-check-circle"></i>No Category 1 events are blocked by missing uploads or group assignments.</div>
                <?php } else { ?>
                    <?php
                    $schoolRowCounts = [];
                    foreach ($scheduleExceptionItems as $scheduleExceptionItem) {
                        $schoolRowCounts[$scheduleExceptionItem['school']] = ($schoolRowCounts[$scheduleExceptionItem['school']] ?? 0) + 1;
                    }
                    $renderedSchools = [];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="margin-bottom:0;">
                            <thead><tr><th>School</th><th>Event</th><th>Group</th><th>Students</th><th>Required Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($scheduleExceptionItems as $scheduleExceptionItem) { ?>
                                    <?php
                                    $eventParts = explode(' ', (string)$scheduleExceptionItem['event'], 2);
                                    $reason = (string)$scheduleExceptionItem['reason'];
									$isFirstSchoolRow = empty($renderedSchools[$scheduleExceptionItem['school']]);
                                    ?>
                                    <tr<?php echo $isFirstSchoolRow ? ' class="school-group-start"' : ''; ?>>
                                        <?php if ($isFirstSchoolRow) { ?>
                                            <td class="school-cell" rowspan="<?php echo (int)$schoolRowCounts[$scheduleExceptionItem['school']]; ?>"><strong><?php echo h($scheduleExceptionItem['school']); ?></strong></td>
                                            <?php $renderedSchools[$scheduleExceptionItem['school']] = true; ?>
                                        <?php } ?>
                                        <td><span class="unscheduled-event-code"><?php echo h($eventParts[0] ?? ''); ?></span><?php echo h($eventParts[1] ?? ''); ?></td>
                                        <td><?php echo h($scheduleExceptionItem['participant']); ?></td>
                                        <td>
                                            <?php $studentList = implode("\n", $scheduleExceptionItem['students'] ?? []); ?>
                                            <?php if ($studentList !== '') { ?>
                                                <button type="button" class="participant-info" data-students="<?php echo h($studentList); ?>" aria-label="Show registered students" title="Show registered students"><i class="fa fa-eye"></i></button>
                                            <?php } else { ?>
                                                <span class="text-muted">No student details</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php if (strpos($reason, 'Students not grouped') !== false) { ?><span class="reason-tag reason-tag-group">Group students</span><?php } ?>
                                            <?php if (strpos($reason, 'Missing matching upload') !== false) { ?><span class="reason-tag reason-tag-upload">Upload submission</span><?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <div class="student-modal-backdrop" id="student-modal" role="dialog" aria-modal="true" aria-labelledby="student-modal-title">
        <div class="student-modal">
            <div class="student-modal-header">
                <h4 id="student-modal-title">Registered Students</h4>
                <button type="button" class="student-modal-close" aria-label="Close student list">&times;</button>
            </div>
            <div class="student-modal-body"><ul class="student-modal-list"></ul></div>
        </div>
    </div>
</div>
<script>
$(function () {
    var $modal = $('#student-modal');
    var $studentList = $modal.find('.student-modal-list');

    $('.participant-info').on('click', function () {
        $studentList.empty();
        $.each(String($(this).data('students') || '').split(/\r?\n/), function (_, studentName) {
            studentName = $.trim(studentName);
            if (studentName) {
                $('<li>').text(studentName).appendTo($studentList);
            }
        });
        $modal.fadeIn(120).find('.student-modal-close').focus();
    });

    $modal.on('click', function (event) {
        if (event.target === this || $(event.target).hasClass('student-modal-close')) {
            $modal.fadeOut(120);
        }
    });
});
</script>