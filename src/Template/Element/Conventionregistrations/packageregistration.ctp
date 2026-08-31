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
                <?php $studentModalId = 'studentModal_' . (int)$datarecord->id; ?>
                <article class="cr-pr-card" data-bs-toggle="modal" data-bs-target="#<?php echo h($studentModalId); ?>" role="button" tabindex="0" aria-label="Open details for <?php echo h($studentName); ?>">
                    <header class="cr-pr-card-header">
                        <div class="cr-pr-card-meta">
                            <h3 class="cr-pr-student-name"><?php echo h($studentName); ?></h3>
                            <p class="cr-pr-student-details">
                                <span>Year of Birth: <?php echo h($datarecord->Students['birth_year']); ?></span>
                                <span>Gender: <?php echo h($datarecord->Students['gender']); ?></span>
                            </p>
                        </div>
                        <div class="cr-pr-card-actions">
                            <span class="cr-pr-count">
                                <span class="cr-pr-submitted-count" data-total="<?php echo (int)$eventCount; ?>">0/<?php echo (int)$eventCount; ?> Submitted</span>
                            </span>
                        </div>
                    </header>

                    <div class="cr-pr-events" aria-hidden="true" style="display:none;">
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
                </article>

                <div class="modal fade cr-pr-detail-modal" id="<?php echo h($studentModalId); ?>" tabindex="-1" aria-labelledby="<?php echo h($studentModalId); ?>Label" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="<?php echo h($studentModalId); ?>Label"><?php echo h($studentName); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="cr-pr-checklist-view">
                                    <div class="cr-pr-modal-meta">
                                        <div class="cr-pr-modal-meta-item">
                                            <span class="cr-pr-modal-meta-label">Year of Birth</span>
                                            <strong class="cr-pr-modal-meta-value"><?php echo h($datarecord->Students['birth_year']); ?></strong>
                                        </div>
                                        <div class="cr-pr-modal-meta-item">
                                            <span class="cr-pr-modal-meta-label">Gender</span>
                                            <strong class="cr-pr-modal-meta-value"><?php echo h($datarecord->Students['gender']); ?></strong>
                                        </div>
                                    </div>
                                    <div class="cr-pr-modal-events-header">
                                        <span>Event Checklist</span>
                                        <strong class="cr-pr-modal-events-count"><?php echo count($studentEventList); ?> event<?php echo count($studentEventList) === 1 ? '' : 's'; ?></strong>
                                    </div>
                                    <div class="cr-pr-modal-events">
                                        <?php foreach($studentEventList as $studentev) { ?>
                                        <?php
                                        $modalRowStateClass = 'is-na';
                                        $modalStatusLabel = 'No Upload Required';
                                        $modalStatusClass = 'is-na';
                                        $modalUploadLink = '';
                                        $modalDivisionName = !empty($studentev->Divisions['name']) ? strtolower($studentev->Divisions['name']) : '';
                                        $modalEventName = !empty($studentev->event_name) ? strtolower($studentev->event_name) : '';
                                        $modalIsAthleticsOrSports = (strpos($modalDivisionName, 'athletic') !== false || strpos($modalDivisionName, 'sport') !== false || strpos($modalEventName, 'athletic') !== false || strpos($modalEventName, 'sport') !== false);

                                        if($studentev->upload_type != 'Nil' || $studentev->report == 1 || $studentev->context_box == 1 || $studentev->score_sheet == 1 || $studentev->additional_documents == 1)
                                        {
                                            $modalCondStudentSubCheck = array();
                                            $modalCondStudentSubCheck[] = "(Eventsubmissions.conventionregistration_id = '".$datarecord->conventionregistration_id."')";
                                            $modalCondStudentSubCheck[] = "(Eventsubmissions.event_id = '".$studentev->id."')";

                                            if($studentev->group_event_yes_no == 1)
                                            {
                                                $modalCheckStudentGroup = $this->Crstudentevents->find()->where([
                                                    'Crstudentevents.conventionregistration_id' => $datarecord->conventionregistration_id,
                                                    'Crstudentevents.student_id' => $datarecord->student_id,
                                                    'Crstudentevents.event_id' => $studentev->id
                                                ])->first();

                                                if(!empty($modalCheckStudentGroup->group_name) && $modalCheckStudentGroup->group_name != NULL)
                                                {
                                                    $modalCondStudentSubCheck[] = "(Eventsubmissions.group_name = '".$modalCheckStudentGroup->group_name."')";
                                                    $modalCheckSubmissionStudent = $this->Eventsubmissions->find()->where($modalCondStudentSubCheck)->count();

                                                    if($modalCheckSubmissionStudent > 0)
                                                    {
                                                        $modalRowStateClass = 'is-sport-complete';
                                                        $modalStatusLabel = 'Grouped & Submitted';
                                                        $modalStatusClass = 'is-complete';
                                                    }
                                                    else
                                                    {
                                                        $modalRowStateClass = 'is-missing';
                                                        $modalStatusLabel = 'Pending Upload';
                                                        $modalStatusClass = 'is-missing';
                                                        $modalUploadLink = $this->Html->link('Upload', ['controller' => 'eventsubmissions', 'action' => 'submitgroupevent', $datarecord->slug, $studentev->slug, $modalCheckStudentGroup->slug], ['escape' => false, 'class' => 'btn btn-primary btn-sm cr-pr-modal-upload-link']);
                                                    }
                                                }
                                                else
                                                {
                                                    $modalRowStateClass = 'is-warning';
                                                    $modalStatusLabel = 'Student Not Grouped';
                                                    $modalStatusClass = 'is-warning';
                                                }
                                            }
                                            else
                                            {
                                                $modalCondStudentSubCheck[] = "(Eventsubmissions.student_id = '".$datarecord->student_id."')";
                                                $modalCheckSubmissionStudent = $this->Eventsubmissions->find()->where($modalCondStudentSubCheck)->count();

                                                if($modalCheckSubmissionStudent > 0)
                                                {
                                                    $modalRowStateClass = 'is-complete';
                                                    $modalStatusLabel = 'Uploaded';
                                                    $modalStatusClass = 'is-complete';
                                                }
                                                else
                                                {
                                                    $modalRowStateClass = 'is-missing';
                                                    $modalStatusLabel = 'Pending Upload';
                                                    $modalStatusClass = 'is-missing';
                                                    $modalUploadLink = $this->Html->link('Upload', ['controller' => 'eventsubmissions', 'action' => 'submitstudentevent', $datarecord->slug, $studentev->slug], ['escape' => false, 'class' => 'btn btn-primary btn-sm cr-pr-modal-upload-link']);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if(!empty($studentev->auto_submission) && (int)$studentev->auto_submission === 1)
                                            {
                                                if((int)$studentev->group_event_yes_no === 1 && $modalIsAthleticsOrSports)
                                                {
                                                    $modalCheckStudentGroup = $this->Crstudentevents->find()->where([
                                                        'Crstudentevents.conventionregistration_id' => $datarecord->conventionregistration_id,
                                                        'Crstudentevents.student_id' => $datarecord->student_id,
                                                        'Crstudentevents.event_id' => $studentev->id
                                                    ])->first();

                                                    if(!empty($modalCheckStudentGroup->group_name) && $modalCheckStudentGroup->group_name != NULL)
                                                    {
                                                        $modalRowStateClass = 'is-sport-complete';
                                                        $modalStatusLabel = 'Grouped & Auto Submitted';
                                                        $modalStatusClass = 'is-complete';
                                                    }
                                                    else
                                                    {
                                                        $modalRowStateClass = 'is-warning';
                                                        $modalStatusLabel = 'Not Grouped Yet';
                                                        $modalStatusClass = 'is-warning';
                                                    }
                                                }
                                                else
                                                {
                                                    $modalRowStateClass = 'is-complete';
                                                    $modalStatusLabel = 'Auto Submitted';
                                                    $modalStatusClass = 'is-complete';
                                                }
                                            }
                                            else
                                            {
                                                $modalRowStateClass = 'is-na';
                                                $modalStatusLabel = 'No Upload Required';
                                                $modalStatusClass = 'is-na';
                                            }
                                        }
                                        ?>
                                        <div class="cr-pr-modal-row <?php echo $modalRowStateClass; ?>">
                                            <div class="cr-pr-modal-row-main">
                                                <span class="cr-pr-modal-event-id"><?php echo h($studentev->event_id_number); ?></span>
                                                <div class="cr-pr-modal-event-details">
                                                    <span class="cr-pr-modal-event-name"><?php echo h($studentev->event_name); ?></span>
                                                    <?php if($studentev->group_event_yes_no == 1) { ?>
                                                        <span class="cr-pr-pill">Group Event</span>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <div class="cr-pr-modal-row-action">
                                                <span class="cr-pr-status <?php echo $modalStatusClass; ?>"><?php echo $modalStatusLabel; ?></span>
                                                <?php echo $modalUploadLink; ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="cr-pr-upload-view" hidden>
                                    <div class="cr-pr-upload-toolbar">
                                        <button type="button" class="btn btn-outline-secondary btn-sm cr-pr-upload-back">Back to Checklist</button>
                                        <a href="#" class="cr-pr-upload-open-new" target="_blank" rel="noopener">Open in new tab</a>
                                    </div>
                                    <iframe class="cr-pr-upload-frame" src="about:blank" loading="lazy" title="Upload Page"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

(function () {
    var isUploadSubmitPath = function (pathname) {
        if (!pathname) {
            return false;
        }

        return pathname.indexOf('/eventsubmissions/submitstudentevent/') !== -1 ||
            pathname.indexOf('/eventsubmissions/submitgroupevent/') !== -1;
    };

    var hasSubmissionSuccessMessage = function (doc) {
        if (!doc) {
            return false;
        }

        var successNode = doc.querySelector('.alert-success, .message.success, .flash-message.success');
        if (!successNode) {
            return false;
        }

        var successText = (successNode.textContent || '').toLowerCase();
        return successText.indexOf('success') !== -1 || successText.indexOf('completed') !== -1 || successText.indexOf('submitted') !== -1;
    };

    var resizeEmbeddedUploadFrame = function (frame) {
        if (!frame || !frame.contentWindow || !frame.contentDocument) {
            return;
        }

        try {
            var doc = frame.contentDocument;
            var root = doc.documentElement;
            var body = doc.body;
            if (!root || !body) {
                return;
            }

            var contentHeight = Math.max(
                root.scrollHeight,
                root.offsetHeight,
                body.scrollHeight,
                body.offsetHeight
            );

            var modalBody = frame.closest('.modal-body');
            var toolbar = frame.closest('.cr-pr-upload-view');
            toolbar = toolbar ? toolbar.querySelector('.cr-pr-upload-toolbar') : null;

            var toolbarHeight = toolbar ? Math.ceil(toolbar.getBoundingClientRect().height) : 0;
            var modalBodyHeight = modalBody ? Math.floor(modalBody.getBoundingClientRect().height) : 0;
            var availableHeight = modalBodyHeight > 0 ? modalBodyHeight - toolbarHeight - 20 : Math.floor(window.innerHeight * 0.72);

            var minHeight = window.innerWidth < 768 ? 320 : 360;
            var maxHeight = Math.max(minHeight, availableHeight);
            var targetHeight = Math.min(maxHeight, Math.max(minHeight, contentHeight + 12));

            frame.style.height = targetHeight + 'px';
            frame.style.minHeight = minHeight + 'px';
            frame.style.maxHeight = maxHeight + 'px';
        } catch (error) {
            // Ignore dynamic sizing errors and keep default frame size.
        }
    };

    var simplifyEmbeddedUploadPage = function (frame) {
        if (!frame || !frame.contentWindow || !frame.contentDocument) {
            return;
        }

        try {
            var doc = frame.contentDocument;
            var win = frame.contentWindow;

            // Enforce predictable iframe sizing even if cached stylesheet is stale.
            frame.style.width = '100%';
            frame.style.display = 'block';
            frame.style.border = '1px solid #d5e3f1';
            frame.style.borderRadius = '10px';

            if (!doc.getElementById('cr-pr-embed-cleanup-style')) {
                var styleEl = doc.createElement('style');
                styleEl.id = 'cr-pr-embed-cleanup-style';
                styleEl.textContent = [
                    'section > nav.navbar, nav.navbar.navbar-expand-lg, #sidebarMenu, footer, .navbar-toggler, #navbarNavDropdown, .header_conv_reg_box { display: none !important; }',
                    '.container-fluid.p-0 > .row { display: block !important; margin: 0 !important; }',
                    '#sidebarMenu { width: 0 !important; max-width: 0 !important; padding: 0 !important; margin: 0 !important; }',
                    '.container-fluid.p-0 > .row > main, main.col-md-9, main.col-lg-10 {',
                    '  width: 100% !important;',
                    '  max-width: 100% !important;',
                    '  flex: 0 0 100% !important;',
                    '  margin: 0 !important;',
                    '  padding-left: 16px !important;',
                    '  padding-right: 16px !important;',
                    '}',
                    'html, body { overflow-x: hidden !important; }',
                    'input[type=file], select, textarea, .form-control { max-width: 100% !important; }',
                    'body { background: #f4f8fd !important; }',
                    '.cr-pr-embed-main { padding: 12px !important; }',
                    '.cr-pr-embed-main main { padding: 0 !important; }',
                    '.cr-pr-embed-main h2.mt-3 { display: none !important; }',
                    '.cr-pr-embed-main .dashboard-form {',
                    '  background: #ffffff !important;',
                    '  border: 1px solid #d8e5f3 !important;',
                    '  border-radius: 12px !important;',
                    '  box-shadow: 0 8px 24px rgba(22, 62, 105, 0.08) !important;',
                    '  padding: 16px !important;',
                    '}',
                    '.cr-pr-embed-main .dashboard-form h2.form-title {',
                    '  margin: 0 0 12px 0 !important;',
                    '  color: #1d446d !important;',
                    '  font-size: 28px !important;',
                    '  font-weight: 700 !important;',
                    '}',
                    '.cr-pr-embed-main .form-group { margin-bottom: 12px !important; }',
                    '.cr-pr-embed-main .form-group { display: block !important; width: 100% !important; clear: both !important; }',
                    '.cr-pr-embed-main .form-group > label {',
                    '  display: block !important;',
                    '  float: none !important;',
                    '  width: 100% !important;',
                    '  font-size: 13px !important;',
                    '  font-weight: 700 !important;',
                    '  color: #355a7f !important;',
                    '  margin-bottom: 6px !important;',
                    '}',
                    '.cr-pr-embed-main .input-multiple {',
                    '  display: block !important;',
                    '  float: none !important;',
                    '  width: 100% !important;',
                    '  background: #f9fcff !important;',
                    '  border: 1px solid #dce8f5 !important;',
                    '  border-radius: 10px !important;',
                    '  padding: 10px 12px !important;',
                    '}',
                    '.cr-pr-embed-main .input-multiple > input,',
                    '.cr-pr-embed-main .input-multiple > select,',
                    '.cr-pr-embed-main .input-multiple > textarea {',
                    '  width: 100% !important;',
                    '  max-width: 100% !important;',
                    '}',
                    '.cr-pr-embed-main .input-multiple .help_text {',
                    '  display: block !important;',
                    '  margin-top: 6px !important;',
                    '  font-size: 12px !important;',
                    '  line-height: 1.45 !important;',
                    '  color: #4d6580 !important;',
                    '}',
                    '.cr-pr-embed-main .class_show_hide > label { display: none !important; }',
                    '.cr-pr-embed-main .form-control {',
                    '  border: 1px solid #b8cde4 !important;',
                    '  border-radius: 8px !important;',
                    '  box-shadow: none !important;',
                    '  color: #1a3f67 !important;',
                    '}',
                    '.cr-pr-embed-main .form-control:focus {',
                    '  border-color: #3f75ad !important;',
                    '  box-shadow: 0 0 0 2px rgba(63, 117, 173, 0.15) !important;',
                    '}',
                    '.cr-pr-embed-main .form-btns {',
                    '  display: flex !important;',
                    '  align-items: center !important;',
                    '  gap: 8px !important;',
                    '}',
                    '.cr-pr-embed-main .form-btns .btn-secondary {',
                    '  background: #24306f !important;',
                    '  border: 1px solid #24306f !important;',
                    '  color: #fff !important;',
                    '  border-radius: 9px !important;',
                    '  padding: 7px 14px !important;',
                    '  font-size: 13px !important;',
                    '  font-weight: 700 !important;',
                    '}',
                    '.cr-pr-embed-main .form-btns .btn-secondary:hover {',
                    '  background: #1c2558 !important;',
                    '  border-color: #1c2558 !important;',
                    '}',
                    '.cr-pr-embed-main .form-btns a[href*="/conventionregistrations/packageregistration"] {',
                    '  display: none !important;',
                    '}',
                    '@media (max-width: 767px) {',
                    '  .cr-pr-embed-main { padding: 8px !important; }',
                    '  .cr-pr-embed-main .dashboard-form { padding: 12px !important; }',
                    '  .cr-pr-embed-main .dashboard-form h2.form-title { font-size: 22px !important; }',
                    '}'
                ].join('\n');
                (doc.head || doc.documentElement).appendChild(styleEl);
            }

            var removeSelectors = [
                'section > nav.navbar',
                'nav.navbar.navbar-expand-lg',
                '.navbar-toggler',
                '#navbarNavDropdown',
                '.header_conv_reg_box',
                '#sidebarMenu',
                'footer'
            ];

            removeSelectors.forEach(function (selector) {
                doc.querySelectorAll(selector).forEach(function (el) {
                    if (el && el.parentNode) {
                        el.parentNode.removeChild(el);
                    }
                });
            });

            // Keep only the main content area in the iframe body.
            var mainContent = doc.querySelector('main.col-md-9, main.col-lg-10, main, .dashboard-form');
            if (mainContent && doc.body) {
                var mainClone = mainContent.cloneNode(true);
                mainClone.querySelectorAll('.form-btns a[href*="/conventionregistrations/packageregistration"]').forEach(function (node) {
                    if (node && node.parentNode) {
                        node.parentNode.removeChild(node);
                    }
                });
                var wrapper = doc.createElement('div');
                wrapper.className = 'cr-pr-embed-main';
                wrapper.style.maxWidth = '100%';
                wrapper.style.margin = '0';
                wrapper.style.padding = '16px';
                wrapper.appendChild(mainClone);
                doc.body.innerHTML = '';
                doc.body.appendChild(wrapper);
            }

            doc.querySelectorAll('.container-fluid.p-0 > .row > main, main.col-md-9, main.col-lg-10').forEach(function (mainEl) {
                mainEl.style.width = '100%';
                mainEl.style.maxWidth = '100%';
                mainEl.style.flex = '0 0 100%';
                mainEl.style.paddingLeft = '16px';
                mainEl.style.paddingRight = '16px';
                mainEl.style.marginLeft = '0';
            });

            doc.querySelectorAll('.container-fluid.p-0 > .row').forEach(function (rowEl) {
                rowEl.style.marginLeft = '0';
                rowEl.style.marginRight = '0';
            });

            if (doc.body) {
                doc.body.style.background = '#ffffff';
            }

            // Keep upload form scrolled into view after layout is simplified.
            win.scrollTo(0, 0);
            resizeEmbeddedUploadFrame(frame);
            setTimeout(function () {
                resizeEmbeddedUploadFrame(frame);
            }, 80);
            setTimeout(function () {
                resizeEmbeddedUploadFrame(frame);
            }, 260);
        } catch (error) {
            // Ignore iframe styling errors and keep default embedded behavior.
        }
    };

    var resetUploadView = function (modal) {
        if (!modal) {
            return;
        }

        var modalBody = modal.querySelector('.modal-body');
        var checklist = modal.querySelector('.cr-pr-checklist-view');
        var uploadView = modal.querySelector('.cr-pr-upload-view');
        var frame = modal.querySelector('.cr-pr-upload-frame');
        var openNew = modal.querySelector('.cr-pr-upload-open-new');

        if (modalBody) {
            modalBody.classList.remove('is-upload-active');
        }
        if (checklist) {
            checklist.hidden = false;
        }
        if (uploadView) {
            uploadView.hidden = true;
        }
        if (frame) {
            frame.dataset.monitorSubmission = '0';
            frame.setAttribute('src', 'about:blank');
            frame.style.height = '';
            frame.style.minHeight = '';
            frame.style.maxHeight = '';
            frame.style.width = '';
            frame.style.display = '';
            frame.style.border = '';
            frame.style.borderRadius = '';
        }
        if (openNew) {
            openNew.setAttribute('href', '#');
        }
    };

    document.addEventListener('click', function (event) {
        var uploadLink = event.target.closest('.cr-pr-modal-upload-link');
        if (uploadLink) {
            event.preventDefault();

            var modal = uploadLink.closest('.cr-pr-detail-modal');
            if (!modal) {
                return;
            }

            var modalBody = modal.querySelector('.modal-body');
            var checklist = modal.querySelector('.cr-pr-checklist-view');
            var uploadView = modal.querySelector('.cr-pr-upload-view');
            var frame = modal.querySelector('.cr-pr-upload-frame');
            var openNew = modal.querySelector('.cr-pr-upload-open-new');

            if (!checklist || !uploadView || !frame) {
                window.location.href = uploadLink.getAttribute('href');
                return;
            }

            frame.onload = function () {
                simplifyEmbeddedUploadPage(frame);

                if (frame.dataset.monitorSubmission !== '1') {
                    return;
                }

                try {
                    var framePath = frame.contentWindow && frame.contentWindow.location ? frame.contentWindow.location.pathname : '';
                    var frameDoc = frame.contentDocument;
                    var shouldRefresh = !isUploadSubmitPath(framePath) || hasSubmissionSuccessMessage(frameDoc);
                    if (shouldRefresh) {
                        frame.dataset.monitorSubmission = '0';
                        window.location.reload();
                    }
                } catch (error) {
                    // Ignore iframe read issues and keep current behavior.
                }
            };
            frame.dataset.monitorSubmission = '1';
            frame.setAttribute('src', uploadLink.getAttribute('href'));
            if (openNew) {
                openNew.setAttribute('href', uploadLink.getAttribute('href'));
            }

            checklist.hidden = true;
            uploadView.hidden = false;
            if (modalBody) {
                modalBody.classList.add('is-upload-active');
            }
            return;
        }

        var backButton = event.target.closest('.cr-pr-upload-back');
        if (backButton) {
            event.preventDefault();
            var parentModal = backButton.closest('.cr-pr-detail-modal');
            resetUploadView(parentModal);
        }
    });

    document.querySelectorAll('.cr-pr-detail-modal').forEach(function (modal) {
        modal.addEventListener('hidden.bs.modal', function () {
            resetUploadView(modal);
        });
    });

    window.addEventListener('resize', function () {
        document.querySelectorAll('.cr-pr-detail-modal.show .cr-pr-upload-frame').forEach(function (frame) {
            resizeEmbeddedUploadFrame(frame);
        });
    });
})();
</script>
