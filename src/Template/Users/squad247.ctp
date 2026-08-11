<?php
$regionsLeft = isset($squad247Data['regions_left']) && is_array($squad247Data['regions_left']) ? $squad247Data['regions_left'] : array();
$regionsRight = isset($squad247Data['regions_right']) && is_array($squad247Data['regions_right']) ? $squad247Data['regions_right'] : array();
$fees = isset($squad247Data['fees']) && is_array($squad247Data['fees']) ? $squad247Data['fees'] : array();
$successRequirements = isset($squad247Data['success_requirements']) && is_array($squad247Data['success_requirements']) ? $squad247Data['success_requirements'] : array(
    'Be at least 17 years of age (if under 18 and staying on site, events team will contact parent/guardian).',
    'Will not be eligible to compete as a student at the Convention.',
    'Will not have responsibility as a sponsor or accompanist for children or students attending Convention.',
    'Will be willing to volunteer their time 24/7 during Convention in any area (judging, stage handling, sound/audio, general help).',
    'Be able to arrive prior to check-in and remain until after awards and campsite clean-up.',
    'Be flexible in sleeping arrangements.',
    'Be passionate about the purpose and ministry of Student Conventions.',
);
$successfulNeedTo = isset($squad247Data['successful_need_to']) && is_array($squad247Data['successful_need_to']) ? $squad247Data['successful_need_to'] : array(
    'Cover their own transport expenses and registration fee listed on the application form.',
    'Registration fee for PNG, Fiji, Cook Islands, Solomon Islands and AUS workshop includes convention registration, food, 24/7 pin and 24/7 T-shirt.',
    'Registration fee for Indonesia and NZ includes convention registration, onsite accommodation and food, and 24/7 T-shirt.',
    'If travelling internationally, check with Events Coordinator for extra food, accommodation and transport cost estimates.',
    'Be prepared to use any musical or platform gifts at evening rallies.',
);
$applicantMustProvide = isset($squad247Data['applicant_must_provide']) && is_array($squad247Data['applicant_must_provide']) ? $squad247Data['applicant_must_provide'] : array(
    'A current portrait photo.',
    'A reference from Principal and/or Pastor.',
    'Personal testimony of salvation and current walk with the Lord.',
    'A description of church background and beliefs.',
);
$blueCardRequirement = trim((string)($squad247Data['blue_card_requirement'] ?? 'For Australian/NZ conventions, Australian volunteers at Australian conventions must hold an approved Blue Card (or the relevant state Working With Children approval). Applicants can apply via bluecard.qld.gov.au.'));
$paymentOptions = trim((string)($squad247Data['payment_options'] ?? 'Once the application is received, specific payment details are sent based on the country selected.'));
$conventionSubtitle = trim((string)($squad247Data['convention_subtitle'] ?? 'Regional Student Conventions 2025'));
if ($conventionSubtitle === '') {
    $conventionSubtitle = 'Regional Student Conventions 2025';
}
?>

<?php echo $this->Html->script('jquery-ui.min.js'); ?>
<?php echo $this->Html->css('themes/base/jquery.ui.datepicker.css'); ?>

