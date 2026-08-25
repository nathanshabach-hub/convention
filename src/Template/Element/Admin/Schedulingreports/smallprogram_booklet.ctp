<?php
$hasRows = isset($programDays) && count($programDays) > 0;
$programTitle = trim((string)($conventionSD->Conventions['name'] ?? 'Student Convention'));
$programSeason = trim((string)($conventionSD->season_year ?? ''));
$programVenue = trim((string)($conventionSD->Conventions['address'] ?? ''));
$smallProgramNotes = isset($smallProgramNotes) && is_array($smallProgramNotes) ? $smallProgramNotes : array();
$introEntriesRaw = trim((string)($smallProgramNotes['intro_entries'] ?? ''));
$introEntries = array();
if ($introEntriesRaw !== '') {
    foreach (preg_split('/\r\n|\r|\n/', $introEntriesRaw) as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }
        $parts = explode('|', $line, 2);
        $introEntries[] = array(
            'time' => trim((string)($parts[0] ?? '')),
            'text' => trim((string)($parts[1] ?? $parts[0] ?? '')),
        );
    }
}
$dinnerBanner = trim((string)($smallProgramNotes['dinner_banner'] ?? ''));
$eveningRallyEntriesRaw = trim((string)($smallProgramNotes['evening_rally_entries'] ?? ($smallProgramNotes['awards_entries'] ?? '')));
$eveningRallyEntries = array();
if ($eveningRallyEntriesRaw !== '') {
    foreach (preg_split('/\r\n|\r|\n/', $eveningRallyEntriesRaw) as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }
        $parts = explode('|', $line, 4);
        if (count($parts) >= 4) {
            $entryDay = trim((string)($parts[0] ?? ''));
            $entryStart = trim((string)($parts[1] ?? ''));
            $entryEnd = trim((string)($parts[2] ?? ''));
            $entryText = trim((string)($parts[3] ?? ''));
        } else {
            $entryDay = '';
            $entryStart = trim((string)($parts[0] ?? ''));
            $entryEnd = trim((string)($parts[1] ?? ''));
            $entryText = trim((string)($parts[2] ?? ''));
        }
        $eveningRallyEntries[] = array(
            'day' => $entryDay,
            'start' => $entryStart,
            'end' => $entryEnd,
            'text' => $entryText,
        );
    }
}
$awardsEntriesRaw = trim((string)($smallProgramNotes['awards_entries'] ?? ''));
$awardsEntries = array();
if ($awardsEntriesRaw !== '') {
    foreach (preg_split('/\r\n|\r|\n/', $awardsEntriesRaw) as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }
        $parts = explode('|', $line, 3);
        $awardsEntries[] = array(
            'start' => trim((string)($parts[0] ?? '')),
            'end' => trim((string)($parts[1] ?? '')),
            'text' => trim((string)($parts[2] ?? '')),
        );
    }
}
$awardsCeremonyTime = trim((string)($smallProgramNotes['awards_ceremony_time'] ?? ''));
$eveningRallyTime = trim((string)($smallProgramNotes['evening_rally_time'] ?? ''));
$eveningRallyLabel = trim((string)($smallProgramNotes['evening_rally_label'] ?? ''));
$offsiteNote = trim((string)($smallProgramNotes['offsite_note'] ?? ''));
$footerNote = trim((string)($smallProgramNotes['footer_note'] ?? ''));

