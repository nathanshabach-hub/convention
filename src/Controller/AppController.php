<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Cake\Mailer\Email;
use Cake\Controller\Component\FlashComponent;
use Cake\Datasource\ConnectionManager;


/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
#[\AllowDynamicProperties]
class AppController extends Controller{
	public $Timezones = null;
	public $Eventtypes = null;
	public $Companies = null;
	public $Users = null;
	public $Admins = null;
	public $Settings = null;
	public $Emailtemplates = null;
	public $Conventions = null;
	public $Conventionseasons = null;
	public $Events = null;
	public $Divisions = null;
	public $Seasons = null;
	public $Conventionregistrations = null;
	public $Conventionregistrationteachers = null;
	public $Eventsubmissions = null;
	public $Schedulingtimings = null;
	public $Schedulings = null;
	public $Conventionrooms = null;
	public $Schedulingeventtweaks = null;

    
	public function initialize(): void{
	parent::initialize();
		$this->loadModel('Timezones');
		$this->loadModel('Eventtypes');
		$this->loadModel('Companies');
		$this->loadModel('Users');
		$this->loadModel('Admins');
		$this->loadModel('Settings');
		
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Eventsubmissions");
		$this->loadModel("Schedulingtimings");
		$this->loadModel("Schedulings");
		$this->loadModel("Conventionrooms");
		$this->loadModel("Schedulingeventtweaks");
	}
	
	public function beforeRender(EventInterface $event): void {
        parent::beforeRender($event);
		
		$adminInfo = $this->Admins->find()->where(['Admins.id' => 1])->first();
        $this->set('adminInfo', $adminInfo);
		//$this->prx($adminInfo);
		
        
		
		// to check if school admin is logged in, then show header dropdown
		$user_id 	= $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		
		if($user_id>0)
		{
			// to get lists of registered conventions for this season
			$season_id = $this->getCurrentSeason();
			$seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
			$seasonYear = $seasonD ? $seasonD->season_year : null;
			//$this->prx($seasonD);
			$userD = $this->Users->find()->where(['Users.id' => $user_id])->first();
			
			$userConvHeaderDD = [];
			
			if ($user_type == "School") {
				$userConvHeaderDD = $this->getSchoolConventionsForCurrentSeason($user_id, $season_id, $seasonYear);
			}
			
			// now get convention id for teacher
			if($user_type == "Teacher_Parent")
			{
				$conventionIDSHeader = array();
				$conventionIDSHeader[] 	= 0;
				$conventionregistrationteachers = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.user_id' => $userD->school_id,'Conventionregistrationteachers.teacher_id' => $user_id,'Conventionregistrationteachers.season_id' => $season_id,'Conventionregistrationteachers.season_year' => $seasonYear])->order(['Conventionregistrationteachers.id' => 'ASC'])->all();
				foreach($conventionregistrationteachers as $convregt)
				{
					if(!in_array($convregt->convention_id,(array)$conventionIDSHeader))
					{
						$conventionIDSHeader[] 	= $convregt->convention_id;
					}
				}
				$userConvHeaderDD = $this->buildConventionDropdownByIds($conventionIDSHeader, $seasonYear);
			}
			
			// now get convention id for judge + supervisor as a judge
			if($user_type == "Judge" || $this->request->session()->read("current_session_profile_type")  == "Judge")
			{
				$conventionIDSHeader = array();
				$conventionIDSHeader[] 	= 0;
				$conventionregistrations = $this->Conventionregistrations->find()->where(['Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id,'Conventionregistrations.season_year' => $seasonYear,'Conventionregistrations.status' => 1])->order(['Conventionregistrations.id' => 'ASC'])->all();
				foreach($conventionregistrations as $convreg)
				{
					if(!in_array($convreg->convention_id,(array)$conventionIDSHeader))
					{
						$conventionIDSHeader[] 	= $convreg->convention_id;
					}
				}
				$userConvHeaderDD = $this->buildConventionDropdownByIds($conventionIDSHeader, $seasonYear);
			}
			
			$this->set('userConvHeaderDD', $userConvHeaderDD);
			
		}

	}
    
//  public function beforeFilter(EventInterface $event): void {
//        $this->set('loggedIn', $this->Auth->loggedIn());
//    }
    
    public function getAdminInfo() {
		
		$adminInfo = $this->Admins->find()->where(['Admins.id' => 1])->first();
        return $adminInfo;
	}
	
	public function fetchUserType($user_id=NULL) {
		
		$userInfo = $this->Users->find()->select(['user_type'])->where(['Users.id' => $user_id])->first();
        return $userInfo->user_type;
	}
	
	public function autoSubmitEvent($arrAutoSubmit=NULL) {
		
		if($arrAutoSubmit['event_id']>0)
		{
			$event_id 						= $arrAutoSubmit['event_id'];
			$conventionregistration_id 		= $arrAutoSubmit['conventionregistration_id'];
			$student_id 					= $arrAutoSubmit['student_id'];
			
			$eventD 		= $this->Events->find()->where(['Events.id' => $event_id])->first();
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $conventionregistration_id])->first();
			
			// to check if event submission done for this event, student, convention reg
			$checkSubmission = $this->Eventsubmissions->find()->where(['Eventsubmissions.event_id' => $event_id,'Eventsubmissions.conventionregistration_id' => $conventionregistration_id, 'Eventsubmissions.conventionseason_id' => $conventionRegD->conventionseason_id,'Eventsubmissions.student_id' => $student_id])->first();
			if(!$checkSubmission)
			{
				// submit event
				$eventsubmissions = $this->Eventsubmissions->newEntity([]);
				$dataES = $this->Eventsubmissions->patchEntity($eventsubmissions, array());

				$dataES->slug 						= 'event-submission-'.$conventionregistration_id.'-'.time().'-'.rand(100,1000000);
				$dataES->conventionregistration_id	= $conventionregistration_id;
				$dataES->conventionseason_id		= $conventionRegD->conventionseason_id;
				$dataES->convention_id				= $conventionRegD->convention_id;
				$dataES->user_id					= $conventionRegD->user_id;
				$dataES->season_id 					= $conventionRegD->season_id;
				$dataES->season_year 				= $conventionRegD->season_year;
				$dataES->event_id 					= $eventD->id;
				$dataES->event_id_number 			= $eventD->event_id_number;
				$dataES->student_id 				= $student_id;
				
				if($eventD->group_event_yes_no == 1)
				{
					$dataES->student_id 			= 0;
				}
				else
				{
					$dataES->group_name 			= NULL;
				}
				
				
				$dataES->uploaded_by_user_id 			= $conventionRegD->user_id;
				
				//$data->book_ids 					= '';
				$dataES->created = date('Y-m-d H:i:s');
				$dataES->modified = date('Y-m-d H:i:s');

				$resultES = $this->Eventsubmissions->save($dataES);
			}
			
			return true;
		}
		
		return false;
	}
	
