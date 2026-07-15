<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

#[\AllowDynamicProperties]
class ResultsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize(): void {
        parent::initialize();
		$this->loadComponent('RequestHandler');
		$this->loadComponent('PImage');
		$this->loadComponent('PImageTest');
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->session()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }
		
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Conventions');
		$this->loadModel('Eventsubmissions');
		$this->loadModel('Judgeevaluations');
		$this->loadModel('Results');
		$this->loadModel('Resultpositions');
		$this->loadModel('Crstudentevents');
		$this->loadModel('Divisions');
		
    }

	private function isAutoAwardEvent($eventD)
	{
		$eventName = strtolower(trim((string)($eventD->event_name ?? '')));
		$eventName = preg_replace('/\s+/', ' ', $eventName);

		$autoAwardEvents = [
			'golden apple award u16',
			'golden harp award open',
			'golden lamb award open',
			'golden apple award open',
			'golden harp award u16',
			'golden lamb award u16',
			'christian soldier award u16',
			'christian worker award u16',
			'christian soldier award open',
			'christian worker award open',
		];

		return in_array($eventName, $autoAwardEvents, true);
	}

	private function saveAutoAwardResultPositions($conventionSD, $eventD, $result_id)
	{
		$submissions = $this->Eventsubmissions->find()
			->where([
				'Eventsubmissions.conventionseason_id' => $conventionSD->id,
				'Eventsubmissions.convention_id' => $conventionSD->convention_id,
				'Eventsubmissions.season_id' => $conventionSD->season_id,
				'Eventsubmissions.season_year' => $conventionSD->season_year,
				'Eventsubmissions.event_id' => $eventD->id,
			])
			->contain(['Students'])
			->all();

		$savedCount = 0;
		foreach ($submissions as $datarecord)
		{
			$resultpositions = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, []);

			$dataRP->slug = 'result-positions-'.$result_id.'-'.$conventionSD->id.'-'.time().'-'.rand(100,1000000);
			$dataRP->result_id = $result_id;
			$dataRP->eventsubmission_id = $datarecord->id;
			$dataRP->conventionregistration_id = $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id = $datarecord->conventionseason_id;
			$dataRP->convention_id = $datarecord->convention_id;
			$dataRP->user_id = $datarecord->user_id;
			$dataRP->season_id = $datarecord->season_id;
			$dataRP->season_year = $datarecord->season_year;
			$dataRP->event_id = $eventD->id;
			$dataRP->event_id_number = $eventD->event_id_number;
			$dataRP->division_id = $eventD->division_id;
			$dataRP->group_name = $datarecord->group_name;
			$dataRP->student_id = $datarecord->student_id;
			$dataRP->gender = $datarecord->Students['gender'] ?? null;
			$dataRP->avg_marks = null;
			$dataRP->position = 1;
			$dataRP->points_obtained = 12;
			$dataRP->created = date('Y-m-d H:i:s');
			$dataRP->modified = date('Y-m-d H:i:s');

			if ($this->Resultpositions->save($dataRP)) {
				$savedCount++;
			}
		}

		return $savedCount;
	}

	private function isSilverAppleEvent($eventD)
	{
		$name = strtolower(preg_replace('/\s+/', ' ', trim((string)($eventD->event_name ?? ''))));
		return in_array($name, ['silver apple award u16', 'silver apple award open'], true);
	}

	private function silverAppleBookTable()
	{
		// [book_id => [chapters, verses]]  — matches books table IDs 1-63
		return [
			1=>[50,1533], 2=>[40,1213], 3=>[27,859],  4=>[36,1263], 5=>[34,959],
			6=>[24,658],  7=>[21,618],  8=>[4,85],    9=>[31,810],  10=>[24,672],
			11=>[22,816], 12=>[25,719], 13=>[29,941], 14=>[36,821], 15=>[10,280],
			16=>[13,406], 17=>[10,167], 18=>[42,1049],19=>[12,222], 20=>[8,117],
			21=>[66,1264],22=>[52,1363],23=>[5,154],  24=>[48,1273],25=>[12,357],
			26=>[14,197], 27=>[3,73],   28=>[9,146],  29=>[1,21],   30=>[4,48],
			31=>[7,105],  32=>[3,47],   33=>[3,56],   34=>[3,53],   35=>[2,38],
			36=>[14,211], 37=>[4,55],   38=>[28,1071],39=>[16,678], 40=>[24,1151],
			41=>[28,1007],42=>[16,433], 43=>[16,437], 44=>[13,239], 45=>[6,149],
			46=>[6,155],  47=>[4,104],  48=>[4,95],   49=>[5,89],   50=>[3,47],
			51=>[6,98],   52=>[4,83],   53=>[3,46],   54=>[1,25],   55=>[13,303],
			56=>[5,108],  57=>[5,105],  58=>[3,61],   59=>[5,105],  60=>[1,13],
			61=>[1,14],   62=>[1,25],   63=>[21,404],
		];
	}

	private function silverAppleCalcPlace($bookIdsString)
	{
		$table         = $this->silverAppleBookTable();
		$ids           = array_filter(array_map('intval', explode(',', $bookIdsString)));
		$totalVerses   = 0;
		$totalChapters = 0;
		foreach ($ids as $bid) {
			if (isset($table[$bid])) {
				$totalChapters += $table[$bid][0];
				$totalVerses   += $table[$bid][1];
			}
		}
		if ($totalVerses <= 0)   return null;
		if ($totalVerses >= 300) return 1;
		if ($totalChapters > 1)  return 2;
		return 3;
	}

	private function versesFromBookIds($bookIdsString)
	{
		$table = $this->silverAppleBookTable();
		$ids = array_filter(array_map('intval', explode(',', (string)$bookIdsString)));
		$totalVerses = 0;
		foreach ($ids as $bookId) {
			if (isset($table[$bookId])) {
				$totalVerses += (int)$table[$bookId][1];
			}
		}
		return $totalVerses;
	}

	private function scriptureTieBucketFromEventName($eventName)
	{
		$name = strtolower(preg_replace('/\s+/', ' ', trim((string)$eventName)));
		if (strpos($name, 'golden harp award') !== false) {
			return 'golden_harp';
		}
		if (strpos($name, 'christian worker award') !== false) {
			return 'christian_worker';
		}
		if (strpos($name, 'golden apple award') !== false) {
			return 'golden_apple';
		}
		if (strpos($name, 'golden lamb award') !== false) {
			return 'golden_lamb';
		}
		if (strpos($name, 'christian soldier award') !== false) {
			return 'christian_soldier';
		}
		if (strpos($name, 'silver apple award') !== false) {
			return 'silver_apple';
		}
		return null;
	}

	private function buildTieBreakerProfiles($conventionSD, $studentIds = [])
	{
		$studentIds = array_values(array_unique(array_map('intval', (array)$studentIds)));
		if (empty($studentIds)) {
			return [];
		}

		$profiles = [];
		foreach ($studentIds as $studentId) {
			$profiles[$studentId] = [
				'scripture' => [
					'golden_harp' => 0,
					'christian_worker' => 0,
					'golden_apple' => 0,
					'golden_lamb' => 0,
					'christian_soldier' => 0,
					'silver_apple_1' => 0,
					'silver_apple_2' => 0,
					'silver_apple_3' => 0,
				],
				'individual' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0],
				'team' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0],
			];
		}

		$scriptureSubs = $this->Eventsubmissions->find()
			->select(['Eventsubmissions.student_id', 'Eventsubmissions.book_ids'])
			->where([
				'Eventsubmissions.conventionseason_id' => $conventionSD->id,
				'Eventsubmissions.convention_id' => $conventionSD->convention_id,
				'Eventsubmissions.season_id' => $conventionSD->season_id,
				'Eventsubmissions.season_year' => $conventionSD->season_year,
				'Eventsubmissions.student_id IN' => $studentIds,
			])
			->contain(['Events' => function($q){
				return $q->select(['Events.id', 'Events.event_name']);
			}])
			->all();

		foreach ($scriptureSubs as $submission) {
			$studentId = (int)$submission->student_id;
			$bucket = $this->scriptureTieBucketFromEventName($submission->Events['event_name'] ?? '');
			if (!$bucket || !isset($profiles[$studentId])) {
				continue;
			}

			$verses = $this->versesFromBookIds((string)($submission->book_ids ?? ''));
			if ($bucket === 'silver_apple') {
				$place = $this->silverAppleCalcPlace((string)($submission->book_ids ?? ''));
				if ($place >= 1 && $place <= 3) {
					$key = 'silver_apple_'.$place;
					$profiles[$studentId]['scripture'][$key] += $verses;
				}
				continue;
			}

			$profiles[$studentId]['scripture'][$bucket] += $verses;
		}

		$resultRows = $this->Resultpositions->find()
			->select([
				'Resultpositions.student_id',
				'Resultpositions.group_name',
				'Resultpositions.conventionregistration_id',
				'Resultpositions.conventionseason_id',
				'Resultpositions.event_id',
				'Resultpositions.position',
			])
			->where([
				'Resultpositions.conventionseason_id' => $conventionSD->id,
				'Resultpositions.convention_id' => $conventionSD->convention_id,
				'Resultpositions.season_id' => $conventionSD->season_id,
				'Resultpositions.season_year' => $conventionSD->season_year,
				'Resultpositions.position >=' => 1,
				'Resultpositions.position <=' => 6,
			])
			->contain(['Events' => function($q){
				return $q->select(['Events.id', 'Events.group_event_yes_no']);
			}])
			->all();

		$studentIdLookup = array_fill_keys($studentIds, true);
		$groupMembersCache = [];

		foreach ($resultRows as $row) {
			$position = (int)$row->position;
			if ($position < 1 || $position > 6) {
				continue;
			}

			$isTeamEvent = ((int)($row->Events['group_event_yes_no'] ?? 0) === 1);
			$studentId = (int)$row->student_id;
			$groupName = trim((string)$row->group_name);

			if (!$isTeamEvent) {
				if ($studentId > 0 && isset($studentIdLookup[$studentId])) {
					$profiles[$studentId]['individual'][$position]++;
				}
				continue;
			}

			if ($groupName === '') {
				if ($studentId > 0 && isset($studentIdLookup[$studentId])) {
					$profiles[$studentId]['team'][$position]++;
				}
				continue;
			}

			$cacheKey = $groupName.'|'.$row->conventionregistration_id.'|'.$row->conventionseason_id.'|'.$row->event_id;
			if (!array_key_exists($cacheKey, $groupMembersCache)) {
				$groupMembers = $this->Crstudentevents->find()
					->select(['Crstudentevents.student_id'])
					->where([
						'Crstudentevents.group_name' => $groupName,
						'Crstudentevents.conventionregistration_id' => $row->conventionregistration_id,
						'Crstudentevents.conventionseason_id' => $row->conventionseason_id,
						'Crstudentevents.event_id' => $row->event_id,
					])
					->enableHydration(false)
					->all();

				$groupMembersCache[$cacheKey] = [];
				foreach ($groupMembers as $groupMember) {
					$memberId = (int)$groupMember['student_id'];
					if ($memberId > 0) {
						$groupMembersCache[$cacheKey][$memberId] = true;
					}
				}
			}

			foreach (array_keys($groupMembersCache[$cacheKey]) as $memberId) {
				if (isset($studentIdLookup[$memberId])) {
					$profiles[$memberId]['team'][$position]++;
				}
			}
		}

		return $profiles;
	}

	private function resolveTiedStudentsByPrecedence($studentIds, $profiles)
	{
		$remaining = array_values(array_unique(array_map('intval', (array)$studentIds)));
		if (count($remaining) <= 1) {
			return $remaining;
		}

		$scriptureOrder = [
			'golden_harp',
			'christian_worker',
			'golden_apple',
			'golden_lamb',
			'christian_soldier',
			'silver_apple_1',
			'silver_apple_2',
			'silver_apple_3',
		];

		foreach ($scriptureOrder as $bucket) {
			$maxValue = null;
			$winners = [];
			foreach ($remaining as $studentId) {
				$value = (int)($profiles[$studentId]['scripture'][$bucket] ?? 0);
				if ($maxValue === null || $value > $maxValue) {
					$maxValue = $value;
					$winners = [$studentId];
				} elseif ($value === $maxValue) {
					$winners[] = $studentId;
				}
			}
			$remaining = $winners;
			if (count($remaining) <= 1) {
				return $remaining;
			}
		}

		for ($position = 1; $position <= 6; $position++) {
			$maxValue = null;
			$winners = [];
			foreach ($remaining as $studentId) {
				$value = (int)($profiles[$studentId]['individual'][$position] ?? 0);
				if ($maxValue === null || $value > $maxValue) {
					$maxValue = $value;
					$winners = [$studentId];
				} elseif ($value === $maxValue) {
					$winners[] = $studentId;
				}
			}
			$remaining = $winners;
			if (count($remaining) <= 1) {
				return $remaining;
			}
		}

		for ($position = 1; $position <= 6; $position++) {
			$maxValue = null;
			$winners = [];
			foreach ($remaining as $studentId) {
				$value = (int)($profiles[$studentId]['team'][$position] ?? 0);
				if ($maxValue === null || $value > $maxValue) {
					$maxValue = $value;
					$winners = [$studentId];
				} elseif ($value === $maxValue) {
					$winners[] = $studentId;
				}
			}
			$remaining = $winners;
			if (count($remaining) <= 1) {
				return $remaining;
			}
		}

		return $remaining;
	}

	private function buildDivisionWinnersData($conventionSD)
	{
		$arrAllResults = [];
		$allResultsConventionSeason = $this->Resultpositions->find()->where([
			'Resultpositions.conventionseason_id' => $conventionSD->id,
			'Resultpositions.convention_id' => $conventionSD->convention_id,
			'Resultpositions.season_id' => $conventionSD->season_id,
			'Resultpositions.season_year' => $conventionSD->season_year,
			'Resultpositions.points_obtained >' => 0,
		])->order(['Resultpositions.id' => 'ASC'])->all();

		if ($allResultsConventionSeason->isEmpty()) {
			return null;
		}

		foreach ($allResultsConventionSeason as $allresultcs)
		{
			$mappedDivisionId = $this->normalizeDivisionIdForManualArts(
				(int)($allresultcs->division_id ?? 0),
				(string)($allresultcs->event_id_number ?? '')
			);

			if($allresultcs->student_id>0)
			{
				$arrAllResults[$mappedDivisionId][$allresultcs->student_id] = ($arrAllResults[$mappedDivisionId][$allresultcs->student_id] ?? 0) + $allresultcs->points_obtained;
			}

			if(!empty($allresultcs->group_name) && $allresultcs->group_name != NULL)
			{
				$groupStudents = $this->Crstudentevents->find()->where([
					'Crstudentevents.group_name' => $allresultcs->group_name,
					'Crstudentevents.conventionregistration_id' => $allresultcs->conventionregistration_id,
					'Crstudentevents.conventionseason_id' => $allresultcs->conventionseason_id,
					'Crstudentevents.event_id' => $allresultcs->event_id,
				])->all();

				foreach($groupStudents as $groupst)
				{
					$arrAllResults[$mappedDivisionId][$groupst->student_id] = ($arrAllResults[$mappedDivisionId][$groupst->student_id] ?? 0) + $allresultcs->points_obtained;
				}
			}
		}

		$singleWinnerDivs = [1, 4, 5, 6, 7, 8, 11];
		$genderSplitDivs  = [2, 3, 9, 10];
		$winnerDivisionIds = array_merge($singleWinnerDivs, $genderSplitDivs);

		$tambourineGroupEventIds = $this->Events->find()
			->select(['id'])
			->where(['Events.division_id' => 8, 'Events.group_event_yes_no' => 1, 'Events.event_name LIKE' => '%Tambourine%'])
			->enableHydration(false)->extract('id')->toList();

		$studentSoloDivs = [];
		$instrumentalEventSets = [];
		$instrumentalAllTambourineGroup = [];

		$allSubs = $this->Eventsubmissions->find()
			->select(['Eventsubmissions.student_id','Eventsubmissions.event_id'])
			->where([
				'Eventsubmissions.conventionseason_id' => $conventionSD->id,
				'Eventsubmissions.convention_id'       => $conventionSD->convention_id,
				'Eventsubmissions.student_id >'        => 0,
			])
			->contain(['Events' => function($q){ return $q->select(['id','division_id','group_event_yes_no']); }])
			->enableHydration(false)->all();

		foreach ($allSubs as $sub) {
			$sid   = (int)$sub['student_id'];
			$divId = (int)($sub['Events']['division_id'] ?? 0);
			$isGrp = (int)($sub['Events']['group_event_yes_no'] ?? 1);
			$eid   = (int)$sub['event_id'];
			if (!$isGrp) {
				$studentSoloDivs[$sid][$divId] = true;
			}
			if ($divId === 8) {
				$instrumentalEventSets[$sid][] = $eid;
			}
		}

		foreach ($instrumentalEventSets as $sid => $eids) {
			$nonTambourineGroup = array_filter($eids, function($eid) use ($tambourineGroupEventIds) {
				return !in_array($eid, $tambourineGroupEventIds);
			});
			if (empty($nonTambourineGroup) && empty($studentSoloDivs[$sid][8])) {
				$instrumentalAllTambourineGroup[$sid] = true;
			}
		}

		$allStudentIds = [];
		foreach ($arrAllResults as $divId => $stuMap) {
			foreach ($stuMap as $sid => $pts) {
				$allStudentIds[] = $sid;
			}
		}
		$allStudentIds = array_values(array_unique($allStudentIds));

		$studentGenders = [];
		if (!empty($allStudentIds)) {
			$genderRows = $this->Users->find()->select(['id','gender'])->where(['Users.id IN' => $allStudentIds])->enableHydration(false)->all();
			foreach ($genderRows as $gr) {
				$studentGenders[(int)$gr['id']] = $gr['gender'] ?? '';
			}
		}

		$trophyWinners = [];
		foreach ($arrAllResults as $divId => $stuMap) {
			if (!in_array((int)$divId, $winnerDivisionIds)) {
				continue;
			}

			$eligible = [];
			foreach ($stuMap as $sid => $pts) {
				if ($pts < 24) {
					continue;
				}
				if (empty($studentSoloDivs[$sid][$divId])) {
					continue;
				}
				if ($divId === 8 && !empty($instrumentalAllTambourineGroup[$sid])) {
					continue;
				}
				$eligible[$sid] = $pts;
			}
			if (empty($eligible)) {
				continue;
			}
			arsort($eligible);

			if (in_array($divId, $genderSplitDivs)) {
				$byGender = ['Male' => [], 'Female' => []];
				foreach ($eligible as $sid => $pts) {
					$g = $studentGenders[$sid] ?? '';
					if ($g === 'Male' || $g === 'Female') {
						$byGender[$g][$sid] = $pts;
					}
				}
				$trophyWinners[$divId] = [];
				foreach (['Male','Female'] as $g) {
					if (!empty($byGender[$g])) {
						$maxPts = max($byGender[$g]);
						$trophyWinners[$divId][$g] = [
							'students' => array_keys(array_filter($byGender[$g], function($p) use ($maxPts){ return $p === $maxPts; })),
							'points'   => $maxPts
						];
					}
				}
			} else {
				$maxPts = max($eligible);
				$trophyWinners[$divId] = [
					'students' => array_keys(array_filter($eligible, function($p) use ($maxPts){ return $p === $maxPts; })),
					'points'   => $maxPts
				];
			}
		}

		$tiedStudentIds = [];
		foreach ($trophyWinners as $divisionId => $winnerData) {
			if (in_array($divisionId, $genderSplitDivs)) {
				foreach (['Male', 'Female'] as $gender) {
					if (!empty($winnerData[$gender]['students']) && count($winnerData[$gender]['students']) > 1) {
						$tiedStudentIds = array_merge($tiedStudentIds, $winnerData[$gender]['students']);
					}
				}
				continue;
			}

			if (!empty($winnerData['students']) && count($winnerData['students']) > 1) {
				$tiedStudentIds = array_merge($tiedStudentIds, $winnerData['students']);
			}
		}

		$tiedStudentIds = array_values(array_unique(array_map('intval', $tiedStudentIds)));
		if (!empty($tiedStudentIds)) {
			$tieProfiles = $this->buildTieBreakerProfiles($conventionSD, $tiedStudentIds);
			foreach ($trophyWinners as $divisionId => $winnerData) {
				if (in_array($divisionId, $genderSplitDivs)) {
					foreach (['Male', 'Female'] as $gender) {
						if (!empty($winnerData[$gender]['students']) && count($winnerData[$gender]['students']) > 1) {
							$trophyWinners[$divisionId][$gender]['students'] = $this->resolveTiedStudentsByPrecedence($winnerData[$gender]['students'], $tieProfiles);
						}
					}
					continue;
				}

				if (!empty($winnerData['students']) && count($winnerData['students']) > 1) {
					$trophyWinners[$divisionId]['students'] = $this->resolveTiedStudentsByPrecedence($winnerData['students'], $tieProfiles);
				}
			}
		}

		return [
			'arrAllResults' => $arrAllResults,
			'trophyWinners' => $trophyWinners,
			'genderSplitDivs' => $genderSplitDivs,
		];
	}

	private function buildEventsPlacedByStudent($conventionSD, array $studentFilterIds = [])
	{
		$eventsPlacedByStudent = [];
		$eventNames = [];
		$divisionNames = [];

		$filterLookup = [];
		if (!empty($studentFilterIds)) {
			$studentFilterIds = array_values(array_unique(array_map('intval', $studentFilterIds)));
			$filterLookup = array_fill_keys($studentFilterIds, true);
		}

		$allEvents = $this->Events->find()
			->select(['Events.id', 'Events.event_name'])
			->enableHydration(false)
			->all();
		foreach ($allEvents as $eventRow) {
			$eventNames[(int)$eventRow['id']] = (string)($eventRow['event_name'] ?? 'Unknown Event');
		}

		$allDivisions = $this->Divisions->find()
			->select(['Divisions.id', 'Divisions.name', 'Divisions.parent_division_id'])
			->enableHydration(false)
			->all();
		$divisionNameRaw = [];
		$divisionParentMap = [];
		foreach ($allDivisions as $divisionRow) {
			$divisionId = (int)$divisionRow['id'];
			$divisionNameRaw[$divisionId] = (string)($divisionRow['name'] ?? 'Other');
			$divisionParentMap[$divisionId] = (int)($divisionRow['parent_division_id'] ?? 0);
		}
		foreach ($divisionNameRaw as $divisionId => $divisionName) {
			$parentId = (int)($divisionParentMap[$divisionId] ?? 0);
			if ($parentId > 0 && !empty($divisionNameRaw[$parentId])) {
				$divisionNames[$divisionId] = $divisionNameRaw[$parentId];
				continue;
			}
			$divisionNames[$divisionId] = $divisionName;
		}

		$allResultsConventionSeason = $this->Resultpositions->find()->where([
			'Resultpositions.conventionseason_id' => $conventionSD->id,
			'Resultpositions.convention_id' => $conventionSD->convention_id,
			'Resultpositions.season_id' => $conventionSD->season_id,
			'Resultpositions.season_year' => $conventionSD->season_year,
			'Resultpositions.points_obtained >' => 0,
		])->order(['Resultpositions.points_obtained' => 'ASC'])->all();

		if ($allResultsConventionSeason->isEmpty()) {
			return $eventsPlacedByStudent;
		}

		foreach ($allResultsConventionSeason as $allresultcs) {
			$eventId = (int)($allresultcs->event_id ?? 0);
			$divisionId = $this->normalizeDivisionIdForManualArts(
				(int)($allresultcs->division_id ?? 0),
				(string)($allresultcs->event_id_number ?? '')
			);
			$position = (int)($allresultcs->position ?? 0);
			$eventName = $eventNames[$eventId] ?? 'Unknown Event';
			$categoryName = $divisionNames[$divisionId] ?? 'Other';

			if ($position <= 0) {
				continue;
			}

			if ($allresultcs->student_id > 0) {
				$studentId = (int)$allresultcs->student_id;
				if (empty($filterLookup) || !empty($filterLookup[$studentId])) {
					$eventsPlacedByStudent[$studentId][] = [
						'division_id' => $divisionId,
						'category' => $categoryName,
						'event_name' => $eventName,
						'position' => $position,
						'is_team' => false,
					];
				}
			}

			if (!empty($allresultcs->group_name) && $allresultcs->group_name != null) {
				$groupStudents = $this->Crstudentevents->find()->where([
					'Crstudentevents.group_name' => $allresultcs->group_name,
					'Crstudentevents.conventionregistration_id' => $allresultcs->conventionregistration_id,
					'Crstudentevents.conventionseason_id' => $allresultcs->conventionseason_id,
					'Crstudentevents.event_id' => $allresultcs->event_id,
				])->all();

				foreach ($groupStudents as $groupst) {
					$studentId = (int)$groupst->student_id;
					if (!empty($filterLookup) && empty($filterLookup[$studentId])) {
						continue;
					}
					$eventsPlacedByStudent[$studentId][] = [
						'division_id' => $divisionId,
						'category' => $categoryName,
						'event_name' => $eventName,
						'position' => $position,
						'is_team' => true,
					];
				}
			}
		}

		foreach ($eventsPlacedByStudent as $studentId => $placements) {
			$seen = [];
			$unique = [];

			foreach ($placements as $placement) {
				$key = $placement['category'].'|'.$placement['event_name'].'|'.$placement['position'].'|'.(int)$placement['is_team'];
				if (isset($seen[$key])) {
					continue;
				}
				$seen[$key] = true;
				$unique[] = $placement;
			}

			usort($unique, function ($a, $b) {
				$categoryCompare = strcasecmp((string)$a['category'], (string)$b['category']);
				if ($categoryCompare !== 0) {
					return $categoryCompare;
				}

				if ((int)$a['position'] === (int)$b['position']) {
					return strcasecmp((string)$a['event_name'], (string)$b['event_name']);
				}
				return (int)$a['position'] <=> (int)$b['position'];
			});

			$eventsPlacedByStudent[$studentId] = $unique;
		}

		return $eventsPlacedByStudent;
	}

	private function normalizeDivisionIdForManualArts($divisionId, $eventIdNumber)
	{
		$manualArtsEventNumbers = ['005', '009', '055', '056', '057', '058', '059', '060'];
		$eventIdNumber = str_pad(trim((string)$eventIdNumber), 3, '0', STR_PAD_LEFT);

		if (in_array($eventIdNumber, $manualArtsEventNumbers, true)) {
			return $this->getManualArtsDivisionId();
		}

		return (int)$divisionId;
	}

	private function getManualArtsDivisionId()
	{
		static $manualArtsDivisionId = null;

		if ($manualArtsDivisionId !== null) {
			return (int)$manualArtsDivisionId;
		}

		$manualArts = $this->Divisions->find()
			->select(['Divisions.id'])
			->where(['Divisions.name' => 'Manual Arts'])
			->first();

		$manualArtsDivisionId = $manualArts ? (int)$manualArts->id : 4;
		return (int)$manualArtsDivisionId;
	}

	private function saveSilverAppleResultPositions($conventionSD, $eventD, $result_id)
	{
		$pointsMap   = [1 => 12, 2 => 10, 3 => 8];
		$submissions = $this->Eventsubmissions->find()
			->where([
				'Eventsubmissions.conventionseason_id' => $conventionSD->id,
				'Eventsubmissions.convention_id'       => $conventionSD->convention_id,
				'Eventsubmissions.season_id'           => $conventionSD->season_id,
				'Eventsubmissions.season_year'         => $conventionSD->season_year,
				'Eventsubmissions.event_id'            => $eventD->id,
			])
			->contain(['Students'])
			->all();

		$savedCount = 0;
		foreach ($submissions as $datarecord) {
			$bookIds = trim((string)($datarecord->book_ids ?? ''));
			$place   = $bookIds ? $this->silverAppleCalcPlace($bookIds) : null;
			if (!$place) continue;

			$dataRP = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($dataRP, []);
			$dataRP->slug                    = 'result-positions-'.$result_id.'-'.$conventionSD->id.'-'.time().'-'.rand(100,1000000);
			$dataRP->result_id               = $result_id;
			$dataRP->eventsubmission_id      = $datarecord->id;
			$dataRP->conventionregistration_id = $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id     = $datarecord->conventionseason_id;
			$dataRP->convention_id           = $datarecord->convention_id;
			$dataRP->user_id                 = $datarecord->user_id;
			$dataRP->season_id               = $datarecord->season_id;
			$dataRP->season_year             = $datarecord->season_year;
			$dataRP->event_id                = $eventD->id;
			$dataRP->event_id_number         = $eventD->event_id_number;
			$dataRP->division_id             = $eventD->division_id;
			$dataRP->group_name              = $datarecord->group_name;
			$dataRP->student_id              = $datarecord->student_id;
			$dataRP->gender                  = $datarecord->Students['gender'] ?? null;
			$dataRP->avg_marks               = null;
			$dataRP->position                = $place;
			$dataRP->points_obtained         = $pointsMap[$place];
			$dataRP->created                 = date('Y-m-d H:i:s');
			$dataRP->modified                = date('Y-m-d H:i:s');
			if ($this->Resultpositions->save($dataRP)) $savedCount++;
		}
		return $savedCount;
	}

    public function index($slug_convention_season = null,$slug_convention = null,$slug_event = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$conventionSD = null;
		$conventionD = null;
		$eventD = null;
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		
		global $resultPoints;
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if (empty($slug_event))
		{
			$this->Flash->error('Event not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		}

		$eventD = $this->Events->find()->where(['Events.slug' => $slug_event])->first();
		$this->set('eventD', $eventD);

		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		}
		
		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);
		
		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'DESC'])->all();
			foreach($resultsPos as $resultp)
			{
				$arrAlreadySavedResults[$resultp->eventsubmission_id]['position'] 				= $resultp->position;
				$arrAlreadySavedResults[$resultp->eventsubmission_id]['avg_marks'] 				= $resultp->avg_marks;
				$arrAlreadySavedResults[$resultp->eventsubmission_id]['points_obtained'] 		= $resultp->points_obtained;
			}
		}
		$this->set('checkResultsAlready',$checkResultsAlready);
		$this->set('arrAlreadySavedResults',$arrAlreadySavedResults);
		
		// to get total number of entries for this event in this conv season
		$eventSubmissionsCS 		= $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionseason_id' => $conventionSD->id,'Eventsubmissions.convention_id' => $conventionSD->convention_id,'Eventsubmissions.season_id' => $conventionSD->season_id,'Eventsubmissions.season_year' => $conventionSD->season_year,'Eventsubmissions.event_id' => $eventD->id])->contain(['Users','Students'])->all();
		$this->set('eventSubmissionsCS', $eventSubmissionsCS);
		
		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());
			
			$postData = $this->request->getData();
			
			//$this->prx($postData);
			
			$existingPositionsBySubmission = [];
			if ($checkResultsAlready) {
				$existingResultPositions = $this->Resultpositions->find()
					->where(["Resultpositions.result_id" => $checkResultsAlready->id])
					->all();
				foreach ($existingResultPositions as $existingResultPosition) {
					$existingPositionsBySubmission[(int)$existingResultPosition->eventsubmission_id] = $existingResultPosition;
				}
			}

			if($checkResultsAlready)
			{
				$result_id = $checkResultsAlready->id;
				$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			}
			else
			{
				$results 	= $this->Results->newEntity([]);
				$dataR 		= $this->Results->patchEntity($results, array());

				$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
				$dataR->conventionseason_id 		= $conventionSD->id;
				$dataR->convention_id 				= $conventionSD->convention_id;
				$dataR->season_id 					= $conventionSD->season_id;
				$dataR->season_year 				= $conventionSD->season_year;
				$dataR->event_id 					= $eventD->id;
				$dataR->event_id_number 			= $eventD->event_id_number;
				$dataR->division_id 				= $eventD->division_id;
				$dataR->created 					= $conventionSD->created;

				$resultR = $this->Results->save($dataR);
				if (!$resultR) {
					$this->Flash->error('Results header could not be saved.');
					return;
				}
				$result_id = $resultR->id;
			}

			$saveFailed = false;
			$failedSubmissionIds = [];
			foreach($eventSubmissionsCS as $datarecord)
			{
				$positionSubRaw = $postData['result_position_'.$datarecord->id] ?? null;
				$avgMarksSubRaw = $postData['result_avg_marks_'.$datarecord->id] ?? null;
				$positionSub = ($positionSubRaw !== null && $positionSubRaw !== '' && is_numeric($positionSubRaw)) ? (int)$positionSubRaw : null;
				$avgMarksSub = ($avgMarksSubRaw !== null && $avgMarksSubRaw !== '' && is_numeric($avgMarksSubRaw)) ? (float)$avgMarksSubRaw : null;

				if($positionSub !== null && $positionSub>=1 && $positionSub<=6)
				{
					$points_obtained = $resultPoints[$positionSub];
				}
				else
				{
					$points_obtained = 0;
				}

				if (isset($existingPositionsBySubmission[(int)$datarecord->id])) {
					$dataRP = $existingPositionsBySubmission[(int)$datarecord->id];
				} else {
					$resultpositions = $this->Resultpositions->newEntity([]);
					$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());
					$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
					$dataRP->result_id						= $result_id;
					$dataRP->eventsubmission_id					= $datarecord->id;
					$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
					$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
					$dataRP->convention_id					= $datarecord->convention_id;
					$dataRP->user_id						= $datarecord->user_id;
					$dataRP->season_id						= $datarecord->season_id;
					$dataRP->season_year					= $datarecord->season_year;
					$dataRP->event_id						= $eventD->id;
					$dataRP->event_id_number				= $eventD->event_id_number;
					$dataRP->division_id					= $eventD->division_id;
					$dataRP->group_name					= $datarecord->group_name;
					$dataRP->student_id					= $datarecord->student_id;
					$dataRP->gender						= $datarecord->Students['gender'] ?? null;
					$dataRP->created 						= date('Y-m-d H:i:s');
				}

				$dataRP->position						= $positionSub;
				$dataRP->avg_marks						= $avgMarksSub;
				$dataRP->points_obtained					= $points_obtained;
				$dataRP->modified 						= date('Y-m-d H:i:s');

				$resultRP = $this->Resultpositions->save($dataRP);
				if (!$resultRP) {
					$saveFailed = true;
					$failedSubmissionIds[] = (int)$datarecord->id;
				}
			}

			if ($saveFailed) {
				$this->Flash->error('Results could not be saved for submission IDs: '.implode(', ', $failedSubmissionIds));
				return;
			}

			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);

			$this->Flash->success('Results saved sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
			
		}
    }
	
	public function closejudging($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		global $resultPoints;
		
		
		
		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}
		
		
		
		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity([]);
		$dataR = $this->Results->patchEntity($results, array());
		$lastResultRow = $this->Results->find()->select(['id'])->order(['id' => 'DESC'])->first();
		$nextResultId = $lastResultRow ? ((int)$lastResultRow->id + 1) : 1;

		$dataR->id 						= $nextResultId;
		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;

		if ($this->isAutoAwardEvent($eventD))
		{
			$autoSavedCount = $this->saveAutoAwardResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully. Auto award rule applied to '.$autoSavedCount.' submission'.($autoSavedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		if ($this->isSilverAppleEvent($eventD))
		{
			$savedCount = $this->saveSilverAppleResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Silver Apple judging closed. Place and points calculated for '.$savedCount.' submission'.($savedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		
		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE
		$lastResultPositionRow = $this->Resultpositions->find()->select(['id'])->order(['id' => 'DESC'])->first();
		$nextResultPositionId = $lastResultPositionRow ? ((int)$lastResultPositionRow->id + 1) : 1;
		
		$eventSubmissionsCS 		= $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionseason_id' => $conventionSD->id,'Eventsubmissions.convention_id' => $conventionSD->convention_id,'Eventsubmissions.season_id' => $conventionSD->season_id,'Eventsubmissions.season_year' => $conventionSD->season_year,'Eventsubmissions.event_id' => $eventD->id])->contain(['Users','Students'])->all();
		
		foreach($eventSubmissionsCS as $datarecord)
		{
			// check how many judges judged this entry and get average
			$condAvg = array();
			$condAvg[] 	= "(Judgeevaluations.eventsubmission_id = '".$datarecord->id."')";
			$condAvg[] 	= "(Judgeevaluations.convention_id = '".$datarecord->convention_id."')";
			$condAvg[] 	= "(Judgeevaluations.season_id = '".$datarecord->season_id."')";
			$condAvg[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
			$judgeEvals = $this->Judgeevaluations->find()->where($condAvg)->all();
			
			$marksObtained = 0;
			$cntrJudging = 0;
			foreach($judgeEvals as $judgeeval)
			{
				$marksObtained = $marksObtained+$judgeeval->total_marks_obtained;
				$cntrJudging++;
			}
			
			if($cntrJudging>0)
			{
				$avgMarksSub = $marksObtained/$cntrJudging;
			}
			else
			{
				$avgMarksSub = 0;
			}
			
			// enter record
			$resultpositions = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->id 								= $nextResultPositionId;
			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			
			$dataRP->avg_marks							= $avgMarksSub;
			
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');
			
			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);
			$nextResultPositionId++;
		}
		
		
		
		// STEP 4 :: Create an array from resultpositions
		$resultPA = $this->Resultpositions->find()->where(['Resultpositions.result_id' => $result_id])->all();
		$records = array();
		foreach($resultPA as $resultpos)
		{
			$records[] = array("resultp_auto_id" => $resultpos->id,"avg_marks" => $resultpos->avg_marks);
		}
		
		
		
		// STEP 5 :: Sort this array from highest to lowest based on avg marks
		$key_values = array_column($records, 'avg_marks');
		array_multisort($key_values, SORT_DESC, $records);
		
		
		
		
		// STEP 6 :: Assign positions based on average marks
		$maxPoints = $records['0']['avg_marks'];
		if ($maxPoints < 80)
		{
			$positions = range(3, 6);
		}
		elseif ($maxPoints < 90)
		{
			$positions = range(2, 6);
		}
		else
		{
			$positions = range(1, 6);
		}

		$lastMark = 0;
		$lastPos = 0;
		foreach ($records as $i => $array)
		{
		  if (empty($positions) || $array['avg_marks'] < 70)
		  {
			$pos = null;
		  }
		  else
		  {
			$pos = $lastMark == $array['avg_marks'] ? $lastPos : array_shift($positions);
		  }

		  $lastMark 				= $array['avg_marks'];
		  $lastPos 					= $pos;
		  $records[$i]['position'] 	= $pos;
		}
		
		
		//$this->prx($records);
		
		
		
		// STEP 7 :: Update these positions in system
		foreach($records as $sortedArr)
		{
			// allocate points
			$positionSub = $sortedArr['position'];
			
			// to check points_obtained
			if($positionSub>=1 && $positionSub<=6)	
			{
				$points_obtained = $resultPoints[$positionSub];
			}
			else
			{
				$points_obtained = 0;
			}
			
			$this->Resultpositions->updateAll(['position' => $sortedArr['position'],'points_obtained' => $points_obtained], ["id" => $sortedArr['resultp_auto_id'],"result_id" => $result_id]);
		}
		
		
		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
		
		
		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
		
		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }

	public function openjudging($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		$conventionSD = null;
		$conventionD = null;
		$eventD = null;

		if ($slug_convention_season) {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		}
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
		}
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_event) {
			$eventD = $this->Events->find()->where(['Events.slug' => $slug_event])->first();
		}
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		$this->Conventionseasonevents->updateAll(
			['judging_ends' => '0'],
			['conventionseasons_id' => $conventionSD->id, 'event_id' => $eventD->id]
		);

		$this->Flash->success('Judging for the event has been reopened successfully.');
		return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
	}
	
	public function points($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$slug_event = null;
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Points > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);
		
		// to get all Resultpositions of this convention season
		$arrAllResults = array();
		$allResultsConventionSeason 		= $this->Resultpositions->find()->where(['Resultpositions.conventionseason_id' => $conventionSD->id,'Resultpositions.convention_id' => $conventionSD->convention_id,'Resultpositions.season_id' => $conventionSD->season_id,'Resultpositions.season_year' => $conventionSD->season_year,'Resultpositions.points_obtained >' => 0])->order(['Resultpositions.id' => 'ASC'])->all();
		if(!$allResultsConventionSeason->isEmpty())
		{
			//$this->prx($allResultsConventionSeason);
			
			foreach($allResultsConventionSeason as $allresultcs)
			{
				$mappedDivisionId = $this->normalizeDivisionIdForManualArts(
					(int)($allresultcs->division_id ?? 0),
					(string)($allresultcs->event_id_number ?? '')
				);

				// There are two conditions
				
				// 1. if its individual student
				if($allresultcs->student_id>0)
				{
					$arrAllResults[$mappedDivisionId][$allresultcs->student_id] = ($arrAllResults[$mappedDivisionId][$allresultcs->student_id] ?? 0) + $allresultcs->points_obtained;
				}
				
				// 2. if its a group
				if(!empty($allresultcs->group_name) && $allresultcs->group_name != NULL)
				{
					//$this->prx($allresultcs);
					
					// now fetch all students of this group
					$groupStudents = $this->Crstudentevents->find()->where(['Crstudentevents.group_name' => $allresultcs->group_name,'Crstudentevents.conventionregistration_id' => $allresultcs->conventionregistration_id,'Crstudentevents.conventionseason_id' => $allresultcs->conventionseason_id,'Crstudentevents.event_id' => $allresultcs->event_id])->all();
					foreach($groupStudents as $groupst)
					{
						//$this->prx($groupst);
						$arrAllResults[$mappedDivisionId][$groupst->student_id] = ($arrAllResults[$mappedDivisionId][$groupst->student_id] ?? 0) + $allresultcs->points_obtained;
						//echo $groupst->student_id;echo '<br>';exit;
					}
				}
			}
			
			$this->set('arrAllResults', $arrAllResults);
			
			//$this->prx($arrAllResults);

			// ── Divisional Trophy Eligibility ────────────────────────────────────────
			$singleWinnerDivs = [1, 4, 5, 6, 7, 8, 11];
			$genderSplitDivs  = [2, 3, 9, 10];
			$winnerDivisionIds = array_merge($singleWinnerDivs, $genderSplitDivs);

			$tambourineGroupEventIds = $this->Events->find()
				->select(['id'])
				->where(['Events.division_id' => 8, 'Events.group_event_yes_no' => 1, 'Events.event_name LIKE' => '%Tambourine%'])
				->enableHydration(false)->extract('id')->toList();

			$studentSoloDivs = [];
			$instrumentalEventSets = [];
			$instrumentalAllTambourineGroup = [];

			$allSubs = $this->Eventsubmissions->find()
				->select(['Eventsubmissions.student_id','Eventsubmissions.event_id'])
				->where([
					'Eventsubmissions.conventionseason_id' => $conventionSD->id,
					'Eventsubmissions.convention_id'       => $conventionSD->convention_id,
					'Eventsubmissions.student_id >'        => 0,
				])
				->contain(['Events' => function($q){ return $q->select(['id','division_id','group_event_yes_no']); }])
				->enableHydration(false)->all();

			foreach ($allSubs as $sub) {
				$sid   = (int)$sub['student_id'];
				$divId = (int)($sub['Events']['division_id'] ?? 0);
				$isGrp = (int)($sub['Events']['group_event_yes_no'] ?? 1);
				$eid   = (int)$sub['event_id'];
				if (!$isGrp) $studentSoloDivs[$sid][$divId] = true;
				if ($divId === 8) $instrumentalEventSets[$sid][] = $eid;
			}

			foreach ($instrumentalEventSets as $sid => $eids) {
				$nonTambourineGroup = array_filter($eids, function($eid) use ($tambourineGroupEventIds) {
					return !in_array($eid, $tambourineGroupEventIds);
				});
				if (empty($nonTambourineGroup) && empty($studentSoloDivs[$sid][8])) {
					$instrumentalAllTambourineGroup[$sid] = true;
				}
			}

			$allStudentIds = [];
			foreach ($arrAllResults as $divId => $stuMap) {
				foreach ($stuMap as $sid => $pts) { $allStudentIds[] = $sid; }
			}
			$allStudentIds = array_values(array_unique($allStudentIds));
			$studentGenders = [];
			if (!empty($allStudentIds)) {
				$genderRows = $this->Users->find()->select(['id','gender'])->where(['Users.id IN' => $allStudentIds])->enableHydration(false)->all();
				foreach ($genderRows as $gr) { $studentGenders[(int)$gr['id']] = $gr['gender'] ?? ''; }
			}

			$trophyWinners = [];
			foreach ($arrAllResults as $divId => $stuMap) {
				if (!in_array((int)$divId, $winnerDivisionIds)) {
					continue;
				}

				$eligible = [];
				foreach ($stuMap as $sid => $pts) {
					if ($pts < 24) continue;
					if (empty($studentSoloDivs[$sid][$divId])) continue;
					if ($divId === 8 && !empty($instrumentalAllTambourineGroup[$sid])) continue;
					$eligible[$sid] = $pts;
				}
				if (empty($eligible)) continue;
				arsort($eligible);

				if (in_array($divId, $genderSplitDivs)) {
					$byGender = ['Male' => [], 'Female' => []];
					foreach ($eligible as $sid => $pts) {
						$g = $studentGenders[$sid] ?? '';
						if ($g === 'Male' || $g === 'Female') $byGender[$g][$sid] = $pts;
					}
					$trophyWinners[$divId] = [];
					foreach (['Male','Female'] as $g) {
						if (!empty($byGender[$g])) {
							$maxPts = max($byGender[$g]);
							$trophyWinners[$divId][$g] = [
								'students' => array_keys(array_filter($byGender[$g], function($p) use ($maxPts){ return $p === $maxPts; })),
								'points'   => $maxPts
							];
						}
					}
				} else {
					$maxPts = max($eligible);
					$trophyWinners[$divId] = [
						'students' => array_keys(array_filter($eligible, function($p) use ($maxPts){ return $p === $maxPts; })),
						'points'   => $maxPts
					];
				}
			}

			$this->set('trophyWinners', $trophyWinners);
			$this->set('studentGenders', $studentGenders);
			$this->set('genderSplitDivs', $genderSplitDivs);

			// Apply tie-break procedures in strict precedence order.
			$tiedStudentIds = [];
			foreach ($trophyWinners as $divisionId => $winnerData) {
				if (in_array($divisionId, $genderSplitDivs)) {
					foreach (['Male', 'Female'] as $gender) {
						if (!empty($winnerData[$gender]['students']) && count($winnerData[$gender]['students']) > 1) {
							$tiedStudentIds = array_merge($tiedStudentIds, $winnerData[$gender]['students']);
						}
					}
					continue;
				}
				if (!empty($winnerData['students']) && count($winnerData['students']) > 1) {
					$tiedStudentIds = array_merge($tiedStudentIds, $winnerData['students']);
				}
			}

			$tiedStudentIds = array_values(array_unique(array_map('intval', $tiedStudentIds)));
			if (!empty($tiedStudentIds)) {
				$tieProfiles = $this->buildTieBreakerProfiles($conventionSD, $tiedStudentIds);
				foreach ($trophyWinners as $divisionId => $winnerData) {
					if (in_array($divisionId, $genderSplitDivs)) {
						foreach (['Male', 'Female'] as $gender) {
							if (!empty($winnerData[$gender]['students']) && count($winnerData[$gender]['students']) > 1) {
								$trophyWinners[$divisionId][$gender]['students'] = $this->resolveTiedStudentsByPrecedence($winnerData[$gender]['students'], $tieProfiles);
							}
						}
						continue;
					}

					if (!empty($winnerData['students']) && count($winnerData['students']) > 1) {
						$trophyWinners[$divisionId]['students'] = $this->resolveTiedStudentsByPrecedence($winnerData['students'], $tieProfiles);
					}
				}
			}

			$this->set('trophyWinners', $trophyWinners);
			// ─────────────────────────────────────────────────────────────────────────
			
		}
		else
		{
			$this->Flash->error('Results not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		}
		
		//$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		        
    }
	
	public function overallpoints($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$slug_event = null;
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Points > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);
		
		// to get all Resultpositions of this convention season
		$arrAllResults = array();
		$eventsPlacedByStudent = [];
		$eventNames = [];
		$divisionNames = [];
		$allEvents = $this->Events->find()
			->select(['Events.id', 'Events.event_name'])
			->enableHydration(false)
			->all();
		foreach ($allEvents as $eventRow) {
			$eventNames[(int)$eventRow['id']] = (string)($eventRow['event_name'] ?? 'Unknown Event');
		}
		$allDivisions = $this->Divisions->find()
			->select(['Divisions.id', 'Divisions.name', 'Divisions.parent_division_id'])
			->enableHydration(false)
			->all();
		$divisionNameRaw = [];
		$divisionParentMap = [];
		foreach ($allDivisions as $divisionRow) {
			$divisionId = (int)$divisionRow['id'];
			$divisionNameRaw[$divisionId] = (string)($divisionRow['name'] ?? 'Other');
			$divisionParentMap[$divisionId] = (int)($divisionRow['parent_division_id'] ?? 0);
		}
		foreach ($divisionNameRaw as $divisionId => $divisionName) {
			$parentId = (int)($divisionParentMap[$divisionId] ?? 0);
			if ($parentId > 0 && !empty($divisionNameRaw[$parentId])) {
				$divisionNames[$divisionId] = $divisionNameRaw[$parentId];
				continue;
			}
			$divisionNames[$divisionId] = $divisionName;
		}
		$allResultsConventionSeason 		= $this->Resultpositions->find()->where(['Resultpositions.conventionseason_id' => $conventionSD->id,'Resultpositions.convention_id' => $conventionSD->convention_id,'Resultpositions.season_id' => $conventionSD->season_id,'Resultpositions.season_year' => $conventionSD->season_year,'Resultpositions.points_obtained >' => 0])->order(['Resultpositions.points_obtained' => 'ASC'])->all();
		if(!$allResultsConventionSeason->isEmpty())
		{
			//$this->prx($allResultsConventionSeason);
			
			
			foreach($allResultsConventionSeason as $allresultcs)
			{
				$eventId = (int)($allresultcs->event_id ?? 0);
				$divisionId = $this->normalizeDivisionIdForManualArts(
					(int)($allresultcs->division_id ?? 0),
					(string)($allresultcs->event_id_number ?? '')
				);
				$position = (int)($allresultcs->position ?? 0);
				$eventName = $eventNames[$eventId] ?? 'Unknown Event';
				$categoryName = $divisionNames[$divisionId] ?? 'Other';

				// There are two conditions
				
				// 1. if its individual student
				if($allresultcs->student_id>0)
				{
					$arrAllResults[$allresultcs->student_id] = ($arrAllResults[$allresultcs->student_id] ?? 0) + $allresultcs->points_obtained;
					if ($position > 0) {
						$eventsPlacedByStudent[$allresultcs->student_id][] = [
							'category' => $categoryName,
							'event_name' => $eventName,
							'position' => $position,
							'is_team' => false,
						];
					}
				}
				
				if(!empty($allresultcs->group_name) && $allresultcs->group_name != NULL)
				{
					//$this->prx($allresultcs);
					
					// now fetch all students of this group
					$groupStudents = $this->Crstudentevents->find()->where(['Crstudentevents.group_name' => $allresultcs->group_name,'Crstudentevents.conventionregistration_id' => $allresultcs->conventionregistration_id,'Crstudentevents.conventionseason_id' => $allresultcs->conventionseason_id,'Crstudentevents.event_id' => $allresultcs->event_id])->all();
					foreach($groupStudents as $groupst)
					{
						$arrAllResults[$groupst->student_id] = ($arrAllResults[$groupst->student_id] ?? 0) + $allresultcs->points_obtained;
						if ($position > 0) {
							$eventsPlacedByStudent[$groupst->student_id][] = [
								'category' => $categoryName,
								'event_name' => $eventName,
								'position' => $position,
								'is_team' => true,
							];
						}
					}
				}
			}

			foreach ($eventsPlacedByStudent as $studentId => $placements) {
				$seen = [];
				$unique = [];
				foreach ($placements as $placement) {
					$key = $placement['category'].'|'.$placement['event_name'].'|'.$placement['position'].'|'.(int)$placement['is_team'];
					if (isset($seen[$key])) {
						continue;
					}
					$seen[$key] = true;
					$unique[] = $placement;
				}

				usort($unique, function ($a, $b) {
					$categoryCompare = strcasecmp((string)$a['category'], (string)$b['category']);
					if ($categoryCompare !== 0) {
						return $categoryCompare;
					}

					if ((int)$a['position'] === (int)$b['position']) {
						return strcasecmp((string)$a['event_name'], (string)$b['event_name']);
					}
					return (int)$a['position'] <=> (int)$b['position'];
				});

				$eventsPlacedByStudent[$studentId] = $unique;
			}
			
			$this->set('arrAllResults', $arrAllResults);
			$this->set('eventsPlacedByStudent', $eventsPlacedByStudent);
			
			//$this->prx($arrAllResults);
			
			if (!empty($arrAllResults)) {
				// Step 1: Find max value
				$maxValue = max($arrAllResults);

				// Step 2: Find all keys with that max value
				$maxKeys = array_keys($arrAllResults, $maxValue);
				$this->set('maxKeys', $maxKeys);
			} else {
				$this->set('maxKeys', []);
			}

			// ── 24/7 Award Eligibility ───────────────────────────────────────────────
			$academicDivId  = 1;
			$physEdDivIds   = [2, 3];
			$musicDivIds    = [7, 8, 9];
			$platformDivId  = 10;
			$exhibitsDivIds = [4, 5, 6];

			// Fetch all 1st-6th placed result positions directly (division_id is on the table)
			$placedRPs = $this->Resultpositions->find()
				->where([
					'Resultpositions.conventionseason_id' => $conventionSD->id,
					'Resultpositions.convention_id'       => $conventionSD->convention_id,
					'Resultpositions.season_id'           => $conventionSD->season_id,
					'Resultpositions.season_year'         => $conventionSD->season_year,
					'Resultpositions.position >='         => 1,
					'Resultpositions.position <='         => 6,
					'Resultpositions.points_obtained >'   => 0,
				])
				->enableHydration(false)->all();

			// Fetch event metadata used by eligibility and tie-break checks.
			$eventGroupFlags = $this->Events->find()
				->select(['id','group_event_yes_no','event_name'])
				->enableHydration(false)->all();
			$eventIsGroup = [];
			$eventNameById = [];
			foreach ($eventGroupFlags as $eg) {
				$eventId = (int)$eg['id'];
				$eventIsGroup[$eventId] = (int)$eg['group_event_yes_no'];
				$eventNameById[$eventId] = (string)($eg['event_name'] ?? '');
			}

			// Solo event flags: event_id => group_event_yes_no
			$criteria = [];
			$allStudentIdsForAward = array_keys($arrAllResults);
			foreach ($allStudentIdsForAward as $sid) {
				$criteria[$sid] = [
					'bible'=>false,
					'academic'=>false,
					'physed'=>false,
					'music'=>false,
					'platform'=>false,
					'exhibits'=>0,
					'teams'=>0,
					'exhibits_events'=>[],
					'team_events'=>[],
				];
			}

			foreach ($placedRPs as $rp) {
				$sid   = (int)($rp['student_id'] ?? 0);
				if (!$sid || !isset($criteria[$sid])) continue;
				$eid   = (int)($rp['event_id'] ?? 0);
				$divId = $this->normalizeDivisionIdForManualArts(
					(int)($rp['division_id'] ?? 0),
					(string)($rp['event_id_number'] ?? '')
				);
				$isGrp = (int)($eventIsGroup[$eid] ?? 0);
				$eventName = strtolower(preg_replace('/\s+/', ' ', trim((string)($eventNameById[$eid] ?? ''))));

				// Scripture/Bible Memorization events include Golden/Christian awards, Silver Apple and Bible Memory.
				$isBibleMemEvent = (
					strpos($eventName, 'golden ') !== false ||
					strpos($eventName, 'christian ') !== false ||
					strpos($eventName, 'silver apple') !== false ||
					strpos($eventName, 'bible memory') !== false
				);

				if ($isBibleMemEvent) $criteria[$sid]['bible'] = true;

				if (!$isGrp && $divId === $academicDivId)            $criteria[$sid]['academic'] = true;
				if (!$isGrp && in_array($divId, $physEdDivIds))      $criteria[$sid]['physed']   = true;
				if (!$isGrp && in_array($divId, $musicDivIds))       $criteria[$sid]['music']    = true;
				if (!$isGrp && $divId === $platformDivId)            $criteria[$sid]['platform'] = true;
				if (in_array($divId, $exhibitsDivIds))               $criteria[$sid]['exhibits_events'][$eid] = true;
			}

			foreach ($placedRPs as $rp) {
				$eid = (int)($rp['event_id'] ?? 0);
				if (empty($eventIsGroup[$eid])) {
					continue;
				}

				$groupStudents = [];
				if (!empty($rp['group_name'])) {
					$groupStudents = $this->Crstudentevents->find()->where([
						'Crstudentevents.group_name' => $rp['group_name'],
						'Crstudentevents.conventionregistration_id' => $rp['conventionregistration_id'],
						'Crstudentevents.conventionseason_id' => $rp['conventionseason_id'],
						'Crstudentevents.event_id' => $eid,
					])->all();
				}

				if (empty($groupStudents) && !empty($rp['student_id'])) {
					$groupStudents = [(object)['student_id' => (int)$rp['student_id']]];
				}

				foreach ($groupStudents as $groupStudent) {
					$sid = (int)($groupStudent->student_id ?? 0);
					if ($sid > 0 && isset($criteria[$sid])) {
						$criteria[$sid]['team_events'][$eid] = true;
					}
				}
			}

			foreach ($criteria as $sid => $c) {
				$criteria[$sid]['exhibits'] = count($c['exhibits_events']);
				$criteria[$sid]['teams'] = count($c['team_events']);
			}

			$award247Eligible = [];
			foreach ($criteria as $sid => $c) {
				if ($c['bible'] && $c['academic'] && $c['physed'] && $c['music'] && $c['platform'] && $c['exhibits'] >= 2 && $c['teams'] >= 2) {
					$award247Eligible[$sid] = $arrAllResults[$sid] ?? 0;
				}
			}

			// Sort by points first and then apply formal tie-break order for tied score bands.
			arsort($award247Eligible);
			$eligibleTieProfiles = $this->buildTieBreakerProfiles($conventionSD, array_keys($award247Eligible));
			$groupedByPoints = [];
			foreach ($award247Eligible as $sid => $points) {
				$groupedByPoints[(int)$points][] = (int)$sid;
			}
			krsort($groupedByPoints, SORT_NUMERIC);

			$award247Ordered = [];
			foreach ($groupedByPoints as $points => $studentIdsInBand) {
				$remaining = array_values($studentIdsInBand);
				while (!empty($remaining)) {
					$resolved = $this->resolveTiedStudentsByPrecedence($remaining, $eligibleTieProfiles);
					if (empty($resolved)) {
						break;
					}
					// If unresolved after all checks, keep all remaining together (director decision).
					if (count($resolved) === count($remaining)) {
						sort($resolved);
						foreach ($resolved as $sid) {
							$award247Ordered[$sid] = $points;
						}
						$remaining = [];
						continue;
					}

					// Promote resolved winner(s) first within this score band.
					foreach ($resolved as $sid) {
						$award247Ordered[$sid] = $points;
					}
					$resolvedLookup = array_fill_keys(array_map('intval', $resolved), true);
					$nextRemaining = [];
					foreach ($remaining as $sid) {
						if (!isset($resolvedLookup[(int)$sid])) {
							$nextRemaining[] = (int)$sid;
						}
					}
					$remaining = $nextRemaining;
				}
			}

			$award247Eligible = $award247Ordered;

			$award247WinnerIds = [];
			if (!empty($award247Eligible)) {
				$topPoints = max($award247Eligible);
				$topStudentIds = [];
				foreach ($award247Eligible as $sid => $points) {
					if ((int)$points === (int)$topPoints) {
						$topStudentIds[] = (int)$sid;
					}
				}
				$award247WinnerIds = $this->resolveTiedStudentsByPrecedence($topStudentIds, $eligibleTieProfiles);
			}

			$this->set('award247Eligible', $award247Eligible);
			$this->set('award247Criteria', $criteria);
			$this->set('award247WinnerIds', $award247WinnerIds);
			// ────────────────────────────────────────────────────────────────────────
			
			//$this->prx($maxKeys);
		}
		else
		{
			$this->Flash->error('Results not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		}
		
		//$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		        
    }
	
	public function overallpositions($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Overall Positions > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);
		
		// First to get list of all events for this conv + seasons
		$arrConvSeasonEvent = array();
		$arrConvSeasonEvent[] = 0;
		
		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($allEventsConvSeason as $convevent)
		{
			$arrConvSeasonEvent[] = $convevent->event_id;
		}

		$seedSessionKey = 'overallpositions_event_seed_'.$conventionSD->id;
		$eventOrderSeed = (int)$this->request->getSession()->read($seedSessionKey);
		if ($eventOrderSeed <= 0) {
			$eventOrderSeed = rand(1, 999999);
			$this->request->getSession()->write($seedSessionKey, $eventOrderSeed);
		}
		$this->set('eventOrderSeed', $eventOrderSeed);
		
		$this->set('arrConvSeasonEvent', $arrConvSeasonEvent);
		
		//$this->prx($arrConvSeasonEvent);
		        
    }
	
	public function overallpositionsprint($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('print_reports');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Overall Positions > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);
		
		// First to get list of all events for this conv + seasons
		$arrConvSeasonEvent = array();
		$arrConvSeasonEvent[] = 0;
		
		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($allEventsConvSeason as $convevent)
		{
			$arrConvSeasonEvent[] = $convevent->event_id;
		}

		$seedSessionKey = 'overallpositions_event_seed_'.$conventionSD->id;
		$eventOrderSeed = (int)$this->request->getSession()->read($seedSessionKey);
		if ($eventOrderSeed <= 0) {
			$eventOrderSeed = rand(1, 999999);
			$this->request->getSession()->write($seedSessionKey, $eventOrderSeed);
		}
		$this->set('eventOrderSeed', $eventOrderSeed);
		
		$this->set('arrConvSeasonEvent', $arrConvSeasonEvent);
		
		//$this->prx($arrConvSeasonEvent);
		        
    }

	public function overallpositionsprintgoogledocs($slug_convention_season = null,$slug_convention = null) {

		$this->viewBuilder()->setLayout('print_reports');

		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);

		if ($slug_convention_season) {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('conventionSD', $conventionSD);
		}
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('conventionD', $conventionD);
		}
		if (!$conventionD) {
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', 'Overall Positions (Google Docs) > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		$arrConvSeasonEvent = array(0);
		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach ($allEventsConvSeason as $convevent) {
			$arrConvSeasonEvent[] = $convevent->event_id;
		}

		$seedSessionKey = 'overallpositions_event_seed_'.$conventionSD->id;
		$eventOrderSeed = (int)$this->request->getSession()->read($seedSessionKey);
		if ($eventOrderSeed <= 0) {
			$eventOrderSeed = rand(1, 999999);
			$this->request->getSession()->write($seedSessionKey, $eventOrderSeed);
		}
		$this->set('eventOrderSeed', $eventOrderSeed);

		$this->set('arrConvSeasonEvent', $arrConvSeasonEvent);
	}

	public function overallpositionscsv($slug_convention_season = null,$slug_convention = null) {
		$this->autoRender = false;

		$conventionSD = null;
		$conventionD = null;

		if ($slug_convention_season) {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		}
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
		}
		if (!$conventionD) {
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$arrConvSeasonEvent = array(0);
		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach ($allEventsConvSeason as $convevent) {
			$arrConvSeasonEvent[] = $convevent->event_id;
		}

		$seedSessionKey = 'overallpositions_event_seed_'.$conventionSD->id;
		$eventOrderSeed = (int)$this->request->getSession()->read($seedSessionKey);
		if ($eventOrderSeed <= 0) {
			$eventOrderSeed = rand(1, 999999);
			$this->request->getSession()->write($seedSessionKey, $eventOrderSeed);
		}

		$events = $this->Events
			->find()
			->where(['Events.id IN' => $arrConvSeasonEvent])
			->order('RAND('.$eventOrderSeed.')')
			->all();

		$filename = 'overall_positions_'.preg_replace('/[^a-z0-9_]/i', '_', (string)$conventionD->name).'_'.preg_replace('/[^a-z0-9_]/i', '_', (string)$conventionSD->season_year).'_'.date('Ymd').'.csv';

		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		$fp = fopen('php://output', 'w');
		fputcsv($fp, ['Event', 'Position', 'School']);

		foreach ($events as $event) {
			$overallpositions = $this->Resultpositions
				->find()
				->where([
					'Resultpositions.conventionseason_id' => $conventionSD->id,
					'Resultpositions.event_id' => $event->id,
					'Resultpositions.position >' => 0,
					'Resultpositions.position <=' => 3,
				])
				->order(['Resultpositions.position' => 'DESC'])
				->contain(['Users'])
				->all();

			if ($overallpositions->isEmpty()) {
				continue;
			}

			fputcsv($fp, [
				$event->event_name.' ('.$event->event_id_number.')',
				'',
				'',
			]);

			foreach ($overallpositions as $ovpos) {
				$showName = '';
				if ((int)$ovpos->student_id > 0) {
					$studentD = $this->Users->find()->where(['Users.id' => $ovpos->student_id])->first();
					if ($studentD) {
						$showName = trim((string)$studentD->first_name.' '.(string)$studentD->last_name);
					}
				} elseif (!empty($ovpos->group_name)) {
					$arrGrpStudent = array();
					$groupstudents = $this->Crstudentevents->find()->where([
						'Crstudentevents.conventionseason_id' => $conventionSD->id,
						'Crstudentevents.event_id' => $event->id,
						'Crstudentevents.group_name' => $ovpos->group_name,
						'Crstudentevents.user_id' => $ovpos->user_id,
					])->order(['Crstudentevents.id' => 'ASC'])->all();

					foreach ($groupstudents as $grpstudent) {
						$studentDG = $this->Users->find()->where(['Users.id' => $grpstudent->student_id])->first();
						if ($studentDG) {
							$arrGrpStudent[] = trim((string)$studentDG->first_name.' '.(string)$studentDG->last_name);
						}
					}
					if (!empty($arrGrpStudent)) {
						$showName = implode(",\n", $arrGrpStudent);
					}
				}

				$schoolName = trim((string)($ovpos->Users['first_name'] ?? ''));
				$positionLabel = ((int)$ovpos->position === 1) ? '1st' : (((int)$ovpos->position === 2) ? '2nd' : '3rd');

				fputcsv($fp, [
					$showName,
					$positionLabel,
					$schoolName,
				]);
			}

			fputcsv($fp, ['', '', '']);
		}

		fclose($fp);
		exit;
	}

	public function overallpositionsjson($slug_convention_season = null,$slug_convention = null) {
		$this->autoRender = false;

		$conventionSD = null;
		$conventionD = null;

		if ($slug_convention_season) {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		}
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
		}
		if (!$conventionD) {
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$arrConvSeasonEvent = array(0);
		$allEventsConvSeason = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach ($allEventsConvSeason as $convevent) {
			$arrConvSeasonEvent[] = $convevent->event_id;
		}

		$seedSessionKey = 'overallpositions_event_seed_'.$conventionSD->id;
		$eventOrderSeed = (int)$this->request->getSession()->read($seedSessionKey);
		if ($eventOrderSeed <= 0) {
			$eventOrderSeed = rand(1, 999999);
			$this->request->getSession()->write($seedSessionKey, $eventOrderSeed);
		}

		$events = $this->Events
			->find()
			->where(['Events.id IN' => $arrConvSeasonEvent])
			->order('RAND('.$eventOrderSeed.')')
			->all();

		$jsonRows = [];

		foreach ($events as $event) {
			$overallpositions = $this->Resultpositions
				->find()
				->where([
					'Resultpositions.conventionseason_id' => $conventionSD->id,
					'Resultpositions.event_id' => $event->id,
					'Resultpositions.position >' => 0,
					'Resultpositions.position <=' => 3,
				])
				->order(['Resultpositions.position' => 'DESC'])
				->contain(['Users'])
				->all();

			if ($overallpositions->isEmpty()) {
				continue;
			}

			foreach ($overallpositions as $ovpos) {
				$showName = '';
				if ((int)$ovpos->student_id > 0) {
					$studentD = $this->Users->find()->where(['Users.id' => $ovpos->student_id])->first();
					if ($studentD) {
						$showName = trim((string)$studentD->first_name.' '.(string)$studentD->last_name);
					}
				} elseif (!empty($ovpos->group_name)) {
					$arrGrpStudent = array();
					$groupstudents = $this->Crstudentevents->find()->where([
						'Crstudentevents.conventionseason_id' => $conventionSD->id,
						'Crstudentevents.event_id' => $event->id,
						'Crstudentevents.group_name' => $ovpos->group_name,
						'Crstudentevents.user_id' => $ovpos->user_id,
					])->order(['Crstudentevents.id' => 'ASC'])->all();

					foreach ($groupstudents as $grpstudent) {
						$studentDG = $this->Users->find()->where(['Users.id' => $grpstudent->student_id])->first();
						if ($studentDG) {
							$arrGrpStudent[] = trim((string)$studentDG->first_name.' '.(string)$studentDG->last_name);
						}
					}

					if (!empty($arrGrpStudent)) {
						$showName = implode(', ', $arrGrpStudent);
					}
				}

				$jsonRows[] = [
					'category' => trim((string)$event->event_name.' '.(string)$event->event_id_number),
					'position' => (string)$ovpos->position,
					'name' => $showName,
					'school' => trim((string)($ovpos->Users['first_name'] ?? '')),
				];
			}
		}

		$filename = 'overall_positions_'.preg_replace('/[^a-z0-9_]/i', '_', (string)$conventionD->name).'_'.preg_replace('/[^a-z0-9_]/i', '_', (string)$conventionSD->season_year).'_'.date('Ymd').'.json';

		return $this->response
			->withType('application/json')
			->withDownload($filename)
			->withStringBody(json_encode($jsonRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	}
	
	public function closejudgingtimes($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		global $resultPoints;
		
		
		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}
		
		
		
		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity([]);
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;

		if ($this->isAutoAwardEvent($eventD))
		{
			$autoSavedCount = $this->saveAutoAwardResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully. Auto award rule applied to '.$autoSavedCount.' submission'.($autoSavedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		if ($this->isSilverAppleEvent($eventD))
		{
			$savedCount = $this->saveSilverAppleResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Silver Apple judging closed. Place and points calculated for '.$savedCount.' submission'.($savedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		
		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE
		
		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.time_score" => "ASC"])->all();
		//$this->prx($judgeEvals);
		
		$cntrRecord = 1;
		
		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{	
			// Calculate tie breakers
			if($cntrRecord == 1)
			{	
				$lastScore = $datarecord->time_score;
			}
			else
			{
				if($lastScore != $datarecord->time_score)
				{
					$cntrPos++;
					$lastScore = $datarecord->time_score;
				}
			}
			
			// enter record
			$resultpositions = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');
			
			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}
			
			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);
			
			
			$cntrRecord++;
		}
		
		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
		
		//echo $result_id;exit;
		
		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
		
		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function resulttimes($slug_convention_season = null,$slug_convention = null,$slug_event = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		
		global $resultPoints;
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);
		
		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		$this->set('checkResultsAlready',$checkResultsAlready);
		if($checkResultsAlready)
		{
			$result_id = $checkResultsAlready->id;
			
			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Users','Students'])->all();
			$this->set('resultsPos',$resultsPos);
		}
		else
		{
			// redirect if no result
			$this->Flash->error('Result not found for this event.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());
			
			$postData = $this->request->getData();
			
			//$this->prx($postData);
			
			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];
				
				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}
				
				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}
			
			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }
	
	public function closejudgingdistances($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		// To get qualifying data
		$convSeasEventD = $this->Conventionseasonevents->find()
				->where([
				'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
				'Conventionseasonevents.event_id' => $eventD->id
				])->first();
				
		//$this->prx($convSeasEventD);
		
		$qualifying_distance = $convSeasEventD->convSeasEventD;
		
		global $resultPoints;
		
		
		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}
		
		
		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity([]);
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;

		if ($this->isAutoAwardEvent($eventD))
		{
			$autoSavedCount = $this->saveAutoAwardResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully. Auto award rule applied to '.$autoSavedCount.' submission'.($autoSavedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		if ($this->isSilverAppleEvent($eventD))
		{
			$savedCount = $this->saveSilverAppleResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Silver Apple judging closed. Place and points calculated for '.$savedCount.' submission'.($savedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		
		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE
		
		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$judgeEvalsRaw = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->all();

		$minQualifyingDistance = (is_numeric($qualifying_distance) ? (float)$qualifying_distance : 0.0);
		$judgeEvals = [];
		foreach ($judgeEvalsRaw as $judgeEval) {
			$scoreCandidates = [
				$judgeEval->distance_score,
				$judgeEval->distance_attempt_1,
				$judgeEval->distance_attempt_2,
				$judgeEval->distance_attempt_3,
			];
			$effectiveScore = 0.0;
			foreach ($scoreCandidates as $scoreCandidate) {
				if ($scoreCandidate === null || $scoreCandidate === '') {
					continue;
				}
				$effectiveScore = max($effectiveScore, (float)$scoreCandidate);
			}

			if ($effectiveScore <= 0) {
				continue;
			}
			if ($effectiveScore < $minQualifyingDistance) {
				continue;
			}

			$judgeEval->effective_distance_score = $effectiveScore;
			$judgeEvals[] = $judgeEval;
		}

		usort($judgeEvals, function ($a, $b) {
			$scoreA = (float)($a->effective_distance_score ?? 0);
			$scoreB = (float)($b->effective_distance_score ?? 0);
			if ($scoreA === $scoreB) {
				return 0;
			}
			return ($scoreA > $scoreB) ? -1 : 1;
		});
		//$this->prx($judgeEvals);
		
		$cntrRecord = 1;
		
		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{	
			$currentDistanceScore = (float)($datarecord->effective_distance_score ?? 0);

			// Calculate tie breakers
			if($cntrRecord == 1)
			{	
				$lastScore = $currentDistanceScore;
			}
			else
			{
				if($lastScore != $currentDistanceScore)
				{
					$cntrPos++;
					$lastScore = $currentDistanceScore;
				}
			}
			
			// enter record
			$resultpositions = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $currentDistanceScore;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');
			
			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}
			
			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);
			
			$cntrRecord++;
		}
		
		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
		
		//echo $result_id;exit;
		
		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
		
		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function resultdistances($slug_convention_season = null,$slug_convention = null,$slug_event = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		
		global $resultPoints;
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);
		
		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		
		if($checkResultsAlready)
		{
			$result_id = $checkResultsAlready->id;

			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Users','Students'])->all();
			$this->set('resultsPos', $resultsPos);
			
			$this->set('checkResultsAlready',$checkResultsAlready);
			
		}
		else
		{
			// Redirect if no result
			$this->Flash->error('No result found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		
		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());
			
			$postData = $this->request->getData();
			
			//$this->prx($postData);
			
			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];
				
				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}
				
				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}
			
			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }
	
	public function closejudgingscores($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		global $resultPoints;
		
		
		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		//$this->prx($checkResultsAlready);
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}
		
		
		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity([]);
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;

		if ($this->isAutoAwardEvent($eventD))
		{
			$autoSavedCount = $this->saveAutoAwardResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully. Auto award rule applied to '.$autoSavedCount.' submission'.($autoSavedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		if ($this->isSilverAppleEvent($eventD))
		{
			$savedCount = $this->saveSilverAppleResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Silver Apple judging closed. Place and points calculated for '.$savedCount.' submission'.($savedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		
		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE
		
		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$condEval[] 	= "(Judgeevaluations.all_pos_score >0)";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.all_pos_score" => "DESC"])->all();
		//$this->prx($judgeEvals);
		
		$cntrRecord = 1;
		
		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{	
			// Calculate tie breakers
			if($cntrRecord == 1)
			{	
				$lastScore = $datarecord->all_pos_score;
			}
			else
			{
				if($lastScore != $datarecord->all_pos_score)
				{
					$cntrPos++;
					$lastScore = $datarecord->all_pos_score;
				}
			}
			
			// enter record
			$resultpositions = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $datarecord->all_pos_score;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');
			
			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}
			
			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);
			
			$cntrRecord++;
		}
		
		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
		
		//echo $result_id;exit;
		
		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
		
		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function resultscores($slug_convention_season = null,$slug_convention = null,$slug_event = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		
		global $resultPoints;
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);
		
		$arrAlreadySavedResults = array();
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$result_id = $checkResultsAlready->id;

			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(["Users","Students"])->all();
			$this->set('resultsPos',$resultsPos);
			
			$this->set('checkResultsAlready',$checkResultsAlready);
		}
		else
		{
			// Redirect if no results
			$this->Flash->error('No result found..');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());
			
			$postData = $this->request->getData();
			
			//$this->prx($postData);
			
			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];
				
				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}
				
				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}
			
			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }
	
	/* Soccer Kick */
	public function closejudgingsoccerkick($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		global $resultPoints;
		
		
		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		//$this->prx($checkResultsAlready);
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}
		
		
		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity([]);
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;

		if ($this->isAutoAwardEvent($eventD))
		{
			$autoSavedCount = $this->saveAutoAwardResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully. Auto award rule applied to '.$autoSavedCount.' submission'.($autoSavedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		if ($this->isSilverAppleEvent($eventD))
		{
			$savedCount = $this->saveSilverAppleResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Silver Apple judging closed. Place and points calculated for '.$savedCount.' submission'.($savedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		
		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE
		
		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$condEval[] 	= "(Judgeevaluations.soccer_kick_best_kick >0)";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.soccer_kick_best_kick" => "DESC"])->all();
		//$this->prx($judgeEvals);
		
		$cntrRecord = 1;
		
		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{	
			// Calculate tie breakers
			if($cntrRecord == 1)
			{	
				$lastScore = $datarecord->soccer_kick_best_kick;
			}
			else
			{
				if($lastScore != $datarecord->soccer_kick_best_kick)
				{
					$cntrPos++;
					$lastScore = $datarecord->soccer_kick_best_kick;
				}
			}
			
			
			// enter record
			$resultpositions = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $datarecord->soccer_kick_best_kick;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');
			
			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}
			
			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);
			
			$cntrRecord++;
		}
		
		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
		
		//echo $result_id;exit;
		
		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
		
		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function resultsoccerkick($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		global $resultPoints;
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);
		
		
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$result_id = $checkResultsAlready->id;

			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Students','Users'])->all();
			$this->set('resultsPos', $resultsPos);
			
			$this->set('checkResultsAlready', $checkResultsAlready);
		}
		else
		{
			$this->Flash->error('No result found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());
			
			$postData = $this->request->getData();
			
			//$this->prx($postData);
			
			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];
				
				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}
				
				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}
			
			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }
	
	//Spellings
	public function closejudgingspellings($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		global $resultPoints;
		
		
		//STEP1 :: DELETE ALL EXISTING RESULTS IF ANY RELATED TO THIS CONV + SEASON + EVENT
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$this->Resultpositions->deleteAll(["result_id" => $checkResultsAlready->id]);
			$this->Results->deleteAll(["id" => $checkResultsAlready->id]);
		}
		
		
		//STEP2 :: SAVE ONE ENTRY IN RESULTS TABLE
		$results = $this->Results->newEntity([]);
		$dataR = $this->Results->patchEntity($results, array());

		$dataR->slug 						= "result-event-".$eventD->id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
		$dataR->conventionseason_id 		= $conventionSD->id;
		$dataR->convention_id 				= $conventionSD->convention_id;
		$dataR->season_id 					= $conventionSD->season_id;
		$dataR->season_year 				= $conventionSD->season_year;
		$dataR->event_id 					= $eventD->id;
		$dataR->event_id_number 			= $eventD->event_id_number;
		$dataR->division_id 				= $eventD->division_id;
		$dataR->created 					= $conventionSD->created;
		$dataR->original_results_modified 	= 0;

		$resultR = $this->Results->save($dataR);
		$result_id = $resultR->id;

		if ($this->isAutoAwardEvent($eventD))
		{
			$autoSavedCount = $this->saveAutoAwardResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully. Auto award rule applied to '.$autoSavedCount.' submission'.($autoSavedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}

		if ($this->isSilverAppleEvent($eventD))
		{
			$savedCount = $this->saveSilverAppleResultPositions($conventionSD, $eventD, $result_id);
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
			$this->Flash->success('Silver Apple judging closed. Place and points calculated for '.$savedCount.' submission'.($savedCount == 1 ? '' : 's').'.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		
		
		//STEP3 :: SAVE ENTRIES IN Resultpositions TABLE
		
		$condEval = array();
		$condEval[] 	= "(Judgeevaluations.conventionseason_id = '".$conventionSD->id."')";
		$condEval[] 	= "(Judgeevaluations.convention_id = '".$conventionSD->convention_id."')";
		$condEval[] 	= "(Judgeevaluations.season_id = '".$conventionSD->season_id."')";
		$condEval[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
		$condEval[] 	= "(Judgeevaluations.withdraw_yes_no != '1')";
		$condEval[] 	= "(Judgeevaluations.spelling_score >0)";
		$judgeEvals = $this->Judgeevaluations->find()->where($condEval)->contain(['Students'])->order(["Judgeevaluations.spelling_score" => "DESC"])->all();
		//$this->prx($judgeEvals);
		
		$cntrRecord = 1;
		
		$cntrPos = 1;
		foreach($judgeEvals as $datarecord)
		{	
			// Calculate tie breakers
			if($cntrRecord == 1)
			{	
				$lastScore = $datarecord->spelling_score;
			}
			else
			{
				if($lastScore != $datarecord->spelling_score)
				{
					$cntrPos++;
					$lastScore = $datarecord->spelling_score;
				}
			}
			
			// enter record
			$resultpositions = $this->Resultpositions->newEntity([]);
			$dataRP = $this->Resultpositions->patchEntity($resultpositions, array());

			$dataRP->slug 								= "result-positions-".$result_id."-".$conventionSD->id."-".time().'-'.rand(100,1000000);
			$dataRP->result_id							= $result_id;
			$dataRP->eventsubmission_id					= $datarecord->id;
			$dataRP->conventionregistration_id			= $datarecord->conventionregistration_id;
			$dataRP->conventionseason_id				= $datarecord->conventionseason_id;
			$dataRP->convention_id						= $datarecord->convention_id;
			$dataRP->user_id							= $datarecord->user_id;
			$dataRP->season_id							= $datarecord->season_id;
			$dataRP->season_year						= $datarecord->season_year;
			$dataRP->event_id							= $eventD->id;
			$dataRP->event_id_number					= $eventD->event_id_number;
			$dataRP->division_id						= $eventD->division_id;
			$dataRP->group_name							= $datarecord->group_name;
			$dataRP->student_id							= $datarecord->student_id;
			$dataRP->gender								= $datarecord->Students['gender'];
			$dataRP->avg_marks							= $datarecord->spelling_score;
			$dataRP->position							= $cntrPos;
			$dataRP->created 							= date('Y-m-d H:i:s');
			$dataRP->modified 							= date('Y-m-d H:i:s');
			
			if($cntrPos>=1 && $cntrPos<=6)
			{
				$dataRP->points_obtained				= $resultPoints[$cntrPos];
			}
			
			//$this->prx($dataRP);

			$resultRP = $this->Resultpositions->save($dataRP);
			
			$cntrRecord++;
		}
		
		//STEP8 :: UPDATE RESULTS MODIFIED FIELD
		$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
		
		//echo $result_id;exit;
		
		//STEP9 :: CLOSE JUDGING
		$this->Conventionseasonevents->updateAll(['judging_ends' => '1'], ["conventionseasons_id" => $conventionSD->id,"event_id" => $eventD->id]);
		
		$this->Flash->success('Judging for the event has been closed successfully and results saved sucessfully.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function resultspellings($slug_convention_season = null,$slug_convention = null,$slug_event = null)
	{
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		global $resultPoints;
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_event) {
            $eventD 		= $this->Events->find()->where(['Events.slug' => $slug_event])->first();
			$this->set('eventD', $eventD);
        }
		if (!$eventD)
		{
			$this->Flash->error('Event not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Results > '.$conventionD->name.' > Event > '.$eventD->event_name.' '.ADMIN_TITLE);
		
		
		// to check that results are already saved for this conv season event or not
		$checkResultsAlready 		= $this->Results->find()->where(['Results.conventionseason_id' => $conventionSD->id,'Results.convention_id' => $conventionSD->convention_id,'Results.season_id' => $conventionSD->season_id,'Results.season_year' => $conventionSD->season_year,'Results.event_id' => $eventD->id])->first();
		if($checkResultsAlready)
		{
			$result_id = $checkResultsAlready->id;

			// to fetch result positions based on already saved results
			$resultsPos 		= $this->Resultpositions->find()->where(['Resultpositions.result_id' => $checkResultsAlready->id])->order(['Resultpositions.position' => 'ASC'])->contain(['Students','Users'])->all();
			$this->set('resultsPos', $resultsPos);
		}
		else
		{
			// Redirect if no result
			$this->Flash->error('No result found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
		// to save results
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());
			
			$postData = $this->request->getData();
			
			//$this->prx($postData);
			
			// make entry that original results modified
			$this->Results->updateAll(['original_results_modified' => 1,'modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			foreach($resultsPos as $resposdata)
			{
				$posVal = $postData['result_position_'.$resposdata->id];
				
				if($posVal>=1 && $posVal<=6)
				{
					$points_obtained				= $resultPoints[$posVal];
				}
				else
				{
					$points_obtained				= NULL;
				}
				
				$this->Resultpositions->updateAll(
					[
						'position' => (int)$posVal,
						'points_obtained' => (int)$points_obtained,
						'modified' => date('Y-m-d H:i:s')
					],
					['id' => (int)$resposdata->id]
				);
			}
			
			// update results
			$this->Results->updateAll(['modified' => date('Y-m-d H:i:s')], ["id" => $result_id]);
			
			$this->Flash->success('Results modified sucessfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
    }
	
	// Division winners
	public function divisionwinners($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$slug_event = null;
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Division Winners > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);
		
		$divisions = $this->Divisions->find()->where(['Divisions.status' => 1])->order(['Divisions.name' => 'ASC'])->all();
		$this->set('divisions', $divisions);

		$divisionWinnersData = $this->buildDivisionWinnersData($conventionSD);
		if(!$divisionWinnersData)
		{
			$this->Flash->error('Results not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
			return;
		}

		$this->set('arrAllResults', $divisionWinnersData['arrAllResults']);
		$this->set('trophyWinners', $divisionWinnersData['trophyWinners']);
		$this->set('genderSplitDivs', $divisionWinnersData['genderSplitDivs']);

		$winnerStudentIds = [];
		foreach ((array)$divisionWinnersData['trophyWinners'] as $divisionId => $winnerData) {
			if (in_array((int)$divisionId, (array)$divisionWinnersData['genderSplitDivs'])) {
				foreach (['Male', 'Female'] as $gender) {
					if (!empty($winnerData[$gender]['students'])) {
						$winnerStudentIds = array_merge($winnerStudentIds, array_map('intval', (array)$winnerData[$gender]['students']));
					}
				}
				continue;
			}

			if (!empty($winnerData['students'])) {
				$winnerStudentIds = array_merge($winnerStudentIds, array_map('intval', (array)$winnerData['students']));
			}
		}

		$this->set('eventsPlacedByStudent', $this->buildEventsPlacedByStudent($conventionSD, $winnerStudentIds));
		
		//$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
		        
    }

	public function divisionwinnersplainprint($slug_convention_season = null,$slug_convention = null) {

		$this->viewBuilder()->disableAutoLayout();

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);

		$conventionSD = null;
		if ($slug_convention_season) {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('conventionSD', $conventionSD);
		}
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
			return;
		}

		$conventionD = null;
		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('conventionD', $conventionD);
		}
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
			return;
		}

		$this->set('title', 'Division Winners (Plain Print) > '.$conventionD->name.' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		$divisions = $this->Divisions->find()->where(['Divisions.status' => 1])->order(['Divisions.name' => 'ASC'])->all();
		$this->set('divisions', $divisions);

		$divisionWinnersData = $this->buildDivisionWinnersData($conventionSD);
		if(!$divisionWinnersData)
		{
			$this->Flash->error('Results not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $slug_convention]);
			return;
		}

		$this->set('arrAllResults', $divisionWinnersData['arrAllResults']);
		$this->set('trophyWinners', $divisionWinnersData['trophyWinners']);
		$this->set('genderSplitDivs', $divisionWinnersData['genderSplitDivs']);
	}
	
	public function divisionwinnercertificatepdf($slug_convention_season = null,$slug_division = null,$slug_student = null) {
        
		$this->viewBuilder()->disableAutoLayout();
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		$slug_convention = null;
		$slug_event = null;
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_division) {
            $divisionD 		= $this->Divisions->find()->where(['Divisions.slug' => $slug_division])->first();
			$this->set('divisionD', $divisionD);
        }
		if (!$divisionD)
		{
			$this->Flash->error('Division not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_student) {
            $studentD 		= $this->Users->find()->where(["Users.slug" => $slug_student])->contain(['Schools'])->first();
			$this->set('studentD', $studentD);
        }
		if (!$studentD)
		{
			$this->Flash->error('Student not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', 'Division Winners > '.$conventionSD->Conventions['name'].' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);
		
		$arrCertData = array();
		
		$arrCertData['convention_name'] = $conventionSD->Conventions['name'];
		$arrCertData['student_name'] 	= $studentD->first_name.' '.$studentD->last_name;
		$arrCertData['school_name'] 	= $studentD->Schools['first_name'];
		$arrCertData['division_name'] 	= $divisionD->name;
		
		
		$this->set('arrCertData', $arrCertData);       
    }
	

	public function divisionwinnercertificateplainprint($slug_convention_season = null,$slug_division = null,$slug_student = null) {

		$this->viewBuilder()->disableAutoLayout();

		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		$slug_convention = null;
		$slug_event = null;

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		$this->set('slug_event', $slug_event);

		if ($slug_convention_season) {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
			$this->set('conventionSD', $conventionSD);
		}
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
			return;
		}

		if ($slug_division) {
			$divisionD = $this->Divisions->find()->where(['Divisions.slug' => $slug_division])->first();
			$this->set('divisionD', $divisionD);
		}
		if (!$divisionD)
		{
			$this->Flash->error('Division not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
			return;
		}

		if ($slug_student) {
			$studentD = $this->Users->find()->where(["Users.slug" => $slug_student])->contain(['Schools'])->first();
			$this->set('studentD', $studentD);
		}
		if (!$studentD)
		{
			$this->Flash->error('Student not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
			return;
		}

		$this->set('title', 'Division Winners (Plain Print) > '.$conventionSD->Conventions['name'].' > Season > '.$conventionSD->season_year.' '.ADMIN_TITLE);

		$arrCertData = [];
		$arrCertData['convention_name'] = $conventionSD->Conventions['name'];
		$arrCertData['season_year'] = $conventionSD->season_year;
		$arrCertData['student_name'] = trim($studentD->first_name.' '.$studentD->last_name);
		$arrCertData['school_name'] = $studentD->Schools['first_name'] ?? '';
		$arrCertData['division_name'] = $divisionD->name;

		$this->set('arrCertData', $arrCertData);
	}
	// 24/7 Certificate
	public function certificate24by7pdf($slug_convention_season = null,$slug_student = null,$points = null) {
        
		$this->viewBuilder()->disableAutoLayout();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_student) {
            $studentD 		= $this->Users->find()->where(["Users.slug" => $slug_student])->contain(['Schools'])->first();
			$this->set('studentD', $studentD);
        }
		if (!$studentD)
		{
			$this->Flash->error('Student not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		
		$arrCertData = array();
		
		$arrCertData['convention_name'] = $conventionSD->Conventions['name'];
		$arrCertData['student_name'] 	= $studentD->first_name.' '.$studentD->last_name;
		$arrCertData['school_name'] 	= $studentD->Schools['first_name'];
		$arrCertData['points'] 			= $points;
		
		//$this->prx($arrCertData);
		
		
		$this->set('arrCertData', $arrCertData);       
    }

	public function certificate24by7plainprint($slug_convention_season = null,$slug_student = null,$points = null) {
		$this->viewBuilder()->disableAutoLayout();

		if ($slug_convention_season) {
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
			$this->set('conventionSD', $conventionSD);
		}
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
			return;
		}

		if ($slug_student) {
			$studentD = $this->Users->find()->where(["Users.slug" => $slug_student])->contain(['Schools'])->first();
			$this->set('studentD', $studentD);
		}
		if (!$studentD)
		{
			$this->Flash->error('Student not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
			return;
		}

		$arrCertData = [];
		$arrCertData['convention_name'] = $conventionSD->Conventions['name'];
		$arrCertData['student_name'] = $studentD->first_name.' '.$studentD->last_name;
		$arrCertData['school_name'] = $studentD->Schools['first_name'];
		$arrCertData['points'] = $points;

		$this->set('arrCertData', $arrCertData);
	}
	

}

?>
