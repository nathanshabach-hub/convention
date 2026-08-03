<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Users = TableRegistry::get('Users');
$this->Resultpositions = TableRegistry::get('Resultpositions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>

<style>
.event-result-accordion {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.event-result-item {
    border: 1px solid #dce4ef;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
}
.event-result-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    cursor: pointer;
    background: #f8fafc;
    font-weight: 600;
    color: #1f2937;
    list-style: none;
}
.event-result-summary::-webkit-details-marker {
    display: none;
}
.event-result-summary:focus {
    outline: 2px solid #7c93b2;
    outline-offset: 2px;
}
.event-result-meta {
    display: block;
    font-size: 12px;
    color: #5b6673;
    font-weight: 500;
    margin-top: 2px;
}
.event-result-badge {
    background: #e3eefb;
    color: #21528f;
    border-radius: 999px;
    padding: 4px 8px;
    font-size: 12px;
    white-space: nowrap;
}
.event-result-body {
    padding: 12px 14px 14px;
    border-top: 1px solid #eef2f7;
}
.event-result-table {
    margin-bottom: 0;
}
.event-result-table thead > tr > th {
    background: #f4f7fb;
}
.event-result-table td {
    vertical-align: top;
}
.event-result-table .student_cp {
    display: inline-block;
    margin-top: 4px;
    color: #b45309;
    font-size: 12px;
}
</style>

<?php if (count($arrConvSeasonEvent) > 0) { ?>

    <div class="panel-body">

        <?php echo $this->Form->create(null, ['id' => 'actionFrom', 'method' => 'Post']); ?>
        <section id="no-more-tables" class="lstng-section">

            <div class="tbl-resp-listing">
                <?php if (count($arrConvSeasonEvent) > 0) { ?>
                    <?php
                    $arrConvSeasonEventImplode = implode(',', $arrConvSeasonEvent);
                    $condEvents = [];
                    $condEvents[] = '(Events.id IN (' . $arrConvSeasonEventImplode . '))';
                    $events = $this->Events->find()->where($condEvents)->order(['Events.event_id_number' => 'ASC'])->all();
                    ?>

                    <div class="event-result-accordion">
                        <?php foreach ($events as $event) { ?>
                            <?php
                            $checkEventCPSubmission = $this->Eventsubmissions->find()->where([
                                'Eventsubmissions.command_performance' => 1,
                                'Eventsubmissions.event_id' => $event->id,
                                'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                'Eventsubmissions.conventionregistration_id' => $conventionRegD->id,
                                'Eventsubmissions.user_id' => $userDetails->id,
                            ])->first();
                            $checkEventCP = !empty($checkEventCPSubmission);

                            $countpositions = $this->Resultpositions->find()->where([
                                'Resultpositions.user_id' => $userDetails->id,
                                'Resultpositions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                'Resultpositions.event_id' => $event->id,
                                'Resultpositions.position >' => 0,
                                'Resultpositions.position <=' => 6,
                            ])->order(['Resultpositions.position' => 'ASC'])->count();
                            ?>

                            <?php if ($countpositions > 0 || $checkEventCP) { ?>
                                <details class="event-result-item" open>
                                    <summary class="event-result-summary">
                                        <span>
                                            <strong><?php echo h($event->event_name); ?></strong>
                                            <span class="event-result-meta"><?php echo h($event->event_id_number); ?></span>
                                        </span>
                                        <span class="event-result-badge"><?php echo $countpositions; ?> placement<?php echo $countpositions == 1 ? '' : 's'; ?></span>
                                    </summary>
                                    <div class="event-result-body">
                                        <?php if ($countpositions > 0) { ?>
                                            <?php
                                            $overallpositions = $this->Resultpositions->find()->where([
                                                'Resultpositions.user_id' => $userDetails->id,
                                                'Resultpositions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                                'Resultpositions.event_id' => $event->id,
                                                'Resultpositions.position >' => 0,
                                                'Resultpositions.position <=' => 6,
                                            ])->order(['Resultpositions.position' => 'ASC'])->all();
                                            ?>
                                            <table class="table table-bordered table-condensed event-result-table">
                                                <thead>
                                                    <tr>
                                                        <th width="20%">Position</th>
                                                        <th width="40%">Student / Group</th>
                                                        <th width="30%">School</th>
                                                        <th width="10%">Print</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($overallpositions as $ovpos) { ?>
                                                        <?php
                                                        $showName = '';
                                                        $showSchoolName = '';
                                                        $checkStudentCP = $checkEventCP;

                                                        if ($ovpos->student_id > 0) {
                                                            $studentD = $this->Users->find()->where(['Users.id' => $ovpos->student_id])->contain(['Schools'])->first();
                                                            $showName = $studentD->first_name . ' ' . $studentD->middle_name . ' ' . $studentD->last_name;
                                                            $showSchoolName = $studentD->Schools['first_name'];

                                                            if (!empty($ovpos->eventsubmission_id)) {
                                                                $checkStudentCP = $this->Eventsubmissions->find()->where([
                                                                    'Eventsubmissions.id' => $ovpos->eventsubmission_id,
                                                                    'Eventsubmissions.command_performance' => 1,
                                                                ])->first();
                                                            }
                                                            if (empty($checkStudentCP) && !empty($ovpos->event_id) && !empty($ovpos->student_id)) {
                                                                $checkStudentCP = $this->Eventsubmissions->find()->where([
                                                                    'Eventsubmissions.command_performance' => 1,
                                                                    'Eventsubmissions.event_id' => $ovpos->event_id,
                                                                    'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                                                    'Eventsubmissions.student_id' => $ovpos->student_id,
                                                                ])->first();
                                                            }
                                                            if (empty($checkStudentCP) && !empty($ovpos->event_id) && !empty($userDetails->id)) {
                                                                $checkStudentCP = $this->Eventsubmissions->find()->where([
                                                                    'Eventsubmissions.command_performance' => 1,
                                                                    'Eventsubmissions.event_id' => $ovpos->event_id,
                                                                    'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                                                    'Eventsubmissions.user_id' => $userDetails->id,
                                                                ])->first();
                                                            }
                                                            if (empty($checkStudentCP) && !empty($ovpos->event_id) && !empty($conventionRegD->id)) {
                                                                $checkStudentCP = $this->Eventsubmissions->find()->where([
                                                                    'Eventsubmissions.command_performance' => 1,
                                                                    'Eventsubmissions.event_id' => $ovpos->event_id,
                                                                    'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                                                    'Eventsubmissions.conventionregistration_id' => $conventionRegD->id,
                                                                ])->first();
                                                            }
                                                        } else if (!empty($ovpos->group_name)) {
                                                            $arrGrpStudent = [];
                                                            $groupstudents = $this->Crstudentevents->find()->where([
                                                                'Crstudentevents.user_id' => $userDetails->id,
                                                                'Crstudentevents.conventionseason_id' => $ovpos->conventionseason_id,
                                                                'Crstudentevents.event_id' => $event->id,
                                                                'Crstudentevents.group_name ' => $ovpos->group_name,
                                                            ])->order(['Crstudentevents.id' => 'ASC'])->all();
                                                            foreach ($groupstudents as $grpstudent) {
                                                                $studentDG = $this->Users->find()->where(['Users.id' => $grpstudent->student_id])->contain(['Schools'])->first();
                                                                $grpStName = $studentDG->first_name . ' ' . $studentDG->middle_name . ' ' . $studentDG->last_name;
                                                                $arrGrpStudent[] = $grpStName;
                                                                $showSchoolName = $studentDG->Schools['first_name'];
                                                            }
                                                            if (count($arrGrpStudent) > 0) {
                                                                $showName = implode(', ', $arrGrpStudent);
                                                            }
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td data-title="Position"><?php echo h($ovpos->position); ?></td>
                                                            <td data-title="Student / Group">
                                                                <?php echo h($showName); ?>
                                                                <?php if ($checkStudentCP) { echo '<br><span class="student_cp">This is event was nominated for a Command Performance.</span>'; } ?>
                                                            </td>
                                                            <td data-title="School"><?php echo h($showSchoolName); ?></td>
                                                            <td data-title="Print Certificate">
                                                                <?php echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'conventionregistrations', 'action' => 'placecertificatepdf', $ovpos->slug, $ovpos->position], ['escape' => false, 'class' => '', 'target' => '_blank', 'title' => 'Print Place Certificate']); ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        <?php } else { ?>
                                            <div class="text-muted">No placements found for this event.</div>
                                        <?php } ?>
                                    </div>
                                </details>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </section>

        <?php echo $this->Form->end(); ?>
    </div>

<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No student event found.</div>
<?php } ?>