	public function getNextWeekDay($schDay=null) {//echo 'here'; exit;
		
		$weekArr = array(
			0 => "Monday",
			1 => "Tuesday",
			2 => "Wednesday",
			3 => "Thursday",
			4 => "Friday",
			5 => "Saturday",
			6 => "Sunday",
		);
		
		if($schDay == "Sunday")
		{
			$schNextDay = 'Monday';
		}
		else
		{
			$keyWeek 		= array_search ($schDay, $weekArr);
			$schNextDay 	= $weekArr[$keyWeek+1];
		}
		
		//echo $schNextDay;exit;
		
		return $schNextDay;
	}

	public function getWeekDayFromDate($date=null) {
		if (empty($date)) {
			return null;
		}

		$timestamp = strtotime((string)$date);
		if ($timestamp === false) {
			return null;
		}

		return date('l', $timestamp);
	}
	
	public function sortAssocArr($assocArr=array()) {
		
		$values = array_values($assocArr);
		$keys 	= array_keys($assocArr);

		array_multisort($values, SORT_ASC, $keys);

		$assocArr = array_combine($keys, $values);
		
		return $assocArr;
		
	}
	public function getAgeFromBirthYear($birth_year=NULL) {
		
		if($birth_year)
		{
			return date("Y") - $birth_year;
		}
		else
		{
			return 0;
		}
	}
	
	public function checkAgeWithGroup($studentAge=NULL,$event_grp_name=NULL) {
		
		$returnVal = 0;
		
		//echo $studentAge;echo '--'.$event_grp_name;exit;
		
		if($studentAge>0 && $event_grp_name>0)
		{;
			//compare age based on event group
			if($event_grp_name == 5 && $studentAge>=8 && $studentAge<=11)
			{
				// 5. U11
				$returnVal = 1;
			}
			else
			if($event_grp_name == 1 && $studentAge<14)
			{
				// 1. U14
				$returnVal = 1;
			}
			else 
			if($event_grp_name == 2 && $studentAge<16)
			{
				// 2. U16
				$returnVal = 1;
			}
			else 
			if($event_grp_name == 3 && $studentAge<17)
			{
				// 3. U17
				$returnVal = 1;;
			}
			else
			if($event_grp_name == 4)
			{
				// 4. Open
				$returnVal = 1;
			}
		}
		
		return $returnVal;
	}
	
	public function checkGenderWithEvent($studentGender=NULL,$event_gender=NULL) {
		
		$returnVal = 0;
		
		if($studentGender == 'F' || $studentGender == 'M')
		{
			//compare age based on event gender
			if(empty($event_gender) || $event_gender == '')
			{
				// 1. No restriction
				$returnVal = 1;
			}
			else 
			if($event_gender == 'F' && $studentGender == 'F')
			{
				// 2. Gender match
				$returnVal = 1;
			}
			else 
			if($event_gender == 'M' && $studentGender == 'M')
			{
				// 3. Gender match
				$returnVal = 1;;
			}
		}
		
		return $returnVal;
	}
	
	public function changeToMysqlTimeFormat($time_data=NULL) {
		
		if($time_data)
		{
			$timestamp_data = strtotime($time_data);
			$mysqlFormat = date('Y-m-d H:i:s', $timestamp_data);
			$mysqlFormatExplode = explode(" ",$mysqlFormat);
			return $mysqlFormatExplode[1];
		}
		else
		{
			return 0;
		}
	}
	
	public function getSettingsInfo() {
		
		$settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
        return $settingsInfo;
	}
	
