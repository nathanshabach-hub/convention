<?php
$eventD = $eventD ?? null;
$conventionSD = $conventionSD ?? null;
$schedCat = isset($schedCat) ? (int)$schedCat : 0;
$bracketData = isset($bracketData) && is_array($bracketData) ? $bracketData : [];
$teamNameMap = isset($teamNameMap) && is_array($teamNameMap) ? $teamNameMap : [];
$eventName = is_object($eventD) ? (string)($eventD->event_name ?? 'Event') : 'Event';
$programTitle = is_object($conventionSD) ? trim((string)($conventionSD->Conventions['name'] ?? '')) : '';

if (!function_exists('sdGetSportRoundLabel')) {
	function sdGetSportRoundLabel($round, $totalRounds) {
		if ($round === $totalRounds) {
			return 'Final';
		}
		if ($round === $totalRounds - 1 && $totalRounds > 2) {
			return 'Semi Final';
		}
		if ($round === $totalRounds - 2 && $totalRounds > 3) {
			return 'Quarter Final';
		}
		return 'Round ' . $round;
	}
}

if (!function_exists('sdGetTeamDisplay')) {
	function sdGetTeamDisplay($rawName, $teamNameMap, $schedCat) {
		$raw = trim((string)$rawName);
		if ($raw === '') {
			return 'TBD';
		}
		if ($schedCat === 3) {
			return 'Team ' . $raw;
		}
		return isset($teamNameMap[$raw]) ? (string)$teamNameMap[$raw] : ('Player ' . $raw);
	}
}

if (!function_exists('sdGetGroupTeamDisplay')) {
	function sdGetGroupTeamDisplay($schoolId, $groupName, $teamNameMap) {
		$uid = (string)(int)$schoolId;
		$grp = trim((string)$groupName);
		if ($uid === '0' && $grp === '') {
			return 'TBD';
		}

		$schoolName = isset($teamNameMap[$uid]) ? trim((string)$teamNameMap[$uid]) : '';
		if ($schoolName === '') {
			$schoolName = 'Team';
		}

		if ($grp !== '') {
			return $schoolName . ' (Group-' . $grp . ')';
		}

		return $schoolName;
	}
}

if (!function_exists('sdIsPlaceholderLabel')) {
	function sdIsPlaceholderLabel($label) {
		return preg_match('/^(WM|RM)-\d+$/', trim((string)$label)) === 1;
	}
}

if (!function_exists('sdGetEliminationSideLabel')) {
	function sdGetEliminationSideLabel($sourceRow, $schedCat, $teamNameMap) {
		if (!$sourceRow) {
			return 'TBD';
		}

		$matchNo = (int)($sourceRow->match_number ?? 0);
		$isBye = (int)($sourceRow->is_bye ?? 0) === 1;

		if ($isBye) {
			if ((int)$schedCat === 3) {
				return sdGetGroupTeamDisplay((int)($sourceRow->user_id ?? 0), (string)($sourceRow->group_name ?? ''), $teamNameMap);
			}
			if ((int)$schedCat === 2) {
				return sdGetTeamDisplay((string)($sourceRow->user_id ?? ''), $teamNameMap, $schedCat);
			}
		}

		if ($matchNo > 0) {
			return 'WM-' . $matchNo;
		}

		return 'TBD';
	}
}

if (!function_exists('sdGetEliminationRunnerUpLabel')) {
	function sdGetEliminationRunnerUpLabel($sourceRow) {
		if (!$sourceRow) {
			return 'TBD';
		}

		if ((int)($sourceRow->is_bye ?? 0) === 1) {
			return 'TBD';
		}

		$matchNo = (int)($sourceRow->match_number ?? 0);
		if ($matchNo > 0) {
			return 'RM-' . $matchNo;
		}

		return 'TBD';
	}
}


