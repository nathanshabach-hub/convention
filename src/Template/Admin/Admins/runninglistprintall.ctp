<?php
use Cake\ORM\TableRegistry;

$this->Crstudentevents = TableRegistry::get('Crstudentevents');

$raceGroupsArray = isset($raceGroups) && is_array($raceGroups) ? $raceGroups : [];
$totalEvents = count($raceGroupsArray);
$heatMap = isset($heatMap) && is_array($heatMap) ? $heatMap : [];
$submissionsByEvent = isset($submissionsByEvent) && is_array($submissionsByEvent) ? $submissionsByEvent : [];
$printedBlockCount = 0;
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
.event-block {
    margin-bottom: 18px;
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
    <button onclick="window.print();" class="btn btn-primary btn-sm">Print All</button>
</div>

<?php if ($totalEvents > 0) { ?>
    <?php foreach ($raceGroupsArray as $groupIndex => $raceGroup) { ?>
        <?php
        $groupEvents = isset($raceGroup['events']) && is_array($raceGroup['events']) ? $raceGroup['events'] : [];
        if (empty($groupEvents)) {
            continue;
        }

        $isCombinedRace = count($groupEvents) > 1;

        $firstEventRecord = $groupEvents[0];
        $firstEventD = $firstEventRecord->Events;

        $eventTitleParts = [];
        $eventCodeParts = [];
        $eventsByEventId = [];
        $groupRows = [];
        $groupHeatSizeCandidates = [];
        $groupQualifyingRaw = null;

        foreach ($groupEvents as $groupEventRecord) {
            $groupEventD = $groupEventRecord->Events;
            $eventTitleParts[] = $groupEventD->event_name;
            $eventCodeParts[] = $groupEventD->event_id_number;
            $eventsByEventId[(int)$groupEventRecord->event_id] = $groupEventD;

            $groupEventRows = isset($submissionsByEvent[(int)$groupEventRecord->event_id]) ? $submissionsByEvent[(int)$groupEventRecord->event_id] : [];
            $groupRows = array_merge($groupRows, $groupEventRows);

            $groupEventId = (int)$groupEventRecord->id;
            if (isset($heatMap[$groupEventId]) && (int)$heatMap[$groupEventId] > 0) {
                $eventHeatSize = (int)$heatMap[$groupEventId];
                $eventEntryCount = count($groupEventRows);

                // In combined mode, the row default is usually "entries count" for each individual event.
                // Ignore that default so combined races don't get split into 1-runner heats unless explicitly set.
                if (!$isCombinedRace || $eventHeatSize !== $eventEntryCount) {
                    $groupHeatSizeCandidates[] = $eventHeatSize;
                }
            }

            if (empty($groupQualifyingRaw) && !empty($groupEventRecord->qualifying_time_score)) {
                $groupQualifyingRaw = $groupEventRecord->qualifying_time_score;
            }
        }

        $displayEventName = $isCombinedRace
            ? 'Combined: ' . implode(' + ', $eventTitleParts)
            : $firstEventD->event_name;
        $displayEventCodes = implode(', ', $eventCodeParts);
        $qualifyingTime = !empty($groupQualifyingRaw) ? date('i:s', strtotime($groupQualifyingRaw)) : 'N/A';

        $uniqueRows = [];
        $seenStudents = [];
        foreach ($groupRows as $submission) {
            if (!empty($submission->student_id) && isset($seenStudents[$submission->student_id])) {
                continue;
            }
            if (!empty($submission->student_id)) {
                $seenStudents[$submission->student_id] = true;
            }
            $uniqueRows[] = $submission;
        }

        $entriesCount = count($uniqueRows);

        // Print All should skip events with no entries.
        if ($entriesCount <= 0) {
            continue;
        }

        if (!empty($groupHeatSizeCandidates)) {
            $eventHeatSize = max($groupHeatSizeCandidates);
        } elseif ($isCombinedRace) {
            // Default combined groups to a single heat/page unless explicitly split.
            $eventHeatSize = $entriesCount;
        } else {
            $eventHeatSize = (int)(isset($runnersPerHeat) ? $runnersPerHeat : 6);
        }

        if (!empty($eventHeatSize) && $eventHeatSize > 0 && $entriesCount > $eventHeatSize) {
            $heats = array_chunk($uniqueRows, $eventHeatSize);
            $isHeated = true;
        } else {
            $heats = [$uniqueRows];
            $isHeated = false;
        }
        $totalHeats = count($heats);
        ?>

        <?php foreach ($heats as $heatIndex => $heatRows) : ?>
        <?php
        $heatNumber = $heatIndex + 1;
        $formatLabel = $isHeated ? "Heat {$heatNumber} of {$totalHeats}" : 'FINAL';
        $heatEntries = count($heatRows);
        $needsPageBreak = ($printedBlockCount > 0);
        ?>

        <div class="event-block"<?php echo $needsPageBreak ? ' style="page-break-before: always;"' : ''; ?>>
            <div class="print-head">
                <h2><?php echo h($displayEventName); ?> (<?php echo h($displayEventCodes); ?>)</h2>
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
                        <th style="width:110px;">Year of Birth</th>
                        <th>School</th>
                        <th style="width:100px;">Time</th>
                        <th style="width:100px;">Place</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($heatEntries > 0) { ?>
                        <?php foreach ($heatRows as $submission) { ?>
                            <?php
                            $studentName = 'N/A';
                            $birthYear = '';
                            $schoolName = 'N/A';

                            if (!empty($submission->student_id) && !empty($submission->Students)) {
                                $studentName = trim(
                                    (isset($submission->Students['first_name']) ? $submission->Students['first_name'] : '') . ' ' .
                                    (isset($submission->Students['middle_name']) ? $submission->Students['middle_name'] : '') . ' ' .
                                    (isset($submission->Students['last_name']) ? $submission->Students['last_name'] : '')
                                );
                                $birthYear = isset($submission->Students['birth_year']) ? (string)$submission->Students['birth_year'] : '';
                            }

                            if (!empty($submission->Users)) {
                                $schoolName = isset($submission->Users['first_name']) ? $submission->Users['first_name'] : 'N/A';
                            }

                            $eventMeta = isset($eventsByEventId[(int)$submission->event_id]) ? $eventsByEventId[(int)$submission->event_id] : null;
                            $isGroupEvent = !empty($eventMeta) && (int)(isset($eventMeta->group_event_yes_no) ? $eventMeta->group_event_yes_no : 0) === 1;

                            // For relay/group events, show member first+last names instead of school/team name.
                            if ($isGroupEvent && !empty($submission->group_name)) {
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
                                <td><span class="blank-box"></span></td>
                                <td><?php echo h($studentName); ?></td>
                                <td><?php echo h($birthYear); ?></td>
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
        </div>
        <?php $printedBlockCount++; ?>
        <?php endforeach; // end heats loop ?>
    <?php } // end race groups loop ?>
<?php } else { ?>
    <p>No running events found for this season.</p>
<?php } ?>

<script>
window.onload = function () {
    window.print();
};
</script>
