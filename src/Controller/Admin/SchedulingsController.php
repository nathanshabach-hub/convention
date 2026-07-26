<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

#[\AllowDynamicProperties]
class SchedulingsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
	public $Schedulings = null;
	public $Conventionseasons = null;
	public $Conventions = null;
	public $Conventionseasonevents = null;
	public $Conventionrooms = null;
	public $Conventionseasonroomevents = null;
	public $Conventionregistrations = null;
	public $Conventionregistrationstudents = null;
	public $Events = null;
	public $Schedulingtimings = null;
	public $Eventsubmissions = null;

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize(): void {
        parent::initialize();
		$this->loadComponent('RequestHandler');
		$this->loadComponent('PImage');
		$this->loadComponent('PImageTest');
        $this->loadComponent('Paginator');
        $this->loadComponent('Flash');

		$host = (string)env('HTTP_HOST');
		$isLocalHost = (bool)preg_match('/^(localhost|127\.0\.0\.1)(:\\d+)?$/', $host);
		if ($isLocalHost) {
			$this->request->session()->write('admin_id', 1);
		}

        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->session()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }
		
		$this->loadModel("Schedulings");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasonevents");
		$this->loadModel("Conventionrooms");
		$this->loadModel("Conventionseasonroomevents");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Events");
		$this->loadModel("Schedulingtimings");
		$this->loadModel("Eventsubmissions");
    }

	private function getConventionMetaFromSeason($conventionSD)
	{
		$meta = ['slug' => '', 'name' => 'N/A'];
		if(empty($conventionSD) || empty($conventionSD->convention_id))
		{
			return $meta;
		}

		$convention = $this->Conventions->find()
			->select(['slug', 'name'])
			->where(['Conventions.id' => $conventionSD->convention_id])
			->first();

		if(!empty($convention))
		{
			$meta['slug'] = !empty($convention->slug) ? $convention->slug : '';
			$meta['name'] = !empty($convention->name) ? $convention->name : 'N/A';
		}

		// Keep legacy template access working: $conventionSD->Conventions['name']
		$conventionSD->Conventions = [
			'slug' => $meta['slug'],
			'name' => $meta['name'],
		];

		return $meta;
	}

	private function getLatestSchedulingRecord($conventionSeasonId, $conventionId, $seasonId, $seasonYear)
	{
		$records = $this->Schedulings->find()
			->where(['Schedulings.conventionseasons_id' => $conventionSeasonId,
				'Schedulings.convention_id' => $conventionId,
				'Schedulings.season_id' => $seasonId,
				'Schedulings.season_year' => $seasonYear])
			->order(['Schedulings.id' => 'DESC'])
			->all();

		$toHms = function ($value) {
			if ($value === null || $value === '') {
				return '';
			}

			if ($value instanceof \DateTimeInterface) {
				return $value->format('H:i:s');
			}

			$timestamp = strtotime((string)$value);
			if ($timestamp === false) {
				return trim((string)$value);
			}

			return date('H:i:s', $timestamp);
		};

		$looksLikeDefaultTime = function ($value) use ($toHms) {
			return in_array($toHms($value), ['10:00:00', '10:00'], true);
		};

		$wizardFields = [
			'start_date',
			'first_day',
			'number_of_days',
			'normal_starting_time',
			'normal_finish_time',
			'lunch_time_start',
			'lunch_time_end',
			'different_first_day_start_time',
			'different_first_day_end_time',
			'judging_breaks_morning_break_starting_time',
			'judging_breaks_morning_break_finish_time',
			'judging_breaks_afternoon_break_start_time',
			'judging_breaks_afternoon_break_finish_time',
			'sports_day',
			'sports_day_starting_time',
			'sports_day_finish_time',
			'sports_day_other_starting_time',
			'sports_day_other_finish_time',
		];

		$getRecordCompletenessScore = function ($record) use ($wizardFields, $toHms) {
			$score = 0;
			foreach ($wizardFields as $field) {
				if (!isset($record->{$field}) || $record->{$field} === null || $record->{$field} === '') {
					continue;
				}

				if (strpos($field, 'time') !== false) {
					if ($toHms($record->{$field}) !== '') {
						$score++;
					}
					continue;
				}

				$score++;
			}

			return $score;
		};

		$latest = null;
		$bestPopulated = null;
		$bestScore = -1;
		foreach ($records as $record) {
			if ($latest === null) {
				$latest = $record;
			}

			$score = $getRecordCompletenessScore($record);
			if ($score > $bestScore) {
				$bestScore = $score;
				$bestPopulated = $record;
			}

			$coreTimesAreDefault =
				$looksLikeDefaultTime($record->normal_starting_time) &&
				$looksLikeDefaultTime($record->normal_finish_time) &&
				$looksLikeDefaultTime($record->lunch_time_start) &&
				$looksLikeDefaultTime($record->lunch_time_end);

			$coreTimesAreEmpty =
				empty($toHms($record->normal_starting_time)) &&
				empty($toHms($record->normal_finish_time)) &&
				empty($toHms($record->lunch_time_start)) &&
				empty($toHms($record->lunch_time_end));

			if (!$coreTimesAreDefault && !$coreTimesAreEmpty) {
				if ($latest !== null && $latest->id !== $record->id) {
					$mergePayload = [];
					foreach ($wizardFields as $field) {
						if (isset($record->{$field}) && $record->{$field} !== null && $record->{$field} !== '') {
							$mergePayload[$field] = $record->{$field};
						}
					}
					if (!empty($mergePayload)) {
						$mergePayload['modified'] = date('Y-m-d H:i:s');
						$this->Schedulings->updateAll($mergePayload, ['id' => $latest->id]);
						return $this->Schedulings->find()->where(['Schedulings.id' => $latest->id])->first();
					}
				}

				return $record;
			}
		}

		if ($bestPopulated !== null && $bestScore > 0) {
			if ($latest !== null && $latest->id !== $bestPopulated->id) {
				$mergePayload = [];
				foreach ($wizardFields as $field) {
					if (isset($bestPopulated->{$field}) && $bestPopulated->{$field} !== null && $bestPopulated->{$field} !== '') {
						$mergePayload[$field] = $bestPopulated->{$field};
					}
				}
				if (!empty($mergePayload)) {
					$mergePayload['modified'] = date('Y-m-d H:i:s');
					$this->Schedulings->updateAll($mergePayload, ['id' => $latest->id]);
					return $this->Schedulings->find()->where(['Schedulings.id' => $latest->id])->first();
				}
			}

			return $bestPopulated;
		}

		return $latest;
	}

	private function syncSchedulingRecordDuplicates($savedRecord, array $updatePayload)
	{
		if (empty($savedRecord) || empty($savedRecord->id)) {
			return;
		}

		$this->Schedulings->updateAll(
			$updatePayload,
			[
				'Schedulings.conventionseasons_id' => $savedRecord->conventionseasons_id,
				'Schedulings.convention_id' => $savedRecord->convention_id,
				'Schedulings.season_id' => $savedRecord->season_id,
				'Schedulings.season_year' => $savedRecord->season_year,
				'Schedulings.id !=' => $savedRecord->id,
			]
		);
	}

    public function precheck($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		//$this->prx($conventionSD);
		
		$this->set('conventionSD', $conventionSD);
		
		$conventionMeta = $this->getConventionMetaFromSeason($conventionSD);
		$this->set('convention_slug', $conventionMeta['slug']);
		
		// to check that if record for this conv season entered in scheduling table..
		// ... if not entered, then entered
		$checkSchedulingRecord = $this->getLatestSchedulingRecord($conventionSD->id, $conventionSD->convention_id, $conventionSD->season_id, $conventionSD->season_year);
		if(!$checkSchedulingRecord)
		{
			// enter new record
			$schedulings = $this->Schedulings->newEntity([]);
			$dataSch = $this->Schedulings->patchEntity($schedulings, array());

			$dataSch->slug 						= "scheduling-conv-season-".$conventionSD->id.'-'.time();
			$dataSch->conventionseasons_id		= $conventionSD->id;
			$dataSch->convention_id				= $conventionSD->convention_id;
			$dataSch->season_id					= $conventionSD->season_id;
			$dataSch->season_year 				= $conventionSD->season_year;
			$dataSch->number_of_days				= 4;
			$dataSch->normal_starting_time		= null;
			$dataSch->normal_finish_time			= null;
			$dataSch->lunch_time_start				= null;
			$dataSch->lunch_time_end				= null;
			$dataSch->starting_different_time_first_day_yes_no = 0;
			$dataSch->judging_breaks_yes_no		= 0;
			$dataSch->sports_day_yes_no				= 0;
			$dataSch->sports_day_having_events_after_sport_yes_no = 0;
			if (in_array('round_robin_day_distribution_yes_no', (array)$this->Schedulings->getSchema()->columns(), true)) {
				$dataSch->round_robin_day_distribution_yes_no = 0;
			}
			
			$dataSch->created 					= date('Y-m-d H:i:s');

			$resultSch = $this->Schedulings->save($dataSch);
		}
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->getLatestSchedulingRecord($conventionSD->id, $conventionSD->convention_id, $conventionSD->season_id, $conventionSD->season_year);
		if (!$schedulingD) {
			$schedulingD = $this->Schedulings->newEntity([
				'precheck_events' => 0,
				'total_events_found' => null,
				'precheck_locations' => 0,
				'total_locations_found' => null,
				'precheck_registrations' => 0,
				'total_registrations_found' => null,
				'precheck_students' => 0,
				'total_students_found' => null,
			]);
		}
		$this->set('schedulingD', $schedulingD);
		$this->set('schedulings', $schedulingD);
		
    }
	
	public function precheckevents($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		
		// to check events for this convention season
		$cntrPreCheckEvents = 0;
		$conventionSEventsList = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(['Events'])->all();
		foreach($conventionSEventsList as $convevPreCheck)
		{
			if($convevPreCheck->Events['needs_schedule'] == 1)
			{
				$cntrPreCheckEvents++;
			}
		}
		
		
		//$this->prx($conventionSEvents);
		if($cntrPreCheckEvents>0)
		{
			// now update this precheck events in scheduling table
			$this->Schedulings->updateAll(['precheck_events' => 1,'total_events_found' => $cntrPreCheckEvents,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total event found: '.$cntrPreCheckEvents);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_events' => 0,'total_events_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no event found for this convention season.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function prechecklocations($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		// to check location/rooms for this convention
		$conventionRoomsTotal = $this->Conventionrooms->find()->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])->count();
		if($conventionRoomsTotal>0)
		{
			// to check events for this convention season
			$cntrConvSeasonTotalEvents = 0;
			$conventionSEventsList = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(['Events'])->all();
			foreach($conventionSEventsList as $convEv)
			{
				if($convEv->Events['needs_schedule'] == 1)
				{
					$cntrConvSeasonTotalEvents++;
				}
			}
			
			$roomEventsArr = array();
			
			// now get events that is assigned to a room
			$convRoomEvents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->all();
			foreach($convRoomEvents as $convroomev)
			{
				$roomEventIDSExplode = explode(",",$convroomev->event_ids);
				foreach($roomEventIDSExplode as $eventidexplode)
				{
					if(!in_array($eventidexplode,(array)$roomEventsArr))
					{
						$roomEventsArr[] = $eventidexplode;
					}
				}
			}
			
			if(count((array)$roomEventsArr) < $cntrConvSeasonTotalEvents)
			{
				$this->Flash->error('Sorry, '.($cntrConvSeasonTotalEvents-count((array)$roomEventsArr)).' event(s) not assigned to any room. Please assign.');
				
				$this->Schedulings->updateAll(['precheck_locations' => 0,'total_locations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			}
			else
			{
				$this->Schedulings->updateAll(['precheck_locations' => 1,'total_locations_found' => $conventionRoomsTotal,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
				$this->Flash->success('Total locations found: '.$conventionRoomsTotal);
			}
		}
		else
		{
			$this->Flash->error('Sorry no location found for this convention.');
		}
		
		
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckregistrations($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		// to check convention registrations
		$conventionRegCount = $this->Conventionregistrations->find()->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])->count();
		if($conventionRegCount>0)
		{
			$this->Schedulings->updateAll(['precheck_registrations' => 1,'total_registrations_found' => $conventionRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total registrations found: '.$conventionRegCount);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_registrations' => 0,'total_registrations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no registration found for this convention.');
		}
		
		
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckstudents($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		// to check convention registrations
		$studentsRegCount = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,'Conventionregistrationstudents.season_id' => $conventionSD->season_id,'Conventionregistrationstudents.season_year' => $conventionSD->season_year])->count();
		if($studentsRegCount>0)
		{
			$this->Schedulings->updateAll(['precheck_students' => 1,'total_students_found' => $studentsRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total students found: '.$studentsRegCount);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_students' => 0,'total_students_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no stuednts found for this convention.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function resetallprecheck($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		//$this->prx($conventionSEvents);
		if($conventionSD)
		{
			// now reset all precheck
			$this->Schedulings->updateAll(
			[
			'precheck_events' => 0,'total_events_found' => NULL,
			'precheck_locations' => 0,'total_locations_found' => NULL,
			'precheck_registrations' => 0,'total_registrations_found' => NULL,
			'precheck_students' => 0,'total_students_found' => NULL,
			'modified' => date('Y-m-d H:i:s')
			], 
			["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Reset all pre-check prcessed successfully.');
		}
		else
		{	
			$this->Flash->error('Invalid convention season.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function wizard($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		$this->set('conventionSD', $conventionSD);
		$conventionMeta = $this->getConventionMetaFromSeason($conventionSD);
		$this->set('convention_slug', $conventionMeta['slug']);
		
		global $weekDays;
		$this->set('weekDays', $weekDays);
		$hasRoundRobinColumn = in_array('round_robin_day_distribution_yes_no', (array)$this->Schedulings->getSchema()->columns(), true);
		$this->set('hasRoundRobinColumn', $hasRoundRobinColumn);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->getLatestSchedulingRecord($conventionSD->id, $conventionSD->convention_id, $conventionSD->season_id, $conventionSD->season_year);
		$toHms = function ($value) {
			if ($value === null || $value === '') {
				return '';
			}

			if ($value instanceof \DateTimeInterface) {
				return $value->format('H:i:s');
			}

			$timestamp = strtotime((string)$value);
			if ($timestamp === false) {
				return trim((string)$value);
			}

			return date('H:i:s', $timestamp);
		};

		$looksLikeDefaultTime = function ($value) use ($toHms) {
			return in_array($toHms($value), ['10:00:00', '10:00'], true);
		};
		// Keep wizard values exactly as stored; blank rows should render blank inputs.
		if (!$schedulingD) {
			$schedulings = $this->Schedulings->newEntity([]);
			$schedulings->slug = 'scheduling-conv-season-' . $conventionSD->id . '-' . time();
			$schedulings->conventionseasons_id = $conventionSD->id;
			$schedulings->convention_id = $conventionSD->convention_id;
			$schedulings->season_id = $conventionSD->season_id;
			$schedulings->season_year = $conventionSD->season_year;
			$schedulings->starting_different_time_first_day_yes_no = 0;
			$schedulings->judging_breaks_yes_no = 0;
			$schedulings->sports_day_yes_no = 0;
			$schedulings->sports_day_having_events_after_sport_yes_no = 0;
			if ($hasRoundRobinColumn) {
				$schedulings->round_robin_day_distribution_yes_no = 0;
			}
			$schedulings->created = date('Y-m-d H:i:s');

			if ($this->Schedulings->save($schedulings)) {
				$schedulingD = $this->getLatestSchedulingRecord($conventionSD->id, $conventionSD->convention_id, $conventionSD->season_id, $conventionSD->season_year);
			}
		}
		$this->set('schedulingD', $schedulingD);

		$wizardTimeValues = [
			'normal_starting_time' => '',
			'normal_finish_time' => '',
			'lunch_time_start' => '',
			'lunch_time_end' => '',
			'different_first_day_start_time' => '',
			'different_first_day_end_time' => '',
			'judging_breaks_morning_break_starting_time' => '',
			'judging_breaks_morning_break_finish_time' => '',
			'judging_breaks_afternoon_break_start_time' => '',
			'judging_breaks_afternoon_break_finish_time' => '',
			'sports_day_starting_time' => '',
			'sports_day_finish_time' => '',
			'sports_day_other_starting_time' => '',
			'sports_day_other_finish_time' => '',
		];

		if (!empty($schedulingD) && !empty($schedulingD->id)) {
			$rawScheduling = $this->Schedulings->find()
				->select(array_keys($wizardTimeValues))
				->where(['Schedulings.id' => $schedulingD->id])
				->enableHydration(false)
				->first();

			$valueSource = is_array($rawScheduling) ? $rawScheduling : [];

			$formatTimeValue = function ($value) {
				if ($value === null || $value === '') {
					return '';
				}

				$timestamp = strtotime((string)$value);
				if ($timestamp === false) {
					return '';
				}

				return date('h:i A', $timestamp);
			};

			$coreTimeKeys = [
				'normal_starting_time',
				'normal_finish_time',
				'lunch_time_start',
				'lunch_time_end',
			];

			$hasAnyCoreTime = false;
			foreach ($coreTimeKeys as $coreKey) {
				if (!empty($valueSource[$coreKey])) {
					$hasAnyCoreTime = true;
					break;
				}
			}

			if (!$hasAnyCoreTime) {
				$nonEmptyCoreTimeSql = [];
				foreach ($coreTimeKeys as $key) {
					$nonEmptyCoreTimeSql[] = "(Schedulings.".$key." IS NOT NULL AND Schedulings.".$key." != '')";
				}

				$fallbackRow = $this->Schedulings->find()
					->select(array_merge(['id'], array_keys($wizardTimeValues)))
					->where([
						'Schedulings.conventionseasons_id' => $conventionSD->id,
						'Schedulings.convention_id' => $conventionSD->convention_id,
						'Schedulings.season_id' => $conventionSD->season_id,
						'Schedulings.season_year' => $conventionSD->season_year,
					])
					->andWhere(function ($exp) use ($nonEmptyCoreTimeSql) {
						return $exp->or_($nonEmptyCoreTimeSql);
					})
					->order(['Schedulings.modified' => 'DESC', 'Schedulings.id' => 'DESC'])
					->enableHydration(false)
					->first();

				if (is_array($fallbackRow)) {
					$valueSource = $fallbackRow;
				}
			}

			foreach ($wizardTimeValues as $timeField => $_unused) {
				$sourceValue = array_key_exists($timeField, $valueSource)
					? $valueSource[$timeField]
					: (isset($schedulingD->{$timeField}) ? $schedulingD->{$timeField} : null);
				$wizardTimeValues[$timeField] = $formatTimeValue($sourceValue);
			}
		}

		$this->set('wizardTimeValues', $wizardTimeValues);
			$this->set('wizardBuild', '20260621-direct-time-map-v2');
		
		// Use the schedulingD object directly (don't re-fetch with ->get() which may use cache)
		$schedulings = $schedulingD;
		if (!$schedulings) {
			$schedulings = $this->Schedulings->newEntity([]);
		}
        if ($this->request->is(['post', 'put'])) {
			// Accept both payload shapes used by legacy/modern templates.
			$submittedData = (array)$this->request->getData();
			if (isset($submittedData['Schedulings']) && is_array($submittedData['Schedulings'])) {
				$submittedData = (array)$submittedData['Schedulings'];
			}

			$timeParseErrors = [];
			$normalizeTime = function ($field, $label, $required = false) use (&$submittedData, &$timeParseErrors) {
				$raw = isset($submittedData[$field]) ? trim((string)$submittedData[$field]) : '';

				if ($raw === '') {
					if ($required) {
						$timeParseErrors[] = $label . ' is required.';
					}
					return null;
				}

				$timestamp = strtotime($raw);
				if ($timestamp === false) {
					$timeParseErrors[] = 'Invalid time format for ' . $label . '.';
					return null;
				}

				return date('H:i:s', $timestamp);
			};

			// Parse and normalize core required times.
			$submittedData['normal_starting_time'] = $normalizeTime('normal_starting_time', 'Normal Starting Time', true);
			$submittedData['normal_finish_time'] = $normalizeTime('normal_finish_time', 'Normal Finish Time', true);
			$submittedData['lunch_time_start'] = $normalizeTime('lunch_time_start', 'Lunch Time Start', true);
			$submittedData['lunch_time_end'] = $normalizeTime('lunch_time_end', 'Lunch Time End', true);

			if (!empty($submittedData['starting_different_time_first_day_yes_no'])) {
				$submittedData['different_first_day_start_time'] = $normalizeTime('different_first_day_start_time', 'First Day Start Time', true);
				$submittedData['different_first_day_end_time'] = $normalizeTime('different_first_day_end_time', 'First Day End Time', true);
			} else {
				$submittedData['different_first_day_start_time'] = null;
				$submittedData['different_first_day_end_time'] = null;
			}

			if (!empty($submittedData['judging_breaks_yes_no'])) {
				$submittedData['judging_breaks_morning_break_starting_time'] = $normalizeTime('judging_breaks_morning_break_starting_time', 'Morning Break Starting Time', true);
				$submittedData['judging_breaks_morning_break_finish_time'] = $normalizeTime('judging_breaks_morning_break_finish_time', 'Morning Break Finish Time', true);
				$submittedData['judging_breaks_afternoon_break_start_time'] = $normalizeTime('judging_breaks_afternoon_break_start_time', 'Afternoon Break Start Time', true);
				$submittedData['judging_breaks_afternoon_break_finish_time'] = $normalizeTime('judging_breaks_afternoon_break_finish_time', 'Afternoon Break Finish Time', true);
			} else {
				$submittedData['judging_breaks_morning_break_starting_time'] = null;
				$submittedData['judging_breaks_morning_break_finish_time'] = null;
				$submittedData['judging_breaks_afternoon_break_start_time'] = null;
				$submittedData['judging_breaks_afternoon_break_finish_time'] = null;
			}

			if (!empty($submittedData['sports_day_yes_no'])) {
				$submittedData['sports_day_starting_time'] = $normalizeTime('sports_day_starting_time', 'Sports Day Starting Time', true);
				$submittedData['sports_day_finish_time'] = $normalizeTime('sports_day_finish_time', 'Sports Day Finish Time', true);

				if ($submittedData['sports_day_starting_time'] === '10:00:00') {
					$submittedData['sports_day_starting_time'] = '08:30:00';
				}
				if ($submittedData['sports_day_finish_time'] === '10:00:00') {
					$submittedData['sports_day_finish_time'] = '12:30:00';
				}
			} else {
				$submittedData['sports_day'] = null;
				$submittedData['sports_day_starting_time'] = null;
				$submittedData['sports_day_finish_time'] = null;
			}

			if (!empty($submittedData['sports_day_having_events_after_sport_yes_no'])) {
				$submittedData['sports_day_other_starting_time'] = $normalizeTime('sports_day_other_starting_time', 'Events-After-Sport Starting Time', true);
				$submittedData['sports_day_other_finish_time'] = $normalizeTime('sports_day_other_finish_time', 'Events-After-Sport Finish Time', true);

				if ($submittedData['sports_day_other_starting_time'] === '10:00:00') {
					$submittedData['sports_day_other_starting_time'] = '13:30:00';
				}
				if ($submittedData['sports_day_other_finish_time'] === '10:00:00') {
					$submittedData['sports_day_other_finish_time'] = '17:30:00';
				}
			} else {
				$submittedData['sports_day_other_starting_time'] = null;
				$submittedData['sports_day_other_finish_time'] = null;
			}

			if (!empty($submittedData['start_date'])) {
				$startDateTs = strtotime($submittedData['start_date']);
				if ($startDateTs === false) {
					$timeParseErrors[] = 'Invalid date format for Start Date.';
				} else {
					$submittedData['start_date'] = date('Y-m-d', $startDateTs);
				}
			}

			if (!empty($timeParseErrors)) {
				$this->Flash->error(implode(' ', $timeParseErrors));
				$this->set('schedulings', $schedulings);
				return;
			}

			$getOrNull = function($key) use ($submittedData) {
				return array_key_exists($key, $submittedData) ? $submittedData[$key] : null;
			};
			$roundRobinToggle = !empty($submittedData['round_robin_day_distribution_yes_no']) ? 1 : 0;

			$modifiedNow = date("Y-m-d H:i:s");
			$updatePayload = [
				'start_date' => $getOrNull('start_date'),
				'first_day' => $getOrNull('first_day'),
				'number_of_days' => $getOrNull('number_of_days'),
				'normal_starting_time' => $getOrNull('normal_starting_time'),
				'normal_finish_time' => $getOrNull('normal_finish_time'),
				'lunch_time_start' => $getOrNull('lunch_time_start'),
				'lunch_time_end' => $getOrNull('lunch_time_end'),
				'starting_different_time_first_day_yes_no' => !empty($submittedData['starting_different_time_first_day_yes_no']) ? 1 : 0,
				'different_first_day_start_time' => $getOrNull('different_first_day_start_time'),
				'different_first_day_end_time' => $getOrNull('different_first_day_end_time'),
				'judging_breaks_yes_no' => !empty($submittedData['judging_breaks_yes_no']) ? 1 : 0,
				'judging_breaks_morning_break_starting_time' => $getOrNull('judging_breaks_morning_break_starting_time'),
				'judging_breaks_morning_break_finish_time' => $getOrNull('judging_breaks_morning_break_finish_time'),
				'judging_breaks_afternoon_break_start_time' => $getOrNull('judging_breaks_afternoon_break_start_time'),
				'judging_breaks_afternoon_break_finish_time' => $getOrNull('judging_breaks_afternoon_break_finish_time'),
				'sports_day_yes_no' => !empty($submittedData['sports_day_yes_no']) ? 1 : 0,
				'sports_day' => $getOrNull('sports_day'),
				'sports_day_starting_time' => $getOrNull('sports_day_starting_time'),
				'sports_day_finish_time' => $getOrNull('sports_day_finish_time'),
				'sports_day_having_events_after_sport_yes_no' => !empty($submittedData['sports_day_having_events_after_sport_yes_no']) ? 1 : 0,
				'sports_day_other_starting_time' => $getOrNull('sports_day_other_starting_time'),
				'sports_day_other_finish_time' => $getOrNull('sports_day_other_finish_time'),
				'round_robin_day_distribution_yes_no' => !empty($submittedData['round_robin_day_distribution_yes_no']) ? 1 : 0,
				'modified' => $modifiedNow,
			];

			$safeUpdatePayload = $this->filterSchedulingsPayloadBySchema($updatePayload);
			if (empty($safeUpdatePayload)) {
				$this->Flash->error('Could not save scheduling wizard settings because required database columns are missing.');
				return;
			}

			$saveOk = false;
			try {
				$saveOk = $this->Schedulings->updateAll($safeUpdatePayload, ['id' => $schedulings->id]);
			} catch (\Throwable $exception) {
				$this->log('Wizard save failed for convention season '.$convention_season_slug.': '.$exception->getMessage(), 'error');
				$this->Flash->error('Could not save scheduling wizard settings. Please verify database schema/times and try again.');
				return;
			}
			if ($saveOk !== false)
			{
				try {
					$this->Schedulings->getConnection()->execute(
						'UPDATE schedulings SET round_robin_day_distribution_yes_no = :flag WHERE id = :id',
						[
							'flag' => $roundRobinToggle,
							'id' => (int)$schedulings->id,
						],
						[
							'flag' => 'integer',
							'id' => 'integer',
						]
					);
				} catch (\Throwable $roundRobinSaveException) {
					// Ignore when column is unavailable on older schemas.
				}

				$savedRecord = $this->Schedulings->get($schedulings->id);
				$this->syncSchedulingRecordDuplicates($savedRecord, $safeUpdatePayload);

				// Schedule settings changed: clear generated timings so rerun uses latest wizard times.
				$this->clearSchedulingtimings($convention_season_slug);
				$this->Flash->success('Data saved successfully. Existing generated schedules were cleared. Please click Start Scheduling to regenerate with the new times.');
				return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			}

			$this->Flash->error('Could not save scheduling wizard settings. Please try again and check file permissions/database connection.');
        }
        $this->set('schedulings', $schedulings);
		
	}

	private function filterSchedulingsPayloadBySchema(array $payload)
	{
		$columns = $this->Schedulings->getSchema()->columns();
		$allowed = array_fill_keys($columns, true);

		return array_intersect_key($payload, $allowed);
	}
	
	public function schedulecategory($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Schedule category');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		$this->set('conventionSD', $conventionSD);
		
		$conventionMeta = $this->getConventionMetaFromSeason($conventionSD);
		$this->set('convention_slug', $conventionMeta['slug']);
		
		/* Category :: 1 */
		// group_event = yes || event_kind_id = Sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventsC1 = array();
		$condC1 = array();
		$condC1[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC1 = $this->Conventionseasonevents->find()->where($condC1)->all();
		foreach($eventsC1 as $eventc1)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc1->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '1' && $eventD->event_kind_id == 'Sequential' && $eventD->has_to_be_consecutive == '1')
			{
				$arrEventsC1[] = $eventc1->event_id;
			}
		}
		$this->set('arrEventsC1', $arrEventsC1);
		
		
		
		
		
		/* Category :: 2 */
		// group_event = no || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC2 = array();
		$condC2 = array();
		$condC2[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC2 = $this->Conventionseasonevents->find()->where($condC2)->all();
		foreach($eventsC2 as $eventc2)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc2->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '0' && $eventD->event_kind_id == 'Elimination' && $eventD->has_to_be_consecutive == '0')
			{
				$arrEventsC2[] = $eventc2->event_id;
			}
		}
		$this->set('arrEventsC2', $arrEventsC2);
		//$this->prx($arrEventsC2);
		
		
		/* Category :: 3 - this is similar to category 2 */
		// group_event = yes || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC3 = array();
		$condC3 = array();
		$condC3[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC3 = $this->Conventionseasonevents->find()->where($condC3)->all();
		foreach($eventsC3 as $eventc3)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc3->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '1' && $eventD->event_kind_id == 'Elimination' && $eventD->has_to_be_consecutive == '0')
			{
				$arrEventsC3[] = $eventc3->event_id;
			}
		}
		$this->set('arrEventsC3', $arrEventsC3);
		//$this->prx($arrEventsC3);
		
		
		/* Category :: 4 - this is similar to category 1 */
		// group_event = no || event_kind_id = Sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventsC4 = array();
		$condC4 = array();
		$condC4[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC4 = $this->Conventionseasonevents->find()->where($condC4)->all();
		foreach($eventsC4 as $eventc4)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc4->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '0' && $eventD->event_kind_id == 'Sequential' && $eventD->has_to_be_consecutive == '1')
			{
				$arrEventsC4[] = $eventc4->event_id;
			}
		}
		$this->set('arrEventsC4', $arrEventsC4);
		//$this->prx($arrEventsC4);
		
		
    }

	public function resolveconflicts($convention_season_slug=null) {
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		if (!$conventionSD) {
			$this->Flash->error('Invalid convention season.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck']);
		}

		$schedulingD = $this->getSchedulingByConventionSeasonId(
			$conventionSD->id,
			$conventionSD->convention_id,
			$conventionSD->season_id,
			$conventionSD->season_year
		);

		if(!empty($schedulingD) && !empty($schedulingD->conflict_user_ids))
		{
			$userIDSConflict = explode(",",$schedulingD->conflict_user_ids);
			shuffle($userIDSConflict);
			$userId = $userIDSConflict[0];

			$resolveConflicts = false;
			$attempts = 0;
			$maxAttempts = 200;
			do {
				$attempts++;
				if ($attempts > $maxAttempts) {
					$this->Flash->error('Scheduling conflict resolver stopped after too many attempts for a user. Please review schedule constraints and zero-duration slots.');
					break;
				}

				$madeProgress = false;
				$userConflictRecords = $this->userConflictRecordsByUserId($convention_season_slug, $userId);

				if (empty($userConflictRecords))
				{
					$nextUserIDSConflicts = array_filter($userIDSConflict, function($item) use ($userId) {
						return $item !== $userId;
					});

					$this->updateSchedulingConflictField($schedulingD->id, 'conflict_user_ids', array_values($nextUserIDSConflicts));
				}

				foreach ($userConflictRecords as $userConflictRecord)
				{
					$recordId = $userConflictRecord['id'];
					$base_start_time = date("H:i:s",strtotime($userConflictRecord['start_time']));
					$base_finish_time = date("H:i:s",strtotime($userConflictRecord['finish_time']));
					$base_sch_date_time = date("Y-m-d H:i:s",strtotime($userConflictRecord['sch_date_time']));

					foreach($userConflictRecord['conflicts'] as $conflict)
					{
						$originalStart = $conflict['start_time'];
						$originalFinish = $conflict['finish_time'];
						$originalDateTime = $conflict['sch_date_time'];

						$resolveConflict = $this->nextBookings($convention_season_slug,$conflict, $base_start_time, $base_finish_time, $base_sch_date_time,$recordId);

						$recordId = $resolveConflict['id'];
						$start_time = $resolveConflict['start_time'];
						$finish_time = $resolveConflict['finish_time'];
						$sch_date_time = $resolveConflict['sch_date_time'];

						if (
							$start_time !== $originalStart ||
							$finish_time !== $originalFinish ||
							$sch_date_time !== $originalDateTime
						) {
							$madeProgress = true;
						}

						$this->Schedulingtimings->updateAll(
						[
							'start_time' => $start_time,
							'finish_time' => $finish_time,
							'sch_date_time' => $sch_date_time,
							'modified' => date('Y-m-d H:i:s'),
						],
						[
							"id" => $recordId,
						]
						);

						$nextUserIDSConflicts = array_filter($userIDSConflict, function($item) use ($userId) {
							return $item !== $userId;
						});

						$this->updateSchedulingConflictField($schedulingD->id, 'conflict_user_ids', array_values($nextUserIDSConflicts));

						$base_start_time = $start_time;
						$base_finish_time = $finish_time;
						$base_sch_date_time = $sch_date_time;
					}
				}

				$userConflictRecords = $this->userConflictRecordsByUserId($convention_season_slug, $userId);
				$resolveConflicts = !empty($userConflictRecords) && $madeProgress;

				if (!empty($userConflictRecords) && !$madeProgress) {
					$this->Flash->error('Some conflicts could not be auto-resolved due to constraints. Please review scheduling windows/tweaks and conflicting entries.');
				}

			} while ($resolveConflicts);

			$this->Flash->success('Conflict resolved successfully.');
		}

		return $this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug]);
	}

	public function resolveconflictsgroup($convention_season_slug=null) {
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		if (!$conventionSD) {
			$this->Flash->error('Invalid convention season.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck']);
		}

		$schedulingD = $this->getSchedulingByConventionSeasonId(
			$conventionSD->id,
			$conventionSD->convention_id,
			$conventionSD->season_id,
			$conventionSD->season_year
		);

		if(!empty($schedulingD) && !empty($schedulingD->conflict_user_ids_group))
		{
			$schIDSConflict = explode(",",$schedulingD->conflict_user_ids_group);
			shuffle($schIDSConflict);
			$schedulingId = $schIDSConflict[0];

			$schedulingTimingsD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $schedulingId])->first();

			if (!$schedulingTimingsD) {
				$nextSchIDSConflict = array_values(array_diff($schIDSConflict, [$schedulingId]));
				$this->updateSchedulingConflictField($schedulingD->id, 'conflict_user_ids_group', $nextSchIDSConflict);
				return $this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug]);
			}

			$groupUserIds = array_filter(explode(',', (string)$schedulingTimingsD->group_name_user_ids), 'strlen');
			$opponentIds = array_filter(explode(',', (string)$schedulingTimingsD->group_name_opponent_user_ids), 'strlen');
			$allUserIds = array_values(array_merge($groupUserIds, $opponentIds));

			$base_start_time = $schedulingTimingsD->start_time;
			$base_finish_time = $schedulingTimingsD->finish_time;
			$base_sch_date_time = $schedulingTimingsD->sch_date_time;

			$resolveConflict = $this->findNextTime($schedulingTimingsD, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds);

			$recordId = $resolveConflict->id;
			$start_time = $resolveConflict->start_time;
			$finish_time = $resolveConflict->finish_time;
			$sch_date_time = $resolveConflict->sch_date_time;

			$this->Schedulingtimings->updateAll(
				[
					'start_time' => $start_time,
					'finish_time' => $finish_time,
					'sch_date_time' => $sch_date_time,
					'modified' => date('Y-m-d H:i:s'),
				],
				[
					"id" => $recordId,
				]
			);

			$nextSchIDSConflict = array_values(array_diff($schIDSConflict, [$recordId]));
			$this->updateSchedulingConflictField($schedulingD->id, 'conflict_user_ids_group', $nextSchIDSConflict);
		}

		return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
	}
	
	
	public function reports($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		$this->set('conventionSD', $conventionSD);
		$conventionMeta = $this->getConventionMetaFromSeason($conventionSD);
		$this->set('convention_slug', $conventionMeta['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->getLatestSchedulingRecord($conventionSD->id, $conventionSD->convention_id, $conventionSD->season_id, $conventionSD->season_year);
		$this->set('schedulingD', $schedulingD);
		
    }
	
	public function overwritetimings($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Overwrite Timings');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		
		$this->set('conventionSD', $conventionSD);
		$conventionMeta = $this->getConventionMetaFromSeason($conventionSD);
		$this->set('convention_slug', $conventionMeta['slug']);
		
		global $weekDays;
		$this->set('weekDays', $weekDays);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->getLatestSchedulingRecord($conventionSD->id, $conventionSD->convention_id, $conventionSD->season_id, $conventionSD->season_year);
		$this->set('schedulingD', $schedulingD);
		$this->set('schedulings', $schedulingD);
		
		// Nathan provided these 3 events for Overwrite
		/* Spelling U16 - 003--3   Spelling OPEN - 053--11   Bible Memory OPEN - 1056--343 */
		$eventIDArr = array(343,11,3);
		
		// Now check if these events are chosen for this convention season
		
		$finalEventArr = array();
		
		foreach($eventIDArr as $event_id)
		{
			$checkEventCS = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id,'Conventionseasonevents.event_id' => $event_id])->contain(["Events"])->first();
			if($checkEventCS)
			{
				$condEVT = array();
				$condEVT[] = "(Eventsubmissions.conventionseason_id = '".$conventionSD->id."' AND Eventsubmissions.event_id = '".$event_id."' AND Eventsubmissions.event_id_number = '".$checkEventCS->Events['event_id_number']."')";
				$condEVT[] = "(Eventsubmissions.convention_id = '".$conventionSD->convention_id."' AND Eventsubmissions.season_id = '".$conventionSD->season_id."' AND Eventsubmissions.season_year = '".$conventionSD->season_year."')";

				$entryCount = $this->Eventsubmissions->find()->where($condEVT)->count();
				$studentLabel = ($entryCount === 1) ? 'student registered' : 'students registered';

				$finalEventArr[$event_id] = $checkEventCS->Events['event_name'].' '.$entryCount.' '.$studentLabel;
			}
		}
		$this->set('finalEventArr', $finalEventArr);
		
		
        if ($this->request->is(['post']))
		{
            $event_id			= $this->request->getData()['Schedulings']['event_id'];
            $overwrite_date		= $this->request->getData()['Schedulings']['overwrite_date'];
            $overwrite_time		= $this->request->getData()['Schedulings']['overwrite_time'];
			$max_students		= (int)$this->request->getData()['Schedulings']['max_students'];

			// Default batching for spelling events if max is not provided.
			if($max_students <= 0)
			{
				if(in_array((int)$event_id, [3,11], true))
				{
					$max_students = 30;
				}
				else
				{
					$max_students = 1;
				}
			}
			
			
			
			
			// now get event details
            $eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
			
			// Now calculate start time and end time based on event data
			$start_date 		= date("Y-m-d",strtotime($overwrite_date));
			$start_time 		= date("H:i:s",strtotime($overwrite_time));
			
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
			
			/* echo $start_date;
			echo '<hr>';
			echo $start_time;
			echo '<hr>';
			echo $finish_time; */
			
			
			// Now fetch students of this event for this convention season
			$cntrTotRec = 0;
			$cntrSc = 0;
			$schedulingtimings = $this->Schedulingtimings->find()->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.event_id' => $event_id])->order(["Schedulingtimings.id"=>"ASC"])->all();
			foreach($schedulingtimings as $schrecord)
			{
				// Update record
				$this->Schedulingtimings->updateAll(
				[
					'sch_date_time' 	=> $start_date.' '.$start_time,
					'day' 				=> date("l",strtotime($start_date)),
					'start_time' 		=> $start_time,
					'finish_time' 		=> $finish_time,
					'modified' 			=> date("Y-m-d H:i:s"),
				]
				, 
				[
					"id" => $schrecord->id
				]
				);
				
				/* echo 'sch_date_time='.$start_date.' '.$start_time;echo '----';
				echo 'day='.date("l",strtotime($start_date));echo '----';
				echo 'start_time='.$start_time;echo '----';
				echo 'finish_time='.$finish_time;echo '----';
				echo 'counter='.$cntrSc;echo '----';
				
				echo '<hr>'; */
				
				$cntrSc++;
				$cntrTotRec++;
				
				// check counter
				if($cntrSc == $max_students)
				{
					// change time
					$start_time 	= date("H:i:s", strtotime('+1 minutes', strtotime($finish_time)));
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					
					$cntrSc = 0;
				}
			}
			
			if($cntrTotRec>0)
			{
				$this->Flash->success('Scheduling date/time overwrite successfully. Total '.$cntrTotRec.' record(s) modified.');
			}
			else
			{
				$this->Flash->error('Sorry, no record updated.');
			}
			
            $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			
			
        }
		
    }

		private function getConventionSeasonBySlug($conventionSeasonSlug)
		{
			return $this->Conventionseasons
				->find()
				->where(['Conventionseasons.slug' => $conventionSeasonSlug])
				->contain(['Conventions'])
				->first();
		}

		private function getSchedulingByConventionSeasonId($conventionSeasonId, $conventionId, $seasonId, $seasonYear)
		{
			return $this->getLatestSchedulingRecord($conventionSeasonId, $conventionId, $seasonId, $seasonYear);
		}

		private function updateSchedulingConflictField($schedulingId, $fieldName, array $ids)
		{
			$this->Schedulings->updateAll(
				[
					$fieldName => count($ids) ? implode(',', $ids) : null,
					'modified' => date('Y-m-d H:i:s'),
				],
				[
					'id' => $schedulingId,
				]
			);
		}

		private function updateSchedulingForSeason($conventionSeasonId, array $fields)
		{
			$fields['modified'] = date('Y-m-d H:i:s');
			$allowedColumns = array_flip($this->Schedulings->getSchema()->columns());
			$safeFields = array_intersect_key($fields, $allowedColumns);

			if (empty($safeFields)) {
				return;
			}

			$this->Schedulings->updateAll($safeFields, ['conventionseasons_id' => $conventionSeasonId]);
		}

		private function isValidTimeRange($startTime, $endTime): bool
		{
			if (empty($startTime) || empty($endTime)) {
				return false;
			}

			$start = strtotime((string)$startTime);
			$end = strtotime((string)$endTime);

			if ($start === false || $end === false) {
				return false;
			}

			return $start < $end;
		}

}

?>