<style>
.s247-wrap {
    background: linear-gradient(135deg, #1c2452 0%, #0f1633 60%, #1a3a5c 100%);
    padding: 30px 0 50px;
    min-height: 100vh;
    overflow: hidden;
    position: relative;
}
.s247-wrap .container {
    position: relative;
    z-index: 2;
}
.s247-particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    pointer-events: none;
}
.s247-wave {
    position: absolute;
    z-index: 1;
    pointer-events: none;
}
.s247-wave-left {
    top: 0;
    left: 0;
}
.s247-wave-right {
    right: 0;
    bottom: 0;
}
.s247-wave img {
    display: block;
    max-width: 100%;
    height: auto;
}
.s247-card {
    background: rgba(255, 255, 255, 0.96);
    border: 1px solid #d7e1f2;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 12px 30px rgba(21, 43, 88, 0.09);
}
.s247-kicker {
    font-size: 25px;
    letter-spacing: 0.08em;
    color: #6a7282;
    text-transform: uppercase;
    margin-bottom: 8px;
    text-align: center;
}
.s247-title {
    margin: 0;
    color: #1f2d4f;
    font-size: 20px;
    font-weight: 700;
    line-height: 1.1;
    text-align: center;
}
.s247-step {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border: 1px solid #b8c9ec;
    background: #eef4ff;
    color: #1f3f75 !important;
    padding: 7px 13px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    opacity: 1 !important;
}
.s247-step:hover {
    background: #dce8ff;
    color: #123166 !important;
}
.s247-step:visited,
.s247-step:focus,
.s247-step:active {
    color: #1f3f75 !important;
    text-decoration: none;
}
.s247-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 20px;
}
.s247-box {
    border: 1px solid #dbe5f7;
    border-radius: 12px;
    padding: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    box-shadow: 0 4px 14px rgba(18, 44, 87, 0.06);
    color: #223457;
}
.s247-box-title {
    margin: 0 0 10px;
    color: #1f2d4f;
    font-size: 28px;
    font-weight: 700;
    position: relative;
    display: inline-block !important;
    width: max-content !important;
    padding-bottom: 8px;
    --s247-underline-width: 100%;
}
.s247-box-title:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: var(--s247-underline-width);
    height: 3px;
    border-radius: 999px;
    background: linear-gradient(90deg, #2f8f4f 0%, #4da5c9 100%);
}
.s247-box-title--fees {
    --s247-underline-width: 100%;
}
.s247-copy-block {
    color: #1f2d4f;
    margin-top: 14px;
    margin-bottom: 8px;
}
.s247-copy-block p,
.s247-copy-block h3,
.s247-copy-block h4 {
    color: #1f2d4f;
}
.s247-copy-block h3 {
    margin: 20px 0 10px;
    font-size: 24px;
}
.s247-copy-block h4 {
    margin: 16px 0 8px;
    font-size: 17px;
}
.s247-copy-list {
    margin: 0 0 12px;
    padding-left: 18px;
    color: #1f2d4f;
}
.s247-copy-list li {
    margin-bottom: 6px;
    line-height: 1.45;
}
.s247-copy-accordion {
    border: 1px solid #d8e2f3;
    border-radius: 10px;
    background: #fff;
    margin: 10px 0;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(24, 52, 99, 0.05);
}
.s247-copy-accordion > summary {
    list-style: none;
    cursor: pointer;
    padding: 11px 13px;
    font-weight: 700;
    color: #1d335f;
    background: linear-gradient(180deg, #f7faff 0%, #eef4ff 100%);
    border-bottom: 1px solid #e0e9f8;
}
.s247-copy-accordion > summary::-webkit-details-marker {
    display: none;
}
.s247-copy-accordion > summary:after {
    content: '+';
    float: right;
    font-size: 18px;
    line-height: 1;
}
.s247-copy-accordion[open] > summary:after {
    content: '-';
}
.s247-copy-accordion-body {
    padding: 12px 14px 4px;
}
.s247-copy-note {
    margin-top: 10px;
    padding: 12px 14px;
    border-left: 4px solid #d6b23c;
    background: #f7f9ff;
    color: #1f2d4f;
    border-radius: 10px;
}
.s247-list {
    margin: 0;
    padding-left: 0;
    list-style: none;
    color: #2b3b5d;
}
.s247-list li {
    margin-bottom: 6px;
    color: #2b3b5d;
    font-size: 14px;
    line-height: 1.45;
    position: relative;
    padding-left: 16px;
}
.s247-list li:before {
    content: '';
    position: absolute;
    left: 0;
    top: 9px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #4f75c8;
    box-shadow: 0 0 0 2px rgba(79, 117, 200, 0.2);
}
.s247-form {
    margin-top: 18px;
    border-top: 1px solid #dde6f7;
    padding-top: 22px;
}
.s247-progress-wrap {
    margin: 0 0 16px;
    border: 1px solid #d8e3f6;
    border-radius: 12px;
    background: linear-gradient(180deg, #fbfdff 0%, #f3f8ff 100%);
    padding: 12px 14px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
}
.s247-progress-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    color: #2a3f66;
    margin-bottom: 9px;
    font-weight: 600;
}
.s247-progress-bar {
    height: 10px;
    border-radius: 999px;
    background: #dfe8f8;
    overflow: hidden;
}
.s247-progress-fill {
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, #2f8f4f 0%, #5eb774 100%);
    transition: width .25s ease;
}
.s247-accordion-item {
    border: 1px solid #d8e2f3;
    border-radius: 12px;
    background: #fff;
    margin-bottom: 13px;
    overflow: hidden;
    box-shadow: 0 3px 12px rgba(24, 52, 99, 0.05);
}
.s247-accordion-item > summary {
    list-style: none;
    cursor: pointer;
    padding: 13px 14px;
    font-weight: 700;
    color: #1d335f;
    background: linear-gradient(180deg, #f7faff 0%, #eef4ff 100%);
    border-bottom: 1px solid #e0e9f8;
    transition: background .2s ease;
}
.s247-accordion-item > summary:hover {
    background: linear-gradient(180deg, #f1f6ff 0%, #e8f0ff 100%);
}
.s247-summary-badge {
    float: right;
    margin-right: 24px;
    font-size: 12px;
    font-weight: 600;
    color: #36558d;
    background: #e3edff;
    border: 1px solid #c8d7f3;
    border-radius: 999px;
    padding: 2px 9px;
}
.s247-required {
    color: #c84848;
    font-weight: 700;
    margin-left: 2px;
}
.s247-accordion-item > summary::-webkit-details-marker {
    display: none;
}
.s247-accordion-item[open] > summary {
    background: linear-gradient(180deg, #eef5ff 0%, #e5eefe 100%);
}
.s247-accordion-item > summary:after {
    content: '+';
    float: right;
    font-size: 18px;
    line-height: 1;
}
.s247-accordion-item[open] > summary:after {
    content: '-';
}
.s247-section {
    border: 0;
    border-radius: 0;
    background: linear-gradient(180deg, #ffffff 0%, #fcfdff 100%);
    padding: 14px;
    margin-bottom: 0;
}
.s247-section h4 {
    margin: 0 0 10px;
    color: #1d335f;
    font-size: 17px;
}
.s247-section p {
    color: #3f4f6b;
}
.s247-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}
.s247-form .full {
    grid-column: 1 / -1;
}
.s247-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #293752;
}
.s247-form input,
.s247-form textarea,
.s247-form select {
    width: 100%;
    border: 1px solid #c8d5ee;
    border-radius: 9px;
    padding: 10px 11px;
    font-size: 14px;
    background: #fff;
    color: #223457;
    transition: border-color .2s ease, box-shadow .2s ease;
}
.s247-form input:focus,
.s247-form textarea:focus,
.s247-form select:focus {
    outline: none;
    border-color: #4f75c8;
    box-shadow: 0 0 0 3px rgba(79, 117, 200, 0.15);
}
.s247-form textarea {
    min-height: 105px;
    resize: vertical;
}
.s247-checkgrid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px 12px;
}
.s247-inline-check {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
}
.s247-inline-check label,
.s247-checkgrid label {
    font-weight: 500;
    margin-bottom: 0;
    color: #334766;
}
.s247-grid .s247-box:last-child {
    display: flex;
    flex-direction: column;
}
.s247-grid .s247-box:last-child .s247-note {
    margin-top: auto;
}
.s247-submit {
    margin-top: 15px;
    border: 0;
    background: linear-gradient(90deg, #1f2d4f 0%, #2f4f87 100%);
    color: #fff;
    border-radius: 10px;
    padding: 12px 18px;
    font-weight: 600;
    box-shadow: 0 6px 16px rgba(24, 46, 89, 0.25);
    transition: transform .15s ease, box-shadow .2s ease;
}
.s247-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(24, 46, 89, 0.3);
}
.s247-sticky-actions {
    display: none;
}
#ui-datepicker-div {
    z-index: 99999 !important;
    border: 1px solid #d4e1f7;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 14px 30px rgba(16, 32, 66, 0.22);
    padding: 8px;
    font-family: poppinsregular, sans-serif;
    min-width: 21em;
}
#ui-datepicker-div .ui-datepicker-header {
    position: relative;
    background: linear-gradient(180deg, #f6f9ff 0%, #ecf3ff 100%);
    border: 1px solid #d6e2f7;
    border-radius: 8px;
    color: #1f3560;
    font-weight: 700;
    padding: 4px 0;
}
#ui-datepicker-div .ui-datepicker-title {
    color: #1f3560;
    margin: 0 2.6em;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
#ui-datepicker-div .ui-datepicker-title select {
    width: auto !important;
    height: auto;
    border: 1px solid #cddbf5;
    border-radius: 6px;
    padding: 1px 4px;
    background: #fff;
    color: #1f3560;
    font-size: 13px;
}
#ui-datepicker-div .ui-datepicker-prev,
#ui-datepicker-div .ui-datepicker-next {
    top: 50%;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    border: 1px solid #cddbf5;
    border-radius: 50%;
    background: #fff;
    text-indent: -9999px;
    overflow: hidden;
}
#ui-datepicker-div .ui-datepicker-prev {
    left: 6px;
}
#ui-datepicker-div .ui-datepicker-next {
    right: 6px;
}
#ui-datepicker-div .ui-datepicker-prev:before,
#ui-datepicker-div .ui-datepicker-next:before {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -54%);
    text-indent: 0;
    color: #355a95;
    font-size: 18px;
    font-weight: 700;
    line-height: 1;
}
#ui-datepicker-div .ui-datepicker-prev:before {
    content: '\2039';
}
#ui-datepicker-div .ui-datepicker-next:before {
    content: '\203A';
}
#ui-datepicker-div .ui-datepicker-prev span,
#ui-datepicker-div .ui-datepicker-next span {
    display: none;
}
#ui-datepicker-div .ui-datepicker-calendar th {
    color: #355383;
    font-weight: 700;
    padding: 6px 2px;
}
#ui-datepicker-div .ui-datepicker-calendar td {
    padding: 1px;
}
#ui-datepicker-div .ui-state-default,
#ui-datepicker-div .ui-widget-content .ui-state-default {
    border: 1px solid transparent;
    background: #fff;
    color: #223b68 !important;
    text-align: center;
    border-radius: 8px;
    padding: 7px 0;
}
#ui-datepicker-div .ui-state-default:hover,
#ui-datepicker-div .ui-widget-content .ui-state-default:hover {
    background: #eaf1ff;
    border-color: #cddcf6;
    color: #1f3560 !important;
}
#ui-datepicker-div .ui-state-highlight,
#ui-datepicker-div .ui-widget-content .ui-state-highlight {
    background: #f2f7ff;
    border-color: #d6e3f8;
    color: #1f3560 !important;
}
#ui-datepicker-div .ui-state-active,
#ui-datepicker-div .ui-widget-content .ui-state-active {
    background: #2f4f87;
    border-color: #274472;
    color: #fff !important;
}
#ui-datepicker-div .ui-datepicker-current-day .ui-state-default {
    background: #2f4f87;
    color: #fff !important;
}
#ui-datepicker-div .ui-datepicker-unselectable .ui-state-default,
#ui-datepicker-div .ui-state-disabled,
#ui-datepicker-div .ui-widget-content .ui-state-disabled {
    opacity: .45;
    color: #7f91b3 !important;
}
@media (max-width: 900px) {
    .s247-grid,
    .s247-form-grid,
    .s247-checkgrid {
        grid-template-columns: 1fr;
    }
    .s247-title {
        font-size: 26px;
    }
    .s247-card {
        padding: 16px;
    }
    .s247-box h4 {
        font-size: 24px;
    }
    .s247-wrap {
        padding-bottom: 50px;
    }
}
</style>

