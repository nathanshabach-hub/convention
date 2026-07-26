<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Small Program - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
          [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Small Program</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

            <div class="admin_search">
               <div class="admin_asearch">
                <div class="add_new_record">
                <?php echo $this->Html->link('<i class="fa fa-print"></i> Print / Save PDF', ['controller'=>'schedulingreports', 'action'=>'smallprogramprint',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);?>
                <?php echo $this->Html->link('Back', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-warning']);?>
                </div>
            </div>
            </div>

            <?php echo $this->Form->create(null, ['id' => 'small-program-form', 'url' => ['controller'=>'schedulingreports', 'action'=>'smallprogram', $convention_season_slug], 'class' => 'form-horizontal']); ?>

            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Schedulingreports/smallprogram_booklet"); ?>
            </div>

            <div class="box box-default" style="margin:20px 20px 0;">
                <div class="box-body" style="padding:10px 15px;">
                    <strong>Inline Card Editing Enabled:</strong> click <strong>Edit</strong> on any room card, change event lines, click <strong>Apply</strong>, then click <strong>Save Card Changes</strong>.
                    <div style="margin-top:8px;">
                        <?php echo $this->Form->button('Save Card Changes', ['type'=>'submit', 'class' => 'btn btn-success']); ?>
                    </div>
                    <div id="sp-inline-overrides" style="display:none;">
                        <?php
                        $existingOverrideRows = isset($smallProgramNotes['event_overrides']) && is_array($smallProgramNotes['event_overrides']) ? $smallProgramNotes['event_overrides'] : array();
                        foreach ($existingOverrideRows as $row) {
                            $rowDay = trim((string)($row['day'] ?? ''));
                            $rowSession = strtolower(trim((string)($row['session'] ?? '')));
                            $rowRoom = trim((string)($row['room'] ?? ''));
                            $rowEvents = $row['events'] ?? array();
                            if (!is_array($rowEvents)) {
                                $rowEvents = preg_split('/\r\n|\r|\n/', (string)$rowEvents);
                            }
                            $rowEventsText = implode("\n", array_filter(array_map('trim', $rowEvents), function($v){ return $v !== ''; }));
                            $rowKey = strtolower($rowDay.'|'.$rowSession.'|'.$rowRoom);
                        ?>
                        <div class="sp-inline-override-row" data-key="<?php echo h($rowKey); ?>" data-day="<?php echo h($rowDay); ?>" data-session="<?php echo h($rowSession); ?>" data-room="<?php echo h($rowRoom); ?>">
                            <input type="hidden" name="Smallprogramnotes[override_day][]" value="<?php echo h($rowDay); ?>" />
                            <input type="hidden" name="Smallprogramnotes[override_session][]" value="<?php echo h($rowSession); ?>" />
                            <input type="hidden" name="Smallprogramnotes[override_room][]" value="<?php echo h($rowRoom); ?>" />
                            <textarea name="Smallprogramnotes[override_events][]" style="display:none;"><?php echo h($rowEventsText); ?></textarea>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="box box-default" style="margin:20px;">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Small Program Notes</h3>
                </div>
                <div class="box-body">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Intro Day Heading</label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->text('Smallprogramnotes.intro_day_label', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['intro_day_label'] ?? '']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Intro Entries</label>
                        <div class="col-sm-10">
                            <table class="table table-condensed" id="intro-entries-table" style="margin-bottom:6px;">
                                <thead>
                                    <tr>
                                        <th style="width:200px;">Time</th>
                                        <th>Description</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="intro-entries-body">
                                <?php
                                $introEntriesRaw = trim((string)($smallProgramNotes['intro_entries'] ?? ''));
                                $introRows = array();
                                if ($introEntriesRaw !== '') {
                                    foreach (preg_split('/\r\n|\r|\n/', $introEntriesRaw) as $ln) {
                                        $ln = trim((string)$ln);
                                        if ($ln === '') continue;
                                        $parts = explode('|', $ln, 2);
                                        $introRows[] = array('time' => trim((string)($parts[0] ?? '')), 'text' => trim((string)($parts[1] ?? '')));
                                    }
                                }
                                if (empty($introRows)) {
                                    $introRows[] = array('time' => '', 'text' => '');
                                }
                                foreach ($introRows as $row) { ?>
                                    <tr class="intro-entry-row">
                                        <td><input type="text" name="Smallprogramnotes[intro_time][]" class="form-control input-sm" value="<?php echo h($row['time']); ?>" placeholder="e.g. 4:00 pm - 5:00 pm" /></td>
                                        <td><input type="text" name="Smallprogramnotes[intro_text][]" class="form-control input-sm" value="<?php echo h($row['text']); ?>" placeholder="Description" /></td>
                                        <td><button type="button" class="btn btn-xs btn-danger intro-remove-row" title="Remove row">&times;</button></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-xs btn-success" id="intro-add-row"><i class="fa fa-plus"></i> Add Row</button>
                        </div>
                    </div>
                    <script>
                    (function(){
                        document.getElementById('intro-add-row').addEventListener('click', function(){
                            var tbody = document.getElementById('intro-entries-body');
                            var tr = document.createElement('tr');
                            tr.className = 'intro-entry-row';
                            tr.innerHTML = '<td><input type="text" name="Smallprogramnotes[intro_time][]" class="form-control input-sm" placeholder="e.g. 4:00 pm - 5:00 pm" /></td>'
                                + '<td><input type="text" name="Smallprogramnotes[intro_text][]" class="form-control input-sm" placeholder="Description" /></td>'
                                + '<td><button type="button" class="btn btn-xs btn-danger intro-remove-row" title="Remove row">&times;</button></td>';
                            tbody.appendChild(tr);
                        });
                        document.getElementById('intro-entries-body').addEventListener('click', function(e){
                            if (e.target && e.target.classList.contains('intro-remove-row')) {
                                var row = e.target.closest('tr');
                                if (row) row.parentNode.removeChild(row);
                            }
                        });
                    })();
                    </script>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Dinner Banner</label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->text('Smallprogramnotes.dinner_banner', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['dinner_banner'] ?? '']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Evening Rally Time</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.evening_rally_time', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['evening_rally_time'] ?? '']); ?>
                        </div>
                        <label class="col-sm-2 control-label">Evening Rally Label</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.evening_rally_label', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['evening_rally_label'] ?? '']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Evening Rally Entries</label>
                        <div class="col-sm-10">
                            <table class="table table-condensed" id="evening-entries-table" style="margin-bottom:6px;">
                                <thead>
                                    <tr>
                                        <th style="width:180px;">Day</th>
                                        <th style="width:180px;">Start Time</th>
                                        <th style="width:180px;">End Time</th>
                                        <th>Activity</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="evening-entries-body">
                                <?php
                                $eveningEntriesRaw = trim((string)($smallProgramNotes['evening_rally_entries'] ?? ''));
                                $eveningRows = array();
                                if ($eveningEntriesRaw !== '') {
                                    foreach (preg_split('/\r\n|\r|\n/', $eveningEntriesRaw) as $ln) {
                                        $ln = trim((string)$ln);
                                        if ($ln === '') continue;
                                        $parts = explode('|', $ln, 4);
                                        if (count($parts) >= 4) {
                                            $day = trim((string)($parts[0] ?? ''));
                                            $start = trim((string)($parts[1] ?? ''));
                                            $end = trim((string)($parts[2] ?? ''));
                                            $text = trim((string)($parts[3] ?? ''));
                                        } else {
                                            $day = '';
                                            $start = trim((string)($parts[0] ?? ''));
                                            $end = trim((string)($parts[1] ?? ''));
                                            $text = trim((string)($parts[2] ?? ''));
                                        }
                                        $eveningRows[] = array(
                                            'day' => $day,
                                            'start' => $start,
                                            'end' => $end,
                                            'text' => $text,
                                        );
                                    }
                                }
                                if (empty($eveningRows)) {
                                    $eveningRows[] = array('day' => '', 'start' => '', 'end' => '', 'text' => '');
                                }
                                $eveningDayOptions = array(
                                    '' => 'Select day',
                                    'Sunday' => 'Sunday',
                                    'Monday' => 'Monday',
                                    'Tuesday' => 'Tuesday',
                                    'Wednesday' => 'Wednesday',
                                    'Thursday' => 'Thursday',
                                    'Friday' => 'Friday',
                                    'Saturday' => 'Saturday',
                                );
                                foreach ($eveningRows as $row) { ?>
                                    <tr class="evening-entry-row">
                                        <td>
                                            <select name="Smallprogramnotes[evening_rally_day][]" class="form-control input-sm">
                                                <?php foreach ($eveningDayOptions as $dayValue => $dayLabel) { ?>
                                                    <option value="<?php echo h($dayValue); ?>"<?php echo ((string)$row['day'] === (string)$dayValue) ? ' selected' : ''; ?>><?php echo h($dayLabel); ?></option>
                                                <?php } ?>
                                                <?php if ($row['day'] !== '' && !isset($eveningDayOptions[$row['day']])) { ?>
                                                    <option value="<?php echo h($row['day']); ?>" selected><?php echo h($row['day']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td><input type="text" name="Smallprogramnotes[evening_rally_time_start][]" class="form-control input-sm" value="<?php echo h($row['start']); ?>" placeholder="e.g. 6:30 pm" /></td>
                                        <td><input type="text" name="Smallprogramnotes[evening_rally_time_end][]" class="form-control input-sm" value="<?php echo h($row['end']); ?>" placeholder="e.g. 7:00 pm" /></td>
                                        <td><input type="text" name="Smallprogramnotes[evening_rally_text][]" class="form-control input-sm" value="<?php echo h($row['text']); ?>" placeholder="What happens after dinner" /></td>
                                        <td><button type="button" class="btn btn-xs btn-danger evening-remove-row" title="Remove row">&times;</button></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-xs btn-success" id="evening-add-row"><i class="fa fa-plus"></i> Add Row</button>
                        </div>
                    </div>
                    <script>
                    (function(){
                        document.getElementById('evening-add-row').addEventListener('click', function(){
                            var tbody = document.getElementById('evening-entries-body');
                            var tr = document.createElement('tr');
                            tr.className = 'evening-entry-row';
                            tr.innerHTML = '<td><select name="Smallprogramnotes[evening_rally_day][]" class="form-control input-sm"><option value="" selected>Select day</option><option value="Sunday">Sunday</option><option value="Monday">Monday</option><option value="Tuesday">Tuesday</option><option value="Wednesday">Wednesday</option><option value="Thursday">Thursday</option><option value="Friday">Friday</option><option value="Saturday">Saturday</option></select></td>'
                                + '<td><input type="text" name="Smallprogramnotes[evening_rally_time_start][]" class="form-control input-sm" placeholder="e.g. 6:30 pm" /></td>'
                                + '<td><input type="text" name="Smallprogramnotes[evening_rally_time_end][]" class="form-control input-sm" placeholder="e.g. 7:00 pm" /></td>'
                                + '<td><input type="text" name="Smallprogramnotes[evening_rally_text][]" class="form-control input-sm" placeholder="What happens after dinner" /></td>'
                                + '<td><button type="button" class="btn btn-xs btn-danger evening-remove-row" title="Remove row">&times;</button></td>';
                            tbody.appendChild(tr);
                        });
                        document.getElementById('evening-entries-body').addEventListener('click', function(e){
                            if (e.target && e.target.classList.contains('evening-remove-row')) {
                                var row = e.target.closest('tr');
                                if (row) row.parentNode.removeChild(row);
                            }
                        });
                    })();
                    </script>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Awards Ceremony Time</label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->text('Smallprogramnotes.awards_ceremony_time', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['awards_ceremony_time'] ?? '', 'placeholder'=>'e.g. 9:00 am - 1:00 pm']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Awards Ceremony Entries</label>
                        <div class="col-sm-10">
                            <table class="table table-condensed" id="awards-entries-table" style="margin-bottom:6px;">
                                <thead>
                                    <tr>
                                        <th style="width:180px;">Start Time</th>
                                        <th style="width:180px;">End Time</th>
                                        <th>Activity</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="awards-entries-body">
                                <?php
                                $awardsEntriesRaw = trim((string)($smallProgramNotes['awards_entries'] ?? ''));
                                $awardsRows = array();
                                if ($awardsEntriesRaw !== '') {
                                    foreach (preg_split('/\r\n|\r|\n/', $awardsEntriesRaw) as $ln) {
                                        $ln = trim((string)$ln);
                                        if ($ln === '') continue;
                                        $parts = explode('|', $ln, 3);
                                        $awardsRows[] = array(
                                            'start' => trim((string)($parts[0] ?? '')),
                                            'end' => trim((string)($parts[1] ?? '')),
                                            'text' => trim((string)($parts[2] ?? '')),
                                        );
                                    }
                                }
                                if (empty($awardsRows)) {
                                    $awardsRows[] = array('start' => '', 'end' => '', 'text' => '');
                                }
                                foreach ($awardsRows as $row) { ?>
                                    <tr class="awards-entry-row">
                                        <td><input type="text" name="Smallprogramnotes[awards_time_start][]" class="form-control input-sm" value="<?php echo h($row['start']); ?>" placeholder="e.g. 9:00 am" /></td>
                                        <td><input type="text" name="Smallprogramnotes[awards_time_end][]" class="form-control input-sm" value="<?php echo h($row['end']); ?>" placeholder="e.g. 9:30 am" /></td>
                                        <td><input type="text" name="Smallprogramnotes[awards_text][]" class="form-control input-sm" value="<?php echo h($row['text']); ?>" placeholder="What happens" /></td>
                                        <td><button type="button" class="btn btn-xs btn-danger awards-remove-row" title="Remove row">&times;</button></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-xs btn-success" id="awards-add-row"><i class="fa fa-plus"></i> Add Row</button>
                        </div>
                    </div>
                    <script>
                    (function(){
                        document.getElementById('awards-add-row').addEventListener('click', function(){
                            var tbody = document.getElementById('awards-entries-body');
                            var tr = document.createElement('tr');
                            tr.className = 'awards-entry-row';
                            tr.innerHTML = '<td><input type="text" name="Smallprogramnotes[awards_time_start][]" class="form-control input-sm" placeholder="e.g. 9:00 am" /></td>'
                                + '<td><input type="text" name="Smallprogramnotes[awards_time_end][]" class="form-control input-sm" placeholder="e.g. 9:30 am" /></td>'
                                + '<td><input type="text" name="Smallprogramnotes[awards_text][]" class="form-control input-sm" placeholder="What happens" /></td>'
                                + '<td><button type="button" class="btn btn-xs btn-danger awards-remove-row" title="Remove row">&times;</button></td>';
                            tbody.appendChild(tr);
                        });
                        document.getElementById('awards-entries-body').addEventListener('click', function(e){
                            if (e.target && e.target.classList.contains('awards-remove-row')) {
                                var row = e.target.closest('tr');
                                if (row) row.parentNode.removeChild(row);
                            }
                        });
                    })();
                    </script>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Offsite Note</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.offsite_note', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['offsite_note'] ?? '']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Footer Note</label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->text('Smallprogramnotes.footer_note', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['footer_note'] ?? '']); ?>
                        </div>
                    </div>

                    <hr>
                    <h4 style="margin-left:15px;"><i class="fa fa-flag"></i> Athletics Day</h4>
                    <p class="col-sm-offset-2 col-sm-10 text-muted" style="margin-bottom:10px;">Fill in these fields only if your convention has an Athletics day. Leave blank to hide from the program.</p>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Athletics Day Label</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.athletics_day_label', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['athletics_day_label'] ?? '', 'placeholder'=>'e.g. TUESDAY 8TH JULY, 2026']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Arrive Time</label>
                        <div class="col-sm-3">
                            <?php echo $this->Form->text('Smallprogramnotes.athletics_arrive_time', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['athletics_arrive_time'] ?? '', 'placeholder'=>'e.g. 8:15 am']); ?>
                        </div>
                        <label class="col-sm-2 control-label">Arrive Venue</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.athletics_arrive_venue', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['athletics_arrive_venue'] ?? '', 'placeholder'=>'e.g. ANZ Stadium']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Begin Time</label>
                        <div class="col-sm-3">
                            <?php echo $this->Form->text('Smallprogramnotes.athletics_begin_time', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['athletics_begin_time'] ?? '', 'placeholder'=>'e.g. 8:30 am']); ?>
                        </div>
                        <label class="col-sm-2 control-label">Begin Label</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.athletics_begin_label', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['athletics_begin_label'] ?? '', 'placeholder'=>'e.g. Athletics Begins - BE PROMPT!']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Order of Events</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->textarea('Smallprogramnotes.athletics_order_of_events', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'rows'=>6, 'value'=>$smallProgramNotes['athletics_order_of_events'] ?? '', 'placeholder'=>"One event per line\ne.g.\n1500m\n800m\n100m Heats"]); ?>
                            <p class="help-block">One event per line.</p>
                        </div>
                        <label class="col-sm-2 control-label">Important Items</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->textarea('Smallprogramnotes.athletics_important_items', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'rows'=>6, 'value'=>$smallProgramNotes['athletics_important_items'] ?? '', 'placeholder'=>"One item per line\ne.g.\nWacky hat\nPlenty of drinking water\nSunscreen"]); ?>
                            <p class="help-block">One item per line. Shows as "IMPORTANT! Please remember to bring:" list.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Athletics Banner</label>
                        <div class="col-sm-6">
                            <?php echo $this->Form->text('Smallprogramnotes.athletics_banner', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['athletics_banner'] ?? '', 'placeholder'=>"e.g. It's wacky hat day, so bring your wacky hat!"]); ?>
                            <p class="help-block">Optional big banner text shown below the order of events.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Athletics Offsite Note</label>
                        <div class="col-sm-6">
                            <?php echo $this->Form->text('Smallprogramnotes.athletics_offsite_note', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['athletics_offsite_note'] ?? '', 'placeholder'=>'e.g. * To be confirmed']); ?>
                        </div>
                    </div>

                    <hr>
                    <h4 style="margin-left:15px;"><i class="fa fa-edit"></i> Room Event Editing</h4>
                    <p class="col-sm-offset-2 col-sm-10 text-muted" style="margin-bottom:10px;">Room event editing now happens directly on each room card above. Click <strong>Edit</strong> on a card, then <strong>Apply</strong>, then save.</p>
                </div>
                <div class="box-footer">
                    <label class="col-sm-2 control-label">&nbsp;</label>
                    <?php echo $this->Form->button('Save Notes', ['type'=>'submit', 'class' => 'btn btn-primary']); ?>
                </div>
                <script>
                (function(){
                    var form = document.getElementById('small-program-form');
                    var overridesContainer = document.getElementById('sp-inline-overrides');
                    if (!form || !overridesContainer) {
                        return;
                    }

                    function normalizeSession(value) {
                        value = (value || '').toString().trim().toLowerCase();
                        return value === '' ? 'day' : value;
                    }

                    function makeKey(day, session, room) {
                        return [(day || '').toString().trim().toLowerCase(), normalizeSession(session), (room || '').toString().trim().toLowerCase()].join('|');
                    }

                    function eventsFromList(listEl) {
                        return Array.prototype.map.call(listEl.querySelectorAll('li'), function(li){
                            if (li.classList.contains('sp-empty-event')) {
                                return '';
                            }
                            return (li.textContent || '').trim();
                        }).filter(function(v){ return v !== ''; });
                    }

                    function renderList(listEl, events) {
                        listEl.innerHTML = '';
                        if (!events.length) {
                            var emptyLi = document.createElement('li');
                            emptyLi.className = 'sp-empty-event';
                            emptyLi.textContent = '(No events)';
                            emptyLi.style.fontStyle = 'italic';
                            emptyLi.style.color = '#666';
                            listEl.appendChild(emptyLi);
                            return;
                        }
                        events.forEach(function(line){
                            var li = document.createElement('li');
                            li.textContent = line;
                            listEl.appendChild(li);
                        });
                    }

                    function upsertOverride(day, session, room, events) {
                        var key = makeKey(day, session, room);
                        var existing = null;
                        Array.prototype.forEach.call(overridesContainer.querySelectorAll('.sp-inline-override-row'), function(row){
                            if (existing) {
                                return;
                            }
                            if ((row.getAttribute('data-key') || '') === key) {
                                existing = row;
                            }
                        });
                        if (existing) {
                            existing.parentNode.removeChild(existing);
                        }

                        var row = document.createElement('div');
                        row.className = 'sp-inline-override-row';
                        row.setAttribute('data-key', key);
                        row.setAttribute('data-day', day);
                        row.setAttribute('data-session', normalizeSession(session));
                        row.setAttribute('data-room', room);

                        var dayInput = document.createElement('input');
                        dayInput.type = 'hidden';
                        dayInput.name = 'Smallprogramnotes[override_day][]';
                        dayInput.value = day;
                        row.appendChild(dayInput);

                        var sessionInput = document.createElement('input');
                        sessionInput.type = 'hidden';
                        sessionInput.name = 'Smallprogramnotes[override_session][]';
                        sessionInput.value = normalizeSession(session);
                        row.appendChild(sessionInput);

                        var roomInput = document.createElement('input');
                        roomInput.type = 'hidden';
                        roomInput.name = 'Smallprogramnotes[override_room][]';
                        roomInput.value = room;
                        row.appendChild(roomInput);

                        var eventsInput = document.createElement('textarea');
                        eventsInput.name = 'Smallprogramnotes[override_events][]';
                        eventsInput.style.display = 'none';
                        eventsInput.value = events.join('\n');
                        row.appendChild(eventsInput);

                        overridesContainer.appendChild(row);
                    }

                    document.addEventListener('click', function(e){
                        var editBtn = e.target.closest('.sp-card-edit-btn');
                        if (editBtn) {
                            var cardId = editBtn.getAttribute('data-card-id');
                            var editor = document.querySelector('.sp-card-editor[data-card-id="' + cardId + '"]');
                            var listEl = document.querySelector('.sp-room-card-events[data-card-id="' + cardId + '"]');
                            if (!editor || !listEl) return;
                            var textarea = editor.querySelector('.sp-card-editor-text');
                            textarea.value = eventsFromList(listEl).join('\n');
                            editor.style.display = 'block';
                            textarea.focus();
                            return;
                        }

                        var cancelBtn = e.target.closest('.sp-card-editor-cancel');
                        if (cancelBtn) {
                            var cancelCardId = cancelBtn.getAttribute('data-card-id');
                            var cancelEditor = document.querySelector('.sp-card-editor[data-card-id="' + cancelCardId + '"]');
                            var cancelList = document.querySelector('.sp-room-card-events[data-card-id="' + cancelCardId + '"]');
                            if (!cancelEditor || !cancelList) return;
                            var cancelTextarea = cancelEditor.querySelector('.sp-card-editor-text');
                            cancelTextarea.value = eventsFromList(cancelList).join('\n');
                            cancelEditor.style.display = 'none';
                            return;
                        }

                        var applyBtn = e.target.closest('.sp-card-editor-save');
                        if (applyBtn) {
                            var applyCardId = applyBtn.getAttribute('data-card-id');
                            var applyEditor = document.querySelector('.sp-card-editor[data-card-id="' + applyCardId + '"]');
                            var applyList = document.querySelector('.sp-room-card-events[data-card-id="' + applyCardId + '"]');
                            if (!applyEditor || !applyList) return;
                            var applyTextarea = applyEditor.querySelector('.sp-card-editor-text');
                            var events = applyTextarea.value.split(/\r\n|\r|\n/).map(function(v){ return v.trim(); }).filter(function(v){ return v !== ''; });
                            renderList(applyList, events);
                            upsertOverride(
                                applyList.getAttribute('data-day') || '',
                                applyList.getAttribute('data-session') || '',
                                applyList.getAttribute('data-room') || '',
                                events
                            );
                            applyEditor.style.display = 'none';
                            return;
                        }
                    });
                })();
                </script>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </section>
</div>