$totalRounds = count($bracketData);
$logoPath = defined('WWW_ROOT') ? WWW_ROOT . 'img/front/accelerate-logo.jpg' : '';
$logoSrc = '';
if ($logoPath !== '' && is_file($logoPath)) {
	$logoData = @file_get_contents($logoPath);
	if ($logoData !== false) {
		$logoSrc = 'data:image/jpeg;base64,' . base64_encode($logoData);
	}
}
?>
<style>
.sd-wrap { font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a1a; background: #f0f0f0; padding: 20px; }
.sd-sheet { max-width: 960px; margin: 0 auto; background: #fff; box-shadow: 0 6px 24px rgba(0,0,0,0.14); border-radius: 4px; overflow: hidden; }
.sd-header { background: #1e3a6e; color: #fff; display: flex; align-items: center; gap: 20px; padding: 18px 28px 14px; }
.sd-logo { width: 80px; height: 80px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.5); background: #fff; object-fit: cover; padding: 0; flex-shrink: 0; }
.sd-header-text .sd-org { font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; opacity: 0.8; }
.sd-header-text .sd-event { font-size: 20px; font-weight: 700; line-height: 1.15; margin-top: 2px; }
.sd-header-text .sd-convention { font-size: 13px; opacity: 0.85; margin-top: 3px; }
.sd-body { padding: 20px 24px 28px; }
.sd-round { margin-bottom: 20px; }
.sd-round-heading { background: #3758a6; color: #fff; font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 5px 14px; border-radius: 3px; margin-bottom: 8px; }
.sd-round-heading.sd-final { background: #8b1a1a; }
.sd-round-heading.sd-semi { background: #5a3a8a; }
.sd-round-heading.sd-quarter { background: #1e6e4e; }
.sd-round-heading.sd-thirdplace { background: #8a6a3a; }
.sd-bracket-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sd-bracket-table thead th { background: #eef2f9; border: 1px solid #c8d0dc; padding: 5px 10px; text-align: left; font-weight: 700; font-size: 12px; color: #1e3a6e; }
.sd-bracket-table tbody tr { border-bottom: 1px solid #e8e8e8; }
.sd-bracket-table tbody tr:last-child { border-bottom: none; }
.sd-bracket-table tbody tr.sd-bye { background: #f7f7f7; color: #999; font-style: italic; }
.sd-bracket-table tbody tr.sd-future { background: #fafafa; color: #aaa; }
.sd-bracket-table tbody td { padding: 6px 10px; border: 1px solid #e8e8e8; vertical-align: middle; }
.sd-match-num { width: 60px; text-align: center; font-weight: 700; color: #555; }
.sd-team-a,
.sd-team-b {
	width: 30%;
	font-weight: 600;
	position: relative;
}
.sd-team-name {
	display: block;
	padding-right: 6px;
}
.sd-team-name.sd-team-actual { color: #1a1a1a; }
.sd-team-name.sd-wm-label { color: #8f98a6; font-weight: 600; }
.sd-team-cell {
	display: block;
}
.sd-vs { width: 36px; text-align: center; color: #999; font-size: 11px; font-style: italic; }
.sd-day { width: 90px; color: #555; }
.sd-time { width: 90px; color: #555; }
.sd-location { color: #555; }
.sd-tbd { color: #bbb; font-style: italic; }
.sd-footer { margin-top: 16px; text-align: center; font-size: 10px; color: #aaa; letter-spacing: 0.06em; }
.sd-empty { padding: 24px; color: #888; font-size: 14px; }
@media print {
	.sd-wrap { background: #fff; padding: 0; }
	.sd-sheet { box-shadow: none; border-radius: 0; max-width: 100%; }
	.sd-round { }
	.sd-round + .sd-round { break-before: page; page-break-before: always; }
	.sd-round + .sd-round + .sd-round { break-before: auto; page-break-before: auto; }
	.sd-round-semi { break-before: page; page-break-before: always; }
	.sd-single-page .sd-round + .sd-round,
	.sd-single-page .sd-round-semi { break-before: auto !important; page-break-before: auto !important; }
	.sd-bracket-table { font-size: 11px; }
	.sd-bracket-table thead th { padding: 3px 7px; font-size: 10px; }
	.sd-bracket-table tbody td { padding: 3px 7px; }
	.sd-body { padding: 10px 14px 14px; }
	.sd-round { margin-bottom: 10px; }
	.sd-round-heading { padding: 3px 10px; font-size: 11px; margin-bottom: 4px; }
}
</style>

<div class="sd-wrap">
	<div class="sd-sheet">
		<div class="sd-header">
			<?php if ($logoSrc !== '') { ?>
				<img src="<?php echo $logoSrc; ?>" alt="Logo" class="sd-logo" />
			<?php } ?>
			<div class="sd-header-text">
				<div class="sd-org">A.C.E. Student Convention</div>
				<div class="sd-event"><?php echo h($eventName); ?></div>
				<div class="sd-convention"><?php echo h($programTitle); ?></div>
			</div>
		</div>

		<div class="sd-body">
			<?php if (empty($bracketData)) { ?>
				<div class="sd-empty">No draw data found for this event. Run the scheduling engine first.</div>
			<?php } else { ?>
				<?php
				$timingById = [];
				$hasActualThirdPlaceMatch = false;
				$finalRoundNumber = 0;
				foreach ($bracketData as $roundRows) {
					foreach ($roundRows as $timingRow) {
						$timingById[(int)$timingRow->id] = $timingRow;
						$finalRoundNumber = max($finalRoundNumber, (int)($timingRow->round_number ?? 0));
					}
				}
				if ($finalRoundNumber > 0 && !empty($bracketData[$finalRoundNumber]) && count($bracketData[$finalRoundNumber]) > 1) {
					$hasActualThirdPlaceMatch = true;
				}
				?>
				<?php foreach ($bracketData as $roundNum => $matches) { ?>
					<?php
					$displayMatches = $matches;
					$actualThirdPlaceRow = null;
					if ((int)$roundNum === $finalRoundNumber && count($matches) > 1) {
						usort($displayMatches, function ($left, $right) {
							return ((int)($left->match_number ?? 0)) <=> ((int)($right->match_number ?? 0));
						});
						$actualThirdPlaceRow = array_pop($displayMatches);
					}
					$roundLabel = sdGetSportRoundLabel((int)$roundNum, $totalRounds);
					$visibleMatches = [];
					foreach ($displayMatches as $candidateMatch) {
						if ((int)($candidateMatch->is_bye ?? 0) === 1) {
							continue;
						}
						$visibleMatches[] = $candidateMatch;
					}
					if (!count($visibleMatches) && !$actualThirdPlaceRow) {
						continue;
					}
					$headingClass = 'sd-round-heading';
					$roundDivClass = 'sd-round';
					if ($roundLabel === 'Final') {
						$headingClass .= ' sd-final';
						$roundDivClass .= ' sd-round-final';
					} elseif ($roundLabel === 'Semi Final') {
						$headingClass .= ' sd-semi';
						$roundDivClass .= ' sd-round-semi';
					} elseif ($roundLabel === 'Quarter Final') {
						$headingClass .= ' sd-quarter';
						$roundDivClass .= ' sd-round-quarter';
					}
					?>
					<div class="<?php echo $roundDivClass; ?>">
						<?php if ($actualThirdPlaceRow) { ?>
							<?php
							$srcOne = !empty($actualThirdPlaceRow->schtimeautoid1) ? ($timingById[(int)$actualThirdPlaceRow->schtimeautoid1] ?? null) : null;
							$srcTwo = !empty($actualThirdPlaceRow->schtimeautoid2) ? ($timingById[(int)$actualThirdPlaceRow->schtimeautoid2] ?? null) : null;
							$thirdA = sdGetEliminationSideLabel($srcOne, (int)$schedCat, $teamNameMap);
							$thirdB = sdGetEliminationSideLabel($srcTwo, (int)$schedCat, $teamNameMap);
							if (sdIsPlaceholderLabel($thirdA) && !sdIsPlaceholderLabel($thirdB)) {
								$tmp = $thirdA;
								$thirdA = $thirdB;
								$thirdB = $tmp;
							}
							$thirdAClass = sdIsPlaceholderLabel($thirdA) ? 'sd-team-name sd-wm-label' : 'sd-team-name sd-team-actual';
							$thirdBClass = sdIsPlaceholderLabel($thirdB) ? 'sd-team-name sd-wm-label' : 'sd-team-name sd-team-actual';
							$thirdTime = !empty($actualThirdPlaceRow->start_time) ? date('g:i a', strtotime((string)$actualThirdPlaceRow->start_time)) : '';
							$thirdRoom = isset($actualThirdPlaceRow->Conventionrooms['room_name']) ? (string)$actualThirdPlaceRow->Conventionrooms['room_name'] : '';
							?>
							<div class="sd-round-heading sd-thirdplace">3rd Place Playoff</div>
							<table class="sd-bracket-table" style="margin-bottom:12px;">
								<thead>
									<tr>
										<th class="sd-match-num">#</th>
										<th class="sd-team-a"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
										<th class="sd-vs">vs</th>
										<th class="sd-team-b"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
										<th class="sd-day">Day</th>
										<th class="sd-time">Time</th>
										<th class="sd-location">Location</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="sd-match-num"><?php echo h($actualThirdPlaceRow->match_number ?? ''); ?></td>
										<td class="sd-team-a"><span class="sd-team-cell"><span class="<?php echo h($thirdAClass); ?>"><?php echo h($thirdA); ?></span></span></td>
										<td class="sd-vs">vs</td>
										<td class="sd-team-b"><span class="sd-team-cell"><span class="<?php echo h($thirdBClass); ?>"><?php echo h($thirdB); ?></span></span></td>
										<td class="sd-day"><?php echo h($actualThirdPlaceRow->day ?? ''); ?></td>
										<td class="sd-time"><?php echo h($thirdTime); ?></td>
										<td class="sd-location"><?php echo h($thirdRoom); ?></td>
									</tr>
								</tbody>
							</table>
						<?php } ?>
						<div class="<?php echo $headingClass; ?>"><?php echo h($roundLabel); ?></div>
						<table class="sd-bracket-table">
							<thead>
								<tr>
									<th class="sd-match-num">#</th>
									<th class="sd-team-a"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
									<th class="sd-vs">vs</th>
									<th class="sd-team-b"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
									<th class="sd-day">Day</th>
									<th class="sd-time">Time</th>
									<th class="sd-location">Location</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($visibleMatches as $row) { ?>
									<?php
									$isBye = (int)($row->is_bye ?? 0) === 1;
									$teamAraw = $schedCat === 3 ? (string)($row->group_name ?? '') : (string)($row->user_id ?? '');
									$teamBraw = $schedCat === 3 ? (string)($row->group_name_opponent ?? '') : (string)($row->user_id_opponent ?? '');
									$teamAtext = $schedCat === 3
										? sdGetGroupTeamDisplay((int)($row->user_id ?? 0), (string)($row->group_name ?? ''), $teamNameMap)
										: sdGetTeamDisplay($teamAraw, $teamNameMap, $schedCat);
									$teamBtext = $schedCat === 3
										? sdGetGroupTeamDisplay((int)($row->user_id_opponent ?? 0), (string)($row->group_name_opponent ?? ''), $teamNameMap)
										: sdGetTeamDisplay($teamBraw, $teamNameMap, $schedCat);

									if ((int)($row->round_number ?? 0) > 1 && ((int)$schedCat === 2 || (int)$schedCat === 3)) {
										$srcOne = !empty($row->schtimeautoid1) ? ($timingById[(int)$row->schtimeautoid1] ?? null) : null;
										$srcTwo = !empty($row->schtimeautoid2) ? ($timingById[(int)$row->schtimeautoid2] ?? null) : null;
										$teamAtext = sdGetEliminationSideLabel($srcOne, (int)$schedCat, $teamNameMap);
										$teamBtext = sdGetEliminationSideLabel($srcTwo, (int)$schedCat, $teamNameMap);

										if (sdIsPlaceholderLabel($teamAtext) && !sdIsPlaceholderLabel($teamBtext)) {
											$tmp = $teamAtext;
											$teamAtext = $teamBtext;
											$teamBtext = $tmp;
										}
									}
									$teamAClass = sdIsPlaceholderLabel($teamAtext) ? 'sd-team-name sd-wm-label' : 'sd-team-name sd-team-actual';
									$teamBClass = sdIsPlaceholderLabel($teamBtext) ? 'sd-team-name sd-wm-label' : 'sd-team-name sd-team-actual';
									$isFuture = trim($teamAraw) === '' && trim($teamBraw) === '';
									$rowClass = $isBye ? ' sd-bye' : ($isFuture ? ' sd-future' : '');
									$startTime = !empty($row->start_time) ? date('g:i a', strtotime((string)$row->start_time)) : '';
									$roomName = isset($row->Conventionrooms['room_name']) ? (string)$row->Conventionrooms['room_name'] : '';
									?>
									<tr class="<?php echo h($rowClass); ?>">
										<td class="sd-match-num"><?php echo h($row->match_number ?? ''); ?></td>
										<td class="sd-team-a">
											<span class="sd-team-cell">
												<span class="<?php echo h($teamAClass); ?>"><?php echo h($teamAtext); ?></span>
											</span>
										</td>
										<td class="sd-vs">vs</td>
										<td class="sd-team-b">
											<span class="sd-team-cell">
												<span class="<?php echo h($teamBClass); ?>"><?php if ($isBye) { ?><em style="color:#bbb;">— bye —</em><?php } else { ?><?php echo h($teamBtext); ?><?php } ?></span>
											</span>
										</td>
										<td class="sd-day"><?php echo h($row->day ?? ''); ?></td>
										<td class="sd-time"><?php echo h($startTime); ?></td>
										<td class="sd-location"><?php echo h($roomName); ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>

						<?php if (!$hasActualThirdPlaceMatch && $roundLabel === 'Semi Final' && count($matches) >= 2) { ?>
							<?php
							$semiOne = $matches[0] ?? null;
							$semiTwo = $matches[1] ?? null;
							$thirdA = sdGetEliminationRunnerUpLabel($semiOne);
							$thirdB = sdGetEliminationRunnerUpLabel($semiTwo);
							if (sdIsPlaceholderLabel($thirdA) && !sdIsPlaceholderLabel($thirdB)) {
								$tmp = $thirdA;
								$thirdA = $thirdB;
								$thirdB = $tmp;
							}
							$thirdAClass = sdIsPlaceholderLabel($thirdA) ? 'sd-team-name sd-wm-label' : 'sd-team-name sd-team-actual';
							$thirdBClass = sdIsPlaceholderLabel($thirdB) ? 'sd-team-name sd-wm-label' : 'sd-team-name sd-team-actual';
							?>
							<div class="sd-round" style="margin-top:12px;">
								<div class="sd-round-heading sd-thirdplace">3rd Place Playoff</div>
								<table class="sd-bracket-table">
									<thead>
										<tr>
											<th class="sd-match-num">#</th>
											<th class="sd-team-a"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
											<th class="sd-vs">vs</th>
											<th class="sd-team-b"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
											<th class="sd-day">Day</th>
											<th class="sd-time">Time</th>
											<th class="sd-location">Location</th>
										</tr>
									</thead>
									<tbody>
										<tr class="sd-future">
											<td class="sd-match-num">3P</td>
											<td class="sd-team-a"><span class="sd-team-cell"><span class="<?php echo h($thirdAClass); ?>"><?php echo h($thirdA); ?></span></span></td>
											<td class="sd-vs">vs</td>
											<td class="sd-team-b"><span class="sd-team-cell"><span class="<?php echo h($thirdBClass); ?>"><?php echo h($thirdB); ?></span></span></td>
											<td class="sd-day">TBD</td>
											<td class="sd-time">TBD</td>
											<td class="sd-location">TBD</td>
										</tr>
									</tbody>
								</table>
							</div>
						<?php } ?>
					</div>
				<?php } ?>
				<div class="sd-footer"><?php echo h($programTitle); ?> &mdash; <?php echo h($eventName); ?> Draw</div>
			<?php } ?>
		</div>
	</div>
</div>