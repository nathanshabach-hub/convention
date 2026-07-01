<?php
/* Scheduling Tweaks – index.php
   Tweaks: A = pinned day, B = pinned room, C = pinned start time */
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Scheduling Tweaks – [<?php echo h($conventionSD->Conventions['name']); ?>]
            &nbsp; [Season Year: <?php echo h($conventionSD->season_year); ?>]
        </h1>
        <ol class="breadcrumb">
            <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard',
                ['controller'=>'admins','action'=>'dashboard'], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Conventions',
                ['controller'=>'conventions','action'=>'index'], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Seasons',
                ['controller'=>'conventions','action'=>'seasons',$convention_slug], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Pre-check',
                ['controller'=>'schedulings','action'=>'precheck',$convention_season_slug], ['escape'=>false]); ?></li>
            <li class="active">Scheduling Tweaks</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Event Tweaks</h3>
                <div class="box-tools pull-right">
                    <?php echo $this->Html->link(
                        '<i class="fa fa-clock-o"></i> Room Availability Windows',
                        ['controller'=>'schedulingtweaks','action'=>'roomavailability',$convention_season_slug],
                        ['class'=>'btn btn-sm btn-default', 'escape'=>false]
                    ); ?>
                    &nbsp;
                    <?php echo $this->Html->link(
                        '<i class="fa fa-bar-chart"></i> Room Time Allocation',
                        ['controller'=>'schedulingtweaks','action'=>'roomlimits',$convention_season_slug],
                        ['class'=>'btn btn-sm btn-info', 'escape'=>false]
                    ); ?>
                </div>
            </div>
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div class="box-body">
                <style>
                    .tweak-kpi-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:12px; margin-bottom:16px; }
                    .tweak-kpi { border:1px solid #e6e6e6; border-radius:8px; padding:12px; background:#fafafa; }
                    .tweak-kpi-label { color:#666; font-size:12px; text-transform:uppercase; letter-spacing:.4px; }
                    .tweak-kpi-value { font-size:24px; font-weight:700; line-height:1.1; margin-top:4px; }
                    .tweak-filter-bar { border:1px solid #e6e6e6; border-radius:8px; padding:10px; margin-bottom:14px; background:#fff; }
                    .tweak-filter-bar .form-control { height:34px; }
                    .tweak-rule-chip { display:inline-block; padding:3px 8px; border-radius:12px; font-size:11px; margin:0 4px 4px 0; background:#f2f6ff; color:#35589a; }
                    .tweak-rule-chip.dim { background:#f5f5f5; color:#777; }
                    .tweak-layout { display:grid; grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr); gap:14px; align-items:start; }
                    .tweak-table-card { border:1px solid #e6e6e6; border-radius:8px; background:#fff; overflow:hidden; }
                    .tweak-panel { border:1px solid #d9edf7; border-radius:8px; background:#fcfdff; position:sticky; top:12px; }
                    .tweak-panel-head { padding:10px 12px; border-bottom:1px solid #e7eef7; background:#f4f8fd; }
                    .tweak-panel-title { margin:0; font-size:14px; font-weight:700; color:#2f4f7f; }
                    .tweak-panel-body { padding:12px; }
                    .tweak-panel-sub { color:#777; font-size:12px; margin-top:4px; }
                    .tweak-editor-label { font-size:12px; color:#666; margin-bottom:4px; display:block; }
                    .tweak-selected { background:#eef7ff !important; }
                    .tweak-panel-empty { border:1px dashed #c7d8ea; border-radius:8px; padding:10px; color:#666; font-size:12px; }
                    .tweak-bulk-tools { padding:10px 12px; border-bottom:1px solid #eee; background:#f9fbfd; }
                    .tweak-bulk-count { font-size:12px; color:#666; margin-left:8px; }
                    .tweak-bulk-card { margin-top:14px; border:1px solid #e6e6e6; border-radius:8px; padding:12px; background:#fff; }
                    .tweak-bulk-title { margin:0 0 10px 0; font-size:14px; font-weight:700; color:#3c566e; }
                    .tweak-bulk-field { margin-bottom:10px; }
                    #tweakScrollTopBtn {
                        position:fixed; bottom:24px; right:24px; z-index:9999;
                        display:none; width:44px; height:44px; border-radius:50%;
                        border:none; background:#3c8dbc; color:#fff;
                        box-shadow:0 2px 8px rgba(0,0,0,.25); cursor:pointer;
                        font-size:18px; line-height:44px; text-align:center;
                    }
                    #tweakScrollTopBtn:hover { background:#367fa9; }
                    @media (max-width: 991px) {
                        .tweak-kpi-grid { grid-template-columns:repeat(2, minmax(0,1fr)); }
                        .tweak-layout { grid-template-columns: 1fr; }
                        .tweak-panel { position:static; }
                    }
                </style>

                <p class="text-muted">
                    Use these controls to restrict or pin events before running scheduling.
                    Changes here take effect the <strong>next time</strong> scheduling is generated.
                </p>
                <p>
                    <strong>A – Pinned Day:</strong> Schedule this event only on the chosen day.<br>
                    <strong>B – Pinned Room:</strong> Always assign this event to the chosen room.<br>
                    <strong>C – Pinned Start Time:</strong> Force this event's block to begin at a set time.<br>
                    <strong>D – Available Window:</strong> Restrict this event to run only between time A and time B.
                </p>

                <?php if (empty($eventsForTweaks)): ?>
                    <div class="alert alert-warning">No schedulable events found for this convention season.</div>
                <?php else: ?>

                    <?php
                    $totalTweaksEvents = count($eventsForTweaks);
                    $activeTweaksCount = 0;
                    $pinnedCount = 0;
                    $windowCount = 0;
                    foreach ($eventsForTweaks as $evStat) {
                        $twStat = $tweaksMap[$evStat->id] ?? null;
                        if ($twStat && ($twStat->pinned_day || $twStat->pinned_room_id || $twStat->pinned_start_time || $twStat->available_from_time || $twStat->available_to_time)) {
                            $activeTweaksCount++;
                        }
                        if ($twStat && ($twStat->pinned_day || $twStat->pinned_room_id || $twStat->pinned_start_time)) {
                            $pinnedCount++;
                        }
                        if ($twStat && ($twStat->available_from_time || $twStat->available_to_time)) {
                            $windowCount++;
                        }
                    }
                    ?>

                    <div class="tweak-kpi-grid">
                        <div class="tweak-kpi">
                            <div class="tweak-kpi-label">Schedulable Events</div>
                            <div class="tweak-kpi-value"><?php echo (int)$totalTweaksEvents; ?></div>
                        </div>
                        <div class="tweak-kpi">
                            <div class="tweak-kpi-label">Events With Active Tweaks</div>
                            <div class="tweak-kpi-value"><?php echo (int)$activeTweaksCount; ?></div>
                        </div>
                        <div class="tweak-kpi">
                            <div class="tweak-kpi-label">Pinned Day/Room/Start</div>
                            <div class="tweak-kpi-value"><?php echo (int)$pinnedCount; ?></div>
                        </div>
                        <div class="tweak-kpi">
                            <div class="tweak-kpi-label">Restricted Time Windows</div>
                            <div class="tweak-kpi-value"><?php echo (int)$windowCount; ?></div>
                        </div>
                    </div>

                    <div class="tweak-filter-bar">
                        <div class="row">
                            <div class="col-sm-4">
                                <input id="tweakSearch" type="text" class="form-control" placeholder="Search by event name or event number">
                            </div>
                            <div class="col-sm-3">
                                <select id="tweakKindFilter" class="form-control">
                                    <option value="">All Event Kinds</option>
                                    <option value="Sequential">Sequential</option>
                                    <option value="Elimination">Elimination</option>
                                </select>
                            </div>
                            <div class="col-sm-3" style="padding-top:7px;">
                                <label style="font-weight:normal; margin:0;">
                                    <input id="tweakActiveOnly" type="checkbox"> Show Active Tweaks Only
                                </label>
                            </div>
                            <div class="col-sm-2 text-right">
                                <button id="tweakClearFilters" type="button" class="btn btn-default btn-sm">Clear Filters</button>
                            </div>
                        </div>
                    </div>

                    <!-- Build allowed convention days from wizard config -->
                    <?php
                    $allowedDays = [];
                    if ($schedulingD && $schedulingD->first_day && $schedulingD->number_of_days > 0) {
                        $weekArr  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        $keyStart = array_search($schedulingD->first_day, $weekArr);
                        if ($keyStart !== false) {
                            for ($d = 0; $d < $schedulingD->number_of_days; $d++) {
                                $allowedDays[] = $weekArr[($keyStart + $d) % count($weekArr)];
                            }
                        }
                    }
                    ?>

                    <?php
                    $roomOptions = ['' => '-- Auto assign --'];
                    foreach ($rooms as $rm) {
                        $roomOptions[$rm->id] = h($rm->room_name);
                    }
                    ?>

                    <div class="tweak-layout">
                        <div class="tweak-table-card">
                            <div class="tweak-bulk-tools">
                                <button id="bulkSelectVisible" type="button" class="btn btn-default btn-xs">Select Visible</button>
                                <button id="bulkClearSelection" type="button" class="btn btn-default btn-xs">Clear Selection</button>
                                <span id="bulkCount" class="tweak-bulk-count">0 selected</span>
                            </div>
                            <table class="table table-bordered table-hover table-striped" style="font-size:13px; margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th style="width:4%; text-align:center;"><input id="bulkToggleAll" type="checkbox" title="Select all visible"></th>
                                        <th style="width:5%">#</th>
                                        <th style="width:30%">Event</th>
                                        <th style="width:12%">Kind</th>
                                        <th style="width:31%">Current Rules</th>
                                        <th style="width:8%">Active</th>
                                        <th style="width:10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php $idx = 1; foreach ($eventsForTweaks as $ev): ?>
                                    <?php $tw = $tweaksMap[$ev->id] ?? null; ?>
                                    <?php
                                    $hasAny = $tw && ($tw->pinned_day || $tw->pinned_room_id || $tw->pinned_start_time || $tw->available_from_time || $tw->available_to_time);
                                    $pinnedRoomName = '';
                                    if ($tw && $tw->pinned_room_id) {
                                        foreach ($rooms as $rm) {
                                            if ($rm->id == $tw->pinned_room_id) {
                                                $pinnedRoomName = $rm->room_name;
                                                break;
                                            }
                                        }
                                    }
                                    $clearUrl = '';
                                    if ($tw) {
                                        $clearUrl = $this->Url->build(['controller'=>'schedulingtweaks','action'=>'clear',$convention_season_slug,$ev->id]);
                                    }
                                    ?>
                                    <tr class="tweak-row"
                                        data-search="<?php echo strtolower(h($ev->event_name . ' ' . $ev->event_id_number)); ?>"
                                        data-kind="<?php echo h($ev->event_kind_id); ?>"
                                        data-active="<?php echo $hasAny ? '1' : '0'; ?>"
                                        data-event-id="<?php echo (int)$ev->id; ?>">
                                        <td style="text-align:center; vertical-align:middle;">
                                            <input type="checkbox" class="js-bulk-event" value="<?php echo (int)$ev->id; ?>">
                                        </td>
                                        <td><?php echo $idx++; ?></td>
                                        <td>
                                            <strong><?php echo h($ev->event_name); ?></strong><br>
                                            <small class="text-muted"><?php echo h($ev->event_id_number); ?>
                                            &nbsp;|&nbsp; <?php echo h($ev->event_kind_id); ?></small>
                                        </td>
                                        <td><?php echo h($ev->event_kind_id); ?></td>
                                        <td>
                                            <?php if ($tw && $tw->pinned_day): ?>
                                                <span class="tweak-rule-chip">Day: <?php echo h($tw->pinned_day); ?></span>
                                            <?php endif; ?>
                                            <?php if ($tw && $tw->pinned_room_id): ?>
                                                <span class="tweak-rule-chip">Room: <?php echo h($pinnedRoomName ?: ('#'.$tw->pinned_room_id)); ?></span>
                                            <?php endif; ?>
                                            <?php if ($tw && $tw->pinned_start_time): ?>
                                                <span class="tweak-rule-chip">Start: <?php echo h(date('H:i', strtotime((string)$tw->pinned_start_time))); ?></span>
                                            <?php endif; ?>
                                            <?php if ($tw && ($tw->available_from_time || $tw->available_to_time)): ?>
                                                <span class="tweak-rule-chip">Window: <?php echo h(($tw->available_from_time ? date('H:i', strtotime((string)$tw->available_from_time)) : '...') . ' - ' . ($tw->available_to_time ? date('H:i', strtotime((string)$tw->available_to_time)) : '...')); ?></span>
                                            <?php endif; ?>
                                            <?php if (!$hasAny): ?>
                                                <span class="tweak-rule-chip dim">No active rules</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $hasAny
                                                ? '<span class="label label-warning">Active</span>'
                                                : '<span class="text-muted">None</span>'; ?>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-xs btn-primary js-edit-tweak"
                                                data-event-id="<?php echo (int)$ev->id; ?>"
                                                data-event-name="<?php echo h($ev->event_name); ?>"
                                                data-event-number="<?php echo h($ev->event_id_number); ?>"
                                                data-event-kind="<?php echo h($ev->event_kind_id); ?>"
                                                data-pinned-day="<?php echo h($tw->pinned_day ?? ''); ?>"
                                                data-pinned-room-id="<?php echo h($tw->pinned_room_id ?? ''); ?>"
                                                data-pinned-start-time="<?php echo h(($tw && $tw->pinned_start_time) ? date('H:i', strtotime((string)$tw->pinned_start_time)) : ''); ?>"
                                                data-available-from="<?php echo h(($tw && $tw->available_from_time) ? date('H:i', strtotime((string)$tw->available_from_time)) : ''); ?>"
                                                data-available-to="<?php echo h(($tw && $tw->available_to_time) ? date('H:i', strtotime((string)$tw->available_to_time)) : ''); ?>"
                                                data-clear-url="<?php echo h($clearUrl); ?>"
                                            >Edit</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="tweak-panel">
                            <div class="tweak-panel-head">
                                <h4 class="tweak-panel-title">Event Editor</h4>
                                <div id="sideMeta" class="tweak-panel-sub">Select an event row to edit its rules.</div>
                            </div>
                            <div class="tweak-panel-body">
                                <div id="sideEmpty" class="tweak-panel-empty">No event selected yet.</div>
                                <?php echo $this->Form->create(null, [
                                    'id' => 'sideTweakForm',
                                    'url' => ['controller'=>'schedulingtweaks','action'=>'save',$convention_season_slug],
                                    'method' => 'post',
                                ]); ?>
                                <?php echo $this->Form->hidden('event_id', ['id' => 'side_event_id']); ?>

                                <div class="form-group">
                                    <label class="tweak-editor-label">A - Day Restriction</label>
                                    <?php echo $this->Form->select('pinned_day',
                                        array_merge(['' => '-- Any day --'], array_combine($allowedDays, $allowedDays)),
                                        ['class' => 'form-control input-sm', 'id' => 'side_pinned_day', 'empty' => false]
                                    ); ?>
                                </div>

                                <div class="form-group">
                                    <label class="tweak-editor-label">B - Room Lock</label>
                                    <?php echo $this->Form->select('pinned_room_id',
                                        $roomOptions,
                                        ['class' => 'form-control input-sm', 'id' => 'side_pinned_room_id', 'empty' => false]
                                    ); ?>
                                </div>

                                <div class="form-group">
                                    <label class="tweak-editor-label">C - Fixed Start</label>
                                    <?php echo $this->Form->text('pinned_start_time', [
                                        'class'       => 'form-control input-sm mdtpicker',
                                        'id'          => 'side_pinned_start_time',
                                        'placeholder' => 'hh:mm (24hr)',
                                    ]); ?>
                                </div>

                                <div class="form-group">
                                    <label class="tweak-editor-label">D - Allowed Time Window (24hr)</label>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <?php echo $this->Form->text('available_from_time', [
                                                'class'       => 'form-control input-sm mdtpicker',
                                                'id'          => 'side_available_from_time',
                                                'placeholder' => 'From',
                                            ]); ?>
                                        </div>
                                        <div class="col-xs-6">
                                            <?php echo $this->Form->text('available_to_time', [
                                                'class'       => 'form-control input-sm mdtpicker',
                                                'id'          => 'side_available_to_time',
                                                'placeholder' => 'To',
                                            ]); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top:12px;">
                                    <?php echo $this->Form->button('Save Tweaks', ['class' => 'btn btn-primary btn-sm', 'id' => 'sideSaveBtn', 'disabled' => true]); ?>
                                    <a id="sideClearBtn" href="#" class="btn btn-danger btn-sm" style="display:none;" onclick="return confirm('Clear all tweaks for this event?');">Clear</a>
                                </div>
                                <?php echo $this->Form->end(); ?>

                                <div class="tweak-bulk-card">
                                    <h5 class="tweak-bulk-title">Bulk Edit Selected Events</h5>
                                    <?php echo $this->Form->create(null, [
                                        'id' => 'bulkTweakForm',
                                        'url' => ['controller'=>'schedulingtweaks','action'=>'bulksave',$convention_season_slug],
                                        'method' => 'post',
                                    ]); ?>
                                    <div id="bulkEventIdsWrap"></div>

                                    <div class="tweak-bulk-field">
                                        <label style="font-weight:normal; margin-bottom:5px;"><input type="checkbox" id="bulk_apply_pinned_day" name="apply_pinned_day" value="1"> Apply A - Day Restriction</label>
                                        <?php echo $this->Form->select('pinned_day',
                                            array_merge(['' => '-- Any day --'], array_combine($allowedDays, $allowedDays)),
                                            ['class' => 'form-control input-sm', 'id' => 'bulk_pinned_day', 'empty' => false]
                                        ); ?>
                                    </div>

                                    <div class="tweak-bulk-field">
                                        <label style="font-weight:normal; margin-bottom:5px;"><input type="checkbox" id="bulk_apply_pinned_room_id" name="apply_pinned_room_id" value="1"> Apply B - Room Lock</label>
                                        <?php echo $this->Form->select('pinned_room_id',
                                            $roomOptions,
                                            ['class' => 'form-control input-sm', 'id' => 'bulk_pinned_room_id', 'empty' => false]
                                        ); ?>
                                    </div>

                                    <div class="tweak-bulk-field">
                                        <label style="font-weight:normal; margin-bottom:5px;"><input type="checkbox" id="bulk_apply_pinned_start_time" name="apply_pinned_start_time" value="1"> Apply C - Fixed Start</label>
                                        <?php echo $this->Form->text('pinned_start_time', [
                                            'class'       => 'form-control input-sm mdtpicker',
                                            'id'          => 'bulk_pinned_start_time',
                                            'placeholder' => 'hh:mm (24hr)',
                                        ]); ?>
                                    </div>

                                    <div class="tweak-bulk-field">
                                        <label style="font-weight:normal; margin-bottom:5px;"><input type="checkbox" id="bulk_apply_available_window" name="apply_available_window" value="1"> Apply D - Allowed Time Window</label>
                                        <div class="row">
                                            <div class="col-xs-6">
                                                <?php echo $this->Form->text('available_from_time', [
                                                    'class'       => 'form-control input-sm mdtpicker',
                                                    'id'          => 'bulk_available_from_time',
                                                    'placeholder' => 'From',
                                                ]); ?>
                                            </div>
                                            <div class="col-xs-6">
                                                <?php echo $this->Form->text('available_to_time', [
                                                    'class'       => 'form-control input-sm mdtpicker',
                                                    'id'          => 'bulk_available_to_time',
                                                    'placeholder' => 'To',
                                                ]); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <?php echo $this->Form->button('Apply To Selected', ['class' => 'btn btn-warning btn-sm', 'id' => 'bulkApplyBtn', 'disabled' => true]); ?>
                                    </div>
                                    <?php echo $this->Form->end(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    (function () {
                        var searchInput = document.getElementById('tweakSearch');
                        var kindFilter = document.getElementById('tweakKindFilter');
                        var activeOnly = document.getElementById('tweakActiveOnly');
                        var clearBtn = document.getElementById('tweakClearFilters');
                        var sideMeta = document.getElementById('sideMeta');
                        var sideEmpty = document.getElementById('sideEmpty');
                        var sideEventId = document.getElementById('side_event_id');
                        var sidePinnedDay = document.getElementById('side_pinned_day');
                        var sidePinnedRoomId = document.getElementById('side_pinned_room_id');
                        var sidePinnedStart = document.getElementById('side_pinned_start_time');
                        var sideAvailFrom = document.getElementById('side_available_from_time');
                        var sideAvailTo = document.getElementById('side_available_to_time');
                        var sideSaveBtn = document.getElementById('sideSaveBtn');
                        var sideClearBtn = document.getElementById('sideClearBtn');
                        var bulkToggleAll = document.getElementById('bulkToggleAll');
                        var bulkSelectVisible = document.getElementById('bulkSelectVisible');
                        var bulkClearSelection = document.getElementById('bulkClearSelection');
                        var bulkCount = document.getElementById('bulkCount');
                        var bulkApplyBtn = document.getElementById('bulkApplyBtn');
                        var bulkWrap = document.getElementById('bulkEventIdsWrap');
                        var rows = [].slice.call(document.querySelectorAll('tr.tweak-row'));

                        function getVisibleRows() {
                            return rows.filter(function (row) {
                                return row.style.display !== 'none';
                            });
                        }

                        function selectedIds() {
                            return rows.map(function (row) {
                                var cb = row.querySelector('.js-bulk-event');
                                return cb && cb.checked ? cb.value : null;
                            }).filter(function (id) { return !!id; });
                        }

                        function syncBulkState() {
                            var ids = selectedIds();
                            bulkCount.textContent = ids.length + ' selected';
                            bulkApplyBtn.disabled = ids.length === 0;
                            bulkWrap.innerHTML = '';
                            ids.forEach(function (id) {
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'event_ids[]';
                                input.value = id;
                                bulkWrap.appendChild(input);
                            });
                        }

                        function applyFilters() {
                            var q = (searchInput.value || '').toLowerCase().trim();
                            var kind = kindFilter.value || '';
                            var active = activeOnly.checked;

                            rows.forEach(function (row) {
                                var text = (row.getAttribute('data-search') || '').toLowerCase();
                                var rowKind = row.getAttribute('data-kind') || '';
                                var isActive = row.getAttribute('data-active') === '1';
                                var match = true;

                                if (q && text.indexOf(q) === -1) match = false;
                                if (kind && rowKind !== kind) match = false;
                                if (active && !isActive) match = false;

                                row.style.display = match ? '' : 'none';
                            });

                            if (bulkToggleAll) {
                                bulkToggleAll.checked = false;
                            }
                        }

                        searchInput.addEventListener('input', applyFilters);
                        kindFilter.addEventListener('change', applyFilters);
                        activeOnly.addEventListener('change', applyFilters);
                        clearBtn.addEventListener('click', function () {
                            searchInput.value = '';
                            kindFilter.value = '';
                            activeOnly.checked = false;
                            applyFilters();
                        });

                        rows.forEach(function (row) {
                            var cb = row.querySelector('.js-bulk-event');
                            if (!cb) return;
                            cb.addEventListener('change', syncBulkState);
                        });

                        bulkSelectVisible.addEventListener('click', function () {
                            getVisibleRows().forEach(function (row) {
                                var cb = row.querySelector('.js-bulk-event');
                                if (cb) cb.checked = true;
                            });
                            syncBulkState();
                        });

                        bulkClearSelection.addEventListener('click', function () {
                            rows.forEach(function (row) {
                                var cb = row.querySelector('.js-bulk-event');
                                if (cb) cb.checked = false;
                            });
                            if (bulkToggleAll) {
                                bulkToggleAll.checked = false;
                            }
                            syncBulkState();
                        });

                        bulkToggleAll.addEventListener('change', function () {
                            var makeChecked = !!bulkToggleAll.checked;
                            getVisibleRows().forEach(function (row) {
                                var cb = row.querySelector('.js-bulk-event');
                                if (cb) cb.checked = makeChecked;
                            });
                            syncBulkState();
                        });

                        document.querySelectorAll('.js-edit-tweak').forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                var eventId = btn.getAttribute('data-event-id') || '';
                                if (!eventId) return;

                                rows.forEach(function (r) { r.classList.remove('tweak-selected'); });
                                var row = btn.closest('tr.tweak-row');
                                if (row) {
                                    row.classList.add('tweak-selected');
                                }

                                sideMeta.textContent = btn.getAttribute('data-event-name') + ' (' + btn.getAttribute('data-event-number') + ') | ' + btn.getAttribute('data-event-kind');
                                sideEmpty.style.display = 'none';

                                sideEventId.value = eventId;
                                sidePinnedDay.value = btn.getAttribute('data-pinned-day') || '';
                                sidePinnedRoomId.value = btn.getAttribute('data-pinned-room-id') || '';
                                sidePinnedStart.value = btn.getAttribute('data-pinned-start-time') || '';
                                sideAvailFrom.value = btn.getAttribute('data-available-from') || '';
                                sideAvailTo.value = btn.getAttribute('data-available-to') || '';
                                sideSaveBtn.disabled = false;

                                var clearUrl = btn.getAttribute('data-clear-url') || '';
                                if (clearUrl) {
                                    sideClearBtn.href = clearUrl;
                                    sideClearBtn.style.display = '';
                                } else {
                                    sideClearBtn.href = '#';
                                    sideClearBtn.style.display = 'none';
                                }
                            });
                        });

                        applyFilters();
                        syncBulkState();
                    })();
                    </script>

                    <button id="tweakScrollTopBtn" type="button" title="Scroll to top" aria-label="Scroll to top">
                        <i class="fa fa-arrow-up"></i>
                    </button>
                    <script>
                    (function () {
                        var btn = document.getElementById('tweakScrollTopBtn');
                        if (!btn) return;
                        function toggle() {
                            btn.style.display = (window.pageYOffset > 300) ? 'block' : 'none';
                        }
                        window.addEventListener('scroll', toggle, { passive: true });
                        btn.addEventListener('click', function () {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });
                        toggle();
                    })();
                    </script>
                <?php endif; ?>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <?php echo $this->Html->link(
                    '<i class="fa fa-arrow-left"></i> Back to Pre-check',
                    ['controller'=>'schedulings','action'=>'precheck',$convention_season_slug],
                    ['class'=>'btn btn-default', 'escape'=>false]
                ); ?>
            </div>
        </div>
    </section>
</div>