<section class="s247-wrap">
    <canvas id="s247-particles" class="s247-particles"></canvas>
    <div class="s247-wave s247-wave-left">
        <img src="/img/front/left-element.png" alt="">
    </div>
    <div class="s247-wave s247-wave-right">
        <img src="/img/front/ryt-element.png" alt="">
    </div>
    <div class="container">
        <div class="s247-card">
            <div class="s247-kicker">24/7 Squad</div>
            <h1 class="s247-title">Regional Application Form</h1>
            <p class="s247-subtitle"><?php echo h($conventionSubtitle); ?></p>
            <div class="s247-steps">
                <a class="s247-step" href="#s247-step-1" data-step-target="s247-step-1">1. Personal Details</a>
                <a class="s247-step" href="#s247-step-2" data-step-target="s247-step-2">2. Convention Selection</a>
                <a class="s247-step" href="#s247-step-3" data-step-target="s247-step-3">3. Blue Card</a>
                <a class="s247-step" href="#s247-step-4" data-step-target="s247-step-4">4. Service History</a>
                <a class="s247-step" href="#s247-step-6" data-step-target="s247-step-6">5. Declaration</a>
            </div>

            <div class="s247-grid">
                <div class="s247-box">
                    <h4 class="s247-box-title s247-box-title--dates">Convention Dates</h4>
                    <ul class="s247-list">
                        <?php foreach ($regionsLeft as $row) { ?>
                            <li><?php echo h(($row['name'] ?? '') . ': ' . ($row['dates'] ?? '')); ?></li>
                        <?php } ?>
                        <?php foreach ($regionsRight as $row) { ?>
                            <li><?php echo h(($row['name'] ?? '') . ': ' . ($row['dates'] ?? '')); ?></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="s247-box">
                    <h4 class="s247-box-title s247-box-title--fees">Convention Fees</h4>
                    <ul class="s247-list">
                        <?php foreach ($fees as $feeRow) { ?>
                            <li><?php echo h(($feeRow['name'] ?? '') . ': ' . ($feeRow['amount'] ?? '')); ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <div class="s247-copy-block">
                <p><?php echo h($squad247Data['application_intro'] ?? 'Southern Cross Educational Enterprises is seeking applications from A.C.E. Graduates and supporters to join the 2025 Regional Convention 24/7 Squads in the following regions:'); ?></p>

                <details class="s247-copy-accordion" open>
                    <summary>Successful Applicants will meet the following requirements:</summary>
                    <div class="s247-copy-accordion-body">
                        <ul class="s247-copy-list">
                            <?php foreach ($successRequirements as $line) { ?>
                                <li><?php echo h($line); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </details>

                <details class="s247-copy-accordion">
                    <summary>Approved Applicants Will Need To:</summary>
                    <div class="s247-copy-accordion-body">
                        <ul class="s247-copy-list">
                            <?php foreach ($successfulNeedTo as $line) { ?>
                                <li><?php echo h($line); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </details>

                <details class="s247-copy-accordion">
                    <summary>Applicant Must Provide:</summary>
                    <div class="s247-copy-accordion-body">
                        <ul class="s247-copy-list">
                            <?php foreach ($applicantMustProvide as $line) { ?>
                                <li><?php echo h($line); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </details>

                <details class="s247-copy-accordion">
                    <summary>Blue Card / Working With Children Requirement:</summary>
                    <div class="s247-copy-accordion-body">
                        <p><?php echo h($blueCardRequirement); ?></p>
                    </div>
                </details>

                <details class="s247-copy-accordion">
                    <summary>Payment Options:</summary>
                    <div class="s247-copy-accordion-body">
                        <p style="margin-bottom:8px;"><?php echo h($paymentOptions); ?></p>
                    </div>
                </details>
            </div>

            <form class="s247-form" id="s247-form" onsubmit="return false;">
                <div class="s247-progress-wrap">
                    <div class="s247-progress-meta">
                        <span>Application Progress</span>
                        <strong id="s247-progress-text">0% Complete</strong>
                    </div>
                    <div class="s247-progress-bar">
                        <div class="s247-progress-fill" id="s247-progress-fill"></div>
                    </div>
                </div>
                <details class="s247-accordion-item" id="s247-step-1" open>
                    <summary>1. Personal Details</summary>
                    <div class="s247-section">
                    <div class="s247-form-grid">
                        <div>
                            <label>Full Name <span class="s247-required">*</span></label>
                            <input type="text" data-required="1" placeholder="Enter full name">
                        </div>
                        <div>
                            <label>Age <span class="s247-required">*</span></label>
                            <input type="number" min="1" data-required="1" placeholder="Age">
                        </div>
                        <div class="full">
                            <label>Gender</label>
                            <div class="s247-inline-check">
                                <label><input type="radio" name="s247_gender" style="width:auto; margin-right:6px;" checked>Male</label>
                                <label><input type="radio" name="s247_gender" style="width:auto; margin-right:6px;">Female</label>
                            </div>
                        </div>
                        <div>
                            <label>Country</label>
                            <input type="text" placeholder="Country">
                        </div>
                        <div>
                            <label>Phone</label>
                            <input type="text" placeholder="Phone">
                        </div>
                        <div class="full">
                            <label>Email <span class="s247-required">*</span></label>
                            <input type="email" data-required="1" placeholder="Email">
                        </div>
                        <div class="full">
                            <label>A.C.E. School / HSSP you attend(ed) as a student</label>
                            <input type="text" placeholder="School / HSSP">
                        </div>
                    </div>
                    </div>
                </details>

                <details class="s247-accordion-item" id="s247-step-2">
                    <summary>2. Convention(s) Applying For</summary>
                    <div class="s247-section">
                    <div class="s247-form-grid">
                        <div class="full">
                            <div class="s247-checkgrid">
                                <?php foreach ($fees as $idx => $feeRow) { ?>
                                    <label><input type="checkbox" data-required-group="conventions" style="width:auto; margin-right:6px;" <?php if ($idx === 0) { ?>checked<?php } ?>><?php echo h(($feeRow['name'] ?? '') . ' (' . ($feeRow['amount'] ?? '') . ')'); ?></label>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="full">
                            <label>Please state your reasons for wanting to attend <span class="s247-required">*</span></label>
                            <textarea data-required="1" placeholder="Your reasons"></textarea>
                        </div>
                        <div class="full">
                            <label>Convention experience and items you are willing to perform <span class="s247-required">*</span></label>
                            <textarea data-required="1" placeholder="Please list your Convention experience and items you are willing to perform (attach a separate list if more space is required)"></textarea>
                        </div>
                    </div>
                    </div>
                </details>

                <details class="s247-accordion-item" id="s247-step-3">
                    <summary>3. Blue Card (Australian / NZ Conventions only)</summary>
                    <div class="s247-section">
                    <p>
                        It is required by law that Australian volunteers at Australian Conventions are approved "Blue Card" holders
                        (or hold the relevant Working With Children approval from your state). Visit bluecard.qld.gov.au, download
                        the "Blue Card application (BC) form," complete it, and send it to us with this application, allow six weeks or more for processing.
                    </p>
                    <div class="s247-form-grid">
                        <div class="full">
                            <p style="margin:0 0 8px; color:#394760; font-size:13px;"><strong>Required:</strong> choose one option below, or upload your Blue Card application form.</p>
                            <label class="s247-inline-check"><span><input type="checkbox" id="s247_bluecard_current" data-required-group="bluecard" style="width:auto; margin-right:6px;">I have a current Blue Card</span></label>
                            <label class="s247-inline-check" style="margin-top:8px;"><span><input type="checkbox" id="s247_bluecard_outside" data-required-group="bluecard" style="width:auto; margin-right:6px;"> <strong>I am applying for a 24/7 squad outside of Australia &amp; NZ (Blue Card not required)</strong></span></label>
                        </div>
                        <div class="s247-bluecard-current-field">
                            <label>Blue Card Number</label>
                            <input type="text" placeholder="Blue Card Number">
                        </div>
                        <div class="s247-bluecard-current-field">
                            <label>Blue Card Expiry Date</label>
                            <input type="text" class="s247-date-input" placeholder="Blue Card Expiry Date">
                        </div>
                        <div class="full s247-bluecard-upload-field">
                            <label>Upload your Blue Card application form (if not already held)</label>
                            <input type="file" data-required-group="bluecard">
                        </div>
                    </div>
                    </div>
                </details>

                <details class="s247-accordion-item" id="s247-step-4">
                    <summary>4. Squad Service History</summary>
                    <div class="s247-section">
                    <div class="s247-form-grid">
                        <div class="full">
                            <div class="s247-inline-check">
                                <label><input type="radio" id="s247_history_new" name="s247_history" value="new" style="width:auto; margin-right:6px;" checked>I have not previously served as a 24/7 Squad Member</label>
                                <label><input type="radio" id="s247_history_served" name="s247_history" value="served" style="width:auto; margin-right:6px;">I have served as a 24/7 Squad Member before</label>
                            </div>
                        </div>
                        <div class="full s247-served-year">
                            <label>If you have served before, what year?</label>
                            <input type="text" placeholder="Year">
                        </div>
                        <div class="full s247-first-time-fields">
                            <p style="margin:0 0 8px;"><strong>If this is your first time applying, please attach the following:</strong></p>
                            <label>1. A current portrait photo of yourself <span class="s247-required">*</span></label>
                            <input type="file" data-required="1">
                            <label style="margin-top:8px;">2. A reference from your Principal and/or Pastor <span class="s247-required">*</span></label>
                            <input type="file" data-required="1">
                            <label style="margin-top:8px;">3. Your personal testimony of salvation</label>
                            <textarea placeholder="Your testimony"></textarea>
                            <label style="margin-top:8px;">4. A description of your church background and beliefs</label>
                            <textarea placeholder="Church background and beliefs"></textarea>
                        </div>
                        <div class="full s247-served-fields">
                            <p style="margin:0 0 8px;"><strong>If you have served before, please attach:</strong></p>
                            <label>A current testimony of your walk with the Lord</label>
                            <textarea placeholder="Current testimony"></textarea>
                        </div>
                    </div>
                    </div>
                </details>

                <details class="s247-accordion-item" id="s247-step-5">
                    <summary>5. Dietary Requirements</summary>
                    <div class="s247-section">
                    <div class="s247-form-grid">
                        <div class="full">
                            <p style="margin:0 0 8px; color:#394760; font-size:13px;"><strong>Required:</strong> please select one option.</p>
                            <label class="s247-inline-check"><span><input type="checkbox" id="s247_has_dietary_requirements" data-required-group="dietary" style="width:auto; margin-right:6px;">I have special dietary requirements or allergies</span></label>
                            <label class="s247-inline-check" style="margin-top:8px;"><span><input type="checkbox" id="s247_no_dietary_requirements" data-required-group="dietary" style="width:auto; margin-right:6px;">No dietary requirements</span></label>
                        </div>
                        <div class="full">
                            <label>Please state your requirements</label>
                            <textarea placeholder="Dietary requirements"></textarea>
                        </div>
                    </div>
                    </div>
                </details>

                <details class="s247-accordion-item" id="s247-step-6">
                    <summary>6. Declaration</summary>
                    <div class="s247-section">
                    <div class="s247-form-grid">
                        <div class="full">
                            <label>Applicant's Full Name (acts as your signature/confirmation)</label>
                            <input type="text" data-required="1" placeholder="Applicant's Full Name">
                        </div>
                        <div>
                            <label>Date</label>
                            <input type="text" class="s247-date-input" data-required="1" placeholder="Date">
                        </div>
                        <div class="full">
                            <p style="margin:6px 0 8px; color:#394760;">If the applicant is under 18, a parent/guardian must also confirm below:</p>
                        </div>
                        <div>
                            <label>Parent/Guardian Name</label>
                            <input type="text" placeholder="Parent/Guardian Name">
                        </div>
                        <div>
                            <label>Parent/Guardian Confirmation (acts as signature)</label>
                            <input type="text" placeholder="Parent/Guardian Confirmation">
                        </div>
                        <div>
                            <label>Date</label>
                            <input type="text" class="s247-date-input" placeholder="Date">
                        </div>
                    </div>
                    </div>
                </details>
                <div id="s247-submit-status" style="margin-top:10px; color:#2a3f66; font-weight:600;"></div>
                <button class="s247-submit" type="submit">Submit application</button>
            </form>
        </div>
    </div>
</section>

<script>
(function(){
    var canvas = document.getElementById('s247-particles');
    var ctx = canvas ? canvas.getContext('2d') : null;
    var particles = [];
    var count = 70;

    function resizeCanvas(){
        if (!canvas) return;
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    }

    function initParticles(){
        if (!canvas || !ctx) return;
        particles = [];
        for (var i = 0; i < count; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                r: Math.random() * 2 + 0.5,
                dx: (Math.random() - 0.5) * 0.4,
                dy: (Math.random() - 0.5) * 0.4,
                alpha: Math.random() * 0.5 + 0.1
            });
        }
    }

    function drawParticles(){
        if (!canvas || !ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        particles.forEach(function(p){
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(120,160,255,' + p.alpha + ')';
            ctx.fill();
            p.x += p.dx;
            p.y += p.dy;
            if (p.x < 0) p.x = canvas.width;
            if (p.x > canvas.width) p.x = 0;
            if (p.y < 0) p.y = canvas.height;
            if (p.y > canvas.height) p.y = 0;
        });
        requestAnimationFrame(drawParticles);
    }

    if (canvas && ctx) {
        resizeCanvas();
        initParticles();
        window.addEventListener('resize', function(){
            resizeCanvas();
            initParticles();
        });
        drawParticles();
    }

    function setDisplay(elements, show) {
        Array.prototype.forEach.call(elements, function(el) {
            el.style.display = show ? '' : 'none';
        });
    }

    var blueCardCheckbox = document.getElementById('s247_bluecard_current');
    var blueCardOutsideCheckbox = document.getElementById('s247_bluecard_outside');
    function syncBlueCardFields() {
        if (!blueCardCheckbox) return;

        var isOutsideAuNz = !!(blueCardOutsideCheckbox && blueCardOutsideCheckbox.checked);
        if (isOutsideAuNz && blueCardCheckbox.checked) {
            blueCardCheckbox.checked = false;
        }

        if (isOutsideAuNz) {
            setDisplay(document.querySelectorAll('.s247-bluecard-current-field'), false);
            setDisplay(document.querySelectorAll('.s247-bluecard-upload-field'), false);
            return;
        }

        var hasCard = !!blueCardCheckbox.checked;
        setDisplay(document.querySelectorAll('.s247-bluecard-current-field'), hasCard);
        setDisplay(document.querySelectorAll('.s247-bluecard-upload-field'), !hasCard);
    }

    var historyNew = document.getElementById('s247_history_new');
    var historyServed = document.getElementById('s247_history_served');
    var hasDietaryRequirementsCheckbox = document.getElementById('s247_has_dietary_requirements');
    var noDietaryRequirementsCheckbox = document.getElementById('s247_no_dietary_requirements');
    function syncHistoryFields() {
        var served = historyServed && historyServed.checked;
        setDisplay(document.querySelectorAll('.s247-served-year'), served);
        setDisplay(document.querySelectorAll('.s247-served-fields'), served);
        setDisplay(document.querySelectorAll('.s247-first-time-fields'), !served);
    }

    function syncDietaryChecks(changedId) {
        if (!hasDietaryRequirementsCheckbox || !noDietaryRequirementsCheckbox) return;
        if (changedId === 's247_has_dietary_requirements' && hasDietaryRequirementsCheckbox.checked) {
            noDietaryRequirementsCheckbox.checked = false;
        }
        if (changedId === 's247_no_dietary_requirements' && noDietaryRequirementsCheckbox.checked) {
            hasDietaryRequirementsCheckbox.checked = false;
        }
    }

    if (blueCardCheckbox) {
        blueCardCheckbox.addEventListener('change', syncBlueCardFields);
        if (blueCardOutsideCheckbox) {
            blueCardOutsideCheckbox.addEventListener('change', syncBlueCardFields);
        }
        syncBlueCardFields();
    }

    function initDatePickers() {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.datepicker) {
            return;
        }
        var $dateInputs = jQuery('.s247-date-input');
        $dateInputs.datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });

        // In this page, details/accordion interactions can steal focus;
        // explicitly show picker on focus/click to keep behavior reliable.
        $dateInputs.on('focus click', function () {
            try {
                jQuery(this).datepicker('show');
            } catch (e) {}
        });
    }
    initDatePickers();

    if (historyNew) historyNew.addEventListener('change', syncHistoryFields);
    if (historyServed) historyServed.addEventListener('change', syncHistoryFields);
    syncHistoryFields();

    var accordionItems = document.querySelectorAll('.s247-accordion-item');
    Array.prototype.forEach.call(accordionItems, function(item) {
        var summary = item.querySelector('summary');
        if (summary && !summary.querySelector('.s247-summary-badge')) {
            var badge = document.createElement('span');
            badge.className = 's247-summary-badge';
            badge.textContent = '0/0';
            summary.appendChild(badge);
        }
        item.addEventListener('toggle', function() {
            if (!item.open) return;
            Array.prototype.forEach.call(accordionItems, function(other) {
                if (other !== item) other.open = false;
            });
        });
    });

    var stepLinks = document.querySelectorAll('.s247-step[data-step-target]');
    Array.prototype.forEach.call(stepLinks, function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = link.getAttribute('data-step-target');
            var target = document.getElementById(targetId);
            if (!target) return;
            target.open = true;
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    function isConditionallyHidden(el) {
        var conditionalContainer = el.closest('.s247-bluecard-current-field, .s247-bluecard-upload-field, .s247-served-year, .s247-served-fields, .s247-first-time-fields');
        if (!conditionalContainer) return false;
        return window.getComputedStyle(conditionalContainer).display === 'none';
    }

    function isFieldComplete(el) {
        var type = (el.type || '').toLowerCase();
        if (type === 'checkbox' || type === 'radio') {
            return !!el.checked;
        }
        if (type === 'file') {
            return el.files && el.files.length > 0;
        }
        return (el.value || '').trim() !== '';
    }

    function isBlueCardRequirementMet() {
        if (!form) return true;

        var outsideChecked = !!(blueCardOutsideCheckbox && blueCardOutsideCheckbox.checked);
        var currentChecked = !!(blueCardCheckbox && blueCardCheckbox.checked);
        if (outsideChecked || currentChecked) {
            return true;
        }

        var blueCardUpload = form.querySelector('.s247-bluecard-upload-field input[type="file"]');
        if (blueCardUpload && !isConditionallyHidden(blueCardUpload)) {
            return isFieldComplete(blueCardUpload);
        }

        return false;
    }

    function isDietaryRequirementMet() {
        if (!form) return true;
        var dietaryChecks = form.querySelectorAll('input[data-required-group="dietary"]');
        if (!dietaryChecks.length) return true;
        return Array.prototype.some.call(dietaryChecks, function(chk){ return chk.checked; });
    }

    function updateProgress() {
        var totalRequired = 0;
        var totalComplete = 0;

        Array.prototype.forEach.call(accordionItems, function(item) {
            var sectionRequired = 0;
            var sectionComplete = 0;

            var requiredFields = item.querySelectorAll('[data-required="1"]');
            Array.prototype.forEach.call(requiredFields, function(field) {
                if (isConditionallyHidden(field)) return;
                sectionRequired++;
                if (isFieldComplete(field)) sectionComplete++;
            });

            var conventionChecks = item.querySelectorAll('input[data-required-group="conventions"]');
            if (conventionChecks.length) {
                sectionRequired++;
                var anyChecked = Array.prototype.some.call(conventionChecks, function(chk){ return chk.checked; });
                if (anyChecked) sectionComplete++;
            }

            if (item.id === 's247-step-3') {
                sectionRequired++;
                if (isBlueCardRequirementMet()) sectionComplete++;
            }

            if (item.id === 's247-step-5') {
                sectionRequired++;
                if (isDietaryRequirementMet()) sectionComplete++;
            }

            totalRequired += sectionRequired;
            totalComplete += sectionComplete;

            var badge = item.querySelector('.s247-summary-badge');
            if (badge) {
                badge.textContent = sectionComplete + '/' + sectionRequired;
            }
        });

        var percent = totalRequired > 0 ? Math.round((totalComplete / totalRequired) * 100) : 0;
        var progressFill = document.getElementById('s247-progress-fill');
        var progressText = document.getElementById('s247-progress-text');
        if (progressFill) progressFill.style.width = percent + '%';
        if (progressText) progressText.textContent = percent + '% Complete';
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.closest('.s247-form')) {
            updateProgress();
        }
    });
    document.addEventListener('change', function(e) {
        if (e.target && e.target.closest('.s247-form')) {
            if (e.target.id === 's247_has_dietary_requirements' || e.target.id === 's247_no_dietary_requirements') {
                syncDietaryChecks(e.target.id);
            }
            syncBlueCardFields();
            syncHistoryFields();
            updateProgress();
        }
    });

    var form = document.getElementById('s247-form');
    var submitStatus = document.getElementById('s247-submit-status');

    function getFieldLabel(field) {
        var label = '';
        if (field.id) {
            var linkedLabel = form.querySelector('label[for="' + field.id + '"]');
            if (linkedLabel) label = linkedLabel.textContent || '';
        }
        if (!label) {
            var wrapLabel = field.closest('label');
            if (wrapLabel) label = wrapLabel.textContent || '';
        }
        if (!label) {
            var fieldParent = field.parentElement;
            if (fieldParent) {
                var siblingLabel = fieldParent.querySelector(':scope > label');
                if (siblingLabel) label = siblingLabel.textContent || '';
            }
        }
        label = label.replace(/\*/g, '').replace(/\s+/g, ' ').trim();
        return label;
    }

    function serializeForm() {
        var payload = [];
        var fieldNodes = form.querySelectorAll('input, textarea, select');
        Array.prototype.forEach.call(fieldNodes, function(field) {
            var type = (field.type || '').toLowerCase();
            if (type === 'button' || type === 'submit') return;
            if (field.closest('.s247-bluecard-current-field, .s247-bluecard-upload-field, .s247-served-year, .s247-served-fields, .s247-first-time-fields') && window.getComputedStyle(field.closest('.s247-bluecard-current-field, .s247-bluecard-upload-field, .s247-served-year, .s247-served-fields, .s247-first-time-fields')).display === 'none') {
                return;
            }

            var entry = {
                label: getFieldLabel(field) || field.placeholder || field.name || field.type || 'Field',
                type: type,
                value: ''
            };

            if (type === 'checkbox' || type === 'radio') {
                if (!field.checked) return;
                entry.value = field.parentElement ? (field.parentElement.textContent || '').replace(/\s+/g, ' ').trim() : 'Checked';
            } else if (type === 'file') {
                if (field.files && field.files.length) {
                    var fileNames = [];
                    Array.prototype.forEach.call(field.files, function(fileItem) {
                        fileNames.push(fileItem.name);
                    });
                    entry.value = fileNames.join(', ');
                } else {
                    entry.value = '';
                }
            } else {
                entry.value = (field.value || '').trim();
            }

            if (entry.value !== '') {
                payload.push(entry);
            }
        });
        return payload;
    }

    function validateRequiredFields() {
        if (!form) return true;

        var missingFieldLabel = '';
        var requiredFields = form.querySelectorAll('[data-required="1"]');
        Array.prototype.some.call(requiredFields, function(field) {
            if (isConditionallyHidden(field)) return false;
            if (isFieldComplete(field)) return false;
            missingFieldLabel = getFieldLabel(field) || 'required field';
            return true;
        });

        if (!missingFieldLabel) {
            var conventionChecks = form.querySelectorAll('input[data-required-group="conventions"]');
            if (conventionChecks.length) {
                var anyChecked = Array.prototype.some.call(conventionChecks, function(chk){ return chk.checked; });
                if (!anyChecked) {
                    missingFieldLabel = 'Convention(s) Applying For';
                }
            }
        }

        if (!missingFieldLabel && !isBlueCardRequirementMet()) {
            missingFieldLabel = 'Blue Card section (select Current Blue Card, or Outside AU/NZ, or upload Blue Card application form)';
        }

        if (!missingFieldLabel && !isDietaryRequirementMet()) {
            missingFieldLabel = 'Dietary Requirements (select one option)';
        }

        if (!missingFieldLabel) {
            return true;
        }

        if (submitStatus) {
            submitStatus.textContent = 'Please complete: ' + missingFieldLabel;
        }
        return false;
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateRequiredFields()) {
                updateProgress();
                return;
            }

            if (submitStatus) submitStatus.textContent = 'Submitting...';

            var body = new FormData();
            var fileFields = form.querySelectorAll('input[type="file"]');
            Array.prototype.forEach.call(fileFields, function(fileField) {
                if (!fileField.files || !fileField.files.length) return;
                if (fileField.closest('.s247-bluecard-current-field, .s247-bluecard-upload-field, .s247-served-year, .s247-served-fields, .s247-first-time-fields') && window.getComputedStyle(fileField.closest('.s247-bluecard-current-field, .s247-bluecard-upload-field, .s247-served-year, .s247-served-fields, .s247-first-time-fields')).display === 'none') {
                    return;
                }
                Array.prototype.forEach.call(fileField.files, function(fileItem) {
                    body.append('attachments[]', fileItem);
                });
            });
            body.append('submission', JSON.stringify({
                submitted_at: new Date().toISOString(),
                fields: serializeForm()
            }));

            fetch('<?php echo HTTP_PATH; ?>/users/squad247submit', {
                method: 'POST',
                body: body,
                credentials: 'same-origin'
            }).then(function(response) {
                return response.json();
            }).then(function(data) {
                if (submitStatus) submitStatus.textContent = data && data.message ? data.message : 'Thank you for your 24/7 Squad application. It has been received and our Events team will be in touch soon.';
                form.reset();
                syncBlueCardFields();
                syncHistoryFields();
                updateProgress();
            }).catch(function() {
                if (submitStatus) submitStatus.textContent = 'Unable to save submission.';
            });
        });
    }

    updateProgress();
})();
</script>
