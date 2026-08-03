<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$enableAccordion = isset($enableAccordion) ? (bool)$enableAccordion : false;
$studentCardIndex = 0;
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
                <?php $studentCardIndex++; ?>
                <?php if ($enableAccordion) { ?>
                <details class="cr-pr-card cr-pr-accordion" <?php echo $studentCardIndex === 1 ? 'open' : ''; ?>>
                    <summary class="cr-pr-card-header cr-pr-accordion-toggle">
                <?php } else { ?>
                <article class="cr-pr-card">
                    <header class="cr-pr-card-header">
                <?php } ?>
                        <div class="cr-pr-card-meta">
                            <h3 class="cr-pr-student-name"><?php echo h($studentName); ?></h3>
                            <p class="cr-pr-student-details">
                                <span>Year of Birth: <?php echo h($datarecord->Students['birth_year']); ?></span>
                                <span>Gender: <?php echo h($datarecord->Students['gender']); ?></span>
                            </p>
                        </div>
                        <span class="cr-pr-count">
                            <span class="cr-pr-submitted-count" data-total="<?php echo (int)$eventCount; ?>">0/<?php echo (int)$eventCount; ?> Submitted</span>
                        </span>
                <?php if ($enableAccordion) { ?>
                    </summary>
                <?php } else { ?>
                    </header>
                <?php } ?>

                    <div class="cr-pr-events">
                        <?php foreach($studentEventList as $studentev) { ?>
                        <?php
                        $rowStateClass = 'is-na';
                        $statusLabel = 'No Upload Required';
                        $statusClass = 'is-na';
                        $uploadLink = '';
                        $isSubmissionSatisfied = false;
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
                                        $isSubmissionSatisfied = true;
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
                                    $isSubmissionSatisfied = true;
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
                                        $isSubmissionSatisfied = true;
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
                                    $isSubmissionSatisfied = true;
                                }
                            }
                            else
                            {
                                $rowStateClass = 'is-na';
                                $statusLabel = 'No Upload Required';
                                $statusClass = 'is-na';
                                $isSubmissionSatisfied = true;
                            }
                        }
                        ?>
                        <div class="cr-pr-event-row <?php echo $rowStateClass; ?><?php echo $isSubmissionSatisfied ? ' is-submitted' : ''; ?>">
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
                <?php if ($enableAccordion) { ?>
                </details>
                <?php } else { ?>
                </article>
                <?php } ?>
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

<script>
document.querySelectorAll('.cr-pr-card').forEach(function(card) {
    var submittedNode = card.querySelector('.cr-pr-submitted-count');
    var header = card.querySelector('.cr-pr-card-header');
    var count = card.querySelector('.cr-pr-count');
    if (!submittedNode) {
        return;
    }
    var total = parseInt(submittedNode.getAttribute('data-total') || '0', 10);
    var submitted = card.querySelectorAll('.cr-pr-event-row.is-submitted').length;
    if (!total) {
        total = card.querySelectorAll('.cr-pr-event-row').length;
    }
    submittedNode.textContent = submitted + '/' + total + ' Submitted';
    card.classList.remove('is-complete', 'is-missing');
    if (total && submitted >= total) {
        card.classList.add('is-complete');
        if (header) {
            header.style.background = 'linear-gradient(180deg, #edf9f0 0%, #e6f7ea 100%)';
            header.style.borderBottomColor = '#bfe2c9';
        }
        if (count) {
            count.style.background = '#e7f6ed';
            count.style.borderColor = '#b8dfc6';
            count.style.color = '#1f6a38';
        }
    } else if (total) {
        card.classList.add('is-missing');
        if (header) {
            header.style.background = 'linear-gradient(180deg, #fff1ef 0%, #ffe9e6 100%)';
            header.style.borderBottomColor = '#f2d4cf';
        }
        if (count) {
            count.style.background = '#fff1ef';
            count.style.borderColor = '#f0c4bd';
            count.style.color = '#8d3224';
        }
    }
});

(function () {
    var overallBox = document.getElementById('cr-pr-overall');
    var overallSubmitted = document.getElementById('cr-pr-overall-submitted');
    var overallTotal = document.getElementById('cr-pr-overall-total');
    var overallBar = document.getElementById('cr-pr-overall-bar');
    if (!overallBox || !overallSubmitted || !overallTotal || !overallBar) {
        return;
    }

    var totalRequired = 0;
    var totalSubmitted = 0;

    document.querySelectorAll('.cr-pr-card').forEach(function(card) {
        var submittedNode = card.querySelector('.cr-pr-submitted-count');
        if (!submittedNode) {
            return;
        }

        var total = parseInt(submittedNode.getAttribute('data-total') || '0', 10);
        var submitted = card.querySelectorAll('.cr-pr-event-row.is-submitted').length;
        if (!total) {
            total = card.querySelectorAll('.cr-pr-event-row').length;
        }

        totalRequired += total;
        totalSubmitted += submitted;
    });

    var overallPercent = totalRequired ? Math.round((totalSubmitted / totalRequired) * 100) : 0;
    overallSubmitted.textContent = String(totalSubmitted);
    overallTotal.textContent = String(totalRequired);
    overallBar.style.width = overallPercent + '%';
    overallBar.style.background = totalRequired && totalSubmitted >= totalRequired ? 'linear-gradient(90deg, #2f9150 0%, #5fbe79 100%)' : 'linear-gradient(90deg, #cc4b3c 0%, #e06a5e 100%)';
    overallBar.textContent = overallPercent + '%';
    overallBar.style.color = '#fff';
    overallBar.style.textShadow = '0 1px 1px rgba(0, 0, 0, 0.25)';
    overallBar.setAttribute('aria-valuenow', String(overallPercent));
    overallBar.setAttribute('aria-valuemin', '0');
    overallBar.setAttribute('aria-valuemax', '100');

    if (!totalRequired) {
        overallBox.style.display = 'none';
    }
})();
</script>
