<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;

#[\AllowDynamicProperties]
class AdminsController extends AppController {

    public $paginate = ['limit' => 1];
    public $components = array('PImage');

    public function initialize(): void {
        parent::initialize();
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        
        // Updated to use getSession()
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') { // check admin login session, direct to admin login if session not active
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['action' => 'login']);
            }
        }
		
        $this->Emailtemplates = $this->loadModel('Emailtemplates');
        $this->Users = $this->loadModel('Users');
        $this->Seasons = $this->loadModel('Seasons');
        $this->Events = $this->loadModel('Events');
        $this->Conventions = $this->loadModel('Conventions');
        $this->Divisions = $this->loadModel('Divisions');
        $this->Settings = $this->loadModel('Settings');
        $this->Transactions = $this->loadModel('Transactions');
        $this->Conventionregistrations = $this->loadModel('Conventionregistrations');
        $this->Conventionregistrationstudents = $this->loadModel('Conventionregistrationstudents');
        $this->Conventionregistrationteachers = $this->loadModel('Conventionregistrationteachers');
        $this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
        $this->Crstudentevents = $this->loadModel('Crstudentevents');
        $this->Eventsubmissions = $this->loadModel('Eventsubmissions');
		$this->Judgeevaluations = $this->loadModel('Judgeevaluations');
    }

    public function login() {
        $this->set('title', ADMIN_TITLE . 'Admin Login');
        $this->viewBuilder()->setLayout('admin_login');

        // Updated to use getSession()
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($loggedAdminId) {
            return $this->redirect(['action' => 'dashboard']);
        }

        $admin = $this->Admins->newEntity([]);

        if ($this->request->is('post')) {
            $postData = $this->request->getData();

            $admin = $this->Admins->patchEntity($admin, $postData);
            if (count($admin->getErrors()) == 0) {
                $userName = $this->request->getData('Admins.username');
                $password = $this->request->getData('Admins.password');

                $adminInfo = $this->Admins->find()
                    ->where(['Admins.username' => $userName])
                    ->first();

                if ($adminInfo) {
                    if ($adminInfo->status == 0) {
                        $this->Flash->error('Your account got temporary disabled.');
                    } elseif (crypt($password, $adminInfo->password) == $adminInfo->password) {

                        if ($this->request->getData('Admins.remember') !== null && $this->request->getData('Admins.remember') == '1') {
                            setcookie("admin_username", $userName, time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_password", $password, time() + 60 * 60 * 24 * 100, "/");
                        } else {
                            setcookie("admin_username", '', time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_password", '', time() + 60 * 60 * 24 * 100, "/");
                        }

                        // Updated to use getSession()
                        $this->request->getSession()->write('admin_id', $adminInfo->id);
                        $this->request->getSession()->write('admin_username', $userName);

                        return $this->redirect(['action' => 'dashboard']);
                    } else {
                        $this->Flash->error('Invalid username or password.');
                    }
                } else {
                    $this->Flash->error('Invalid username or password.');
                }
            } else {
                $this->Flash->error('Please below listed errors.');
            }
        } else {
            if (isset($_COOKIE["admin_username"]) && isset($_COOKIE["admin_password"])) {
                $admin = $this->Admins->newEntity([
                    'username' => $_COOKIE["admin_username"],
                    'password' => $_COOKIE["admin_password"],
                    'remember' => 1
                ]);
            }
        }

        $this->set('admin', $admin);
    }

    public function forgotPassword() {
        $this->set('title', ADMIN_TITLE . 'Forgot Password');
        $this->viewBuilder()->setLayout('admin_login');

        $admin = $this->Admins->newEntity([]);
        if ($this->request->is('post')) {
            $admin = $this->Admins->patchEntity($admin, $this->request->getData(), ['validate' => 'forgotPassword']);
            if (count($admin->getErrors()) == 0) {
                $email = $this->request->getData('Admins.email');
                $adminInfo = $this->Admins->find()->where(['Admins.email' => $email])->first();
                if ($adminInfo) {
                    $new_password = rand(1000000, 999999999);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $this->Admins->updateAll(['password' => $password], ['id' => $adminInfo->id]);

                    $username = $adminInfo['username'];
                    $emailId = $adminInfo['email'];
                    
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '1'])->first();

                    $toRepArray = array('[!email!]', '[!username!]', '[!password!]', '[!HTTP_PATH!]', '[!SITE_TITLE!]');
                    $fromRepArray = array($emailId, $username, $new_password, HTTP_PATH, SITE_TITLE);

                    $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
                    $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);

                    $email = new Email();
                    $email->setTemplate('default')
                            ->setLayout('admintemplate');
                    $email->setEmailFormat('html')
                        ->setTo($emailId)
                        ->setFrom([MAIL_FROM => SITE_TITLE])
                        ->setSubject($subjectToSend)
                        ->setViewVars(['content_for_layout' => $messageToSend])
                        ->send();

                    $this->Flash->success('New admin password sent to admin email address.');
                    $this->redirect(['action' => 'login']);
                } else {
                    $this->Flash->error('Invalid email address, please enter correct email address.');
                }
            }
        }
        $this->set('admin', $admin);
    }

    public function logout() {
        // Clear session via framework interface safely
        $this->request->getSession()->destroy();
        $this->Flash->success('Logout successfully.');
        $this->redirect(['action' => 'login']);
    }

    public function headerchooseconvseas() {
        $admin_header_season_id = $this->request->getData('admin_header_season_id');
		
        if($admin_header_season_id > 0)
        {
            $convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$admin_header_season_id])->contain(['Conventions'])->first();
			
            if($convSD)
            {
                // Updated to use getSession()
                $this->request->getSession()->write('sess_admin_header_season_id', $admin_header_season_id);
				
                return $this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convSD->Conventions['slug']]);
            }
        }
        else
        {
            // Updated to use getSession()
            $this->request->getSession()->write('sess_admin_header_season_id', 0);
        }
		
        return $this->redirect(['action' => 'dashboard']);
    }
	
    public function dashboard() {
        $this->set('title', ADMIN_TITLE . 'Admin Dashboard');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
		
        // Updated to use getSession()
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        $this->set('sess_admin_header_season_id', $sess_admin_header_season_id);
        if($sess_admin_header_season_id > 0)
        {
            $convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$sess_admin_header_season_id])->first();
			
            $this->set('conv_season_slug', $convSD->slug);
			
            $total_students = $this->Conventionregistrationstudents->find()
                ->select(['student_id'])
                ->where([
                    "convention_id" => $convSD->convention_id,
                    "season_id" => $convSD->season_id,
                    "season_year" => $convSD->season_year,
                    "student_id IS NOT" => null,
                    "student_id >" => 0,
                ])
                ->distinct(['student_id'])
                ->count();
            $this->set('total_students', $total_students);
			
            $total_teachers_parents = $this->Conventionregistrationteachers->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->count();
            $this->set('total_teachers_parents', $total_teachers_parents);
			
            $cntrSchools = 0;
            $listSchools = $this->Conventionregistrations->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->contain(['Users'])->all();
            foreach($listSchools as $schoolcntr)
            {
                if(isset($schoolcntr->Users) && $schoolcntr->Users['user_type'] == "School")
                {
                    $cntrSchools++;
                }
            }
            $this->set('total_schools', $cntrSchools);
			
            $cntrJudges = 0;
            $listCR = $this->Conventionregistrations->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->contain(['Users'])->all();
            foreach($listCR as $judgcntr)
            {
                if(isset($judgcntr->Users) && ($judgcntr->Users['user_type'] == "Judge" || $judgcntr->Users['user_type'] == "Teacher_Parent") && $judgcntr->Users['is_judge'] == 1)
                {
                    $cntrJudges++;
                }
            }
            $this->set('total_judges', $cntrJudges);

            $cntrPastors = 0;
            foreach($listCR as $pastorcntr)
            {
                if(isset($pastorcntr->Users) && $pastorcntr->Users['user_type'] == 'Teacher_Parent' && strtolower(trim((string)$pastorcntr->Users['title'])) == 'pastor')
                {
                    $cntrPastors++;
                }
            }
            $this->set('total_pastors', $cntrPastors);
			
            // Count registered event entries, not configured season event definitions.
            $total_conv_seas_events = $this->Crstudentevents->find()->where(["conventionseason_id"=> $convSD->id])->count();
            $this->set('total_conv_seas_events', $total_conv_seas_events);

			$total_events_judged = $this->Judgeevaluations->find()->where(["conventionseason_id" => $convSD->id])->count();
			$this->set('total_events_judged', $total_events_judged);

            $total_running_events = $this->Conventionseasonevents->find()
                ->innerJoinWith('Events', function ($q) {
                    return $q->where(['Events.event_judging_type' => 'times']);
                })
                ->where(["Conventionseasonevents.conventionseasons_id" => $convSD->id])
                ->count();
            $this->set('total_running_events', $total_running_events);
			
            $condTr = array();
            $condTr[] = "(Transactions.conventionseason_id = '".$convSD->id."')";
			
            $total_transactions = $this->Transactions->find()->where($condTr)->count();
            $this->set('total_transactions', $total_transactions);
        }
        else
        {
            $total_seasons = $this->Seasons->find()->where(['1 = 1'])->count();
            $this->set('total_seasons', $total_seasons);
			
            $total_events = $this->Events->find()->where(['1 = 1'])->count();
            $this->set('total_events', $total_events);
			
            $total_conventions = $this->Conventions->find()->where(['1 = 1'])->count();
            $this->set('total_conventions', $total_conventions);
			
            $total_divisions = $this->Divisions->find()->where(['1 = 1'])->count();
            $this->set('total_divisions', $total_divisions);
			
            $total_schools = $this->Users->find()->where(["user_type"=> "School"])->count();
            $this->set('total_schools', $total_schools);
			
            $total_teachers_parents = $this->Users->find()->where(["user_type"=> "Teacher_Parent"])->count();
            $this->set('total_teachers_parents', $total_teachers_parents);
			
            $total_students = $this->Users->find()->where(["user_type"=> "Student"])->count();
            $this->set('total_students', $total_students);

            $total_pastors = $this->Users->find()->where(["user_type"=> "Teacher_Parent", "LOWER(TRIM(title))" => 'pastor'])->count();
            $this->set('total_pastors', $total_pastors);
			
            $total_registrations = $this->Conventionregistrations->find()->where(['1 = 1'])->count();
            $this->set('total_registrations', $total_registrations);
			
            $total_transactions = $this->Transactions->find()->where(['1 = 1'])->count();
            $this->set('total_transactions', $total_transactions);
			
            $condJ = array();
            $condJ[] = "(Users.activation_status = '1' AND (Users.status = '1' OR Users.status = '2'))";
            $condJ[] = "(Users.user_type = 'Judge' OR (Users.user_type = 'Teacher_Parent' AND Users.is_judge = '1'))";
            $total_judges = $this->Users->find()->where($condJ)->count();
            $this->set('total_judges', $total_judges);
        }
    }

    public function runninglist() {
        $this->set('title', ADMIN_TITLE . 'Running List');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        // Updated to use getSession()
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        if (empty($sess_admin_header_season_id)) {
            $this->Flash->error('Please select a convention season from the header first.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
        if (!$convSeasonD) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $condition = array();
        $condition[] = "(Conventionseasonevents.conventionseasons_id = '" . $convSeasonD->id . "')";

        $conventionseasonevents = $this->Conventionseasonevents->find()
            ->contain(['Events'])
            ->innerJoinWith('Events', function ($q) {
                return $q->where(['Events.event_judging_type' => 'times']);
            })
            ->where($condition)
            ->order(["Conventionseasonevents.id" => "DESC"])
            ->all();

        $this->set('convSeasonD', $convSeasonD);
        $this->set('conventionseasonevents', $conventionseasonevents);
    }

    public function runninglistprint($conventionSeasonEventId = null, $runnersPerHeat = null) {
        $this->set('title', ADMIN_TITLE . 'Running List Print');
        $this->viewBuilder()->setLayout('print_reports');

        $runnersPerHeat = (!empty($runnersPerHeat) && is_numeric($runnersPerHeat) && (int)$runnersPerHeat > 0)
            ? (int)$runnersPerHeat
            : null;

        // Updated to use getSession()
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        if (empty($sess_admin_header_season_id)) {
            $this->Flash->error('Please select a convention season from the header first.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
        if (!$convSeasonD) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        if (empty($conventionSeasonEventId)) {
            $this->Flash->error('Invalid running event selected.');
            return $this->redirect(['action' => 'runninglist']);
        }

        $conventionSeasonEvent = $this->Conventionseasonevents->find()
            ->contain(['Events'])
            ->where([
                'Conventionseasonevents.id' => $conventionSeasonEventId,
                'Conventionseasonevents.conventionseasons_id' => $convSeasonD->id,
                'Events.event_judging_type' => 'times'
            ])
            ->first();

        if (!$conventionSeasonEvent) {
            $this->Flash->error('Running event not found.');
            return $this->redirect(['action' => 'runninglist']);
        }

        $eventSubmissions = $this->Eventsubmissions->find()
            ->contain(['Students', 'Users'])
            ->where([
                'Eventsubmissions.conventionseason_id' => $convSeasonD->id,
                'Eventsubmissions.event_id' => $conventionSeasonEvent->event_id
            ])
            ->order(['Students.first_name' => 'ASC', 'Students.last_name' => 'ASC'])
            ->all();

        $this->set('convSeasonD', $convSeasonD);
        $this->set('conventionSeasonEvent', $conventionSeasonEvent);
        $this->set('eventSubmissions', $eventSubmissions);
        $this->set('runnersPerHeat', $runnersPerHeat);
    }

    public function runninglistprintall($runnersPerHeat = null) {
        $this->set('title', ADMIN_TITLE . 'Running List Print All');
        $this->viewBuilder()->setLayout('print_reports');

        $runnersPerHeat = (!empty($runnersPerHeat) && is_numeric($runnersPerHeat) && (int)$runnersPerHeat > 0)
            ? (int)$runnersPerHeat
            : null;

        $heatMapRaw = (array)$this->request->getQuery('heatmap', []);
        $heatMap = [];
        foreach ($heatMapRaw as $cseId => $heatSize) {
            if (is_numeric($cseId) && is_numeric($heatSize) && (int)$cseId > 0 && (int)$heatSize > 0) {
                $heatMap[(int)$cseId] = (int)$heatSize;
            }
        }

        $orderMapRaw = (array)$this->request->getQuery('ordermap', []);
        $orderMap = [];
        foreach ($orderMapRaw as $cseId => $sortOrder) {
            if (is_numeric($cseId) && is_numeric($sortOrder) && (int)$cseId > 0 && (int)$sortOrder > 0) {
                $orderMap[(int)$cseId] = (int)$sortOrder;
            }
        }

        $eventOrderRaw = (array)$this->request->getQuery('eventorder', []);
        $eventOrder = [];
        foreach ($eventOrderRaw as $orderedEventId) {
            if (is_numeric($orderedEventId) && (int)$orderedEventId > 0) {
                $eventOrder[] = (int)$orderedEventId;
            }
        }

        $combineMapRaw = (array)$this->request->getQuery('combinemap', []);
        $combineMap = [];
        foreach ($combineMapRaw as $cseId => $combineKey) {
            if (!is_numeric($cseId) || (int)$cseId <= 0) {
                continue;
            }
            $normalizedKey = trim((string)$combineKey);
            if ($normalizedKey === '') {
                continue;
            }
            $combineMap[(int)$cseId] = substr($normalizedKey, 0, 20);
        }

        // Updated to use getSession()
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        if (empty($sess_admin_header_season_id)) {
            $this->Flash->error('Please select a convention season from the header first.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
        if (!$convSeasonD) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $runningEvents = $this->Conventionseasonevents->find()
            ->contain(['Events'])
            ->innerJoinWith('Events', function ($q) {
                return $q->where(['Events.event_judging_type' => 'times']);
            })
            ->where(['Conventionseasonevents.conventionseasons_id' => $convSeasonD->id])
            ->all()
            ->toArray();

        $eventOrderIndex = array_flip($eventOrder);
        usort($runningEvents, function ($left, $right) use ($eventOrderIndex, $orderMap) {
            $leftId = (int)$left->id;
            $rightId = (int)$right->id;

            $leftSequence = $eventOrderIndex[$leftId] ?? PHP_INT_MAX;
            $rightSequence = $eventOrderIndex[$rightId] ?? PHP_INT_MAX;
            if ($leftSequence !== $rightSequence) {
                return $leftSequence <=> $rightSequence;
            }

            $leftOrder = $orderMap[$leftId] ?? PHP_INT_MAX;
            $rightOrder = $orderMap[$rightId] ?? PHP_INT_MAX;
            if ($leftOrder !== $rightOrder) {
                return $leftOrder <=> $rightOrder;
            }

            return $leftId <=> $rightId;
        });

        $raceGroups = [];
        foreach ($runningEvents as $eventRecord) {
            $eventRecordId = (int)$eventRecord->id;
            $groupKey = $combineMap[$eventRecordId] ?? '';
            if ($groupKey === '') {
                $groupKey = '__single_' . $eventRecordId;
            }

            if (!isset($raceGroups[$groupKey])) {
                $raceGroups[$groupKey] = [
                    'group_key' => $groupKey,
                    'events' => []
                ];
            }

            $raceGroups[$groupKey]['events'][] = $eventRecord;
        }

        $raceGroups = array_values($raceGroups);

        $eventIds = [];
        foreach ($runningEvents as $evt) {
            $eventIds[] = (int)$evt->event_id;
        }

        $submissionsByEvent = [];
        if (!empty($eventIds)) {
            $allSubmissions = $this->Eventsubmissions->find()
                ->contain(['Students', 'Users'])
                ->where([
                    'Eventsubmissions.conventionseason_id' => $convSeasonD->id,
                    'Eventsubmissions.event_id IN' => $eventIds
                ])
                ->order([
                    'Eventsubmissions.event_id' => 'ASC',
                    'Students.first_name' => 'ASC',
                    'Students.last_name' => 'ASC'
                ])
                ->all();

            foreach ($allSubmissions as $submission) {
                $evtId = (int)$submission->event_id;
                if (!isset($submissionsByEvent[$evtId])) {
                    $submissionsByEvent[$evtId] = [];
                }
                $submissionsByEvent[$evtId][] = $submission;
            }
        }

        $this->set('convSeasonD', $convSeasonD);
        $this->set('runningEvents', $runningEvents);
        $this->set('raceGroups', $raceGroups);
        $this->set('submissionsByEvent', $submissionsByEvent);
        $this->set('runnersPerHeat', $runnersPerHeat);
        $this->set('heatMap', $heatMap);
    }

    public function runninglistcsv($runnersPerHeat = null) {
        $runnersPerHeat = (!empty($runnersPerHeat) && is_numeric($runnersPerHeat) && (int)$runnersPerHeat > 0)
            ? (int)$runnersPerHeat
            : null;

        $heatMapRaw = (array)$this->request->getQuery('heatmap', []);
        $heatMap = [];
        foreach ($heatMapRaw as $cseId => $heatSize) {
            if (is_numeric($cseId) && is_numeric($heatSize) && (int)$cseId > 0 && (int)$heatSize > 0) {
                $heatMap[(int)$cseId] = (int)$heatSize;
            }
        }

        $orderMapRaw = (array)$this->request->getQuery('ordermap', []);
        $orderMap = [];
        foreach ($orderMapRaw as $cseId => $sortOrder) {
            if (is_numeric($cseId) && is_numeric($sortOrder) && (int)$cseId > 0 && (int)$sortOrder > 0) {
                $orderMap[(int)$cseId] = (int)$sortOrder;
            }
        }

        $eventOrderRaw = (array)$this->request->getQuery('eventorder', []);
        $eventOrder = [];
        foreach ($eventOrderRaw as $orderedEventId) {
            if (is_numeric($orderedEventId) && (int)$orderedEventId > 0) {
                $eventOrder[] = (int)$orderedEventId;
            }
        }

        $combineMapRaw = (array)$this->request->getQuery('combinemap', []);
        $combineMap = [];
        foreach ($combineMapRaw as $cseId => $combineKey) {
            if (!is_numeric($cseId) || (int)$cseId <= 0) {
                continue;
            }
            $normalizedKey = trim((string)$combineKey);
            if ($normalizedKey === '') {
                continue;
            }
            $combineMap[(int)$cseId] = substr($normalizedKey, 0, 20);
        }

        // Updated to use getSession()
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        if (empty($sess_admin_header_season_id)) {
            $this->Flash->error('Please select a convention season from the header first.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
        if (!$convSeasonD) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $runningEvents = $this->Conventionseasonevents->find()
            ->contain(['Events'])
            ->innerJoinWith('Events', function ($q) {
                return $q->where(['Events.event_judging_type' => 'times']);
            })
            ->where(['Conventionseasonevents.conventionseasons_id' => $convSeasonD->id])
            ->all()
            ->toArray();

        $eventOrderIndex = array_flip($eventOrder);
        usort($runningEvents, function ($left, $right) use ($eventOrderIndex, $orderMap) {
            $leftId = (int)$left->id;
            $rightId = (int)$right->id;

            $leftSequence = $eventOrderIndex[$leftId] ?? PHP_INT_MAX;
            $rightSequence = $eventOrderIndex[$rightId] ?? PHP_INT_MAX;
            if ($leftSequence !== $rightSequence) {
                return $leftSequence <=> $rightSequence;
            }

            $leftOrder = $orderMap[$leftId] ?? PHP_INT_MAX;
            $rightOrder = $orderMap[$rightId] ?? PHP_INT_MAX;
            if ($leftOrder !== $rightOrder) {
                return $leftOrder <=> $rightOrder;
            }

            return $leftId <=> $rightId;
        });

        $raceGroups = [];
        foreach ($runningEvents as $eventRecord) {
            $eventRecordId = (int)$eventRecord->id;
            $groupKey = $combineMap[$eventRecordId] ?? '';
            if ($groupKey === '') {
                $groupKey = '__single_' . $eventRecordId;
            }

            if (!isset($raceGroups[$groupKey])) {
                $raceGroups[$groupKey] = [
                    'group_key' => $groupKey,
                    'events' => []
                ];
            }

            $raceGroups[$groupKey]['events'][] = $eventRecord;
        }

        $raceGroups = array_values($raceGroups);

        $eventIds = [];
        foreach ($runningEvents as $evt) {
            $eventIds[] = (int)$evt->event_id;
        }

        $submissionsByEvent = [];
        if (!empty($eventIds)) {
            $allSubmissions = $this->Eventsubmissions->find()
                ->contain(['Students', 'Users'])
                ->where([
                    'Eventsubmissions.conventionseason_id' => $convSeasonD->id,
                    'Eventsubmissions.event_id IN' => $eventIds
                ])
                ->order([
                    'Eventsubmissions.event_id' => 'ASC',
                    'Students.first_name' => 'ASC',
                    'Students.last_name' => 'ASC'
                ])
                ->all();

            foreach ($allSubmissions as $submission) {
                $evtId = (int)$submission->event_id;
                if (!isset($submissionsByEvent[$evtId])) {
                    $submissionsByEvent[$evtId] = [];
                }
                $submissionsByEvent[$evtId][] = $submission;
            }
        }

        $csvRows = [];
        $csvRows[] = [
            'Running Order',
            'Combine Group',
            'Race Name',
            'Event ID Numbers',
            'Qualifying Time',
            'Format',
            'Heat Number',
            'Total Heats',
            'Lane',
            'Student Name',
            'Year of Birth',
            'School',
            'Source Event Name',
            'Source Event ID Number'
        ];

        foreach ($raceGroups as $raceGroup) {
            $groupEvents = isset($raceGroup['events']) && is_array($raceGroup['events']) ? $raceGroup['events'] : [];
            if (empty($groupEvents)) {
                continue;
            }

            $isCombinedRace = count($groupEvents) > 1;
            $firstEventRecord = $groupEvents[0];
            $firstEventD = $firstEventRecord->Events;

            $eventTitleParts = [];
            $eventCodeParts = [];
            $groupRows = [];
            $groupHeatSizeCandidates = [];
            $groupQualifyingRaw = null;

            foreach ($groupEvents as $groupEventRecord) {
                $groupEventD = $groupEventRecord->Events;
                $eventTitleParts[] = $groupEventD->event_name;
                $eventCodeParts[] = $groupEventD->event_id_number;

                $groupEventRows = isset($submissionsByEvent[(int)$groupEventRecord->event_id]) ? $submissionsByEvent[(int)$groupEventRecord->event_id] : [];
                $groupRows = array_merge($groupRows, $groupEventRows);

                $groupEventId = (int)$groupEventRecord->id;
                if (isset($heatMap[$groupEventId]) && (int)$heatMap[$groupEventId] > 0) {
                    $eventHeatSize = (int)$heatMap[$groupEventId];
                    $eventEntryCount = count($groupEventRows);
                    if (!$isCombinedRace || $eventHeatSize !== $eventEntryCount) {
                        $groupHeatSizeCandidates[] = $eventHeatSize;
                    }
                }

                if (empty($groupQualifyingRaw) && !empty($groupEventRecord->qualifying_time_score)) {
                    $groupQualifyingRaw = $groupEventRecord->qualifying_time_score;
                }
            }

            $displayEventName = $isCombinedRace
                ? 'Combined: ' . implode(' + ', $eventTitleParts)
                : $firstEventD->event_name;
            $displayEventCodes = implode(', ', $eventCodeParts);
            $qualifyingTime = !empty($groupQualifyingRaw) ? date('i:s', strtotime($groupQualifyingRaw)) : 'N/A';

            $uniqueRows = [];
            $seenStudents = [];
            foreach ($groupRows as $submission) {
                if (!empty($submission->student_id) && isset($seenStudents[$submission->student_id])) {
                    continue;
                }
                if (!empty($submission->student_id)) {
                    $seenStudents[$submission->student_id] = true;
                }
                $uniqueRows[] = $submission;
            }

            $entriesCount = count($uniqueRows);
            if ($entriesCount <= 0) {
                continue;
            }

            if (!empty($groupHeatSizeCandidates)) {
                $groupHeatSize = max($groupHeatSizeCandidates);
            } elseif ($isCombinedRace) {
                $groupHeatSize = $entriesCount;
            } else {
                $groupHeatSize = (int)($runnersPerHeat ?? 6);
            }

            if (!empty($groupHeatSize) && $groupHeatSize > 0 && $entriesCount > $groupHeatSize) {
                $heats = array_chunk($uniqueRows, $groupHeatSize);
                $isHeated = true;
            } else {
                $heats = [$uniqueRows];
                $isHeated = false;
            }

            $totalHeats = count($heats);
            $runningOrder = $orderMap[(int)$firstEventRecord->id] ?? '';
            $combineGroup = $raceGroup['group_key'] ?? '';
            if (strpos($combineGroup, '__single_') === 0) {
                $combineGroup = '';
            }

            foreach ($heats as $heatIndex => $heatRows) {
                $heatNumber = $heatIndex + 1;
                $formatLabel = $isHeated ? "Heat {$heatNumber} of {$totalHeats}" : 'FINAL';

                foreach ($heatRows as $laneIndex => $submission) {
                    $studentName = 'N/A';
                    $birthYear = '';
                    $schoolName = 'N/A';

                    if (!empty($submission->student_id) && !empty($submission->Students)) {
                        $studentName = trim(
                            ($submission->Students['first_name'] ?? '') . ' ' .
                            ($submission->Students['middle_name'] ?? '') . ' ' .
                            ($submission->Students['last_name'] ?? '')
                        );
                        $birthYear = !empty($submission->Students['birth_date']) ? date('Y', strtotime($submission->Students['birth_date'])) : '';
                    }

                    if (!empty($submission->Users['first_name'])) {
                        $schoolName = $submission->Users['first_name'];
                    }

                    $sourceEventName = 'N/A';
                    $sourceEventCode = 'N/A';
                    if (!empty($submission->event_id)) {
                        foreach ($groupEvents as $ge) {
                            if ((int)$ge->event_id === (int)$submission->event_id && !empty($ge->Events)) {
                                $sourceEventName = $ge->Events->event_name;
                                $sourceEventCode = $ge->Events->event_id_number;
                                break;
                            }
                        }
                    }

                    $csvRows[] = [
                        $runningOrder,
                        $combineGroup,
                        $displayEventName,
                        $displayEventCodes,
                        $qualifyingTime,
                        $formatLabel,
                        $heatNumber,
                        $totalHeats,
                        $laneIndex + 1,
                        $studentName,
                        $birthYear,
                        $schoolName,
                        $sourceEventName,
                        $sourceEventCode
                    ];
                }
            }
        }

        // Generate response stream for browser download integration
        $this->autoRender = false;
        $response = $this->response->withType('csv')
            ->withDownload('running_list_' . $convSeasonD->id . '.csv');
        
        $stream = fopen('php://temp', 'w+');
        foreach ($csvRows as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $response = $response->withStringBody(stream_get_contents($stream));
        fclose($stream);
        
        return $response;
    }

    /**
     * Change email action
     * Allows admin to change their email address
     */
    public function changeEmail() {
        $this->viewBuilder()->setLayout('admin');
        $this->set('title', ADMIN_TITLE . 'Change Email');
        
        $adminId = $this->request->getSession()->read('admin_id');
        if (!$adminId) {
            return $this->redirect(['action' => 'login']);
        }

        $adminInfo = $this->Admins->find()->where(['Admins.id' => $adminId])->first();
        $admin = $this->Admins->newEntity([]);

        if ($this->request->is('post')) {
            $newEmail = $this->request->getData('Admins.new_email');
            $confirmEmail = $this->request->getData('Admins.conf_email');

            if ($newEmail !== $confirmEmail) {
                $this->Flash->error('New email and confirm email do not match.');
            } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $this->Flash->error('Invalid email format.');
            } else {
                $existingAdmin = $this->Admins->find()->where(['Admins.email' => $newEmail, 'Admins.id !=' => $adminId])->first();
                if ($existingAdmin) {
                    $this->Flash->error('This email is already in use.');
                } else {
                    $this->Admins->updateAll(['email' => $newEmail], ['id' => $adminId]);
                    $this->Flash->success('Email changed successfully.');
                    $adminInfo->email = $newEmail;
                }
            }
        }

        $this->set('admin', $admin);
        $this->set('adminInfo', $adminInfo);
    }

    /**
     * Change username action
     * Allows admin to change their username
     */
    public function changeUsername() {
        $this->viewBuilder()->setLayout('admin');
        $this->set('title', ADMIN_TITLE . 'Change Username');
        
        $adminId = $this->request->getSession()->read('admin_id');
        if (!$adminId) {
            return $this->redirect(['action' => 'login']);
        }

        $adminInfo = $this->Admins->find()->where(['Admins.id' => $adminId])->first();
        $admin = $this->Admins->newEntity([]);

        if ($this->request->is('post')) {
            $newUsername = $this->request->getData('Admins.new_username');
            $confirmUsername = $this->request->getData('Admins.conf_username');

            if ($newUsername !== $confirmUsername) {
                $this->Flash->error('New username and confirm username do not match.');
            } elseif (strlen($newUsername) < 3) {
                $this->Flash->error('Username must be at least 3 characters.');
            } else {
                $existingAdmin = $this->Admins->find()->where(['Admins.username' => $newUsername, 'Admins.id !=' => $adminId])->first();
                if ($existingAdmin) {
                    $this->Flash->error('This username is already in use.');
                } else {
                    $this->Admins->updateAll(['username' => $newUsername], ['id' => $adminId]);
                    $this->request->getSession()->write('admin_username', $newUsername);
                    $this->Flash->success('Username changed successfully.');
                    $adminInfo->username = $newUsername;
                }
            }
        }

        $this->set('admin', $admin);
        $this->set('adminInfo', $adminInfo);
    }

    /**
     * Change password action
     * Allows admin to change their password
     */
    public function changePassword() {
        $this->viewBuilder()->setLayout('admin');
        $this->set('title', ADMIN_TITLE . 'Change Password');
        
        $adminId = $this->request->getSession()->read('admin_id');
        if (!$adminId) {
            return $this->redirect(['action' => 'login']);
        }

        $adminInfo = $this->Admins->find()->where(['Admins.id' => $adminId])->first();
        $admin = $this->Admins->newEntity([]);

        if ($this->request->is('post')) {
            $currentPassword = $this->request->getData('Admins.current_password');
            $newPassword = $this->request->getData('Admins.new_password');
            $confirmPassword = $this->request->getData('Admins.conf_password');

            if (crypt($currentPassword, $adminInfo->password) != $adminInfo->password) {
                $this->Flash->error('Current password is incorrect.');
            } elseif ($newPassword !== $confirmPassword) {
                $this->Flash->error('New password and confirm password do not match.');
            } elseif (strlen($newPassword) < 6) {
                $this->Flash->error('Password must be at least 6 characters.');
            } else {
                $salt = uniqid(mt_rand(), true);
                $hashedPassword = crypt($newPassword, '$2a$07$' . $salt . '$');
                $this->Admins->updateAll(['password' => $hashedPassword], ['id' => $adminId]);
                $this->Flash->success('Password changed successfully.');
            }
        }

        $this->set('admin', $admin);
        $this->set('adminInfo', $adminInfo);
    }

    /**
     * Settings action
     * Admin settings management
     */
    public function settings() {
        $this->viewBuilder()->setLayout('admin');
        $this->set('title', ADMIN_TITLE . 'Settings');
        
        $adminId = $this->request->getSession()->read('admin_id');
        if (!$adminId) {
            return $this->redirect(['action' => 'login']);
        }

        $adminInfo = $this->Admins->find()->where(['Admins.id' => $adminId])->first();
        $this->set('adminInfo', $adminInfo);
    }

    public function videos() {
        $this->viewBuilder()->setLayout('admin');
        $this->set('title', ADMIN_TITLE . 'Dashboard Videos');

        $adminId = $this->request->getSession()->read('admin_id');
        if (!$adminId) {
            return $this->redirect(['action' => 'login']);
        }

        $settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
        if (!$settingsInfo) {
            $this->Flash->error('Settings record not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $videoLinks = [];
        if (!empty($settingsInfo->video_links_json)) {
            $decodedLinks = json_decode((string)$settingsInfo->video_links_json, true);
            if (is_array($decodedLinks)) {
                $videoLinks = array_values(array_filter(array_map('trim', $decodedLinks), static function ($value) {
                    return $value !== '';
                }));
            }
        }

        if (empty($videoLinks)) {
            for ($i = 1; $i <= 9; $i++) {
                $fieldName = 'video_link_' . $i;
                $fieldValue = isset($settingsInfo->{$fieldName}) ? trim((string)$settingsInfo->{$fieldName}) : '';
                if ($fieldValue !== '') {
                    $videoLinks[] = $fieldValue;
                }
            }
        }

        if ($this->request->is('post')) {
            $settingsData = (array)$this->request->getData('Settings', []);
            $submittedVideoLinks = isset($settingsData['video_links']) && is_array($settingsData['video_links']) ? $settingsData['video_links'] : [];
            $submittedVideoLinks = array_values(array_filter(array_map('trim', $submittedVideoLinks), static function ($value) {
                return $value !== '';
            }));

            $fields = [
                'video_links_json' => json_encode($submittedVideoLinks, JSON_UNESCAPED_SLASHES),
            ];

            for ($i = 1; $i <= 9; $i++) {
                $fieldName = 'video_link_' . $i;
                $fields[$fieldName] = isset($submittedVideoLinks[$i - 1]) ? $submittedVideoLinks[$i - 1] : null;
            }

            $fields['modified'] = date('Y-m-d H:i:s');
            $this->Settings->updateAll($fields, ['Settings.id' => 1]);

            $this->Flash->success('Dashboard video links updated successfully.');
            return $this->redirect(['action' => 'videos']);
        }

        $this->set('settingsInfo', $settingsInfo);
        $this->set('videoLinks', $videoLinks);
        $this->set('manageConfig', '1');
        $this->set('videos', '1');
    }

    /**
     * Post info action
     * Post information management
     */
    public function postinfo() {
        $this->viewBuilder()->setLayout('admin');
        $this->set('title', ADMIN_TITLE . 'Post Information');
        
        $adminId = $this->request->getSession()->read('admin_id');
        if (!$adminId) {
            return $this->redirect(['action' => 'login']);
        }

        $adminInfo = $this->Admins->find()->where(['Admins.id' => $adminId])->first();
        $this->set('adminInfo', $adminInfo);
    }
}