// Athletics day
$athleticsDayLabel      = trim((string)($smallProgramNotes['athletics_day_label'] ?? ''));
$athleticsArriveTime    = trim((string)($smallProgramNotes['athletics_arrive_time'] ?? ''));
$athleticsArriveVenue   = trim((string)($smallProgramNotes['athletics_arrive_venue'] ?? ''));
$athleticsBeginTime     = trim((string)($smallProgramNotes['athletics_begin_time'] ?? ''));
$athleticsBeginLabel    = trim((string)($smallProgramNotes['athletics_begin_label'] ?? ''));
$athleticsOrderRaw      = trim((string)($smallProgramNotes['athletics_order_of_events'] ?? ''));
$athleticsImportantRaw  = trim((string)($smallProgramNotes['athletics_important_items'] ?? ''));
$athleticsOffsiteNote   = trim((string)($smallProgramNotes['athletics_offsite_note'] ?? ''));
$athleticsBanner        = trim((string)($smallProgramNotes['athletics_banner'] ?? ''));
// Derive the weekday name from athletics_day_label for matching (e.g. "TUESDAY 8TH JULY" → "tuesday")
$athleticsDayName = '';
if ($athleticsDayLabel !== '') {
    $parts = preg_split('/\s+/', strtolower($athleticsDayLabel), 2);
    $athleticsDayName = trim($parts[0] ?? '');
}
$logoPath = WWW_ROOT . 'img/front/accelerate-logo.jpg';
$logoSrc = '';
if (is_file($logoPath)) {
    $logoData = @file_get_contents($logoPath);
    if ($logoData !== false) {
        $logoSrc = 'data:image/jpeg;base64,'.base64_encode($logoData);
    }
}

$lunchBanner = '';
if (!empty($schedulingD->lunch_time_start) && !empty($schedulingD->lunch_time_end)) {
    $lunchBanner = 'LUNCH '.date('g:i a', strtotime((string)$schedulingD->lunch_time_start)).' - '.date('g:i a', strtotime((string)$schedulingD->lunch_time_end));
}

$smallProgramEditable = isset($smallProgramEditable) ? (bool)$smallProgramEditable : false;

/**
 * Helper function to consolidate Male & Female event pairs while preserving
 * distinct age/category variants such as U16 and OPEN.
 * Examples:
 * - ["Event (Male) U16", "Event (Female) U16"] → ["Event M & F U16"]
 * - ["Event (Male) OPEN", "Event (Female) OPEN"] → ["Event M & F OPEN"]
 * - U16 and OPEN entries remain distinct and are not merged together.
 */
function consolidateEventNames($eventNames) {
    if (empty($eventNames)) {
        return array();
    }

    $eventNames = array_values(array_filter(array_map(function($eventName) {
        return trim((string)$eventName);
    }, $eventNames), function($eventName) {
        return $eventName !== '';
    }));

    $byKey = array();
    foreach ($eventNames as $eventName) {
        $key = null;
        $baseName = null;
        $suffix = null;

        if (preg_match('/^(.*?)\s+\((Male|Female)\)\s*(.*)$/i', $eventName, $matches)) {
            $baseName = trim((string)($matches[1] ?? ''));
            $suffix = trim((string)($matches[3] ?? ''));
            $key = $baseName . '|' . $suffix;
            $byKey[$key]['baseName'] = $baseName;
            $byKey[$key]['suffix'] = $suffix;
            $byKey[$key]['genders'][strtoupper((string)($matches[2] ?? ''))] = true;
        } elseif (preg_match('/^(.*?)\s+M\s*&\s*F\s*(.*)$/i', $eventName, $matches)) {
            $baseName = trim((string)($matches[1] ?? ''));
            $suffix = trim((string)($matches[2] ?? ''));
            $key = $baseName . '|' . $suffix;
            $byKey[$key]['baseName'] = $baseName;
            $byKey[$key]['suffix'] = $suffix;
            $byKey[$key]['combined'] = true;
        }

        if ($key !== null && $baseName !== null) {
            $byKey[$key]['entries'][] = $eventName;
        }
    }

    $processed = array();
    $output = array();

    foreach ($eventNames as $eventName) {
        if (isset($processed[$eventName])) {
            continue;
        }

        $key = null;
        $baseName = null;
        $suffix = null;

        if (preg_match('/^(.*?)\s+\((Male|Female)\)\s*(.*)$/i', $eventName, $matches)) {
            $baseName = trim((string)($matches[1] ?? ''));
            $suffix = trim((string)($matches[3] ?? ''));
            $key = $baseName . '|' . $suffix;
        } elseif (preg_match('/^(.*?)\s+M\s*&\s*F\s*(.*)$/i', $eventName, $matches)) {
            $baseName = trim((string)($matches[1] ?? ''));
            $suffix = trim((string)($matches[2] ?? ''));
            $key = $baseName . '|' . $suffix;
        }

        if ($key !== null && isset($byKey[$key])) {
            $meta = $byKey[$key];
            $hasMale = !empty($meta['genders']['M']);
            $hasFemale = !empty($meta['genders']['F']);
            $hasCombined = !empty($meta['combined']);

            if (($hasMale && $hasFemale) || $hasCombined) {
                $merged = (string)($meta['baseName'] ?? $baseName) . ' M & F';
                if (!empty($meta['suffix'])) {
                    $merged .= ' ' . $meta['suffix'];
                }
                $output[] = $merged;

                foreach (($meta['entries'] ?? array()) as $candidate) {
                    $processed[$candidate] = true;
                }
                continue;
            }
        }

        $output[] = $eventName;
        $processed[$eventName] = true;
    }

    return $output;
}
?>

