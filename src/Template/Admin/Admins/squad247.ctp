<div class="content-wrapper">
    <section class="content-header">
        <h1>24/7 Squad Information</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo HTTP_PATH; ?>/admin/admins/dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">24/7 Squad Information</li>
        </ol>
    </section>

    <style>
        .s247-admin-shell {
            border-top: 3px solid #315caa;
            box-shadow: 0 8px 22px rgba(19, 40, 74, 0.08);
        }
        .s247-admin-header-meta {
            margin-top: 4px;
            color: #6e7d99;
            font-size: 13px;
        }
        .s247-admin-editor {
            border: 1px solid #d9e3f3;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .s247-admin-editor .box-header {
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7ff 100%);
            border-bottom: 1px solid #d9e3f3;
        }
        .s247-admin-editor .box-title {
            font-weight: 700;
            color: #22365f;
        }
        .s247-admin-editor .box-body {
            background: #fcfdff;
        }
        .s247-admin-hint {
            border: 1px solid #d7e4f9;
            background: #f4f8ff;
            color: #2a4678;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 13px;
        }
        .s247-admin-section {
            border: 1px solid #e2e8f4;
            border-radius: 8px;
            background: #fff;
            padding: 14px;
            margin-bottom: 14px;
        }
        .s247-admin-section-title {
            margin: 0 0 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e8edf6;
            color: #1f3560;
            font-size: 16px;
            font-weight: 700;
        }
        .s247-admin-editor .form-group {
            margin-bottom: 12px;
        }
        .s247-admin-editor .control-label {
            color: #2d4267;
            font-weight: 600;
            font-size: 13px;
        }
        .s247-admin-editor .form-control {
            border-color: #ccd7eb;
            border-radius: 6px;
            box-shadow: none;
        }
        .s247-admin-editor .form-control:focus {
            border-color: #4b73ba;
            box-shadow: 0 0 0 2px rgba(75, 115, 186, 0.12);
        }
        .s247-admin-table {
            margin-bottom: 10px;
        }
        .s247-admin-table thead th {
            background: #f5f8ff;
            color: #2a416a;
            font-weight: 700;
            border-bottom: 1px solid #dce5f3;
        }
        .s247-admin-accordion {
            margin-bottom: 0;
        }
        .s247-admin-accordion .panel {
            border-color: #d9e3f3;
            box-shadow: none;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        .s247-admin-accordion .panel-heading {
            padding: 0;
            background: transparent;
            border-color: #d9e3f3;
        }
        .s247-admin-accordion .panel-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
        }
        .s247-admin-accordion .panel-title a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            color: #22365f;
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7ff 100%);
        }
        .s247-admin-accordion .panel-title a:hover,
        .s247-admin-accordion .panel-title a:focus {
            text-decoration: none;
            background: linear-gradient(180deg, #f8fbff 0%, #f2f7ff 100%);
        }
        .s247-admin-accordion .panel-title .fa {
            transition: transform 0.18s ease;
            margin-left: 12px;
            float: none;
        }
        .s247-admin-accordion .panel-title a.collapsed .fa {
            transform: rotate(-90deg);
        }
        .s247-admin-accordion .panel-body {
            background: #fff;
            padding: 14px;
        }
        .s247-admin-accordion .panel-collapse {
            display: none;
        }
        .s247-admin-accordion .panel-collapse.in {
            display: block;
        }
        .s247-admin-preview ul,
        .s247-admin-preview p {
            line-height: 1.5;
        }
        .s247-admin-preview h4 {
            color: #1f3560;
            font-weight: 700;
            margin-top: 16px;
            margin-bottom: 8px;
        }
        .s247-admin-submissions .table thead th {
            background: #f5f8ff;
            color: #2a416a;
            border-bottom: 1px solid #d7e3f7;
            font-weight: 700;
            font-size: 13px;
        }
        .s247-admin-submissions .table tbody td {
            vertical-align: top;
            padding: 10px 8px;
        }
        .s247-admin-submissions .table tbody tr:nth-child(odd) {
            background: #fcfdff;
        }
        .s247-submitted-at {
            font-weight: 600;
            color: #2a3f67;
            white-space: nowrap;
        }
        .s247-field-count {
            display: inline-block;
            margin-bottom: 8px;
            padding: 4px 8px;
            border-radius: 999px;
            background: #eef4ff;
            color: #27426f;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #d6e2f7;
        }
        .s247-field-list {
            margin-top: 0;
            font-size: 13px;
            line-height: 1.45;
        }
        .s247-field-row {
            margin-bottom: 5px;
            color: #2d3f60;
        }
        .s247-field-label {
            font-weight: 700;
            color: #1f3560;
        }
        .s247-file-actions {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .s247-file-actions li {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .s247-file-preview-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #c9d8f0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f5f8ff;
            color: #2b4f89;
            text-decoration: none;
            cursor: pointer;
        }
        .s247-file-preview-name {
            color: #2b456f;
            font-size: 12px;
            max-width: 175px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }
        .s247-file-preview-btn:hover,
        .s247-file-preview-btn:focus {
            text-decoration: none;
            background: #e7efff;
            color: #1d3e74;
        }
        .s247-file-modal {
            position: fixed;
            inset: 0;
            background: rgba(12, 21, 39, 0.72);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1040;
            padding: 16px;
        }
        .s247-file-modal.is-open {
            display: flex;
        }
        .s247-file-modal-dialog {
            width: min(960px, 100%);
            max-height: 92vh;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 18px 35px rgba(10, 18, 33, 0.45);
            display: flex;
            flex-direction: column;
        }
        .s247-file-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid #e5ebf6;
            background: #f7faff;
        }
        .s247-file-modal-title {
            font-weight: 700;
            color: #1f3560;
            margin: 0;
            font-size: 14px;
        }
        .s247-file-modal-close {
            border: 0;
            background: transparent;
            color: #5a6f92;
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            padding: 0 4px;
        }
        .s247-file-modal-body {
            padding: 0;
            min-height: 320px;
            background: #fff;
        }
        .s247-file-frame {
            width: 100%;
            height: 72vh;
            border: 0;
            display: block;
            background: #fff;
        }
        .s247-file-fallback {
            padding: 18px;
            color: #2d3f60;
            font-size: 14px;
        }
        .s247-submissions-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .s247-submission-item {
            border: 1px solid #d7e3f6;
            border-radius: 10px;
            background: #fff;
            overflow: hidden;
        }
        .s247-submission-item > summary {
            list-style: none;
            cursor: pointer;
            padding: 11px 14px;
            background: linear-gradient(180deg, #f9fbff 0%, #f2f7ff 100%);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            border-bottom: 1px solid #dbe6f8;
            color: #1f3560;
            font-weight: 700;
        }
        .s247-submission-item > summary::-webkit-details-marker {
            display: none;
        }
        .s247-submission-item > summary:after {
            content: '+';
            font-size: 18px;
            line-height: 1;
            color: #355a95;
            margin-left: 4px;
        }
        .s247-submission-item[open] > summary:after {
            content: '-';
        }
        .s247-submission-summary-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .s247-submission-summary-right {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }
        .s247-submission-delete {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid #f4c6c3;
            background: #fff5f5;
            color: #c0392b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        .s247-submission-delete:hover,
        .s247-submission-delete:focus {
            background: #fde9e8;
            border-color: #e3a29c;
            color: #9f2f23;
            outline: none;
        }
        .s247-submission-date {
            font-weight: 700;
            color: #223c6a;
            white-space: nowrap;
        }
        .s247-submission-body {
            padding: 12px 14px;
            background: #fff;
        }
        .s247-submission-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 14px;
        }
        .s247-submission-card {
            border: 1px solid #e2e9f7;
            border-radius: 8px;
            padding: 10px 11px;
            background: #fcfdff;
        }
        .s247-submission-card-title {
            margin: 0 0 8px;
            font-size: 13px;
            color: #274572;
            font-weight: 700;
        }
        .s247-submission-empty {
            color: #7a879f;
            font-size: 12px;
        }
        @media (max-width: 900px) {
            .s247-submission-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 767px) {
            .s247-admin-editor .control-label {
                margin-bottom: 6px;
            }
        }
    </style>

    <section class="content">
        <div class="box box-primary s247-admin-shell">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo h($squad247Data['convention_subtitle'] ?? 'Regional Student Conventions 2025'); ?></h3>
                <div class="s247-admin-header-meta">Manage public 24/7 Squad copy, dates, fees, and review recent submissions.</div>
                <div class="box-tools pull-right">
                    <a class="btn btn-sm btn-primary" target="_blank" href="<?php echo HTTP_PATH; ?>/files/247-Application-Updated-Fillable.pdf">
                        <i class="fa fa-file-pdf-o"></i> Open Fillable Form
                    </a>
                </div>
            </div>
            <div class="box-body">
                <?php
                $regionsLeft = isset($squad247Data['regions_left']) && is_array($squad247Data['regions_left']) ? $squad247Data['regions_left'] : array();
                $regionsRight = isset($squad247Data['regions_right']) && is_array($squad247Data['regions_right']) ? $squad247Data['regions_right'] : array();
                $fees = isset($squad247Data['fees']) && is_array($squad247Data['fees']) ? $squad247Data['fees'] : array();
                $successRequirements = isset($squad247Data['success_requirements']) && is_array($squad247Data['success_requirements']) ? $squad247Data['success_requirements'] : array();
                $successfulNeedTo = isset($squad247Data['successful_need_to']) && is_array($squad247Data['successful_need_to']) ? $squad247Data['successful_need_to'] : array();
                $applicantMustProvide = isset($squad247Data['applicant_must_provide']) && is_array($squad247Data['applicant_must_provide']) ? $squad247Data['applicant_must_provide'] : array();
                $applicationIntro = trim((string)($squad247Data['application_intro'] ?? 'Southern Cross Educational Enterprises is seeking applications from A.C.E. Graduates and supporters to join the 2025 Regional Convention 24/7 Squads in the following regions:'));
                $importantNote = trim((string)($squad247Data['important_note'] ?? 'Regional Squad experience is required (where possible) for South Pacific Squad applicants.'));
                $applicationsEmail = trim((string)($squad247Data['applications_email'] ?? 'events@scee.edu.au'));
                $applicationsDeadline = trim((string)($squad247Data['applications_deadline'] ?? 'ALL APPLICATIONS MUST BE RECEIVED TWO MONTHS PRIOR to the commencement of the Convention.'));
                $page2Title = trim((string)($squad247Data['page2_title'] ?? '24/7 Squad Regional Application Form 2025 (Page 2)'));
                $page2Description = trim((string)($squad247Data['page2_description'] ?? 'The application form includes personal details, country selection, convention experience, references, testimony, and declaration sections.'));
                $blueCardRequirement = trim((string)($squad247Data['blue_card_requirement'] ?? 'For Australian/NZ conventions, Australian volunteers at Australian conventions must hold an approved Blue Card (or the relevant state Working With Children approval). Applicants can apply via bluecard.qld.gov.au.'));
                $paymentOptions = trim((string)($squad247Data['payment_options'] ?? 'Once the application is received, specific payment details are sent based on the country selected.'));
                $selectedSquad247ConventionName = isset($selectedSquad247ConventionName) ? trim((string)$selectedSquad247ConventionName) : '';
                $selectedSquad247Aliases = isset($selectedSquad247Aliases) && is_array($selectedSquad247Aliases) ? $selectedSquad247Aliases : array();
                $selectedSquad247SeasonDateRange = isset($selectedSquad247SeasonDateRange) ? trim((string)$selectedSquad247SeasonDateRange) : '';
                $hasConventionFilter = !empty($selectedSquad247ConventionName) && !empty($selectedSquad247Aliases);

                $matchesSelectedConvention = static function ($label, array $aliases) {
                    $normalizedLabel = strtolower(trim((string)$label));
                    if ($normalizedLabel === '' || empty($aliases)) {
                        return false;
                    }

                    foreach ($aliases as $alias) {
                        $alias = strtolower(trim((string)$alias));
                        if ($alias === '') {
                            continue;
                        }
                        if ($normalizedLabel === $alias || strpos($normalizedLabel, $alias) !== false || strpos($alias, $normalizedLabel) !== false) {
                            return true;
                        }
                    }
                    return false;
                };

                $regionsLeftVisibleIdx = array();
                foreach ($regionsLeft as $i => $row) {
                    if (!$hasConventionFilter || $matchesSelectedConvention($row['name'] ?? '', $selectedSquad247Aliases)) {
                        $regionsLeftVisibleIdx[] = $i;
                    }
                }
                if ($hasConventionFilter && empty($regionsLeftVisibleIdx)) {
                    $regionsLeftVisibleIdx = array();
                }
                if (!$hasConventionFilter) {
                    $regionsLeftVisibleIdx = array_keys($regionsLeft);
                }
                $regionsLeftHiddenIdx = array_diff(array_keys($regionsLeft), $regionsLeftVisibleIdx);

                $regionsRightVisibleIdx = array();
                foreach ($regionsRight as $i => $row) {
                    if (!$hasConventionFilter || $matchesSelectedConvention($row['name'] ?? '', $selectedSquad247Aliases)) {
                        $regionsRightVisibleIdx[] = $i;
                    }
                }
                if ($hasConventionFilter && empty($regionsRightVisibleIdx)) {
                    $regionsRightVisibleIdx = array();
                }
                if (!$hasConventionFilter) {
                    $regionsRightVisibleIdx = array_keys($regionsRight);
                }
                $regionsRightHiddenIdx = array_diff(array_keys($regionsRight), $regionsRightVisibleIdx);

                $feesVisibleIdx = array();
                foreach ($fees as $i => $feeRow) {
                    if (!$hasConventionFilter || $matchesSelectedConvention($feeRow['name'] ?? '', $selectedSquad247Aliases)) {
                        $feesVisibleIdx[] = $i;
                    }
                }
                if ($hasConventionFilter && empty($feesVisibleIdx)) {
                    $feesVisibleIdx = array();
                }
                if (!$hasConventionFilter) {
                    $feesVisibleIdx = array_keys($fees);
                }
                $feesHiddenIdx = array_diff(array_keys($fees), $feesVisibleIdx);
                ?>

                <div class="box box-default s247-admin-editor">
                    <div class="box-header with-border">
                        <h3 class="box-title">Content Editor</h3>
                    </div>
                    <?php echo $this->Form->create(null, ['url' => ['controller' => 'admins', 'action' => 'squad247'], 'class' => 'form-horizontal']); ?>
                    <div class="box-body">
                        <div class="s247-admin-hint">Use one bullet point per line in list-based fields. Changes update the public 24/7 page immediately after saving.</div>

                        <div class="panel-group s247-admin-accordion" id="s247-editor-accordion" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="s247-editor-heading-primary">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#s247-editor-accordion" href="#s247-editor-primary" aria-expanded="true" aria-controls="s247-editor-primary">
                                            Primary Settings
                                            <i class="fa fa-chevron-down pull-right"></i>
                                        </a>
                                    </h4>
                                </div>
                                <div id="s247-editor-primary" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="s247-editor-heading-primary">
                                    <div class="panel-body">

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Convention Subtitle / Date Text</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        name="Squad247[convention_subtitle]"
                                        class="form-control"
                                        value="<?php echo h($squad247Data['convention_subtitle'] ?? 'Regional Student Conventions 2025'); ?>"
                                        placeholder="Regional Student Conventions 2025"
                                    >
                                    <p class="help-block" style="margin-bottom:0;">This updates the subtitle shown on the public 24/7 application page.</p>
                                </div>
                            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="s247-editor-heading-copy">
                                    <h4 class="panel-title">
                                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#s247-editor-accordion" href="#s247-editor-copy" aria-expanded="false" aria-controls="s247-editor-copy">
                                            Application Copy
                                            <i class="fa fa-chevron-down pull-right"></i>
                                        </a>
                                    </h4>
                                </div>
                                <div id="s247-editor-copy" class="panel-collapse collapse" role="tabpanel" aria-labelledby="s247-editor-heading-copy">
                                    <div class="panel-body">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Application Intro</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[application_intro]" class="form-control" rows="3"><?php echo h($squad247Data['application_intro'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Successful Applicants Requirements</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[success_requirements]" class="form-control" rows="8" placeholder="One bullet per line"><?php echo h(implode("\n", $successRequirements)); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Approved Applicants Will Need To:</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[successful_need_to]" class="form-control" rows="8" placeholder="One bullet per line"><?php echo h(implode("\n", $successfulNeedTo)); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Important Note</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[important_note]" class="form-control" rows="2"><?php echo h($squad247Data['important_note'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Applications Deadline</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[applications_deadline]" class="form-control" rows="2"><?php echo h($squad247Data['applications_deadline'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Applicant Must Provide</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[applicant_must_provide]" class="form-control" rows="5" placeholder="One bullet per line"><?php echo h(implode("\n", $applicantMustProvide)); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Blue Card Requirement</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[blue_card_requirement]" class="form-control" rows="3"><?php echo h($squad247Data['blue_card_requirement'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="col-sm-3 control-label">Payment Options</label>
                                <div class="col-sm-9">
                                    <textarea name="Squad247[payment_options]" class="form-control" rows="3"><?php echo h($squad247Data['payment_options'] ?? ''); ?></textarea>
                                </div>
                            </div>
                                    </div>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading" role="tab" id="s247-editor-heading-dates">
                                    <h4 class="panel-title">
                                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#s247-editor-accordion" href="#s247-editor-dates" aria-expanded="false" aria-controls="s247-editor-dates">
                                            Convention Dates and Fees
                                            <i class="fa fa-chevron-down pull-right"></i>
                                        </a>
                                    </h4>
                                </div>
                                <div id="s247-editor-dates" class="panel-collapse collapse" role="tabpanel" aria-labelledby="s247-editor-heading-dates">
                                    <div class="panel-body">
                            <?php if ($hasConventionFilter) { ?>
                                <div class="alert alert-info" style="margin-bottom:10px;">
                                    Editing convention rows for: <strong><?php echo h($selectedSquad247ConventionName); ?></strong><?php if ($selectedSquad247SeasonDateRange !== '') { ?> (<?php echo h($selectedSquad247SeasonDateRange); ?>)<?php } ?>.
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-sm-6">
                                    <table class="table table-bordered table-condensed s247-admin-table">
                                        <thead>
                                            <tr>
                                                <th style="width:40%;">Region</th>
                                                <th>Date Range</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($regionsLeftVisibleIdx as $i) { $row = $regionsLeft[$i]; ?>
                                                <tr>
                                                    <td><input type="text" name="Squad247[regions_left][<?php echo (int)$i; ?>][name]" class="form-control input-sm" value="<?php echo h($row['name'] ?? ''); ?>"></td>
                                                    <td><input type="text" name="Squad247[regions_left][<?php echo (int)$i; ?>][dates]" class="form-control input-sm" value="<?php echo h($row['dates'] ?? ''); ?>"></td>
                                                </tr>
                                            <?php } ?>
                                            <?php if (empty($regionsLeftVisibleIdx)) { ?>
                                                <tr>
                                                    <td colspan="2" class="text-muted" style="font-size:12px;">No matching region rows for the selected convention.</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <?php foreach ($regionsLeftHiddenIdx as $i) { $row = $regionsLeft[$i]; ?>
                                        <input type="hidden" name="Squad247[regions_left][<?php echo (int)$i; ?>][name]" value="<?php echo h($row['name'] ?? ''); ?>">
                                        <input type="hidden" name="Squad247[regions_left][<?php echo (int)$i; ?>][dates]" value="<?php echo h($row['dates'] ?? ''); ?>">
                                    <?php } ?>
                                </div>
                                <div class="col-sm-6">
                                    <table class="table table-bordered table-condensed s247-admin-table">
                                        <thead>
                                            <tr>
                                                <th style="width:40%;">Region</th>
                                                <th>Date Range</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($regionsRightVisibleIdx as $i) { $row = $regionsRight[$i]; ?>
                                                <tr>
                                                    <td><input type="text" name="Squad247[regions_right][<?php echo (int)$i; ?>][name]" class="form-control input-sm" value="<?php echo h($row['name'] ?? ''); ?>"></td>
                                                    <td><input type="text" name="Squad247[regions_right][<?php echo (int)$i; ?>][dates]" class="form-control input-sm" value="<?php echo h($row['dates'] ?? ''); ?>"></td>
                                                </tr>
                                            <?php } ?>
                                            <?php if (empty($regionsRightVisibleIdx)) { ?>
                                                <tr>
                                                    <td colspan="2" class="text-muted" style="font-size:12px;">No matching region rows for the selected convention.</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <?php foreach ($regionsRightHiddenIdx as $i) { $row = $regionsRight[$i]; ?>
                                        <input type="hidden" name="Squad247[regions_right][<?php echo (int)$i; ?>][name]" value="<?php echo h($row['name'] ?? ''); ?>">
                                        <input type="hidden" name="Squad247[regions_right][<?php echo (int)$i; ?>][dates]" value="<?php echo h($row['dates'] ?? ''); ?>">
                                    <?php } ?>
                                </div>
                            </div>

                            <h4>Convention Fees</h4>
                            <table class="table table-bordered table-condensed s247-admin-table" style="margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th style="width:30%;">Convention</th>
                                        <th>Fee</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feesVisibleIdx as $i) { $feeRow = $fees[$i]; ?>
                                        <tr>
                                            <td><input type="text" name="Squad247[fees][<?php echo (int)$i; ?>][name]" class="form-control input-sm" value="<?php echo h($feeRow['name'] ?? ''); ?>"></td>
                                            <td><input type="text" name="Squad247[fees][<?php echo (int)$i; ?>][amount]" class="form-control input-sm" value="<?php echo h($feeRow['amount'] ?? ''); ?>"></td>
                                        </tr>
                                    <?php } ?>
                                    <?php if (empty($feesVisibleIdx)) { ?>
                                        <tr>
                                            <td colspan="2" class="text-muted" style="font-size:12px;">No matching fee rows for the selected convention.</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <?php foreach ($feesHiddenIdx as $i) { $feeRow = $fees[$i]; ?>
                                <input type="hidden" name="Squad247[fees][<?php echo (int)$i; ?>][name]" value="<?php echo h($feeRow['name'] ?? ''); ?>">
                                <input type="hidden" name="Squad247[fees][<?php echo (int)$i; ?>][amount]" value="<?php echo h($feeRow['amount'] ?? ''); ?>">
                            <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <?php echo $this->Form->button('Save 24/7 Details', ['type' => 'submit', 'class' => 'btn btn-primary']); ?>
                    </div>
                    <?php echo $this->Form->end(); ?>
                </div>

                <div class="panel-group s247-admin-accordion" id="s247-view-accordion" role="tablist" aria-multiselectable="true">
                <div class="panel panel-default s247-admin-preview">
                <div class="panel-heading" role="tab" id="s247-view-heading-preview">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#s247-view-accordion" href="#s247-view-preview" aria-expanded="true" aria-controls="s247-view-preview">
                            Public Page Preview Content
                            <i class="fa fa-chevron-down pull-right"></i>
                        </a>
                    </h4>
                </div>
                <div id="s247-view-preview" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="s247-view-heading-preview">
                <div class="panel-body">
                <p><?php echo h($applicationIntro); ?></p>

                <div class="row" style="margin-bottom:10px;">
                    <div class="col-sm-6">
                        <ul>
                            <?php foreach ($regionsLeft as $row) { ?>
                                <li><?php echo h(($row['name'] ?? '') . ': ' . ($row['dates'] ?? '')); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <ul>
                            <?php foreach ($regionsRight as $row) { ?>
                                <li><?php echo h(($row['name'] ?? '') . ': ' . ($row['dates'] ?? '')); ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>

                <h4>Successful Applicants will meet the following requirements:</h4>
                <ul>
                    <?php foreach ($successRequirements as $line) { ?>
                        <li><?php echo h($line); ?></li>
                    <?php } ?>
                </ul>

                <h4>The successful applicants will need to:</h4>
                <ul>
                    <?php foreach ($successfulNeedTo as $line) { ?>
                        <li><?php echo h($line); ?></li>
                    <?php } ?>
                </ul>

                <div class="alert alert-warning" style="margin-top:12px;">
                    <strong>Important note:</strong> <?php echo h($importantNote); ?>
                </div>

                <p style="margin:0;"><strong><?php echo h($applicationsDeadline); ?></strong></p>

                <h4>Convention Fees</h4>
                <ul>
                    <?php foreach ($fees as $feeRow) { ?>
                        <li><?php echo h(($feeRow['name'] ?? '') . ': ' . ($feeRow['amount'] ?? '')); ?></li>
                    <?php } ?>
                </ul>

                <h4>Applicant Must Provide</h4>
                <ul>
                    <?php foreach ($applicantMustProvide as $line) { ?>
                        <li><?php echo h($line); ?></li>
                    <?php } ?>
                </ul>

                <h4>Blue Card / Working With Children Requirement</h4>
                <p><?php echo h($blueCardRequirement); ?></p>

                <p style="margin-bottom:0;"><strong>Payment Options:</strong> <?php echo h($paymentOptions); ?></p>
                </div>
                </div>
                </div>

                <div class="panel panel-default s247-admin-submissions">
                <div class="panel-heading" role="tab" id="s247-view-heading-submissions">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#s247-view-accordion" href="#s247-view-submissions" aria-expanded="true" aria-controls="s247-view-submissions">
                            Submitted 24/7 Applications
                            <i class="fa fa-chevron-down pull-right"></i>
                        </a>
                    </h4>
                </div>
                <div id="s247-view-submissions" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="s247-view-heading-submissions">
                <div class="panel-body">
                <?php if (!empty($selectedSquad247ConventionName)) { ?>
                    <div class="alert alert-info" style="margin-bottom:10px;">
                        Showing submissions for: <strong><?php echo h($selectedSquad247ConventionName); ?></strong>
                    </div>
                <?php } ?>
                <?php if (!empty($squad247Submissions)) { ?>
                    <div class="s247-submissions-list">
                        <?php foreach (array_slice($squad247Submissions, 0, 10, true) as $index => $submission) { ?>
                            <?php
                            $payload = isset($submission['payload']) ? $submission['payload'] : array();
                            if (is_array($payload) && count($payload) === 1 && isset($payload[0]) && is_string($payload[0])) {
                                $decodedPayload = json_decode($payload[0], true);
                                if (is_array($decodedPayload)) {
                                    $payload = $decodedPayload;
                                }
                            } elseif (is_string($payload)) {
                                $decodedPayload = json_decode($payload, true);
                                if (is_array($decodedPayload)) {
                                    $payload = $decodedPayload;
                                }
                            }
                            $fieldCount = isset($payload['fields']) && is_array($payload['fields']) ? count($payload['fields']) : 0;
                            $uploadedFiles = isset($payload['files']) && is_array($payload['files']) ? $payload['files'] : array();
                            $uploadedCount = count($uploadedFiles);

                            $applicantName = '';
                            $firstName = '';
                            $lastName = '';
                            if (!empty($payload['fields']) && is_array($payload['fields'])) {
                                foreach ($payload['fields'] as $field) {
                                    $label = strtolower(trim((string)($field['label'] ?? '')));
                                    $value = trim((string)($field['value'] ?? ''));
                                    if ($value === '') {
                                        continue;
                                    }

                                    if (strpos($label, 'parent') !== false || strpos($label, 'guardian') !== false || strpos($label, 'pastor') !== false || strpos($label, 'principal') !== false || strpos($label, 'reference') !== false) {
                                        continue;
                                    }

                                    if ($label === "applicant's full name (acts as your signature/confirmation)" || $label === 'full name' || $label === 'applicant full name' || $label === 'applicant name') {
                                        $applicantName = $value;
                                        break;
                                    }

                                    if ($label === 'first name' || $label === 'given name') {
                                        $firstName = $value;
                                        continue;
                                    }

                                    if ($label === 'last name' || $label === 'surname' || $label === 'family name') {
                                        $lastName = $value;
                                    }
                                }
                            }

                            if ($applicantName === '' && ($firstName !== '' || $lastName !== '')) {
                                $applicantName = trim($firstName . ' ' . $lastName);
                            }

                            if ($applicantName === '') {
                                $applicantName = 'Unnamed Applicant';
                            }
                            ?>
                            <details class="s247-submission-item" <?php if ($index === 0) { ?>open<?php } ?>>
                                <summary>
                                    <span class="s247-submission-summary-left">
                                        <span class="s247-submission-date"><?php echo h($applicantName); ?></span>
                                    </span>
                                    <span class="s247-submission-summary-right">
                                        <span class="s247-field-count"><?php echo (int)$fieldCount; ?> field(s)</span>
                                        <span class="s247-field-count"><?php echo (int)$uploadedCount; ?> file(s)</span>
                                        <button
                                            type="button"
                                            class="s247-submission-delete js-s247-delete"
                                            title="Delete this submission"
                                            aria-label="Delete this submission"
                                            data-form-id="s247-delete-form-<?php echo (int)$index; ?>"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </span>
                                </summary>
                                <?php echo $this->Form->create(null, [
                                    'url' => ['controller' => 'Admins', 'action' => 'squad247DeleteSubmission', (int)$index],
                                    'id' => 's247-delete-form-' . (int)$index,
                                    'style' => 'display:none;'
                                ]); ?>
                                <?php echo $this->Form->end(); ?>
                                <div class="s247-submission-body">
                                    <div class="s247-submission-grid">
                                        <div class="s247-submission-card">
                                            <h5 class="s247-submission-card-title">Submission Details</h5>
                                            <?php if ($fieldCount > 0) { ?>
                                                <div class="s247-field-list">
                                                    <?php foreach ($payload['fields'] as $field) { ?>
                                                        <div class="s247-field-row"><span class="s247-field-label"><?php echo h($field['label'] ?? 'Field'); ?>:</span> <?php echo h(is_scalar($field['value'] ?? '') ? (string)($field['value'] ?? '') : json_encode($field['value'] ?? '')); ?></div>
                                                    <?php } ?>
                                                </div>
                                            <?php } else { ?>
                                                <div class="s247-submission-empty">No fields in this submission.</div>
                                            <?php } ?>
                                        </div>
                                        <div class="s247-submission-card">
                                            <h5 class="s247-submission-card-title">Uploaded Files</h5>
                                            <?php if (!empty($uploadedFiles)) { ?>
                                                <ul class="s247-file-actions">
                                                    <?php foreach ($uploadedFiles as $fileInfo) { ?>
                                                        <li>
                                                            <?php if (!empty($fileInfo['url'])) { ?>
                                                                <a href="javascript:void(0);" class="s247-file-preview-btn" data-file-url="<?php echo h($fileInfo['url']); ?>" data-file-name="<?php echo h($fileInfo['name'] ?? 'Attachment'); ?>" title="<?php echo h($fileInfo['name'] ?? 'Attachment'); ?>">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>
                                                                <span class="s247-file-preview-name" title="<?php echo h($fileInfo['name'] ?? 'Attachment'); ?>"><?php echo h($fileInfo['name'] ?? 'Attachment'); ?></span>
                                                            <?php } else { ?>
                                                                <span class="s247-submission-empty">N/A</span>
                                                            <?php } ?>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            <?php } else { ?>
                                                <div class="s247-submission-empty">No files uploaded</div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </details>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p>No submissions have been received yet.</p>
                <?php } ?>
                </div>
                </div>
                </div>
                </div>
            </div>
            <div class="box-footer">
                <a class="btn btn-default" href="<?php echo HTTP_PATH; ?>/admin/admins/dashboard">Back to Dashboard</a>
            </div>
        </div>
    </section>
</div>

<div class="s247-file-modal" id="s247-file-modal" aria-hidden="true">
    <div class="s247-file-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="s247-file-modal-title">
        <div class="s247-file-modal-header">
            <h4 class="s247-file-modal-title" id="s247-file-modal-title">Attachment Preview</h4>
            <button type="button" class="s247-file-modal-close" id="s247-file-modal-close" aria-label="Close">&times;</button>
        </div>
        <div class="s247-file-modal-body" id="s247-file-modal-body"></div>
    </div>
</div>

<script>
(function () {
    function updateAccordionState(group) {
        var links = group.querySelectorAll('[data-toggle="collapse"]');
        Array.prototype.forEach.call(links, function (link) {
            var targetSelector = link.getAttribute('href');
            var target = targetSelector ? document.querySelector(targetSelector) : null;
            var isOpen = !!(target && target.classList.contains('in'));
            link.classList.toggle('collapsed', !isOpen);
            link.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    function initAccordions() {
        var groups = document.querySelectorAll('.s247-admin-accordion');
        Array.prototype.forEach.call(groups, function (group) {
            updateAccordionState(group);
            var links = group.querySelectorAll('[data-toggle="collapse"]');
            Array.prototype.forEach.call(links, function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    var targetSelector = link.getAttribute('href');
                    var target = targetSelector ? document.querySelector(targetSelector) : null;
                    if (!target) {
                        return;
                    }

                    var isOpen = target.classList.contains('in');
                    var openPanels = group.querySelectorAll('.panel-collapse.in');
                    Array.prototype.forEach.call(openPanels, function (panel) {
                        panel.classList.remove('in');
                    });

                    if (!isOpen) {
                        target.classList.add('in');
                    }

                    updateAccordionState(group);
                });
            });
        });
    }

    function openFileModal(fileUrl, fileName) {
        var modal = document.getElementById('s247-file-modal');
        var modalTitle = document.getElementById('s247-file-modal-title');
        var modalBody = document.getElementById('s247-file-modal-body');
        if (!modal || !modalTitle || !modalBody || !fileUrl) {
            return;
        }

        modalTitle.textContent = fileName || 'Attachment Preview';
        var lowerUrl = fileUrl.toLowerCase();
        var isPdf = lowerUrl.indexOf('.pdf') !== -1;
        var isImage = /\.(jpg|jpeg|png|gif|webp)(\?|$)/.test(lowerUrl);

        if (isPdf || isImage) {
            if (isImage) {
                modalBody.innerHTML = '<img src="' + fileUrl + '" alt="Attachment" style="max-width:100%; max-height:72vh; display:block; margin:0 auto;">';
            } else {
                modalBody.innerHTML = '<iframe class="s247-file-frame" src="' + fileUrl + '"></iframe>';
            }
        } else {
            modalBody.innerHTML = '<div class="s247-file-fallback">Preview is not available for this file type. <a target="_blank" href="' + fileUrl + '">Open file in new tab</a>.</div>';
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeFileModal() {
        var modal = document.getElementById('s247-file-modal');
        var modalBody = document.getElementById('s247-file-modal-body');
        if (!modal || !modalBody) {
            return;
        }
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modalBody.innerHTML = '';
    }

    function initFilePreview() {
        var previewButtons = document.querySelectorAll('.s247-file-preview-btn');
        Array.prototype.forEach.call(previewButtons, function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                openFileModal(btn.getAttribute('data-file-url'), btn.getAttribute('data-file-name'));
            });
        });

        var closeBtn = document.getElementById('s247-file-modal-close');
        var modal = document.getElementById('s247-file-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeFileModal);
        }
        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeFileModal();
                }
            });
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeFileModal();
            }
        });
    }

    function initSubmissionDeleteButtons() {
        var deleteButtons = document.querySelectorAll('.js-s247-delete');
        Array.prototype.forEach.call(deleteButtons, function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var formId = btn.getAttribute('data-form-id');
                if (!formId) {
                    return;
                }

                var form = document.getElementById(formId);
                if (!form) {
                    return;
                }

                if (window.confirm('Delete this 24/7 submission? This action cannot be undone.')) {
                    form.submit();
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initAccordions();
            initFilePreview();
            initSubmissionDeleteButtons();
        });
    } else {
        initAccordions();
        initFilePreview();
        initSubmissionDeleteButtons();
    }
})();
</script>
