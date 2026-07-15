<?php
use Cake\ORM\TableRegistry;
$this->Users = TableRegistry::get('Users');

$conventionD = $conventionD ?? null;
$conventionSD = $conventionSD ?? null;
$divisions = $divisions ?? [];
$genderSplitDivs = $genderSplitDivs ?? [];
$trophyWinners = $trophyWinners ?? [];
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Division Winners Plain Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background: #fff;
            margin: 20px;
        }
        h2 {
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 700;
        }
        .subhead {
            margin: 0 0 14px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            background: #fff;
        }
        th {
            font-weight: 700;
        }
        @media print {
            body {
                margin: 0;
            }
            @page {
                margin: 12mm;
            }
        }
    </style>
</head>
<body>
    <h2>Division Winners</h2>
    <p class="subhead"><?php echo h($conventionD->name ?? ''); ?> - <?php echo h($conventionSD->season_year ?? ''); ?></p>

    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>School</th>
                <th>Division</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($divisions as $divisionrecord): ?>
                <?php
                    $divId = (int)$divisionrecord->id;
                    if (empty($trophyWinners[$divId])) {
                        continue;
                    }

                    $winnerStudentIds = [];
                    if (in_array($divId, $genderSplitDivs)) {
                        foreach (['Male', 'Female'] as $category) {
                            if (!empty($trophyWinners[$divId][$category]['students'])) {
                                $winnerStudentIds[] = (int)reset($trophyWinners[$divId][$category]['students']);
                            }
                        }
                    } else {
                        $winnerStudentIds[] = (int)reset($trophyWinners[$divId]['students']);
                    }

                    foreach ($winnerStudentIds as $winnerStudentId):
                        $studentD = $this->Users->find()->where(["Users.id" => $winnerStudentId])->contain(['Schools'])->first();
                        if (!$studentD) {
                            continue;
                        }
                        $studentName = trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name);
                        $schoolName = $studentD->Schools['first_name'] ?? '';
                ?>
            <tr>
                <td><?php echo h($studentName); ?></td>
                <td><?php echo h($schoolName); ?></td>
                <td><?php echo h($divisionrecord->name); ?></td>
            </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        window.print();
    </script>
</body>
</html>
