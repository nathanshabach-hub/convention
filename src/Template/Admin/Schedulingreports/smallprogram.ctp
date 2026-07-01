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

            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Schedulingreports/smallprogram_booklet"); ?>
            </div>

            <div class="box box-default" style="margin:20px;">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Small Program Notes</h3>
                </div>
                <?php echo $this->Form->create(null, ['url' => ['controller'=>'schedulingreports', 'action'=>'smallprogram', $convention_season_slug], 'class' => 'form-horizontal']); ?>
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
                </div>
                <div class="box-footer">
                    <label class="col-sm-2 control-label">&nbsp;</label>
                    <?php echo $this->Form->button('Save Notes', ['type'=>'submit', 'class' => 'btn btn-primary']); ?>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </section>
</div>
