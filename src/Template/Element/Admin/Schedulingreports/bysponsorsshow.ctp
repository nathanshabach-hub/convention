<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::get('Schedulingtimings');
$schedulingTimingsList = $schedulingTimingsList ?? [];
$sponsorName = isset($sponsorD) ? trim((string)($sponsorD->first_name ?? '') . ' ' . (string)($sponsorD->last_name ?? '')) : '';

// Build student_id → school_name map for this convention season
$studentSchoolMap = [];
$sponsorStudentIds = [];
$sponsorSchoolIds = [];
if (isset($conventionSD)) {
    $db = \Cake\Datasource\ConnectionManager::get('default');
    $rows = $db->execute(
        "SELECT crs.student_id, u.first_name
         FROM conventionregistrationstudents crs
         JOIN users u ON u.id = crs.user_id
         WHERE crs.convention_id = ? AND crs.season_year = ? AND crs.status = 1 AND crs.student_id > 0",
        [$conventionSD->convention_id, $conventionSD->season_year]
    )->fetchAll('assoc');
    foreach ($rows as $row) {
        $studentSchoolMap[(int)$row['student_id']] = trim((string)$row['first_name']);
    }

    // Build set of this sponsor's student IDs and their school IDs
    if (isset($sponsor_id)) {
        $sRows = $db->execute(
            "SELECT student_id, user_id FROM conventionregistrationstudents
             WHERE teacher_parent_id = ? AND convention_id = ? AND season_year = ? AND status = 1 AND student_id > 0",
            [(int)$sponsor_id, $conventionSD->convention_id, $conventionSD->season_year]
        )->fetchAll('assoc');
        foreach ($sRows as $sr) {
            $sponsorStudentIds[(int)$sr['student_id']] = true;
            $sponsorSchoolIds[(int)$sr['user_id']] = true;
        }
    }
}
?>
<?php if (!empty($schedulingTimingsList)) { ?>
    <div class="panel-body">
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left" style="display:flex; justify-content:space-between; align-items:baseline; width:95%;">
                    <h3 style="margin:0;"><?php echo h($sponsorName); ?></h3>
                    <?php
                        if (isset($conventionSD) && isset($sponsor_id)) {
                            $sponsorSchoolRow = $db->execute(
                                "SELECT u.first_name FROM conventionregistrationstudents crs
                                 JOIN users u ON u.id = crs.user_id
                                 WHERE crs.teacher_parent_id = ? AND crs.convention_id = ? AND crs.season_year = ? AND crs.status = 1 AND crs.student_id > 0
                                 LIMIT 1",
                                [(int)$sponsor_id, $conventionSD->convention_id, $conventionSD->season_year]
                            )->fetch('assoc');
                            if (!empty($sponsorSchoolRow['first_name'])) {
                                echo '<span style="font-size:1.2em; color:#555;">' . h($sponsorSchoolRow['first_name']) . '</span>';
                            }
                        }
                    ?>
                </div>
            </div>

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Start</th>
                    <th>Event</th>
                    <th>Location</th>
                    <th>School</th>
                    <th>Match</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Collect all records into array first so we can append projected path rows
            $arrSponsorSchedule = [];
            $projectedCollected = [];
            foreach ($schedulingTimingsList as $datarecord) {
                $arr = [];
                $arr['sch_date_time']       = $datarecord->sch_date_time;
                $arr['day']                 = $datarecord->day;
                $arr['start_time']          = !empty($datarecord->start_time) ? date('h:i A', strtotime((string)$datarecord->start_time)) : '';
                $eventIdNumber = trim((string)($datarecord->event_id_number ?? ($datarecord->Events['event_id_number'] ?? '')));
                $arr['event_name']          = h($datarecord->Events['event_name'] ?? '') . ($eventIdNumber !== '' ? ' (' . h($eventIdNumber) . ')' : '');
                $arr['room_name']           = $datarecord->Conventionrooms['room_name'] ?? '';
                $arr['db_id']               = $datarecord->id;
                $arr['is_bye']              = $datarecord->is_bye;
                $arr['schedule_category']   = (int)$datarecord->schedule_category;
                $arr['round_number']        = (int)$datarecord->round_number;
                $arr['match_number']        = $datarecord->match_number;
                $arr['group_name']          = $datarecord->group_name ?? '';
                $arr['group_name_opponent'] = $datarecord->group_name_opponent ?? '';
                $arr['user_id']             = (int)$datarecord->user_id;
                $arr['user_id_opponent']    = (int)$datarecord->user_id_opponent;
                $arr['schtimeautoid1']      = $datarecord->schtimeautoid1;
                $arr['schtimeautoid2']      = $datarecord->schtimeautoid2;
                $arr['Users']               = $datarecord->Users;
                $arr['Opponentuser']        = $datarecord->Opponentuser;
                $arr['is_projected']        = false;
                $arrSponsorSchedule[] = $arr;
            }

            // Append projected path rows for elimination events (category 2 or 3)
            foreach ($arrSponsorSchedule as $studentsch) {
                if (!empty($studentsch['is_bye']) || !in_array($studentsch['schedule_category'], [2, 3])) continue;
                $nextBaseId = (int)$studentsch['db_id'];
                for ($projStep = 1; $projStep <= 2; $projStep++) {
                    $nextMatch = $this->Schedulingtimings->find()
                        ->where(["(Schedulingtimings.schtimeautoid1 = '".$nextBaseId."' OR Schedulingtimings.schtimeautoid2 = '".$nextBaseId."')"])
                        ->contain(['Events','Conventionrooms'])
                        ->order(['Schedulingtimings.round_number' => 'ASC', 'Schedulingtimings.match_number' => 'ASC', 'Schedulingtimings.id' => 'ASC'])
                        ->first();
                    if (!$nextMatch) break;
                    if (isset($projectedCollected[(int)$nextMatch->id])) { $nextBaseId = (int)$nextMatch->id; continue; }
                    $projectedCollected[(int)$nextMatch->id] = 1;
                    $eIdNum = trim((string)($nextMatch->event_id_number ?? ($nextMatch->Events['event_id_number'] ?? '')));
                    $arr = [];
                    $arr['sch_date_time']       = $nextMatch->sch_date_time;
                    $arr['day']                 = !empty($nextMatch->day) ? $nextMatch->day : 'TBD';
                    $arr['start_time']          = $nextMatch->start_time != null ? date('h:i A', strtotime((string)$nextMatch->start_time)) : 'TBD';
                    $arr['event_name']          = h($nextMatch->Events['event_name'] ?? '') . ($eIdNum !== '' ? ' (' . h($eIdNum) . ')' : '');
                    $arr['room_name']           = !empty($nextMatch->Conventionrooms['room_name']) ? $nextMatch->Conventionrooms['room_name'] : 'TBD';
                    $arr['db_id']               = $nextMatch->id;
                    $arr['is_bye']              = 0;
                    $arr['schedule_category']   = (int)$nextMatch->schedule_category;
                    $arr['round_number']        = (int)$nextMatch->round_number;
                    $arr['match_number']        = $nextMatch->match_number;
                    $arr['group_name']          = '';
                    $arr['group_name_opponent'] = '';
                    $arr['user_id']             = 0;
                    $arr['user_id_opponent']    = 0;
                    $arr['schtimeautoid1']      = $nextMatch->schtimeautoid1;
                    $arr['schtimeautoid2']      = $nextMatch->schtimeautoid2;
                    $arr['Users']               = null;
                    $arr['Opponentuser']        = null;
                    $arr['proj_label']          = ($projStep === 1 ? 'If Win' : 'If Win Again') . ': Match-' . $nextMatch->match_number;
                    $arr['is_projected']        = true;
                    $arrSponsorSchedule[] = $arr;
                    $nextBaseId = (int)$nextMatch->id;
                }
            }

            // Sort chronologically
            usort($arrSponsorSchedule, function ($a, $b) { return $a['sch_date_time'] <=> $b['sch_date_time']; });

            foreach ($arrSponsorSchedule as $datarecord) {
                if ($datarecord['is_bye'] == 1) continue;
                $rowStyle = !empty($datarecord['is_projected']) ? ' style="background:#ffffcc;"' : '';
                $schoolName = trim((string)($datarecord['Users']['first_name'] ?? ''));
                if ($schoolName === '') { $schoolName = trim((string)($datarecord['Opponentuser']['first_name'] ?? '')); }
            ?>
                <tr<?php echo $rowStyle; ?>>
                    <td><?php echo h($datarecord['day']); ?></td>
                    <td><?php echo h($datarecord['start_time']); ?></td>
                    <td>
                        <?php echo $datarecord['event_name']; ?>
                        <?php if (!empty($datarecord['is_projected'])): ?>
                            <br><span style="color:#b9770e; font-size:12px;">Projected path</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo h($datarecord['room_name']); ?></td>
                    <td><?php
                        $uid = (int)($datarecord['user_id'] ?? 0);
                        $oppId = (int)($datarecord['user_id_opponent'] ?? 0);
                        $cat = (int)($datarecord['schedule_category'] ?? 0);
                        if ($cat === 2 || $cat === 4) {
                            // For individual events, pick the sponsor's student side
                            if (!empty($sponsorStudentIds[$oppId]) && !empty($studentSchoolMap[$oppId])) {
                                echo h($studentSchoolMap[$oppId]);
                            } elseif (!empty($studentSchoolMap[$uid])) {
                                echo h($studentSchoolMap[$uid]);
                            } else {
                                echo h($schoolName);
                            }
                        } elseif ($cat === 3) {
                            // For team events, show the sponsor's school side
                            if (!empty($sponsorSchoolIds[$oppId])) {
                                echo h(trim((string)($datarecord['Opponentuser']['first_name'] ?? $schoolName)));
                            } else {
                                echo h($schoolName);
                            }
                        } else {
                            echo h($schoolName);
                        }
                    ?></td>
                    <td>
                        <?php
                            if (!empty($datarecord['is_projected'])) {
                                echo $datarecord['proj_label'];
                            } elseif ($datarecord['schedule_category'] === 1) {
                                $groupName = trim((string)($datarecord['group_name'] ?? ''));
                                if ($groupName === '') { $groupName = trim((string)($datarecord['group_name_opponent'] ?? '')); }
                                if ($groupName !== '') {
                                    echo 'Group ' . h($groupName);
                                    if ($schoolName !== '') { echo ' (<b>' . h($schoolName) . '</b>)'; }
                                }
                            } elseif ($datarecord['schedule_category'] === 2) {
                                if (!empty($datarecord['match_number'])) { echo 'Match-' . h($datarecord['match_number']) . ':&nbsp;'; }
                                if ($datarecord['round_number'] > 1) {
                                    if (!empty($datarecord['schtimeautoid1']) && !empty($datarecord['schtimeautoid2'])) {
                                        $matchOneD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $datarecord['schtimeautoid1']])->first();
                                        $matchTwoD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $datarecord['schtimeautoid2']])->first();
                                        if ($matchOneD) { echo '(Winner of Match-' . h($matchOneD->match_number) . ')'; }
                                        if ($matchOneD && $matchTwoD) { echo ' <b>VS</b> '; }
                                        if ($matchTwoD) { echo '(Winner of Match-' . h($matchTwoD->match_number) . ')'; }
                                    }
                                } else {
                                    $leftName = trim((string)(($datarecord['Users']['first_name'] ?? '') . ' ' . ($datarecord['Users']['middle_name'] ?? '') . ' ' . ($datarecord['Users']['last_name'] ?? '')));
                                    $rightName = trim((string)(($datarecord['Opponentuser']['first_name'] ?? '') . ' ' . ($datarecord['Opponentuser']['middle_name'] ?? '') . ' ' . ($datarecord['Opponentuser']['last_name'] ?? '')));
                                    if ($datarecord['user_id'] > 0 && $datarecord['user_id_opponent'] === 0) {
                                        echo h($leftName) . ' (<b>BYE</b>)';
                                    } else {
                                        echo h($leftName);
                                        if ($rightName !== '') { echo ' <b>VS</b> ' . h($rightName); }
                                    }
                                }
                            } elseif ($datarecord['schedule_category'] === 3) {
                                if (!empty($datarecord['match_number'])) { echo 'Match-' . h($datarecord['match_number']) . ':&nbsp;'; }
                                if ($datarecord['round_number'] > 1) {
                                    if (!empty($datarecord['schtimeautoid1']) && !empty($datarecord['schtimeautoid2'])) {
                                        $matchOneD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $datarecord['schtimeautoid1']])->first();
                                        $matchTwoD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $datarecord['schtimeautoid2']])->first();
                                        if ($matchOneD) { echo '(Winner of Match-' . h($matchOneD->match_number) . ')'; }
                                        if ($matchOneD && $matchTwoD) { echo ' <b>VS</b> '; }
                                        if ($matchTwoD) { echo '(Winner of Match-' . h($matchTwoD->match_number) . ')'; }
                                    }
                                } else {
                                    $leftSchool = trim((string)($datarecord['Users']['first_name'] ?? ''));
                                    $leftGroup  = trim((string)($datarecord['group_name'] ?? ''));
                                    $rightSchool = trim((string)($datarecord['Opponentuser']['first_name'] ?? ''));
                                    $rightGroup = trim((string)($datarecord['group_name_opponent'] ?? ''));
                                    if ($datarecord['user_id'] > 0 && $datarecord['user_id_opponent'] === 0) {
                                        echo h($leftSchool);
                                        if ($leftGroup !== '') { echo ' (Group-' . h($leftGroup) . ')'; }
                                        echo '(<b>BYE</b>)';
                                    } else {
                                        echo h($leftSchool);
                                        if ($leftGroup !== '') { echo ' (Group-' . h($leftGroup) . ')'; }
                                        if ($rightSchool !== '') {
                                            echo ' <b>VS</b> ' . h($rightSchool);
                                            if ($rightGroup !== '') { echo '(Group-' . h($rightGroup) . ')'; }
                                        }
                                    }
                                }
                            } else {
                                $fallbackName = trim((string)(($datarecord['Users']['first_name'] ?? '') . ' ' . ($datarecord['Users']['middle_name'] ?? '') . ' ' . ($datarecord['Users']['last_name'] ?? '')));
                                echo h($fallbackName);
                            }
                        ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
            </div><!-- tbl-resp-listing -->
        </section>
    </div><!-- panel-body -->
<?php } else { ?>
    <div class="admin_no_record">No record found.</div>
<?php } ?>
