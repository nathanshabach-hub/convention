<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\I18n\I18n;


#[\AllowDynamicProperties]
class GroupsController extends AppController {

    public $paginate = ['limit' => 50];
	
	public function initialize(): void {
        parent::initialize();
		$this->loadComponent('RequestHandler');
		$this->loadComponent('PImage');
		$this->loadComponent('PImageTest');

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Conventionseasonevents");
		$this->loadModel("Events");
		$this->loadModel("Crstudentevents");
		$this->loadModel("Eventsubmissions");
		$this->loadModel("Combinerequests");
    }

    public function viewlist() {

        $this->userLoginCheck();
        $this->multiLoginCheck(['School','Teacher_Parent']);
		
        $this->set("title_for_layout", "Student Grouping" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('active_cr_studentgroups','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = null;
		if (!empty($user_id)) {
			$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		}
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			// to get list of all events selected for this convention season
			$condConvSeason = array();
			$condConvSeason[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionRegD->conventionseason_id."')";
			$condConvSeason[] = "(Conventionseasonevents.convention_id = '".$conventionRegD->convention_id."')";
			$condConvSeason[] = "(Conventionseasonevents.season_id = '".$conventionRegD->season_id."')";
			$condConvSeason[] = "(Conventionseasonevents.season_year = '".$conventionRegD->season_year."')";
			$allEventsOfThisConvS = $this->Conventionseasonevents->find()->where($condConvSeason)->all();
			$arrEvConvS = array();
			$arrEvConvS[] = 0;
			foreach($allEventsOfThisConvS as $dataevconvseason)
			{
				$arrEvConvS[] = $dataevconvseason->event_id;
			}
			$arrEvConvSImplode = implode(",",$arrEvConvS);
			
			//To list all events that selecyed for this conv season
			$condition[] = "(Events.id IN ($arrEvConvSImplode))";
			$condition[] = "(Events.status  = '1')";
			$condition[] = "(Events.group_event_yes_no = '1')";
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$events = $this->Events->find()->where($condition)->order(['Events.event_id_number' => 'ASC','Events.event_name' => 'ASC'])->all();
		$this->set('events',$events);
    }
	
	public function eventgroups($event_slug = null) {
		
		$this->userLoginCheck();
		//$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Event groups " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_studentgroups','active');
		
		$this->set('event_slug',$event_slug);
		$stGArr = [];
		
        $user_id = $this->request->session()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			$this->set('conventionRegD', $conventionRegD);
			
			// to get event details
			$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
			$this->syncApprovedCombinedRequestsToCurrentSchool($conventionRegD, $eventD);
			//echo $eventD->id;
			
			// to get all the students of this event
			$condStEvNoG = array();
			$condStEvNoG[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."')";
			$condStEvNoG[] = "(Crstudentevents.convention_id = '".$conventionRegD->convention_id."')";
			$condStEvNoG[] = "(Crstudentevents.season_id = '".$conventionRegD->season_id."')";
			$condStEvNoG[] = "(Crstudentevents.season_year = '".$conventionRegD->season_year."')";
			$condStEvNoG[] = "(Crstudentevents.event_id = '".$eventD->id."')";
			$condStEvNoG[] = "(Crstudentevents.group_name = '' OR Crstudentevents.group_name IS NULL)";
			$eventStudentsList = $this->Crstudentevents->find()->where($condStEvNoG)->all();
			$studentsArr = array();
			$studentsArr[] = 0;
			foreach($eventStudentsList as $evstudent)
			{
				$studentsArr[] = $evstudent->student_id;
			}
			//$this->prx($studentsArr);
			
			// to get list of students name
			$studentDD = array();
			$condSE = array();
			$condSE[] = "(Users.id IN (".implode(",",$studentsArr).") )";
			$studentL = $this->Users->find()->where($condSE)->order(["Users.first_name" => "ASC","Users.middle_name" => "ASC"])->all();
			$schoolNameCache = [];
			foreach($studentL as $studentel)
			{
				$studentAge = date("Y") - $studentel->birth_year;
				$studentLabel = $studentel->first_name.' '.$studentel->middle_name.' '.$studentel->last_name.' (Age: '.$studentAge.' Years)';
				$studentSchoolId = (int)($studentel->school_id ?? 0);
				if ($studentSchoolId > 0 && $studentSchoolId !== (int)$conventionRegD->user_id) {
					if (!array_key_exists($studentSchoolId, $schoolNameCache)) {
						$sourceSchool = $this->Users->find()->where(['Users.id' => $studentSchoolId])->first();
						$schoolNameCache[$studentSchoolId] = $sourceSchool ? trim($sourceSchool->first_name . ' ' . $sourceSchool->last_name) : '';
					}
					$sourceSchoolName = trim((string)$schoolNameCache[$studentSchoolId]);
					if ($sourceSchoolName !== '') {
						$studentLabel .= ' [Combined from ' . $sourceSchoolName . ']';
					} else {
						$studentLabel .= ' [Combined student]';
					}
				}
				$studentDD[$studentel->id] = $studentLabel;
			}
			$this->set('studentDD', $studentDD);
			//$this->prx($condSE);
			
			// now group the students based on their selected groups
			$stGroups = $this->Crstudentevents->find()->where(['Crstudentevents.conventionregistration_id' => $conventionRegD->id,'Crstudentevents.convention_id' => $conventionRegD->convention_id,'Crstudentevents.season_id' => $conventionRegD->season_id,'Crstudentevents.season_year' => $conventionRegD->season_year,'Crstudentevents.event_id' => $eventD->id,'Crstudentevents.group_name != ' => ''])->all();
			$stGArr = array();
			foreach($stGroups as $stgroup)
			{
				if($stgroup->group_name != "" && !empty($stgroup->group_name))
				{
					$stGArr[$stgroup->group_name][] = $stgroup->student_id;
				}
			}
			$this->ensureAutomaticGroupSubmissions($eventD, $conventionRegD, array_keys($stGArr));
			$this->set('stGArr', $stGArr);

			$eligibleFillInStudents = [];
			$totalStudentsEvent = $this->Crstudentevents->find()->where([
				'Crstudentevents.conventionregistration_id' => $conventionRegD->id,
				'Crstudentevents.convention_id' => $conventionRegD->convention_id,
				'Crstudentevents.season_id' => $conventionRegD->season_id,
				'Crstudentevents.season_year' => $conventionRegD->season_year,
				'Crstudentevents.event_id' => $eventD->id,
			])->count();
			$minStudentsPerGroup = (int)$eventD->min_no;
			$hasIncompleteGroup = false;
			$maxGroupDeficit = 0;
			if ($minStudentsPerGroup > 0) {
				foreach ($stGArr as $studentIdsInGroup) {
					$groupCount = count($studentIdsInGroup);
					if ($groupCount > 0 && $groupCount < $minStudentsPerGroup) {
						$hasIncompleteGroup = true;
						$maxGroupDeficit = max($maxGroupDeficit, $minStudentsPerGroup - $groupCount);
					}
				}
			}

			$eventNeedsStudents = $minStudentsPerGroup > 0 && $totalStudentsEvent < $minStudentsPerGroup;
			$showFillInPanel = $eventNeedsStudents || $hasIncompleteGroup;
			$fillInNeededCount = $eventNeedsStudents
				? ($minStudentsPerGroup - $totalStudentsEvent)
				: $maxGroupDeficit;

			if ($showFillInPanel) {
				$schoolId = $userDetails->user_type === 'School' ? $user_id : $userDetails->school_id;
				$registeredStudentIds = $this->Conventionregistrationstudents->find()
					->where([
						'Conventionregistrationstudents.conventionregistration_id' => $conventionRegD->id,
						'Conventionregistrationstudents.status' => 1,
						'Conventionregistrationstudents.student_id >' => 0,
					])
					->extract('student_id')
					->map(static function ($studentId) {
						return (int)$studentId;
					})
					->toList();
				$registeredStudentIds = $registeredStudentIds ?: [0];
				$schoolStudents = $this->Users->find()->where([
					'Users.school_id' => $schoolId,
					'Users.user_type' => 'Student',
					'Users.status' => 1,
					'Users.id IN' => $registeredStudentIds,
				])->order(['Users.first_name' => 'ASC', 'Users.last_name' => 'ASC'])->all();
				$eventLimits = $this->getMinMaxEvents($conventionRegD->id);
				foreach ($schoolStudents as $candidate) {
					$alreadyInEvent = $this->Crstudentevents->find()->where([
						'Crstudentevents.conventionregistration_id' => $conventionRegD->id,
						'Crstudentevents.student_id' => $candidate->id,
						'Crstudentevents.event_id' => $eventD->id,
					])->count() > 0;
					if ($alreadyInEvent) {
						continue;
					}
					$studentAge = $conventionRegD->season_year - (int)$candidate->birth_year;
					$studentGender = strtoupper(substr((string)$candidate->gender, 0, 1));
					if (!$this->checkAgeWithGroup($studentAge, $eventD->event_grp_name) || !$this->checkGenderWithEvent($studentGender, $eventD->event_gender)) {
						continue;
					}
					$eventCount = $this->Crstudentevents->find()->where([
						'Crstudentevents.conventionregistration_id' => $conventionRegD->id,
						'Crstudentevents.student_id' => $candidate->id,
					])->count();
					if ((int)$eventLimits['max_events_student'] > 0 && $eventCount >= (int)$eventLimits['max_events_student']) {
						continue;
					}
					$eligibleFillInStudents[] = [
						'name' => trim($candidate->first_name . ' ' . $candidate->middle_name . ' ' . $candidate->last_name),
						'age' => $studentAge,
						'events' => $eventCount,
					];
				}
			}
			$this->set('eligibleFillInStudents', $eligibleFillInStudents);
			$this->set('showFillInPanel', $showFillInPanel);
			$this->set('fillInNeededCount', $fillInNeededCount);

			$nextGroupNumber = 1;
			foreach (array_keys($stGArr) as $groupName) {
				if (is_numeric($groupName)) {
					$nextGroupNumber = max($nextGroupNumber, (int)$groupName + 1);
				}
			}
			$this->set('nextGroupName', (string)$nextGroupNumber);
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// to create group
		if ($this->request->is('post')) {
			$postData = $this->request->getData();
			if (($postData['Groups']['action'] ?? '') === 'auto_solo_groups') {
				if ((int)$eventD->group_event_yes_no !== 1 || (int)$eventD->min_no > 1 || (int)$eventD->max_no > 2) {
					$this->Flash->error('Automatic solo groups are only available for variable group events with a maximum of two students.');
					return $this->redirect(['controller' => 'groups', 'action' => 'eventgroups', $event_slug]);
				}

				$ungroupedStudents = $this->Crstudentevents->find()->where([
					'Crstudentevents.conventionregistration_id' => $conventionRegD->id,
					'Crstudentevents.convention_id' => $conventionRegD->convention_id,
					'Crstudentevents.season_id' => $conventionRegD->season_id,
					'Crstudentevents.season_year' => $conventionRegD->season_year,
					'Crstudentevents.event_id' => $eventD->id,
					'OR' => [
						['Crstudentevents.group_name' => ''],
						['Crstudentevents.group_name IS' => null],
					],
				])->all();
				$nextGroupNumber = 1;
				foreach (array_keys($stGArr) as $groupName) {
					if (is_numeric($groupName)) {
						$nextGroupNumber = max($nextGroupNumber, (int)$groupName + 1);
					}
				}
				$createdCount = 0;
				foreach ($ungroupedStudents as $ungroupedStudent) {
					$this->Crstudentevents->updateAll(['group_name' => (string)$nextGroupNumber, 'modified' => date('Y-m-d H:i:s')], [
						'Crstudentevents.id' => $ungroupedStudent->id,
					]);
					$this->ensureAutomaticGroupSubmissions($eventD, $conventionRegD, [(string)$nextGroupNumber]);
					$nextGroupNumber++;
					$createdCount++;
				}
				$this->Flash->success($createdCount.' solo group(s) created.');
				return $this->redirect(['controller' => 'groups', 'action' => 'eventgroups', $event_slug]);
			}
		}

		if ($this->request->is('post')) {
            
			$student_ids = $this->request->getData()['Groups']['student_id'] ?? [];
			$student_ids = array_values(array_unique(array_filter(array_map('intval', (array)$student_ids))));
			$group_name = trim((string)($this->request->getData()['Groups']['group_name'] ?? ''));
			$minStudents = (int)$eventD->min_no;
			$maxStudents = (int)$eventD->max_no;

			if (!$student_ids) {
				$this->Flash->error('Please select at least one student.');
				return $this->redirect(['controller' => 'groups', 'action' => 'eventgroups', $event_slug]);
			}
			if ($minStudents > 0 && count($student_ids) < $minStudents) {
				$this->Flash->error('Select at least '.$minStudents.' students for this event.');
				return $this->redirect(['controller' => 'groups', 'action' => 'eventgroups', $event_slug]);
			}
			if ($maxStudents > 0 && count($student_ids) > $maxStudents) {
				$this->Flash->error('Select no more than '.$maxStudents.' students for this event.');
				return $this->redirect(['controller' => 'groups', 'action' => 'eventgroups', $event_slug]);
			}
            
			// now update group name
			foreach($student_ids as $student_id)
			{
				$this->Crstudentevents->updateAll(['group_name' => $group_name],
				[
				"conventionregistration_id" 		=> $conventionRegD->id,
				"convention_id" 					=> $conventionRegD->convention_id,
				"season_id" 						=> $conventionRegD->season_id,
				"season_year" 						=> $conventionRegD->season_year,
				"student_id" 						=> $student_id,
				"event_id" 							=> $eventD->id
				]);
				$this->mirrorCombinedGroupNameToStudentSchool((int)$student_id, (string)$group_name, $conventionRegD, $eventD);
				
				// now check if this event is a group event and auto submission is yes,
				// then submit once only
				//$this->prx($eventD);
				if($eventD->auto_submission == 1 && $eventD->group_event_yes_no == 1)
				{
					// now check if submission already done for this Group
					$checkSubmission = $this->Eventsubmissions->find()->where(['Eventsubmissions.event_id' => $eventD->id,'Eventsubmissions.conventionregistration_id' => $conventionRegD->id,'Eventsubmissions.group_name' => $group_name])->first();
					//$this->prx($checkSubmission);
					if(!$checkSubmission)
					{
						// submit event
						$eventsubmissions = $this->Eventsubmissions->newEntity([]);
						$dataES = $this->Eventsubmissions->patchEntity($eventsubmissions, array());

						$dataES->slug 						= 'event-submission-'.$conventionRegD->id.'-'.time().'-'.rand(100,1000000);
						$dataES->conventionregistration_id	= $conventionRegD->id;
						$dataES->conventionseason_id		= $conventionRegD->conventionseason_id;
						$dataES->convention_id				= $conventionRegD->convention_id;
						$dataES->user_id					= $conventionRegD->user_id;
						$dataES->season_id 					= $conventionRegD->season_id;
						$dataES->season_year 				= $conventionRegD->season_year;
						$dataES->event_id 					= $eventD->id;
						$dataES->event_id_number 			= $eventD->event_id_number;
						$dataES->student_id 				= 0;
						$dataES->group_name 				= $group_name;
						
						$dataES->uploaded_by_user_id 			= $conventionRegD->user_id;
						
						//$data->book_ids 					= '';
						$dataES->created = date('Y-m-d H:i:s');
						$dataES->modified = date('Y-m-d H:i:s');

						$resultES = $this->Eventsubmissions->save($dataES);
					}
				}
			}
			
			$this->Flash->success('Group created successfully.');
			$this->redirect(['controller' => 'groups', 'action' => 'eventgroups',$event_slug]);
        }
    }

	private function syncApprovedCombinedRequestsToCurrentSchool($conventionRegD, $eventD): void {
		if (!$conventionRegD || !$eventD) {
			return;
		}

		$incomingApprovedRequests = $this->Combinerequests->find()
			->where([
				'Combinerequests.status' => 1,
				'Combinerequests.conventionseason_id' => $conventionRegD->conventionseason_id,
				'Combinerequests.event_id' => $eventD->id,
				'Combinerequests.combine_with_user_id' => $conventionRegD->user_id,
			])
			->all();

		foreach ($incomingApprovedRequests as $approvedRequest) {
			$sourceConventionReg = $this->Conventionregistrations->find()
				->where([
					'Conventionregistrations.user_id' => $approvedRequest->user_id,
					'Conventionregistrations.conventionseason_id' => $approvedRequest->conventionseason_id,
				])
				->first();
			if (!$sourceConventionReg) {
				continue;
			}

			$requestedStudentId = $this->resolveApprovedCombinedStudentId((string)($approvedRequest->student_name ?? ''), (int)$sourceConventionReg->id, (int)$eventD->id);
			if ($requestedStudentId <= 0) {
				continue;
			}

			$alreadyLinkedRecord = $this->Crstudentevents->find()->where([
				'Crstudentevents.conventionregistration_id' => $conventionRegD->id,
				'Crstudentevents.event_id' => $eventD->id,
				'Crstudentevents.student_id' => $requestedStudentId,
			])->first();
			if ($alreadyLinkedRecord) {
				$currentGroupName = trim((string)($alreadyLinkedRecord->group_name ?? ''));
				if ($currentGroupName !== '') {
					$this->Crstudentevents->updateAll(
						['group_name' => $currentGroupName, 'modified' => date('Y-m-d H:i:s')],
						[
							'Crstudentevents.conventionregistration_id' => $sourceConventionReg->id,
							'Crstudentevents.event_id' => $eventD->id,
							'Crstudentevents.student_id' => $requestedStudentId,
						]
					);
				}
				continue;
			}

			$this->ensureCombinedEventEntry($conventionRegD, $eventD, $requestedStudentId, '');
		}

		$outgoingApprovedRequests = $this->Combinerequests->find()
			->where([
				'Combinerequests.status' => 1,
				'Combinerequests.conventionseason_id' => $conventionRegD->conventionseason_id,
				'Combinerequests.event_id' => $eventD->id,
				'Combinerequests.user_id' => $conventionRegD->user_id,
			])
			->all();

		foreach ($outgoingApprovedRequests as $approvedRequest) {
			$targetConventionReg = $this->Conventionregistrations->find()
				->where([
					'Conventionregistrations.user_id' => $approvedRequest->combine_with_user_id,
					'Conventionregistrations.conventionseason_id' => $approvedRequest->conventionseason_id,
				])
				->first();
			if (!$targetConventionReg) {
				continue;
			}

			$sourceEventStudentIds = $this->Crstudentevents->find()
				->where([
					'Crstudentevents.conventionregistration_id' => $conventionRegD->id,
					'Crstudentevents.event_id' => $eventD->id,
					'Crstudentevents.student_id >' => 0,
				])
				->extract('student_id')
				->map(static function ($sid) {
					return (int)$sid;
				})
				->toList();
			if (empty($sourceEventStudentIds)) {
				continue;
			}

			$targetLinkedEntries = $this->Crstudentevents->find()->where([
				'Crstudentevents.conventionregistration_id' => $targetConventionReg->id,
				'Crstudentevents.event_id' => $eventD->id,
				'Crstudentevents.student_id IN' => $sourceEventStudentIds,
				'Crstudentevents.group_name !=' => '',
			])->all();

			$groupNamesToMirror = [];
			foreach ($targetLinkedEntries as $targetLinkedEntry) {
				$linkedGroupName = trim((string)($targetLinkedEntry->group_name ?? ''));
				if ($linkedGroupName !== '') {
					$groupNamesToMirror[] = $linkedGroupName;
				}
			}
			$groupNamesToMirror = array_values(array_unique($groupNamesToMirror));
			foreach ($groupNamesToMirror as $groupNameToMirror) {
				$targetGroupMembers = $this->Crstudentevents->find()->where([
					'Crstudentevents.conventionregistration_id' => $targetConventionReg->id,
					'Crstudentevents.event_id' => $eventD->id,
					'Crstudentevents.group_name' => $groupNameToMirror,
					'Crstudentevents.student_id >' => 0,
				])->all();

				foreach ($targetGroupMembers as $targetGroupMember) {
					$this->ensureCombinedEventEntry($conventionRegD, $eventD, (int)$targetGroupMember->student_id, $groupNameToMirror);
				}
			}
		}
	}

	private function ensureCombinedEventEntry($conventionRegD, $eventD, int $studentId, string $groupName = ''): void {
		if (!$conventionRegD || !$eventD || $studentId <= 0) {
			return;
		}

		$groupName = trim($groupName);
		$existing = $this->Crstudentevents->find()->where([
			'Crstudentevents.conventionregistration_id' => $conventionRegD->id,
			'Crstudentevents.event_id' => $eventD->id,
			'Crstudentevents.student_id' => $studentId,
		])->first();

		if ($existing) {
			if ($groupName !== '' && trim((string)$existing->group_name) !== $groupName) {
				$this->Crstudentevents->updateAll(
					['group_name' => $groupName, 'modified' => date('Y-m-d H:i:s')],
					['Crstudentevents.id' => $existing->id]
				);
			}
			return;
		}

		$linkedStudent = $this->Crstudentevents->newEntity([]);
		$linkedStudent->slug = 'combined-approved-'.$conventionRegD->id.'-'.$studentId.'-'.time();
		$linkedStudent->conventionregistration_id = $conventionRegD->id;
		$linkedStudent->conventionseason_id = $conventionRegD->conventionseason_id;
		$linkedStudent->convention_id = $conventionRegD->convention_id;
		$linkedStudent->user_id = $conventionRegD->user_id;
		$linkedStudent->season_id = $conventionRegD->season_id;
		$linkedStudent->season_year = $conventionRegD->season_year;
		$linkedStudent->student_id = $studentId;
		$linkedStudent->event_id = $eventD->id;
		$linkedStudent->event_id_number = $eventD->event_id_number;
		$linkedStudent->group_name = $groupName;
		$linkedStudent->created = date('Y-m-d H:i:s');
		$linkedStudent->modified = date('Y-m-d H:i:s');
		$this->Crstudentevents->save($linkedStudent);
	}

	private function resolveApprovedCombinedStudentId(string $requestedStudentName, int $sourceConventionRegistrationId, int $eventId): int {
		$requestedName = $this->normalizeCombinedName($requestedStudentName);
		if ($requestedName === '' || $sourceConventionRegistrationId <= 0 || $eventId <= 0) {
			return 0;
		}

		$sourceStudents = $this->Crstudentevents->find()
			->contain(['Students'])
			->where([
				'Crstudentevents.conventionregistration_id' => $sourceConventionRegistrationId,
				'Crstudentevents.event_id' => $eventId,
				'Crstudentevents.student_id >' => 0,
			])
			->all();
		$sourceStudentsList = $sourceStudents->toArray();

		$fallbackStudentId = 0;
		$nameParts = array_values(array_filter(explode(' ', $requestedName)));
		$bestScore = 0;
		foreach ($sourceStudentsList as $sourceStudent) {
			if (empty($sourceStudent->Students)) {
				continue;
			}
			$fullName = trim((string)$sourceStudent->Students->first_name . ' ' . (string)$sourceStudent->Students->middle_name . ' ' . (string)$sourceStudent->Students->last_name);
			$normalizedFullName = $this->normalizeCombinedName($fullName);
			if ($normalizedFullName === $requestedName) {
				return (int)$sourceStudent->student_id;
			}

			if (!empty($nameParts)) {
				$matchedParts = 0;
				foreach ($nameParts as $part) {
					if (strpos($normalizedFullName, $part) !== false) {
						$matchedParts++;
					}
				}
				if ($matchedParts > $bestScore) {
					$bestScore = $matchedParts;
					$fallbackStudentId = (int)$sourceStudent->student_id;
				}
			}
		}

		if ($fallbackStudentId > 0 && $bestScore >= 2) {
			return $fallbackStudentId;
		}

		if (count($sourceStudentsList) === 1) {
			return (int)$sourceStudentsList[0]->student_id;
		}

		return $fallbackStudentId;
	}

	private function normalizeCombinedName(string $name): string {
		$name = strtolower(trim($name));
		$name = preg_replace('/\s+/', ' ', $name);
		return (string)$name;
	}

	private function mirrorCombinedGroupNameToStudentSchool(int $studentId, string $groupName, $currentConventionReg, $eventD): void {
		if ($studentId <= 0 || trim($groupName) === '' || !$currentConventionReg || !$eventD) {
			return;
		}

		$student = $this->Users->find()->where(['Users.id' => $studentId])->first();
		if (!$student) {
			return;
		}

		$studentSchoolId = (int)($student->school_id ?? 0);
		if ($studentSchoolId <= 0 || $studentSchoolId === (int)$currentConventionReg->user_id) {
			return;
		}

		$sourceConventionReg = $this->Conventionregistrations->find()
			->where([
				'Conventionregistrations.user_id' => $studentSchoolId,
				'Conventionregistrations.conventionseason_id' => $currentConventionReg->conventionseason_id,
			])
			->first();
		if (!$sourceConventionReg) {
			return;
		}

		$this->Crstudentevents->updateAll(
			['group_name' => $groupName, 'modified' => date('Y-m-d H:i:s')],
			[
				'Crstudentevents.conventionregistration_id' => $sourceConventionReg->id,
				'Crstudentevents.convention_id' => $sourceConventionReg->convention_id,
				'Crstudentevents.season_id' => $sourceConventionReg->season_id,
				'Crstudentevents.season_year' => $sourceConventionReg->season_year,
				'Crstudentevents.event_id' => $eventD->id,
				'Crstudentevents.student_id' => $studentId,
			]
		);
	}

	private function ensureAutomaticGroupSubmissions($eventD, $conventionRegD, array $groupNames): void {
		if ((int)$eventD->auto_submission !== 1 || (int)$eventD->group_event_yes_no !== 1) {
			return;
		}

		foreach ($groupNames as $groupName) {
			$groupName = trim((string)$groupName);
			if ($groupName === '') {
				continue;
			}

			$submissionExists = $this->Eventsubmissions->find()->where([
				'Eventsubmissions.event_id' => $eventD->id,
				'Eventsubmissions.conventionregistration_id' => $conventionRegD->id,
				'Eventsubmissions.group_name' => $groupName,
			])->count() > 0;
			if ($submissionExists) {
				continue;
			}

			$submission = $this->Eventsubmissions->newEntity([]);
			$submission->slug = 'event-submission-'.$conventionRegD->id.'-'.time().'-'.rand(100, 1000000);
			$submission->conventionregistration_id = $conventionRegD->id;
			$submission->conventionseason_id = $conventionRegD->conventionseason_id;
			$submission->convention_id = $conventionRegD->convention_id;
			$submission->user_id = $conventionRegD->user_id;
			$submission->season_id = $conventionRegD->season_id;
			$submission->season_year = $conventionRegD->season_year;
			$submission->event_id = $eventD->id;
			$submission->event_id_number = $eventD->event_id_number;
			$submission->student_id = 0;
			$submission->group_name = $groupName;
			$submission->uploaded_by_user_id = $conventionRegD->user_id;
			$submission->created = date('Y-m-d H:i:s');
			$submission->modified = date('Y-m-d H:i:s');
			$this->Eventsubmissions->save($submission);
		}
	}
	
	public function removestudentfromgroup($event_slug = null,$student_id = null) {
		
		$this->userLoginCheck();
		//$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get event details
			$eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
			//echo $eventD->id;exit;
			
			// to get group details
			$groupD = $this->Crstudentevents->find()
						->where([
						'conventionregistration_id' => $conventionRegD->id,
						"convention_id"				=> $conventionRegD->convention_id,
						"season_id" 				=> $conventionRegD->season_id,
						"season_year" 				=> $conventionRegD->season_year,
						"student_id" 				=> $student_id,
						"event_id" 					=> $eventD->id
						])->first();
			$group_name = $groupD->group_name;
			
			$this->Crstudentevents->updateAll(['group_name' => '','modified' => date("Y-m-d H:i:s")],
				[
				"conventionregistration_id" 		=> $conventionRegD->id,
				"convention_id" 					=> $conventionRegD->convention_id,
				"season_id" 						=> $conventionRegD->season_id,
				"season_year" 						=> $conventionRegD->season_year,
				"student_id" 						=> $student_id,
				"event_id" 							=> $eventD->id
				]);
				
			// here check that how many students are in this group..
			// if no student left in this group, then remove submission for this Group
			// .. as well
			$checkStudentsInGroupCount = $this->Crstudentevents
								->find()
								->where([
								"conventionregistration_id" => $conventionRegD->id,
								"convention_id"				=> $conventionRegD->convention_id,
								"season_id"					=> $conventionRegD->season_id,
								"season_year"				=> $conventionRegD->season_year,
								"season_year"				=> $conventionRegD->season_year,
								"group_name"				=> $group_name
								])->count();
								
			if($checkStudentsInGroupCount == 0)
			{
				// remove submission of this group
				$this->Eventsubmissions->deleteAll(
				[
					"conventionregistration_id" => $conventionRegD->id,
					"convention_id"				=> $conventionRegD->convention_id,
					"season_id"					=> $conventionRegD->season_id,
					"season_year"				=> $conventionRegD->season_year,
					"season_year"				=> $conventionRegD->season_year,
					"event_id" 					=> $eventD->id
				]);
				
			}
				
			$this->Flash->success('Student removed from group successfully.');
			$this->redirect(['controller' => 'groups', 'action' => 'eventgroups', $event_slug]);
			
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
    }
    

}

?>
