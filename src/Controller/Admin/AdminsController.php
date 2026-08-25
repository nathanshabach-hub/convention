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
        $this->Conventionseasons = $this->loadModel('Conventionseasons');
        $this->Events = $this->loadModel('Events');
        $this->Conventions = $this->loadModel('Conventions');
        $this->Divisions = $this->loadModel('Divisions');
        $this->Settings = $this->loadModel('Settings');
        $this->Transactions = $this->loadModel('Transactions');
        $this->Conventionregistrations = $this->loadModel('Conventionregistrations');
        $this->Conventionregistrationstudents = $this->loadModel('Conventionregistrationstudents');
        $this->Conventionregistrationteachers = $this->loadModel('Conventionregistrationteachers');
        $this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
		$this->Schedulingtimings = $this->loadModel('Schedulingtimings');
        $this->Schedulings = $this->loadModel('Schedulings');
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

        $allSquad247Submissions = $this->loadSquad247Submissions();
        $this->set('total_squad247', count($allSquad247Submissions));
		
        // Updated to use getSession()
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        $this->set('sess_admin_header_season_id', $sess_admin_header_season_id);
        if($sess_admin_header_season_id > 0)
        {
            $convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$sess_admin_header_season_id])->contain(['Conventions'])->first();

            if (!$convSD) {
                $this->Flash->error('Selected convention season was not found.');
                return $this->redirect(['action' => 'dashboard']);
            }
			
            $this->set('conv_season_slug', $convSD->slug);
            $selectedConventionName = trim((string)($convSD->Conventions['name'] ?? ''));
            $this->set('total_squad247', $this->countSquad247SubmissionsForConvention($allSquad247Submissions, $selectedConventionName));
			
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

            $hasScheduleRows = $this->Schedulingtimings->find()
                ->where(['Schedulingtimings.conventionseasons_id' => $convSD->id])
                ->count() > 0;
            $this->set('hasScheduleRows', $hasScheduleRows);
            $this->set('unscheduledEventsCount', $hasScheduleRows ? count($this->getCategoryOneScheduleExceptions($convSD)) : 0);
        }
        else
        {
            // `seasons` is legacy; use `conventionseasons` for active schema.
            $total_seasons = $this->Conventionseasons->find()->where(['1 = 1'])->count();
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

    public function unscheduledevents() {
        $this->set('title', ADMIN_TITLE . 'Unscheduled Events');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $seasonId = (int)$this->request->getSession()->read('sess_admin_header_season_id');
        if ($seasonId <= 0) {
            $this->Flash->error('Please select a convention season first.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.id' => $seasonId])
            ->contain(['Conventions'])
            ->first();
        if (!$convSD) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $this->set('convSD', $convSD);
        $scheduleExceptionItems = $this->getCategoryOneScheduleExceptions($convSD);
        $this->set('scheduleExceptionItems', $scheduleExceptionItems);
        $this->set('schedulingWarnings', $this->getLatestSchedulingWarnings($convSD, $scheduleExceptionItems));
    }

    private function getLatestSchedulingWarnings($convSD, array $scheduleExceptionItems): array {
        $warnings = [];
        $categoryOne = (array)$this->request->getSession()->read('Scheduling.runWarnings.category1');
        $ungrouped = (int)($categoryOne['ungrouped'] ?? 0);
        $skipped = (int)($categoryOne['skipped'] ?? 0);
        if ($ungrouped === 0 && $skipped === 0) {
            $ungrouped = count(array_filter($scheduleExceptionItems, function ($item) {
                return strpos((string)$item['reason'], 'Students not grouped') !== false;
            }));
            $skipped = count($scheduleExceptionItems);
        }
        if ($ungrouped > 0) {
            $warnings[] = 'Category 1 used Ungrouped fallback for '.$ungrouped.' registration/event entry(ies) where group name was missing.';
        }
        if ($skipped > 0) {
            $warnings[] = 'Category 1 skipped '.$skipped.' registration/event group entry(ies) because no matching event submission was found.';
        }

        $latestSchedule = $this->Schedulings->find()
            ->where(['Schedulings.conventionseasons_id' => $convSD->id])
            ->order(['Schedulings.modified' => 'DESC', 'Schedulings.id' => 'DESC'])
            ->first();
        if (!$latestSchedule || empty($latestSchedule->start_date)) {
            return $warnings;
        }

        $startDate = date('Y-m-d', strtotime((string)$latestSchedule->start_date));
        $numberOfDays = max(1, (int)($latestSchedule->number_of_days ?? 1));
        $endDate = date('Y-m-d', strtotime($startDate.' +'.($numberOfDays - 1).' day'));
        $categoryTwoWarnings = (array)$this->request->getSession()->read('Scheduling.runWarnings.category2');
        foreach ($categoryTwoWarnings as $eventNumber => $eventWarning) {
            $fallback = (int)($eventWarning['fallback'] ?? 0);
            $overflow = (int)($eventWarning['overflow'] ?? 0);
            if ($fallback > 0) {
                $warnings[] = 'Category 2 fallback placed '.$fallback.' remaining match(es) for event '.$eventNumber.'.';
            }
            if ($overflow > 0) {
                $warnings[] = 'Category 2 overflow placed '.$overflow.' remaining match(es) for event '.$eventNumber.' on days after the configured window.';
            }
        }

        $overflowCount = $this->Schedulingtimings->find()
            ->where([
                'Schedulingtimings.conventionseasons_id' => $convSD->id,
                'Schedulingtimings.schedule_category' => 2,
                'Schedulingtimings.sch_date_time >' => $endDate.' 23:59:59',
            ])
            ->count();
        if ($overflowCount > 0) {
            $warnings[] = 'Scheduling reached the configured convention window from '.date('D j M Y', strtotime($startDate)).' to '.date('D j M Y', strtotime($endDate)).'. Some items could not be placed inside the selected Number of Days.';
        }

        return array_values(array_unique($warnings));
    }

    private function getCategoryOneScheduleExceptions($convSD): array {
        $conventionRegistrations = $this->Conventionregistrations->find()
            ->select(['id', 'user_id'])
            ->where(['Conventionregistrations.conventionseason_id' => $convSD->id])
            ->all();
        $registrationSchoolIds = [];
        foreach ($conventionRegistrations as $conventionRegistration) {
            $registrationSchoolIds[(int)$conventionRegistration->id] = (int)$conventionRegistration->user_id;
        }

        $schoolNames = [];
        $schoolIds = array_values(array_unique(array_filter($registrationSchoolIds)));
        if (count($schoolIds) > 0) {
            $schools = $this->Users->find()->select(['id', 'first_name'])->where(['Users.id IN' => $schoolIds])->all();
            foreach ($schools as $school) {
                $schoolNames[(int)$school->id] = (string)$school->first_name;
            }
        }

        $categoryOneEvents = $this->Events->find()
            ->select(['id', 'event_id_number', 'event_name'])
            ->where([
                'Events.needs_schedule' => '1',
                'Events.group_event_yes_no' => '1',
                'Events.event_kind_id' => 'Sequential',
                'Events.has_to_be_consecutive' => '1',
            ])
            ->all();
        $eventDetails = [];
        foreach ($categoryOneEvents as $event) {
            $eventDetails[(int)$event->id] = $event;
        }
        if (count($eventDetails) === 0) {
            return [];
        }

        $registrationEvents = $this->Crstudentevents->find()
            ->select(['conventionregistration_id', 'event_id', 'group_name', 'student_id'])
            ->where([
                'Crstudentevents.conventionseason_id' => $convSD->id,
                'Crstudentevents.convention_id' => $convSD->convention_id,
                'Crstudentevents.event_id IN' => array_keys($eventDetails),
            ])
            ->all();
        $studentIds = [];
        foreach ($registrationEvents as $registrationEvent) {
            if ((int)$registrationEvent->student_id > 0) {
                $studentIds[] = (int)$registrationEvent->student_id;
            }
        }
        $studentNames = [];
        if (count($studentIds) > 0) {
            $students = $this->Users->find()
                ->select(['id', 'first_name', 'last_name'])
                ->where(['Users.id IN' => array_values(array_unique($studentIds))])
                ->all();
            foreach ($students as $student) {
                $studentNames[(int)$student->id] = trim((string)($student->first_name . ' ' . $student->last_name));
            }
        }

        $exceptions = [];
        $exceptionIndexes = [];
        foreach ($registrationEvents as $registrationEvent) {
            $registrationId = (int)$registrationEvent->conventionregistration_id;
            $eventId = (int)$registrationEvent->event_id;
            $schoolId = $registrationSchoolIds[$registrationId] ?? 0;
            $event = $eventDetails[$eventId] ?? null;
            if ($schoolId <= 0 || !$event) {
                continue;
            }

            $groupName = trim((string)$registrationEvent->group_name);
            $exceptionKey = $registrationId . '|' . $eventId . '|' . ($groupName === '' ? 'Ungrouped' : $groupName);
            $studentName = $studentNames[(int)$registrationEvent->student_id] ?? '';

            $submissionConditions = [
                'Eventsubmissions.conventionregistration_id' => $registrationId,
                'Eventsubmissions.conventionseason_id' => $convSD->id,
                'Eventsubmissions.convention_id' => $convSD->convention_id,
                'Eventsubmissions.season_id' => $convSD->season_id,
                'Eventsubmissions.season_year' => $convSD->season_year,
                'Eventsubmissions.event_id' => $eventId,
                'Eventsubmissions.user_id' => $schoolId,
            ];
            if ($groupName === '') {
                $submissionConditions[] = "(Eventsubmissions.group_name = 'Ungrouped' OR Eventsubmissions.group_name IS NULL OR TRIM(Eventsubmissions.group_name) = '')";
            } else {
                $submissionConditions['Eventsubmissions.group_name'] = $groupName;
            }

            $hasMatchingSubmission = $this->Eventsubmissions->find()->where($submissionConditions)->count() > 0;
            if ($groupName === '' || !$hasMatchingSubmission) {
                if (isset($exceptionIndexes[$exceptionKey])) {
                    if ($studentName !== '' && !in_array($studentName, $exceptions[$exceptionIndexes[$exceptionKey]]['students'], true)) {
                        $exceptions[$exceptionIndexes[$exceptionKey]]['students'][] = $studentName;
                    }
                    continue;
                }

                $reasons = [];
                if ($groupName === '') {
                    $reasons[] = 'Students not grouped';
                }
                if (!$hasMatchingSubmission) {
                    $reasons[] = 'Missing matching upload';
                }
                $exceptions[] = [
                    'school' => $schoolNames[$schoolId] ?? 'Unassigned school',
                    'event' => trim((string)($event->event_id_number . ' ' . $event->event_name)),
                    'participant' => $groupName !== '' ? 'Group ' . $groupName : 'Not grouped',
                    'reason' => implode('; ', $reasons),
                    'students' => $studentName !== '' ? [$studentName] : [],
                ];
                $exceptionIndexes[$exceptionKey] = count($exceptions) - 1;
            }
        }

        usort($exceptions, function ($left, $right) {
            return [$left['school'], $left['event'], $left['participant']] <=> [$right['school'], $right['event'], $right['participant']];
        });
        return $exceptions;
    }

    public function squad247() {
        $this->set('title', ADMIN_TITLE . '24/7 Squad Information');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $contentFile = CONFIG . 'squad247_content.json';
        $defaultData = [
            'convention_subtitle' => 'Regional Student Conventions 2025',
            'application_intro' => 'Southern Cross Educational Enterprises is seeking applications from A.C.E. Graduates and supporters to join the 2025 Regional Convention 24/7 Squads in the following regions:',
            'success_requirements' => [
                'Be at least 17 years of age (if under 18 and staying on site, events team will contact parent/guardian).',
                'Will not be eligible to compete as a student at the Convention.',
                'Will not have responsibility as a sponsor or accompanist for children or students attending Convention.',
                'Will be willing to volunteer their time 24/7 during Convention in any area (judging, stage handling, sound/audio, general help).',
                'Be able to arrive prior to check-in and remain until after awards and campsite clean-up.',
                'Be flexible in sleeping arrangements.',
                'Be passionate about the purpose and ministry of Student Conventions.',
            ],
            'successful_need_to' => [
                'Cover their own transport expenses and registration fee listed on the application form.',
                'Registration fee for PNG, Fiji, Cook Islands, Solomon Islands and AUS workshop includes convention registration, food, 24/7 pin and 24/7 T-shirt.',
                'Registration fee for Indonesia and NZ includes convention registration, onsite accommodation and food, and 24/7 T-shirt.',
                'If travelling internationally, check with Events Coordinator for extra food, accommodation and transport cost estimates.',
                'Be prepared to use any musical or platform gifts at evening rallies.',
            ],
            'important_note' => 'Regional Squad experience is required (where possible) for South Pacific Squad applicants.',
            'applications_email' => 'events@scee.edu.au',
            'applications_deadline' => 'ALL APPLICATIONS MUST BE RECEIVED TWO MONTHS PRIOR to the commencement of the Convention.',
            'page2_title' => '24/7 Squad Regional Application Form 2025 (Page 2)',
            'page2_description' => 'The application form includes personal details, country selection, convention experience, references, testimony, and declaration sections.',
            'applicant_must_provide' => [
                'A current portrait photo.',
                'A reference from Principal and/or Pastor.',
                'Personal testimony of salvation and current walk with the Lord.',
                'A description of church background and beliefs.',
            ],
            'blue_card_requirement' => 'For Australian/NZ conventions, Australian volunteers at Australian conventions must hold an approved Blue Card (or the relevant state Working With Children approval). Applicants can apply via bluecard.qld.gov.au.',
            'payment_options' => 'Once the application is received, specific payment details are sent based on the country selected.',
            'regions_left' => [
                ['name' => 'FIJI', 'dates' => '30 June - 4 July'],
                ['name' => 'NZ', 'dates' => '1 - 5 September'],
            ],
            'regions_right' => [
                ['name' => 'PNG', 'dates' => '8 - 12 September'],
                ['name' => 'AUS Workshop', 'dates' => '10 - 12 June'],
                ['name' => 'INDO', 'dates' => '13 - 17 October'],
            ],
            'fees' => [
                ['name' => 'Fiji', 'amount' => '100 FJD'],
                ['name' => 'AUS', 'amount' => '100 AUD'],
                ['name' => 'NZ', 'amount' => '200 NZD'],
                ['name' => 'PNG', 'amount' => '225 Kina'],
                ['name' => 'INDO', 'amount' => '1,500,000 Rp'],
            ],
        ];

        $squad247Data = $defaultData;
        if (is_file($contentFile)) {
            $decoded = json_decode((string)@file_get_contents($contentFile), true);
            if (is_array($decoded)) {
                if (isset($decoded['convention_subtitle'])) {
                    $squad247Data['convention_subtitle'] = trim((string)$decoded['convention_subtitle']);
                }
                foreach (['application_intro','important_note','applications_email','applications_deadline','page2_title','page2_description','blue_card_requirement','payment_options'] as $textKey) {
                    if (isset($decoded[$textKey])) {
                        $squad247Data[$textKey] = trim((string)$decoded[$textKey]);
                    }
                }
                foreach (['success_requirements','successful_need_to','applicant_must_provide'] as $listKey) {
                    if (isset($decoded[$listKey]) && is_array($decoded[$listKey])) {
                        $squad247Data[$listKey] = $decoded[$listKey];
                    }
                }
                if (isset($decoded['regions_left']) && is_array($decoded['regions_left'])) {
                    $squad247Data['regions_left'] = $decoded['regions_left'];
                }
                if (isset($decoded['regions_right']) && is_array($decoded['regions_right'])) {
                    $squad247Data['regions_right'] = $decoded['regions_right'];
                }
                if (isset($decoded['fees']) && is_array($decoded['fees'])) {
                    $squad247Data['fees'] = $decoded['fees'];
                }
            }
        }

        if ($this->request->is('post')) {
            $formMode = trim((string)$this->request->getData('s247_mode'));
            if ($formMode === 'manual_add') {
                $manual = (array)$this->request->getData('Manual247', []);

                $toText = static function ($value) {
                    return trim((string)$value);
                };

                $fullName = $toText($manual['full_name'] ?? '');
                $age = $toText($manual['age'] ?? '');
                $gender = $toText($manual['gender'] ?? '');
                $country = $toText($manual['country'] ?? '');
                $phone = $toText($manual['phone'] ?? '');
                $email = $toText($manual['email'] ?? '');
                $schoolHssp = $toText($manual['school_hssp'] ?? '');

                $conventions = isset($manual['conventions']) && is_array($manual['conventions']) ? $manual['conventions'] : [];
                $conventions = array_values(array_filter(array_map(function ($item) use ($toText) {
                    return $toText($item);
                }, $conventions), function ($item) {
                    return $item !== '';
                }));
                $conventionsText = implode(', ', $conventions);

                $reasonsToAttend = $toText($manual['reasons_to_attend'] ?? '');
                $conventionExperience = $toText($manual['convention_experience'] ?? '');

                $hasBlueCard = !empty($manual['has_blue_card']);
                $blueCardNumber = $toText($manual['blue_card_number'] ?? '');
                $blueCardExpiryDate = $toText($manual['blue_card_expiry_date'] ?? '');
                $blueCardApplicationProvided = $toText($manual['blue_card_application_provided'] ?? '');

                $serviceHistory = $toText($manual['service_history'] ?? 'new');
                $servedYear = $toText($manual['served_year'] ?? '');
                $currentTestimony = $toText($manual['current_testimony'] ?? '');
                $salvationTestimony = $toText($manual['salvation_testimony'] ?? '');
                $churchBackground = $toText($manual['church_background'] ?? '');
                $portraitPhotoProvided = $toText($manual['portrait_photo_provided'] ?? '');
                $principalPastorReferenceProvided = $toText($manual['principal_pastor_reference_provided'] ?? '');

                $hasDietaryRequirements = !empty($manual['has_dietary_requirements']);
                $dietaryRequirements = $toText($manual['dietary_requirements'] ?? '');

                $applicantSignatureName = $toText($manual['applicant_signature_name'] ?? '');
                $applicantSignatureDate = $toText($manual['applicant_signature_date'] ?? '');
                $parentGuardianName = $toText($manual['parent_guardian_name'] ?? '');
                $parentGuardianConfirmation = $toText($manual['parent_guardian_confirmation'] ?? '');
                $parentGuardianDate = $toText($manual['parent_guardian_date'] ?? '');

                $position = $toText($manual['position'] ?? '');
                $notes = $toText($manual['notes'] ?? '');
                $submittedAt = trim((string)($manual['submitted_at'] ?? ''));

                if ($fullName === '' || empty($conventions)) {
                    $this->Flash->error('Please provide at least Full Name and one Convention selection for manual entry.');
                } else {
                    $rawFields = [
                        ['label' => 'Full Name', 'value' => $fullName],
                        ['label' => 'Age', 'value' => $age],
                        ['label' => 'Gender', 'value' => $gender],
                        ['label' => 'Country', 'value' => $country],
                        ['label' => 'Phone', 'value' => $phone],
                        ['label' => 'Email', 'value' => $email],
                        ['label' => 'A.C.E. School / HSSP you attend(ed) as a student', 'value' => $schoolHssp],
                        ['label' => 'Convention(s) Applying For', 'value' => $conventionsText],
                        ['label' => 'Please state your reasons for wanting to attend', 'value' => $reasonsToAttend],
                        ['label' => 'Convention experience and items you are willing to perform', 'value' => $conventionExperience],
                        ['label' => 'I have a current Blue Card', 'value' => $hasBlueCard ? 'Yes' : 'No'],
                        ['label' => 'Blue Card Number', 'value' => $blueCardNumber],
                        ['label' => 'Blue Card Expiry Date', 'value' => $blueCardExpiryDate],
                        ['label' => 'Blue Card application form provided', 'value' => $blueCardApplicationProvided],
                        ['label' => 'Squad Service History', 'value' => $serviceHistory === 'served' ? 'I have served as a 24/7 Squad Member before' : 'I have not previously served as a 24/7 Squad Member'],
                        ['label' => 'If you have served before, what year?', 'value' => $servedYear],
                        ['label' => 'A current portrait photo of yourself provided', 'value' => $portraitPhotoProvided],
                        ['label' => 'A reference from your Principal and/or Pastor provided', 'value' => $principalPastorReferenceProvided],
                        ['label' => 'Your personal testimony of salvation', 'value' => $salvationTestimony],
                        ['label' => 'A description of your church background and beliefs', 'value' => $churchBackground],
                        ['label' => 'A current testimony of your walk with the Lord', 'value' => $currentTestimony],
                        ['label' => 'I have special dietary requirements or allergies', 'value' => $hasDietaryRequirements ? 'Yes' : 'No'],
                        ['label' => 'Please state your requirements', 'value' => $dietaryRequirements],
                        ['label' => "Applicant's Full Name (acts as your signature/confirmation)", 'value' => $applicantSignatureName],
                        ['label' => 'Declaration Date', 'value' => $applicantSignatureDate],
                        ['label' => 'Parent/Guardian Name', 'value' => $parentGuardianName],
                        ['label' => 'Parent/Guardian Confirmation (acts as signature)', 'value' => $parentGuardianConfirmation],
                        ['label' => 'Parent/Guardian Date', 'value' => $parentGuardianDate],
                        ['label' => 'Position', 'value' => $position],
                        ['label' => 'Admin Notes', 'value' => $notes],
                    ];

                    $fields = [];
                    foreach ($rawFields as $row) {
                        $label = trim((string)($row['label'] ?? ''));
                        $value = trim((string)($row['value'] ?? ''));
                        if ($label === '' || $value === '') {
                            continue;
                        }
                        $fields[] = ['label' => $label, 'value' => $value];
                    }

                    $payload = [
                        'fields' => $fields,
                        'files' => [],
                        'manual_entry' => true,
                    ];

                    $parsedSubmittedAt = $submittedAt !== '' ? strtotime($submittedAt) : false;
                    if ($parsedSubmittedAt === false) {
                        $submittedAt = date('Y-m-d H:i:s');
                    } else {
                        $submittedAt = date('Y-m-d H:i:s', $parsedSubmittedAt);
                    }

                    $submission = [
                        'submitted_at' => $submittedAt,
                        'ip_address' => (string)$this->request->clientIp(),
                        'payload' => $payload,
                    ];

                    $submissions = $this->loadSquad247Submissions();
                    array_unshift($submissions, $submission);

                    if ($this->saveSquad247Submissions($submissions)) {
                        $this->Flash->success('Manual 24/7 application added successfully.');
                        return $this->redirect(['action' => 'squad247']);
                    }

                    $this->Flash->error('Unable to save manual 24/7 application right now. Please try again.');
                }
            }

            $posted = (array)$this->request->getData('Squad247', []);

            $normalizeRows = static function (array $rows, $firstKey, $secondKey) {
                $normalized = [];
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $firstVal = trim((string)($row[$firstKey] ?? ''));
                    $secondVal = trim((string)($row[$secondKey] ?? ''));
                    if ($firstVal === '' && $secondVal === '') {
                        continue;
                    }
                    $normalized[] = [$firstKey => $firstVal, $secondKey => $secondVal];
                }
                return $normalized;
            };

            $normalizeLines = static function ($value) {
                $text = trim((string)$value);
                if ($text === '') {
                    return [];
                }

                $lines = preg_split('/\r\n|\r|\n/', $text);
                $normalized = [];
                foreach ($lines as $line) {
                    $line = trim((string)$line);
                    $line = preg_replace('/^[\-*\x{2022}\d]+[\.)\-\s]+/u', '', $line);
                    $line = trim((string)$line);
                    if ($line !== '') {
                        $normalized[] = $line;
                    }
                }
                return $normalized;
            };

            $nextData = [
                'convention_subtitle' => trim((string)($posted['convention_subtitle'] ?? '')),
                'application_intro' => trim((string)($posted['application_intro'] ?? '')),
                'success_requirements' => $normalizeLines($posted['success_requirements'] ?? ''),
                'successful_need_to' => $normalizeLines($posted['successful_need_to'] ?? ''),
                'important_note' => trim((string)($posted['important_note'] ?? '')),
                'applications_email' => trim((string)($posted['applications_email'] ?? '')),
                'applications_deadline' => trim((string)($posted['applications_deadline'] ?? '')),
                'page2_title' => trim((string)($posted['page2_title'] ?? '')),
                'page2_description' => trim((string)($posted['page2_description'] ?? '')),
                'applicant_must_provide' => $normalizeLines($posted['applicant_must_provide'] ?? ''),
                'blue_card_requirement' => trim((string)($posted['blue_card_requirement'] ?? '')),
                'payment_options' => trim((string)($posted['payment_options'] ?? '')),
                'regions_left' => $normalizeRows(isset($posted['regions_left']) && is_array($posted['regions_left']) ? $posted['regions_left'] : [], 'name', 'dates'),
                'regions_right' => $normalizeRows(isset($posted['regions_right']) && is_array($posted['regions_right']) ? $posted['regions_right'] : [], 'name', 'dates'),
                'fees' => $normalizeRows(isset($posted['fees']) && is_array($posted['fees']) ? $posted['fees'] : [], 'name', 'amount'),
            ];

            if ($nextData['convention_subtitle'] === '') {
                $nextData['convention_subtitle'] = $defaultData['convention_subtitle'];
            }
            foreach (['application_intro','important_note','applications_email','applications_deadline','page2_title','page2_description','blue_card_requirement','payment_options'] as $textKey) {
                if ($nextData[$textKey] === '') {
                    $nextData[$textKey] = $defaultData[$textKey];
                }
            }
            foreach (['success_requirements','successful_need_to','applicant_must_provide'] as $listKey) {
                if (empty($nextData[$listKey])) {
                    $nextData[$listKey] = $defaultData[$listKey];
                }
            }

            if (empty($nextData['regions_left']) || empty($nextData['regions_right']) || empty($nextData['fees'])) {
                $this->Flash->error('Please provide at least one row for each section before saving.');
                $squad247Data = $nextData;
            } else {
                @file_put_contents($contentFile, json_encode($nextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                $this->Flash->success('24/7 Squad information updated successfully.');
                return $this->redirect(['action' => 'squad247']);
            }
        }

        $allSquad247Submissions = $this->loadSquad247Submissions();
        $filteredSquad247Submissions = $allSquad247Submissions;

        $selectedSquad247ConventionName = '';
        $selectedSquad247Aliases = array();
        $selectedSquad247SeasonDateRange = '';
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        if (!empty($sess_admin_header_season_id)) {
            $selectedConvSeason = $this->Conventionseasons->find()
                ->where(["Conventionseasons.id" => $sess_admin_header_season_id])
                ->contain(['Conventions'])
                ->first();
            if ($selectedConvSeason && !empty($selectedConvSeason->Conventions['name'])) {
                $selectedSquad247ConventionName = trim((string)$selectedConvSeason->Conventions['name']);
                $selectedSquad247Aliases = $this->resolveSquad247ConventionAliases($selectedSquad247ConventionName);

                $startDateText = '';
                $endDateText = '';
                if (!empty($selectedConvSeason->registration_start_date)) {
                    $startDateText = date('j M Y', strtotime((string)$selectedConvSeason->registration_start_date));
                }
                if (!empty($selectedConvSeason->registration_end_date)) {
                    $endDateText = date('j M Y', strtotime((string)$selectedConvSeason->registration_end_date));
                }
                if ($startDateText !== '' && $endDateText !== '') {
                    $selectedSquad247SeasonDateRange = $startDateText . ' - ' . $endDateText;
                } elseif ($startDateText !== '') {
                    $selectedSquad247SeasonDateRange = $startDateText;
                } elseif ($endDateText !== '') {
                    $selectedSquad247SeasonDateRange = $endDateText;
                }

                if ($selectedSquad247ConventionName !== '') {
                    $filteredSquad247Submissions = $this->filterSquad247SubmissionsForConvention($allSquad247Submissions, $selectedSquad247ConventionName);
                }
            }
        }

        $this->set('selectedSquad247ConventionName', $selectedSquad247ConventionName);
        $this->set('selectedSquad247Aliases', $selectedSquad247Aliases);
        $this->set('selectedSquad247SeasonDateRange', $selectedSquad247SeasonDateRange);
        $this->set('squad247Data', $squad247Data);
        $this->set('squad247Submissions', $filteredSquad247Submissions);
    }

    protected function squad247SubmissionsFile() {
        return TMP . 'squad247_submissions.json';
    }

    protected function loadSquad247Submissions() {
        $submissionsFile = $this->squad247SubmissionsFile();
        if (!is_file($submissionsFile)) {
            return array();
        }

        $decoded = json_decode((string)@file_get_contents($submissionsFile), true);
        return is_array($decoded) ? $decoded : array();
    }

    protected function decodeSquad247Payload($payload) {
        if (is_array($payload) && count($payload) === 1 && isset($payload[0]) && is_string($payload[0])) {
            $decodedPayload = json_decode($payload[0], true);
            if (is_array($decodedPayload)) {
                return $decodedPayload;
            }
        }

        if (is_string($payload)) {
            $decodedPayload = json_decode($payload, true);
            if (is_array($decodedPayload)) {
                return $decodedPayload;
            }
        }

        return is_array($payload) ? $payload : array();
    }

    protected function countSquad247SubmissionsForConvention(array $submissions, $selectedConventionName) {
        $selectedAliases = $this->resolveSquad247ConventionAliases((string)$selectedConventionName);
        if (empty($selectedAliases)) {
            return count($submissions);
        }

        $count = 0;
        foreach ($submissions as $submission) {
            $payload = $this->decodeSquad247Payload($submission['payload'] ?? array());
            $searchText = $this->extractSquad247SubmissionSearchText($payload);
            if ($searchText === '') {
                continue;
            }

            foreach ($selectedAliases as $alias) {
                if ($alias !== '' && strpos($searchText, $alias) !== false) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    protected function filterSquad247SubmissionsForConvention(array $submissions, $selectedConventionName) {
        $selectedAliases = $this->resolveSquad247ConventionAliases((string)$selectedConventionName);
        if (empty($selectedAliases)) {
            return $submissions;
        }

        $filtered = array();
        foreach ($submissions as $index => $submission) {
            $payload = $this->decodeSquad247Payload($submission['payload'] ?? array());
            $searchText = $this->extractSquad247SubmissionSearchText($payload);
            if ($searchText === '') {
                continue;
            }

            foreach ($selectedAliases as $alias) {
                if ($alias !== '' && strpos($searchText, $alias) !== false) {
                    $filtered[$index] = $submission;
                    break;
                }
            }
        }

        return $filtered;
    }

    protected function extractSquad247SubmissionSearchText(array $payload) {
        $parts = array();

        if (!empty($payload['fields']) && is_array($payload['fields'])) {
            foreach ($payload['fields'] as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $label = strtolower(trim((string)($field['label'] ?? '')));
                $value = strtolower(trim((string)($field['value'] ?? '')));
                if ($value === '') {
                    continue;
                }

                if (strpos($label, 'country') !== false || strpos($label, 'convention') !== false || strpos($label, 'region') !== false) {
                    $parts[] = $value;
                    continue;
                }

                if (in_array($value, ['indo', 'png', 'nz', 'aus', 'ck', 'slb', 'fiji'], true)) {
                    $parts[] = $value;
                }
            }
        }

        return implode(' ', $parts);
    }

    protected function resolveSquad247ConventionAliases($conventionName) {
        $name = strtolower(trim((string)$conventionName));
        if ($name === '') {
            return array();
        }

        $aliasMap = [
            'indonesia' => ['indonesia', 'indo'],
            'papua new guinea' => ['papua new guinea', 'png'],
            'new zealand' => ['new zealand', 'nz'],
            'australia' => ['australia', 'aus'],
            'fiji' => ['fiji'],
            'cook islands' => ['cook islands', 'cook', 'ck'],
            'solomon islands' => ['solomon islands', 'solomon', 'slb'],
        ];

        foreach ($aliasMap as $needle => $aliases) {
            if (strpos($name, $needle) !== false) {
                return $aliases;
            }
        }

        return array($name);
    }

    protected function saveSquad247Submissions(array $submissions) {
        return @file_put_contents(
            $this->squad247SubmissionsFile(),
            json_encode(array_values($submissions), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ) !== false;
    }

    public function squad247DeleteSubmission($index = null) {
        $this->request->allowMethod(['post', 'delete']);

        if (!is_numeric($index)) {
            $this->Flash->error('Invalid submission selected for deletion.');
            return $this->redirect(['action' => 'squad247']);
        }

        $submissionIndex = (int)$index;
        $submissions = $this->loadSquad247Submissions();

        if (!isset($submissions[$submissionIndex])) {
            $this->Flash->error('Submission not found. It may have already been deleted.');
            return $this->redirect(['action' => 'squad247']);
        }

        $submission = $submissions[$submissionIndex];
        $payload = $this->decodeSquad247Payload(isset($submission['payload']) ? $submission['payload'] : array());

        $uploadedFiles = isset($payload['files']) && is_array($payload['files']) ? $payload['files'] : array();
        $uploadsDir = WWW_ROOT . 'uploads' . DS . 'squad247' . DS;
        foreach ($uploadedFiles as $fileInfo) {
            $storedName = '';
            if (is_array($fileInfo) && !empty($fileInfo['stored_name'])) {
                $storedName = basename((string)$fileInfo['stored_name']);
            } elseif (is_array($fileInfo) && !empty($fileInfo['url'])) {
                $storedName = basename(parse_url((string)$fileInfo['url'], PHP_URL_PATH));
            }

            if ($storedName !== '') {
                $filePath = $uploadsDir . $storedName;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        unset($submissions[$submissionIndex]);

        if ($this->saveSquad247Submissions($submissions)) {
            $this->Flash->success('24/7 submission deleted successfully.');
        } else {
            $this->Flash->error('Unable to delete the submission right now. Please try again.');
        }

        return $this->redirect(['action' => 'squad247']);
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

        $settingsColumns = (array)$this->Settings->getSchema()->columns();
        $hasVideoLinksJson = in_array('video_links_json', $settingsColumns, true);
        $videoLinksFile = WWW_ROOT . 'files' . DS . 'dashboard_video_links.json';

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
        if (is_file($videoLinksFile)) {
            $fileVideoLinks = json_decode((string)@file_get_contents($videoLinksFile), true);
            if (is_array($fileVideoLinks)) {
                $videoLinks = array_values(array_filter(array_map('trim', $fileVideoLinks), static function ($value) {
                    return $value !== '';
                }));
            }
        }

        if (empty($videoLinks) && $hasVideoLinksJson && !empty($settingsInfo->video_links_json)) {
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

            $fields = [];
            if ($hasVideoLinksJson) {
                $fields['video_links_json'] = json_encode($submittedVideoLinks, JSON_UNESCAPED_SLASHES);
            }

            for ($i = 1; $i <= 9; $i++) {
                $fieldName = 'video_link_' . $i;
                if (in_array($fieldName, $settingsColumns, true)) {
                    $fields[$fieldName] = isset($submittedVideoLinks[$i - 1]) ? $submittedVideoLinks[$i - 1] : null;
                }
            }

            $fields['modified'] = date('Y-m-d H:i:s');
            $this->Settings->updateAll($fields, ['Settings.id' => 1]);

            @file_put_contents($videoLinksFile, json_encode($submittedVideoLinks, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

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

    /**
     * Convention Season Report
     * Shows statistics for a selected convention season
     */
    public function report() {
        $this->viewBuilder()->setLayout('admin');
        $this->set('title', ADMIN_TITLE . 'Convention Report');
        $this->set('report', '1');

        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        $this->set('sess_admin_header_season_id', $sess_admin_header_season_id);

        if ($sess_admin_header_season_id <= 0) {
            $this->Flash->error('Please select a convention season from the header.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSD = $this->Conventionseasons->find()
            ->where(["Conventionseasons.id" => $sess_admin_header_season_id])
            ->contain(['Conventions'])
            ->first();

        if (!$convSD) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $this->set('conv_season', $convSD);

        // Count registered schools
        $total_schools = 0;
        $listSchools = $this->Conventionregistrations->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->contain(['Users'])
            ->all();
        foreach ($listSchools as $school) {
            if (isset($school->Users) && $school->Users['user_type'] == "School") {
                $total_schools++;
            }
        }
        $this->set('total_schools', $total_schools);

        // Count registered students
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

        // Count supervisors
        $total_supervisors = $this->Conventionregistrationteachers->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->count();
        $this->set('total_supervisors', $total_supervisors);

        // Count judges
        $total_judges = 0;
        foreach ($listSchools as $school) {
            if (isset($school->Users) && 
                ($school->Users['user_type'] == "Judge" || $school->Users['user_type'] == "Teacher_Parent") && 
                $school->Users['is_judge'] == 1) {
                $total_judges++;
            }
        }
        $this->set('total_judges', $total_judges);

        // Count visitors
        try {
            $Visitors = $this->loadModel('Visitors');
            $total_visitors = $Visitors->find()
                ->where([
                    "convention_id" => $convSD->convention_id,
                    "season_id" => $convSD->season_id
                ])
                ->count();
        } catch (\Exception $e) {
            $total_visitors = 0;
        }
        $this->set('total_visitors', $total_visitors);

        // Get all students with their event counts
        $studentEventCounts = $this->Crstudentevents->find()
            ->select(['student_id'])
            ->where(["conventionseason_id" => $convSD->id])
            ->group(['student_id'])
            ->all();

        $students_20_events = 0;
        $students_11_15_events = 0;
        $students_5_10_events = 0;

        foreach ($studentEventCounts as $row) {
            $eventCount = $this->Crstudentevents->find()
                ->where([
                    "conventionseason_id" => $convSD->id,
                    "student_id" => $row->student_id
                ])
                ->select(['DISTINCT event_id'])
                ->count();

            if ($eventCount == 20) {
                $students_20_events++;
            } elseif ($eventCount >= 11 && $eventCount <= 15) {
                $students_11_15_events++;
            } elseif ($eventCount >= 5 && $eventCount <= 10) {
                $students_5_10_events++;
            }
        }

        $this->set('students_20_events', $students_20_events);
        $this->set('students_11_15_events', $students_11_15_events);
        $this->set('students_5_10_events', $students_5_10_events);

        // Count 1st, 2nd, 3rd place winners
        try {
            $Resultpositions = $this->loadModel('Resultpositions');
            
            // 1st place winners
            $first_place = $Resultpositions->find()
                ->where([
                    "position" => 1,
                    "convention_id" => $convSD->convention_id
                ])
                ->count();
            $this->set('first_place_winners', $first_place);

            // 2nd place winners
            $second_place = $Resultpositions->find()
                ->where([
                    "position" => 2,
                    "convention_id" => $convSD->convention_id
                ])
                ->count();
            $this->set('second_place_winners', $second_place);

            // 3rd place winners
            $third_place = $Resultpositions->find()
                ->where([
                    "position" => 3,
                    "convention_id" => $convSD->convention_id
                ])
                ->count();
            $this->set('third_place_winners', $third_place);
        } catch (\Exception $e) {
            $this->set('first_place_winners', 0);
            $this->set('second_place_winners', 0);
            $this->set('third_place_winners', 0);
        }

        // Award counts - Silver Apple Award (events 336, 342)
        $silver_apple = 0;
        $golden_awards = 0;
        
        $silver_apple_students = $this->Conventionregistrationstudents->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->all();
        
        foreach ($silver_apple_students as $reg) {
            $event_ids = !empty($reg->event_ids) ? explode(',', (string)$reg->event_ids) : [];
            if (in_array('336', $event_ids) || in_array('342', $event_ids)) {
                $silver_apple++;
            }
            
            $golden_event_ids = ['331', '337', '332', '338', '333', '339', '334', '340', '335', '341'];
            foreach ($event_ids as $eid) {
                if (in_array(trim((string)$eid), $golden_event_ids)) {
                    $golden_awards++;
                    break;
                }
            }
        }
        
        $this->set('silver_apple_count', $silver_apple);
        $this->set('golden_awards_count', $golden_awards);

        // Count total entries (event registrations)
        $total_entries = $this->Crstudentevents->find()
            ->where(["conventionseason_id" => $convSD->id])
            ->count();
        $this->set('total_entries', $total_entries);
    }

    public function downloadReport() {
        $sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");

        if ($sess_admin_header_season_id <= 0) {
            $this->Flash->error('Please select a convention season from the header.');
            return $this->redirect(['action' => 'dashboard']);
        }

        $convSD = $this->Conventionseasons->find()
            ->where(["Conventionseasons.id" => $sess_admin_header_season_id])
            ->contain(['Conventions'])
            ->first();

        if (!$convSD) {
            $this->Flash->error('Selected convention season was not found.');
            return $this->redirect(['action' => 'dashboard']);
        }

        // Gather all the report data (using same logic as report() method)
        
        // Count registered schools
        $total_schools = 0;
        $listSchools = $this->Conventionregistrations->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->contain(['Users'])
            ->all();
        foreach ($listSchools as $school) {
            if (isset($school->Users) && $school->Users['user_type'] == "School") {
                $total_schools++;
            }
        }

        // Count registered students
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

        // Count supervisors
        $total_supervisors = $this->Conventionregistrationteachers->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->count();

        // Count judges
        $total_judges = 0;
        foreach ($listSchools as $school) {
            if (isset($school->Users) && 
                ($school->Users['user_type'] == "Judge" || $school->Users['user_type'] == "Teacher_Parent") && 
                $school->Users['is_judge'] == 1) {
                $total_judges++;
            }
        }

        // Count visitors
        try {
            $Visitors = $this->loadModel('Visitors');
            $total_visitors = $Visitors->find()
                ->where([
                    "convention_id" => $convSD->convention_id,
                    "season_id" => $convSD->season_id
                ])
                ->count();
        } catch (\Exception $e) {
            $total_visitors = 0;
        }

        // Get student event distribution
        $studentEventCounts = $this->Crstudentevents->find()
            ->select(['student_id'])
            ->where(["conventionseason_id" => $convSD->id])
            ->group(['student_id'])
            ->all();

        $students_20_events = 0;
        $students_11_15_events = 0;
        $students_5_10_events = 0;

        foreach ($studentEventCounts as $row) {
            $eventCount = $this->Crstudentevents->find()
                ->where([
                    "conventionseason_id" => $convSD->id,
                    "student_id" => $row->student_id
                ])
                ->select(['DISTINCT event_id'])
                ->count();

            if ($eventCount == 20) {
                $students_20_events++;
            } elseif ($eventCount >= 11 && $eventCount <= 15) {
                $students_11_15_events++;
            } elseif ($eventCount >= 5 && $eventCount <= 10) {
                $students_5_10_events++;
            }
        }

        // Count place winners
        try {
            $Resultpositions = $this->loadModel('Resultpositions');
            
            $first_place = $Resultpositions->find()
                ->where(["position" => 1, "convention_id" => $convSD->convention_id])
                ->count();
            $second_place = $Resultpositions->find()
                ->where(["position" => 2, "convention_id" => $convSD->convention_id])
                ->count();
            $third_place = $Resultpositions->find()
                ->where(["position" => 3, "convention_id" => $convSD->convention_id])
                ->count();
        } catch (\Exception $e) {
            $first_place = 0;
            $second_place = 0;
            $third_place = 0;
        }

        // Award counts
        $silver_apple = 0;
        $golden_awards = 0;
        
        $silver_apple_students = $this->Conventionregistrationstudents->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->all();
        
        foreach ($silver_apple_students as $reg) {
            $event_ids = !empty($reg->event_ids) ? explode(',', (string)$reg->event_ids) : [];
            if (in_array('336', $event_ids) || in_array('342', $event_ids)) {
                $silver_apple++;
            }
            
            $golden_event_ids = ['331', '337', '332', '338', '333', '339', '334', '340', '335', '341'];
            foreach ($event_ids as $eid) {
                if (in_array(trim((string)$eid), $golden_event_ids)) {
                    $golden_awards++;
                    break;
                }
            }
        }

        // Count total entries
        $total_entries = $this->Crstudentevents->find()
            ->where(["conventionseason_id" => $convSD->id])
            ->count();

        // Generate PDF with professional grayscale design
        $pdf = $this->_generateProfessionalReportPDF($convSD, $first_place, $second_place, $third_place, 
            $total_schools, $total_students, $total_supervisors, $total_judges, $total_visitors,
            $students_20_events, $students_11_15_events, $students_5_10_events,
            $silver_apple, $golden_awards, $total_entries);

        // Output PDF for download
        $filename = 'convention_report_' . date('Y-m-d_His') . '.pdf';
        $pdf->Output($filename, 'D');
        die();
    }

    public function previewReport()
    {
        // Check session
        if (!$this->request->getSession()->read('admin_id')) {
            return $this->redirect(['controller' => 'admins', 'action' => 'login']);
        }

        // Get season from session
        $season_id = $this->request->getSession()->read('sess_admin_header_season_id');
        if (!$season_id) {
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }

        // Load required models
        $this->loadModel('Conventionseasons');
        $this->loadModel('Conventions');
        $this->loadModel('Conventionregistrations');
        $this->loadModel('Conventionregistrationstudents');
        $this->loadModel('Conventionregistrationteachers');
        $this->loadModel('Crstudentevents');

        // Get convention season data
        $convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" => $season_id])->contain(['Conventions'])->first();
        if (!$convSD) {
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }

        // Get registrations
        $listSchools = $this->Conventionregistrations->find()
            ->where([
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year,
                "convention_id" => $convSD->convention_id
            ])
            ->contain(['Users'])
            ->all();

        // Count schools
        $total_schools = count($listSchools);

        // Count students
        $total_students = $this->Conventionregistrationstudents->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->count();

        // Count supervisors
        $total_supervisors = $this->Conventionregistrationteachers->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->count();

        // Count judges
        $total_judges = 0;
        foreach ($listSchools as $school) {
            if (isset($school->Users) && 
                ($school->Users['user_type'] == "Judge" || $school->Users['user_type'] == "Teacher_Parent") && 
                $school->Users['is_judge'] == 1) {
                $total_judges++;
            }
        }

        // Count visitors
        try {
            $Visitors = $this->loadModel('Visitors');
            $total_visitors = $Visitors->find()
                ->where([
                    "convention_id" => $convSD->convention_id,
                    "season_id" => $convSD->season_id
                ])
                ->count();
        } catch (\Exception $e) {
            $total_visitors = 0;
        }

        // Get student event distribution
        $studentEventCounts = $this->Crstudentevents->find()
            ->select(['student_id'])
            ->where(["conventionseason_id" => $convSD->id])
            ->group(['student_id'])
            ->all();

        $students_20_events = 0;
        $students_11_15_events = 0;
        $students_5_10_events = 0;

        foreach ($studentEventCounts as $row) {
            $eventCount = $this->Crstudentevents->find()
                ->where([
                    "conventionseason_id" => $convSD->id,
                    "student_id" => $row->student_id
                ])
                ->select(['DISTINCT event_id'])
                ->count();

            if ($eventCount == 20) {
                $students_20_events++;
            } elseif ($eventCount >= 11 && $eventCount <= 15) {
                $students_11_15_events++;
            } elseif ($eventCount >= 5 && $eventCount <= 10) {
                $students_5_10_events++;
            }
        }

        // Count place winners
        try {
            $Resultpositions = $this->loadModel('Resultpositions');
            
            $first_place = $Resultpositions->find()
                ->where(["position" => 1, "convention_id" => $convSD->convention_id])
                ->count();
            $second_place = $Resultpositions->find()
                ->where(["position" => 2, "convention_id" => $convSD->convention_id])
                ->count();
            $third_place = $Resultpositions->find()
                ->where(["position" => 3, "convention_id" => $convSD->convention_id])
                ->count();
        } catch (\Exception $e) {
            $first_place = 0;
            $second_place = 0;
            $third_place = 0;
        }

        // Award counts
        $silver_apple = 0;
        $golden_awards = 0;
        
        $silver_apple_students = $this->Conventionregistrationstudents->find()
            ->where([
                "convention_id" => $convSD->convention_id,
                "season_id" => $convSD->season_id,
                "season_year" => $convSD->season_year
            ])
            ->all();
        
        foreach ($silver_apple_students as $reg) {
            $event_ids = !empty($reg->event_ids) ? explode(',', (string)$reg->event_ids) : [];
            if (in_array('336', $event_ids) || in_array('342', $event_ids)) {
                $silver_apple++;
            }
            
            $golden_event_ids = ['331', '337', '332', '338', '333', '339', '334', '340', '335', '341'];
            foreach ($event_ids as $eid) {
                if (in_array(trim((string)$eid), $golden_event_ids)) {
                    $golden_awards++;
                    break;
                }
            }
        }

        // Count total entries
        $total_entries = $this->Crstudentevents->find()
            ->where(["conventionseason_id" => $convSD->id])
            ->count();

        // Generate PDF with professional grayscale design
        $pdf = $this->_generateProfessionalReportPDF($convSD, $first_place, $second_place, $third_place, 
            $total_schools, $total_students, $total_supervisors, $total_judges, $total_visitors,
            $students_20_events, $students_11_15_events, $students_5_10_events,
            $silver_apple, $golden_awards, $total_entries);

        // Output PDF inline for preview
        $response = $this->response->withType('application/pdf');
        $response = $response->withStringBody($pdf->Output('', 'S'));
        return $response;
    }

    private function _generateProfessionalReportPDF($convSD, $first_place, $second_place, $third_place, 
        $total_schools, $total_students, $total_supervisors, $total_judges, $total_visitors,
        $students_20_events, $students_11_15_events, $students_5_10_events,
        $silver_apple, $golden_awards, $total_entries)
    {
        $pdf = new \TCPDF();
        $pdf->SetCreator('ACP Live');
        $pdf->SetAuthor('Convention Management System');
        $pdf->SetTitle('Convention Report');
        $pdf->SetDefaultMonospacedFont(\PDF_FONT_MONOSPACED);
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        // Professional header
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(40, 40, 40);
        $pdf->Cell(0, 12, 'CONVENTION REPORT', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 8, h($convSD->Conventions['name']) . ' ' . $convSD->season_year, 0, 1, 'C');
        $pdf->Ln(3);

        // Detailed Sections
        $sections = [
            [
                'title' => 'PLACE WINNERS',
                'data' => [
                    ['1st Place Winners', $first_place],
                    ['2nd Place Winners', $second_place],
                    ['3rd Place Winners', $third_place],
                ]
            ],
            [
                'title' => 'CONVENTION REGISTRATIONS',
                'data' => [
                    ['Schools', $total_schools],
                    ['Students', $total_students],
                    ['Supervisors', $total_supervisors],
                    ['Judges', $total_judges],
                    ['Visitors', $total_visitors],
                ]
            ],
            [
                'title' => 'STUDENT EVENT DISTRIBUTION',
                'data' => [
                    ['Students with 20 Events', $students_20_events],
                    ['Students with 11-15 Events', $students_11_15_events],
                    ['Students with 5-10 Events', $students_5_10_events],
                ]
            ],
            [
                'title' => 'SCRIPTURE READING AWARDS',
                'data' => [
                    ['Silver Apple Readings', $silver_apple],
                    ['Golden Awards', $golden_awards],
                ]
            ],
            [
                'title' => 'EVENT REGISTRATIONS',
                'data' => [
                    ['Total Event Entries', $total_entries],
                ]
            ],
        ];

        $alternateBackground = false;
        foreach ($sections as $section) {
            // Section header with background
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetTextColor(40, 40, 40);
            if ($alternateBackground) {
                $pdf->SetFillColor(245, 245, 245);
            } else {
                $pdf->SetFillColor(235, 235, 235);
            }
            $pdf->Cell(0, 8, $section['title'], 0, 1, 'L', true);
            $pdf->Ln(2);

            // Section data
            $pdf->SetFont('helvetica', '', 10);
            $rowCount = 0;
            foreach ($section['data'] as $row) {
                $pdf->SetTextColor(40, 40, 40);
                $pdf->Cell(110, 7, $row[0], 0, 0, 'L');
                
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->Cell(50, 7, number_format($row[1]), 0, 1, 'R');
                $pdf->SetFont('helvetica', '', 10);
                $rowCount++;
            }
            
            $pdf->Ln(4);
            $alternateBackground = !$alternateBackground;
        }

        $pdf->Ln(10);

        // Events Coordinator Signature Section
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'Events Coordinator', 0, 1, 'L');
        $pdf->Ln(12);
        
        $pdf->SetDrawColor(64, 64, 64);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(20, $pdf->GetY(), 90, $pdf->GetY());
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(70, 5, 'Signature', 0, 1, 'L');
        
        // Add the date stamp below signature
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(70, 5, 'Date: ' . date('F d, Y'), 0, 1, 'L');

        return $pdf;
    }
}