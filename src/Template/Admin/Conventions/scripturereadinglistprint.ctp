<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Scripture Reading List Print</title>
    <style>
        :root {
            --ink: #111;
            --muted: #5a5a5a;
            --rule: #cfcfcf;
            --rule-strong: #8b8b8b;
            --accent: #1e8c3f;
            --accent-soft: #eaf5ee;
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: "Helvetica Neue", Arial, sans-serif;
            color: var(--ink);
            background: #fff;
            line-height: 1.3;
        }
        .page {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            padding: 14px 16px 16px 16px;
            box-sizing: border-box;
        }
        h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            font-family: Georgia, serif;
            font-weight: 600;
            letter-spacing: 0.2px;
        }
        .meta {
            margin-bottom: 6px;
            border-bottom: 1px solid var(--rule-strong);
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .meta .title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: #1f1f1f;
        }
        .meta .place {
            color: var(--accent);
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.4px;
            background: var(--accent-soft);
            border: 1px solid #b7d8c2;
            border-radius: 999px;
            padding: 2px 12px;
        }
        .meta .place.place-first {
            color: #1f4fae;
            background: #eaf1ff;
            border-color: #b8ccf5;
        }
        .meta .place.place-second {
            color: #b32727;
            background: #ffecec;
            border-color: #f2bbbb;
        }
        .school {
            margin-top: 8px;
            margin-bottom: 4px;
            font-size: 15px;
            font-weight: 700;
            border-bottom: 1px solid var(--rule);
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        th {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--muted);
            font-weight: 700;
            text-align: left;
            padding: 2px 3px 3px 3px;
            border-bottom: 1px solid var(--rule-strong);
        }
        td {
            font-size: 12px;
            padding: 3px 3px;
            vertical-align: top;
        }
        tbody tr + tr td {
            border-top: 1px solid #ececec;
        }
        td.student {
            width: 52%;
            font-weight: 500;
        }
        td.book {
            width: 48%;
            font-weight: 600;
            text-align: left;
        }
        .footer {
            margin-top: 14px;
            border-top: 1px solid var(--rule);
            padding-top: 6px;
            text-align: right;
            font-size: 11px;
            color: var(--muted);
        }
        @media print {
            .page {
                max-width: none;
                padding: 0;
                break-after: page;
                page-break-after: always;
            }
            .page:last-child {
                break-after: auto;
                page-break-after: auto;
            }
            .footer {
                display: none;
            }
            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<?php
$formatPlaceHeading = function ($placeLabel) {
    if (preg_match('/Place\s*(\d+)\s*\(([^\)]+)\)/i', (string)$placeLabel, $m)) {
        $place = (int)$m[1];
        $division = strtoupper(trim($m[2]));
        $suffix = 'th';
        if ($place % 100 < 11 || $place % 100 > 13) {
            if ($place % 10 === 1) {
                $suffix = 'st';
            } elseif ($place % 10 === 2) {
                $suffix = 'nd';
            } elseif ($place % 10 === 3) {
                $suffix = 'rd';
            }
        }

        return $division . $place . $suffix;
    }

    if (preg_match('/^\s*([A-Za-z0-9-]+)\s*(\d+)(st|nd|rd|th)\s*$/i', (string)$placeLabel, $m)) {
        return strtoupper(trim($m[1])) . ((int)$m[2]) . strtolower($m[3]);
    }

    return (string)$placeLabel;
};

$placeSortWeight = function ($placeLabel) {
    if (preg_match('/Place\s*(\d+)\s*\(([^\)]+)\)/i', (string)$placeLabel, $m)) {
        $place = (int)$m[1];
        $division = strtoupper(trim($m[2]));
        $divisionWeight = 99;
        if ($division === 'U16') {
            $divisionWeight = 0;
        } elseif ($division === 'OPEN') {
            $divisionWeight = 1;
        } elseif ($division === 'NON-COMPETITOR') {
            $divisionWeight = 2;
        }

        return [$divisionWeight, -$place, $division];
    }

    if (preg_match('/^\s*([A-Za-z0-9-]+)\s*(\d+)(st|nd|rd|th)\s*$/i', (string)$placeLabel, $m)) {
        $division = strtoupper(trim($m[1]));
        $place = (int)$m[2];
        $divisionWeight = 99;
        if ($division === 'U16') {
            $divisionWeight = 0;
        } elseif ($division === 'OPEN') {
            $divisionWeight = 1;
        } elseif ($division === 'NON-COMPETITOR') {
            $divisionWeight = 2;
        }

        return [$divisionWeight, -$place, $division];
    }

    if (preg_match('/^\s*(\d+)(st|nd|rd|th)\s*$/i', (string)$placeLabel, $m)) {
        return [0, -((int)$m[1]), 'U16'];
    }

    return [999, 0, strtoupper((string)$placeLabel)];
};

$ROWS_FIRST_PAGE  = 40; // student rows fitting on first page of a place group
$ROWS_CONT_PAGE   = 41; // student rows fitting on continuation pages
$SCHOOL_OVERHEAD  = 1;  // "rows" consumed by each school name header (except first on page)

$printPages = [];
if (!empty($groupedReadingList)) {
    $placeLabels = array_keys($groupedReadingList);
    usort($placeLabels, function ($a, $b) use ($placeSortWeight) {
        $wa = $placeSortWeight($a);
        $wb = $placeSortWeight($b);
        if ($wa[0] !== $wb[0]) {
            return $wa[0] <=> $wb[0];
        }
        if ($wa[1] !== $wb[1]) {
            return $wa[1] <=> $wb[1];
        }

        return strcmp($wa[2], $wb[2]);
    });

    foreach ($placeLabels as $placeLabel) {
        $schools = $groupedReadingList[$placeLabel];
        ksort($schools, SORT_NATURAL | SORT_FLAG_CASE);

        $placeHeading = $formatPlaceHeading($placeLabel);
        $validSchools = [];
        foreach ($schools as $schoolName => $rows) {
            $printableRows = [];
            foreach ((array)$rows as $row) {
                $studentName = isset($row['student_name']) ? trim((string)$row['student_name']) : '';
                $bookNames = isset($row['book_names']) ? trim((string)$row['book_names']) : '';
                if ($studentName === '' && $bookNames === '') {
                    continue;
                }
                $printableRows[] = $row;
            }
            if (!empty($printableRows)) {
                $validSchools[] = ['school_name' => $schoolName, 'rows' => $printableRows];
            }
        }

        if (empty($validSchools)) { continue; }

        // Pre-paginate: split schools/rows into explicit page-sized chunks
        $placePageChunks = []; // each entry = one printed page for this place
        $curPageSchools  = []; // schools on the current page
        $curPageRowCount = 0;
        $isFirstPlacePage = true;
        $rowLimit = $ROWS_FIRST_PAGE;

        foreach ($validSchools as $school) {
            $overhead = empty($curPageSchools) ? 0 : $SCHOOL_OVERHEAD;
            $schoolRows = $school['rows'];
            $pendingRows = [];

            foreach ($schoolRows as $row) {
                // Would adding this row (plus any school overhead) exceed the limit?
                if ($curPageRowCount + $overhead + 1 > $rowLimit && $curPageRowCount > 0) {
                    // Flush pending rows for this school so far
                    if (!empty($pendingRows)) {
                        $curPageSchools[] = ['school_name' => $school['school_name'], 'rows' => $pendingRows];
                        $pendingRows = [];
                    }
                    // Save current page
                    $placePageChunks[] = $curPageSchools;
                    $curPageSchools   = [];
                    $curPageRowCount  = 0;
                    $isFirstPlacePage = false;
                    $rowLimit         = $ROWS_CONT_PAGE;
                    $overhead         = 0;
                }
                $curPageRowCount += $overhead + 1;
                $overhead = 0; // overhead only counted once per school per page
                $pendingRows[] = $row;
            }

            if (!empty($pendingRows)) {
                $curPageSchools[] = ['school_name' => $school['school_name'], 'rows' => $pendingRows];
                $pendingRows = [];
            }
        }

        if (!empty($curPageSchools)) {
            $placePageChunks[] = $curPageSchools;
        }

        $totalPlacePages = count($placePageChunks);
        foreach ($placePageChunks as $pageIdx => $pageSchools) {
            $printPages[] = [
                'place_label'        => $placeLabel,
                'place_heading'      => $placeHeading,
                'place_page_index'   => $pageIdx + 1,
                'place_page_total'   => $totalPlacePages,
                'schools'            => $pageSchools,
            ];
        }
    }
}

$formatStudentName = function ($name) {
    $name = trim((string)$name);
    if ($name === '') {
        return '';
    }

    $parts = preg_split('/\s+/', $name);
    if (empty($parts)) {
        return $name;
    }
    if (count($parts) === 1) {
        return $parts[0];
    }

    return $parts[0] . ' ' . $parts[count($parts) - 1];
};

$totalPages = count($printPages);
$pageNum = 1;
?>

<?php if (!empty($printPages)) { ?>
    <?php foreach ($printPages as $page) { ?>
        <?php
        $placeClass = 'place-other';
        if (preg_match('/(^|\b)1st(\b|$)/i', $page['place_heading'])) {
            $placeClass = 'place-first';
        } elseif (preg_match('/(^|\b)2nd(\b|$)/i', $page['place_heading'])) {
            $placeClass = 'place-second';
        }
        $ph = $page['place_heading'];
        $totalPlacePages = $page['place_page_total'];
        $placeDisplay = $totalPlacePages > 1
            ? $ph . ' (' . $page['place_page_index'] . ' of ' . $totalPlacePages . ')'
            : $ph;
        ?>
        <div class="page">
            <?php foreach ($page['schools'] as $schoolIdx => $schoolSection) { ?>
                <?php
                $isFirstSchool = $schoolIdx === 0;
                ?>
                <table style="width:100%; border-collapse:collapse; margin-bottom: 18px;">
                    <thead>
                        <tr>
                            <td colspan="2" style="padding:0 0 4px 0; border-bottom: 1px solid var(--rule-strong);">
                                <?php if ($isFirstSchool): ?>
                                <h1 style="margin:0 0 2px 0; font-size:19px; font-family:Georgia,serif; font-weight:600;"><?php echo h($conventionD->name.' '.$conventionSD->season_year); ?></h1>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:12px; font-weight:700; letter-spacing:0.6px; text-transform:uppercase; color:#1f1f1f;">Silver Apple Reader's List</span>
                                    <span class="place <?php echo h($placeClass); ?>"><?php echo h($placeDisplay); ?></span>
                                </div>
                                <?php endif; ?>
                                <div style="font-size:15px; font-weight:700; margin-top:<?php echo $isFirstSchool ? '6' : '0'; ?>px; padding-bottom:3px; border-bottom:1px solid var(--rule);"><?php echo h($schoolSection['school_name']); ?></div>
                            </td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--rule-strong);">
                            <th style="font-size:10px; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); font-weight:700; text-align:left; padding:3px 3px; width:52%;">Student</th>
                            <th style="font-size:10px; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); font-weight:700; text-align:left; padding:3px 3px; width:48%;">Scripture Book</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($schoolSection['rows'] as $row) { ?>
                        <tr style="break-inside:avoid; page-break-inside:avoid;">
                            <td style="font-size:12px; padding:3px 3px; vertical-align:top; font-weight:500; border-top:1px solid #ececec;"><?php echo h($formatStudentName($row['student_name'])); ?></td>
                            <td style="font-size:12px; padding:3px 3px; vertical-align:top; font-weight:600; border-top:1px solid #ececec;"><?php echo h($row['book_names']); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
            <div class="footer">Page <?php echo $pageNum; ?> of <?php echo $totalPages; ?></div>
        </div>
        <?php $pageNum++; ?>
    <?php } ?>
<?php } else { ?>
    <div class="page">
        <h1><?php echo h($conventionD->name.' '.$conventionSD->season_year); ?></h1>
        <div class="meta">
            <span class="title">Silver Apple Reader's List</span>
        </div>
        <p>No scripture reading submissions found for this season.</p>
    </div>
<?php } ?>

<script>
window.print();
</script>
</body>
</html>
