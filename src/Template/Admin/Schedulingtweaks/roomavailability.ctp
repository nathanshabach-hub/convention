<?php /* Room Availability Windows – roomavailability.php */ ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Room Availability Windows – [<?php echo h($conventionSD->Conventions['name']); ?>]
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
            <li class="active">Room Availability Windows</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">D – Room Availability Windows</h3>
            </div>
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div class="box-body">
                <p class="text-muted">
                    Set a time window for each room. Scheduling will only place events in a room
                    during its available window. Leave both fields blank to use the convention's
                    normal start/finish times.
                </p>

                <?php echo $this->Form->create(null, [
                    'url'    => ['controller'=>'schedulingtweaks','action'=>'roomavailability',$convention_season_slug],
                    'method' => 'post',
                ]); ?>

                <table class="table table-bordered table-hover table-striped" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th style="width:5%">#</th>
                            <th style="width:45%">Room Name</th>
                            <th style="width:20%">Available From</th>
                            <th style="width:20%">Available To</th>
                            <th style="width:10%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $idx = 1; foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo $idx++; ?></td>
                            <td><strong><?php echo h($room->room_name); ?></strong></td>
                            <td>
                                <?php echo $this->Form->text(
                                    'available_from[' . $room->id . ']',
                                    [
                                        'class'       => 'form-control input-sm mdtpicker',
                                        'placeholder' => 'hh:mm (24hr)',
                                            'value'       => ($room->available_from)
                                                ? date('H:i', strtotime((string)$room->available_from)) : '',
                                    ]
                                ); ?>
                            </td>
                            <td>
                                <?php echo $this->Form->text(
                                    'available_to[' . $room->id . ']',
                                    [
                                        'class'       => 'form-control input-sm mdtpicker',
                                        'placeholder' => 'hh:mm (24hr)',
                                            'value'       => ($room->available_to)
                                                ? date('H:i', strtotime((string)$room->available_to)) : '',
                                    ]
                                ); ?>
                            </td>
                            <td>
                                <?php
                                if ($room->available_from || $room->available_to) {
                                    $from = $room->available_from ? date('H:i', strtotime((string)$room->available_from)) : '...';
                                    $to   = $room->available_to   ? date('H:i', strtotime((string)$room->available_to))   : '...';
                                    echo '<span class="label label-warning">' . h($from) . ' – ' . h($to) . '</span>';
                                } else {
                                    echo '<span class="text-muted">Convention hours</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="form-group" style="margin-top:10px;">
                    <?php echo $this->Form->button('Save Availability Windows', [
                        'class' => 'btn btn-primary'
                    ]); ?>
                    &nbsp;
                    <?php echo $this->Html->link(
                        'Cancel',
                        ['controller'=>'schedulingtweaks','action'=>'index',$convention_season_slug],
                        ['class'=>'btn btn-default']
                    ); ?>
                </div>

                <?php echo $this->Form->end(); ?>
            </div><!-- /.box-body -->
        </div>
    </section>
</div>
