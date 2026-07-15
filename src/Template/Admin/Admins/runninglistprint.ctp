<?php
use Cake\ORM\TableRegistry;

$this->Crstudentevents = TableRegistry::get('Crstudentevents');

$eventD = $conventionSeasonEvent->Events;
$qualifyingTime = !empty($conventionSeasonEvent->qualifying_time_score) ? date('i:s', strtotime($conventionSeasonEvent->qualifying_time_score)) : 'N/A';
$seasonYearForAge = (isset($convSeasonD->season_year) && is_numeric($convSeasonD->season_year)) ? (int)$convSeasonD->season_year : null;

$uniqueRows = [];
$seenStudents = [];
foreach ($eventSubmissions as $submission) {
    if (!empty($submission->student_id) && isset($seenStudents[$submission->student_id])) {
        continue;
    }
    if (!empty($submission->student_id)) {
        $seenStudents[$submission->student_id] = true;
    }
    $uniqueRows[] = $submission;
}

$entriesCount = count($uniqueRows);

// Split into heats if runnersPerHeat is set, otherwise treat all as one "FINAL" heat
if (!empty($runnersPerHeat) && $runnersPerHeat > 0 && $entriesCount > $runnersPerHeat) {
    $heats = array_chunk($uniqueRows, $runnersPerHeat);
    $isHeated = true;
} else {
    $heats = [$uniqueRows];
    $isHeated = false;
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    color: #000;
    margin: 14px;
}
.print-head {
    border-bottom: 2px solid #000;
    margin-bottom: 10px;
    padding-bottom: 8px;
}
.print-head h2 {
    margin: 0 0 6px;
    font-size: 24px;
}
.meta-row {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    font-size: 14px;
}
.meta-row div {
    margin-bottom: 4px;
}
.running-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}
.running-table th,
.running-table td {
    border: 1px solid #000;
    padding: 6px;
    font-size: 13px;
}
.running-table th {
    text-align: left;
    background: #f3f3f3;
}
.blank-box {
    display: inline-block;
    width: 24px;
    height: 18px;
    border: 1px solid #000;
}
.toolbar {
    margin-bottom: 10px;
}
@media print {
    .toolbar {
        display: none;
    }
    body {
        margin: 0;
    }
}
</style>

<div class="toolbar">
    <button onclick="window.print();" class="btn btn-primary btn-sm">Print</button>
</div>

<?php foreach ($heats as $heatIndex => $heatRows) : ?>
    <?php
    $heatNumber = $heatIndex + 1;
    $totalHeats = count($heats);
    $formatLabel = $isHeated ? "Heat {$heatNumber} of {$totalHeats}" : 'FINAL';
    $heatEntries = count($heatRows);
    ?>

    <?php if ($heatIndex > 0) : ?>
        <div style="page-break-before: always;"></div>
    <?php endif; ?>

    <div class="print-head">
        <h2><?php echo h($eventD->event_name); ?> (<?php echo h($eventD->event_id_number); ?>)</h2>
        <div class="meta-row">
            <div><strong>Qualifying Time:</strong> <?php echo h($qualifyingTime); ?></div>
            <div><strong>No. of Entries:</strong> <?php echo h($entriesCount); ?></div>
            <div><strong>Format:</strong> <?php echo h($formatLabel); ?></div>
        </div>
    </div>

    <table class="running-table">
        <thead>
            <tr>
                <th style="width:70px;">Lane</th>
                <th>Name</th>
                <th style="width:110px;">Age</th>
                <th>School</th>
                <th style="width:100px;">Time</th>
                <th style="width:100px;">Place</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($heatEntries > 0) { ?>
                <?php foreach ($heatRows as $laneIndex => $submission) { ?>
                    <?php
                    $studentName = 'N/A';
                    $ageDisplay = '';
                    $schoolName = 'N/A';

                    if (!empty($submission->student_id) && !empty($submission->Students)) {
                        $studentName = trim(
                            (isset($submission->Students['first_name']) ? $submission->Students['first_name'] : '') . ' ' .
                            (isset($submission->Students['middle_name']) ? $submission->Students['middle_name'] : '') . ' ' .
                            (isset($submission->Students['last_name']) ? $submission->Students['last_name'] : '')
                        );

                        $birthYearRaw = isset($submission->Students['birth_year']) ? $submission->Students['birth_year'] : null;
                        if ($seasonYearForAge !== null && is_numeric($birthYearRaw)) {
                            $calculatedAge = $seasonYearForAge - (int)$birthYearRaw;
                            if ($calculatedAge >= 0) {
                                $ageDisplay = (string)$calculatedAge;
                            }
                        }
                    }

                    if (!empty($submission->Users)) {
                        $schoolName = isset($submission->Users['first_name']) ? $submission->Users['first_name'] : 'N/A';
                    }

                    // For relay/group events, show member first+last names instead of school/team name.
                    if ((int)(isset($eventD->group_event_yes_no) ? $eventD->group_event_yes_no : 0) === 1 && !empty($submission->group_name)) {
                        $groupCond = [
                            'Crstudentevents.conventionregistration_id' => $submission->conventionregistration_id,
                            'Crstudentevents.conventionseason_id' => $submission->conventionseason_id,
                            'Crstudentevents.event_id' => $submission->event_id,
                            'Crstudentevents.group_name' => $submission->group_name
                        ];
                        $groupMembers = $this->Crstudentevents->find()
                            ->where($groupCond)
                            ->contain(['Students'])
                            ->order(['Crstudentevents.id' => 'ASC'])
                            ->all();

                        $memberNames = [];
                        foreach ($groupMembers as $memberRow) {
                            if (!empty($memberRow->Students)) {
                                $memberFullName = trim(
                                    (isset($memberRow->Students['first_name']) ? $memberRow->Students['first_name'] : '') . ' ' .
                                    (isset($memberRow->Students['last_name']) ? $memberRow->Students['last_name'] : '')
                                );
                                if ($memberFullName !== '') {
                                    $memberNames[] = $memberFullName;
                                }
                            }
                        }

                        if (!empty($memberNames)) {
                            $studentName = implode(', ', $memberNames);
                        }
                    }

                    if ($studentName === 'N/A') {
                        if (!empty($submission->group_name)) {
                            if (is_numeric(trim((string)$submission->group_name)) && $schoolName !== 'N/A') {
                                $studentName = $schoolName . ' Team';
                            } else {
                                $studentName = $submission->group_name;
                            }
                        } elseif ($schoolName !== 'N/A') {
                            $studentName = $schoolName . ' Team';
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo (int)$laneIndex + 1; ?></td>
                        <td><?php echo h($studentName); ?></td>
                        <td><?php echo h($ageDisplay); ?></td>
                        <td><?php echo h($schoolName); ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr>
                    <td colspan="6">No entries found for this event.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php endforeach; ?>

<script>
window.onload = function () {
    window.print();
};
</script>
