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
class CombinerequestsController extends AppController {

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
    }

    public function viewlist() {

        $this->userLoginCheck();
        //$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
        $this->set("title_for_layout", "Combined Team/Group Events " . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('active_cr_studentgroups','active');
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$user_type 	= $this->request->session()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Combinerequests.conventionregistration_id = '".$this->request->session()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// if Teacher_Parent is logged in then need to choose school id
		if($user_type == "Teacher_Parent")
		{
			$condition[] = "(Combinerequests.user_id = '".$userDetails->school_id."')";
		}
		else
		{
			$condition[] = "(Combinerequests.user_id = '".$user_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Combinerequests->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Combinerequests->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Combinerequests->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Combinerequests']['keyword']) && $this->request->getData()['Combinerequests']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Combinerequests']['keyword']);
            }
        } elseif ($this->request->getParam('pass')) {
            if (isset($this->request->getParam('pass', [])[0]) && $this->request->getParam('pass', [])[0] != '') {
                $searchArr = $this->request->getParam('pass', []);
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (isset($keyword) && $keyword != '') {
            $separator[] = 'keyword:' . urlencode($keyword);
            $condition[] = "(Combinerequests.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
		
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Combineduser','Events'],'conditions' => $condition, 'limit' => 30, 'order' => ['combinerequests.season_year' => 'DESC']];
        $this->set('combinerequests', $this->paginate($this->Combinerequests));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Combinerequests');
            $this->render('viewlist');
        }
    }
	
	public function addrequest() {

		$this->userLoginCheck();
		//$this->schoolAdminLoginCheck();
		$this->multiLoginCheck(['School','Teacher_Parent']);
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Combined Team/Group Events - Add Request " . TITLE_FOR_PAGES);
		
		$this->set('active_cr_studentgroups','active');
		
        $user_id = $this->request->session()->read("user_id");
		if ((int)$user_id <= 0) {
			$this->Flash->error('Please login first.');
			return $this->redirect(['controller' => 'users', 'action' => 'login']);
		}

		$userDetails = $this->Users->find()->where(['Users.id' => (int)$user_id])->first();
		if (!$userDetails) {
			$this->Flash->error('Could not load your account. Please login again.');
			return $this->redirect(['controller' => 'users', 'action' => 'logout']);
		}
        $this->set('userDetails', $userDetails);
		$eventNameIDDD = [];
		$schoolNamesDD = [];
		$combinerequests = $this->Combinerequests->newEntity([]);
		$conventionregistrationstudents = [];
		$eventRegisteredStudents = [];
		$selectedEventId = 0;
		$selectedStudentIds = [];
		$selectedCombineWithUserId = 0;
		
		$sessSelectedConventionRegistrationId = (int)$this->request->session()->read("sess_selected_convention_registration_id");
		if ($sessSelectedConventionRegistrationId <= 0) {
			$sessSelectedConventionRegistrationId = $this->resolveSelectedConventionRegistrationId($userDetails);
		}

		if($sessSelectedConventionRegistrationId>0)
		{
			$sess_selected_convention_registration_id = $sessSelectedConventionRegistrationId;
			
			// to get convention registration details
			$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->contain(['Conventions'])->first();
			if (!$conventionRegD) {
				$this->request->session()->delete('sess_selected_convention_registration_id');
				$this->request->session()->delete('sess_selected_convention_id');
				$sess_selected_convention_registration_id = $this->resolveSelectedConventionRegistrationId($userDetails);
				if ($sess_selected_convention_registration_id > 0) {
					$conventionRegD = $this->Conventionregistrations->find()
						->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])
						->contain(['Conventions'])
						->first();
				}
			}

			if (!$conventionRegD) {
				$this->Flash->error('Could not find an active convention registration for your account. Please select your convention from the top dropdown first.');
				return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'myregistrations']);
			}
			
			/* 1. to get the list of event ids chosen in this convention for this season */
			$arrConvSeasonEvents = array();
			$arrConvSeasonEvents[] = 0;
			$convSeasonEvents = $this->Conventionseasonevents->find()->where(["Conventionseasonevents.conventionseasons_id" => $conventionRegD->conventionseason_id])->order(['Conventionseasonevents.id' => 'ASC'])->all();
			foreach($convSeasonEvents as $convsevent)
			{
				$arrConvSeasonEvents[] = $convsevent->event_id;
			}
			$arrConvSeasonEventsImplode = implode(",",$arrConvSeasonEvents);
			
			// now create event dropdown with event name and number_format
			$eventNameIDDD = array();
			$condEvents = array();
			$condEvents[] = "(Events.id IN ($arrConvSeasonEventsImplode) )";
			$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_id_number' => 'ASC'])->all();
			foreach($eventsList as $eventrec)
			{
				$eventNameIDDD[$eventrec->id] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
			}
			$this->set('eventNameIDDD', $eventNameIDDD);
			
			
			/* 2. To get list of schools participated in this season */
			$arrSchoolIDSCR = array();
			$arrSchoolIDSCR[] = 0;
			$convSchools = $this->Conventionregistrations->find()->where(["Conventionregistrations.conventionseason_id" => $conventionRegD->conventionseason_id])->order(['Conventionregistrations.id' => 'DESC'])->all();
			foreach($convSchools as $convsch)
			{
				$arrSchoolIDSCR[] = $convsch->user_id;
			}
			$arrSchoolIDSCRImplode = implode(",",$arrSchoolIDSCR);
			
			// now create dropdown for schools name
			$schoolNamesDD = array();
			$condSchoolsNameCR = array();
			$condSchoolsNameCR[] = "(Users.id IN ($arrSchoolIDSCRImplode) )";
			$condSchoolsNameCR[] = "(Users.user_type = 'School')";
			$schoolsListCR = $this->Users->find()->where($condSchoolsNameCR)->order(['Users.first_name' => 'ASC'])->all();
			foreach($schoolsListCR as $schoollistcr)
			{
				$schoolNamesDD[$schoollistcr->id] = $schoollistcr->first_name.' '.$schoollistcr->last_name;
			}
			$this->set('schoolNamesDD', $schoolNamesDD);

			if ($this->request->is('post')) {
				$selectedEventId = (int)($this->request->getData('Combinerequests.event_id') ?? 0);
				$selectedCombineWithUserId = (int)($this->request->getData('Combinerequests.combine_with_user_id') ?? 0);
				$selectedStudentIds = array_values(array_unique(array_filter(array_map('intval', (array)$this->request->getData('selected_student_ids')), static function ($sid) {
					return $sid > 0;
				})));
			} else {
				$selectedEventId = (int)$this->request->getQuery('event_id');
			}

			$eventRegisteredStudents = $this->getRegisteredStudentsForEvent($sess_selected_convention_registration_id, $selectedEventId);
			
			//$this->prx($schoolNamesDD);
			
		}
		else
		{
			$this->Flash->error('Please select your convention from the top dropdown first, then open Combined Team/Group Events again.');
			return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'myregistrations']);
		}
		
		if ($this->request->is('post')) {
			$eventId = (int)($this->request->getData('Combinerequests.event_id') ?? 0);
			$combineWithUserId = (int)($this->request->getData('Combinerequests.combine_with_user_id') ?? 0);
			if ($eventId <= 0) {
				$this->Flash->error('Please choose an event.');
			} elseif ($combineWithUserId <= 0) {
				$this->Flash->error('Please choose school to combine with.');
			} elseif (empty($selectedStudentIds)) {
				$this->Flash->error('Please select at least one student name.');
			} else {
				$eventD = $this->Events->find()->where(['Events.id' => $eventId])->first();
				$combinedSchoolName = $this->Users->find()->where(['Users.id' => $combineWithUserId])->first();
				if (!$eventD || !$combinedSchoolName) {
					$this->Flash->error('Invalid event or school selection. Please try again.');
				} else {
					$validStudentIds = [];
					foreach ($selectedStudentIds as $sid) {
						if (isset($eventRegisteredStudents[$sid])) {
							$validStudentIds[] = (int)$sid;
						}
					}
					$validStudentIds = array_values(array_unique($validStudentIds));

					if (empty($validStudentIds)) {
						$this->Flash->error('Selected students are not registered for this event. Please select from the list.');
					} else {
						$savedCount = 0;
						$now = date('Y-m-d H:i:s');
						foreach ($validStudentIds as $studentId) {
							$studentName = (string)$eventRegisteredStudents[$studentId];
							$newRequest = $this->Combinerequests->newEntity([]);
							$newRequest->slug = 'combined-request-event-'.$sess_selected_convention_registration_id.'-'.$studentId.'-'.time();
							$newRequest->event_id = $eventD->id;
							$newRequest->event_id_number = $eventD->event_id_number;
							$newRequest->combine_with_user_id = $combineWithUserId;
							$newRequest->student_name = $studentName;
							$newRequest->conventionregistration_id = $sess_selected_convention_registration_id;
							$newRequest->conventionseason_id = $conventionRegD->conventionseason_id;
							$newRequest->convention_id = $conventionRegD->convention_id;
							$newRequest->user_id = $conventionRegD->user_id;
							$newRequest->season_id = $conventionRegD->season_id;
							$newRequest->season_year = $conventionRegD->season_year;
							$newRequest->status = 2;
							$newRequest->created = $now;
							$newRequest->modified = $now;

							if ($this->Combinerequests->save($newRequest)) {
								$savedCount++;
							}
						}

						if ($savedCount > 0) {
							$adminD = $this->Admins->find()->where(['Admins.id' => 1])->first();
							$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '22'])->first();
							if ($adminD && $emailtemplateMessage) {
								$toRepArray = array('[!school_name!]','[!combine_with_school_name!]','[!event_name!]','[!event_id_number!]','[!convention_name!]','[!season_year!]');
								$fromRepArray = array($userDetails->first_name,$combinedSchoolName->first_name,$eventD->event_name,$eventD->event_id_number,$conventionRegD->Conventions['name'],$conventionRegD->season_year);
								$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
								$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
								try {
									$email = new Email();
									$email->setEmailFormat('html')
										->setTo($adminD->email)
										->setCc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
										->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
										->setSubject($subjectToSend)
										->send($messageToSend);
								} catch (\Throwable $exception) {
									$this->log('Combined request admin email failed: ' . $exception->getMessage(), 'error');
								}
							}

							if ($savedCount === count($validStudentIds)) {
								$this->Flash->success('We have received your request. Admin will review and you will get notified if request approved or decline.');
							} else {
								$this->Flash->success('Request saved for '.$savedCount.' student(s). Some students could not be saved.');
							}
							return $this->redirect(['controller' => 'combinerequests', 'action' => 'viewlist']);
						}

						$this->Flash->error('Could not save request. Please try again.');
					}
				}
			}
		}
		$this->set(compact('combinerequests', 'eventNameIDDD', 'schoolNamesDD', 'eventRegisteredStudents', 'selectedEventId', 'selectedStudentIds', 'selectedCombineWithUserId'));
        $this->set('conventionregistrationstudents', $conventionregistrationstudents);
    }

	private function getRegisteredStudentsForEvent(int $conventionRegistrationId, int $eventId): array {
		if ($conventionRegistrationId <= 0 || $eventId <= 0) {
			return [];
		}

		$eventStudents = $this->Crstudentevents->find()
			->contain(['Students'])
			->where([
				'Crstudentevents.conventionregistration_id' => $conventionRegistrationId,
				'Crstudentevents.event_id' => $eventId,
				'Crstudentevents.student_id >' => 0,
			])
			->order(['Students.first_name' => 'ASC', 'Students.last_name' => 'ASC'])
			->all();

		$students = [];
		foreach ($eventStudents as $eventStudent) {
			$studentId = (int)($eventStudent->student_id ?? 0);
			if ($studentId <= 0 || isset($students[$studentId])) {
				continue;
			}

			if (!empty($eventStudent->Students)) {
				$studentName = trim((string)$eventStudent->Students->first_name.' '.(string)$eventStudent->Students->middle_name.' '.(string)$eventStudent->Students->last_name);
				$studentName = preg_replace('/\s+/', ' ', $studentName ?? '');
				if ($studentName !== '') {
					$students[$studentId] = $studentName;
				}
			}
		}

		return $students;
	}

	private function resolveSelectedConventionRegistrationId($userDetails = null): int {
		if (!$userDetails || empty($userDetails->id)) {
			return 0;
		}

		$userType = (string)$this->request->session()->read('user_type');
		$schoolUserId = 0;
		if ($userType === 'Teacher_Parent') {
			$schoolUserId = (int)($userDetails->school_id ?? 0);
		} else {
			$schoolUserId = (int)$userDetails->id;
		}
		if ($schoolUserId <= 0) {
			return 0;
		}

		$seasonId = (int)$this->getCurrentSeason();
		if ($seasonId <= 0) {
			return 0;
		}

		$selectedConventionId = (int)$this->request->session()->read('sess_selected_convention_id');
		$query = $this->Conventionregistrations->find()
			->where([
				'Conventionregistrations.user_id' => $schoolUserId,
				'Conventionregistrations.season_id' => $seasonId,
			])
			->order(['Conventionregistrations.id' => 'DESC']);

		if ($selectedConventionId > 0) {
			$query->where(['Conventionregistrations.convention_id' => $selectedConventionId]);
		}

		$convReg = $query->first();
		if (!$convReg && $selectedConventionId > 0) {
			$convReg = $this->Conventionregistrations->find()
				->where([
					'Conventionregistrations.user_id' => $schoolUserId,
					'Conventionregistrations.season_id' => $seasonId,
				])
				->order(['Conventionregistrations.id' => 'DESC'])
				->first();
		}

		if (!$convReg) {
			return 0;
		}

		$this->request->session()->write('sess_selected_convention_registration_id', (int)$convReg->id);
		$this->request->session()->write('sess_selected_convention_id', (int)$convReg->convention_id);

		return (int)$convReg->id;
	}
    

}

?>
