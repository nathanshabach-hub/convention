<?php

// src/Controller/UserssController.php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
 

#[\AllowDynamicProperties]
class UsersController extends AppController {
	public $Conventionregistrationstudents = null;
	public $Evaluationforms = null;

    public function initialize(): void {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('PImage');
        $this->loadComponent('Auth');
        $this->loadComponent('PImageTest');
        $this->loadComponent('Flash');
    }

    
    public function beforeFilter(EventInterface $event): void {
        parent::beforeFilter($event);

        $this->Auth->allow(['login','registration','registerprevdetails','forgotpassword','resetpassword','teachersetpassword','dashboard','myschedule','myevents','editprofile','changepassword','teachers','editteacher','addteacher','archiveteacher','restoreteacher','students','addstudent','editstudent','archivestudent','restorestudent','generatestudentlogincode','judgesregistration','squad247','squad247submit','logout','judgesconfirmation','judgeeditprofile','applyforjudge','switchprofile','judgeexperience','judgingform','logintotest']);

        $this->loadModel("Emailtemplates");
        $this->loadModel("Conventions");
        $this->loadModel("Conventionseasons");
        $this->loadModel("Events");
        $this->loadModel("Eventsubmissions");
        $this->loadModel("Schedulingtimings");
        $this->loadModel("Divisions");
        $this->loadModel("Seasons");
        $this->loadModel("Conventionregistrations");
        $this->loadModel("Conventionregistrationteachers");
        $this->loadModel("Conventionregistrationstudents");
        $this->loadModel("Crstudentevents");
        $this->loadModel("Judgeevaluations");
        
        $this->loadModel("Evaluationforms");
        $this->loadModel("Settings");
    }
    
    public function login($convention_slug=null,$season_id=null) {
        
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Login '.TITLE_FOR_PAGES);
        
        $user_id = $this->request->session()->read("user_id");
        
        $this->set('header_menu_login_active', 'active');
        
        global $loginUserTypes;
        $this->set('loginUserTypes', $loginUserTypes);
        
        // Set empty Users entity for form rendering
        $users = $this->Users->newEmptyEntity();
        $this->set('users', $users);
        
        if ($this->request->is('post'))
        {	
            $formData = (array)$this->request->getData('Users');

            $email_address = trim((string)($formData['email_address'] ?? ''));
            $password = (string)($formData['password'] ?? '');
            $user_type = trim((string)($formData['user_type'] ?? ''));
            $student_code = strtoupper(trim((string)($formData['student_code'] ?? '')));
            $student_last4 = strtoupper(trim((string)($formData['student_last4'] ?? '')));

            if ($user_type === 'Student') {
                $userInfo = null;

                if ($student_code === '' || $student_last4 === '') {
                    $this->Flash->error('Please enter student code and first 4 letters of last name.');
                    return;
                }

                // Student code is mapped to customer_code in this codebase.
                $studentUser = $this->Users->find()
                    ->where(['Users.user_type' => 'Student', 'Users.customer_code' => $student_code])
                    ->first();

                if ($studentUser) {
                    $lastNamePrefix = strtoupper(substr((string)$studentUser->last_name, 0, 4));
                    if ($lastNamePrefix === $student_last4) {
                        $userInfo = $studentUser;
                    }
                }
            } else {
                // Step 1 :: To check in users table
                $userInfo = $this->Users->find()->where(['Users.email_address' => $email_address, 'Users.user_type' => $user_type])->first();
            }
            //$this->prx($userInfo);
            if ($userInfo)
            {
                if ($userInfo->activation_status == 0) {
                    $this->Flash->error('Your account has not yet verified.');
                }
                elseif ($userInfo->status == 0) {
                    $this->Flash->error('Your account got temporary disabled. Please contact events team.');
                }
                elseif ($userInfo->status == 2) {
                    $this->Flash->error('Your account is archived. Please contact events team.');
                }	
                elseif ($userInfo->status == 3) {
                    $this->Flash->error('Your account is rejected. Please contact events team.');
                }				
                elseif ($user_type === 'Student' || (!empty($userInfo) && crypt($password, $userInfo->password) == $userInfo->password)) {
                    
                    
                    //$this->prx($userInfo);

                    $this->request->session()->write("user_id", $userInfo->id);
                    $this->request->session()->write("email_address", $userInfo->email_address);
                    $this->request->session()->write("user_type", $userInfo->user_type);
                    $this->request->session()->write("last_login", $userInfo->last_login);
                    
                    if($userInfo->user_type == "Teacher_Parent" && $userInfo->is_judge == "1")
                    {
                        $this->request->session()->write("current_session_profile_type", 'Supervisor');
                    }
                    
                    // update last login
                    $this->Users->updateAll(['forgot_password_status' => '0','last_login' => date('Y-m-d H:i:s')], ["id" => $userInfo->id]);
                    
                    
                    if($convention_slug && $season_id>0)
                    {
                        // now check if registration for convention required
                        // 1. Check convention exists
                        $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
                        if($conventionD)
                        {
                            // to get season details
                            $seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
                            if($seasonD)
                            {
                                // enter this user record in conventionregistrations table
                                $convention_id 	= $conventionD->id;
                                $user_id 		= $userInfo->id;
                                
                                // get the convention season record so we can store conventionseason_id
                                $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $convention_id,'Conventionseasons.season_id' => $season_id,'Conventionseasons.season_year' => $seasonD->season_year])->first();
                                
                                // to check if this record already exists
                                $checkRegExists = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.user_id' => $user_id,'Conventionregistrations.season_id' => $season_id])->first();
                                if(!$checkRegExists)
                                {
                                    // insert new record
                                    $dataCR = $this->Conventionregistrations->newEntity([]);

                                    $dataCR->slug 					= "convention-registration-".$convention_id.'-'.$user_id.'-'.$season_id.'-'.time();
                                    $dataCR->convention_id			= $convention_id;
                                    $dataCR->conventionseason_id	= $convSeasonD ? $convSeasonD->id : null;
                                    $dataCR->user_id				= $user_id;
                                    $dataCR->season_id				= $season_id;
                                    $dataCR->season_year 			= $seasonD->season_year;
                                    $dataCR->status 				= 1;
                                    
                                    $dataCR->created 				= date('Y-m-d H:i:s');
                                    $dataCR->modified 				= NULL;

                                    $resultCR 		= $this->Conventionregistrations->save($dataCR);
                                }
                                elseif($checkRegExists && $convSeasonD && !$checkRegExists->conventionseason_id)
                                {
                                    // fix existing registrations missing conventionseason_id
                                    $this->Conventionregistrations->updateAll(['conventionseason_id' => $convSeasonD->id], ['id' => $checkRegExists->id]);
                                }
                            }
                        }
                    }
                    
                    
                    $returnUrl = $this->request->session()->read("returnUrl");
                    
                    if(isset($returnUrl) && !empty($returnUrl))
                    {						
                        $this->request->session()->delete("returnUrl");
                        $this->redirect('/' . $returnUrl);
                    }
                    else
                    {
                        $this->request->session()->delete("returnUrl");
                        $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                    }
                    
                } else {
                    $this->Flash->error('Invalid email or password.');
                }
            }
        
