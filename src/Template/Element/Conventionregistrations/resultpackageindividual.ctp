<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Users = TableRegistry::get('Users');
$this->Resultpositions = TableRegistry::get('Resultpositions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>

<style>
.student-result-accordion {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.student-result-item {
    border: 1px solid #dce4ef;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
}
.student-result-summary {
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
.student-result-summary::-webkit-details-marker {
    display: none;
}
.student-result-summary:focus {
    outline: 2px solid #7c93b2;
    outline-offset: 2px;
}
.student-result-meta {
    display: block;
    font-size: 12px;
    color: #5b6673;
    font-weight: 500;
    margin-top: 2px;
}
.student-result-badge {
    background: #e3eefb;
    color: #21528f;
    border-radius: 999px;
    padding: 4px 8px;
    font-size: 12px;
    white-space: nowrap;
}
.student-result-body {
    padding: 12px 14px 14px;
    border-top: 1px solid #eef2f7;
}
.student-result-table {
    margin-bottom: 0;
}
.student-result-table thead > tr > th {
    background: #f4f7fb;
}
.student-result-table td {
    vertical-align: top;
}
.student-result-table .student_cp {
    display: inline-block;
    margin-top: 4px;
    color: #b45309;
    font-size: 12px;
}
</style>

<?php if (count($arrStudentsSchool) > 0) { ?>

    <div class="panel-body">

        <?php echo $this->Form->create(null, ['id' => 'actionFrom', 'method' => 'Post']); ?>
        <section id="no-more-tables" class="lstng-section">

            <div class="tbl-resp-listing">
                <?php if (count($arrStudentsSchool) > 0) { ?>
                    <?php
                    $arrStudentsSchoolImplode = implode(',', $arrStudentsSchool);
                    $condStudents = [];
                    $condStudents[] = '(Users.id IN (' . $arrStudentsSchoolImplode . '))';
                    $students = $this->Users->find()->where($condStudents)->order(['Users.first_name' => 'ASC'])->all();
                    ?>

                    <div class="student-result-accordion">
                        <?php $cntrP = 0; foreach ($students as $student) { ?>

                            <?php if ($show_header_each_page == 1 && $cntrP > 0) { ?>
                                <div class="teachers-top-heading">
                                    <span><h1 style="color:#000;"><?php echo $userDetails->first_name; ?></h1></span>
                                    <br />
                                    <span>Result Package Individual - <?php echo $conventionRegD->Conventions['name']; ?> <?php echo $conventionRegD->season_year; ?></span>
                                </div>
                            <?php } ?>

                            <?php
                            $stName = trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name);
                            $studentRows = [];

                            $studentpositions = $this->Resultpositions->find()
                                ->where([
                                    'Resultpositions.user_id' => $userDetails->id,
                                    'Resultpositions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                    'Resultpositions.student_id' => $student->id,
                                    'Resultpositions.position >' => 0,
                                    'Resultpositions.position <=' => 6,
                                ])
                                ->contain(['Events'])
                                ->order(['Resultpositions.position' => 'ASC'])
                                ->all();
                            $renderedEventIds = [];

                            foreach ($studentpositions as $studentpos) {
                                if (empty($studentpos->event_id)) {
                                    continue;
                                }
                                $eventId = (int)$studentpos->event_id;
                                if (in_array($eventId, $renderedEventIds, true)) {
                                    continue;
                                }
                                $renderedEventIds[] = $eventId;

                                $checkStudentCP = false;
                                if (!empty($studentpos->eventsubmission_id)) {
                                    $checkStudentCP = $this->Eventsubmissions->find()->where([
                                        'Eventsubmissions.id' => $studentpos->eventsubmission_id,
                                        'Eventsubmissions.command_performance' => 1,
                                    ])->first();
                                }
                                if (empty($checkStudentCP) && !empty($studentpos->event_id) && !empty($studentpos->student_id)) {
                                    $checkStudentCP = $this->Eventsubmissions->find()->where([
                                        'Eventsubmissions.command_performance' => 1,
                                        'Eventsubmissions.event_id' => $studentpos->event_id,
                                        'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                        'Eventsubmissions.student_id' => $studentpos->student_id,
                                    ])->first();
                                }
                                if (empty($checkStudentCP) && !empty($studentpos->event_id) && !empty($student->id)) {
                                    $checkStudentCP = $this->Eventsubmissions->find()->where([
                                        'Eventsubmissions.command_performance' => 1,
                                        'Eventsubmissions.event_id' => $studentpos->event_id,
                                        'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                        'Eventsubmissions.user_id' => $student->id,
                                    ])->first();
                                }
                                if (empty($checkStudentCP) && !empty($studentpos->event_id) && !empty($conventionRegD->id)) {
                                    $checkStudentCP = $this->Eventsubmissions->find()->where([
                                        'Eventsubmissions.command_performance' => 1,
                                        'Eventsubmissions.event_id' => $studentpos->event_id,
                                        'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                        'Eventsubmissions.conventionregistration_id' => $conventionRegD->id,
                                    ])->first();
                                }

                                $studentRows[] = [
                                    'type' => 'direct',
                                    'place' => $studentpos->position,
                                    'points' => $studentpos->points_obtained,
                                    'event_name' => $studentpos->Events['event_name'],
                                    'event_id_number' => $studentpos->Events['event_id_number'],
                                    'cp_notice' => $checkStudentCP ? '<br><span class="student_cp">This is event was nominated for a Command Performance.</span>' : '',
                                    'slug' => $studentpos->slug,
                                ];
                            }

                            $studentGroupEventsList = $this->Crstudentevents->find()
                                ->where([
                                    'Crstudentevents.user_id' => $userDetails->id,
                                    'Crstudentevents.conventionseason_id' => $conventionRegD->conventionseason_id,
                                    'Crstudentevents.student_id' => $student->id,
                                ])
                                ->contain(['Events'])
                                ->order(['Crstudentevents.id' => 'ASC'])
                                ->all();

                            foreach ($studentGroupEventsList as $studentgrpdetail) {
                                if (empty($studentgrpdetail->event_id)) {
                                    continue;
                                }
                                $eventId = (int)$studentgrpdetail->event_id;
                                if (in_array($eventId, $renderedEventIds, true)) {
                                    continue;
                                }
                                $renderedEventIds[] = $eventId;

                                $groupName = !empty($studentgrpdetail->group_name) ? $studentgrpdetail->group_name : null;
                                $groupConditions = [
                                    'Resultpositions.user_id' => $userDetails->id,
                                    'Resultpositions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                    'Resultpositions.event_id' => $studentgrpdetail->event_id,
                                    'Resultpositions.position >' => 0,
                                    'Resultpositions.position <=' => 6,
                                ];
                                if (!empty($groupName)) {
                                    $groupConditions['Resultpositions.group_name'] = $groupName;
                                }
                                $checkstudentgroupposition = $this->Resultpositions->find()->where($groupConditions)->contain(['Events'])->first();
                                if ($checkstudentgroupposition) {
                                    $groupCheckStudentCP = false;
                                    if (!empty($checkstudentgroupposition->eventsubmission_id)) {
                                        $groupCheckStudentCP = $this->Eventsubmissions->find()->where([
                                            'Eventsubmissions.id' => $checkstudentgroupposition->eventsubmission_id,
                                            'Eventsubmissions.command_performance' => 1,
                                        ])->first();
                                    }
                                    if (empty($groupCheckStudentCP) && !empty($checkstudentgroupposition->event_id) && !empty($studentgrpdetail->student_id)) {
                                        $groupCheckStudentCP = $this->Eventsubmissions->find()->where([
                                            'Eventsubmissions.command_performance' => 1,
                                            'Eventsubmissions.event_id' => $checkstudentgroupposition->event_id,
                                            'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                            'Eventsubmissions.student_id' => $studentgrpdetail->student_id,
                                        ])->first();
                                    }
                                    if (empty($groupCheckStudentCP) && !empty($checkstudentgroupposition->event_id) && !empty($student->id)) {
                                        $groupCheckStudentCP = $this->Eventsubmissions->find()->where([
                                            'Eventsubmissions.command_performance' => 1,
                                            'Eventsubmissions.event_id' => $checkstudentgroupposition->event_id,
                                            'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                            'Eventsubmissions.user_id' => $student->id,
                                        ])->first();
                                    }
                                    if (empty($groupCheckStudentCP) && !empty($checkstudentgroupposition->event_id) && !empty($conventionRegD->id)) {
                                        $groupCheckStudentCP = $this->Eventsubmissions->find()->where([
                                            'Eventsubmissions.command_performance' => 1,
                                            'Eventsubmissions.event_id' => $checkstudentgroupposition->event_id,
                                            'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,
                                            'Eventsubmissions.conventionregistration_id' => $conventionRegD->id,
                                        ])->first();
                                    }

                                    $studentRows[] = [
                                        'type' => 'group',
                                        'place' => 'Group:' . $studentgrpdetail->group_name . ' Place:' . $checkstudentgroupposition->position,
                                        'points' => $checkstudentgroupposition->points_obtained,
                                        'event_name' => $checkstudentgroupposition->Events['event_name'],
                                        'event_id_number' => $checkstudentgroupposition->Events['event_id_number'],
                                        'cp_notice' => $groupCheckStudentCP ? '<br><span class="student_cp">This is event was nominated for a Command Performance.</span>' : '',
                                        'slug' => null,
                                    ];
                                }
                            }

                            $studentPlacingsCount = count($studentRows);
                            ?>

                            <details class="student-result-item" <?php echo $cntrP === 0 ? 'open' : ''; ?>>
                                <summary class="student-result-summary">
                                    <span>
                                        <strong><?php echo h($stName); ?></strong>
                                        <span class="student-result-meta">Birth Year: <?php echo h($student->birth_year); ?> | Gender: <?php echo h($student->gender); ?></span>
                                    </span>
                                    <span class="student-result-badge"><?php echo $studentPlacingsCount; ?> placings</span>
                                </summary>
                                <div class="student-result-body">
                                    <?php if ($studentPlacingsCount > 0) { ?>
                                        <table class="table table-bordered table-condensed student-result-table">
                                            <thead>
                                                <tr>
                                                    <th width="25%">Place</th>
                                                    <th width="25%">Points</th>
                                                    <th width="35%">Event</th>
                                                    <th width="15%">Print</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($studentRows as $row) { ?>
                                                    <tr>
                                                        <td data-title="Place"><?php echo h($row['place']); ?></td>
                                                        <td data-title="Points"><?php echo h($row['points']); ?></td>
                                                        <td data-title="Event"><?php echo h($row['event_name']); ?> (<?php echo h($row['event_id_number']); ?>)<?php echo $row['cp_notice']; ?></td>
                                                        <td data-title="Print">
                                                            <?php if (!empty($row['slug'])) { ?>
                                                                <?php echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'conventionregistrations', 'action' => 'participationcertificatepdf', $row['slug']], ['escape' => false, 'class' => '', 'target' => '_blank', 'title' => 'Print Participation Certificate']); ?>
                                                            <?php } else { ?>
                                                                &nbsp;
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    <?php } else { ?>
                                        <div class="text-muted">No placements found for this student.</div>
                                    <?php } ?>
                                </div>
                            </details>

                            <?php $cntrP++; } ?>
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