<style>
@page {
    size: A4 portrait;
    margin: 8mm;
}

/* ===== SMALL PROGRAM BOOKLET ===== */
.sp-wrap {
    background: #d8d8d8;
    padding: 24px 16px;
    font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
}
.sp-sheet {
    max-width: 860px;
    margin: 0 auto;
    background: #fff;
    color: #1a1a1a;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    border-radius: 4px;
    overflow: hidden;
}

/* --- HEADER --- */
.sp-header {
    background: #1e3a6e;
    color: #fff;
    padding: 22px 32px 16px;
    display: flex;
    align-items: center;
    gap: 24px;
}
.sp-header-logo {
    flex-shrink: 0;
}
.sp-logo-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.6);
    object-fit: cover;
    background: #fff;
    padding: 0;
}
.sp-header-title {
    flex: 1;
    text-align: center;
}
.sp-header-title .sp-org {
    font-size: 13px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    opacity: 0.85;
    margin-bottom: 2px;
}
.sp-header-title .sp-convention {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    line-height: 1.1;
}
.sp-header-title .sp-name {
    font-size: 17px;
    font-weight: 600;
    margin-top: 3px;
}
.sp-header-title .sp-dates {
    font-size: 14px;
    margin-top: 4px;
    opacity: 0.9;
}
.sp-header-title .sp-venue {
    font-size: 13px;
    margin-top: 3px;
    color: #a8d08d;
    font-weight: 600;
    letter-spacing: 0.06em;
}

/* --- BODY --- */
.sp-body {
    padding: 20px 28px 24px;
}

/* --- INTRO SECTION --- */
.sp-intro {
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e0e0e0;
}
.sp-intro-heading {
    font-size: 16px;
    font-weight: 700;
    color: #1e3a6e;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.sp-intro-row {
    display: flex;
    gap: 0;
    padding: 3px 0;
    font-size: 14px;
    border-bottom: 1px solid #f0f0f0;
}
.sp-intro-row:last-child {
    border-bottom: none;
}
.sp-intro-time {
    width: 155px;
    flex-shrink: 0;
    color: #444;
    font-weight: 600;
}
.sp-intro-text {
    flex: 1;
}

/* --- DAY SECTION --- */
.sp-day {
    margin-top: 18px;
}
.sp-day-heading {
    background: #1e3a6e;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 3px;
    margin-bottom: 10px;
}