	public function getMinMaxEvents($conv_reg_id = 0) {
		
		$min_events_student = 0;
		$max_events_student = 0;
		
		// first to get from convention season
		if($conv_reg_id)
		{
			// to get conv reg details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $conv_reg_id])->contain(['Conventionseasons'])->first();
			//$this->prx($conventionRegD);
			
			if($conventionRegD->Conventionseasons['min_events_student']>0 && $conventionRegD->Conventionseasons['max_events_student']>0)
			{
				$min_events_student = $conventionRegD->Conventionseasons['min_events_student'];
				$max_events_student = $conventionRegD->Conventionseasons['max_events_student'];
			}
			else
			{
				// get from Settings
				$getSettingsInfo = $this->getSettingsInfo();
				$min_events_student = $getSettingsInfo->min_events_student;
				$max_events_student = $getSettingsInfo->max_events_student;
			}
		}
		
		return array('min_events_student' => $min_events_student,'max_events_student' => $max_events_student);
		
	}
	
	public function getCurrentSeason(){
		$today = date('Y-m-d');

		$activeSeason = $this->Conventionseasons->find()
			->contain(['Seasons'])
			->where([
				'Seasons.status' => 1,
				'Conventionseasons.registration_start_date <=' => $today,
				'Conventionseasons.registration_end_date >=' => $today,
			])
			->order(['Seasons.season_year' => 'DESC', 'Seasons.id' => 'DESC'])
			->first();

		if ($activeSeason && !empty($activeSeason->season_id)) {
			return (int)$activeSeason->season_id;
		}

		$seasonD = $this->Seasons->find()->where(['Seasons.status' => 1])->order(['Seasons.season_year' => 'DESC', 'Seasons.id' => 'DESC'])->first();
		return $seasonD ? (int)$seasonD->id : 0;
    }

    public function getCurrentSeasonConventions($seasonId = null, $seasonYear = null) {
        $seasonId = (int)($seasonId ?: $this->getCurrentSeason());
        if ($seasonId <= 0) {
            return [];
        }

        $seasonD = $this->Seasons->find()->where(['Seasons.id' => $seasonId])->first();
        $seasonYear = $seasonYear ?? ($seasonD ? $seasonD->season_year : null);

        $conventionIds = [];
        $conventionIds[] = 0;

        $conventionSeasons = $this->Conventionseasons->find()
            ->where([
                'Conventionseasons.season_id' => $seasonId,
                'Conventionseasons.season_year' => $seasonYear,
            ])
            ->order(['Conventionseasons.id' => 'ASC'])
            ->all();

        foreach ($conventionSeasons as $conventionSeason) {
            if (!in_array((int)$conventionSeason->convention_id, $conventionIds, true)) {
                $conventionIds[] = (int)$conventionSeason->convention_id;
            }
        }

		return $this->buildConventionDropdownByIds($conventionIds, $seasonYear);
    }

    public function getSchoolConventionsForCurrentSeason($userId = null, $seasonId = null, $seasonYear = null) {
        $userId = (int)($userId ?: $this->request->session()->read('user_id'));
        $seasonId = (int)($seasonId ?: $this->getCurrentSeason());
        if ($userId <= 0 || $seasonId <= 0) {
            return [];
        }

        $seasonD = $this->Seasons->find()->where(['Seasons.id' => $seasonId])->first();
        $seasonYear = $seasonYear ?? ($seasonD ? $seasonD->season_year : null);

        $registeredConventionIds = [];
        $registeredConventionIds[] = 0;

        $conventionRegistrations = $this->Conventionregistrations->find()
            ->where([
                'Conventionregistrations.user_id' => $userId,
                'Conventionregistrations.season_id' => $seasonId,
                'Conventionregistrations.season_year' => $seasonYear,
            ])
            ->order(['Conventionregistrations.id' => 'ASC'])
            ->all();

        foreach ($conventionRegistrations as $conventionRegistration) {
            if (!in_array((int)$conventionRegistration->convention_id, $registeredConventionIds, true)) {
                $registeredConventionIds[] = (int)$conventionRegistration->convention_id;
            }
        }

        if (count($registeredConventionIds) <= 1) {
            return $this->getCurrentSeasonConventions($seasonId, $seasonYear);
        }

		return $this->buildConventionDropdownByIds($registeredConventionIds, $seasonYear);
    }

	private function buildConventionDropdownByIds(array $conventionIds = [], $seasonYear = null) {
		$normalizedConventionIds = [];
		foreach ($conventionIds as $conventionId) {
			$conventionId = (int)$conventionId;
			if ($conventionId > 0 && !in_array($conventionId, $normalizedConventionIds, true)) {
				$normalizedConventionIds[] = $conventionId;
			}
		}

		if (count($normalizedConventionIds) === 0) {
			return [];
		}

		$conventionIdsImploded = implode(',', $normalizedConventionIds);
		$condConvention = [];
		$condConvention[] = "(Conventions.id IN ($conventionIdsImploded))";
		$condConvention[] = "(Conventions.status = '1')";

		$conventionRows = $this->Conventions->find()
			->where($condConvention)
			->order(['Conventions.name' => 'ASC'])
			->all();

		$dropdown = [];
		foreach ($conventionRows as $conventionRow) {
			$label = $conventionRow->name;
			if (!empty($seasonYear)) {
				$label .= ' (' . $seasonYear . ')';
			}
			$dropdown[(int)$conventionRow->id] = $label;
		}

		return $dropdown;
	}

