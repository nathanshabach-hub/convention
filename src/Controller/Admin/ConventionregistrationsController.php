<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\Datasource\ConnectionManager;

#[\AllowDynamicProperties]
class ConventionregistrationsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventionregistrations.name' => 'asc']];

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
		
		$this->loadModel('Conventions');
		$this->loadModel('Events');
		$this->loadModel('Settings');
		$this->loadModel('Seasons');
		$this->loadModel('Emailtemplates');
		$this->loadModel('Conventionregistrationteachers');
		$this->loadModel('Conventionregistrationstudents');
		$this->loadModel('Resultpositions');
		$this->loadModel('Heartevents');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionseasons');
    }


    protected function hasJudgingAssignmentsTable() {
        try {
            $connection = \Cake\Datasource\ConnectionManager::get('default');
            $tables = $connection->getSchemaCollection()->listTables();
            return in_array('judging_assignments', $tables, true);
        } catch (\Throwable $e) {
            return false;
        }
    }

	protected function hasTable($tableName) {
		try {
			$connection = ConnectionManager::get('default');
			$tables = $connection->getSchemaCollection()->listTables();
			return in_array($tableName, $tables, true);
		} catch (\Throwable $e) {
			return false;
		}
	}
    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Convention Registrations');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Conventionregistrations.parent_id' => 0);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Conventionregistrations.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		$conventionsDD = $this->Conventions->find()->where([])->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('conventionsDD', $conventionsDD);
		
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'DESC'])->combine('season_year', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionregistrations->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionregistrations->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionregistrations->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Conventionregistrations']['convention_id']) && $this->request->getData()['Conventionregistrations']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Conventionregistrations']['convention_id']);
            }
			if (isset($this->request->getData()['Conventionregistrations']['season_year']) && $this->request->getData()['Conventionregistrations']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Conventionregistrations']['season_year']);
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

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Conventionregistrations.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Conventionregistrations.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }
		
		//$this->pr($condition);
		
        /* //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Users'], 'conditions' => $condition, 'limit' => 1000000000, 'order' => ['Conventionregistrations.id' => 'DESC']];
        $this->set('conventionregistrations', $this->paginate($this->Conventionregistrations));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventionregistrations');
            $this->render('index');
        } */
		
		$conventionregistrations 		= $this->Conventionregistrations->find()->where($condition)->contain(['Conventions','Users'])->order(['Conventionregistrations.id' => 'DESC'])->all();
		$this->set('conventionregistrations', $conventionregistrations);
		
    }
	
	public function teachers($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Supervisors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');
		
		$separator = array();
        $condition = array();
		$seasonSupervisorOptions = [];
        //$condition = array('Conventionregistrations.parent_id' => 0);
		
		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
			$this->set('CRDetails', $CRDetails);
			
			$this->set('slug', $slug);
			
			$condition = array('Conventionregistrationteachers.conventionregistration_id' => $CRDetails->id);

			if (!empty($CRDetails->convention_id) && !empty($CRDetails->season_id) && !empty($CRDetails->season_year)) {
				$seasonSupervisors = $this->Conventionregistrationteachers->find()
					->where([
						'Conventionregistrationteachers.convention_id' => $CRDetails->convention_id,
						'Conventionregistrationteachers.season_id' => $CRDetails->season_id,
						'Conventionregistrationteachers.season_year' => $CRDetails->season_year,
						'Conventionregistrationteachers.status' => 1
					])
					->contain(['Teachers'])
					->order([
						'Teachers.first_name' => 'ASC',
						'Teachers.last_name' => 'ASC'
					])
					->all();

				$schoolIds = [];
				foreach ($seasonSupervisors as $supervisorRecord) {
					$schoolId = (int)($supervisorRecord->Teachers['school_id'] ?? 0);
					if ($schoolId > 0) {
						$schoolIds[] = $schoolId;
					}
				}

				$schoolNameMap = [];
				$schoolIds = array_values(array_unique($schoolIds));
				if (!empty($schoolIds)) {
					$schoolUsers = $this->Conventionregistrationteachers->Teachers->find()
						->select(['id', 'first_name'])
						->where(['id IN' => $schoolIds])
						->all();
					foreach ($schoolUsers as $schoolUser) {
						$schoolNameMap[(int)$schoolUser->id] = trim((string)$schoolUser->first_name);
					}
				}

				foreach ($seasonSupervisors as $supervisorRecord) {
					$teacherId = (int)$supervisorRecord->teacher_id;
					if ($teacherId <= 0 || isset($seasonSupervisorOptions[$teacherId])) {
						continue;
					}

					$displayName = trim(
						trim((string)($supervisorRecord->Teachers['title'] ?? '')) . ' ' .
						trim((string)($supervisorRecord->Teachers['first_name'] ?? '')) . ' ' .
						trim((string)($supervisorRecord->Teachers['last_name'] ?? ''))
					);
					if ($displayName === '') {
						$displayName = 'Supervisor #' . $teacherId;
					}

					$schoolId = (int)($supervisorRecord->Teachers['school_id'] ?? 0);
					$schoolName = $schoolId > 0 && isset($schoolNameMap[$schoolId]) ? trim((string)$schoolNameMap[$schoolId]) : '';
					if ($schoolName !== '') {
						$displayName .= ' [School: ' . $schoolName . ']';
					}

					$seasonSupervisorOptions[$teacherId] = $displayName;
				}
			}
		}

        

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionregistrationteachers->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionregistrationteachers->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionregistrationteachers->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Conventionregistrationteachers']['convention_id']) && $this->request->getData()['Conventionregistrationteachers']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Conventionregistrationteachers']['convention_id']);
            }
			if (isset($this->request->getData()['Conventionregistrationteachers']['season_year']) && $this->request->getData()['Conventionregistrationteachers']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Conventionregistrationteachers']['season_year']);
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

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Conventionregistrationteachers.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Conventionregistrationteachers.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }
		
        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Users','Teachers'], 'conditions' => $condition, 'limit' => 500, 'order' => ['Conventionregistrationteachers.id' => 'DESC']];
		$conventionregistrationteachers = $this->paginate($this->Conventionregistrationteachers);
		$teacherStudentsMap = [];
		if (!$conventionregistrationteachers->isEmpty()) {
			$teacherIds = [];
			$registrationIds = [];
			foreach ($conventionregistrationteachers as $teacherRecord) {
				if (!empty($teacherRecord->teacher_id)) {
					$teacherIds[] = (int)$teacherRecord->teacher_id;
				}
				if (!empty($teacherRecord->conventionregistration_id)) {
					$registrationIds[] = (int)$teacherRecord->conventionregistration_id;
				}
			}

			$teacherIds = array_values(array_unique($teacherIds));
			$registrationIds = array_values(array_unique($registrationIds));

			if (!empty($teacherIds) && !empty($registrationIds)) {
				$assignedStudents = $this->Conventionregistrationstudents->find()
					->where([
						'Conventionregistrationstudents.teacher_parent_id IN' => $teacherIds,
						'Conventionregistrationstudents.conventionregistration_id IN' => $registrationIds
					])
					->contain(['Students'])
					->order([
						'Students.first_name' => 'ASC',
						'Students.last_name' => 'ASC'
					])
					->all();

				foreach ($assignedStudents as $studentRecord) {
					$mapKey = (int)$studentRecord->conventionregistration_id . '_' . (int)$studentRecord->teacher_parent_id;
					if (!isset($teacherStudentsMap[$mapKey])) {
						$teacherStudentsMap[$mapKey] = [];
					}
					$teacherStudentsMap[$mapKey][] = $studentRecord;
				}
			}
		}

		$this->set('teacherStudentsMap', $teacherStudentsMap);
		$this->set('seasonSupervisorOptions', $seasonSupervisorOptions);
		$this->set('conventionregistrationteachers', $conventionregistrationteachers);
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventionregistrations');
            $this->render('teachers');
        }
    }

	public function reassignstudentsupervisor() {
		if (!$this->request->is('post')) {
			return $this->redirect($this->referer(['action' => 'index']));
		}

		$studentRegistrationId = (int)$this->request->getData('student_registration_id');
		$newTeacherParentId = (int)$this->request->getData('new_teacher_parent_id');
		$returnSlug = trim((string)$this->request->getData('return_slug'));

		if ($studentRegistrationId <= 0 || $newTeacherParentId <= 0) {
			$this->Flash->error('Invalid reassignment request.');
			return ($returnSlug !== '')
				? $this->redirect(['action' => 'teachers', $returnSlug])
				: $this->redirect($this->referer(['action' => 'index']));
		}

		$studentRecord = $this->Conventionregistrationstudents->find()
			->where(['Conventionregistrationstudents.id' => $studentRegistrationId])
			->first();

		if (empty($studentRecord)) {
			$this->Flash->error('Student registration record not found.');
			return ($returnSlug !== '')
				? $this->redirect(['action' => 'teachers', $returnSlug])
				: $this->redirect($this->referer(['action' => 'index']));
		}

		$targetSupervisor = $this->Conventionregistrationteachers->find()
			->where([
				'Conventionregistrationteachers.teacher_id' => $newTeacherParentId,
				'Conventionregistrationteachers.convention_id' => $studentRecord->convention_id,
				'Conventionregistrationteachers.season_id' => $studentRecord->season_id,
				'Conventionregistrationteachers.season_year' => $studentRecord->season_year,
				'Conventionregistrationteachers.status' => 1
			])
			->first();

		if (empty($targetSupervisor)) {
			$this->Flash->error('Selected supervisor is not registered in this convention season.');
			return ($returnSlug !== '')
				? $this->redirect(['action' => 'teachers', $returnSlug])
				: $this->redirect($this->referer(['action' => 'index']));
		}

		$studentRecord->teacher_parent_id = $newTeacherParentId;
		if ($this->Conventionregistrationstudents->save($studentRecord)) {
			$this->Flash->success('Student was reassigned successfully.');
		} else {
			$this->Flash->error('Unable to reassign student. Please try again.');
		}

		return ($returnSlug !== '')
			? $this->redirect(['action' => 'teachers', $returnSlug])
			: $this->redirect($this->referer(['action' => 'index']));
	}
	
	public function students($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Students');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');
		
		$separator = array();
        $condition = array();
		
		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
			$this->set('CRDetails', $CRDetails);
			
			$this->set('slug', $slug);
			
			$condition = array('Conventionregistrationstudents.conventionregistration_id' => $CRDetails->id);
		}

        

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionregistrationstudents->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionregistrationstudents->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionregistrationstudents->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Conventionregistrationstudents']['convention_id']) && $this->request->getData()['Conventionregistrationstudents']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Conventionregistrationstudents']['convention_id']);
            }
			if (isset($this->request->getData()['Conventionregistrationstudents']['season_year']) && $this->request->getData()['Conventionregistrationstudents']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Conventionregistrationstudents']['season_year']);
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

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Conventionregistrationstudents.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Conventionregistrationstudents.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }
		
        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Users','Students','Teachers'], 'conditions' => $condition, 'limit' => 500, 'order' => ['Conventionregistrationstudents.id' => 'DESC']];
        $this->set('conventionregistrationstudents', $this->paginate($this->Conventionregistrationstudents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventionregistrations');
            $this->render('students');
        }
    }
	
	public function heartevents($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Events of the heart');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');
		
		$separator = array();
        $condition = array();
		
		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
			$this->set('CRDetails', $CRDetails);
			
			$this->set('slug', $slug);
			
			$condition = array('Heartevents.conventionregistration_id' => $CRDetails->id);
		}

        

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Heartevents->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Heartevents->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Heartevents->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Heartevents']['convention_id']) && $this->request->getData()['Heartevents']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Heartevents']['convention_id']);
            }
			if (isset($this->request->getData()['Heartevents']['season_year']) && $this->request->getData()['Heartevents']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Heartevents']['season_year']);
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

        if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Heartevents.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Heartevents.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        }
		
        //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['contain' => ['Conventions','Students','Uploadeduser'], 'conditions' => $condition, 'limit' => 50, 'order' => ['Heartevents.id' => 'DESC']];
        $this->set('heartevents', $this->paginate($this->Heartevents));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Heartevents');
            $this->render('heartevents');
        }
    }
	
	public function removedocument($eventheart_slug = null, $conv_reg_slug = null) {
		
		$convRedG = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->first();
		if($convRedG)
		{
			// check if events of heart exists
			$checkExists = $this->Heartevents->find()->where(['Heartevents.slug' => $eventheart_slug,'Heartevents.conventionregistration_id' => $convRedG->id])->first();
			
			if($checkExists)
			{
				// to remove document as well
				@unlink(UPLOAD_EVENTS_HEART_PATH.$checkExists->mediafile_file_system_name);
				
				$this->Flash->success('Events of the heart removed successfully.');
				$this->Heartevents->deleteAll(["slug" => $eventheart_slug]);
			}
			else
			{
				$this->Flash->error('Invalid document.');
			}
		}
		else
		{
			$this->Flash->error('Invalid registration.');
		}
		
		$this->redirect(['controller' => 'conventionregistrations', 'action' => 'heartevents', $conv_reg_slug]);
    }
	
	public function approvejudgeregistration($slug=null) {
        
		$convRegEnteredD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug,'Conventionregistrations.status' => 2])->contain(['Conventions','Users'])->first();
		if($convRegEnteredD)
		{
			$this->Conventionregistrations->updateAll(['status' => '1','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sendning email to judge that account is active
			$emailId = $convRegEnteredD->Users['email_address'];
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '19'])->first();

			$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]');
			$fromRepArray = array($convRegEnteredD->Users['first_name'],$convRegEnteredD->Conventions['name'],$convRegEnteredD->season_year);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
			try {
				$email = new Email();
				$email->setEmailFormat('html')
					->setTo($emailId)
					->setCc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
					->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->setSubject($subjectToSend);

				$email->viewBuilder()
					->setTemplate('default')
					->setLayout('admintemplate')
					->setVar('content_for_layout', $messageToSend);

				$email->send();
			} catch (\Exception $e) {
				// Keep admin action successful even if email transport fails.
			}
			
			$this->Flash->success('Registration approved successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);
    }
	
	public function declinejudgeregistration($slug=null) {
        
		$convRegEnteredD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug,'Conventionregistrations.status' => 2])->contain(['Conventions','Users'])->first();
		if($convRegEnteredD)
		{
			$this->Conventionregistrations->updateAll(['status' => '0','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
			
			// now sendning email to judge that account is active
			$emailId = $convRegEnteredD->Users['email_address'];
							
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '20'])->first();

			$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]');
			$fromRepArray = array($convRegEnteredD->Users['first_name'],$convRegEnteredD->Conventions['name'],$convRegEnteredD->season_year);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
			try {
				$email = new Email();
				$email->setEmailFormat('html')
					->setTo($emailId)
					->setCc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
					->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->setSubject($subjectToSend);

				$email->viewBuilder()
					->setTemplate('default')
					->setLayout('admintemplate')
					->setVar('content_for_layout', $messageToSend);

				$email->send();
			} catch (\Exception $e) {
				// Keep admin action successful even if email transport fails.
			}
			
			$this->Flash->success('Registration approved successfully.');
		
		}
		else
		{
			$this->Flash->error('Invalid action.');
		}
        $this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);
    }
	
	public function judgeregevents($slug=null) {

        $this->set('title', ADMIN_TITLE . 'Judge Events');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');
		
		if($slug)
		{
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions','Users'])->first();
			$this->set('CRDetails', $CRDetails);
			
			// sometimes conventionseason_id is null
			if($CRDetails->conventionseason_id >0)
			{
				$conventionseason_id = $CRDetails->conventionseason_id;
			}
			else
			{
				// get conv season
				$getConvSeason = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $CRDetails->convention_id,'Conventionseasons.season_id' => $CRDetails->season_id,'Conventionseasons.season_year' => $CRDetails->season_year])->first();
				
				if($getConvSeason->id >0)
				{
					// update conv season id
					$this->Conventionregistrations->updateAll(['conventionseason_id' => $getConvSeason->id], ["slug" => $slug]);
					
					$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions','Users'])->first();
				}
				
			}
			
			$this->set('slug', $slug);
			
			// to get the list of event ids chosen in this convention for this season
			$arrConvSeasonEvents = array();
			$arrConvSeasonEvents[] = 0;
			$convSeasonEvents = $this->Conventionseasonevents->find()->where(["Conventionseasonevents.conventionseasons_id" => $CRDetails->conventionseason_id])->order(['Conventionseasonevents.id' => 'ASC'])->all();
			foreach($convSeasonEvents as $convsevent)
			{
				$arrConvSeasonEvents[] = $convsevent->event_id;
			}
			$arrConvSeasonEventsImplode = implode(",",$arrConvSeasonEvents);
			
			// now create event dropdown with event name and number
			$eventNameIDDD = array();
			$condEvents = array();
			$condEvents[] = "(Events.id IN ($arrConvSeasonEventsImplode) )";
			$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_id_number' => 'ASC'])->all();
			foreach($eventsList as $eventrec)
			{
				$eventNameIDDD[$eventrec->id] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
			}
			$this->set('eventNameIDDD', $eventNameIDDD);
			
			
			
			if ($this->request->is('post'))
			{	
				//$this->prx($this->request->getData());
				
				$send_email_notification = (int)$this->request->getData('send_email_notification', 0);

				$oldJudgeEventIds = array();
				if (!empty($CRDetails->judges_event_ids)) {
					$oldJudgeEventIds = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$CRDetails->judges_event_ids)))));
				}
				
				$selectedJudgeEvents = $this->request->getData('Conventionregistrations.judges_event_ids');
				$newJudgeEventIdsArr = array();
				if (is_array($selectedJudgeEvents)) {
					$newJudgeEventIdsArr = array_values(array_unique(array_filter(array_map('intval', $selectedJudgeEvents))));
				} else if (!empty($selectedJudgeEvents)) {
					$newJudgeEventIdsArr = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)$selectedJudgeEvents)))));
				}

				if (is_array($selectedJudgeEvents) && count($selectedJudgeEvents) > 0)
				{
					$judges_event_ids 			= implode(",", $selectedJudgeEvents);
				}
				else if (!is_array($selectedJudgeEvents) && !empty($selectedJudgeEvents))
				{
					$judges_event_ids 			= (string)$selectedJudgeEvents;
				}
				else
				{
					$judges_event_ids 			= '';
				}

				$removedJudgeEventIds = array_values(array_diff($oldJudgeEventIds, $newJudgeEventIdsArr));
				$removedEvaluationsCount = 0;
				if (!empty($removedJudgeEventIds)) {
					$this->loadModel('Judgeevaluations');
					$deleteCond = [
						'Judgeevaluations.uploaded_by_user_id' => $CRDetails->user_id,
						'Judgeevaluations.event_id IN' => $removedJudgeEventIds,
						'Judgeevaluations.conventionseason_id' => $CRDetails->conventionseason_id,
						'Judgeevaluations.convention_id' => $CRDetails->convention_id,
						'Judgeevaluations.season_id' => $CRDetails->season_id,
						'Judgeevaluations.season_year' => $CRDetails->season_year,
					];
					$removedEvaluationsCount = $this->Judgeevaluations->find()->where($deleteCond)->count();
					if ($removedEvaluationsCount > 0) {
						$this->Judgeevaluations->deleteAll($deleteCond);
					}
				}
				
				$this->Conventionregistrations->updateAll(['judges_event_ids' => $judges_event_ids, 'modified' => date("Y-m-d H:i:s")], ["slug" => $slug]);
				
				
				// for us to send email notification that events have been added to their judges portal
				$msgNot = "";
				if($send_email_notification)
				{
					$emailId = $CRDetails->Users['email_address'];
									
					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '25'])->first();

					$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]');
					$fromRepArray = array($CRDetails->Users['first_name'],$CRDetails->Conventions['name'],$CRDetails->season_year);

					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend; exit;
					
					try {
						$email = new Email();
						$email->setTemplate('default')
							->setLayout('admintemplate')
							->setEmailFormat('html')
							->setTo($emailId)
							->setCc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
							->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
							->setSubject($subjectToSend)
							->setViewVars(['content_for_layout' => $messageToSend])
							->send();
					} catch (\Exception $e) {
						// Keep event update successful even if email transport fails.
					}
					
					$msgNot = " Email notification sent successfully to judge.";
				}
				
				
				$msgDelete = '';
				if ($removedEvaluationsCount > 0) {
					$msgDelete = ' Removed '.$removedEvaluationsCount.' evaluation'.($removedEvaluationsCount == 1 ? '' : 's').' for unassigned event'.($removedEvaluationsCount == 1 ? '' : 's').'.';
				}

				$this->Flash->success('Events list updated successfully.'.$msgDelete.$msgNot);
				$this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);
				 
			}
			
			
		}
		else
		{
			$this->Flash->error('Invalid action.');
			$this->redirect(['controller'=>'conventionregistrations', 'action' => 'index']);
		}
        
    }
	
	public function judginglist() {

        $this->set('title', ADMIN_TITLE . 'Judging List');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

		$hasJudgingAssignments = true;
		if(!isset($this->JudgingAssignments))
		{
			try {
				$this->JudgingAssignments = $this->loadModel('JudgingAssignments');
			} catch (\Throwable $e) {
				$hasJudgingAssignments = false;
			}
		}
		if($hasJudgingAssignments && !$this->hasJudgingAssignmentsTable())
		{
			$hasJudgingAssignments = false;
		}

		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id <= 0)
		{
			$this->Flash->error('Please select a convention season first.');
			return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
		}

		// Handle save POST
		if($this->request->is('post'))
		{
			if(!$hasJudgingAssignments)
			{
				$this->Flash->error('Judging assignments table is unavailable. Please run the latest DB updates and try again.');
				return $this->redirect(['action' => 'judginglist']);
			}

			$assignments = $this->request->getData('assignments');
			if(!empty($assignments) && is_array($assignments))
			{
				foreach($assignments as $eventId => $panel)
				{
					$eventId = (int)$eventId;
					if($eventId <= 0) { continue; }

					$j1 = !empty($panel['judge1']) ? (int)$panel['judge1'] : null;
					$j2 = !empty($panel['judge2']) ? (int)$panel['judge2'] : null;
					$j3 = !empty($panel['judge3']) ? (int)$panel['judge3'] : null;

					$existing = $this->JudgingAssignments->find()->where([
						'conventionseason_id' => $sess_admin_header_season_id,
						'event_id' => $eventId,
					])->first();

					if($existing)
					{
						$existing->judge1_user_id = $j1;
						$existing->judge2_user_id = $j2;
						$existing->judge3_user_id = $j3;
						$this->JudgingAssignments->save($existing);
					}
					else
					{
						$newRow = $this->JudgingAssignments->newEntity([
							'conventionseason_id' => $sess_admin_header_season_id,
							'event_id' => $eventId,
							'judge1_user_id' => $j1,
							'judge2_user_id' => $j2,
							'judge3_user_id' => $j3,
						]);
						$this->JudgingAssignments->save($newRow);
					}
				}
				$this->Flash->success('Judging assignments saved successfully.');
				return $this->redirect(['action' => 'judginglist']);
			}
		}

		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		if(empty($convSeasonD))
		{
			$this->Flash->error('Selected convention season was not found.');
			return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
		}

		$events = $this->Conventionseasonevents->find()
			->contain(['Events'])
			->where(['Conventionseasonevents.conventionseasons_id' => $convSeasonD->id])
			->order(['Conventionseasonevents.event_id' => 'ASC'])
			->all();

		$judgeRegistrations = $this->Conventionregistrations->find()
			->contain(['Users'])
			->where([
				'Conventionregistrations.convention_id' => $convSeasonD->convention_id,
				'Conventionregistrations.season_id' => $convSeasonD->season_id,
				'Conventionregistrations.season_year' => $convSeasonD->season_year,
			])
			->order(['Conventionregistrations.id' => 'DESC'])
			->all();

		$judgePool = [];
		foreach($judgeRegistrations as $registration)
		{
			$userData = null;
			if(!empty($registration->Users))
			{
				$userData = $registration->Users;
			}
			elseif(!empty($registration->user))
			{
				$userData = $registration->user;
			}

			if(empty($userData))
			{
				continue;
			}

			$isJudge = ($userData['user_type'] == 'Judge') || ($userData['user_type'] == 'Teacher_Parent' && (int)$userData['is_judge'] === 1);
			if(!$isJudge)
			{
				continue;
			}

			$selectedEventIds = [];
			if(!empty($registration->judges_event_ids))
			{
				$rawIds = explode(',', (string)$registration->judges_event_ids);
				foreach($rawIds as $rawId)
				{
					$eventId = (int)trim($rawId);
					if($eventId > 0)
					{
						$selectedEventIds[$eventId] = $eventId;
					}
				}
			}

			$judgePool[] = [
				'name' => trim($userData['first_name'].' '.$userData['last_name']),
				'selected_event_ids' => $selectedEventIds,
				'selected_count' => count($selectedEventIds),
			];
		}

		$eventJudgeRows = [];
		foreach($events as $eventRow)
		{
			$eventId = (int)$eventRow->event_id;

			$eventName = 'Event #'.$eventId;
			$eventIdNumber = '';
			if(!empty($eventRow->Events))
			{
				if(!empty($eventRow->Events['event_name']))
				{
					$eventName = $eventRow->Events['event_name'];
				}
				if(!empty($eventRow->Events['event_id_number']))
				{
					$eventIdNumber = $eventRow->Events['event_id_number'];
				}
			}

			$preferredJudges = [];
			foreach($judgePool as $judge)
			{
				if(isset($judge['selected_event_ids'][$eventId]))
				{
					$preferredJudges[] = $judge;
				}
			}

			usort($preferredJudges, function($a, $b) {
				if($a['selected_count'] === $b['selected_count'])
				{
					return strcmp($a['name'], $b['name']);
				}
				return $a['selected_count'] <=> $b['selected_count'];
			});

			$panelTwo = array_slice($preferredJudges, 0, 2);
			$panelThree = array_slice($preferredJudges, 0, 3);

			$eventJudgeRows[] = [
				'event_id' => $eventId,
				'event_id_number' => $eventIdNumber,
				'event_name' => $eventName,
				'preferred_count' => count($preferredJudges),
				'preferred_names' => array_values(array_map(function($judge){ return $judge['name']; }, $preferredJudges)),
				'panel_two_names' => array_values(array_map(function($judge){ return $judge['name']; }, $panelTwo)),
				'panel_three_names' => array_values(array_map(function($judge){ return $judge['name']; }, $panelThree)),
			];
		}

		$eventsWithUnderTwo = 0;
		$eventsWithUnderThree = 0;
		foreach($eventJudgeRows as $row)
		{
			if($row['preferred_count'] < 2)
			{
				$eventsWithUnderTwo++;
			}
			if($row['preferred_count'] < 3)
			{
				$eventsWithUnderThree++;
			}
		}

		$this->set('convSeasonD', $convSeasonD);
		$this->set('eventJudgeRows', $eventJudgeRows);
		$this->set('totalJudgesInPool', count($judgePool));
		$this->set('eventsWithUnderTwo', $eventsWithUnderTwo);
		$this->set('eventsWithUnderThree', $eventsWithUnderThree);

		// Build judge dropdown: user_id => name, sorted by name
		$judgeDD = ['' => '-- None --'];
		foreach($judgePool as $j)
		{
			// need user_id; rebuild from judgeRegistrations
		}
		foreach($judgeRegistrations as $reg)
		{
			$userData = null;
			if(!empty($reg->Users)) { $userData = $reg->Users; }
			elseif(!empty($reg->user)) { $userData = $reg->user; }
			if(empty($userData)) { continue; }
			$isJudge = ($userData['user_type'] == 'Judge') || ($userData['user_type'] == 'Teacher_Parent' && (int)$userData['is_judge'] === 1);
			if(!$isJudge) { continue; }
			$judgeDD[(int)$userData['id']] = trim($userData['first_name'].' '.$userData['last_name']);
		}
		asort($judgeDD);
		$this->set('judgeDD', $judgeDD);

		// Load saved assignments keyed by event_id
		$savedRows = [];
		if($hasJudgingAssignments)
		{
			$savedRows = $this->JudgingAssignments->find()->where([
				'conventionseason_id' => $sess_admin_header_season_id,
			])->all();
		}
		$savedAssignments = [];
		foreach($savedRows as $row)
		{
			$savedAssignments[(int)$row->event_id] = [
				'judge1' => $row->judge1_user_id,
				'judge2' => $row->judge2_user_id,
				'judge3' => $row->judge3_user_id,
			];
		}
		$this->set('savedAssignments', $savedAssignments);
		$this->set('hasJudgingAssignments', $hasJudgingAssignments);

		// Build workload data: how many events each judge is currently assigned to
		$workloadCounts = [];
		foreach($savedAssignments as $eid => $panel)
		{
			foreach(['judge1','judge2','judge3'] as $slot)
			{
				$uid = (int)$panel[$slot];
				if($uid > 0)
				{
					if(!isset($workloadCounts[$uid])) { $workloadCounts[$uid] = 0; }
					$workloadCounts[$uid]++;
				}
			}
		}
		$workloadData = [];
		foreach($judgeDD as $uid => $name)
		{
			if($uid === '') { continue; }
			$uid = (int)$uid;
			$workloadData[] = [
				'user_id' => $uid,
				'name'    => $name,
				'count'   => isset($workloadCounts[$uid]) ? $workloadCounts[$uid] : 0,
			];
		}
		usort($workloadData, function($a, $b) { return $b['count'] <=> $a['count']; });
		$avgLoad = count($workloadData) > 0 ? array_sum(array_column($workloadData, 'count')) / count($workloadData) : 0;
		$this->set('workloadData', $workloadData);
		$this->set('avgLoad', round($avgLoad, 1));
    }

    public function judginglistcsv() {

        $this->viewBuilder()->setLayout('admin');

        $hasJudgingAssignments = true;
        if(!isset($this->JudgingAssignments)) {
            try {
                $this->JudgingAssignments = $this->loadModel('JudgingAssignments');
            } catch (\Throwable $e) {
                $hasJudgingAssignments = false;
            }
        }
        if($hasJudgingAssignments && !$this->hasJudgingAssignmentsTable()) {
            $hasJudgingAssignments = false;
        }

        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        if($sess_admin_header_season_id <= 0) {
            $this->Flash->error('Please select a convention season first.');
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }

        $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
        if(empty($convSeasonD)) {
            $this->Flash->error('Convention season not found.');
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }

        $events = $this->Conventionseasonevents->find()
            ->contain(['Events'])
            ->where(['Conventionseasonevents.conventionseasons_id' => $convSeasonD->id])
            ->order(['Conventionseasonevents.event_id' => 'ASC'])
            ->all();

        $judgeRegistrations = $this->Conventionregistrations->find()
            ->contain(['Users'])
            ->where([
                'Conventionregistrations.convention_id' => $convSeasonD->convention_id,
                'Conventionregistrations.season_id'     => $convSeasonD->season_id,
                'Conventionregistrations.season_year'   => $convSeasonD->season_year,
            ])
            ->all();

        $judgeNames = [];
        $judgePool = [];
        foreach($judgeRegistrations as $reg) {
            $userData = !empty($reg->Users) ? $reg->Users : (!empty($reg->user) ? $reg->user : null);
            if(empty($userData)) { continue; }
            $isJudge = ($userData['user_type'] == 'Judge') || ($userData['user_type'] == 'Teacher_Parent' && (int)$userData['is_judge'] === 1);
            if(!$isJudge) { continue; }

            $judgeId = (int)$userData['id'];
            $judgeName = trim($userData['first_name'].' '.$userData['last_name']);
            $selectedEventIds = [];
            if(!empty($reg->judges_event_ids)) {
                $rawIds = explode(',', (string)$reg->judges_event_ids);
                foreach($rawIds as $rawId) {
                    $eventId = (int)trim($rawId);
                    if($eventId > 0) {
                        $selectedEventIds[$eventId] = $eventId;
                    }
                }
            }

            $judgeNames[$judgeId] = $judgeName;
            $judgePool[] = [
                'id' => $judgeId,
                'name' => $judgeName,
                'selected_event_ids' => $selectedEventIds,
                'selected_count' => count($selectedEventIds),
            ];
        }

        $savedRows = [];
        if($hasJudgingAssignments) {
            $savedRows = $this->JudgingAssignments->find()->where(['conventionseason_id' => $sess_admin_header_season_id])->all();
        }
        $savedAssignments = [];
        foreach($savedRows as $row) {
            $savedAssignments[(int)$row->event_id] = [
                'judge1' => $row->judge1_user_id,
                'judge2' => $row->judge2_user_id,
                'judge3' => $row->judge3_user_id,
            ];
        }

        $filename = 'judging_list_'.preg_replace('/[^a-z0-9_]/i', '_', $convSeasonD->season_year).'_'.date('Ymd').'.csv';

        $this->autoRender = false;
        ob_start();
        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['Event Code', 'Event Name', 'Judge 1', 'Judge 2', 'Judge 3']);
        foreach($events as $eventRow) {
            $eid = (int)$eventRow->event_id;
            $eventName = 'Event #'.$eid;
            $eventCode = '';
            if(!empty($eventRow->Events)) {
                if(!empty($eventRow->Events['event_name'])) { $eventName = $eventRow->Events['event_name']; }
                if(!empty($eventRow->Events['event_id_number'])) { $eventCode = $eventRow->Events['event_id_number']; }
            }

            $saved = isset($savedAssignments[$eid]) ? $savedAssignments[$eid] : ['judge1'=>null,'judge2'=>null,'judge3'=>null];
            $j1 = isset($judgeNames[(int)$saved['judge1']]) ? $judgeNames[(int)$saved['judge1']] : '';
            $j2 = isset($judgeNames[(int)$saved['judge2']]) ? $judgeNames[(int)$saved['judge2']] : '';
            $j3 = isset($judgeNames[(int)$saved['judge3']]) ? $judgeNames[(int)$saved['judge3']] : '';

            if($j1 === '' && $j2 === '' && $j3 === '') {
                $preferredJudges = [];
                foreach($judgePool as $judge) {
                    if(isset($judge['selected_event_ids'][$eid])) {
                        $preferredJudges[] = $judge;
                    }
                }
                usort($preferredJudges, function($a, $b) {
                    if($a['selected_count'] === $b['selected_count']) {
                        return strcmp($a['name'], $b['name']);
                    }
                    return $a['selected_count'] <=> $b['selected_count'];
                });
                $j1 = isset($preferredJudges[0]['name']) ? $preferredJudges[0]['name'] : '';
                $j2 = isset($preferredJudges[1]['name']) ? $preferredJudges[1]['name'] : '';
                $j3 = isset($preferredJudges[2]['name']) ? $preferredJudges[2]['name'] : '';
            }

            fputcsv($fp, [$eventCode, $eventName, $j1, $j2, $j3]);
        }
        fclose($fp);
        $csvContent = ob_get_clean();

        $response = $this->response
            ->withType('text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->withHeader('Pragma', 'no-cache')
            ->withStringBody($csvContent);
        return $response;
    }
	public function allschools($conv_season_slug=null) {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Schools');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
		
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		$convSeasonD = null;
		if (!empty($sess_admin_header_season_id)) {
			$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		}
		
		$this->set('convSeasonD', $convSeasonD);
		
		$condition = array();
		
		$condition[] = "(Conventionregistrations.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrations.season_id = '".$convSeasonD->season_id."' AND Conventionregistrations.season_year = '".$convSeasonD->season_year."')";
		
		
		$conventionregistrations = $this->Conventionregistrations->find()->contain(['Users'])->where($condition)->order(["Conventionregistrations.id" => "DESC"])->all();

		// Deduplicate: keep only the latest registration per school (highest id comes first)
		$seen = [];
		$unique = [];
		foreach ($conventionregistrations as $record) {
			if (!in_array($record->user_id, $seen)) {
				$seen[] = $record->user_id;
				$unique[] = $record;
			}
		}
		$this->set('conventionregistrations', new \Cake\Collection\Collection($unique));
    }

	public function deleteschoolregistration($conv_reg_slug = null) {

		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if (empty($sess_admin_header_season_id)) {
			$this->Flash->error('Please choose a convention/season first.');
			return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'allschools']);
		}

		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		if (empty($convSeasonD->id)) {
			$this->Flash->error('Invalid convention/season selected.');
			return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'allschools']);
		}

		$convRegD = $this->Conventionregistrations->find()->contain(['Users'])->where([
			'Conventionregistrations.slug' => $conv_reg_slug,
			'Conventionregistrations.convention_id' => $convSeasonD->convention_id,
			'Conventionregistrations.season_id' => $convSeasonD->season_id,
			'Conventionregistrations.season_year' => $convSeasonD->season_year,
		])->first();

		if (empty($convRegD->id)) {
			$this->Flash->error('School registration not found.');
			return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'allschools']);
		}

		$registrationId = (int)$convRegD->id;

		// Remove linked submissions and uploaded files first.
		if ($this->hasTable('eventsubmissions')) {
			$this->loadModel('Eventsubmissions');
			$eventSubmissions = $this->Eventsubmissions->find()->where(['conventionregistration_id' => $registrationId])->all();
			foreach ($eventSubmissions as $submission) {
				$fileFields = ['mediafile_file_system_name', 'report', 'score_sheet', 'additional_documents'];
				foreach ($fileFields as $field) {
					$fileName = isset($submission->{$field}) ? $submission->{$field} : null;
					if (!empty($fileName) && file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH . $fileName)) {
						@unlink(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH . $fileName);
					}
				}
			}
			$this->Eventsubmissions->deleteAll(['conventionregistration_id' => $registrationId]);
		}

		if ($this->hasTable('judgeevaluations')) {
			$this->loadModel('Judgeevaluations');
			$this->Judgeevaluations->deleteAll(['conventionregistration_id' => $registrationId]);
		}

		if ($this->hasTable('crstudentevents')) {
			$this->loadModel('Crstudentevents');
			$this->Crstudentevents->deleteAll(['conventionregistration_id' => $registrationId]);
		}

		if ($this->hasTable('resultpositions')) {
			$this->Resultpositions->deleteAll(['conventionregistration_id' => $registrationId]);
		}

		$this->Conventionregistrationstudents->deleteAll(['conventionregistration_id' => $registrationId]);
		$this->Conventionregistrationteachers->deleteAll(['conventionregistration_id' => $registrationId]);

		if ($this->hasTable('heartevents')) {
			$this->Heartevents->deleteAll(['conventionregistration_id' => $registrationId]);
		}

		$this->Conventionregistrations->deleteAll(['id' => $registrationId]);

		$schoolName = !empty($convRegD->Users['first_name']) ? $convRegD->Users['first_name'] : 'School';
		$this->Flash->success($schoolName . ' registration deleted successfully.');
		return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'allschools']);
	}
	
	public function alljudges() {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Judges');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
        //$this->set('registrationsList', '1');
		
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		$convSeasonD = null;
		if (!empty($sess_admin_header_season_id)) {
			$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		}

		$condition = [];
		if (!empty($convSeasonD)) {
			$condition[] = "(Conventionregistrations.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrations.season_id = '".$convSeasonD->season_id."' AND Conventionregistrations.season_year = '".$convSeasonD->season_year."')";
		} else {
			$condition[] = "(Conventionregistrations.id IN (0))";
		}
		
		$conventionregistrations = $this->Conventionregistrations->find()->contain(['Users'])->where($condition)->order(["Conventionregistrations.id" => "DESC"])->all();

		// Deduplicate: keep only the latest registration per judge (highest id comes first)
		$seen = [];
		$unique = [];
		foreach ($conventionregistrations as $record) {
			if (!in_array($record->user_id, $seen)) {
				$seen[] = $record->user_id;
				$unique[] = $record;
			}
		}
		$this->set('conventionregistrations', new \Cake\Collection\Collection($unique));
    }

	public function participationcertificatepdf($resultpositions_slug = null) {
		$this->viewBuilder()->disableAutoLayout();

		if (!$resultpositions_slug) {
			return $this->response->withStringBody('Result position not found.');
		}

		$resultPositionD = $this->Resultpositions->find()->where(['Resultpositions.slug' => $resultpositions_slug])->contain(['Students','Users','Conventions'])->first();
		if (!$resultPositionD) {
			return $this->response->withStringBody('Result position not found.');
		}

		$arrCertData = array();
		$arrCertData['convention_name'] = $resultPositionD->Conventions['name'];
		$arrCertData['student_name'] = $resultPositionD->Students['first_name'];
		if (!empty($resultPositionD->Students['middle_name'])) {
			$arrCertData['student_name'] .= ' ' . $resultPositionD->Students['middle_name'];
		}
		if (!empty($resultPositionD->Students['last_name'])) {
			$arrCertData['student_name'] .= ' ' . $resultPositionD->Students['last_name'];
		}
		$arrCertData['school_name'] = $resultPositionD->Users['first_name'];
		$arrCertData['season_year'] = $resultPositionD->season_year;

		$this->set('arrCertData', $arrCertData);

		ini_set('memory_limit', '512M');
		set_time_limit(0);
	}

	public function placecertificatepdf($resultpositions_slug = null, $position = null) {
		$this->viewBuilder()->disableAutoLayout();

		if (!$resultpositions_slug) {
			return $this->response->withStringBody('Result position not found.');
		}

		$resultPositionD = $this->Resultpositions->find()->where(['Resultpositions.slug' => $resultpositions_slug])->contain(['Students','Users','Conventions','Events'])->first();
		if (!$resultPositionD) {
			return $this->response->withStringBody('Result position not found.');
		}

		global $resultPositions;
		$this->set('resultPositions', $resultPositions);

		$arrCertData = array();
		$arrCertData['convention_name'] = $resultPositionD->Conventions['name'];
		$arrCertData['student_name'] = $resultPositionD->Students['first_name'];
		if (!empty($resultPositionD->Students['middle_name'])) {
			$arrCertData['student_name'] .= ' ' . $resultPositionD->Students['middle_name'];
		}
		if (!empty($resultPositionD->Students['last_name'])) {
			$arrCertData['student_name'] .= ' ' . $resultPositionD->Students['last_name'];
		}
		$arrCertData['school_name'] = $resultPositionD->Users['first_name'];
		$arrCertData['season_year'] = $resultPositionD->season_year;
		$arrCertData['position'] = $position;
		$arrCertData['event_name'] = $resultPositionD->Events['event_name'];

		$this->set('arrCertData', $arrCertData);

		ini_set('memory_limit', '512M');
		set_time_limit(0);
	}
	

}

?>
