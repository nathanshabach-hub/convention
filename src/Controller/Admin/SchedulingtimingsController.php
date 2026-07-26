<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

use Cake\Datasource\ConnectionManager;

class SchedulingtimingsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
    public $components = array('PImage');
private $scheduleWindowWarningShown = false;

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize(): void {
        parent::initialize();
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
		}
		
		$this->Conventionseasons = $this->loadModel('Conventionseasons');
		$this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
		$this->Events = $this->loadModel('Events');
		$this->Conventionregistrations = $this->loadModel('Conventionregistrations');
		$this->Crstudentevents = $this->loadModel('Crstudentevents');
		$this->Schedulingtimings = $this->loadModel('Schedulingtimings');
		$this->Conventionseasonroomevents = $this->loadModel('Conventionseasonroomevents');
		$this->Schedulings = $this->loadModel('Schedulings');
		$this->Conventionregistrationstudents = $this->loadModel('Conventionregistrationstudents');
		$this->Schedulingeventtweaks = $this->loadModel('Schedulingeventtweaks');
		$this->Eventsubmissions = $this->loadModel('Eventsubmissions');
    }
	
	
	public function viewscheduling($convention_season_slug=null,$scheduling_category=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('scheduling_category', $scheduling_category);
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		//$this->prx($conventionSD);
		
		$this->set('conventionSD', $conventionSD);
		
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to list all schedulings
		$baseConditions = [
			'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
			'Schedulingtimings.convention_id' => $conventionSD->convention_id,
			'Schedulingtimings.season_id' => $conventionSD->season_id,
			'Schedulingtimings.season_year' => $conventionSD->season_year,
			'Schedulingtimings.schedule_category' => $scheduling_category,
		];

		$scheduledConditions = $baseConditions + [
			'Schedulingtimings.day IS NOT' => null,
			'Schedulingtimings.start_time IS NOT' => null,
			'Schedulingtimings.finish_time IS NOT' => null,
			'Schedulingtimings.sch_date_time IS NOT' => null,
			'Schedulingtimings.day !=' => '',
			'Schedulingtimings.start_time !=' => '',
			'Schedulingtimings.finish_time !=' => '',
		];

		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($scheduledConditions)
			->contain(["Events","Users","Conventionrooms","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC", "Schedulingtimings.room_id" => "ASC", "Schedulingtimings.start_time" => "ASC", "Schedulingtimings.id" => "ASC"])
			->all();

		$unscheduledCount = $this->Schedulingtimings->find()
			->where($baseConditions)
			->andWhere(function ($exp) {
				return $exp->or_([
					['Schedulingtimings.day IS' => null],
					['Schedulingtimings.start_time IS' => null],
					['Schedulingtimings.finish_time IS' => null],
					['Schedulingtimings.sch_date_time IS' => null],
					['Schedulingtimings.day' => ''],
					['Schedulingtimings.start_time' => ''],
					['Schedulingtimings.finish_time' => ''],
				]);
			})
			->count();

		if ($unscheduledCount > 0) {
			$this->Flash->warning('There are '.$unscheduledCount.' unscheduled row(s) in this category that are hidden from this view because day/start/finish are empty. Increase scheduling capacity or adjust tweaks, then rerun Start Scheduling to place them.');
		}
		$this->set('schedulingTimingsList', $schedulingTimingsList);
		$this->set('pendingEventsToRoomsList', []);
    }

    public function startschedulec1($convention_season_slug=null) {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');
        
		
        $this->set('convention_season_slug', $convention_season_slug);
		$this->request->getSession()->delete('Scheduling.windowWarningShown');
		$this->scheduleWindowWarningShown = false;
		$this->clearSchedulingtimings($convention_season_slug);
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		// to get details of schedule timings
		$schedulingsD = $this->resolveSchedulingWizardRecord($conventionSD);
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		$first_day 				= $schedulingsD->first_day;
		$normal_starting_time 	= $this->normalizeWizardTime($schedulingsD->normal_starting_time);
		$normal_finish_time 	= $this->normalizeWizardTime($schedulingsD->normal_finish_time);
		
		$lunch_time_start 		= $this->normalizeWizardTime($schedulingsD->lunch_time_start);
		$lunch_time_end 		= $this->normalizeWizardTime($schedulingsD->lunch_time_end);

		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = $this->normalizeWizardTime($schedulingsD->different_first_day_start_time);
			$different_first_day_end_time 	= $this->normalizeWizardTime($schedulingsD->different_first_day_end_time);
		}
		
		\Cake\Log\Log::error('C1 parsed window season '.$conventionSD->id.': start_date_raw='.(string)$schedulingsD->start_date.', first_day='.(string)$first_day.', normal_start_raw='.(string)$schedulingsD->normal_starting_time.', normal_finish_raw='.(string)$schedulingsD->normal_finish_time.', lunch_start_raw='.(string)$schedulingsD->lunch_time_start.', lunch_end_raw='.(string)$schedulingsD->lunch_time_end.', normal_start_parsed='.$normal_starting_time.', normal_finish_parsed='.$normal_finish_time.', lunch_start_parsed='.$lunch_time_start.', lunch_end_parsed='.$lunch_time_end.', number_of_days='.(string)$schedulingsD->number_of_days);
		
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
		// group_event = yes || event_kind_id = sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventC1 = array();
		$arrEventC1[] = 0;
		$condEventList = array();
		$condEventList[] = "(Events.needs_schedule = '1' AND Events.group_event_yes_no = '1' AND Events.event_kind_id = 'Sequential' AND Events.has_to_be_consecutive = '1')";
		$eventList = $this->Events->find()->where($condEventList)->select(['id','event_id_number'])->all();
		//$this->prx($eventList);
		foreach($eventList as $eventdata)
		{
			$arrEventC1[] = $eventdata->id;
		}
		$arrEventC1Implode = implode(",",$arrEventC1);
		//$this->prx($arrEventC1);
		//$arrEventC1Implode = '344';
		
		
		
		// 1. Fetch all events that is required scheduling for this convention season
		$condEVCS = array();
		$condEVCS[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		$condEVCS[] = "(Conventionseasonevents.event_id IN ($arrEventC1Implode))"; // these are for event_id_number 870, 871 = 352, 353
		//$condEVCS[] = "(Conventionseasonevents.event_id IN (352,353))"; // these are for event_id_number 870, 871 = 352, 353
		
		$allEventsCS = $this->Conventionseasonevents->find()->where($condEVCS)->all();
		$c1EventsWithoutRooms = 0;
		$c1EventsWithoutGroups = 0;
		$c1RecordsCreated = 0;
		$c1UngroupedFallbackCount = 0;
		$c1EventsUsingSeasonRoomFallback = 0;
		$c1SaveFailures = 0;
		$c1FirstSaveError = '';
		$c1EventsWithGroups = 0;
		$c1AttemptedRows = 0;
		$c1WindowExceededEvents = 0;
		$c1SkippedWithoutSubmission = 0;
		$submissionExistsCache = [];

		$seasonFallbackRoomIds = [];
		$condSeasonRooms = array();
		$condSeasonRooms[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
		$seasonRoomRows = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condSeasonRooms)->all();
		foreach($seasonRoomRows as $seasonRoomRow)
		{
			$roomId = (int)$seasonRoomRow->room_id;
			if($roomId > 0 && !in_array($roomId, $seasonFallbackRoomIds, true))
			{
				$seasonFallbackRoomIds[] = $roomId;
			}
		}
		//$this->prx($condEVCS);
		foreach($allEventsCS as $eventcs)
		{
			$mainArrForEvent = array();
			// to check if this event require schedule
			
			$eventD = $this->Events->find()->where(['Events.id' => $eventcs->event_id])->first();
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			$eventIDCS = $eventD->id;
			// for now we are doing schedulings for event_id_number 870,871 for testing
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$eventIDCS."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$eventIDCS.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}

			if(!count((array)$roomArrCSEvent) && count((array)$seasonFallbackRoomIds))
			{
				$roomArrCSEvent = $seasonFallbackRoomIds;
				$c1EventsUsingSeasonRoomFallback++;
			}
			//$this->prx($roomArrCSEvent);
			
			// Check if there's only one room, then duplicate
			/* if (count($roomArrCSEvent) === 1) {
				// Duplicate the same record up to 4 times
				while (count($roomArrCSEvent) < 4) {
					$roomArrCSEvent[] = $roomArrCSEvent[0];
				}
			} */
			
			
			
			// ---- Load event tweaks (A: pinned day, B: pinned room, C: pinned start time) ----
			$eventTweak = $this->Schedulingeventtweaks->find()
				->where(['Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
				         'Schedulingeventtweaks.event_id'             => $eventIDCS])
				->first();
			// B: override room list with the pinned room
			if ($eventTweak && $eventTweak->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak->pinned_room_id];
			}
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent)>0)
			{	
				// First to fetch conv registrations
				$condCR = array();
				$condCR[] = "(Conventionregistrations.conventionseason_id = '".$conventionSD->id."' AND Conventionregistrations.convention_id = '".$conventionSD->convention_id."')";
				$conventionRegistrations = $this->Conventionregistrations->find()->where($condCR)->all();
				foreach($conventionRegistrations as $convreg)
				{
					//now fetch groups for this CR
					$condCRSTEV = array();
					$condCRSTEV[] = "(Crstudentevents.conventionseason_id = '".$conventionSD->id."' AND Crstudentevents.convention_id = '".$conventionSD->convention_id."')";
					$condCRSTEV[] = "(Crstudentevents.conventionregistration_id = '".$convreg->id."' AND Crstudentevents.event_id = '".$eventIDCS."')";
					$convRegSTEV = $this->Crstudentevents->find()->where($condCRSTEV)->select(['group_name'])->all();
					//$this->prx($convRegSTEV);
					if($convRegSTEV)
					{
						// if any group exists, then push it to array
						foreach($convRegSTEV as $convregstev)
						{
							// now create a variable with combination 
							// format is conventionseasons_id==convention_id==season_id==season_year==conventionregistration_id==event_id==event_id_number==user_id==group_name
							
							$varEventCombination 	= $conventionSD->id."==";
							$varEventCombination 	.= $conventionSD->convention_id."==";
							$varEventCombination 	.= $conventionSD->season_id."==";
							$varEventCombination 	.= $conventionSD->season_year."==";
							$varEventCombination 	.= $convreg->id."==";
							$varEventCombination	.= $eventIDCS."==";
							$varEventCombination 	.= $eventD->event_id_number."==";
							$varEventCombination 	.= $convreg->user_id."==";

							$groupName = trim((string)($convregstev->group_name ?? ''));
							if ($groupName === '') {
								$groupName = 'Ungrouped';
								$c1UngroupedFallbackCount++;
							}

							$submissionKey = $convreg->id.'|'.$eventIDCS.'|'.$convreg->user_id.'|'.$groupName;
							if (!array_key_exists($submissionKey, $submissionExistsCache)) {
								$submissionCount = $this->Eventsubmissions->find()->where([
									'Eventsubmissions.conventionregistration_id' => $convreg->id,
									'Eventsubmissions.conventionseason_id' => $conventionSD->id,
									'Eventsubmissions.convention_id' => $conventionSD->convention_id,
									'Eventsubmissions.season_id' => $conventionSD->season_id,
									'Eventsubmissions.season_year' => $conventionSD->season_year,
									'Eventsubmissions.event_id' => $eventIDCS,
									'Eventsubmissions.user_id' => $convreg->user_id,
									'Eventsubmissions.group_name' => $groupName,
								])->count();
								$submissionExistsCache[$submissionKey] = $submissionCount > 0;
							}

							if (!$submissionExistsCache[$submissionKey]) {
								$c1SkippedWithoutSubmission++;
								continue;
							}
							$varEventCombination 	.= $groupName;
							
							if(!in_array($varEventCombination,(array)$mainArrForEvent))
							{
								$mainArrForEvent[] = $varEventCombination;
							}
						}
					}
				}
			
			
				// now define timings for schedule for this event
			
				//echo 'eeee';
				//$this->prx($mainArrForEvent);
			
				if(count((array)$mainArrForEvent))
				{
					$c1EventsWithGroups++;
					$windowExceeded = false;

					$eventBandsC1 = $this->buildCategory4EventBands($schedulingsD, $eventTweak);
					if (count($eventBandsC1) === 0) {
						$c1WindowExceededEvents++;
						$this->flashConventionWindowExceeded($schedulingsD);
						continue;
					}

					shuffle($mainArrForEvent);
					$pendingRows = array_values($mainArrForEvent);

					$slotGuard = 0;
					foreach ($eventBandsC1 as $eventBandC1) {
						if (count($pendingRows) === 0) {
							break;
						}

						$roomCursors = [];
						foreach ($roomArrCSEvent as $roomID) {
							$roomCursor = $this->getRoomBandCursorTime($conventionSD, (int)$roomID, $eventBandC1['date'], $eventBandC1['start']);
							if ($roomCursor !== null && strtotime($roomCursor) < strtotime($eventBandC1['end'])) {
								$roomCursors[(int)$roomID] = $roomCursor;
							}
						}

						while (count($pendingRows) > 0 && count($roomCursors) > 0)
						{
							$slotGuard++;
							if ($slotGuard > 10000) {
								$this->Flash->warning('Category 1 scheduler stopped early for event '.$eventD->event_id_number.' to avoid an infinite loop while placing groups.');
								break 2;
							}

							asort($roomCursors);
							$roomID = (int)array_key_first($roomCursors);
							$cursorStart = $roomCursors[$roomID];
							$cursorFinish = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($cursorStart)));

							if (strtotime($cursorFinish) > strtotime($eventBandC1['end'])) {
								unset($roomCursors[$roomID]);
								continue;
							}

							$bandDayNumber = $this->getConventionDayNumber($start_date, $eventBandC1['date']);
							$c1AttemptedRows++;
							$validSlot = $this->findValidSlot($cursorStart, $cursorFinish, $eventBandC1['day'], $eventBandC1['date'], $bandDayNumber, $eventBandC1['start'], $eventBandC1['end'], $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$eventIDCS);
							if (!empty($validSlot['window_exhausted'])) {
								$windowExceeded = true;
								$this->flashConventionWindowExceeded($schedulingsD);
								break 2;
							}

							if (!$this->isValidSlotInsideBand($eventBandC1, $validSlot)) {
								unset($roomCursors[$roomID]);
								continue;
							}

							$rowPayload = array_shift($pendingRows);
							$stData = explode("==", (string)$rowPayload);
							$slotStartDate = $validSlot['schStartDate'];
							$slotStartTime = $validSlot['start_time'];
							$slotFinishTime = $validSlot['finish_time'];
							$userOverlapCursorTime = $this->getUserOverlapCursorTime($conventionSD, (int)$stData[7], $slotStartDate, $slotStartTime, $slotFinishTime);
							if ($userOverlapCursorTime !== null) {
								array_unshift($pendingRows, $rowPayload);
								$roomCursors[$roomID] = $userOverlapCursorTime;
								if (strtotime($roomCursors[$roomID]) >= strtotime($eventBandC1['end'])) {
									unset($roomCursors[$roomID]);
								}
								continue;
							}

							$fetchUserType = $this->fetchUserType($stData[7]);

							$schedulingtimings = $this->Schedulingtimings->newEntity([]);
							$dataST = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

							$dataST->schedule_category				= 1;
							$dataST->conventionseasons_id			= $stData[0];
							$dataST->convention_id					= $stData[1];
							$dataST->season_id						= $stData[2];
							$dataST->season_year					= $stData[3];
							$dataST->conventionregistration_id 		= $stData[4];
							$dataST->event_id 						= $stData[5];
							$dataST->event_id_number 				= $stData[6];
							$dataST->user_id 						= $stData[7];
							$dataST->group_name 					= $stData[8];
							$dataST->room_id 						= $roomID;
							$dataST->day 							= $eventBandC1['day'];
							$dataST->start_time 					= $slotStartTime;
							$dataST->finish_time 					= $slotFinishTime;
							$dataST->created 						= date('Y-m-d H:i:s');
							$dataST->modified 						= date('Y-m-d H:i:s');
							$dataST->user_type 						= $fetchUserType;
							$dataST->sch_date_time 					= $slotStartDate.' '.date("H:i:s", strtotime($slotStartTime));

							$resultST = $this->Schedulingtimings->save($dataST);
							if($resultST)
							{
								$c1RecordsCreated++;
							}
							else
							{
								$c1SaveFailures++;
								if($c1FirstSaveError === '')
								{
									$c1FirstSaveError = json_encode($dataST->getErrors());
								}
							}

							$roomCursors[$roomID] = $slotFinishTime;
							if (strtotime($roomCursors[$roomID]) >= strtotime($eventBandC1['end'])) {
								unset($roomCursors[$roomID]);
							}
						}
					}

					if (count($pendingRows) > 0 && !$windowExceeded) {
						$windowExceeded = true;
						$this->flashConventionWindowExceeded($schedulingsD);
					}

					if ($windowExceeded) {
						$c1WindowExceededEvents++;
						continue;
					}
				}
				else
				{
					$c1EventsWithoutGroups++;
				}
			
			}
			else
			{
				$c1EventsWithoutRooms++;
			}
		}

		if($c1RecordsCreated === 0 && count((array)$allEventsCS) > 0)
		{
			$this->Flash->warning('Category 1 has no generated schedule rows yet. Possible reasons: rooms not assigned for '.$c1EventsWithoutRooms.' event(s), no grouped participants found for '.$c1EventsWithoutGroups.' event(s), or the configured day window ran out before slots could be assigned.');
		}
		if($c1EventsUsingSeasonRoomFallback > 0)
		{
			$this->Flash->warning('Category 1 used season-level room fallback for '.$c1EventsUsingSeasonRoomFallback.' event(s) because per-event room mappings were missing.');
		}
		if($c1UngroupedFallbackCount > 0)
		{
			$this->Flash->warning('Category 1 used Ungrouped fallback for '.$c1UngroupedFallbackCount.' registration/event entry(ies) where group name was missing.');
		}
		if($c1SkippedWithoutSubmission > 0)
		{
			$this->Flash->warning('Category 1 skipped '.$c1SkippedWithoutSubmission.' registration/event group entry(ies) because no matching event submission was found.');
		}
		if($c1SaveFailures > 0)
		{
			$this->Flash->warning('Category 1 failed to save '.$c1SaveFailures.' row(s). First save error: '.$c1FirstSaveError);
		}

		\Cake\Log\Log::error('C1 summary season '.$conventionSD->id.': events='.count((array)$allEventsCS).', events_with_groups='.$c1EventsWithGroups.', attempted_rows='.$c1AttemptedRows.', window_exceeded_events='.$c1WindowExceededEvents.', created='.$c1RecordsCreated.', save_failures='.$c1SaveFailures.', no_rooms='.$c1EventsWithoutRooms.', no_groups='.$c1EventsWithoutGroups.', season_room_fallback='.$c1EventsUsingSeasonRoomFallback.', ungrouped_fallback='.$c1UngroupedFallbackCount.', skipped_without_submission='.$c1SkippedWithoutSubmission.', first_save_error='.$c1FirstSaveError);
		
		//exit;
		
		//$this->Flash->success($msgSuccess);
		return $this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec2', $convention_season_slug]);
		
    }
	
	
	public function startschedulec2($convention_season_slug=null) {
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		//$this->prx($conventionSD);
		
		// to get details of schedule timings
		$schedulingsD = $this->resolveSchedulingWizardRecord($conventionSD);
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$first_day 				= $schedulingsD->first_day;
		$normal_starting_time 	= $this->normalizeWizardTime($schedulingsD->normal_starting_time);
		$normal_finish_time 	= $this->normalizeWizardTime($schedulingsD->normal_finish_time);
		
		$lunch_time_start 		= $this->normalizeWizardTime($schedulingsD->lunch_time_start);
		$lunch_time_end 		= $this->normalizeWizardTime($schedulingsD->lunch_time_end);
		
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		
		
		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = $this->normalizeWizardTime($schedulingsD->different_first_day_start_time);
			$different_first_day_end_time 	= $this->normalizeWizardTime($schedulingsD->different_first_day_end_time);
		}
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
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
				$arrEventsC2[] = $eventD->id;
			}
		}
		//$this->pr($arrEventsC2);
		
		/* NOW GET STUDENTS FOR EACH EVENT */
		$arrStudentsC2 = array();
		foreach($arrEventsC2 as $event_id_c2)
		{
			$condSTC2 = array();
			$condSTC2[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND   Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
			$condSTC2[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
			
			$condSTC2[] = "(Conventionregistrationstudents.event_ids LIKE '".$event_id_c2."' OR Conventionregistrationstudents.event_ids LIKE '".$event_id_c2.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c2.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c2."')";
			
			$studentsC2 = $this->Conventionregistrationstudents->find()->where($condSTC2)->all();
			
			if($studentsC2)
			{
				foreach($studentsC2 as $studentEV)
				{
					$arrStudentsC2[$event_id_c2][] = $studentEV->student_id;
				}
			}
		}
		//$this->prx($arrStudentsC2);
		

		/* NOW FETCH STUDENTS FOR EACH EVENT AND PERFORM SCHEDULING */
		foreach($arrStudentsC2 as $event_id_c2 => $studentsListC2)
		{	
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c2])->first();
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id_c2."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id_c2.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c2.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c2."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}

			// Apply saved tweaks (pinned day / room / start time) for this elimination event.
			$eventTweak2 = $this->Schedulingeventtweaks->find()
				->where([
					'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
					'Schedulingeventtweaks.event_id' => $event_id_c2
				])
				->first();
			if ($eventTweak2 && $eventTweak2->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak2->pinned_room_id];
			}
			$eventBandsC2 = $this->buildEliminationEventBands($schedulingsD, $eventTweak2);
			$defaultBandC2 = !empty($eventBandsC2) ? $eventBandsC2[0] : null;
			$byeDayC2 = $defaultBandC2['day'] ?? $first_day;
			$byeStartTimeC2 = $defaultBandC2['start'] ?? ($starting_different_time_first_day_yes_no == 1 ? $different_first_day_start_time : $normal_starting_time);
			$byeDateC2 = $defaultBandC2['date'] ?? $start_date;
			//$this->prx($roomArrCSEvent);
			
			// shuffle array
			shuffle($studentsListC2);
			
			$totalStudentsEV 			= count($studentsListC2);
			$totalByePlayer 			= $this->getByePlayerScheduling($totalStudentsEV);
			$arrStudentsForSplice 		= $studentsListC2;
			
			//echo $totalByePlayer;exit;
			
			$match_number = 1;
			/* DEFINE SCHEDULING FOR BYE PLAYERS */
			if($totalByePlayer>0)
			{
				$arrByePlayer 			= array();
				
				// pick number of random players for bye
				for($cntrByeP=0;$cntrByeP<$totalByePlayer;$cntrByeP++)
				{
					// generate a random number from 0 to total count of students
					$randByeNumber 		= rand(0,count($arrStudentsForSplice)-1);
					$byeStudentID 		= $arrStudentsForSplice[$randByeNumber];
					$arrByePlayer[] 	= $byeStudentID;
					array_splice($arrStudentsForSplice, $randByeNumber, 1);
					
					/* Here we will check that this user_id is School Or student 
					School means its a group event
					Student means it's an individual event
					*/
					$fetchUserType = $this->fetchUserType($byeStudentID);
					
					//now save bye player in database, opponent of bye player id will be 0
					$schedulingtimings = $this->Schedulingtimings->newEntity([]);
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 2;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $event_id_c2;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= $byeStudentID;
					$dataBye->group_name 					= NULL;
					$dataBye->room_id 						= $roomArrCSEvent[0];
					$dataBye->day 							= $byeDayC2;
					$dataBye->start_time 					= $byeStartTimeC2;
					$dataBye->finish_time 					= $byeStartTimeC2;
					$dataBye->user_id_opponent 				= 0;
					$dataBye->round_number 					= 1;
					$dataBye->match_number 					= $match_number;
					$dataBye->is_bye 						= 1;
					$dataBye->created 						= date('Y-m-d H:i:s');
					
					$dataBye->sch_date_time 				= $byeDateC2.' '.date("H:i:s", strtotime($dataBye->start_time));
					
					$dataBye->user_type 					= $fetchUserType;

					$resultBye = $this->Schedulingtimings->save($dataBye);
					
					$match_number++;
				}
			}
			
			$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
			// now firstly choose first room
			$cntrRoomCSEvent = 0;
			$cntrEVSCH = 0;
			
			//$this->prx($arrStudentsForSplice);
			
			/* DEFINE SCHEDULING FOR REMAINING PLAYERS AFTER BYE PLAYERS */
			// To check how many matches are there
			$totalMatches = ($totalStudentsEV-$totalByePlayer)/2;
			for($cntrRemainP=0;$cntrRemainP<$totalMatches;$cntrRemainP++)
			{
				// to get first player id
				$randFirstP 				= rand(0,count((array)$arrStudentsForSplice)-1);
				$first_student_id 			= $arrStudentsForSplice[$randFirstP];
				array_splice($arrStudentsForSplice, $randFirstP, 1);
				
				// to get opponent user id
				$randSecondP 				= rand(0,count((array)$arrStudentsForSplice)-1);
				$second_student_id 			= $arrStudentsForSplice[$randSecondP];
				array_splice($arrStudentsForSplice, $randSecondP, 1);
				
				/* Here we will check that this user_id is School Or student 
				School means its a group event
				Student means it's an individual event
				*/
				$fetchUserType = $this->fetchUserType($first_student_id);
				
				//now save remaining player in database with opponent user id
				$schedulingtimings = $this->Schedulingtimings->newEntity([]);
				$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

				$dataBye->schedule_category				= 2;
				$dataBye->conventionseasons_id			= $conventionSD->id;
				$dataBye->convention_id					= $conventionSD->convention_id;
				$dataBye->season_id						= $conventionSD->season_id;
				$dataBye->season_year 					= $conventionSD->season_year;
				$dataBye->conventionregistration_id 	= NULL;
				$dataBye->event_id 						= $event_id_c2;
				$dataBye->event_id_number 				= $eventD->event_id_number;
				$dataBye->user_id 						= $first_student_id;
				$dataBye->group_name 					= NULL;
				$dataBye->room_id 						= (int)$roomArrCSEvent[$cntrRoomCSEvent];
				$dataBye->day 							= NULL;
				$dataBye->start_time 					= NULL;
				$dataBye->finish_time 					= NULL;
				$dataBye->user_id_opponent 				= $second_student_id;
				$dataBye->round_number 					= 1;
				$dataBye->match_number 					= $match_number;
				$dataBye->is_bye 						= 0;
				$dataBye->created 						= date('Y-m-d H:i:s');
				
				$dataBye->sch_date_time 				= $start_date.' 00:00:00';
				
				$dataBye->user_type 					= $fetchUserType;

				$resultBye = $this->Schedulingtimings->save($dataBye);
				
				$match_number++;
				
				$cntrEVSCH++;
			}
		}
		
		
		
		
		/* After first round, we need to schedule next rounds till last round between 2 players */
		// Get all matches for each event and perform scheduling 'Schedulingtimings.schedule_category' => 2
		foreach($arrEventsC2 as $event_id_c2)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c2])->first();
			
			// Later-round placeholders must use this event's mapped room, not a leftover room from the previous event.
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id_c2."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id_c2.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c2.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c2."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}

			$eventTweak2Round = $this->Schedulingeventtweaks->find()
				->where([
					'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
					'Schedulingeventtweaks.event_id' => $event_id_c2
				])
				->first();
			if ($eventTweak2Round && $eventTweak2Round->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak2Round->pinned_room_id];
			}
			
			// to get total matches played in first round for this event including byes if any
			$countTotalMatR1Event = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c2,'Schedulingtimings.round_number' => 1])->count();
			
			// to get the last match number for this event
			$evLastMatch = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c2,'Schedulingtimings.round_number' => 1])->order(['Schedulingtimings.match_number' => 'DESC'])->first();
			$lastMatchNumber = $evLastMatch->match_number;
			
			$lastMatchNumber = $lastMatchNumber+1;
			
			/* Generate all subsequent rounds until only the Final remains.
			   Odd-round leftovers get a bye into the next round so no player
			   is dropped from the bracket. */
			$currentRound  = 1;
			$safetyLimit   = 0;
			$roomIdForRound = isset($roomArrCSEvent[0]) ? $roomArrCSEvent[0] : NULL;
			while (true) {
				$safetyLimit++;
				if ($safetyLimit > 15) break;

				$arrNR = array();
				$nextRounds = $this->Schedulingtimings->find()->where([
					'Schedulingtimings.schedule_category'   => 2,
					'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
					'Schedulingtimings.convention_id'       => $conventionSD->convention_id,
					'Schedulingtimings.season_id'           => $conventionSD->season_id,
					'Schedulingtimings.season_year'         => $conventionSD->season_year,
					'Schedulingtimings.event_id'            => $event_id_c2,
					'Schedulingtimings.round_number'        => $currentRound
				])->all();
				foreach ($nextRounds as $nextRound) {
					$arrNR[] = $nextRound->id;
				}

				// 0 or 1 records means this round IS the Final (or empty) — stop
				if (count($arrNR) <= 1) break;

				$tempNR = $arrNR;

				// Pair matches for the next round
				while (count($tempNR) >= 2) {
					$randFirstID = rand(0, count($tempNR) - 1);
					$first_id    = $tempNR[$randFirstID];
					array_splice($tempNR, $randFirstID, 1);

					$randSecondID = rand(0, count($tempNR) - 1);
					$second_id    = $tempNR[$randSecondID];
					array_splice($tempNR, $randSecondID, 1);

					$schedulingtimings = $this->Schedulingtimings->newEntity([]);
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 2;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $event_id_c2;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= $roomIdForRound;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $first_id;
					$dataBye->schtimeautoid2			= $second_id;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 0;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
				}

				// Odd player out — give them a bye into the next round
				if (count($tempNR) === 1) {
					$byeMatchId = $tempNR[0];
					$schedulingtimings = $this->Schedulingtimings->newEntity([]);
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 2;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $event_id_c2;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= $roomIdForRound;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $byeMatchId;
					$dataBye->schtimeautoid2			= 0;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 1;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
				}

				$currentRound++;
			}
		}
		
		//exit;
		
		
		
		/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY (EXCEPT BYE), START AND END TIME */
		/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE 
		DAY, START TIME AND END TIME */
		
		//exit;
		
		foreach($arrEventsC2 as $event_id)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}

			$eventTweak2Schedule = $this->Schedulingeventtweaks->find()
				->where([
					'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
					'Schedulingeventtweaks.event_id' => $event_id
				])
				->first();
			if ($eventTweak2Schedule && $eventTweak2Schedule->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak2Schedule->pinned_room_id];
			}
			
			//$this->prx($roomArrCSEvent);
			
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '2' AND Schedulingtimings.is_bye = '0' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);
				$windowExceeded = false;

				$eventBandsC2 = $this->buildEliminationEventBands($schedulingsD, $eventTweak2Schedule);
				$pendingRows = [];
				foreach ($schedulingT as $schdata) {
					$pendingRows[] = $schdata;
				}

				$slotGuard = 0;
				foreach ($eventBandsC2 as $eventBandC2) {
					if (count($pendingRows) === 0) {
						break;
					}

					$roomCursors = [];
					foreach ($roomArrCSEvent as $roomID) {
						$roomCursor = $this->getRoomBandCursorTime($conventionSD, (int)$roomID, $eventBandC2['date'], $eventBandC2['start']);
						if ($roomCursor !== null && strtotime($roomCursor) < strtotime($eventBandC2['end'])) {
							$roomCursors[(int)$roomID] = $roomCursor;
						}
					}

					while (count($pendingRows) > 0 && count($roomCursors) > 0)
					{
						$slotGuard++;
						if ($slotGuard > 10000) {
							$this->Flash->warning('Category 2 scheduler stopped early for event '.$eventD->event_id_number.' to avoid an infinite loop while placing matches.');
							break 2;
						}

						asort($roomCursors);
						$roomID = (int)array_key_first($roomCursors);
						$cursorStart = $roomCursors[$roomID];
						$cursorFinish = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($cursorStart)));

						if (strtotime($cursorFinish) > strtotime($eventBandC2['end'])) {
							unset($roomCursors[$roomID]);
							continue;
						}

						$bandDayNumber = $this->getConventionDayNumber($start_date, $eventBandC2['date']);
						$validSlot = $this->findValidSlot($cursorStart, $cursorFinish, $eventBandC2['day'], $eventBandC2['date'], $bandDayNumber, $eventBandC2['start'], $eventBandC2['end'], $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$event_id);
						if (!empty($validSlot['window_exhausted'])) {
							$windowExceeded = true;
							$this->flashConventionWindowExceeded($schedulingsD);
							break 2;
						}

						if (!$this->isValidSlotInsideBand($eventBandC2, $validSlot)) {
							unset($roomCursors[$roomID]);
							continue;
						}

						$schdata = array_shift($pendingRows);
						$slotStartDate = $validSlot['schStartDate'];
						$slotStartTime = $validSlot['start_time'];
						$slotFinishTime = $validSlot['finish_time'];
						$roomOverlapCursorTime = $this->getRoomOverlapCursorTime($conventionSD, $roomID, $slotStartDate, $slotStartTime, $slotFinishTime);
						if ($roomOverlapCursorTime !== null) {
							array_unshift($pendingRows, $schdata);
							$roomCursors[$roomID] = $roomOverlapCursorTime;
							if (strtotime($roomCursors[$roomID]) >= strtotime($eventBandC2['end'])) {
								unset($roomCursors[$roomID]);
							}
							continue;
						}

						$this->Schedulingtimings->updateAll(
						[
						'room_id' 		=> $roomID,
						'day' 			=> $eventBandC2['day'],
						'start_time' 	=> $slotStartTime,
						'finish_time' 	=> $slotFinishTime,
						'sch_date_time' 	=> $slotStartDate.' '.date("H:i:s", strtotime($slotStartTime)),
						'modified' 		=> date("Y-m-d H:i:s")
						],
						["id" => $schdata->id]
						);

						$roomCursors[$roomID] = $slotFinishTime;
						if (strtotime($roomCursors[$roomID]) >= strtotime($eventBandC2['end'])) {
							unset($roomCursors[$roomID]);
						}
					}
				}

				if (count($pendingRows) > 0) {
					$fallbackResultC2 = $this->placePendingRowsInBandsWithoutUserConflict(
						$conventionSD,
						$schedulingsD,
						$start_date,
						$eventBandsC2,
						$roomArrCSEvent,
						$eventSetupRoundJudTime,
						$lunch_time_start,
						$lunch_time_end,
						(int)$event_id,
						1,
						$pendingRows
					);

					if (!empty($fallbackResultC2['assignedCount'])) {
						$this->Flash->warning('Category 2 fallback placed '.$fallbackResultC2['assignedCount'].' remaining match(es) for event '.$eventD->event_id_number.'.');
					}

					$pendingRows = $fallbackResultC2['pendingRows'];
				}

				if (count($pendingRows) > 0) {
					$overflowResultC2 = $this->placePendingRowsInOverflowWindow(
						$conventionSD,
						$schedulingsD,
						$roomArrCSEvent,
						$eventSetupRoundJudTime,
						$lunch_time_start,
						$lunch_time_end,
						$pendingRows
					);

					if (!empty($overflowResultC2['assignedCount'])) {
						$this->Flash->warning('Category 2 overflow placed '.$overflowResultC2['assignedCount'].' remaining match(es) for event '.$eventD->event_id_number.' on days after the configured window.');
					}

					$pendingRows = $overflowResultC2['pendingRows'];
				}

				if (count($pendingRows) > 0 && !$windowExceeded) {
					$windowExceeded = true;
					$this->flashConventionWindowExceeded($schedulingsD);
					$this->Flash->warning('Category 2 still has '.count($pendingRows).' unscheduled match(es) for event '.$eventD->event_id_number.'. Please increase rooms/time window and rerun scheduling.');
				}

				if ($windowExceeded) {
					continue;
				}
			
			}
			
			//exit;
			
			
		}
		
		//exit;
		
		//$this->Flash->success('Scheduling completed successfully for category 2.');
		return $this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec3', $convention_season_slug]);
		
	}
	
	
	public function startschedulec3($convention_season_slug=null) {
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		//$this->prx($conventionSD);
		
		// to get details of schedule timings
		$schedulingsD = $this->resolveSchedulingWizardRecord($conventionSD);
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$first_day 				= $schedulingsD->first_day;
		
		$normal_starting_time 	= $this->normalizeWizardTime($schedulingsD->normal_starting_time);
		$normal_finish_time 	= $this->normalizeWizardTime($schedulingsD->normal_finish_time);
		
		$lunch_time_start 		= $this->normalizeWizardTime($schedulingsD->lunch_time_start);
		$lunch_time_end 		= $this->normalizeWizardTime($schedulingsD->lunch_time_end);
		
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		
		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = $this->normalizeWizardTime($schedulingsD->different_first_day_start_time);
			$different_first_day_end_time 	= $this->normalizeWizardTime($schedulingsD->different_first_day_end_time);
		}
		
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
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
				$arrEventsC3[] = $eventD->id;
			}
		}
		//$this->prx($arrEventsC3);
		/* $arrEventsC3 = array();
		$arrEventsC3[] = 63;
		$arrEventsC3[] = 107; */
		//$arrEventsC3 = array();
		//$arrEventsC3[] = 65;
		//$this->prx($arrEventsC3);
		
		
		$eventCTR = 0;
		// Now run loop on each event and get groups and schedule
		foreach($arrEventsC3 as $event_id_c3)
		{
			/* PART 1 OF THIS EVENT */
			
			$mainArrForEvent = array();
			
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c3])->first();
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id_c3."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id_c3.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c3.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c3."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}

			$eventTweak3 = $this->Schedulingeventtweaks->find()
				->where([
					'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
					'Schedulingeventtweaks.event_id' => $event_id_c3
				])
				->first();
			if ($eventTweak3 && $eventTweak3->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak3->pinned_room_id];
			}
			$eventBandsC3 = $this->buildEliminationEventBands($schedulingsD, $eventTweak3);
			$defaultBandC3 = !empty($eventBandsC3) ? $eventBandsC3[0] : null;
			$byeDayC3 = $defaultBandC3['day'] ?? $first_day;
			$byeStartTimeC3 = $defaultBandC3['start'] ?? ($starting_different_time_first_day_yes_no == 1 ? $different_first_day_start_time : $normal_starting_time);
			$byeDateC3 = $defaultBandC3['date'] ?? $start_date;
			//$this->prx($roomArrCSEvent);
			 
			// now get groups for this event from convention registration
			// First to fetch conv registrations
			$condCR = array();
			$condCR[] = "(Conventionregistrations.conventionseason_id = '".$conventionSD->id."' AND Conventionregistrations.convention_id = '".$conventionSD->convention_id."')";
			$conventionRegistrations = $this->Conventionregistrations->find()->where($condCR)->all();
			foreach($conventionRegistrations as $convreg)
			{
				//now fetch groups for this CR
				$condCRSTEV = array();
				$condCRSTEV[] = "(Crstudentevents.conventionseason_id = '".$conventionSD->id."' AND Crstudentevents.convention_id = '".$conventionSD->convention_id."')";
				$condCRSTEV[] = "(Crstudentevents.conventionregistration_id = '".$convreg->id."' AND Crstudentevents.event_id = '".$event_id_c3."')";
				$condCRSTEV[] = "(Crstudentevents.group_name != '')";
				
				//$condCRSTEV[] = "(Crstudentevents.user_id = '55')"; // to test
				
				$convRegSTEV = $this->Crstudentevents->find()->where($condCRSTEV)->select(['group_name'])->all();
				//$this->prx($convRegSTEV);
				if($convRegSTEV)
				{
					// if any group exists, then push it to array
					foreach($convRegSTEV as $convregstev)
					{
						// now create a variable with combination 
						// format is conventionseasons_id==convention_id==season_id==season_year==conventionregistration_id==event_id==event_id_number==user_id==group_name
						
						$varEventCombination 	= $conventionSD->id."==";
						$varEventCombination 	.= $conventionSD->convention_id."==";
						$varEventCombination 	.= $conventionSD->season_id."==";
						$varEventCombination 	.= $conventionSD->season_year."==";
						$varEventCombination 	.= $convreg->id."==";
						$varEventCombination	.= $event_id_c3."==";
						$varEventCombination 	.= $eventD->event_id_number."==";
						$varEventCombination 	.= $convreg->user_id."==";
						$varEventCombination 	.= $convregstev->group_name;
						
						if(!in_array($varEventCombination,(array)$mainArrForEvent))
						{
							$mainArrForEvent[] = $varEventCombination;
						}
					}
				}
			}
			
			//$this->prx($mainArrForEvent);
			
			
			
			if(count((array)$mainArrForEvent))
			{
				shuffle($mainArrForEvent);
				
				// now get total bye groups
				$totalGroupsEV 				= count($mainArrForEvent);
				$totalByeGroup 				= $this->getByePlayerScheduling($totalGroupsEV);
				$arrGroupsForSplice 		= $mainArrForEvent;
				
				//$this->prx($arrGroupsForSplice);
				
				//echo $totalByeGroup;exit;
				
				$match_number = 1;
				/* DEFINE SCHEDULING FOR BYE GROUPS */
				if($totalByeGroup>0)
				{
					// pick number of random players for bye
					for($cntrByeP=0;$cntrByeP<$totalByeGroup;$cntrByeP++)
					{
						// generate a random number from 0 to total count of students
						$randByeNumber 		= rand(0,count($arrGroupsForSplice)-1);
						
						// now explode data from array to get al details
						$dataGExplode = explode("==",$arrGroupsForSplice[$randByeNumber]);
						//$this->prx($dataGExplode);
						
						array_splice($arrGroupsForSplice, $randByeNumber, 1);
						
						/* Here we will check that this user_id is School Or student 
						School means its a group event
						Student means it's an individual event
						*/
						$fetchUserType = $this->fetchUserType($dataGExplode[7]);
						
						
						
						//now save bye player in database, opponent of bye player id will be 0
						$schedulingtimings = $this->Schedulingtimings->newEntity([]);
						$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

						$dataBye->schedule_category				= 3;
						$dataBye->conventionseasons_id			= $conventionSD->id;
						$dataBye->convention_id					= $conventionSD->convention_id;
						$dataBye->season_id						= $conventionSD->season_id;
						$dataBye->season_year 					= $conventionSD->season_year;
						$dataBye->conventionregistration_id 	= $dataGExplode[4];
						$dataBye->event_id 						= $eventD->id;
						$dataBye->event_id_number 				= $eventD->event_id_number;
						$dataBye->user_id 						= $dataGExplode[7];
						$dataBye->group_name 					= $dataGExplode[8];
						$dataBye->room_id 						= $roomArrCSEvent[0];
						$dataBye->day 							= $byeDayC3;
						$dataBye->start_time 					= $byeStartTimeC3;
						$dataBye->finish_time 					= $byeStartTimeC3;
						$dataBye->user_id_opponent 				= 0;
						$dataBye->round_number 					= 1;
						$dataBye->match_number 					= $match_number;
						$dataBye->is_bye 						= 1;
						$dataBye->created 						= date('Y-m-d H:i:s');
						
						$dataBye->sch_date_time 				= $byeDateC3.' '.date("H:i:s", strtotime($dataBye->start_time));
						
						$dataBye->user_type 					= $fetchUserType;

						$resultBye = $this->Schedulingtimings->save($dataBye);
						
						$match_number++;
					}
				}
				
				//$this->prx($arrGroupsForSplice);
				
				/* DEFINE SCHEDULING FOR REMAINING PLAYERS AFTER BYE PLAYERS */
				// To check how many matches are there
				$totalMatches = ($totalGroupsEV-$totalByeGroup)/2;
				for($cntrRemainP=0;$cntrRemainP<$totalMatches;$cntrRemainP++)
				{
					// to get first group id
					$randFirstP 				= rand(0,count((array)$arrGroupsForSplice)-1);
					// now explode data to get info
					$dataGExplodeFirst = explode("==",$arrGroupsForSplice[$randFirstP]);
					array_splice($arrGroupsForSplice, $randFirstP, 1);
					
					
					// to get opponent group id
					$randSecondP 				= rand(0,count((array)$arrGroupsForSplice)-1);
					// now explode data to get info
					$dataGExplodeSecond = explode("==",$arrGroupsForSplice[$randSecondP]);
					array_splice($arrGroupsForSplice, $randSecondP, 1);
					
					/* Here we will check that this user_id is School Or student 
					School means its a group event
					Student means it's an individual event
					*/
					$fetchUserType = $this->fetchUserType($dataGExplodeFirst[7]);
					
					//now save remaining player in database with opponent user id
					$schedulingtimings = $this->Schedulingtimings->newEntity([]);
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 3;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $eventD->id;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= $dataGExplodeFirst[7];
					$dataBye->group_name 					= $dataGExplodeFirst[8];
					$dataBye->room_id 						= NULL;
					$dataBye->day 							= NULL;
					$dataBye->start_time 					= NULL;
					$dataBye->finish_time 					= NULL;
					$dataBye->user_id_opponent 				= $dataGExplodeSecond[7];
					$dataBye->group_name_opponent 			= $dataGExplodeSecond[8];
					$dataBye->round_number 					= 1;
					$dataBye->match_number 					= $match_number;
					$dataBye->is_bye 						= 0;
					$dataBye->created 						= date('Y-m-d H:i:s');
					
					$dataBye->sch_date_time 				= $start_date.' 00:00:00';
					
					$dataBye->user_type 					= $fetchUserType;

					$resultBye = $this->Schedulingtimings->save($dataBye);
					
					$match_number++;
					
				}
			}
			
			
			
			
			
			
			/* PART 2 OF THIS EVENT */
			
			/* After first round, we need to schedule next rounds till last round between 2 players */
			// Get all matches for each event and perform scheduling 'Schedulingtimings.schedule_category' => 3
			
			// to get total matches played in first round for this event including byes if any
			$countTotalMatR1Event = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c3,'Schedulingtimings.round_number' => 1])->count();
			
			// to get the last match number for this event
			$evLastMatch = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c3,'Schedulingtimings.round_number' => 1])->order(['Schedulingtimings.match_number' => 'DESC'])->first();
			if(!$evLastMatch || (int)$countTotalMatR1Event === 0)
			{
				continue;
			}
			$lastMatchNumber = $evLastMatch->match_number;
			
			$lastMatchNumber = $lastMatchNumber+1;
			
			/* Generate all subsequent rounds until only the Final remains.
			   Odd-round leftovers get a bye into the next round so no group
			   is dropped from the bracket. */
			$currentRound  = 1;
			$safetyLimit   = 0;
			while (true) {
				$safetyLimit++;
				if ($safetyLimit > 15) break;

				$arrNR = array();
				$nextRounds = $this->Schedulingtimings->find()->where([
					'Schedulingtimings.schedule_category'   => 3,
					'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
					'Schedulingtimings.convention_id'       => $conventionSD->convention_id,
					'Schedulingtimings.season_id'           => $conventionSD->season_id,
					'Schedulingtimings.season_year'         => $conventionSD->season_year,
					'Schedulingtimings.event_id'            => $event_id_c3,
					'Schedulingtimings.round_number'        => $currentRound
				])->all();
				foreach ($nextRounds as $nextRound) {
					$arrNR[] = $nextRound->id;
				}

				// 0 or 1 records means this round IS the Final (or empty) — stop
				if (count($arrNR) <= 1) break;

				$tempNR = $arrNR;

				// Pair matches for the next round
				while (count($tempNR) >= 2) {
					$randFirstID = rand(0, count($tempNR) - 1);
					$first_id    = $tempNR[$randFirstID];
					array_splice($tempNR, $randFirstID, 1);

					$randSecondID = rand(0, count($tempNR) - 1);
					$second_id    = $tempNR[$randSecondID];
					array_splice($tempNR, $randSecondID, 1);

					$schedulingtimings = $this->Schedulingtimings->newEntity([]);
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 3;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $eventD->id;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= NULL;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $first_id;
					$dataBye->schtimeautoid2			= $second_id;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 0;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
				}

				// Odd group out — give them a bye into the next round
				if (count($tempNR) === 1) {
					$byeMatchId = $tempNR[0];
					$schedulingtimings = $this->Schedulingtimings->newEntity([]);
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 3;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $eventD->id;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= NULL;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $byeMatchId;
					$dataBye->schtimeautoid2			= 0;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 1;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
				}

				$currentRound++;
			}
			
			
			
			
			
			
			
			
			
			/* PART 3 OF THIS EVENT */
			
			/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY (EXCEPT BYE), START AND END TIME */
			/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE 
			DAY, START TIME AND END TIME */
			
			$event_id = $event_id_c3;
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '3' AND Schedulingtimings.is_bye = '0' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);
				$windowExceeded = false;

				$eventBandsC3 = $this->buildEliminationEventBands($schedulingsD, $eventTweak3);
				$pendingRows = [];
				foreach ($schedulingT as $schdata) {
					$pendingRows[] = $schdata;
				}

				$slotGuard = 0;
				foreach ($eventBandsC3 as $eventBandC3) {
					if (count($pendingRows) === 0) {
						break;
					}

					$roomCursors = [];
					foreach ($roomArrCSEvent as $roomID) {
						$roomCursor = $this->getRoomBandCursorTime($conventionSD, (int)$roomID, $eventBandC3['date'], $eventBandC3['start']);
						if ($roomCursor !== null && strtotime($roomCursor) < strtotime($eventBandC3['end'])) {
							$roomCursors[(int)$roomID] = $roomCursor;
						}
					}

					while (count($pendingRows) > 0 && count($roomCursors) > 0)
					{
						$slotGuard++;
						if ($slotGuard > 10000) {
							$this->Flash->warning('Category 3 scheduler stopped early for event '.$eventD->event_id_number.' to avoid an infinite loop while placing matches.');
							break 2;
						}

						asort($roomCursors);
						$roomID = (int)array_key_first($roomCursors);
						$cursorStart = $roomCursors[$roomID];
						$cursorFinish = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($cursorStart)));

						if (strtotime($cursorFinish) > strtotime($eventBandC3['end'])) {
							unset($roomCursors[$roomID]);
							continue;
						}

						$bandDayNumber = $this->getConventionDayNumber($start_date, $eventBandC3['date']);
						$validSlot = $this->findValidSlot($cursorStart, $cursorFinish, $eventBandC3['day'], $eventBandC3['date'], $bandDayNumber, $eventBandC3['start'], $eventBandC3['end'], $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$event_id);
						if (!empty($validSlot['window_exhausted'])) {
							$windowExceeded = true;
							$this->flashConventionWindowExceeded($schedulingsD);
							break 2;
						}

						if (!$this->isValidSlotInsideBand($eventBandC3, $validSlot)) {
							unset($roomCursors[$roomID]);
							continue;
						}

						$schdata = array_shift($pendingRows);
						$slotStartDate = $validSlot['schStartDate'];
						$slotStartTime = $validSlot['start_time'];
						$slotFinishTime = $validSlot['finish_time'];
						$roomOverlapCursorTime = $this->getRoomOverlapCursorTime($conventionSD, $roomID, $slotStartDate, $slotStartTime, $slotFinishTime);
						if ($roomOverlapCursorTime !== null) {
							array_unshift($pendingRows, $schdata);
							$roomCursors[$roomID] = $roomOverlapCursorTime;
							if (strtotime($roomCursors[$roomID]) >= strtotime($eventBandC3['end'])) {
								unset($roomCursors[$roomID]);
							}
							continue;
						}

						$this->Schedulingtimings->updateAll(
						[
						'room_id' 		=> $roomID,
						'day' 			=> $eventBandC3['day'],
						'start_time' 	=> $slotStartTime,
						'finish_time' 	=> $slotFinishTime,
						'sch_date_time' 	=> $slotStartDate.' '.date("H:i:s", strtotime($slotStartTime)),
						'modified' 		=> date("Y-m-d H:i:s")
						],
						["id" => $schdata->id]
						);

						$roomCursors[$roomID] = $slotFinishTime;
						if (strtotime($roomCursors[$roomID]) >= strtotime($eventBandC3['end'])) {
							unset($roomCursors[$roomID]);
						}
					}
				}

				if (count($pendingRows) > 0) {
					$fallbackResultC3 = $this->placePendingRowsInBandsWithoutUserConflict(
						$conventionSD,
						$schedulingsD,
						$start_date,
						$eventBandsC3,
						$roomArrCSEvent,
						$eventSetupRoundJudTime,
						$lunch_time_start,
						$lunch_time_end,
						(int)$event_id,
						1,
						$pendingRows
					);

					if (!empty($fallbackResultC3['assignedCount'])) {
						$this->Flash->warning('Category 3 fallback placed '.$fallbackResultC3['assignedCount'].' remaining match(es) for event '.$eventD->event_id_number.'.');
					}

					$pendingRows = $fallbackResultC3['pendingRows'];
				}

				if (count($pendingRows) > 0 && !$windowExceeded) {
					$windowExceeded = true;
					$this->flashConventionWindowExceeded($schedulingsD);
				}

				if ($windowExceeded) {
					continue;
				}
			
			}
			
			//echo '<hr>';
			
		$eventCTR++;	
			
		}
		
		//exit;
		//echo $cntrEVSCH;exit;
		
		//$this->Flash->success('Scheduling completed successfully for category 3.');
		return $this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec4', $convention_season_slug]);
		
	}
	
	
	public function startschedulec4($convention_season_slug=null) {
		@set_time_limit(0);
		@ini_set('max_execution_time', '0');
		$this->set('convention_season_slug', $convention_season_slug);
		$defaultConnection = ConnectionManager::get('default');
		if (method_exists($defaultConnection, 'enableQueryLogging')) {
			$defaultConnection->enableQueryLogging(false);
		} elseif (method_exists($defaultConnection, 'logQueries')) {
			$defaultConnection->logQueries(false);
		}
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);

		/* Category 4 now runs after categories 1-3 in full chain. */
		
		//$this->prx($conventionSD);
		
		// to get details of schedule timings
		$schedulingsD = $this->resolveSchedulingWizardRecord($conventionSD);
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$category4Bands = $this->buildConventionBands($schedulingsD);
		if (!empty($category4Bands)) {
			\Cake\Log\Log::debug('Category 4 convention bands mode='.$this->getScheduleBandMode($schedulingsD).', total_bands='.count($category4Bands).', first_band='.$category4Bands[0]['day'].' '.$category4Bands[0]['band'].' '.$category4Bands[0]['start'].'-'.$category4Bands[0]['end'].', last_band='.$category4Bands[count($category4Bands) - 1]['day'].' '.$category4Bands[count($category4Bands) - 1]['band'].' '.$category4Bands[count($category4Bands) - 1]['start'].'-'.$category4Bands[count($category4Bands) - 1]['end']);
		}
		$first_day 				= $schedulingsD->first_day;
		$normal_starting_time 	= $schedulingsD->normal_starting_time;
		$normal_finish_time 	= $schedulingsD->normal_finish_time;
		
		$lunch_time_start 		= $schedulingsD->lunch_time_start;
		$lunch_time_end 		= $schedulingsD->lunch_time_end;
		
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		
		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = $schedulingsD->different_first_day_start_time;
			$different_first_day_end_time 	= $schedulingsD->different_first_day_end_time;
		}
		
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
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
				$arrEventsC4[] = $eventD->id;
			}
		}
		//$this->prx($arrEventsC4);
		//$arrEventsC4 = array();$arrEventsC4[] = 59;
		
		
		/* NOW GET STUDENTS FOR EACH EVENT */
		$arrStudentsC4 = array();
		foreach($arrEventsC4 as $event_id_c4)
		{
			$condSTC4 = array();
			$condSTC4[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND   Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
			$condSTC4[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
			
			$condSTC4[] = "(Conventionregistrationstudents.event_ids LIKE '".$event_id_c4."' OR Conventionregistrationstudents.event_ids LIKE '".$event_id_c4.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c4.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c4."')";
			
			$studentsC4 = $this->Conventionregistrationstudents->find()->where($condSTC4)->all();
			
			if($studentsC4)
			{
				foreach($studentsC4 as $studentEV)
				{
					$arrStudentsC4[$event_id_c4][] = $studentEV->student_id;
				}
			}
		}
		//$this->prx($arrStudentsC4);
		
		
		/* NOW FETCH STUDENTS FOR EACH EVENT AND PERFORM SCHEDULING */
		foreach($arrStudentsC4 as $event_id_c4 => $studentsListC4)
		{	
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c4])->first();
			
			// shuffle array
			shuffle($studentsListC4);
			
			foreach($studentsListC4 as $student_id)
			{
				/* Here we will check that this user_id is School Or student 
				School means its a group event
				Student means it's an individual event
				*/
				$fetchUserType = $this->fetchUserType($student_id);
				
				//now enter schedule timings
				$schedulingtimings = $this->Schedulingtimings->newEntity([]);
				$dataST = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

				$dataST->schedule_category				= 4;
				$dataST->conventionseasons_id			= $conventionSD->id;
				$dataST->convention_id					= $conventionSD->convention_id;
				$dataST->season_id						= $conventionSD->season_id;
				$dataST->season_year					= $conventionSD->season_year;
				$dataST->conventionregistration_id 		= NULL;
				$dataST->event_id 						= $eventD->id;
				$dataST->event_id_number 				= $eventD->event_id_number;
				$dataST->user_id 						= $student_id;
				$dataST->group_name 					= NULL;
				
				$dataST->room_id 						= NULL;
				$dataST->day 							= NULL;
				$dataST->start_time 					= NULL;
				$dataST->finish_time 					= NULL;
				
				$dataST->created 						= date('Y-m-d H:i:s');
				$dataST->modified 						= date('Y-m-d H:i:s');
				
				$dataST->sch_date_time 					= $start_date.' 00:00:00';
				
				$dataST->user_type 						= $fetchUserType;
				//$this->prx($dataST);

				$resultST = $this->Schedulingtimings->save($dataST);
			}
			
			
		}
		
		
		/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY, START AND END TIME */
		/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE 
		DAY, START TIME AND END TIME */
		
		foreach($arrEventsC4 as $event_id)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			//$this->prx($roomArrCSEvent);
			
			// ---- Load event tweaks (A: pinned day, B: pinned room, C: pinned start time) ----
			$eventTweak4 = $this->Schedulingeventtweaks->find()
				->where(['Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
				         'Schedulingeventtweaks.event_id'             => $event_id])
				->first();
			// B: override room assignment
			if ($eventTweak4 && $eventTweak4->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak4->pinned_room_id];
			}
			$eventBandsC4 = $this->buildCategory4EventBands($schedulingsD, $eventTweak4);
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent) && count($eventBandsC4) > 0)
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '4' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);
				$windowExceeded = false;
				
				$batchSizeC4 = $this->getCategory4ConcurrentBatchSize($eventD);
				$cntrEVSCH 			= 0;

				$pendingRows = [];
				foreach ($schedulingT as $schdata) {
					$pendingRows[] = $schdata;
				}


				$slotGuard = 0;
				foreach ($eventBandsC4 as $eventBandC4) {
					if (count($pendingRows) === 0) {
						break;
					}

					$roomCursors = [];
					foreach ($roomArrCSEvent as $roomID) {
						$roomCursor = $this->getRoomBandCursorTime($conventionSD, (int)$roomID, $eventBandC4['date'], $eventBandC4['start']);
						if ($roomCursor !== null && strtotime($roomCursor) < strtotime($eventBandC4['end'])) {
							$roomCursors[(int)$roomID] = $roomCursor;
						}
					}

					while (count($pendingRows) > 0 && count($roomCursors) > 0)
					{
						$slotGuard++;
						if ($slotGuard > 10000) {
							$this->Flash->warning('Category 4 scheduler stopped early for event '.$eventD->event_id_number.' to avoid an infinite loop while placing participants.');
							break 2;
						}

						asort($roomCursors);
						$roomID = (int)array_key_first($roomCursors);
						$cursorStart = $roomCursors[$roomID];
						$cursorFinish = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($cursorStart)));

						if (strtotime($cursorFinish) > strtotime($eventBandC4['end'])) {
							unset($roomCursors[$roomID]);
							continue;
						}

						$bandDayNumber = $this->getConventionDayNumber($start_date, $eventBandC4['date']);
						$validSlot = $this->findValidSlot($cursorStart, $cursorFinish, $eventBandC4['day'], $eventBandC4['date'], $bandDayNumber, $eventBandC4['start'], $eventBandC4['end'], $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$event_id);
						if (!empty($validSlot['window_exhausted'])) {
							$windowExceeded = true;
							$this->flashConventionWindowExceeded($schedulingsD);
							break 2;
						}

						if (!$this->isValidSlotInsideBand($eventBandC4, $validSlot)) {
							unset($roomCursors[$roomID]);
							continue;
						}

						$slotStartDate = $validSlot['schStartDate'];
						$slotStartTime = $validSlot['start_time'];
						$slotFinishTime = $validSlot['finish_time'];

						$assignIds = [];
						$remainingRows = [];
						$assignedInThisSlot = 0;
						foreach ($pendingRows as $pendingRow) {
							if ($assignedInThisSlot < $batchSizeC4 && !$this->hasUserSchedulingConflict($conventionSD, (int)$pendingRow->user_id, $slotStartDate, $slotStartTime, $slotFinishTime)) {
								$assignIds[] = $pendingRow->id;
								$assignedInThisSlot++;
							} else {
								$remainingRows[] = $pendingRow;
							}
						}

						$roomCursors[$roomID] = $slotFinishTime;
						if (strtotime($roomCursors[$roomID]) >= strtotime($eventBandC4['end'])) {
							unset($roomCursors[$roomID]);
						}

						if (count($assignIds) === 0) {
							continue;
						}

						$this->Schedulingtimings->updateAll(
						[
						'room_id' 		=> $roomID,
						'day' 			=> $eventBandC4['day'],
						'start_time' 	=> $slotStartTime,
						'finish_time' 	=> $slotFinishTime,
						'sch_date_time' 	=> $slotStartDate.' '.date("H:i:s", strtotime($slotStartTime)),
						'modified' 		=> date("Y-m-d H:i:s")
						],
						['id IN' => $assignIds]
						);

						$pendingRows = $remainingRows;
						$cntrEVSCH += count($assignIds);
					}
				}

				if (count($pendingRows) > 0) {
					$fallbackResult = $this->placePendingRowsInBandsWithoutUserConflict(
						$conventionSD,
						$schedulingsD,
						$start_date,
						$eventBandsC4,
						$roomArrCSEvent,
						$eventSetupRoundJudTime,
						$lunch_time_start,
						$lunch_time_end,
						(int)$event_id,
						$batchSizeC4,
						$pendingRows
					);

					if (!empty($fallbackResult['assignedCount'])) {
						$this->Flash->warning('Category 4 fallback placed '.$fallbackResult['assignedCount'].' remaining participant(s) for event '.$eventD->event_id_number.' by relaxing user conflict checks.');
					}

					$pendingRows = $fallbackResult['pendingRows'];
				}

				if (count($pendingRows) > 0 && !$windowExceeded) {
					$windowExceeded = true;
					$this->flashConventionWindowExceeded($schedulingsD);
				}

				if ($windowExceeded) {
					continue;
				}
				
				//exit;
			
			}
			
			
		}
		
		//$this->Flash->success('Scheduling done for category 4.');
		return $this->redirect(['controller' => 'schedulingtimings', 'action' => 'fillgroupuserids', $convention_season_slug]);
		
	}
	
	public function fillgroupuserids($convention_season_slug=null)
	{
		$updateDateTime = date("Y-m-d H:i:s");
		
		// To get convention season details
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		// Now fetch group events from Schedulings
		$condGroupScht = array();
		$condGroupScht[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND 
			Schedulingtimings.user_type = 'School'  AND 
			(Schedulingtimings.group_name != '' OR  Schedulingtimings.group_name != NULL) AND
			(Schedulingtimings.group_name_opponent != '' OR  Schedulingtimings.group_name_opponent != NULL) AND
			Schedulingtimings.user_id > 0 AND Schedulingtimings.user_id_opponent > 0 AND
			Schedulingtimings.is_bye != 1 
		)";
		
		// Fetch each schedule and check if there is any group name assigned
		$schGroup = $this->Schedulingtimings
					->find()
					->where($condGroupScht)
					->order(["Schedulingtimings.id" => "ASC"])
					->all();
		//$this->prx($schGroup);
		foreach($schGroup as $schrecord)
		{
			// Now for each record, we need to get users of each group and group_opponent and fillgroupuserids
			
			// 1. First do for user_id, now fetch all users of this group for this user_id and this event
			$groupUsersID = $this->Crstudentevents
							->find()
							->where(
								[
									'conventionseason_id' 	=> $conventionSD->id,
									'user_id' 				=> $schrecord->user_id,
									'event_id' 				=> $schrecord->event_id,
									'group_name' 			=> $schrecord->group_name,
								]
							)
							->select('student_id')
							->order(["Crstudentevents.id" => "ASC"])
							->all();
			$studentIds = $groupUsersID->extract('student_id')->toArray();
			//$this->prx($studentIds);
			if(count($studentIds))
			{
				// Update record
				$this->Schedulingtimings->updateAll(
					[
						'group_name_user_ids' => implode(",",$studentIds)
					], 
					[
						"id" => $schrecord->id
					]
				);
			}
			
			
			
			// 2. Now do for user_id_opponent, now fetch all users of this group for this user_id_opponent and this event
			$groupUsersIDOpponent = $this->Crstudentevents
							->find()
							->where(
								[
									'conventionseason_id' 	=> $conventionSD->id,
									'user_id' 				=> $schrecord->user_id_opponent,
									'event_id' 				=> $schrecord->event_id,
									'group_name' 			=> $schrecord->group_name_opponent,
								]
							)
							->select('student_id')
							->order(["Crstudentevents.id" => "ASC"])
							->all();
			$studentIdsOpponent = $groupUsersIDOpponent->extract('student_id')->toArray();
			//$this->prx($studentIdsOpponent);
			if(count($studentIdsOpponent))
			{
				// Update record
				$this->Schedulingtimings->updateAll(
					[
						'group_name_opponent_user_ids' => implode(",",$studentIdsOpponent)
					], 
					[
						"id" => $schrecord->id
					]
				);
			}
		}
		//exit;
		
		// Now check for conflicts
		return $this->redirect(['controller' => 'schedulingtimings', 'action' => 'listconflicts', $convention_season_slug]);
	}
	
	
	public function listconflicts($convention_season_slug=null)
	{
		// First we need to collect all students list of all schools
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		// To get list of all conflict
		$condSchList = array();
		$condSchList[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND 
			Schedulingtimings.user_type = 'Student' AND 
			Schedulingtimings.user_id > 0 AND 
			Schedulingtimings.is_bye != 1 
			
		)";
		// Fetch each schedule and check if there is any group name assigned
		$schedulingtimings = $this->Schedulingtimings
			->find()
			->where($condSchList)
			->order(["Schedulingtimings.id" => "ASC"])
			->all();
		
		// Step 2: Normalize - build a mapping of user_id → their schedules
		$userSchedules = [];
		
		
		foreach($schedulingtimings as $schrecord)
		{
			$day 	= $schrecord->day;
			$start 	= strtotime($schrecord->start_time);
			$end   	= strtotime($schrecord->finish_time);

			// Direct user_id
			if ($schrecord->user_id) {
				$userSchedules[$schrecord->user_id][] = [
					'id' => $schrecord->id,
					'day' => $day,
					'start' => $start,
					'end' => $end
				];
			}
			
			// Direct user_id_opponent
			if ($schrecord->user_id_opponent) {
				$userSchedules[$schrecord->user_id_opponent][] = [
					'id' => $schrecord->id,
					'day' => $day,
					'start' => $start,
					'end' => $end
				];
			}

			// Group members
			foreach (['group_name_user_ids', 'group_name_opponent_user_ids'] as $col) {
				if (!empty($row[$col])) {
					$ids = array_map('trim', explode(',', $row[$col]));
					foreach ($ids as $uid) {
						if ($uid > 0) {
							$userSchedules[$uid][] = [
								'id' => $row['id'],
								'day' => $day,
								'start' => $start,
								'end' => $end
							];
						}
					}
				}
			}
			
		}
		
		// Step 3: Detect conflicts
		$conflicts = [];

		foreach ($userSchedules as $uid => $entries) {
			// Compare each pair of schedules for same user
			for ($i = 0; $i < count($entries); $i++) {
				for ($j = $i + 1; $j < count($entries); $j++) {
					$a = $entries[$i];
					$b = $entries[$j];

					if ($a['day'] == $b['day']) {
						// Check overlap: (startA < endB) and (endA > startB)
						if ($a['start'] < $b['end'] && $a['end'] > $b['start']) {
							$conflicts[$uid][] = [
								'schedule1' => $a['id'],
								'schedule2' => $b['id']
							];
						}
					}
				}
			}
		}
		
		//$this->prx($conflicts);

		// Step 4: Output
		$conflictUIDS 		= [];
		$conflictDBAutoID 	= [];
		foreach ($conflicts as $uid => $conflictList) {
			$conflictUIDS[] = $uid;
			
			// Get db ids of schedules
			foreach ($conflictList as $row) {
				$conflictDBAutoID[] = $row['schedule1'];
				$conflictDBAutoID[] = $row['schedule2'];
			}
		}
		
		$finalDBAutoIDUnique = array_values(array_unique($conflictDBAutoID));
		
		$msG = 'Scheduling completed successfully.';
		
		//$this->prx($conflictUIDS);
		
		// Save conflicted user ids in database
		if(count($conflictUIDS)>0)
		{
			$this->Schedulings->updateAll(['conflict_user_ids' => implode(",",$conflictUIDS)], ["conventionseasons_id" => $conventionSD->id]);
			$msG .= ' There are some conflicts found. Click on resolve conflict button below and resolve conflicts.';
		}
		
		
		// Now filter group db ids where conflict found
		if(count($finalDBAutoIDUnique)>0)
		{
			$finalGroupSchDBIDs = array();
			// filter group ids only and save to db
			foreach($finalDBAutoIDUnique as $group_db_id)
			{
				// check if its a group id
				$checkGroupGame = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $group_db_id])->first();
				if($checkGroupGame->user_type == 'School')
				{
					$finalGroupSchDBIDs[] = $group_db_id;
				}
			}
			// Now update group record db auto ids to db
			if(count($finalGroupSchDBIDs)>0)
			{
				$this->Schedulings->updateAll(['conflict_user_ids_group' => implode(",",$finalGroupSchDBIDs)], ["conventionseasons_id" => $conventionSD->id]);
			}
		}
		
		$this->Flash->success($msG);
		return $this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
	}
	
	
	
	
	
	
	
	public function conflictdone($convention_season_slug=null) {
		
		$this->Flash->success('Scheduling completed successfully. Overlapping and conflicts removed successfully.');
		
		return $this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
		
	}

	private function ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)
	{
		if (empty($schedulingsD) || empty($schedulingsD->id)) {
			$this->Flash->error('Scheduling wizard configuration is missing. Please save the wizard settings before generating schedules.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		$startDate = !empty($schedulingsD->start_date) ? date('Y-m-d', strtotime($schedulingsD->start_date)) : null;
		$actualFirstDay = $this->getWeekDayFromDate($startDate);
		$configuredFirstDay = (string)($schedulingsD->first_day ?? '');
		$numberOfDays = (int)($schedulingsD->number_of_days ?? 0);
		$normalStartingTime = $this->normalizeWizardTime($schedulingsD->normal_starting_time ?? null);
		$normalFinishTime = $this->normalizeWizardTime($schedulingsD->normal_finish_time ?? null);
		$lunchStartTime = $this->normalizeWizardTime($schedulingsD->lunch_time_start ?? null);
		$lunchEndTime = $this->normalizeWizardTime($schedulingsD->lunch_time_end ?? null);

		if ($numberOfDays < 1) {
			$this->Flash->error('Scheduling wizard configuration is incomplete. Number of Days must be at least 1 before schedules can be generated.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if (!$startDate || !$actualFirstDay || $configuredFirstDay === '') {
			$this->Flash->error('Scheduling wizard configuration is incomplete. Please review the Start Date and First Day before generating schedules.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if ($actualFirstDay !== $configuredFirstDay) {
			$this->Flash->error('Scheduling wizard mismatch: Start Date falls on '.$actualFirstDay.', but First Day is set to '.$configuredFirstDay.'. Please fix the wizard before generating schedules.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if (empty($normalStartingTime) || empty($normalFinishTime) || empty($lunchStartTime) || empty($lunchEndTime)) {
			$this->Flash->error('Scheduling wizard configuration is incomplete. Normal and lunch start/finish times are required before schedules can be generated.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if (!$this->isValidTimeRange($normalStartingTime, $normalFinishTime)) {
			$this->Flash->error('Scheduling wizard time window is invalid. Normal Starting Time must be earlier than Normal Finish Time.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if (!$this->isValidTimeRange($lunchStartTime, $lunchEndTime)) {
			$this->Flash->error('Scheduling wizard lunch window is invalid. Lunch Time Start must be earlier than Lunch Time End.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if ($schedulingsD->starting_different_time_first_day_yes_no == 1) {
			$firstDayStart = $this->normalizeWizardTime($schedulingsD->different_first_day_start_time ?? null);
			$firstDayEnd = $this->normalizeWizardTime($schedulingsD->different_first_day_end_time ?? null);
			if (empty($firstDayStart) || empty($firstDayEnd) || !$this->isValidTimeRange($firstDayStart, $firstDayEnd)) {
				$this->Flash->error('Scheduling wizard first-day window is invalid. Different first-day start and finish times are required and must be ordered correctly.');
				return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
			}
		}

		if ($schedulingsD->sports_day_yes_no == 1) {
			$sportsStart = $this->normalizeWizardTime($schedulingsD->sports_day_starting_time ?? null);
			$sportsEnd = $this->normalizeWizardTime($schedulingsD->sports_day_finish_time ?? null);
			if (empty($schedulingsD->sports_day) || empty($sportsStart) || empty($sportsEnd) || !$this->isValidTimeRange($sportsStart, $sportsEnd)) {
				$this->Flash->error('Scheduling wizard sports-day window is invalid. Sports day, start time, and finish time are required and must be ordered correctly.');
				return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
			}
		}

		return null;
	}

	private function resolveSchedulingWizardRecord($conventionSD)
	{
		if (empty($conventionSD) || empty($conventionSD->id)) {
			return null;
		}

		$records = $this->Schedulings->find()
			->where([
				'Schedulings.conventionseasons_id' => $conventionSD->id,
				'Schedulings.convention_id' => $conventionSD->convention_id,
				'Schedulings.season_id' => $conventionSD->season_id,
				'Schedulings.season_year' => $conventionSD->season_year,
			])
			->order(['Schedulings.modified' => 'DESC', 'Schedulings.id' => 'DESC'])
			->all();

		$fallback = null;
		foreach ($records as $record) {
			if ($fallback === null) {
				$fallback = $record;
			}

			$hasCoreWindow =
				!empty($this->normalizeWizardTime($record->normal_starting_time ?? null)) &&
				!empty($this->normalizeWizardTime($record->normal_finish_time ?? null)) &&
				!empty($this->normalizeWizardTime($record->lunch_time_start ?? null)) &&
				!empty($this->normalizeWizardTime($record->lunch_time_end ?? null));

			if ($hasCoreWindow) {
				return $record;
			}
		}

		return $fallback;
	}

	private function normalizeWizardTime($timeValue)
	{
		if ($timeValue === null || $timeValue === '') {
			return null;
		}

		if (is_object($timeValue) && method_exists($timeValue, 'format')) {
			return $timeValue->format('H:i:s');
		}

		$timestamp = strtotime((string)$timeValue);
		if ($timestamp === false) {
			return null;
		}

		return date('H:i:s', $timestamp);
	}

	private function isValidTimeRange($startTime, $endTime): bool
	{
		$start = strtotime((string)$startTime);
		$end = strtotime((string)$endTime);

		if ($start === false || $end === false) {
			return false;
		}

		return $start < $end;
	}

	private function applyNextConventionDay(&$schDay, &$schStartDate, &$cntrDays, $schedulingsD): bool
	{
		$numberOfDays = max(1, (int)($schedulingsD->number_of_days ?? 1));
		if ($cntrDays >= $numberOfDays) {
			return false;
		}

		$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
		$schDay = $this->getWeekDayFromDate($schStartDate);
		$cntrDays++;

		return true;
	}

	private function flashConventionWindowExceeded($schedulingsD): void
	{
		$session = $this->request->getSession();
		if ($this->scheduleWindowWarningShown || $session->read('Scheduling.windowWarningShown')) {
			return;
		}

		$startDate = date('Y-m-d', strtotime($schedulingsD->start_date));
		$numberOfDays = max(1, (int)($schedulingsD->number_of_days ?? 1));
		$endDate = date('Y-m-d', strtotime($startDate.' +'.($numberOfDays - 1).' day'));
		$this->Flash->warning('Scheduling reached the configured convention window from '.date('D j M Y', strtotime($startDate)).' to '.date('D j M Y', strtotime($endDate)).'. Some items could not be placed inside the selected Number of Days.');
		$this->scheduleWindowWarningShown = true;
		$session->write('Scheduling.windowWarningShown', true);
	}

	private function isRoundRobinDayDistributionEnabled($schedulingsD): bool
	{
		return !empty($schedulingsD->round_robin_day_distribution_yes_no);
	}

	private function getScheduleBandMode($schedulingsD): string
	{
		$mode = strtolower((string)($schedulingsD->schedule_band_mode ?? 'single_pass'));
		if (!in_array($mode, ['single_pass', 'two_pass'], true)) {
			return 'single_pass';
		}

		return $mode;
	}

	private function buildBandsForDateRow(array $dateRow, $lunchStart, $lunchEnd): array
	{
		$dayStart = $dateRow['day_start'];
		$dayEnd = $dateRow['day_end'];

		if ($dayStart === null || $dayEnd === null) {
			return [];
		}

		$bands = [];
		if (strtotime((string)$dayStart) < strtotime((string)$lunchStart)) {
			$morningEnd = strtotime((string)$lunchStart) < strtotime((string)$dayEnd) ? $lunchStart : $dayEnd;
			if (strtotime((string)$dayStart) < strtotime((string)$morningEnd)) {
				$bands[] = [
					'date' => $dateRow['date'],
					'day' => $dateRow['day'],
					'band' => 'AM',
					'start' => $dayStart,
					'end' => $morningEnd,
				];
			}
		}

		$afternoonStart = strtotime((string)$lunchEnd) > strtotime((string)$dayStart) ? $lunchEnd : $dayStart;
		if (strtotime((string)$afternoonStart) < strtotime((string)$dayEnd)) {
			$bands[] = [
				'date' => $dateRow['date'],
				'day' => $dateRow['day'],
				'band' => 'PM',
				'start' => $afternoonStart,
				'end' => $dayEnd,
			];
		}

		return $bands;
	}

	private function interleaveConventionBandsByDay(array $bandsByDate): array
	{
		$bands = [];
		$maxBandCount = 0;
		foreach ($bandsByDate as $dayBands) {
			$maxBandCount = max($maxBandCount, count($dayBands));
		}

		for ($bandIndex = 0; $bandIndex < $maxBandCount; $bandIndex++) {
			foreach ($bandsByDate as $dayBands) {
				if (isset($dayBands[$bandIndex])) {
					$bands[] = $dayBands[$bandIndex];
				}
			}
		}

		return $bands;
	}

	private function buildConventionBands($schedulingsD): array
	{
		$numberOfDays = max(1, (int)($schedulingsD->number_of_days ?? 1));
		$mode = $this->getScheduleBandMode($schedulingsD);
		$roundRobinByDay = $this->isRoundRobinDayDistributionEnabled($schedulingsD);
		$baseDate = new \DateTime(date('Y-m-d', strtotime((string)$schedulingsD->start_date)));
		$normalStart = $this->normalizeWizardTime($schedulingsD->normal_starting_time);
		$normalEnd = $this->normalizeWizardTime($schedulingsD->normal_finish_time);
		$lunchStart = $this->normalizeWizardTime($schedulingsD->lunch_time_start);
		$lunchEnd = $this->normalizeWizardTime($schedulingsD->lunch_time_end);
		$hasFirstDayOverride = !empty($schedulingsD->starting_different_time_first_day_yes_no);
		$firstDayStart = $hasFirstDayOverride ? $this->normalizeWizardTime($schedulingsD->different_first_day_start_time) : $normalStart;
		$firstDayEnd = $hasFirstDayOverride ? $this->normalizeWizardTime($schedulingsD->different_first_day_end_time) : $normalEnd;

		$dates = [];
		for ($index = 0; $index < $numberOfDays; $index++) {
			$currentDate = clone $baseDate;
			if ($index > 0) {
				$currentDate->modify('+'.$index.' day');
			}

			$dayStart = $index === 0 ? $firstDayStart : $normalStart;
			$dayEnd = $index === 0 ? $firstDayEnd : $normalEnd;
			$dates[] = [
				'date' => $currentDate->format('Y-m-d'),
				'day' => $currentDate->format('l'),
				'day_start' => $dayStart,
				'day_end' => $dayEnd,
			];
		}

		$bandsByDate = [];
		foreach ($dates as $dateRow) {
			$bandsByDate[] = $this->buildBandsForDateRow($dateRow, $lunchStart, $lunchEnd);
		}

		if ($roundRobinByDay || $mode === 'two_pass') {
			return $this->interleaveConventionBandsByDay($bandsByDate);
		}

		$bands = [];
		foreach ($bandsByDate as $dayBands) {
			foreach ($dayBands as $band) {
				$bands[] = $band;
			}
		}

		return $bands;
	}

	private function buildCategory4EventBands($schedulingsD, $eventTweak4): array
	{
		$bands = $this->buildConventionBands($schedulingsD);
		if (empty($bands)) {
			return [];
		}

		if ($eventTweak4 && !empty($eventTweak4->pinned_day)) {
			$bands = array_values(array_filter($bands, function (array $band) use ($eventTweak4) {
				return $band['day'] === $eventTweak4->pinned_day;
			}));
		}

		if ($eventTweak4 && !empty($eventTweak4->pinned_start_time)) {
			$pinnedStart = date('H:i:s', strtotime($eventTweak4->pinned_start_time));
			$filteredBands = [];
			$firstStartApplied = false;

			foreach ($bands as $band) {
				if ($firstStartApplied) {
					$filteredBands[] = $band;
					continue;
				}

				if (strtotime($pinnedStart) >= strtotime($band['end'])) {
					continue;
				}

				if (strtotime($pinnedStart) > strtotime($band['start'])) {
					$band['start'] = $pinnedStart;
				}

				$filteredBands[] = $band;
				$firstStartApplied = true;
			}

			$bands = $filteredBands;
		}

		return array_values($bands);
	}

	/*
	 * For elimination events, pinned day/time means "start from here" and continue
	 * through later bands/days. It does not mean "schedule only on that day".
	 */
	private function buildEliminationEventBands($schedulingsD, $eventTweak): array
	{
		$bands = $this->buildConventionBands($schedulingsD);
		if (empty($bands)) {
			return [];
		}

		if ($eventTweak && !empty($eventTweak->pinned_day)) {
			$pinnedDay = (string)$eventTweak->pinned_day;
			$firstPinnedIndex = null;
			foreach ($bands as $idx => $band) {
				if ($band['day'] === $pinnedDay) {
					$firstPinnedIndex = $idx;
					break;
				}
			}

			if ($firstPinnedIndex !== null) {
				$bands = array_slice($bands, $firstPinnedIndex);
			}
		}

		if ($eventTweak && !empty($eventTweak->pinned_start_time)) {
			$pinnedStart = date('H:i:s', strtotime((string)$eventTweak->pinned_start_time));
			$filteredBands = [];
			$firstStartApplied = false;

			foreach ($bands as $band) {
				if ($firstStartApplied) {
					$filteredBands[] = $band;
					continue;
				}

				if (strtotime($pinnedStart) >= strtotime($band['end'])) {
					continue;
				}

				if (strtotime($pinnedStart) > strtotime($band['start'])) {
					$band['start'] = $pinnedStart;
				}

				$filteredBands[] = $band;
				$firstStartApplied = true;
			}

			$bands = $filteredBands;
		}

		return array_values($bands);
	}

	private function getConventionDayNumber(string $startDate, string $slotDate): int
	{
		$start = new \DateTime($startDate);
		$slot = new \DateTime($slotDate);
		$diff = $start->diff($slot);

		return ((int)$diff->days) + 1;
	}

	private function getRoomBandCursorTime($conventionSD, int $roomId, string $slotDate, string $bandStart): ?string
	{
		$condRAvail = [];
		$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condRAvail[] = "(Schedulingtimings.room_id = '".$roomId."' AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
		$condRAvail[] = "(DATE(Schedulingtimings.sch_date_time) = '".$slotDate."')";
		$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.finish_time" => "DESC", "Schedulingtimings.id" => "DESC"])->first();

		if (!$checkRoomAvailability) {
			return $bandStart;
		}

		$roomFinishTime = date('H:i:s', strtotime($checkRoomAvailability->finish_time));
		if (strtotime($roomFinishTime) > strtotime($bandStart)) {
			return $roomFinishTime;
		}

		return $bandStart;
	}

	private function getRoomOverlapCursorTime($conventionSD, int $roomId, string $slotDate, string $startTime, string $finishTime): ?string
	{
		$condRoomConflict = [];
		$condRoomConflict[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condRoomConflict[] = "(Schedulingtimings.room_id = '".$roomId."' AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
		$condRoomConflict[] = "(DATE(Schedulingtimings.sch_date_time) = '".$slotDate."')";
		$condRoomConflict[] = "(Schedulingtimings.start_time < '".$finishTime."' AND Schedulingtimings.finish_time > '".$startTime."')";

		$conflictRow = $this->Schedulingtimings->find()
			->select(['finish_time'])
			->where($condRoomConflict)
			->order(["Schedulingtimings.finish_time" => "DESC", "Schedulingtimings.id" => "DESC"])
			->first();

		if (!$conflictRow || empty($conflictRow->finish_time)) {
			return null;
		}

		return date('H:i:s', strtotime((string)$conflictRow->finish_time));
	}

	private function getUserOverlapCursorTime($conventionSD, int $userId, string $slotDate, string $startTime, string $finishTime): ?string
	{
		if ($userId <= 0) {
			return null;
		}

		$condUserConflict = [];
		$condUserConflict[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condUserConflict[] = "(Schedulingtimings.user_id = '".$userId."' AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
		$condUserConflict[] = "(DATE(Schedulingtimings.sch_date_time) = '".$slotDate."')";
		$condUserConflict[] = "(Schedulingtimings.start_time < '".$finishTime."' AND Schedulingtimings.finish_time > '".$startTime."')";

		$conflictRow = $this->Schedulingtimings->find()
			->select(['finish_time'])
			->where($condUserConflict)
			->order(["Schedulingtimings.finish_time" => "DESC", "Schedulingtimings.id" => "DESC"])
			->first();

		if (!$conflictRow || empty($conflictRow->finish_time)) {
			return null;
		}

		return date('H:i:s', strtotime((string)$conflictRow->finish_time));
	}

	private function isValidSlotInsideBand(array $band, array $validSlot): bool
	{
		if (($validSlot['schStartDate'] ?? '') !== $band['date']) {
			return false;
		}

		if (($validSlot['schDay'] ?? '') !== $band['day']) {
			return false;
		}

		if (strtotime((string)($validSlot['start_time'] ?? '')) < strtotime($band['start'])) {
			return false;
		}

		if (strtotime((string)($validSlot['finish_time'] ?? '')) > strtotime($band['end'])) {
			return false;
		}

		return true;
	}

	private function getCategory4ConcurrentBatchSize($eventD): int
	{
		$eventIdNumber = (string)($eventD->event_id_number ?? '');
		if (in_array($eventIdNumber, ['003', '053'], true)) {
			return 40;
		}

		return 1;
	}

	private function hasUserSchedulingConflict($conventionSD, int $userId, string $slotDate, string $slotStartTime, string $slotFinishTime): bool
	{
		if ($userId <= 0 || $slotDate === '') {
			return false;
		}

		$condConflict = [];
		$condConflict[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condConflict[] = "(Schedulingtimings.user_id = '".$userId."' AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
		$condConflict[] = "(DATE(Schedulingtimings.sch_date_time) = '".$slotDate."')";
		$condConflict[] = "(Schedulingtimings.finish_time > '".$slotStartTime."' AND Schedulingtimings.start_time < '".$slotFinishTime."')";

		$conflictCount = $this->Schedulingtimings->find()->where($condConflict)->count();
		return $conflictCount > 0;
	}

	private function placePendingRowsInBandsWithoutUserConflict($conventionSD, $schedulingsD, string $startDate, array $eventBands, array $roomIds, int $eventDurationMinutes, $lunchStart, $lunchEnd, int $eventId, int $batchSize, array $pendingRows): array
	{
		$windowExhausted = false;
		$assignedCount = 0;
		$slotGuard = 0;

		foreach ($eventBands as $eventBand) {
			if (count($pendingRows) === 0) {
				break;
			}

			$roomCursors = [];
			foreach ($roomIds as $roomId) {
				$roomCursor = $this->getRoomBandCursorTime($conventionSD, (int)$roomId, $eventBand['date'], $eventBand['start']);
				if ($roomCursor !== null && strtotime($roomCursor) < strtotime($eventBand['end'])) {
					$roomCursors[(int)$roomId] = $roomCursor;
				}
			}

			while (count($pendingRows) > 0 && count($roomCursors) > 0) {
				$slotGuard++;
				if ($slotGuard > 20000) {
					break 2;
				}

				asort($roomCursors);
				$roomId = (int)array_key_first($roomCursors);
				$cursorStart = $roomCursors[$roomId];
				$cursorFinish = date('H:i:s', strtotime('+ '.$eventDurationMinutes.' minutes', strtotime($cursorStart)));

				if (strtotime($cursorFinish) > strtotime($eventBand['end'])) {
					unset($roomCursors[$roomId]);
					continue;
				}

				$bandDayNumber = $this->getConventionDayNumber($startDate, $eventBand['date']);
				$roomOverlapCursor = $this->getRoomOverlapCursorTime($conventionSD, $roomId, $eventBand['date'], $cursorStart, $cursorFinish);
				if ($roomOverlapCursor !== null && strtotime($roomOverlapCursor) > strtotime($cursorStart)) {
					$roomCursors[$roomId] = $roomOverlapCursor;
					if (strtotime($roomCursors[$roomId]) >= strtotime($eventBand['end'])) {
						unset($roomCursors[$roomId]);
					}
					continue;
				}
				$validSlot = $this->findValidSlot($cursorStart, $cursorFinish, $eventBand['day'], $eventBand['date'], $bandDayNumber, $eventBand['start'], $eventBand['end'], $eventDurationMinutes, $schedulingsD, $lunchStart, $lunchEnd, $roomId, $eventId);

				if (!empty($validSlot['window_exhausted'])) {
					unset($roomCursors[$roomId]);
					continue;
				}

				if (!$this->isValidSlotInsideBand($eventBand, $validSlot)) {
					unset($roomCursors[$roomId]);
					continue;
				}

				$slotStartDate = $validSlot['schStartDate'];
				$slotStartTime = $validSlot['start_time'];
				$slotFinishTime = $validSlot['finish_time'];

				$assignIds = [];
				$remainingRows = [];
				$assignedInThisSlot = 0;
				foreach ($pendingRows as $pendingRow) {
					if ($assignedInThisSlot < $batchSize) {
						$assignIds[] = $pendingRow->id;
						$assignedInThisSlot++;
					} else {
						$remainingRows[] = $pendingRow;
					}
				}

				if (count($assignIds) > 0) {
					$this->Schedulingtimings->updateAll(
						[
							'room_id' => $roomId,
							'day' => $eventBand['day'],
							'start_time' => $slotStartTime,
							'finish_time' => $slotFinishTime,
							'sch_date_time' => $slotStartDate.' '.date('H:i:s', strtotime($slotStartTime)),
							'modified' => date('Y-m-d H:i:s'),
						],
						['id IN' => $assignIds]
					);
					$assignedCount += count($assignIds);
					$pendingRows = $remainingRows;
				}

				$roomCursors[$roomId] = $slotFinishTime;
				if (strtotime($roomCursors[$roomId]) >= strtotime($eventBand['end'])) {
					unset($roomCursors[$roomId]);
				}
			}
		}

		return [
			'pendingRows' => $pendingRows,
			'assignedCount' => $assignedCount,
			'window_exhausted' => $windowExhausted,
		];
	}

	private function placePendingRowsInOverflowWindow($conventionSD, $schedulingsD, array $roomIds, int $eventDurationMinutes, $lunchStart, $lunchEnd, array $pendingRows): array
	{
		$assignedCount = 0;
		if (count($pendingRows) === 0 || count($roomIds) === 0) {
			return [
				'pendingRows' => $pendingRows,
				'assignedCount' => 0,
			];
		}

		$normalStart = $this->normalizeWizardTime($schedulingsD->normal_starting_time ?? null) ?: '09:00:00';
		$normalFinish = $this->normalizeWizardTime($schedulingsD->normal_finish_time ?? null) ?: '17:00:00';
		$lunchStartNorm = $this->normalizeWizardTime($lunchStart ?? null);
		$lunchEndNorm = $this->normalizeWizardTime($lunchEnd ?? null);

		$windowDays = max(1, (int)($schedulingsD->number_of_days ?? 1));
		$baseStartDate = !empty($schedulingsD->start_date) ? date('Y-m-d', strtotime((string)$schedulingsD->start_date)) : date('Y-m-d');
		$overflowDate = date('Y-m-d', strtotime($baseStartDate.' +'.$windowDays.' days'));

		$dayGuard = 0;
		while (count($pendingRows) > 0) {
			$dayGuard++;
			if ($dayGuard > 365) {
				break;
			}

			$roomCursors = [];
			foreach ($roomIds as $roomIdRaw) {
				$roomId = (int)$roomIdRaw;
				if ($roomId > 0) {
					$roomCursors[$roomId] = $normalStart;
				}
			}

			$slotGuard = 0;
			while (count($pendingRows) > 0 && count($roomCursors) > 0) {
				$slotGuard++;
				if ($slotGuard > 20000) {
					break;
				}

				asort($roomCursors);
				$roomId = (int)array_key_first($roomCursors);
				$cursorStart = $roomCursors[$roomId];
				$cursorFinish = date('H:i:s', strtotime('+ '.$eventDurationMinutes.' minutes', strtotime($cursorStart)));

				if (strtotime($cursorFinish) > strtotime($normalFinish)) {
					unset($roomCursors[$roomId]);
					continue;
				}

				if ($this->slotOverlapsRange($cursorStart, $cursorFinish, $lunchStartNorm, $lunchEndNorm)) {
					if ($lunchEndNorm === null || strtotime($lunchEndNorm) >= strtotime($normalFinish)) {
						unset($roomCursors[$roomId]);
						continue;
					}
					$roomCursors[$roomId] = $lunchEndNorm;
					continue;
				}

				$roomOverlapCursorTime = $this->getRoomOverlapCursorTime($conventionSD, $roomId, $overflowDate, $cursorStart, $cursorFinish);
				if ($roomOverlapCursorTime !== null && strtotime($roomOverlapCursorTime) > strtotime($cursorStart)) {
					$roomCursors[$roomId] = $roomOverlapCursorTime;
					if (strtotime($roomCursors[$roomId]) >= strtotime($normalFinish)) {
						unset($roomCursors[$roomId]);
					}
					continue;
				}

				$schdata = array_shift($pendingRows);
				$this->Schedulingtimings->updateAll(
				[
					'room_id' => $roomId,
					'day' => date('l', strtotime($overflowDate)),
					'start_time' => $cursorStart,
					'finish_time' => $cursorFinish,
					'sch_date_time' => $overflowDate.' '.date('H:i:s', strtotime($cursorStart)),
					'modified' => date('Y-m-d H:i:s'),
				],
				['id' => $schdata->id]
				);

				$assignedCount++;
				$roomCursors[$roomId] = $cursorFinish;
				if (strtotime($roomCursors[$roomId]) >= strtotime($normalFinish)) {
					unset($roomCursors[$roomId]);
				}
			}

			$overflowDate = date('Y-m-d', strtotime($overflowDate.' +1 day'));
		}

		return [
			'pendingRows' => $pendingRows,
			'assignedCount' => $assignedCount,
		];
	}

	private function slotOverlapsRange($slotStart, $slotFinish, $rangeStart, $rangeEnd): bool
	{
		if (empty($slotStart) || empty($slotFinish) || empty($rangeStart) || empty($rangeEnd)) {
			return false;
		}

		return strtotime((string)$slotStart) < strtotime((string)$rangeEnd)
			&& strtotime((string)$slotFinish) > strtotime((string)$rangeStart);
	}
	
	
	/**
	 * Validate a time slot against all break periods (lunch, judging, sports day, events-after-sport).
	 * Loops until the slot is clean — fixes the "blind jump" bug where sequential break checks
	 * could skip over a break after being pushed past another one.
	 */
	private function findValidSlot($start_time, $finish_time, $schDay, $schStartDate, $cntrDays, $normal_starting_time, $normal_finish_time, $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID = null, $eventID = null)
	{
		$maxIterations = 20;
		$iteration = 0;
		$windowExhausted = false;
		$roomAvailableFrom = null;
		$roomAvailableTo = null;
		$eventAvailableFrom = null;
		$eventAvailableTo = null;

		if (!empty($eventID)) {
			$eventTweak = $this->Schedulingeventtweaks->find()->where([
				'Schedulingeventtweaks.conventionseasons_id' => $schedulingsD->conventionseasons_id,
				'Schedulingeventtweaks.event_id' => (int)$eventID,
			])->first();
			if ($eventTweak) {
				if (!empty($eventTweak->available_from_time)) {
					$eventAvailableFrom = date('H:i:s', strtotime($eventTweak->available_from_time));
				}
				if (!empty($eventTweak->available_to_time)) {
					$eventAvailableTo = date('H:i:s', strtotime($eventTweak->available_to_time));
				}
			}
		}

		if (!empty($roomID)) {
			$roomD = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $roomID])->first();
			if ($roomD) {
				if (!empty($roomD->available_from)) {
					$roomAvailableFrom = date('H:i:s', strtotime($roomD->available_from));
				}
				if (!empty($roomD->available_to)) {
					$roomAvailableTo = date('H:i:s', strtotime($roomD->available_to));
				}
			}
		}
		
		do {
			$slotChanged = false;
			$iteration++;

			if ($eventAvailableFrom !== null && strtotime($start_time) < strtotime($eventAvailableFrom)) {
				$start_time = $eventAvailableFrom;
				$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
				$slotChanged = true;
			}

			if ($eventAvailableTo !== null && strtotime($finish_time) > strtotime($eventAvailableTo)) {
				if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
					$windowExhausted = true;
					break;
				}
				$normal_starting_time = $this->normalizeWizardTime($schedulingsD->normal_starting_time);
				$normal_finish_time = $this->normalizeWizardTime($schedulingsD->normal_finish_time);
				$start_time = $eventAvailableFrom !== null ? $eventAvailableFrom : $normal_starting_time;
				$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
				$slotChanged = true;
			}
			
			/* Lunch break check */
			if( (strtotime($start_time)>=strtotime($lunch_time_start) && strtotime($start_time)<=strtotime($lunch_time_end)) || 
				(strtotime($finish_time)>=strtotime($lunch_time_start) && strtotime($finish_time)<=strtotime($lunch_time_end)))
			{
				$start_time 	= $lunch_time_end;
				$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($lunch_time_end)));
				$slotChanged = true;
				
				if(strtotime($finish_time)>strtotime($normal_finish_time))
				{
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time 	= $this->normalizeWizardTime($schedulingsD->normal_starting_time);
					$normal_finish_time 	= $this->normalizeWizardTime($schedulingsD->normal_finish_time);
					$start_time 	= $normal_starting_time;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
				}
			}
			
			/* Judging breaks check */
			if($schedulingsD->judging_breaks_yes_no == 1)
			{
				// Morning break
				$jb_morning_start 	= $this->normalizeWizardTime($schedulingsD->judging_breaks_morning_break_starting_time);
				$jb_morning_end 	= $this->normalizeWizardTime($schedulingsD->judging_breaks_morning_break_finish_time);
				
				if( (strtotime($start_time)>=strtotime($jb_morning_start) && strtotime($start_time)<=strtotime($jb_morning_end)) || 
				(strtotime($finish_time)>=strtotime($jb_morning_start) && strtotime($finish_time)<=strtotime($jb_morning_end)))
				{
					$start_time 	= $jb_morning_end;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($jb_morning_end)));
					$slotChanged = true;
				}
				
				if(strtotime($finish_time)>=strtotime($normal_finish_time))
				{
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time 	= $this->normalizeWizardTime($schedulingsD->normal_starting_time);
					$normal_finish_time 	= $this->normalizeWizardTime($schedulingsD->normal_finish_time);
					$start_time 	= $normal_starting_time;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
					$slotChanged = true;
				}
				
				// Afternoon break
				$jb_afternoon_start = $this->normalizeWizardTime($schedulingsD->judging_breaks_afternoon_break_start_time);
				$jb_afternoon_end 	= $this->normalizeWizardTime($schedulingsD->judging_breaks_afternoon_break_finish_time);
				
				if( (strtotime($start_time)>=strtotime($jb_afternoon_start) && strtotime($start_time)<=strtotime($jb_afternoon_end)) || 
				(strtotime($finish_time)>=strtotime($jb_afternoon_start) && strtotime($finish_time)<=strtotime($jb_afternoon_end)))
				{
					$start_time 	= $jb_afternoon_end;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($jb_afternoon_end)));
					$slotChanged = true;
				}
				
				if(strtotime($finish_time)>=strtotime($normal_finish_time))
				{
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time 	= $this->normalizeWizardTime($schedulingsD->normal_starting_time);
					$normal_finish_time 	= $this->normalizeWizardTime($schedulingsD->normal_finish_time);
					$start_time 	= $normal_starting_time;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
					$slotChanged = true;
				}
			}
			
			/* Sports day check */
			if($schedulingsD->sports_day_yes_no == 1)
			{
				$sports_day					= $schedulingsD->sports_day;
				$sports_day_starting_time	= $this->normalizeWizardTime($schedulingsD->sports_day_starting_time);
				$sports_day_finish_time		= $this->normalizeWizardTime($schedulingsD->sports_day_finish_time);
				
				if($sports_day == $schDay)
				{
					if( (strtotime($start_time)>=strtotime($sports_day_starting_time) && strtotime($start_time)<=strtotime($sports_day_finish_time)) || 
					(strtotime($finish_time)>=strtotime($sports_day_starting_time) && strtotime($finish_time)<=strtotime($sports_day_finish_time)))
					{
						$start_time 	= $sports_day_finish_time;
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($sports_day_finish_time)));
						$slotChanged = true;
					}
					
					if(strtotime($finish_time)>=strtotime($normal_finish_time))
					{
						if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
							$windowExhausted = true;
							break;
						}
						$normal_starting_time 	= $this->normalizeWizardTime($schedulingsD->normal_starting_time);
						$normal_finish_time 	= $this->normalizeWizardTime($schedulingsD->normal_finish_time);
						$start_time 	= $normal_starting_time;
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						$slotChanged = true;
					}
				}
			}
			
			/* Events after sport are allowed in the configured post-sport window.
			 * The lunch and sports-day checks above already move slots out of blocked periods.
			 */

			/* Room availability window check */
			if (!empty($roomID) && ($roomAvailableFrom || $roomAvailableTo)) {
				$effectiveRoomStart = $roomAvailableFrom ?: $normal_starting_time;
				$effectiveRoomEnd = $roomAvailableTo ?: $normal_finish_time;

				if (strtotime($start_time) < strtotime($effectiveRoomStart)) {
					$start_time = $effectiveRoomStart;
					$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					$slotChanged = true;
				}

				if (strtotime($finish_time) > strtotime($effectiveRoomEnd)) {
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time = $this->normalizeWizardTime($schedulingsD->normal_starting_time);
					$normal_finish_time = $this->normalizeWizardTime($schedulingsD->normal_finish_time);
					$start_time = (strtotime($effectiveRoomStart) > strtotime($normal_starting_time)) ? $effectiveRoomStart : $normal_starting_time;
					$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					$slotChanged = true;
				}
			}
			
		} while ($slotChanged && $iteration < $maxIterations);
		
		return [
			'start_time' => $start_time,
			'finish_time' => $finish_time,
			'schDay' => $schDay,
			'schStartDate' => $schStartDate,
			'cntrDays' => $cntrDays,
			'normal_starting_time' => $normal_starting_time,
			'normal_finish_time' => $normal_finish_time,
			'window_exhausted' => $windowExhausted,
		];
	}

	private function getConventionSeasonBySlug($conventionSeasonSlug)
	{
		return $this->Conventionseasons
			->find()
			->where(['Conventionseasons.slug' => $conventionSeasonSlug])
			->contain(['Conventions'])
			->first();
	}

}

?>