	private function applySlotConstraintsForConflict($conventionSD, $schedulingD, $eventId, $roomId, $playTime, $candidateDate, $candidateStartTime)
	{
		if (empty($schedulingD)) {
			return ['ok' => false];
		}

		$normal_finish_time   = date("H:i:s", strtotime($schedulingD->normal_finish_time));
		$normal_starting_time = date("H:i:s", strtotime($schedulingD->normal_starting_time));
		$convStartDate        = date('Y-m-d', strtotime($schedulingD->start_date));
		$numberOfDays         = max(1, (int)($schedulingD->number_of_days ?? 1));
		$convEndDate          = date('Y-m-d', strtotime($convStartDate . ' +' . ($numberOfDays - 1) . ' day'));

		$eventTweak = $this->Schedulingeventtweaks->find()->where([
			'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
			'Schedulingeventtweaks.event_id' => $eventId,
		])->first();

		$eventStart = null;
		$eventEnd = null;
		if ($eventTweak) {
			if (!empty($eventTweak->available_from_time)) {
				$eventStart = date('H:i:s', strtotime($eventTweak->available_from_time));
			}
			if (!empty($eventTweak->available_to_time)) {
				$eventEnd = date('H:i:s', strtotime($eventTweak->available_to_time));
			}
		}

		$roomD = null;
		if ((int)$roomId > 0) {
			$roomD = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $roomId])->first();
		}

		$guard = 0;
		while ($guard < 60) {
			$guard++;
			if ($candidateDate > $convEndDate) {
				return ['ok' => false];
			}

			if ($eventTweak && !empty($eventTweak->pinned_day) && date('l', strtotime($candidateDate)) !== $eventTweak->pinned_day) {
				$candidateDate = date('Y-m-d', strtotime($candidateDate . ' +1 day'));
				$candidateStartTime = $normal_starting_time;
				continue;
			}

			$roomStart = $normal_starting_time;
			$roomEnd = $normal_finish_time;
			if ($roomD) {
				if (!empty($roomD->available_from)) {
					$roomStart = date('H:i:s', strtotime($roomD->available_from));
				}
				if (!empty($roomD->available_to)) {
					$roomEnd = date('H:i:s', strtotime($roomD->available_to));
				}
			}

			if (strtotime($candidateStartTime) < strtotime($roomStart)) {
				$candidateStartTime = $roomStart;
			}

			if ($eventStart !== null && strtotime($candidateStartTime) < strtotime($eventStart)) {
				$candidateStartTime = $eventStart;
			}

			$candidateFinishTime = date("H:i:s", strtotime($candidateStartTime . " +$playTime minute"));

			if (
				strtotime($candidateFinishTime) > strtotime($normal_finish_time) ||
				strtotime($candidateFinishTime) > strtotime($roomEnd) ||
				($eventEnd !== null && strtotime($candidateFinishTime) > strtotime($eventEnd))
			) {
				$candidateDate = date('Y-m-d', strtotime($candidateDate . ' +1 day'));
				$candidateStartTime = $normal_starting_time;
				continue;
			}

			return [
				'ok' => true,
				'date' => $candidateDate,
				'start' => $candidateStartTime,
				'finish' => $candidateFinishTime,
			];
		}

		return ['ok' => false];
	}

	public function nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId = null, $depth = 0)
	{
		if ((int)$depth >= 300) {
			return $conflict;
		}

		if ($recordId === null) {
			return $conflict;
		}

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		if (!$conventionSD) {
			return $conflict;
		}
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();

		$schedulingTimingsD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $recordId])->first();
		if (!$schedulingTimingsD || !$schedulingD) {
			return $conflict;
		}

		$schDateTime = date('Y-m-d', strtotime($base_sch_date_time));
		$finishTime = $base_finish_time;
		$playTime = (strtotime($conflict['finish_time']) - strtotime($conflict['start_time'])) / 60;
		$userId = $conflict['user_id'];

		$nextStartTime = date("H:i:s", strtotime($finishTime . " +1 minute"));
		$nextFinishTime = date("H:i:s", strtotime($nextStartTime . " +$playTime minute"));

		$normal_finish_time = date("H:i:s", strtotime($schedulingD->normal_finish_time));
		$normal_starting_time = date("H:i:s", strtotime($schedulingD->normal_starting_time));
		$convStartDate = date('Y-m-d', strtotime($schedulingD->start_date));
		$numberOfDays = max(1, (int)($schedulingD->number_of_days ?? 1));
		$convEndDate = date('Y-m-d', strtotime($convStartDate . ' +' . ($numberOfDays - 1) . ' day'));

		if (strtotime($nextFinishTime) > strtotime($normal_finish_time)) {
			$nextDate = date('Y-m-d', strtotime($schDateTime . ' +1 day'));
			if ($nextDate > $convEndDate) {
				return $conflict;
			}
			$schDateTime = $nextDate;
			$nextStartTime = $normal_starting_time;
			$nextFinishTime = date("H:i:s", strtotime($nextStartTime . " +$playTime minute"));
		}

		$constraint = $this->applySlotConstraintsForConflict(
			$conventionSD,
			$schedulingD,
			(int)$schedulingTimingsD->event_id,
			(int)$schedulingTimingsD->room_id,
			(int)$playTime,
			$schDateTime,
			$nextStartTime
		);
		if (empty($constraint['ok'])) {
			return $conflict;
		}
		$schDateTime = $constraint['date'];
		$nextStartTime = $constraint['start'];
		$nextFinishTime = $constraint['finish'];

		$schDateTime = $schDateTime . ' ' . $nextStartTime;
		$conflict['sch_date_time'] = $schDateTime;
		$conflict['start_time'] = $nextStartTime;
		$conflict['finish_time'] = $nextFinishTime;
		$conflict['day'] = date('l', strtotime($schDateTime));

		$base_start_time = $nextStartTime;
		$base_finish_time = $nextFinishTime;
		$base_sch_date_time = $schDateTime;

		$cond = [
			'conventionseasons_id' => $conventionSD->id,
			'user_type' => 'Student',
			'is_bye' => 0,
			'user_id' => $userId,
			'DATE(sch_date_time) =' => $schDateTime,
			'start_time >' => $nextStartTime,
		];
		$nextBooking = $this->Schedulingtimings
			->find()
			->where($cond)
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->first();

		if (!empty($nextBooking)) {
			if (strtotime($nextFinishTime) >= strtotime($nextBooking->start_time)) {
				return $this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId, $depth + 1);
			}
		}

		$opponentBooking = $this->checkForOpponent($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time);
		if (!empty($opponentBooking)) {
			if (strtotime($nextFinishTime) >= strtotime($opponentBooking->start_time)) {
				return $this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId, $depth + 1);
			}
		}

		$lunch_time_start = date("H:i:s", strtotime($schedulingD->lunch_time_start));
		$lunch_time_end = date("H:i:s", strtotime($schedulingD->lunch_time_end));
		if (
			(strtotime($base_start_time) >= strtotime($lunch_time_start) && strtotime($base_start_time) <= strtotime($lunch_time_end)) ||
			(strtotime($base_finish_time) >= strtotime($lunch_time_start) && strtotime($base_finish_time) <= strtotime($lunch_time_end))
		) {
			$base_start_time = $lunch_time_start;
			$base_finish_time = $lunch_time_end;
			return $this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId, $depth + 1);
		}

		if ($schedulingD->judging_breaks_yes_no == 1) {
			$judging_breaks_morning_break_starting_time = date("H:i:s", strtotime($schedulingD->judging_breaks_morning_break_starting_time));
			$judging_breaks_morning_break_finish_time = date("H:i:s", strtotime($schedulingD->judging_breaks_morning_break_finish_time));
			if (
				(strtotime($base_start_time) >= strtotime($judging_breaks_morning_break_starting_time) && strtotime($base_start_time) <= strtotime($judging_breaks_morning_break_finish_time)) ||
				(strtotime($base_finish_time) >= strtotime($judging_breaks_morning_break_starting_time) && strtotime($base_finish_time) <= strtotime($judging_breaks_morning_break_finish_time))
			) {
				$base_start_time = $judging_breaks_morning_break_starting_time;
				$base_finish_time = $judging_breaks_morning_break_finish_time;
				return $this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId, $depth + 1);
			}

			$judging_breaks_afternoon_break_start_time = date("H:i:s", strtotime($schedulingD->judging_breaks_afternoon_break_start_time));
			$judging_breaks_afternoon_break_finish_time = date("H:i:s", strtotime($schedulingD->judging_breaks_afternoon_break_finish_time));
			if (
				(strtotime($base_start_time) >= strtotime($judging_breaks_afternoon_break_start_time) && strtotime($base_start_time) <= strtotime($judging_breaks_afternoon_break_finish_time)) ||
				(strtotime($base_finish_time) >= strtotime($judging_breaks_afternoon_break_start_time) && strtotime($base_finish_time) <= strtotime($judging_breaks_afternoon_break_finish_time))
			) {
				$base_start_time = $judging_breaks_afternoon_break_start_time;
				$base_finish_time = $judging_breaks_afternoon_break_finish_time;
				return $this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId, $depth + 1);
			}
		}

		if ($schedulingD->sports_day_yes_no == 1) {
			$sports_day = $schedulingD->sports_day;
			$sports_day_starting_time = date("H:i:s", strtotime($schedulingD->sports_day_starting_time));
			$sports_day_finish_time = date("H:i:s", strtotime($schedulingD->sports_day_finish_time));

			if ($sports_day == $schedulingTimingsD->day) {
				if (
					(strtotime($base_start_time) >= strtotime($sports_day_starting_time) && strtotime($base_start_time) <= strtotime($sports_day_finish_time)) ||
					(strtotime($base_finish_time) >= strtotime($sports_day_starting_time) && strtotime($base_finish_time) <= strtotime($sports_day_finish_time))
				) {
					$base_start_time = $sports_day_starting_time;
					$base_finish_time = $sports_day_finish_time;
					return $this->nextBookings($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $recordId, $depth + 1);
				}
			}
		}

		return $conflict;
	}

	public function checkForOpponent($convention_season_slug, $conflict, $base_start_time, $base_finish_time, $base_sch_date_time)
	{
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		if (!$conventionSD) {
			return [];
		}

		$schDateTime = date('Y-m-d', strtotime($base_sch_date_time));
		$finishTime = $base_finish_time;
		$userIdOpponent = $conflict['user_id_opponent'];

		if (empty($userIdOpponent)) {
			return [];
		}

		$cond = [
			'conventionseasons_id' => $conventionSD->id,
			'user_type' => 'Student',
			'is_bye' => 0,
			'user_id' => $userIdOpponent,
			'DATE(sch_date_time) =' => $schDateTime,
			'start_time >' => $finishTime,
		];

		return $this->Schedulingtimings
			->find()
			->where($cond)
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->first();
	}

	public function userConflictRecordsByUserId($convention_season_slug, $userId)
	{
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		if (!$conventionSD) {
			return [];
		}

		$condSchList = array();
		$condSchList[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND 
			Schedulingtimings.user_type = 'Student' AND 
			Schedulingtimings.is_bye = 0 AND 
			(Schedulingtimings.user_id ='".$userId."' OR Schedulingtimings.user_id_opponent ='".$userId."')
		)";
		$userConflictRecords = $this->Schedulingtimings
			->find()
			->where($condSchList)
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();

		$data = [];
		foreach ($userConflictRecords as $userConflictRecord) {
			$date = date('Y-m-d', strtotime($userConflictRecord->sch_date_time));
			$data[] = [
				'id' => $userConflictRecord->id,
				'user_id' => $userConflictRecord->user_id,
				'user_id_opponent' => $userConflictRecord->user_id_opponent,
				'sch_date_time' => $userConflictRecord->sch_date_time,
				'start_time_with_date' => $date . ' ' . date('H:i:s', strtotime($userConflictRecord->start_time)),
				'finish_time_with_date' => $date . ' ' . date('H:i:s', strtotime($userConflictRecord->finish_time)),
				'start_time' => $userConflictRecord->start_time,
				'finish_time' => $userConflictRecord->finish_time,
			];
		}

		$conflictsById = [];
		for ($i = 0; $i < count($data); $i++) {
			for ($j = $i + 1; $j < count($data); $j++) {
				$aStart = strtotime($data[$i]['start_time_with_date']);
				$aEnd = strtotime($data[$i]['finish_time_with_date']);
				$bStart = strtotime($data[$j]['start_time_with_date']);
				$bEnd = strtotime($data[$j]['finish_time_with_date']);

				if ($aStart < $bEnd && $aEnd > $bStart) {
					$userConflictRecordId = $data[$i]['id'];
					$conflict = [
						'id' => $data[$j]['id'],
						'sch_date_time' => $data[$j]['sch_date_time'],
						'start_time' => $data[$j]['start_time'],
						'finish_time' => $data[$j]['finish_time'],
						'user_id' => $data[$j]['user_id'],
						'user_id_opponent' => $data[$j]['user_id_opponent'],
					];

					if (array_key_exists($userConflictRecordId, $conflictsById)) {
						$conflictsById[$userConflictRecordId]['conflicts'][] = $conflict;
					} else {
						$conflictsById[$userConflictRecordId] = [
							'id' => $data[$i]['id'],
							'sch_date_time' => $data[$i]['sch_date_time'],
							'start_time' => $data[$i]['start_time'],
							'finish_time' => $data[$i]['finish_time'],
							'user_id' => $data[$i]['user_id'],
							'user_id_opponent' => $data[$i]['user_id_opponent'],
							'conflicts' => [$conflict],
						];
					}
				}
			}
		}

		return $conflictsById;
	}

	public function findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds, $depth = 0)
	{
		if ((int)$depth >= 300) {
			return $conflict;
		}

		$schedulingDFNT = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conflict->conventionseasons_id])->first();

		$schDate = date('Y-m-d', strtotime($base_sch_date_time));
		$schDateTime = date('Y-m-d', strtotime($base_sch_date_time));
		$finishTime = $base_finish_time;

		$playTime = (strtotime($conflict->finish_time) - strtotime($conflict->start_time)) / 60;
		$nextStartTime = date("H:i:s", strtotime($finishTime . " +1 minute"));
		$nextFinishTime = date("H:i:s", strtotime($nextStartTime . " +$playTime minute"));

		if ($schedulingDFNT) {
			$fnt_normal_finish_time = date("H:i:s", strtotime($schedulingDFNT->normal_finish_time));
			$fnt_normal_starting_time = date("H:i:s", strtotime($schedulingDFNT->normal_starting_time));
			$fnt_convStartDate = date('Y-m-d', strtotime($schedulingDFNT->start_date));
			$fnt_numberOfDays = max(1, (int)($schedulingDFNT->number_of_days ?? 1));
			$fnt_convEndDate = date('Y-m-d', strtotime($fnt_convStartDate . ' +' . ($fnt_numberOfDays - 1) . ' day'));

			if (strtotime($nextFinishTime) > strtotime($fnt_normal_finish_time)) {
				$fnt_nextDate = date('Y-m-d', strtotime($schDate . ' +1 day'));
				if ($fnt_nextDate > $fnt_convEndDate) {
					return $conflict;
				}
				$schDate = $fnt_nextDate;
				$schDateTime = $fnt_nextDate;
				$nextStartTime = $fnt_normal_starting_time;
				$nextFinishTime = date("H:i:s", strtotime($nextStartTime . " +$playTime minute"));
			}
		}

		$conventionSDForConstraint = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $conflict->conventionseasons_id])->first();
		if ($conventionSDForConstraint) {
			$constraint = $this->applySlotConstraintsForConflict(
				$conventionSDForConstraint,
				$schedulingDFNT,
				(int)$conflict->event_id,
				(int)$conflict->room_id,
				(int)$playTime,
				$schDate,
				$nextStartTime
			);
			if (empty($constraint['ok'])) {
				return $conflict;
			}
			$schDate = $constraint['date'];
			$schDateTime = $constraint['date'];
			$nextStartTime = $constraint['start'];
			$nextFinishTime = $constraint['finish'];
		}

		$schDateTime = $schDateTime . ' ' . $nextStartTime;
		$conflict->sch_date_time = $schDateTime;
		$conflict->start_time = $nextStartTime;
		$conflict->finish_time = $nextFinishTime;
		$conflict->day = date('l', strtotime($schDateTime));

		$base_start_time = $nextStartTime;
		$base_finish_time = $nextFinishTime;
		$base_sch_date_time = $schDateTime;

		foreach ($allUserIds as $userId) {
			$userId = (string)$userId;
			if ($userId === '' || $userId === '0') {
				continue;
			}

			$checkGBusy = $this->Schedulingtimings->find()
				->where([
					'Schedulingtimings.conventionseasons_id' => $conflict->conventionseasons_id,
				])
				->andWhere(function ($exp, $query) use ($schDate) {
					return $exp->eq(
						$query->func()->date([
							'Schedulingtimings.sch_date_time' => 'identifier',
						]),
						$schDate
					);
				})
				->andWhere(function ($exp) use ($nextStartTime, $nextFinishTime) {
					return $exp->add(
						"'$nextStartTime' < Schedulingtimings.finish_time AND '$nextFinishTime' > Schedulingtimings.start_time"
					);
				})
				->andWhere(function ($exp) use ($userId) {
					return $exp->or_([
						$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_user_ids)"),
						$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_opponent_user_ids)"),
					]);
				})
				->count();

			if ($checkGBusy > 0) {
				return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds, $depth + 1);
			}
		}

		if ($schedulingDFNT) {
			$lunch_time_start = date("H:i:s", strtotime($schedulingDFNT->lunch_time_start));
			$lunch_time_end = date("H:i:s", strtotime($schedulingDFNT->lunch_time_end));
			if (
				(strtotime($base_start_time) >= strtotime($lunch_time_start) && strtotime($base_start_time) <= strtotime($lunch_time_end)) ||
				(strtotime($base_finish_time) >= strtotime($lunch_time_start) && strtotime($base_finish_time) <= strtotime($lunch_time_end))
			) {
				return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds, $depth + 1);
			}

			if ($schedulingDFNT->judging_breaks_yes_no == 1) {
				$judging_breaks_morning_break_starting_time = date("H:i:s", strtotime($schedulingDFNT->judging_breaks_morning_break_starting_time));
				$judging_breaks_morning_break_finish_time = date("H:i:s", strtotime($schedulingDFNT->judging_breaks_morning_break_finish_time));
				if (
					(strtotime($base_start_time) >= strtotime($judging_breaks_morning_break_starting_time) && strtotime($base_start_time) <= strtotime($judging_breaks_morning_break_finish_time)) ||
					(strtotime($base_finish_time) >= strtotime($judging_breaks_morning_break_starting_time) && strtotime($base_finish_time) <= strtotime($judging_breaks_morning_break_finish_time))
				) {
					return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds, $depth + 1);
				}

				$judging_breaks_afternoon_break_start_time = date("H:i:s", strtotime($schedulingDFNT->judging_breaks_afternoon_break_start_time));
				$judging_breaks_afternoon_break_finish_time = date("H:i:s", strtotime($schedulingDFNT->judging_breaks_afternoon_break_finish_time));
				if (
					(strtotime($base_start_time) >= strtotime($judging_breaks_afternoon_break_start_time) && strtotime($base_start_time) <= strtotime($judging_breaks_afternoon_break_finish_time)) ||
					(strtotime($base_finish_time) >= strtotime($judging_breaks_afternoon_break_start_time) && strtotime($base_finish_time) <= strtotime($judging_breaks_afternoon_break_finish_time))
				) {
					return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds, $depth + 1);
				}
			}

			if ($schedulingDFNT->sports_day_yes_no == 1) {
				$sports_day = $schedulingDFNT->sports_day;
				$sports_day_starting_time = date("H:i:s", strtotime($schedulingDFNT->sports_day_starting_time));
				$sports_day_finish_time = date("H:i:s", strtotime($schedulingDFNT->sports_day_finish_time));

				if ($sports_day == $conflict->day) {
					if (
						(strtotime($base_start_time) >= strtotime($sports_day_starting_time) && strtotime($base_start_time) <= strtotime($sports_day_finish_time)) ||
						(strtotime($base_finish_time) >= strtotime($sports_day_starting_time) && strtotime($base_finish_time) <= strtotime($sports_day_finish_time))
					) {
						return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds, $depth + 1);
					}
				}
			}

			if ($schedulingDFNT->sports_day_having_events_after_sport_yes_no == 1) {
				$sports_day = $schedulingDFNT->sports_day;
				$sports_day_other_starting_time = date("H:i:s", strtotime($schedulingDFNT->sports_day_other_starting_time));
				$sports_day_other_finish_time = date("H:i:s", strtotime($schedulingDFNT->sports_day_other_finish_time));

				if ($sports_day == $conflict->day) {
					if (
						(strtotime($base_start_time) >= strtotime($sports_day_other_starting_time) && strtotime($base_start_time) <= strtotime($sports_day_other_finish_time)) ||
						(strtotime($base_finish_time) >= strtotime($sports_day_other_starting_time) && strtotime($base_finish_time) <= strtotime($sports_day_other_finish_time))
					) {
						return $this->findNextTime($conflict, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds, $depth + 1);
					}
				}
			}
		}

		return $conflict;
	}
	
	public function isAuthorized($user){
        // Admin can access every action
        if (isset($user['role']) && $user['role'] === 'admin') {
            return true;
        }
        return false;
    }
	
	public function verifyRecatpcha($aData)
	{
		//echo 'ddddddddd<pre>';pr($aData);exit;
		if(!$aData)
		{
			return false;
		} 
		if(isset($aData['g-recaptcha-response']) && !empty($aData['g-recaptcha-response']))
		{
			$recaptcha_secret = SECRETKEY;
			$url = "https://www.google.com/recaptcha/api/siteverify?secret=".$recaptcha_secret."&response=".$aData['g-recaptcha-response']; 
			$response = json_decode(@file_get_contents($url));   

			if($response->success == true)
			{
				return true;
			}
			else
			{
				return false; 
			} 
		}
		else
		{
			return false;
		}
	}
    
    
	// general login check for user
	function userLoginCheck() {
		// $returnUrl = $this->request->getParam('pass')->url;
        $returnUrl = $this->request->getRequestTarget();
        $userid =$this->request->session()->read("user_id");
        $this->loadModel("Users");
        $isExists = null;
        if (!empty($userid)) {
            $isExists = $this->Users->find()->where(['Users.id' => $userid, 'Users.activation_status' => 1, 'Users.status' => 1])->select(['id'])->first();
        }
        if (empty($isExists)) {
            $msgString = "Please Login"; 
            $this->request->session()->delete('user_id');
            $this->request->session()->delete('email_address');
            $this->request->session()->delete('user_type');
            $this->request->session()->delete('last_login');
			
            $this->Flash->error($msgString);
            $this->request->session()->write("returnUrl", $returnUrl);
            $this->redirect('/users/login');
        }
    }
	
	// to check subscribers type login
	function schoolAdminLoginCheck() {  
		if($this->request->session()->read("user_type") != "School")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	// to check individuals user type login
	function teacherLoginCheck() {  
		if($this->request->session()->read("user_type") != "Teacher_Parent")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	// to check individuals user type login
	function judgeLoginCheck() {  
		if($this->request->session()->read("user_type") != "Judge")
		{
			$msgString = "Un-authorize access.";
			$this->Flash->error($msgString);
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
	
	function multiLoginCheck($usersTypesList=null) { 
        $user_type =$this->request->session()->read("user_type");
		//echo $user_type;exit;
        if (!in_array($user_type,(array)$usersTypesList)) {
            $msgString = "Unauthorize access !!!"; 
            $this->Flash->error($msgString);
            $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
        }
    }
	
	function checkRegistrationStillOpen($convention_registration_id=NULL) {
        
		$regAccepted = 0;
        $this->loadModel("Conventionregistrations");
		
		// to get conv reg details
        $convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $convention_registration_id])->contain(['Conventionseasons'])->first();
		//$this->prx($convRegD);
        if($convRegD->id>0)
		{
           // to check if registration closed
			$currDateTime = time();
			$regStartDateTime = strtotime($convRegD->Conventionseasons['registration_start_date']);
			$regEndDateTime = strtotime($convRegD->Conventionseasons['registration_end_date']);
			
			if($currDateTime>=$regStartDateTime && $currDateTime<=$regEndDateTime)
			{
				// registration accepted
				$regAccepted = 1;
			}
        }
		
		if($regAccepted == 0)
		{
			$this->Flash->error('Sorry, registration has been closed.');
            $this->redirect('/users/dashboard');
		}
    }
	
	public function getByePlayerScheduling($total_students) {
		$total_students = (int)$total_students;
		if ($total_students <= 2) {
			return 0;
		}

		// Standard single-elimination BYE count:
		// BYEs = nextPowerOfTwo(total_students) - total_students
		$nextPower = 1;
		while ($nextPower < $total_students) {
			$nextPower *= 2;
		}

		$byeTeamCount = $nextPower - $total_students;
		return max(0, (int)$byeTeamCount);
	}
	
	public function clearSchedulingtimings($convention_season_slug=NULL)
	{
		if($convention_season_slug)
		{
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
			//$this->prx($conventionSD);
			
			$this->Schedulingtimings->deleteAll(["conventionseasons_id" => $conventionSD->id, "convention_id" => $conventionSD->convention_id, "season_id" => $conventionSD->season_id, "season_year" => $conventionSD->season_year]);
		}
		
		return true;
	}
    
    public function getSlug($str, $table='Admins'){
        $slug = Text::slug($str);
        $slug = strtolower($slug);
        //$slug = 'dinesh-dhaker';
        $isRecord =  $this->$table->find()->where([$table . '.slug like' => $slug . '%'])->order([$table.'.id'=>'DESC'])->first();
        
        if($isRecord){
            $oldslug = explode('-', $isRecord->slug);
            $last = array_pop($oldslug);
            $slug = $last;
            if(is_numeric($last)){
                $last = $last + 1;
                $slug = $slug.'-'.$last;
            }else{
               $slug = $slug.'-'.$last.'-1'; 
            }
            
            return $slug.time();
        }else{
            return $slug;
        }
    }
	
	function valid_email($str)
	{
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
	}
	
	function prx($arrV = NULL)
	{
		echo '<pre>';
		print_r($arrV);
		echo '</pre>';
		exit;
	}
	
	function pr($arrV = NULL)
	{
		echo '<pre>';
		print_r($arrV);
		echo '</pre>';
		return;
		//exit;
	}
    
    
            
    
}
?>