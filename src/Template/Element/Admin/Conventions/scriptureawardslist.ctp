<?php
use Cake\ORM\TableRegistry;
$this->Users = TableRegistry::get('Users');
$this->Events = TableRegistry::get('Events');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (count($finalSchoolsList)) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

        <div class="box-header with-border" style="margin-bottom:12px;">
            <h4 class="box-title" style="margin:0;">Scripture Award Certificates</h4>
            <span class="text-muted" style="font-size:13px;">Click a school row to expand its events.</span>
        </div>

        <?php
        foreach ($finalSchoolsList as $school_id) {
            $schoolD     = $this->Users->find()->where(['Users.id' => $school_id])->first();
            $schoolEvents = $finalSchoolsEventsList[$school_id];
            $certCount   = isset($finalCertCount[$school_id]) ? $finalCertCount[$school_id] : count($schoolEvents);
        ?>
        <div class="panel panel-default" style="margin-bottom:8px; border:1px solid #ddd; border-radius:4px;">
            <div class="panel-heading" style="cursor:pointer; padding:10px 15px; background:#f5f5f5; display:flex; justify-content:space-between; align-items:center;"
                 onclick="toggleSchool(<?php echo $school_id; ?>)">
                <span style="font-weight:600; font-size:14px;">
                    <i class="fa fa-building-o" style="margin-right:6px;"></i>
                    <?php echo h($schoolD->first_name); ?>
                    <small class="text-muted">&nbsp;#<?php echo $school_id; ?></small>
                </span>
                <span class="badge" style="background:#1a98d5; font-size:12px;"><?php echo $certCount; ?> certificate<?php echo $certCount !== 1 ? 's' : ''; ?></span>
            </div>
            <div id="school_<?php echo $school_id; ?>" style="display:none; padding:0;">
                <table class="table table-bordered table-striped table-condensed" style="margin:0;">
                    <thead>
                        <tr>
                            <th style="width:60%;">Event</th>
                            <th style="width:20%; text-align:center;">Event #</th>
                            <th style="width:20%; text-align:center;">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($schoolEvents as $event_id):
                        $eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
                        if (!$eventD) continue;
                    ?>
                        <tr>
                            <td><?php echo h($eventD->event_name); ?></td>
                            <td style="text-align:center;"><?php echo h($eventD->event_id_number); ?></td>
                            <td style="text-align:center;">
                                <?php
                                $studentCount = isset($finalEventStudentCount[$school_id][$event_id]) ? $finalEventStudentCount[$school_id][$event_id] : 0;
                                echo $this->Html->link(
                                    '<i class="fa fa-file-pdf-o"></i> PDF',
                                    ['controller' => 'conventionregistrationstudents', 'action' => 'scriptureawardpdf', $slug_convention_season, $schoolD->slug, $eventD->slug],
                                    ['escape' => false, 'class' => 'btn btn-xs btn-danger', 'title' => 'Generate Certificate', 'target' => '_blank']
                                );
                                if ($studentCount > 0): ?>
                                    <span class="badge" style="background:#555; margin-left:4px;"><?php echo $studentCount; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>

    </div>

    <script>
    function toggleSchool(id) {
        var el = document.getElementById('school_' + id);
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
    // Auto-expand if only one school
    <?php if (count($finalSchoolsList) === 1): ?>
    toggleSchool(<?php echo $finalSchoolsList[0]; ?>);
    <?php endif; ?>
    </script>

<?php } else { ?>
    <div class="admin_no_record">No record found.</div>
<?php } ?>