            if(!$userInfo)
            {
                if ($user_type === 'Student') {
                    $this->Flash->error('Invalid student code or last name details.');
                } else {
                    $this->Flash->error('Invalid email. Account not found in system.');
                }
            }
            
        } //end if ($this->request->is('post'))
        
    }
    
    public function registration($conventionregistration_slug=null) {
        
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Registration '.TITLE_FOR_PAGES);
        
        $this->set('conventionregistration_slug', $conventionregistration_slug);
        
        $conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conventionregistration_slug])->first();
        if($conventionRegD)
        {
            $this->set('conventionRegD', $conventionRegD);
        }
        else
        {
            $this->Flash->error('Invalid registration information.');
            $this->redirect(['controller' => 'homes', 'action' => 'index']);
        }
        
        $user_id = $conventionRegD->user_id;
        
        
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if (count($data->getErrors()) == 0) {
                
                //$this->prx($this->request->getData());
                
                $new_password 	= $data->password;
                $salt 			= uniqid(mt_rand(), true);
                $password 		= crypt($new_password, '$2a$07$' . $salt . '$');
                
                // update details
                $this->Users->updateAll([
                'first_name' => $data->first_name,
                'middle_name' => $data->middle_name,
                'phone' => $data->phone,
                'password' => $password
                ], ["id" => $user_id]);
                
                $userInfo = $this->Users->find()->where(['Users.id' => $user_id])->first();
                
                // now create a session for this user
                $this->request->session()->write("user_id", $userInfo->id);
                $this->request->session()->write("email_address", $userInfo->email_address);
                $this->request->session()->write("user_type", $userInfo->user_type);
                $this->request->session()->write("first_name", $userInfo->first_name);
                //$this->request->session()->write("last_login", $userInfo->last_login);
                
                $this->Flash->success('Profile details updated successfully.');
                $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
            }
        }
        $this->set('users', $users);
        
        
    }
    
    public function registerprevdetails($convention_slug=null,$season_id=null) {
        
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Register Using Previous Details '.TITLE_FOR_PAGES);
        
        $this->set('convention_slug',$convention_slug);
        $this->set('season_id',$season_id);
        
        // check convention details
        $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
        if($conventionD)
        {
            // to get season details
            $seasonD = $this->Seasons->find()->where(['Seasons.id' => $season_id])->first();
        }
        else
        {
            $this->Flash->error('Invalid details.');
            $this->redirect(['controller' => 'homes', 'action' => 'index']);
        }
        
        if ($this->request->is('post'))
        {	
            //$this->prx($this->request->getData());
            $customer_code	= $this->request->getData()['Users']['customer_code'];
            $password		= $this->request->getData()['Users']['password'];
            
            // Step 1 :: To check in users table
            $userInfo = $this->Users->find()->where(['Users.customer_code' => $customer_code,'Users.user_type' => 'School'])->first();
            //echo crypt($password, $userInfo->password);
            //$this->prx($userInfo);
            if ($userInfo)
            {
                if ($userInfo->activation_status == 0) {
                    $this->Flash->error('Your account has not yet active.');
                }
                elseif ($userInfo->status == 0) {
                    $this->Flash->error('Your account got temporary disabled.');
                } 
                elseif (!empty($userInfo) && crypt($password, $userInfo->password) == $userInfo->password) {

                    $this->request->session()->write("user_id", $userInfo->id);
                    $this->request->session()->write("email_address", $userInfo->email_address);
                    $this->request->session()->write("user_type", $userInfo->user_type);
                    $this->request->session()->write("last_login", $userInfo->last_login);
                    
                    // update last login
                    $this->Users->updateAll(['forgot_password_status' => '0','last_login' => date('Y-m-d H:i:s')], ["id" => $userInfo->id]);
                    
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                    
                    
                } else {
                    $this->Flash->error('Invalid customer code or password.');
                }
            }
        
            if(!$userInfo)
            {
                $this->Flash->error('Invalid customer codes. Account not found in system.');
            }
            
        } //end if ($this->request->is('post'))
        
        
    }
    
    public function forgotpassword() {
        
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Forgot Password '.TITLE_FOR_PAGES);
        
        global $loginUserTypes;
        $this->set('loginUserTypes', $loginUserTypes);
        
        if ($this->request->is('post'))
        {			
            $captchaValid=1;
            
            //$captchaValid=1;
            if ($captchaValid)
            {
                $email_address			= $this->request->getData()['Users']['email_address'];
                $user_type				= $this->request->getData()['Users']['user_type'];
                
                // Step 1 :: To check in users table
                $userInfo = $this->Users->find()->where(['Users.email_address' => $email_address,'Users.user_type' => $user_type])->first();
                
                //$this->prx($userInfo);
                
                if ($userInfo)
                {
                    if ($userInfo->activation_status == 0) {
                        $this->Flash->error('Your account has not yet verified.');
                    }
                    elseif ($userInfo->status == 0) {
                        $this->Flash->error('Your account got temporary disabled.');
                    }
                    elseif ($userInfo) {
                        
                        // update forgot password status
                        $this->Users->updateAll(['forgot_password_status' => 1], ['id' => $userInfo->id]);
                        
                        $emailId = $userInfo['email_address'];
                        
                        $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '3'])->first();
                        
                        $reset_password_link = HTTP_PATH."/users/resetpassword/".$userInfo->email_address."/".md5($userInfo->email_address)."/".$userInfo->id."/".md5($userInfo->id);

                        $toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!email_address!]','[!reset_password_link!]');
                        $fromRepArray = array(SITE_TITLE,$userInfo->first_name,$userInfo->email_address,$reset_password_link);

                        $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
                        $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
                        
                        //echo $messageToSend; exit;
                        
                        $email = new Email();
                        $email->template('default')
                            ->layout('admintemplate')
                            ->emailFormat('html')
                            ->to($emailId)
                            ->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
                            ->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
                            ->subject($subjectToSend)
                            ->viewVars(['content_for_layout' => $messageToSend])
                            ->send();

                        $this->Flash->success('We have successfully sent you reset password link. Please click that link and reset your password.');
                        $this->redirect(['controller' => 'users', 'action' => 'login']);
                        
                        
                    } else {
                        $this->Flash->error('Invalid email entered.');
                    }
                }
                
                // if not found anywhere
                if(!$userInfo)
                {
                    $this->Flash->error('Invalid email. Account not found in system.');
                }
                
            }			
            
            //$this->Flash->error('Invalid email or password.');
            
        } //end if ($this->request->is('post'))
        
    }
    
    public function resetpassword($email_address = NULL, $enc_email = NULL, $userID = NULL, $enc_userID = NULL) {
        
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Reset Password '.TITLE_FOR_PAGES);

        if(md5($email_address) != $enc_email)		
        {
            $this->Flash->error('Invalid link.');
            $this->redirect(['controller' => 'users', 'action' => 'login']);
        }
        
        if(md5($userID) != $enc_userID)		
        {
            $this->Flash->error('Invalid reset password link.');
            $this->redirect(['controller' => 'users', 'action' => 'login']);
        }
        
        $userInfo = $this->Users->find()->where(['Users.id' => $userID, 'Users.email_address' => $email_address])->first();
        
        if($userInfo->forgot_password_status != 1)		
        {
            $this->Flash->error('Invalid link.');
            $this->redirect(['controller' => 'users', 'action' => 'login']);
        }
        
        if ($this->request->is('post'))
        {
            if($userInfo)
            {
                $new_password = $this->request->getData('Users.password');
                $salt = uniqid(mt_rand(), true);
                $password = crypt($new_password, '$2a$07$' . $salt . '$');				
                
                $this->Users->updateAll(['password' => $password,'forgot_password_status' => '0'], ['id' => $userID]);
                $this->Flash->success('Your password reset successfully. Please login.');
                $this->redirect(['controller' => 'users', 'action' => 'login']);
            }
            else
            {
                $this->Flash->error('Invalid request.');
                $this->redirect(['controller' => 'users', 'action' => 'login']);
            }
        }
    }
    
    public function teachersetpassword($email_address = NULL, $enc_email = NULL, $userID = NULL, $enc_userID = NULL) {
        
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Teacher Set Password '.TITLE_FOR_PAGES);

        if(md5($email_address) != $enc_email)		
        {
            $this->Flash->error('Invalid link.');
            $this->redirect(['controller' => 'users', 'action' => 'login']);
        }
        
        if(md5($userID) != $enc_userID)		
        {
            $this->Flash->error('Invalid reset password link.');
            $this->redirect(['controller' => 'users', 'action' => 'login']);
        }
        
        $userInfo = $this->Users->find()->where(['Users.id' => $userID, 'Users.email_address' => $email_address])->first();
        
        if ($this->request->is('post'))
        {
            if($userInfo)
            {
                $new_password = $this->request->getData('Users.password');
                $salt = uniqid(mt_rand(), true);
                $password = crypt($new_password, '$2a$07$' . $salt . '$');				
                
                $this->Users->updateAll(['password' => $password,'forgot_password_status' => '0'], ['id' => $userID]);
                $this->Flash->success('Your password reset successfully. Please login.');
                $this->redirect(['controller' => 'users', 'action' => 'login']);
            }
            else
            {
                $this->Flash->error('Invalid request.');
                $this->redirect(['controller' => 'users', 'action' => 'login']);
            }
        }
        
    }
    
    public function dashboard() {
        //echo 'ddd';exit;
        $this->userLoginCheck();
        
        $user_id 	= $this->request->session()->read("user_id");
        $user_type 	= $this->request->session()->read("user_type");
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Dashboard '.TITLE_FOR_PAGES);
        
        $this->set('active_dashboard', 'active');
        
        $userDetails = null;
        if (!empty($user_id)) {
            $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        }
        $this->set('userDetails', $userDetails);

        if ($userDetails && $userDetails->user_type === 'Student' && !$this->request->session()->read('sess_selected_convention_registration_id')) {
            $currentSeasonId = $this->getCurrentSeason();
            $studentConventionReg = $this->Conventionregistrationstudents->find()
                ->contain(['Conventionregistrations'])
                ->where([
                    'Conventionregistrationstudents.student_id' => $user_id,
                    'Conventionregistrationstudents.season_id' => $currentSeasonId,
                    'Conventionregistrationstudents.status' => 1,
                ])
                ->order(['Conventionregistrationstudents.id' => 'DESC'])
                ->first();

            if ($studentConventionReg && !empty($studentConventionReg->conventionregistration_id)) {
                $this->request->session()->write('sess_selected_convention_registration_id', $studentConventionReg->conventionregistration_id);
            }
        }
        
        $settingsD = $this->Settings->find()->where(['Settings.id' => 1])->first();
        $this->set('settingsD', $settingsD);
        
    }

    public function myschedule() {

        $this->userLoginCheck();
        $this->multiLoginCheck(['Student']);

        $this->viewBuilder()->setLayout('home');
        $this->set('title_for_layout', 'My Schedule ' . TITLE_FOR_PAGES);
        $this->set('active_myschedule', 'active');

        $userId = (int)$this->request->session()->read('user_id');
        $student = $this->Users->find()->where(['Users.id' => $userId])->first();
        $this->set('userDetails', $student);

        $scheduleRows = [];
        if ($student) {
            $currentSeasonId = $this->getCurrentSeason();
            $schoolId = (int)($student->school_id ?? 0);
            $conventionReg = null;

            if ($schoolId > 0 && $currentSeasonId > 0) {
                $conventionReg = $this->Conventionregistrations->find()
                    ->where([
                        'Conventionregistrations.user_id' => $schoolId,
                        'Conventionregistrations.season_id' => $currentSeasonId,
                    ])
                    ->first();
            }

            $scheduleLookup = [];

            $scheduleBaseConditions = [
                'OR' => [
                    ['Schedulingtimings.user_id' => $userId],
                    ['Schedulingtimings.user_id_opponent' => $userId],
                ]
            ];

            if ($conventionReg) {
                $scheduleBaseConditions['Schedulingtimings.conventionseasons_id'] = $conventionReg->conventionseason_id;
                $scheduleBaseConditions['Schedulingtimings.convention_id'] = $conventionReg->convention_id;
                $scheduleBaseConditions['Schedulingtimings.season_id'] = $conventionReg->season_id;
                $scheduleBaseConditions['Schedulingtimings.season_year'] = $conventionReg->season_year;
            }

            $directSchedules = $this->Schedulingtimings->find()
                ->where($scheduleBaseConditions)
                ->contain(['Events', 'Conventionrooms'])
                ->order(['Schedulingtimings.sch_date_time' => 'ASC', 'Schedulingtimings.start_time' => 'ASC'])
                ->all();

            foreach ($directSchedules as $scheduleRow) {
                $scheduleLookup[(int)$scheduleRow->id] = $scheduleRow;
            }

            if ($conventionReg) {
                $studentGroups = $this->Crstudentevents->find()
                    ->where([
                        'Crstudentevents.conventionseason_id' => $conventionReg->conventionseason_id,
                        'Crstudentevents.convention_id' => $conventionReg->convention_id,
                        'Crstudentevents.season_id' => $conventionReg->season_id,
                        'Crstudentevents.season_year' => $conventionReg->season_year,
                        'Crstudentevents.student_id' => $userId,
                        'Crstudentevents.group_name !=' => ''
                    ])
                    ->order(['Crstudentevents.id' => 'ASC'])
                    ->all();

                foreach ($studentGroups as $studentGroup) {
                    $groupSchedules = $this->Schedulingtimings->find()
                        ->where([
                            'Schedulingtimings.conventionseasons_id' => $conventionReg->conventionseason_id,
                            'Schedulingtimings.convention_id' => $conventionReg->convention_id,
                            'Schedulingtimings.season_id' => $conventionReg->season_id,
                            'Schedulingtimings.season_year' => $conventionReg->season_year,
                            'OR' => [
                                ['Schedulingtimings.user_id' => $schoolId],
                                ['Schedulingtimings.user_id_opponent' => $schoolId],
                            ],
                            'Schedulingtimings.event_id' => $studentGroup->event_id,
                            'Schedulingtimings.event_id_number' => $studentGroup->event_id_number,
                            'Schedulingtimings.group_name' => $studentGroup->group_name,
                        ])
                        ->contain(['Events', 'Conventionrooms'])
                        ->order(['Schedulingtimings.sch_date_time' => 'ASC'])
                        ->all();

                    foreach ($groupSchedules as $groupSchedule) {
                        $scheduleLookup[(int)$groupSchedule->id] = $groupSchedule;
                    }
                }

                $projectedRendered = [];
                $baseScheduleRows = array_values($scheduleLookup);
                usort($baseScheduleRows, function ($a, $b) {
                    return strtotime((string)$a->sch_date_time) <=> strtotime((string)$b->sch_date_time);
                });

                foreach ($baseScheduleRows as $scheduleRow) {
                    if (
                        ((int)($scheduleRow->schedule_category ?? 0) === 2 || (int)($scheduleRow->schedule_category ?? 0) === 3)
                        && (int)($scheduleRow->round_number ?? 0) === 1
                    ) {
                        $nextBaseId = (int)$scheduleRow->id;
                        for ($projStep = 1; $projStep <= 2; $projStep++) {
                            $nextMatch = $this->Schedulingtimings->find()
                                ->where([
                                    'Schedulingtimings.conventionseasons_id' => $conventionReg->conventionseason_id,
                                    'Schedulingtimings.convention_id' => $conventionReg->convention_id,
                                    'Schedulingtimings.season_id' => $conventionReg->season_id,
                                    'Schedulingtimings.season_year' => $conventionReg->season_year,
                                    'OR' => [
                                        ['Schedulingtimings.schtimeautoid1' => $nextBaseId],
                                        ['Schedulingtimings.schtimeautoid2' => $nextBaseId],
                                    ]
                                ])
                                ->contain(['Events', 'Conventionrooms'])
                                ->order(['Schedulingtimings.round_number' => 'ASC', 'Schedulingtimings.match_number' => 'ASC', 'Schedulingtimings.id' => 'ASC'])
                                ->first();

                            if (!$nextMatch) {
                                break;
                            }

                            if (isset($projectedRendered[(int)$nextMatch->id])) {
                                $nextBaseId = (int)$nextMatch->id;
                                continue;
                            }

                            $projectedRendered[(int)$nextMatch->id] = 1;
                            $scheduleLookup[(int)$nextMatch->id] = $nextMatch;
                            $nextBaseId = (int)$nextMatch->id;
                        }
                    }
                }
            }

            $scheduleRows = array_values($scheduleLookup);
            usort($scheduleRows, function ($a, $b) {
                return strtotime((string)$a->sch_date_time) <=> strtotime((string)$b->sch_date_time);
            });

            if ($conventionReg) {
                $judgeRegs = $this->Conventionregistrations->find()
                    ->contain(['Users'])
                    ->where([
                        'Conventionregistrations.conventionseason_id' => $conventionReg->conventionseason_id,
                        'Conventionregistrations.convention_id' => $conventionReg->convention_id,
                        'Conventionregistrations.season_id' => $conventionReg->season_id,
                        'Conventionregistrations.season_year' => $conventionReg->season_year,
                        'Conventionregistrations.status' => 1,
                    ])
                    ->all();

                $judgeUserIdsByEvent = [];
                foreach ($judgeRegs as $judgeReg) {
                    if (empty($judgeReg->judges_event_ids)) {
                        continue;
                    }

                    $eventIds = array_map('trim', explode(',', (string)$judgeReg->judges_event_ids));
                    foreach ($eventIds as $eventId) {
                        $eventId = (int)$eventId;
                        if ($eventId <= 0) {
                            continue;
                        }

                        if (!isset($judgeUserIdsByEvent[$eventId])) {
                            $judgeUserIdsByEvent[$eventId] = [];
                        }

                        $judgeUserIdsByEvent[$eventId][] = (int)$judgeReg->user_id;
                    }
                }

                foreach ($judgeUserIdsByEvent as $eventId => $userIds) {
                    $judgeUserIdsByEvent[$eventId] = array_values(array_unique(array_filter($userIds)));
                }

                foreach ($scheduleRows as $scheduleRow) {
                    $scheduleRow->is_judged_complete = false;
                    $scheduleRow->assigned_judges_count = 0;
                    $scheduleRow->submitted_judges_count = 0;

                    $eventId = (int)($scheduleRow->event_id ?? 0);
                    if ($eventId <= 0 || empty($judgeUserIdsByEvent[$eventId])) {
                        continue;
                    }

                    $eventSubmission = $this->Eventsubmissions->find()
                        ->where([
                            'Eventsubmissions.conventionregistration_id' => $conventionReg->id,
                            'Eventsubmissions.conventionseason_id' => $conventionReg->conventionseason_id,
                            'Eventsubmissions.event_id' => $eventId,
                            'Eventsubmissions.student_id' => $userId,
                        ])
                        ->order(['Eventsubmissions.id' => 'DESC'])
                        ->first();

                    if (!$eventSubmission) {
                        continue;
                    }

                    $assignedJudgeIds = $judgeUserIdsByEvent[$eventId];
                    $assignedJudgeCount = count($assignedJudgeIds);
                    if ($assignedJudgeCount <= 0) {
                        continue;
                    }

                    $submittedJudgeCount = $this->Judgeevaluations->find()
                        ->select(['Judgeevaluations.uploaded_by_user_id'])
                        ->where([
                            'Judgeevaluations.eventsubmission_id' => $eventSubmission->id,
                            'Judgeevaluations.uploaded_by_user_id IN' => $assignedJudgeIds,
                        ])
                        ->distinct(['Judgeevaluations.uploaded_by_user_id'])
                        ->enableHydration(false)
                        ->all()
                        ->count();

                    $scheduleRow->assigned_judges_count = $assignedJudgeCount;
                    $scheduleRow->submitted_judges_count = $submittedJudgeCount;
                    $scheduleRow->is_judged_complete = ($submittedJudgeCount >= $assignedJudgeCount);
                }
            }
        }

        $this->set('scheduleRows', $scheduleRows);
    }

    public function myevents() {

        $this->userLoginCheck();
        $this->multiLoginCheck(['Student']);

        $this->viewBuilder()->setLayout('home');
        $this->set('title_for_layout', 'My Events ' . TITLE_FOR_PAGES);
        $this->set('active_myevents', 'active');

        $userId = (int)$this->request->session()->read('user_id');
        $student = $this->Users->find()->where(['Users.id' => $userId])->first();
        $this->set('userDetails', $student);

        $eventRows = [];
        if ($student) {
            $currentSeasonId = $this->getCurrentSeason();
            $schoolId = (int)($student->school_id ?? 0);
            $conventionReg = null;

            if ($schoolId > 0 && $currentSeasonId > 0) {
                $conventionReg = $this->Conventionregistrations->find()
                    ->where([
                        'Conventionregistrations.user_id' => $schoolId,
                        'Conventionregistrations.season_id' => $currentSeasonId,
                    ])
                    ->first();
            }

            $conditions = ['Crstudentevents.student_id' => $userId];
            if ($conventionReg) {
                $conditions['Crstudentevents.conventionregistration_id'] = $conventionReg->id;
                $conditions['Crstudentevents.convention_id'] = $conventionReg->convention_id;
                $conditions['Crstudentevents.season_id'] = $conventionReg->season_id;
                $conditions['Crstudentevents.season_year'] = $conventionReg->season_year;
            }

            $eventRows = $this->Crstudentevents->find()
                ->where($conditions)
                ->contain(['Events', 'Conventions'])
                ->order(['Crstudentevents.id' => 'DESC'])
                ->all();
        }

        $this->set('eventRows', $eventRows);
    }
    
    public function editprofile() {
        
        $this->userLoginCheck();
        $this->multiLoginCheck(['School','Teacher_Parent','Student']);
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Edit Profile " . TITLE_FOR_PAGES);
        
        $this->set('active_editprofile','active');
        
        $user_id = $this->request->session()->read("user_id");
        
        $userDetails = null;
        if (!empty($user_id)) {
            $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
        }
        $this->set('userDetails', $userDetails);
        
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if (count($data->getErrors()) == 0) {
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('Profile details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
        //pr($users);exit;
        
    }
    
    public function changepassword() {
        
        $this->userLoginCheck();
        $this->multiLoginCheck(['School','Teacher_Parent','Judge','Student']);
        
        $this->set("title_for_layout", "Change Password" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
        $this->set('active_changepassword','active');
        
        $msgString = '';

        $user_id = $this->request->session()->read("user_id");

        $userDetails = null;
        if (!empty($user_id)) {
            $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        }
        $this->set('userDetails', $userDetails);
        
        $users = $this->Users->newEntity([]);

        if ($this->request->is('post')) {
            //die;
            $data = $this->Users->patchEntity($users, $this->request->getData(), ['validate' => 'changePassword']);

            if (count($data->getErrors()) == 0) {
//               pr($data); die('tsting');
                $old_password = $this->request->getData()['Users']['old_password'];
                $new_password = $this->request->getData()['Users']['new_password'];
                if ($userDetails && crypt($old_password, $userDetails->password) != $userDetails->password) {// Checking the OLD password matched aur not
                    $msgString = 'Old Password is not correct.';
                } else {
                    if ($userDetails && crypt($new_password, $userDetails->password) == $userDetails->password) {// Checking the both password matched aur not
                        $msgString .="- You cannot put your old password for the new password.";
                    }
                }


                if (isset($msgString) && $msgString != '') {
                    $this->Flash->error($msgString);
                } else {
                    $new_password = $data["new_password"];
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    /* geting and setting users data */
                    $usersTable = TableRegistry::get("Users");
                    $user = $usersTable->get($user_id); // Return user with id 
                    $user->password = $password;
                    $usersTable->save($user);

                    $this->Flash->success('Your Password has been changed successfully.');
                    return $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            } else {
                $this->Flash->error('Please below listed errors.');
            }
        }


        $this->set('users', $users);
    }
    
    public function teachers() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        $this->set("title_for_layout", "Supervisors List" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
        $this->set('active_teachers','active');
        
        $msgString = '';

        $user_id = $this->request->session()->read("user_id");

        $userDetails = null;
        if (!empty($user_id)) {
            $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        }
        $this->set('userDetails', $userDetails);

        $separator = array();
        $keyword = '';

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Users->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Users->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Users->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Users']['keyword']) && $this->request->getData()['Users']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Users']['keyword']);
            }
        } else {
            $searchArr = (array)$this->request->getParam('pass', []);
            if (!empty($searchArr[0])) {
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (!empty($keyword)) {
            $separator[] = 'keyword:' . urlencode($keyword);
            $this->set('keyword', $keyword);
        }

        $separator = implode("/", $separator);
        $this->set('separator', $separator);

        if (empty($user_id)) {
            return $this->redirect(['controller' => 'users', 'action' => 'login']);
        }
        
        // Use a closure to build the query with CakePHP 4 syntax
        $this->paginate = [
            'limit' => 30,
            'order' => ['Users.id' => 'DESC']
        ];
        
        $query = $this->Users->find()
            ->where([
                'Users.school_id' => $user_id,
                'Users.user_type' => 'Teacher_Parent'
            ]);
        
        // Add keyword search if provided
        if (!empty($keyword)) {
            $query = $query->where(function ($exp, $q) use ($keyword) {
                return $exp->like('Users.name', '%' . $keyword . '%');
            });
        }
        
        $this->set('users', $this->paginate($query));
        if (strtolower($this->request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
            $this->viewBuilder()->disableAutoLayout();
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Users');
            return $this->render('teachers');
        }
    }
    
    public function editteacher($teacher_slug=null) {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Edit Supervisor Details " . TITLE_FOR_PAGES);
        
        $this->set('active_teachers','active');
        
        $user_id = $this->request->session()->read("user_id");
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
        
        global $genderDD;
        $this->set('genderDD', $genderDD);
        
        global $yesNoDD;
        $this->set('yesNoDD', $yesNoDD);
        
        // to get teacher details
        $teacherD = $this->Users->find()->where(['Users.school_id' => $user_id,'Users.slug' => $teacher_slug])->first();
        
        $users = $this->Users->get($teacherD->id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if (count($data->getErrors()) == 0) {
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('Supervisor details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'teachers']);
                }
            }
        }
        $this->set('users', $users);
        //pr($users);exit;
        
    }
    
    public function addteacher() {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Add Supervisors Details " . TITLE_FOR_PAGES);
        
        $this->set('active_teachers','active');
        
        $user_id = $this->request->session()->read("user_id");
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
        
        global $genderDD;
        $this->set('genderDD', $genderDD);
        
        global $yesNoDD;
        $this->set('yesNoDD', $yesNoDD);
        
        $users = $this->Users->newEntity([]);
        if ($this->request->is('post')) {
            
            //$this->prx($this->request->getData());
            
            $flagCheck = 1;
            
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            // to check that this email not duplicate for one school
            $checkEmailS = $this->Users->find()->where(['Users.email_address' => $data->email_address,'Users.school_id' => $user_id])->first();
            
            // to check if this email exists - in admins table
            $checkEmailA = $this->Admins->find()->where(['Admins.email' => $data->email_address])->first();
            
            if($checkEmailA || $checkEmailS)
            {
                $flagCheck = 0;
                $this->Flash->error('Email address already exists.');
            }
            
            if (count($data->getErrors()) == 0 && $flagCheck == 1) {

                $slug = $this->getSlug($this->request->getData()['Users']['first_name'] . ' ' . time(), 'Users');
                $data->slug = $slug;
                
                $data->user_type = 'Teacher_Parent';
                $data->school_id = $user_id;
                $data->status = 0;
                $data->activation_status = 0;
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
                if ($resultUT = $this->Users->save($data)) {
                    
                    // now send an email to teacher to verify account set password
                    $emailId 				= $resultUT->email_address;
                    $teacher_name 			= $resultUT->first_name;
                    
                    $school_name 			= $userDetails->first_name;
                    
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '4'])->first();

                    $link = HTTP_PATH . "/homes/teacherverifyaccount/" . $resultUT->slug . "/" . md5($resultUT->slug) . "/" . md5($resultUT->id);
                    //$sitelink = '<a style="color:#000; text-decoration: underline;" href="mailto:' . MAIL_FROM . '">' . MAIL_FROM . '</a>';
                    
                    $toRepArray 	= array('[!teacher_name!]','[!school_name!]','[!teacher_email_address!]','[!LINK!]','[!customer_code!]');
                    $fromRepArray 	= array($teacher_name,$school_name,$emailId,$link,$userDetails->customer_code);
                    
                    $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
                    $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
                    
                    //echo $messageToSend;exit;
                    
                    $email = new Email();
                    $email->template('default')
                            ->layout('admintemplate')
                        ->emailFormat('html')
                        ->to($emailId)
                        //->cc(HEADERS_CC)
                        ->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
                        ->subject($subjectToSend)
                        ->viewVars(['content_for_layout' => $messageToSend])
                        ->send();
                    
                    
                    $this->Flash->success('Supervisor details added successfully. Supervisor will receive an email to verify account.');
                    $this->redirect(['controller' => 'users', 'action' => 'teachers']);
                }
                
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
    
    public function archiveteacher($teacher_slug=null) {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        $school_id 	= $this->request->session()->read("user_id");
        
        // to check if this teacher exists
        $teacherD = $this->Users->find()->where(['Users.slug' => $teacher_slug,'Users.school_id' => $school_id])->first();
        if($teacherD)
        {
            $this->Users->updateAll(['status' => '2'], ["slug"=>$teacher_slug]);
            $this->Flash->success('Supervisors details archived successfully.');
        }
        else
        {
            $this->Flash->error('Supervisors not found.');
        }
        
        
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
    
    public function restoreteacher($teacher_slug=null) {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        $school_id 	= $this->request->session()->read("user_id");
        
        // to check if this teacher exists
        $teacherD = $this->Users->find()->where(['Users.slug' => $teacher_slug,'Users.school_id' => $school_id])->first();
        if($teacherD)
        {
            $this->Users->updateAll(['status' => '1'], ["slug"=>$teacher_slug]);
            $this->Flash->success('Supervisor details restored successfully.');
        }
        else
        {
            $this->Flash->error('Supervisor not found.');
        }
        
        
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
    
    public function students() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        $this->set("title_for_layout", "Students List" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
        $this->set('active_students','active');
        
        $msgString = '';

        $user_id 	= $this->request->session()->read("user_id");
        $user_type 	= $this->request->session()->read("user_type");
        $userDetails = null;
        if (!empty($user_id)) {
            $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        }
        $this->set('userDetails', $userDetails);

        $separator = array();
        $keyword = '';

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Users->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Users->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Users->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Users']['keyword']) && $this->request->getData()['Users']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Users']['keyword']);
            }
        } else {
            $searchArr = (array)$this->request->getParam('pass', []);
            if (!empty($searchArr[0])) {
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        if (!empty($keyword)) {
            $separator[] = 'keyword:' . urlencode($keyword);
            $this->set('keyword', $keyword);
        }

        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        
        // Build the query with CakePHP 4 syntax
        $query = $this->Users->find();
        
        // Always filter by Student user type
        $query = $query->where(['Users.user_type' => 'Student']);
        
        // Filter by school_id based on user_type
        if (!empty($user_type)) {
            if ($user_type == "School") {
                $query = $query->where(['Users.school_id' => $user_id]);
            } elseif ($user_type == "Teacher_Parent" && !empty($userDetails)) {
                $query = $query->where(['Users.school_id' => $userDetails->school_id]);
            }
        }
        
        // Add keyword search if provided
        if (!empty($keyword)) {
            $query = $query->where(function ($exp, $q) use ($keyword) {
                return $exp->like('Users.name', '%' . $keyword . '%');
            });
        }
        
        $this->paginate = [
            'contain' => ['Schools'],
            'limit' => 1000,
            'order' => ['Users.first_name' => 'ASC','Users.middle_name' => 'ASC','Users.last_name' => 'ASC']
        ];
        $this->set('users', $this->paginate($query));
        if (strtolower($this->request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
            $this->viewBuilder()->disableAutoLayout();
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Users');
            return $this->render('students');
        }
    }
    
    public function addstudent() {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Add Student Details " . TITLE_FOR_PAGES);
        
        $this->set('active_students','active');
        
        $user_id = $this->request->session()->read("user_id");
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
        
        global $birthYearDD;
        $this->set('birthYearDD', $birthYearDD);
        
        global $genderDD;
        $this->set('genderDD', $genderDD);
        
        $users = $this->Users->newEntity([]);
        if ($this->request->is('post')) {
            
            //$this->prx($this->request->getData());
            
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if (count($data->getErrors()) == 0) {

                $slug = $this->getSlug($this->request->getData()['Users']['first_name'] . ' ' . time(), 'Users');
                $data->slug = $slug;
                
                if($this->request->session()->read("user_type") == "School")
                {
                    $data->school_id = $user_id;
                }
                else
                if($this->request->session()->read("user_type") == "Teacher")
                {
                    $data->school_id 			= $userDetails->school_id;
                }
                
                $data->user_type = 'Student';
                $data->status = 1;
                $data->activation_status = 1;
                if (empty($data->customer_code)) {
                    $data->customer_code = $this->generateUniqueStudentLoginCode();
                }
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
                if ($this->Users->save($data)) {
                    $this->Flash->success('Student details added successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'students']);
                }
                
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
    
    public function editstudent($student_slug=null) {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Edit Student Details " . TITLE_FOR_PAGES);
        
        $this->set('active_students','active');
        
        $user_id = $this->request->session()->read("user_id");
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
        
        global $birthYearDD;
        $this->set('birthYearDD', $birthYearDD);
        
        global $genderDD;
        $this->set('genderDD', $genderDD);
        
        // to get student details
        $studentD = $this->Users->find()->where(['Users.slug' => $student_slug])->first();
        
        
        $users = $this->Users->get($studentD->id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if (count($data->getErrors()) == 0) {
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('Student details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'students']);
                }
            }
        }
        $this->set('users', $users);
        //pr($users);exit;
        
    }
    
    public function archivestudent($student_slug=null) {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        $school_id 	= $this->request->session()->read("user_id");
        
        // to check if this student exists
        $studentD = $this->Users->find()->where(['Users.slug' => $student_slug,'Users.school_id' => $school_id])->first();
        if($studentD)
        {
            $this->Users->updateAll(['status' => '2'], ["slug"=>$student_slug]);
            $this->Flash->success('Student details archived successfully.');
        }
        else
        {
            $this->Flash->error('Student not found.');
        }
        
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
    
    public function restorestudent($student_slug=null) {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        $school_id 	= $this->request->session()->read("user_id");
        
        // to check if this student exists
        $studentD = $this->Users->find()->where(['Users.slug' => $student_slug,'Users.school_id' => $school_id])->first();
        if($studentD)
        {
            $this->Users->updateAll(['status' => '1'], ["slug"=>$student_slug]);
            $this->Flash->success('Student details restored successfully.');
        }
        else
        {
            $this->Flash->error('Student not found.');
        }
        
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }

    public function generatestudentlogincode($student_slug=null) {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();

        $sessionUserId = (int)$this->request->session()->read("user_id");
        $sessionUserType = (string)$this->request->session()->read("user_type");

        $schoolId = $sessionUserId;
        if ($sessionUserType === 'Teacher_Parent') {
            $sessionUser = $this->Users->find()->where(['Users.id' => $sessionUserId])->first();
            if ($sessionUser && !empty($sessionUser->school_id)) {
                $schoolId = (int)$sessionUser->school_id;
            }
        }

        $studentD = $this->Users->find()->where([
            'Users.slug' => $student_slug,
            'Users.school_id' => $schoolId,
            'Users.user_type' => 'Student'
        ])->first();

        if (!$studentD) {
            $this->Flash->error('Student not found.');
            return $this->redirect(['controller' => 'users', 'action' => 'students']);
        }

        $loginCode = $this->generateUniqueStudentLoginCode();

        $this->Users->updateAll(['customer_code' => $loginCode], ['id' => $studentD->id]);

        $this->Flash->success('Login code generated successfully: ' . $loginCode);
        return $this->redirect(['controller' => 'users', 'action' => 'students']);
    }

    protected function generateUniqueStudentLoginCode() {
        $seed = time();
        do {
            $loginCode = strtoupper(substr(str_pad(dechex($seed), 8, '0', STR_PAD_LEFT), -8));
            $existingCode = $this->Users->find()->where(['Users.customer_code' => $loginCode])->first();
            $seed++;
        } while ($existingCode);

        return $loginCode;
    }
    
    public function deletestudent_old($student_slug=null) {
        
        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
        
        $school_id 	= $this->request->session()->read("user_id");
        
        // to check if this student exists
        $studentD = $this->Users->find()->where(['Users.slug' => $student_slug,'Users.school_id' => $school_id])->first();
        if($studentD)
        {
            // to check if this student is linked with any convention registration students list
            $checkCRS = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.student_id' => $studentD->id])->first();
            if($checkCRS)
            {
                $this->Flash->error('Student cannot delete. Student is linked with convention registration teachers list.');
            }
            else
            {
                $this->Users->deleteAll(['Users.school_id' => $school_id,'Users.id' => $studentD->id]);
                $this->Flash->success('Student details deleted successfully.');
            }
        }
        else
        {
            $this->Flash->error('Student not found.');
        }
        
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
    
    public function judgesregistration() {
        
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Judges Registration '.TITLE_FOR_PAGES);
        
        $this->set('header_menu_judgesreg_active', 'active');
        
        $users = $this->Users->newEntity([]);
        if ($this->request->is('post')) {
            
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            $flagCheck				=	1;
            
            // to check if its exists in users table
            $checkUE = $this->Users->find()->where(['Users.email_address' => $data->email_address])->first();
            if($checkUE)
            {
                $flagCheck = 0;
                $this->Flash->error('Email address already exists.');
            }
            // to check if its exists in admins table
            $checkUA = $this->Admins->find()->where(['Admins.email' => $data->email_address])->first();
            if($checkUA)
            {
                $flagCheck = 0;
                $this->Flash->error('Email address already exists.');
            }
            
            if (count($data->getErrors()) == 0 && $flagCheck)
            {
                //$this->prx($this->request->getData());
                
                $slug = $this->getSlug($this->request->getData()['Users']['first_name'].' '.$this->request->getData()['Users']['last_name'].' '.time(), 'Users');
                $data->slug 				= $slug;
                $data->created 				= date('Y-m-d H:i:s', time());
                $data->modified 			= date('Y-m-d H:i:s', time());
                
                $data->user_type 			= 'Judge';
                $data->is_judge 			= 1;
                
                $data->status 				= 0;
                $data->activation_status 	= 0;
                
                $new_password = $this->request->getData()['Users']['password'];
                $salt = uniqid(mt_rand(), true);
                $password = crypt($new_password, '$2a$07$' . $salt . '$');
                $data->password = $password;
                
                if ($result = $this->Users->save($data)) {
                    
                    $first_name = $result->first_name;
                    $last_name 	= $result->last_name;
                    $emailId 	= $this->request->getData()['Users']['email_address'];
                    
                    $uIDA 		= $result->id + 2013;
                    
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '11'])->first();

                    $link = HTTP_PATH . "/users/judgesconfirmation/".$result->slug."/". md5($result->slug)."/".urlencode($emailId).'/'.md5($uIDA);
                    
                    $toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!last_name!]','[!email_address!]','[!LINK!]');
                    $fromRepArray = array(SITE_TITLE,$first_name,$last_name,$emailId,$link);
                    
                    $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
                    $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
                    
                    //echo $messageToSend;exit;
                    
                    $email = new Email();
                    $email->template('default')
                            ->layout('admintemplate')
                        ->emailFormat('html')
                        ->to($emailId)
                        ->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
                        ->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
                        ->subject($subjectToSend)
                        ->viewVars(['content_for_layout' => $messageToSend])
                        ->send();
                    
                    $this->Flash->success('Your account has been successfully created. Please check your email for your activation link. If you do not receive it within a few minutes, please check your spam folder or contact our support team.');
                    
                    $this->redirect(['controller' => 'users', 'action' => 'login']);
                }
            } else {
                
            }
        }
        $this->set('users', $users);
        
    }

    public function squad247() {
        $this->viewBuilder()->setLayout("home");
        $this->set('title_for_layout', '24/7 Squad Application '.TITLE_FOR_PAGES);

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
                if (isset($decoded['application_intro'])) {
                    $squad247Data['application_intro'] = trim((string)$decoded['application_intro']);
                }
                if (isset($decoded['success_requirements']) && is_array($decoded['success_requirements'])) {
                    $squad247Data['success_requirements'] = $decoded['success_requirements'];
                }
                if (isset($decoded['successful_need_to']) && is_array($decoded['successful_need_to'])) {
                    $squad247Data['successful_need_to'] = $decoded['successful_need_to'];
                }
                if (isset($decoded['important_note'])) {
                    $squad247Data['important_note'] = trim((string)$decoded['important_note']);
                }
                if (isset($decoded['applications_email'])) {
                    $squad247Data['applications_email'] = trim((string)$decoded['applications_email']);
                }
                if (isset($decoded['applications_deadline'])) {
                    $squad247Data['applications_deadline'] = trim((string)$decoded['applications_deadline']);
                }
                if (isset($decoded['page2_title'])) {
                    $squad247Data['page2_title'] = trim((string)$decoded['page2_title']);
                }
                if (isset($decoded['page2_description'])) {
                    $squad247Data['page2_description'] = trim((string)$decoded['page2_description']);
                }
                if (isset($decoded['applicant_must_provide']) && is_array($decoded['applicant_must_provide'])) {
                    $squad247Data['applicant_must_provide'] = $decoded['applicant_must_provide'];
                }
                if (isset($decoded['blue_card_requirement'])) {
                    $squad247Data['blue_card_requirement'] = trim((string)$decoded['blue_card_requirement']);
                }
                if (isset($decoded['payment_options'])) {
                    $squad247Data['payment_options'] = trim((string)$decoded['payment_options']);
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

        $this->set('squad247Data', $squad247Data);
    }

    public function squad247submit() {
        $this->request->allowMethod(['post']);
        $payload = $this->request->getData('submission', []);
        if (is_array($payload) && count($payload) === 1 && isset($payload[0]) && is_string($payload[0])) {
            $payload = $payload[0];
        }
        if (is_string($payload)) {
            $decodedPayload = json_decode($payload, true);
            $payload = is_array($decodedPayload) ? $decodedPayload : [];
        }

        $uploadedFiles = $this->request->getData('attachments');
        $savedFiles = $this->saveSquad247Uploads($uploadedFiles);
        if (!is_array($payload)) {
            $payload = [];
        }
        $payload['files'] = $savedFiles;

        $submission = [
            'submitted_at' => date('Y-m-d H:i:s'),
            'ip_address' => (string)$this->request->clientIp(),
            'payload' => $payload,
        ];

        $this->saveSquad247Submission($submission);
        $this->sendSquad247SubmissionNotification($submission);

        $response = ['success' => true, 'message' => 'Thank you for your 24/7 Squad application. It has been received and our Events team will be in touch soon.'];
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
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

    protected function saveSquad247Submission(array $submission) {
        $submissions = $this->loadSquad247Submissions();
        array_unshift($submissions, $submission);
        @file_put_contents($this->squad247SubmissionsFile(), json_encode($submissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    protected function sendSquad247SubmissionNotification(array $submission) {
        try {
            $payload = isset($submission['payload']) && is_array($submission['payload']) ? $submission['payload'] : [];
            $fields = isset($payload['fields']) && is_array($payload['fields']) ? $payload['fields'] : [];

            $findFieldValue = static function (array $rows, array $labelNeedles, array $excludeNeedles = []) {
                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $label = strtolower(trim((string)($row['label'] ?? '')));
                    $value = trim((string)($row['value'] ?? ''));
                    if ($label === '' || $value === '') {
                        continue;
                    }

                    $skip = false;
                    foreach ($excludeNeedles as $excludeNeedle) {
                        if ($excludeNeedle !== '' && strpos($label, strtolower((string)$excludeNeedle)) !== false) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) {
                        continue;
                    }

                    foreach ($labelNeedles as $needle) {
                        if ($needle !== '' && strpos($label, strtolower((string)$needle)) !== false) {
                            return $value;
                        }
                    }
                }
                return '';
            };

            $applicantName = $findFieldValue($fields, ['full name', 'applicant name'], ['parent', 'guardian', 'principal', 'pastor', 'reference']);
            $emailAddress = $findFieldValue($fields, ['email']);
            $phone = $findFieldValue($fields, ['phone']);
            $country = $findFieldValue($fields, ['country']);

            if ($applicantName === '') {
                $applicantName = 'Unknown Applicant';
            }
            if ($emailAddress === '') {
                $emailAddress = 'Not provided';
            }
            if ($phone === '') {
                $phone = 'Not provided';
            }
            if ($country === '') {
                $country = 'Not provided';
            }

            $submittedAt = trim((string)($submission['submitted_at'] ?? date('Y-m-d H:i:s')));
            $ipAddress = trim((string)($submission['ip_address'] ?? 'N/A'));

            $adminUrl = rtrim((string)HTTP_PATH, '/') . '/admin/admins/squad247';
            $fromEmail = defined('MAIL_FROM') ? (string)MAIL_FROM : 'no-reply@accelerateministries.com.au';
            $fromName = defined('SITE_TITLE') ? (string)SITE_TITLE : 'Accelerate';

            $subject = 'New 24/7 Squad Application - ' . $applicantName;
            $body = '';
            $body .= '<p>A new 24/7 Squad application has been submitted.</p>';
            $body .= '<p><strong>Applicant:</strong> ' . htmlspecialchars($applicantName, ENT_QUOTES, 'UTF-8') . '<br>';
            $body .= '<strong>Email:</strong> ' . htmlspecialchars($emailAddress, ENT_QUOTES, 'UTF-8') . '<br>';
            $body .= '<strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '<br>';
            $body .= '<strong>Country:</strong> ' . htmlspecialchars($country, ENT_QUOTES, 'UTF-8') . '<br>';
            $body .= '<strong>Submitted At:</strong> ' . htmlspecialchars($submittedAt, ENT_QUOTES, 'UTF-8') . '<br>';
            $body .= '<strong>IP Address:</strong> ' . htmlspecialchars($ipAddress, ENT_QUOTES, 'UTF-8') . '</p>';
            $body .= '<p><a href="' . htmlspecialchars($adminUrl, ENT_QUOTES, 'UTF-8') . '">Open 24/7 submissions in admin</a></p>';

            $email = new Email();
            $email->setEmailFormat('html')
                ->setTo('nathanwaqa@accelerate.edu.au')
                ->setFrom([$fromEmail => $fromName])
                ->setSubject($subject)
                ->send($body);
        } catch (\Throwable $e) {
            $this->log('Failed to send 24/7 submission notification email: ' . $e->getMessage(), 'error');
        }
    }

    protected function squad247UploadsDir() {
        return WWW_ROOT . 'uploads' . DS . 'squad247';
    }

    protected function saveSquad247Uploads($uploadedFiles) {
        $files = [];
        if (empty($uploadedFiles)) {
            return $files;
        }

        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $uploadDir = $this->squad247UploadsDir();
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $maxBytes = 10 * 1024 * 1024;

        foreach ($uploadedFiles as $uploadedFile) {
            if (!is_object($uploadedFile) || !method_exists($uploadedFile, 'getError')) {
                continue;
            }
            if ((int)$uploadedFile->getError() !== 0) {
                continue;
            }

            $clientName = trim((string)$uploadedFile->getClientFilename());
            if ($clientName === '') {
                continue;
            }

            $size = (int)$uploadedFile->getSize();
            if ($size <= 0 || $size > $maxBytes) {
                continue;
            }

            $ext = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                continue;
            }

            $base = pathinfo($clientName, PATHINFO_FILENAME);
            $base = preg_replace('/[^A-Za-z0-9_-]/', '_', $base);
            $base = trim((string)$base, '_');
            if ($base === '') {
                $base = 'attachment';
            }

            $storedName = date('YmdHis') . '_' . mt_rand(1000, 9999) . '_' . $base . '.' . $ext;
            $targetPath = $uploadDir . DS . $storedName;

            try {
                $uploadedFile->moveTo($targetPath);
            } catch (\Throwable $e) {
                continue;
            }

            $files[] = [
                'name' => $clientName,
                'stored_name' => $storedName,
                'size' => $size,
                'url' => HTTP_PATH . '/uploads/squad247/' . rawurlencode($storedName),
            ];
        }

        return $files;
    }
    
    public function judgesconfirmation($slug = null, $md5slug = null, $email = null, $uIDA = null) {
        
        if (md5($slug) == $md5slug)
        {
            $userCheck = $this->Users->find()->where(['Users.slug' => $slug])->first();
            
            if($userCheck && $uIDA == md5($userCheck->id + 2013))
            {
                if ($userCheck->status == 0 && $userCheck->activation_status == 0)
                {
                    $this->Users->updateAll(['activation_status' => '1','modified' => date('Y-m-d H:i:s', time())], ["id" => $userCheck->id]);
                    
                    // send email to events team
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '12'])->first();
                    
                    $toRepArray = array('[!first_name!]','[!last_name!]','[!email_address!]');
                    $fromRepArray = array($userCheck->first_name,$userCheck->last_name,$$userCheck->email_address);
                    
                    $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
                    $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
                    
                    //echo $messageToSend;exit;
                    
                    $email = new Email();
                    $email->template('default')
                            ->layout('admintemplate')
                        ->emailFormat('html')
                        ->to(ACCOUNTS_TEAM_ANOTHER_EMAIL)
                        //->cc(HEADERS_CC)
                        ->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
                        ->subject($subjectToSend)
                        ->viewVars(['content_for_layout' => $messageToSend])
                        ->send();
                    
                    
                    $this->Flash->success('Your account has been successfully verified. Admin will review and activate your account.');
                    $this->redirect(['controller' => 'users', 'action' => 'login']);
                }
                else
                {
                    $this->Flash->error('You have already used this activation link!');
                }
            }
            else
            {
                $this->Flash->error('User not found.');
            }
        }
        else
        {
            $this->Flash->error('Invalid activation link!');
        }
        
        $this->redirect(['controller' => 'users', 'action' => 'login']);
    }
    
    public function judgeeditprofile() {
        
        $this->userLoginCheck();
        $this->judgeLoginCheck();
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Edit Profile " . TITLE_FOR_PAGES);
        
        $this->set('active_editprofile','active');
        
        $user_id = $this->request->session()->read("user_id");
        
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
        
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if (count($data->getErrors()) == 0) {
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('Profile details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
        //pr($users);exit;
        
    }
    
    public function applyforjudge() {
        
        $this->userLoginCheck();
        $this->teacherLoginCheck();
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Apply For Judge " . TITLE_FOR_PAGES);
        
        $this->set('active_applyforjudge','active');
        
        $user_id = $this->request->session()->read("user_id");
        
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
        $this->set('userDetails', $userDetails);
        
        /* if($userDetails->is_judge != 0)
        {
            $this->Flash->error('Invalid access.');
            $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
        } */
        
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            $data->is_judge = 2;
            
            if (count($data->getErrors()) == 0) {
                
                if ($this->Users->save($data)) {
                    
                    // no send email to admin that one supervisor applied for judge
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '15'])->first();
                    
                    $toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!last_name!]','[!email_address!]');
                    $fromRepArray = array(SITE_TITLE,$userDetails->first_name,$userDetails->last_name,$userDetails->email_address);
                    
                    $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
                    $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
                    
                    //echo $messageToSend;exit;
                    
                    $email = new Email();
                    $email->template('default')
                            ->layout('admintemplate')
                        ->emailFormat('html')
                        ->to(ACCOUNTS_TEAM_ANOTHER_EMAIL)
                        ->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
                        ->subject($subjectToSend)
                        ->viewVars(['content_for_layout' => $messageToSend])
                        ->send();
                    
                    $this->Flash->success('Your request to apply for judge has been submitted successfully. Please wait while admin review and approve/reject.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
        //pr($users);exit;
        
    }
    
    public function switchprofile($switchprofiletype=null) {

        $user_id = $this->request->session()->read("user_id");
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
        
        if($switchprofiletype == 'switchtojudge' || $switchprofiletype == 'switchtosupervisor')
        {
            $this->request->session()->delete('sess_selected_convention_registration_id');
            $this->request->session()->delete('sess_selected_convention_id');
            
            if($userDetails->user_type == 'Teacher_Parent' && $userDetails->is_judge == '1')
            {
                if($switchprofiletype == 'switchtojudge')
                {
                    $this->request->session()->delete('current_session_profile_type');
                    $this->request->session()->write("current_session_profile_type", 'Judge');
                    
                    $this->Flash->success('Profile successfully switched as judge.');
                }
                
                if($switchprofiletype == 'switchtosupervisor')
                {
                    $this->request->session()->delete('current_session_profile_type');
                    $this->request->session()->write("current_session_profile_type", 'Supervisor');
                    
                    $this->Flash->success('Profile successfully switched as supervisor.');
                }
            }
            else
            {
                $this->Flash->error('Invalid user type.');
            }
        }
        else
        {
            $this->Flash->error('Invalid action.');
        }
        
        
        $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
    }
    
    public function judgeexperience() {
        
        $this->userLoginCheck();
        $this->multiLoginCheck(['School','Teacher_Parent']);
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Judge Experience " . TITLE_FOR_PAGES);
        
        $this->set('active_judgeexp','active');
        
        $user_id = $this->request->session()->read("user_id");
        
        $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->contain(['Schools'])->first();
        $this->set('userDetails', $userDetails);
        
        $users = $this->Users->get($user_id);

        if ($this->request->is(['post', 'put'])) {
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if (count($data->getErrors()) == 0) {
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('Judge experience details updated successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
                }
            }
        }
        $this->set('users', $users);
        //pr($users);exit;
        
    }
    
    public function logout() {

        $this->Flash->success('Logout successfully.');
        $this->request->session()->delete('user_id');
        $this->request->session()->delete('email_address');
        $this->request->session()->delete('user_type');
        
        $this->request->session()->delete('sess_selected_convention_registration_id');
        $this->request->session()->delete('sess_selected_convention_id');
        $this->request->session()->delete('current_session_profile_type');
        
        $this->redirect(['controller' => 'users', 'action' => 'login']);
    }
    
    public function logintotest($user_id=null) {
        
        $userInfo = $this->Users->find()->where(['Users.id' => $user_id])->first();
        
        $this->request->session()->write("user_id", $userInfo->id);
        $this->request->session()->write("email_address", $userInfo->email_address);
        $this->request->session()->write("user_type", $userInfo->user_type);
        $this->request->session()->write("last_login", $userInfo->last_login);
        
        if($userInfo->user_type == "Teacher_Parent" && $userInfo->is_judge == "1")
        {
            $this->request->session()->write("current_session_profile_type", 'Supervisor');
        }
        
        $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
        
    }
    
    public function judgingform($event_id_number = '055') {
        //echo 'ddd';exit;
        $this->userLoginCheck();
        
        $user_id 	= $this->request->session()->read("user_id");
        $user_type 	= $this->request->session()->read("user_type");
        
        //echo ' fsdf sdf sdf d';exit;
        $this->viewBuilder()->setLayout("home");		
        $this->set('title_for_layout', 'Judging Form '.TITLE_FOR_PAGES);
        
        $this->set('active_dashboard', 'active');
        
        $userDetails = null;
        if (!empty($user_id)) {
            $userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        }
        $this->set('userDetails', $userDetails);
        
        //$event_id_number = '055';
        
        // now fetch the form based on event id number
        $condEvalForm = array();
        $condEvalForm[] = "(Evaluationforms.event_id_numbers LIKE '".$event_id_number."' OR Evaluationforms.event_id_numbers LIKE '".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number."')";
        
        $evalFormD = $this->Evaluationforms->find()->where($condEvalForm)->first();
        //$this->prx($evalFormD);
        
        $this->set('evalFormD', $evalFormD);
        
        
        if ($this->request->is(['post'])) {
            
            $this->prx($this->request->getData());
        }
        
        
    }

 

}

?>