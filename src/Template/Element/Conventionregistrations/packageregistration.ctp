<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
?>

<?php if ($packageregistration) { ?>

    <div class="panel-body cr-pr-panel">
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', 'method' => 'Post']); ?>
        <section class="lstng-section cr-pr-section">
            <div class="cr-pr-list">
                <?php
                foreach ($packageregistration as $datarecord)
                {
                    if (!empty($datarecord->event_ids) && $datarecord->event_ids != NULL)
                    {
                        $condStudentEvents = array();
                        $condStudentEvents[] = '(Events.id IN ('.$datarecord->event_ids.') )';
                        $studentEventList = $this->Events->find()->where($condStudentEvents)->contain(['Divisions'])->order(['Events.event_name' => 'ASC'])->all();

                        $studentName = trim($datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name']);
                        $studentName = $studentName !== '' ? $studentName : 'Student';
                        $eventCount = count(explode(',', $datarecord->event_ids));
                ?>
                <article class="cr-pr-card">
                    <header class="cr-pr-card-header">
                        <div class="cr-pr-card-meta">
                            <h3 class="cr-pr-student-name"><?php echo h($studentName); ?></h3>
                            <p class="cr-pr-student-details">
                                <span>Year of Birth: <?php echo h($datarecord->Students['birth_year']); ?></span>
                                <span>Gender: <?php echo h($datarecord->Students['gender']); ?></span>
                            </p>
                        </div>
                        <span class="cr-pr-count"><?php echo $eventCount; ?> Events Entered</span>
                    </header>

                    <div class="cr-pr-events">
                        <?php foreach($studentEventList as $studentev) { ?>
                        <?php
                        $rowStateClass = 'is-na';
                        $statusLabel = 'No Upload Required';
                        $statusClass = 'is-na';
                        $uploadLink = '';
                        $divisionName = !empty($studentev->Divisions['name']) ? strtolower($studentev->Divisions['name']) : '';
                        $eventName = !empty($studentev->event_name) ? strtolower($studentev->event_name) : '';
                        $isAthleticsOrSports = (strpos($divisionName, 'athletic') !== false || strpos($divisionName, 'sport') !== false || strpos($eventName, 'athletic') !== false || strpos($eventName, 'sport') !== false);

                        if($studentev->upload_type != 'Nil' || $studentev->report == 1 || $studentev->context_box == 1 || $studentev->score_sheet == 1 || $studentev->additional_documents == 1)
                        {
                            $condStudentSubCheck = array();
                            $condStudentSubCheck[] = "(Eventsubmissions.conventionregistration_id = '".$datarecord->conventionregistration_id."')";
                            $condStudentSubCheck[] = "(Eventsubmissions.event_id = '".$studentev->id."')";

                            if($studentev->group_event_yes_no == 1)
                            {
                                $checkStudentGroup = $this->Crstudentevents->find()->where([
                                    'Crstudentevents.conventionregistration_id' => $datarecord->conventionregistration_id,
                                    'Crstudentevents.student_id' => $datarecord->student_id,
                                    'Crstudentevents.event_id' => $studentev->id
                                ])->first();

                                if(!empty($checkStudentGroup->group_name) && $checkStudentGroup->group_name != NULL)
                                {
                                    $condStudentSubCheck[] = "(Eventsubmissions.group_name = '".$checkStudentGroup->group_name."')";
                                    $checkSubmissionStudent = $this->Eventsubmissions->find()->where($condStudentSubCheck)->count();

                                    if($checkSubmissionStudent > 0)
                                    {
                                        $rowStateClass = 'is-sport-complete';
                                        $statusLabel = 'Grouped & Submitted';
                                        $statusClass = 'is-complete';
                                    }
                                    else
                                    {
                                        $rowStateClass = 'is-missing';
                                        $statusLabel = 'Pending Upload';
                                        $statusClass = 'is-missing';
                                        $uploadLink = $this->Html->link('Upload', ['controller' => 'eventsubmissions', 'action' => 'submitgroupevent', $datarecord->slug, $studentev->slug, $checkStudentGroup->slug], ['escape' => false, 'class' => 'btn btn-primary cr-pr-upload-btn']);
                                    }
                                }
                                else
                                {
                                    $rowStateClass = 'is-warning';
                                    $statusLabel = 'Student Not Grouped';
                                    $statusClass = 'is-warning';
                                }
                            }
                            else
                            {
                                $condStudentSubCheck[] = "(Eventsubmissions.student_id = '".$datarecord->student_id."')";
                                $checkSubmissionStudent = $this->Eventsubmissions->find()->where($condStudentSubCheck)->count();

                                if($checkSubmissionStudent > 0)
                                {
                                    $rowStateClass = 'is-complete';
                                    $statusLabel = 'Uploaded';
                                    $statusClass = 'is-complete';
                                }
                                else
                                {
                                    $rowStateClass = 'is-missing';
                                    $statusLabel = 'Pending Upload';
                                    $statusClass = 'is-missing';
                                    $uploadLink = $this->Html->link('Upload', ['controller' => 'eventsubmissions', 'action' => 'submitstudentevent', $datarecord->slug, $studentev->slug], ['escape' => false, 'class' => 'btn btn-primary cr-pr-upload-btn']);
                                }
                            }
                        }
                        else
                        {
                            if(!empty($studentev->auto_submission) && (int)$studentev->auto_submission === 1)
                            {
                                if((int)$studentev->group_event_yes_no === 1 && $isAthleticsOrSports)
                                {
                                    $checkStudentGroup = $this->Crstudentevents->find()->where([
                                        'Crstudentevents.conventionregistration_id' => $datarecord->conventionregistration_id,
                                        'Crstudentevents.student_id' => $datarecord->student_id,
                                        'Crstudentevents.event_id' => $studentev->id
                                    ])->first();

                                    if(!empty($checkStudentGroup->group_name) && $checkStudentGroup->group_name != NULL)
                                    {
                                        $rowStateClass = 'is-sport-complete';
                                        $statusLabel = 'Grouped & Auto Submitted';
                                        $statusClass = 'is-complete';
                                    }
                                    else
                                    {
                                        $rowStateClass = 'is-warning';
                                        $statusLabel = 'Not Grouped Yet';
                                        $statusClass = 'is-warning';
                                    }
                                }
                                else
                                {
                                    $rowStateClass = 'is-complete';
                                    $statusLabel = 'Auto Submitted';
                                    $statusClass = 'is-complete';
                                }
                            }
                            else
                            {
                                $rowStateClass = 'is-na';
                                $statusLabel = 'No Upload Required';
                                $statusClass = 'is-na';
                            }
                        }
                        ?>
                        <div class="cr-pr-event-row <?php echo $rowStateClass; ?>">
                            <div class="cr-pr-event-id"><?php echo h($studentev->event_id_number); ?></div>
                            <div class="cr-pr-event-name">
                                <?php echo h($studentev->event_name); ?>
                                <?php if($studentev->group_event_yes_no == 1) { ?>
                                    <span class="cr-pr-pill">Group Event</span>
                                <?php } ?>
                            </div>
                            <div class="cr-pr-event-action">
                                <span class="cr-pr-status <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                <?php echo $uploadLink; ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </article>
                <?php
                    }
                }
                ?>
            </div>
        </section>
        <?php echo $this->Form->end(); ?>
    </div>

<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record cr-pr-empty">No student event found.</div>
<?php }
?>
