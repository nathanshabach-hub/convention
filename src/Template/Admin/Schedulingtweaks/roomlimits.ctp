<?php /* Room Time Allocation – roomlimits.php */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Room Time Allocation – [<?php echo h($conventionSD->Conventions['name']); ?>]
            &nbsp; [Season Year: <?php echo h($conventionSD->season_year); ?>]
        </h1>
        <ol class="breadcrumb">
            <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard',
                ['controller'=>'admins','action'=>'dashboard'], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Conventions',
                ['controller'=>'conventions','action'=>'index'], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Seasons',
                ['controller'=>'conventions','action'=>'seasons',$convention_slug], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Scheduling Tweaks',
                ['controller'=>'schedulingtweaks','action'=>'index',$convention_season_slug], ['escape'=>false]); ?></li>
            <li class="active">Room Time Allocation</li>
        </ol>
    </section>

    <section class="content">

        <style>
            .rla-table th, .rla-table td { vertical-align: middle; font-size: 13px; }
            .rla-table th { background: #2f3c7e; color: #fff; }
            .rla-room-name { font-weight: 600; min-width: 160px; }
            .rla-bar-wrap { 
                background: #e9ecef;
                border-radius: 4px; height: 16px; min-width: 80px; position: relative; overflow: visible; 
                font-size: 9px; font-weight: 600; 
            }
            .rla-bar-wrap::before { 
                content: 'AM'; position: absolute; left: 3px; top: 50%; transform: translateY(-50%); 
                color: #555; z-index: 3; pointer-events: none; 
            }
            .rla-bar-wrap::after { 
                content: 'PM'; position: absolute; right: 3px; top: 50%; transform: translateY(-50%); 
                color: #555; z-index: 3; pointer-events: none; 
            }
            .rla-bar-wrap > .rla-divider { 
                position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; 
                background: #999; z-index: 10; transform: translateX(-50%); border-radius: 0; 
                pointer-events: none;
            }
            .rla-bar-wrap::after { content: 'PM'; position: absolute; right: 3px; top: 50%; transform: translateY(-50%); color: #555; z-index: 2; pointer-events: none; }
            .rla-bar { height: 100%; border-radius: 4px; transition: width 0.3s; position: relative; }
            .rla-bar.ok      { background: #28a745; }
            .rla-bar.warn    { background: #ffc107; }
            .rla-bar.over    { background: #dc3545; }
            .rla-input { width: 70px; text-align: center; }
            .rla-mins  { font-size: 11px; color: #666; }
            .rla-day-header { text-align: center; min-width: 120px; }
            .rla-window { font-size: 11px; color: #888; }
            .rla-zero-duration {
                display: inline-block;
                margin-top: 4px;
                padding: 2px 6px;
                font-size: 11px;
                font-weight: 600;
                color: #856404;
                background: #fff3cd;
                border: 1px solid #ffeeba;
                border-radius: 3px;
            }
            .rla-cell-zero {
                background: #fff9e6;
            }
        </style>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Room Time Allocation</h3>
                <div class="box-tools pull-right">
                    <?php echo $this->Html->link(
                        '<i class="fa fa-clock-o"></i> Room Availability Windows',
                        ['controller'=>'schedulingtweaks','action'=>'roomavailability',$convention_season_slug],
                        ['class'=>'btn btn-sm btn-default', 'escape'=>false]
                    ); ?>
                </div>
            </div>
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div class="box-body">
                <p class="text-muted">
                    View how many hours are booked per room per day, and optionally set a
                    <strong>Max Hours</strong> cap. The progress bar turns
                    <span style="color:#28a745;font-weight:600;">green</span> (&lt;80%),
                    <span style="color:#ffc107;font-weight:600;">yellow</span> (80–100%), or
                    <span style="color:#dc3545;font-weight:600;">red</span> (over limit).
                    Leave Max Hours blank for no limit.
                </p>

                <?php if (empty($allowedDays)): ?>
                    <div class="alert alert-warning">No convention days configured. Run the Scheduling Wizard first.</div>
                <?php elseif (empty($rooms->toArray())): ?>
                    <div class="alert alert-warning">No rooms found for this convention.</div>
                <?php else: ?>

                <?php echo $this->Form->create(null, [
                    'url'    => ['controller'=>'schedulingtweaks','action'=>'roomlimits',$convention_season_slug],
                    'method' => 'post',
                ]); ?>

                <div class="table-responsive">
                <table class="table table-bordered rla-table">
                    <thead>
                        <tr>
                            <th style="width:180px;">Room</th>
                            <?php foreach ($allowedDays as $day): ?>
                            <th class="rla-day-header"><?php echo h($day); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td class="rla-room-name">
                                <?php echo h($room->room_name); ?>
                                <?php if ($room->available_from || $room->available_to): ?>
                                <div class="rla-window">
                                    Window: <?php
                                        echo ($room->available_from ? date('H:i', strtotime((string)$room->available_from)) : '—')
                                           . ' → '
                                           . ($room->available_to ? date('H:i', strtotime((string)$room->available_to)) : '—');
                                    ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <?php foreach ($allowedDays as $day):
                                $bookedMin  = $booked[$room->id][$day] ?? 0;
                                $bookedHrs  = round($bookedMin / 60, 2);
                                $maxHrs     = $limits[$room->id][$day] ?? null;
                                $scheduledCount = $scheduledCounts[$room->id][$day] ?? 0;

                                /* Window hours */
                                $winFrom = $room->available_from ? strtotime($room->available_from) : strtotime('08:00');
                                $winTo   = $room->available_to   ? strtotime($room->available_to)   : strtotime('18:00');
                                $winHrs  = max(0, ($winTo - $winFrom) / 3600);

                                /* Progress bar */
                                $capHrs = $maxHrs ?? $winHrs;
                                if ($capHrs > 0 && $bookedMin > 0) {
                                    $pct = min(($bookedHrs / $capHrs) * 100, 100);
                                    $barClass = ($pct >= 100) ? 'over' : (($pct >= 80) ? 'warn' : 'ok');
                                } else {
                                    $pct = 0; $barClass = 'ok';
                                }
                            ?>
                            <?php
                                $isZeroDurationScheduled = ($scheduledCount > 0 && $bookedMin <= 0);
                            ?>
                            <td class="<?php echo $isZeroDurationScheduled ? 'rla-cell-zero' : ''; ?>" style="text-align:center; padding: 6px 10px;">
                                <?php if ($bookedMin > 0 || $scheduledCount > 0): ?>
                                <div class="rla-mins">
                                    <strong><?php echo number_format($bookedHrs, 1); ?>h</strong>
                                    booked<?php if ($maxHrs !== null): ?> / <?php echo number_format($maxHrs, 1); ?>h max<?php endif; ?>
                                    <?php if ($scheduledCount > 0): ?>
                                        <br><span class="text-muted"><?php echo (int)$scheduledCount; ?> scheduled item<?php echo $scheduledCount === 1 ? '' : 's'; ?></span>
                                    <?php endif; ?>
                                    <?php if ($isZeroDurationScheduled): ?>
                                        <br><span class="rla-zero-duration">Zero-duration timings detected</span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($bookedMin > 0): ?>
                                <div class="rla-bar-wrap" title="<?php echo number_format($bookedHrs,2); ?>h booked">
                                    <div class="rla-bar <?php echo $barClass; ?>" style="width:<?php echo number_format($pct,1); ?>%"></div>
                                    <div class="rla-divider"></div>
                                </div>
                                <?php endif; ?>
                                <?php else: ?>
                                <span class="text-muted" style="font-size:11px;">—</span>
                                <?php endif; ?>
                                <div style="margin-top:4px;">
                                    <?php echo $this->Form->text(
                                        'max_hours[' . $room->id . '][' . $day . ']',
                                        [
                                            'class'       => 'form-control input-sm rla-input',
                                            'placeholder' => 'Max h',
                                            'value'       => $maxHrs !== null ? number_format((float)$maxHrs, 2, '.', '') : '',
                                            'title'       => 'Max hours for ' . $room->room_name . ' on ' . $day,
                                        ]
                                    ); ?>
                                </div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <div style="margin-top:16px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Max Hours
                    </button>
                    <span class="text-muted" style="margin-left:12px; font-size:12px;">
                        All rooms are shown, including rooms with no scheduled events yet.
                    </span>
                </div>

                <?php echo $this->Form->end(); ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>