/* --- SESSION BLOCK --- */
.sp-session {
    margin-bottom: 12px;
}
.sp-session-bar {
    display: flex;
    align-items: baseline;
    gap: 10px;
    background: #eef2f9;
    border-left: 4px solid #3758a6;
    padding: 5px 10px;
    margin-bottom: 8px;
    border-radius: 0 3px 3px 0;
}
.sp-session-time {
    font-size: 13px;
    font-weight: 700;
    color: #1e3a6e;
    white-space: nowrap;
}
.sp-session-label {
    font-size: 12px;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

/* --- ROOM CARDS GRID --- */
.sp-rooms {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sp-room-card {
    flex: 1 1 160px;
    min-width: 130px;
    border: 1px solid #c8d0dc;
    border-radius: 4px;
    overflow: hidden;
    break-inside: avoid;
    page-break-inside: avoid;
}
.sp-room-card-header {
    background: #3758a6;
    color: #fff;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 4px 8px;
    text-align: center;
}
.sp-room-card-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.sp-card-edit-btn {
    border: 0;
    background: rgba(255,255,255,0.18);
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 3px;
    cursor: pointer;
}
.sp-card-editor {
    display: none;
    padding: 8px;
    border-top: 1px solid #d9d9d9;
    background: #f8fbff;
}
.sp-card-editor textarea {
    width: 100%;
    min-height: 88px;
    resize: vertical;
    font-size: 11px;
    line-height: 1.35;
    padding: 6px;
    border: 1px solid #c4cfe5;
    border-radius: 3px;
}
.sp-card-editor-actions {
    margin-top: 6px;
    display: flex;
    gap: 6px;
}
.sp-card-editor-actions button {
    border: 0;
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 3px;
    cursor: pointer;
}
.sp-card-editor-save {
    background: #2f8f4f;
    color: #fff;
}
.sp-card-editor-cancel {
    background: #b8c3d6;
    color: #1a1a1a;
}
.sp-room-card-events {
    padding: 5px 8px 4px;
    list-style: none;
    margin: 0;
    font-size: 11.5px;
    line-height: 1.35;
}
.sp-room-card-events li {
    padding: 1px 0;
    border-bottom: 1px solid #f2f2f2;
}
.sp-room-card-events li:last-child {
    border-bottom: none;
}

/* --- BANNERS --- */
.sp-banner {
    margin: 14px 0 10px;
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 7px 16px;
    border-radius: 30px;
    background: #1e3a6e;
    color: #fff;
}
.sp-banner.sp-dinner {
    background: #5a3a8a;
}

/* --- EVENING ROW --- */
.sp-evening-row {
    display: flex;
    gap: 0;
    padding: 4px 0;
    font-size: 14px;
    margin-top: 4px;
}
.sp-evening-time {
    width: 155px;
    flex-shrink: 0;
    font-weight: 700;
    color: #5a3a8a;
}
.sp-evening-label {
    flex: 1;
    font-weight: 600;
}
.sp-evening-entries {
    margin-top: 6px;
    border: 1px solid #d6deef;
    border-radius: 3px;
    background: #f9fbff;
}
.sp-evening-entry {
    display: flex;
    gap: 10px;
    padding: 6px 8px;
    border-top: 1px solid #e5ebf6;
}
.sp-evening-entry:first-child {
    border-top: 0;
}
.sp-evening-entry-time {
    width: 180px;
    flex-shrink: 0;
    font-weight: 700;
    color: #1e3a6e;
    font-size: 12px;
}
.sp-evening-entry-text {
    flex: 1;
    font-size: 12px;
    color: #222;
}

/* --- DIVIDER --- */
.sp-divider {
    border: none;
    border-top: 1px dashed #bbb;
    margin: 10px 0 8px;
}

/* --- LEGEND --- */
.sp-legend {
    margin-top: 16px;
    padding-top: 10px;
    border-top: 2px solid #e0e0e0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px 18px;
    font-size: 11.5px;
    color: #444;
}
.sp-legend-item {
    white-space: nowrap;
}
.sp-legend-key {
    font-weight: 700;
    color: #1e3a6e;
}

/* --- OFFSITE NOTE --- */
.sp-offsite-note {
    margin-top: 10px;
    font-size: 12px;
    font-style: italic;
    color: #0077c8;
    font-weight: 700;
}

/* --- ATHLETICS DAY --- */
.sp-athletics {
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e0e0e0;
}
.sp-athletics-row {
    display: flex;
    gap: 0;
    padding: 4px 0;
    font-size: 14px;
    border-bottom: 1px solid #f0f0f0;
}
.sp-athletics-row:last-child { border-bottom: none; }
.sp-athletics-time {
    width: 155px;
    flex-shrink: 0;
    font-weight: 700;
    color: #1e3a6e;
}
.sp-athletics-text { flex: 1; }
.sp-athletics-columns {
    display: flex;
    gap: 18px;
    margin: 10px 0;
}
.sp-athletics-order { flex: 1; }
.sp-athletics-order-heading {
    font-size: 12px;
    font-weight: 700;
    text-decoration: underline;
    margin-bottom: 4px;
    text-transform: uppercase;
}
.sp-athletics-order ul {
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 12px;
    line-height: 1.5;
}
.sp-athletics-order .sp-athletics-order-note {
    font-size: 11px;
    font-style: italic;
    color: #666;
    margin-top: 4px;
}
.sp-athletics-important {
    flex: 1;
    border: 1px solid #c8d0dc;
    border-radius: 4px;
    padding: 8px 10px;
}
.sp-athletics-important-heading {
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 6px;
}
.sp-athletics-important ul {
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 12px;
    font-style: italic;
    line-height: 1.6;
    text-align: center;
}
.sp-athletics-offsite {
    font-size: 11px;
    font-style: italic;
    color: #555;
    margin-top: 6px;
}
.sp-athletics-banner {
    margin: 10px 0 6px;
    text-align: center;
    font-size: 20px;
    font-weight: 700;
    color: #2e7d32;
    font-family: cursive, sans-serif;
}

/* --- FOOTER --- */
.sp-footer {
    margin-top: 12px;
    padding-top: 8px;
    border-top: 1px solid #e8e8e8;
    text-align: center;
    font-size: 11px;
    color: #888;
    letter-spacing: 0.08em;
}

.sp-empty {
    padding: 32px 0;
    font-size: 15px;
    color: #888;
}

@media print {
    .sp-wrap {
        background: #fff;
        padding: 0;
    }
    .sp-sheet {
        box-shadow: none;
        border-radius: 0;
        max-width: none;
        width: 100%;
        min-height: auto;
        margin: 0;
    }
    .sp-header {
        padding: 12px 14px 10px;
    }
    .sp-header-title .sp-name {
        font-size: 20px;
    }
    .sp-body {
        padding: 10px 12px 14px;
    }
    .sp-room-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    /* Cover page (header + intro + first day) stands alone; every subsequent day starts a new page */
    .sp-day + .sp-day {
        break-before: page;
        page-break-before: always;
    }
    /* Awards Ceremony gets its own page */
    .sp-awards-ceremony {
        break-before: page;
        page-break-before: always;
        break-after: page;
        page-break-after: always;
        min-height: 0 !important;
        height: auto !important;
        margin: 0 !important;
        padding: 10mm 12mm !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
        background-size: cover, cover !important;
        background-position: center top, center top !important;
    }
}

/* --- AWARDS CEREMONY --- */
.sp-awards-ceremony {
    margin: 0 -28px -24px;
    min-height: 1120px;
    padding: 18px 28px 24px;
    border-radius: 0;
    background-color: transparent;
    background-image:
        linear-gradient(rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0.45)),
        url('/img/front/awards_ceremony.png');
    background-repeat: no-repeat, no-repeat;
    background-position: center top, center top;
    background-size: 100% 100%, 100% 100%;
}
.sp-awards-banner {
    background: #2f2b84;
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 20px 14px;
    border-radius: 3px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    position: relative;
}
.sp-awards-time {
    max-width: 360px;
    margin: 0 auto;
    padding: 8px 12px;
    text-align: center;
    font-size: 15px;
    font-weight: 700;
    color: #2f2b84;
    background: rgba(255, 255, 255, 0.85);
    border-radius: 3px;
    letter-spacing: 0.04em;
}
.sp-awards-entries {
    max-width: 760px;
    margin: 14px auto 0;
    background: rgba(255, 255, 255, 0.82);
    border-radius: 4px;
    overflow: hidden;
}
.sp-awards-entry {
    display: flex;
    gap: 12px;
    border-bottom: 1px solid rgba(30, 58, 110, 0.16);
    padding: 8px 10px;
    align-items: center;
}
.sp-awards-entry:last-child {
    border-bottom: 0;
}
.sp-awards-entry-time {
    width: 210px;
    flex-shrink: 0;
    color: #2f2b84;
    font-weight: 700;
    font-size: 13px;
}
.sp-awards-entry-text {
    color: #1f1f1f;
    font-size: 13px;
}
</style>

<div class="sp-wrap">
    <div class="sp-sheet">

        <!-- HEADER -->
        <div class="sp-header">
            <?php if ($logoSrc !== '') { ?>
                <div class="sp-header-logo">
                    <img src="<?php echo $logoSrc; ?>" alt="Accelerate" class="sp-logo-img" />
                </div>
            <?php } ?>
            <div class="sp-header-title">
                <div class="sp-org">A.C.E.</div>
                <div class="sp-convention">Student Convention</div>
                <div class="sp-name"><?php echo h($programTitle); ?></div>
                <?php if ($programDateRangeLabel !== '') { ?>
                    <div class="sp-dates"><?php echo h($programDateRangeLabel); ?></div>
                <?php } ?>
                <?php if ($programVenue !== '') { ?>
                    <div class="sp-venue"><?php echo h($programVenue); ?></div>
                <?php } ?>
            </div>
        </div>

        <!-- BODY -->
        <div class="sp-body">

            <?php if ($hasRows) { ?>

                <?php if (count($introEntries)) { ?>
                    <div class="sp-intro">
                        <div class="sp-intro-heading"><?php echo h((string)($smallProgramNotes['intro_day_label'] ?? '')); ?></div>
                        <?php foreach ($introEntries as $entry) { ?>
                            <div class="sp-intro-row">
                                <div class="sp-intro-time"><?php echo h($entry['time']); ?></div>
                                <div class="sp-intro-text"><?php echo h($entry['text']); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php foreach ($programDays as $dayIndex => $dayData) { ?>
                    <div class="sp-day">
                        <div class="sp-day-heading">
                            <?php
                            $dateHeading = trim((string)($dayData['dateLabel'] ?? ''));
                            echo h($dateHeading !== '' ? $dateHeading : $dayData['dayLabel']);
                            ?>
                        </div>

                        <?php
                        // Check if this day is the athletics day
                        $thisDayName = strtolower(trim((string)($dayData['dayLabel'] ?? '')));
                        if (strpos($thisDayName, ' ') !== false) {
                            $thisDayName = strtolower((string)explode(' ', $thisDayName, 2)[0]);
                        }
                        $isAthleticsDay = ($athleticsDayName !== '' && $thisDayName === $athleticsDayName);
                        ?>

                        <?php if ($isAthleticsDay && ($athleticsArriveTime !== '' || $athleticsBeginTime !== '' || $athleticsOrderRaw !== '')) { ?>
                        <div class="sp-athletics">
                            <?php if ($athleticsArriveTime !== '' || $athleticsArriveVenue !== '') { ?>
                            <div class="sp-athletics-row">
                                <div class="sp-athletics-time"><?php echo h($athleticsArriveTime); ?></div>
                                <div class="sp-athletics-text">ARRIVE AT ATHLETICS<?php if ($athleticsArriveVenue !== '') { ?><br><?php echo h($athleticsArriveVenue); ?><?php } ?></div>
                            </div>
                            <?php } ?>
                            <?php if ($athleticsBeginTime !== '' || $athleticsBeginLabel !== '') { ?>
                            <div class="sp-athletics-row">
                                <div class="sp-athletics-time"><?php echo h($athleticsBeginTime); ?></div>
                                <div class="sp-athletics-text"><?php echo h($athleticsBeginLabel); ?></div>
                            </div>
                            <?php } ?>

                            <?php
                            $orderLines = array();
                            if ($athleticsOrderRaw !== '') {
                                foreach (preg_split('/\r\n|\r|\n/', $athleticsOrderRaw) as $ol) {
                                    $ol = trim((string)$ol);
                                    if ($ol !== '') $orderLines[] = $ol;
                                }
                            }
                            $importantLines = array();
                            if ($athleticsImportantRaw !== '') {
                                foreach (preg_split('/\r\n|\r|\n/', $athleticsImportantRaw) as $il) {
                                    $il = trim((string)$il);
                                    if ($il !== '') $importantLines[] = $il;
                                }
                            }
                            ?>
                            <?php if (!empty($orderLines) || !empty($importantLines)) { ?>
                            <div class="sp-athletics-columns">
                                <?php if (!empty($orderLines)) { ?>
                                <div class="sp-athletics-order">
                                    <div class="sp-athletics-order-heading">Order of Events</div>
                                    <ul>
                                        <?php foreach ($orderLines as $ol) { ?>
                                            <li><?php echo h($ol); ?></li>
                                        <?php } ?>
                                    </ul>
                                    <?php if ($athleticsOffsiteNote !== '') { ?>
                                        <div class="sp-athletics-offsite"><?php echo h($athleticsOffsiteNote); ?></div>
                                    <?php } ?>
                                </div>
                                <?php } ?>
                                <?php if (!empty($importantLines)) { ?>
                                <div class="sp-athletics-important">
                                    <div class="sp-athletics-important-heading">Important!</div>
                                    <p style="font-size:12px;text-align:center;margin-bottom:4px;">Please remember to bring:</p>
                                    <ul>
                                        <?php foreach ($importantLines as $il) { ?>
                                            <li><?php echo h($il); ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <?php } ?>
                            </div>
                            <?php } ?>

                            <?php if ($athleticsBanner !== '') { ?>
                            <div class="sp-athletics-banner"><?php echo h($athleticsBanner); ?></div>
                            <?php } ?>
                        </div>
                        <?php } ?>

                        <?php foreach ($dayData['sessions'] as $sessionData) { ?>
                            <?php
                            $sessionStart  = !empty($sessionData['startRaw'])  ? date('g:i a', strtotime((string)$sessionData['startRaw']))  : '';
                            $sessionFinish = !empty($sessionData['finishRaw']) ? date('g:i a', strtotime((string)$sessionData['finishRaw'])) : '';
                            $sessionRange  = trim($sessionStart . ($sessionFinish !== '' ? ' – ' . $sessionFinish : ''));
                            ?>

                            <div class="sp-session">
                                <div class="sp-session-bar">
                                    <span class="sp-session-time"><?php echo h($sessionRange); ?></span>
                                    <span class="sp-session-label"><?php echo h($sessionData['title']); ?></span>
                                </div>

                                <div class="sp-rooms">
                                    <?php foreach ($sessionData['rooms'] as $roomName => $eventNames) { ?>
                                        <?php
                                        $displayEventNames = consolidateEventNames($eventNames);
                                        $cardId = md5((string)$dayData['dayLabel'].'|'.(string)$sessionData['key'].'|'.(string)$roomName);
                                        ?>
                                        <div class="sp-room-card">
                                            <div class="sp-room-card-header">
                                                <div class="sp-room-card-header-row">
                                                    <span><?php echo h($roomName); ?></span>
                                                    <?php if ($smallProgramEditable) { ?>
                                                        <button type="button" class="sp-card-edit-btn" data-card-id="<?php echo h($cardId); ?>">Edit</button>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <ul class="sp-room-card-events" data-card-id="<?php echo h($cardId); ?>" data-day="<?php echo h($dayData['dayLabel']); ?>" data-session="<?php echo h($sessionData['key']); ?>" data-room="<?php echo h($roomName); ?>">
                                                <?php foreach ($displayEventNames as $eventName) { ?>
                                                    <li><?php echo h($eventName); ?></li>
                                                <?php } ?>
                                            </ul>
                                            <?php if ($smallProgramEditable) { ?>
                                            <div class="sp-card-editor" data-card-id="<?php echo h($cardId); ?>">
                                                <textarea class="sp-card-editor-text" placeholder="One event per line"><?php echo h(implode("\n", $displayEventNames)); ?></textarea>
                                                <div class="sp-card-editor-actions">
                                                    <button type="button" class="sp-card-editor-save" data-card-id="<?php echo h($cardId); ?>">Apply</button>
                                                    <button type="button" class="sp-card-editor-cancel" data-card-id="<?php echo h($cardId); ?>">Cancel</button>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if ($sessionData['key'] === 'morning' && $lunchBanner !== '') { ?>
                                <div class="sp-banner"><?php echo h($lunchBanner); ?></div>
                            <?php } ?>

                            <?php if ($sessionData['key'] === 'afternoon' && $dinnerBanner !== '') { ?>
                                <div class="sp-banner sp-dinner"><?php echo h($dinnerBanner); ?></div>
                            <?php } ?>

                        <?php } ?>

                        <?php
                        $dayName = strtolower(trim((string)($dayData['dayLabel'] ?? '')));
                        if ($dayName !== '' && strpos($dayName, ' ') !== false) {
                            $dayName = strtolower((string)explode(' ', $dayName, 2)[0]);
                        }
                        $eveningEntriesForDay = array();
                        foreach ($eveningRallyEntries as $entry) {
                            $entryDay = strtolower(trim((string)($entry['day'] ?? '')));
                            if ($entryDay === '') {
                                $eveningEntriesForDay[] = $entry;
                                continue;
                            }
                            if ($dayName !== '' && (strpos($entryDay, $dayName) === 0 || strpos($dayName, $entryDay) === 0)) {
                                $eveningEntriesForDay[] = $entry;
                            }
                        }
                        ?>
                        <?php if (($eveningRallyTime !== '' || $eveningRallyLabel !== '') && !empty($eveningEntriesForDay)) { ?>
                            <div class="sp-evening-row">
                                <div class="sp-evening-time"><?php echo h($eveningRallyTime); ?></div>
                                <div class="sp-evening-label"><?php echo h($eveningRallyLabel); ?></div>
                            </div>
                            <div class="sp-evening-entries">
                                <?php foreach ($eveningEntriesForDay as $entry) { ?>
                                    <div class="sp-evening-entry">
                                        <div class="sp-evening-entry-time">
                                            <?php
                                            $range = trim((string)$entry['start']);
                                            if (trim((string)$entry['end']) !== '') {
                                                $range .= ($range !== '' ? ' - ' : '') . trim((string)$entry['end']);
                                            }
                                            echo h($range);
                                            ?>
                                        </div>
                                        <div class="sp-evening-entry-text"><?php echo h((string)$entry['text']); ?></div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if ($dayIndex === array_key_last($programDays)) { ?>
                            <div class="sp-legend">
                                <span class="sp-legend-item"><span class="sp-legend-key">M</span> – Male</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">F</span> – Female</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">U14</span> – Under 14</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">U16</span> – Under 16</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">U17</span> – Under 17</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">O</span> – Open</span>
                            </div>
                            <?php if ($offsiteNote !== '') { ?>
                                <div class="sp-offsite-note"><?php echo h($offsiteNote); ?></div>
                            <?php } ?>
                            <?php if ($footerNote !== '') { ?>
                                <div class="sp-footer" style="color:#444;letter-spacing:0.02em;"><?php echo h($footerNote); ?></div>
                            <?php } ?>
                        <?php } ?>

                    </div>
                <?php } ?>

            <?php } else { ?>
                <div class="sp-empty">No scheduling rows found for this season. Generate schedules first, then open Small Program again.</div>
            <?php } ?>

            <!-- AWARDS CEREMONY PAGE -->
            <div class="sp-awards-ceremony">
                <div class="sp-awards-banner">Awards Ceremony</div>
                <?php if ($awardsCeremonyTime !== '') { ?>
                    <div class="sp-awards-time"><?php echo h($awardsCeremonyTime); ?></div>
                <?php } ?>
                <?php if (!empty($awardsEntries)) { ?>
                    <div class="sp-awards-entries">
                        <?php foreach ($awardsEntries as $entry) { ?>
                            <div class="sp-awards-entry">
                                <div class="sp-awards-entry-time">
                                    <?php
                                    $range = trim((string)$entry['start']);
                                    if (trim((string)$entry['end']) !== '') {
                                        $range .= ($range !== '' ? ' - ' : '') . trim((string)$entry['end']);
                                    }
                                    echo h($range);
                                    ?>
                                </div>
                                <div class="sp-awards-entry-text"><?php echo h((string)$entry['text']); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if ($footerNote !== '') { ?>
                    <div class="sp-footer" style="margin-top:16px;color:#444;letter-spacing:0.02em;"><?php echo h($footerNote); ?></div>
                <?php } ?>
                <div class="sp-footer"><?php echo h($programTitle); ?> A.C.E. Student Convention<?php if ($programSeason !== '') { ?> &mdash; <?php echo h($programSeason); ?><?php } ?></div>
            </div>

        </div><!-- /.sp-body -->
    </div><!-- /.sp-sheet -->
</div><!-- /.sp-wrap -->
