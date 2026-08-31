<?php
namespace App\Controller\Admin;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\Datasource\ConnectionManager;

#[\AllowDynamicProperties]
class UsersController extends AppController{

    public $paginate = ['limit' => 50];
   
    public function initialize(): void{
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
                 $this->redirect(['controller'=>'admins', 'action' => 'login']);
            }
        }
        
        $this->loadModel('Emailtemplates');
        $this->loadModel('Admins');
    }
    
 
    /*School Module*/
    
    public function index() {
        $this->set('title', ADMIN_TITLE. 'Manage Schools/Homeschools');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolList', '1');
        
        $separator = array();
        $condition = array();
        
        $condition[] = "(Users.user_type = 'School')";
        
        if($this->request->is('post')){
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
            
            if(isset($this->request->getData()['Users']['keyword']) && $this->request->getData()['Users']['keyword']!=''){
              $keyword = trim($this->request->getData()['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass', [])[0]) && $this->request->getParam('pass', [])[0]!=''){
                $searchArr = $this->request->getParam('pass', []);
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.customer_code LIKE '%".addslashes($keyword)."%' OR  Users.first_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 50, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Users');
            $this->render('index');
        }
    }
    
    public function activateuser($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/deactivateuser/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function deactivateuser($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '0'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/activateuser/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function archiveuser($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('School details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'index']);
    }
    
    public function restoreuser($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('School details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'index']);
    }
    
    public function resetaccount($slug=null) {
        if ($slug != '') {
            $this->Users->updateAll(
                ['status' => '1', 'activation_status' => '1'],
                ['slug' => $slug]
            );
            $this->Flash->success('Account has been reset successfully. The school can now log in.');
        }
        $this->redirect(['controller' => 'users', 'action' => 'index']);
    }
    
    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add School/Homeschool');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolAdd', '1');
        
        $users = $this->Users->newEntity([]);
        if ($this->request->is('post')) {
            
            $data = $this->Users->patchEntity($users, $this->request->getData(), ['validate' => 'add']);
            if (count($data->getErrors()) == 0) {

                $flagCheck = 1;
                
                $checkCCode = $this->Users->find()->where(['Users.customer_code' => $data->customer_code])->first();
                if($checkCCode)
                {
                    $flagCheck = 0;
                    $this->Flash->success('Customer code already exists.');
                }
                
                if($flagCheck == 1)
                {
                    $slug = $this->getSlug($this->request->getData()['Users']['first_name'] . ' ' . time(), 'Users');
                    $data->slug = $slug;
                    
                    $data->user_type = 'School';
                    $data->status = 1;
                    $data->activation_status = 0;
                    $data->created = date('Y-m-d H:i:s');
                    $data->modified = date('Y-m-d H:i:s');
                    if ($this->Users->save($data)) {
                        $this->Flash->success('School details added successfully. School admin need to verify account from front end.');
                        $this->redirect(['controller' => 'users', 'action' => 'index']);
                    }
                }
                
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
    
    public function add_custom_query() {
        $this->set('title', ADMIN_TITLE . 'Add School/Homeschool');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolAdd', '1');
        
        $users = $this->Users->newEntity([]);
        if ($this->request->is('post')) {
            
            $data = $this->Users->patchEntity($users, $this->request->getData(), ['validate' => 'add']);
            if (count($data->getErrors()) == 0) {

                $flagCheck = 1;
                
                $checkCCode = $this->Users->find()->where(['Users.customer_code' => $data->customer_code])->first();
                if($checkCCode)
                {
                    $flagCheck = 0;
                    $this->Flash->error('Customer code already exists.');
                }
                
                if($flagCheck == 1)
                {
                    
                    $conn = ConnectionManager::get('default');
                    
                    $slug = $this->getSlug($this->request->getData()['Users']['first_name'] . ' ' . time(), 'Users');
                    
                    $queryAdd = "INSERT INTO users 
                    (
                    `slug`,`user_type`,`customer_code`,
                    `first_name`,`middle_name`,`phone`,
                    `phone2`,`email_address`,`bill_to_street`,
                    `bill_to_block`,`bill_to_city`,`bill_to_zip`,
                    `bill_to_country`,`status`,`activation_status`,
                    `created`,`modified`
                    )
                    VALUES
                    (
                    '".$slug."','School','".$data->customer_code."',
                    '".$data->first_name."','".$data->middle_name."','".$data->phone."',
                    '".$data->phone2."','".$data->email_address."','".$data->bill_to_street."',
                    '".$data->bill_to_block."','".$data->bill_to_city."','".$data->bill_to_zip."',
                    '".$data->bill_to_country."','1','0',
                    '".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."'
                    )
                    ";
                    
                    $stmt = $conn->execute($queryAdd);
                    $this->Flash->success('School details added successfully. School admin need to verify account from front end.');
                    $this->redirect(['controller' => 'users', 'action' => 'index']);
                }
                
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
    
    public function edit($slug=null){
        $this->set('title', ADMIN_TITLE. 'Edit School');
        $this->viewBuilder()->setLayout('admin');
        
        $this->set('manageSchools', '1');
        $this->set('schoolList', '1');
       
        if($slug){
            $userD = $this->Users->find()->where(['Users.slug' => $slug])->first();
            $uid = $userD->id;
            $this->set('userD', $userD);
        }
        $users = $this->Users->get($uid);
        if ($this->request->is(['post', 'put'])) {
            if(empty($this->request->getData()['Users']['password'])){
                unset($this->request->getData()['Users']['password']);
            }
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            $flagCheck = 1;
            if($data->email_address_old != $data->email_address)
            {
                $checkUE = $this->Users->find()->where(['Users.email_address' => $data->email_address])->first();
                if($checkUE)
                {
                    $flagCheck = 0;
                    $this->Flash->error('Email address already exists.');
                }
                
                $checkUA = $this->Admins->find()->where(['Admins.email' => $data->email_address])->first();
                if($checkUA)
                {
                    $flagCheck = 0;
                    $this->Flash->error('Email address already exists.');
                }
            }
            
            if(count($data->getErrors()) == 0 && $flagCheck == 1){
               
                if(isset($this->request->getData()['Users']['password']) && $this->request->getData()['Users']['password'] !=''){
                    $new_password = $this->request->getData()['Users']['password'];
                    unset($this->request->getData()['Users']['password']);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $data->password = $password;
                }
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('School details updated successfully.');
                    $this->redirect(['controller'=>'users', 'action' => 'index']);
                }
                
            }else{
                if(empty($this->request->getData()['Users']['password'])){
                    $this->request->getData()['Users']['password'] = ''; 
                }
            }
        }else{
             $this->request->getData()['Users']['password'] = '';
        }
        $this->set('users', $users);
    }
    
    
    
    /* Teacher Module */
    
    public function teachers() {
        $this->set('title', ADMIN_TITLE. 'Manage Supervisors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageTeachers', '1');
        $this->set('teacherList', '1');
        
        $separator = array();
        $condition = array();
        
        $condition[] = "(Users.user_type = 'Teacher_Parent')";
        
        if($this->request->is('post')){
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
            
            if(isset($this->request->getData()['Users']['keyword']) && $this->request->getData()['Users']['keyword']!=''){
              $keyword = trim($this->request->getData()['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass', [])[0]) && $this->request->getParam('pass', [])[0]!=''){
                $searchArr = $this->request->getParam('pass', []);
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 20, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Users');
            $this->render('teachers');
        }
    }
    
    public function activateteacher($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/deactivateteacher/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function deactivateteacher($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '0'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/activateteacher/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function archiveteacher($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('Supervisors details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
    
    public function restoreteacher($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('Supervisors details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'teachers']);
    }
    
    public function addteacher_noneed() {
        $this->set('title', ADMIN_TITLE . 'Add Supervisors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageTeachers', '1');
        $this->set('teacherAdd', '1');
        
        $schoolsDD = $this->Users->find()->where(['Users.user_type' => 'School'])->order(['Users.first_name' => 'ASC'])->combine('id', 'first_name')->toArray();
        $this->set('schoolsDD', $schoolsDD);
        
        global $genderDD;
        $this->set('genderDD', $genderDD);
        
        global $yesNoDD;
        $this->set('yesNoDD', $yesNoDD);
        
        $users = $this->Users->newEntity([]);
        if ($this->request->is('post')) {
            
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            $flagC = 1;
            
            $checkEmailS = $this->Users->find()->where(['Users.email_address' => $data->email_address,'Users.school_id' => $data->school_id])->first();
            
            $checkEmailAdmin = $this->Admins->find()->where(['Admins.email' => $data->email_address])->first();
            if($checkEmailAdmin || $checkEmailS)
            {
                $flagC = 0;
                $this->Flash->error('Email already exists.');
            }
            
            if (count($data->getErrors()) == 0 && $flagC == 1) {

                $slug = $this->getSlug($this->request->getData()['Users']['first_name'] . ' ' . time(), 'Users');
                $data->slug = $slug;
                
                $data->user_type = 'Teacher_Parent';
                $data->status = 1;
                $data->activation_status = 0;
                $data->created = date('Y-m-d H:i:s');
                $data->modified = date('Y-m-d H:i:s');
                if ($this->Users->save($data)) {
                    $this->Flash->success('Supervisors details added successfully.');
                    $this->redirect(['controller' => 'users', 'action' => 'teachers']);
                }
                
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('users', $users);
    }
    
    public function editteacher($slug=null){
        $this->set('title', ADMIN_TITLE. 'Edit Supervisors');
        $this->viewBuilder()->setLayout('admin');
        
        $this->set('manageTeachers', '1');
        $this->set('teacherList', '1');
        
        $schoolsDD = $this->Users->find()->where(['Users.user_type' => 'School'])->order(['Users.first_name' => 'ASC'])->combine('id', 'first_name')->toArray();
        $this->set('schoolsDD', $schoolsDD);
        
        global $genderDD;
        $this->set('genderDD', $genderDD);
        
        global $yesNoDD;
        $this->set('yesNoDD', $yesNoDD);
       
        if($slug){
            $users1 = $this->Users->find()->where(['Users.slug' => $slug])->first();
            $uid = $users1->id;
        }
        $users = $this->Users->get($uid);
        if ($this->request->is(['post', 'put'])) {
            if(empty($this->request->getData()['Users']['password'])){
                unset($this->request->getData()['Users']['password']);
            }
            $data = $this->Users->patchEntity($users, $this->request->getData());
            
            if(count($data->getErrors()) == 0){
               
                if(isset($this->request->getData()['Users']['password']) && $this->request->getData()['Users']['password'] !=''){
                    $new_password = $this->request->getData()['Users']['password'];
                    unset($this->request->getData()['Users']['password']);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $data->password = $password;
                }
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('Supervisors details updated successfully.');
                    $this->redirect(['controller'=>'users', 'action' => 'teachers']);
                }
                
            }else{
                if(empty($this->request->getData()['Users']['password'])){
                    $this->request->getData()['Users']['password'] = ''; 
                }
            }
        }else{
             $this->request->getData()['Users']['password'] = '';
        }
        $this->set('users', $users);
    }
    
    
    /* Student module starts */
    
    public function students() {
        $this->set('title', ADMIN_TITLE. 'Manage Students');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageStudents', '1');
        $this->set('studentList', '1');
        
        $separator = array();
        $condition = array();
        
        $condition[] = "(Users.user_type = 'Student')";
        
        if($this->request->is('post')){
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
            
            if(isset($this->request->getData()['Users']['keyword']) && $this->request->getData()['Users']['keyword']!=''){
              $keyword = trim($this->request->getData()['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass', [])[0]) && $this->request->getParam('pass', [])[0]!=''){
                $searchArr = $this->request->getParam('pass', []);
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.middle_name LIKE '%".addslashes($keyword)."%' OR  Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 20, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout("");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Users');
            $this->render('students');
        }
    }
    
    public function activatestudent($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/deactivateparent/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function deactivatestudent($slug=null){
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Users->updateAll(['status' => '0'], ["slug"=>$slug]);
            $this->set('action', '/admin/users/activateparent/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
    
    public function archivestudent($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('Student details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
    
    public function restorestudent($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('Students details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'students']);
    }
    
    public function editstudent($slug=null){
        $this->set('title', ADMIN_TITLE. 'Edit Student');
        $this->viewBuilder()->setLayout('admin');
        
        $this->set('manageStudents', '1');
        $this->set('studentList', '1');
        
        global $genderDD;
        $this->set('genderDD', $genderDD);
        
        global $birthYearDD;
        $this->set('birthYearDD', $birthYearDD);
        
        $schoolsDD = $this->Users->find()->where(['Users.user_type' => 'School'])->order(['Users.first_name' => 'ASC'])->combine('id', 'first_name')->toArray();
        $this->set('schoolsDD', $schoolsDD);
       
        if($slug){
            $users1 = $this->Users->find()->where(['Users.slug' => $slug])->first();
            $uid = $users1->id;
        }
        $users = $this->Users->get($uid);
        if ($this->request->is(['post', 'put'])) {
            if(empty($this->request->getData()['Users']['password'])){
                unset($this->request->getData()['Users']['password']);
            }
            $data = $this->Users->patchEntity($users, $this->request->getData(), ['validate' => 'edit']);
            
            if(count($data->getErrors()) == 0){
               
                if(isset($this->request->getData()['Users']['password']) && $this->request->getData()['Users']['password'] !=''){
                    $new_password = $this->request->getData()['Users']['password'];
                    unset($this->request->getData()['Users']['password']);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $data->password = $password;
                }
                
                if ($this->Users->save($data)) {
                    $this->Flash->success('Student details updated successfully.');
                    $this->redirect(['controller'=>'users', 'action' => 'students']);
                }
                
            }else{
                if(empty($this->request->getData()['Users']['password'])){
                    $this->request->getData()['Users']['password'] = ''; 
                }
            }
        }else{
             $this->request->getData()['Users']['password'] = '';
        }
        $this->set('users', $users);
    }
    
    
    /* CSV Functions */
    public function downloadcsvformat() {
        
        $filename = "schools_standard_csv_format.csv";
        $dataArray = array();
        
        $dataArray[] = array('7732','TEMP001','A B Customer','38815777','','mikaelawaqa@accelerate.edu.au','8-12 Business Drive','','Narangba','4504','AU');
        $dataArray[] = array('7733','TEMP002','X Y Customer','34532456','','abc@accelerate.edu.au','12-13 Business Drive','','Narangba','4504','AU');
        
        $delimiter = ",";
         
        $f = fopen('php://memory', 'w'); 
         
        $fields = array('#','BP Code','BP Name','Telephone 1','Telephone 2','E-Mail','Bill-to Street','Bill-to Block','Bill-to City','Bill-to Zip Code','Bill-to Country');
        fputcsv($f, $fields, $delimiter); 
         
        foreach($dataArray as $datarecord)
        {	
            fputcsv($f, $datarecord, $delimiter);
        } 
         
        fseek($f, 0); 
         
        header('Content-Type: text/csv'); 
        header('Content-Disposition: attachment; filename="' . $filename . '";'); 
         
        fpassthru($f); 
        
        exit;
    }
    
    public function csvimport() {
        $this->set('title', ADMIN_TITLE . 'Import CSV');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageSchools', '1');
        $this->set('schoolImport', '1');
        
        $users = $this->Users->newEntity([]);
        if ($this->request->is('post')) {
            
            $data = $this->Users->patchEntity($users, $this->request->getData());
            if (count($data->getErrors()) == 0) {

                if(!empty($this->request->getData()['Users']['csv_file']['name'])){
                    $specialCharacters = array('#', '$', '%', '@', '+', '=', '\\', '/', '"', ' ', "'", ':', '~', '`', '!', '^', '*', '(', ')', '|', "'", "&");
                    $toReplace = "-";
                    $this->request->getData()['Users']['csv_file']['name'] = str_replace($specialCharacters, $toReplace, $this->request->getData()['Users']['csv_file']['name']);
                    $imageArray = $this->request->getData()['Users']['csv_file'];
                    $returnedUploadImageArray = $this->PImage->upload($imageArray, UPLOAD_SCHOOLS_CSV_PATH);                     
                     
                    $csv_file_system_name 	=  $returnedUploadImageArray[0];
                    $csv_original_name 		= 	$this->request->getData()['Users']['csv_file']['name'];
                    
                    $filename = UPLOAD_SCHOOLS_CSV_PATH.$csv_file_system_name;
                    
                    $cntrTotalRecords 		= 0;
                    $cntrCustCodeExists 	= 0;
                    $cntrCustEmailExists 	= 0;
                    $cntrRecordsImport 		= 0;
                    
                    $file = fopen($filename, "r");
                    while (($getData = fgetcsv($file, 10000, ",")) !== FALSE)
                    {
                        if($cntrTotalRecords>0)
                        {	
                            $flagCheck = 1;
                            
                            $checkCCode = $this->Users->find()->where(['Users.customer_code' => $getData[1]])->first();
                            if($checkCCode)
                            {
                                $cntrCustCodeExists++;
                                $flagCheck = 0;
                            }
                            
                            if($flagCheck == 1)
                            {
                                $checkEmail = $this->Users->find()->where(['Users.email_address' => $getData[5]])->first();
                                if($checkEmail)
                                {
                                    $cntrCustEmailExists++;
                                    $flagCheck = 0;
                                }
                            }
                            
                            if($flagCheck == 1)
                            {
                                $users = $this->Users->newEntity([]);
                                $dataU = $this->Users->patchEntity($users, array());
                                
                                $dataU->slug 							= $this->getSlug($getData[2] . ' ' . time(), 'Users');
                                $dataU->user_type						= "School";
                                $dataU->status							= 1;
                                $dataU->activation_status				= 0;
                                
                                $dataU->customer_hash_from_csv			= $getData[0];
                                $dataU->customer_code					= $getData[1];
                                $dataU->first_name						= $getData[2];
                                $dataU->phone							= $getData[3];
                                $dataU->phone2							= $getData[4];
                                $dataU->email_address					= $getData[5];
                                $dataU->bill_to_street					= $getData[6];
                                $dataU->bill_to_block					= $getData[7];
                                $dataU->bill_to_city					= $getData[8];
                                $dataU->bill_to_zip						= $getData[9];
                                $dataU->bill_to_country					= $getData[10];
                                
                                $user_password = rand(1000,33455678899000);
                                $salt = uniqid(mt_rand(), true);
                                $dataU->password = crypt($user_password, '$2a$07$' . $salt . '$');
                                $dataU->created 				= date('Y-m-d H:i:s');

                                $resultU = $this->Users->save($dataU);
                                
                                $cntrRecordsImport++;
                            }
                        }
                        
                        $cntrTotalRecords++;
                    }
                    
                    @unlink($filename);
                    
                    $this->Flash->success("Total records in csv file = ".($cntrTotalRecords-1));
                    if($cntrCustCodeExists > 0)
                    {
                        $this->Flash->error("Customer code already exists = ".$cntrCustCodeExists);
                    }
                    
                    if($cntrCustEmailExists > 0)
                    {
                        $this->Flash->error("Email already exists = ".$cntrCustEmailExists);
                    }
                    
                    $this->Flash->success("Total records import = ".$cntrRecordsImport);
                    $this->redirect(['controller'=>'users', 'action' => 'index']);
                     
                }
                else
                {
                    $this->Flash->error('CSV import process failed.');
                    $this->redirect(['controller'=>'users', 'action' => 'index']);
                }
            }
        }
        $this->set('users', $users);
    }
    
    
    /*Judges Module */
    
    public function judges() {
        $this->set('title', ADMIN_TITLE. ' Judges');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageJudges', '1');
        $this->set('activeJudges', '1');
        
        $separator = array();
        $condition = array();
        
        $condition[] = "(Users.status = '0' OR Users.status = '1' OR Users.status = '2')";
        $condition[] = "(Users.user_type = 'Judge' OR (Users.user_type = 'Teacher_Parent' AND Users.is_judge = '1'))";
        
        if($this->request->is('post')){
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
            
            if(isset($this->request->getData()['Users']['keyword']) && $this->request->getData()['Users']['keyword']!=''){
              $keyword = trim($this->request->getData()['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass', [])[0]) && $this->request->getParam('pass', [])[0]!=''){
                $searchArr = $this->request->getParam('pass', []);
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 50, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->disableAutoLayout();
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Users');
            $this->render('judges');
        }
    }

    public function activatejudgeaccount($slug=null) {
        $judgeD = $this->Users->find()->where([
            'Users.slug' => $slug,
            '(Users.user_type = "Judge" OR (Users.user_type = "Teacher_Parent" AND Users.is_judge = "1"))',
            'Users.status IN' => [0, 1, 2]
        ])->first();

        if($judgeD)
        {
            $this->Users->updateAll([
                'activation_status' => '1',
                'status' => '1',
                'modified' => date('Y-m-d H:i:s', time())
            ], ['slug' => $slug]);

            $this->Flash->success('Judge account activated successfully. The judge can now log in without email verification.');
        }
        else
        {
            $this->Flash->error('Invalid action.');
        }

        return $this->redirect(['controller'=>'users', 'action' => 'judges']);
    }

    public function verifyaccount($slug=null, $returnAction='index') {
        $allowedActions = ['index', 'teachers', 'judges', 'pendingjudges'];
        $user = $this->Users->find()->where([
            'Users.slug' => $slug,
            'Users.user_type IN' => ['School', 'Teacher_Parent', 'Judge']
        ])->first();

        if ($user && in_array($returnAction, $allowedActions, true)) {
            $this->Users->updateAll([
                'activation_status' => '1',
                'modified' => date('Y-m-d H:i:s')
            ], ['id' => $user->id]);
            $this->Flash->success('Account verified successfully.');
        } else {
            $this->Flash->error('Invalid action.');
            $returnAction = 'index';
        }

        return $this->redirect(['controller' => 'users', 'action' => $returnAction]);
    }

    public function unverifyaccount($slug=null, $returnAction='index') {
        $allowedActions = ['index', 'teachers', 'judges', 'pendingjudges'];
        $user = $this->Users->find()->where([
            'Users.slug' => $slug,
            'Users.user_type IN' => ['School', 'Teacher_Parent', 'Judge']
        ])->first();

        if ($user && in_array($returnAction, $allowedActions, true)) {
            $this->Users->updateAll([
                'activation_status' => '0',
                'modified' => date('Y-m-d H:i:s')
            ], ['id' => $user->id]);
            $this->Flash->success('Account marked as unverified.');
        } else {
            $this->Flash->error('Invalid action.');
            $returnAction = 'index';
        }

        return $this->redirect(['controller' => 'users', 'action' => $returnAction]);
    }

    public function activatejudge($slug=null) {
        return $this->updateJudgeStatus($slug, 1);
    }

    public function deactivatejudge($slug=null) {
        return $this->updateJudgeStatus($slug, 0);
    }

    private function updateJudgeStatus($slug, $status) {
        $judge = $this->Users->find()->where([
            'Users.slug' => $slug,
            '(Users.user_type = "Judge" OR (Users.user_type = "Teacher_Parent" AND Users.is_judge = "1"))',
            'Users.status IN' => [0, 1, 2]
        ])->first();

        if ($judge) {
            $this->Users->updateAll([
                'status' => (string)$status,
                'modified' => date('Y-m-d H:i:s')
            ], ['id' => $judge->id]);
            $this->Flash->success($status ? 'Judge account activated successfully.' : 'Judge account deactivated successfully.');
        } else {
            $this->Flash->error('Invalid action.');
        }

        return $this->redirect(['controller' => 'users', 'action' => 'judges']);
    }

    public function editjudge($slug=null){
        $this->set('title', ADMIN_TITLE. 'Edit Judge');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageJudges', '1');
        $this->set('activeJudges', '1');

        $schoolsDD = $this->Users->find()->where(['Users.user_type' => 'School'])->order(['Users.first_name' => 'ASC'])->combine('id', 'first_name')->toArray();
        $this->set('schoolsDD', $schoolsDD);

        global $genderDD;
        $this->set('genderDD', $genderDD);

        global $yesNoDD;
        $this->set('yesNoDD', $yesNoDD);

        if($slug){
            $users1 = $this->Users->find()->where([
                'Users.slug' => $slug,
                "(Users.user_type = 'Judge' OR (Users.user_type = 'Teacher_Parent' AND Users.is_judge = '1'))"
            ])->first();
            if(!$users1){
                $this->Flash->error('Invalid judge record.');
                return $this->redirect(['controller'=>'users', 'action' => 'judges']);
            }
            $uid = $users1->id;
        }

        $users = $this->Users->get($uid);
        if ($this->request->is(['post', 'put'])) {
            if(empty($this->request->getData()['Users']['password'])){
                unset($this->request->getData()['Users']['password']);
            }
            $data = $this->Users->patchEntity($users, $this->request->getData());

            if(count($data->getErrors()) == 0){

                if(isset($this->request->getData()['Users']['password']) && $this->request->getData()['Users']['password'] !=''){
                    $new_password = $this->request->getData()['Users']['password'];
                    unset($this->request->getData()['Users']['password']);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $data->password = $password;
                }

                if ($this->Users->save($data)) {
                    $this->Flash->success('Judge details updated successfully.');
                    return $this->redirect(['controller'=>'users', 'action' => 'judges']);
                }

            }else{
                if(empty($this->request->getData()['Users']['password'])){
                    $this->request->getData()['Users']['password'] = '';
                }
            }
        }else{
             $this->request->getData()['Users']['password'] = '';
        }
        $this->set('users', $users);
    }
    
    public function pendingjudges() {
        $this->set('title', ADMIN_TITLE. ' Pending Judges');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageJudges', '1');
        $this->set('pendingJudges', '1');
        
        $separator = array();
        $condition = array();
        
        $condition[] = "( 
            (Users.user_type = 'Judge' AND Users.status = '0' AND Users.activation_status = '1')
            OR 
            (Users.user_type = 'Teacher_Parent' AND Users.is_judge = '2' AND Users.status = '1' AND Users.activation_status = '1') 
            )";
        
        if($this->request->is('post')){
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
            
            if(isset($this->request->getData()['Users']['keyword']) && $this->request->getData()['Users']['keyword']!=''){
              $keyword = trim($this->request->getData()['Users']['keyword']); 
            }
        }elseif($this->request->getParam('pass')){
            if(isset($this->request->getParam('pass', [])[0]) && $this->request->getParam('pass', [])[0]!=''){
                $searchArr = $this->request->getParam('pass', []);
                foreach($searchArr as $val){
                if (strpos($val, ":") !== false) {
                   $vars  = explode(":",$val);
                   ${$vars[0]}   = urldecode($vars[1]);
                }
               }
            }
        }
        
        if (isset($keyword) && $keyword != '') {
             $separator[] = 'keyword:' . urlencode($keyword);
             $condition[] = "(Users.first_name LIKE '%".addslashes($keyword)."%' OR Users.last_name LIKE '%".addslashes($keyword)."%' OR  Users.email_address LIKE '%".addslashes($keyword)."%')";
             $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator); 
        $this->set('separator',$separator);
        $this->paginate = ['contain'=>['Schools'],'conditions' => $condition, 'limit' => 50, 'order' => ['Users.id' => 'DESC']];
        $this->set('users', $this->paginate($this->Users));
        if($this->request->is("ajax")){
            $this->viewBuilder()->disableAutoLayout();
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Users');
            $this->render('pendingjudges');
        }
    }
    
    public function approvejudge($slug=null) {
        
        $judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.status' => 0])->first();
        if($judgeD)
        {
            $this->Users->updateAll(['status' => '1','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
            
            $emailId = $judgeD->email_address;
                            
            $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '13'])->first();
            
            $LINK = HTTP_PATH."/users/login/";

            $toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!LINK!]');
            $fromRepArray = array(SITE_TITLE,$judgeD->first_name,$LINK);

            $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
            $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
            
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
            
            $this->Flash->success('Judge details approved successfully.');
        
        }
        else
        {
            $this->Flash->error('Invalid action.');
        }
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
    
    public function rejectjudge($slug=null) {
        
        $judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.status' => 0])->first();
        if($judgeD)
        {
            $this->Users->updateAll(['status' => '3','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
            
            $emailId = $judgeD->email_address;
                            
            $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '14'])->first();

            $toRepArray = array('[!SITE_TITLE!]','[!first_name!]');
            $fromRepArray = array(SITE_TITLE,$judgeD->first_name);

            $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
            $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
            
            echo $messageToSend; exit;
            
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
            
            $this->Flash->success('Judge details approved successfully.');
        
        }
        else
        {
            $this->Flash->error('Invalid action.');
        }
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
    
    public function archivejudge($slug=null) {
        $this->Users->updateAll(['status' => '2'], ["slug"=>$slug]);
        $this->Flash->success('Judge details archived successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'judges']);
    }
    
    public function restorejudge($slug=null) {
        $this->Users->updateAll(['status' => '1'], ["slug"=>$slug]);
        $this->Flash->success('Judge details restored successfully.');
        $this->redirect(['controller'=>'users', 'action' => 'judges']);
    }
    
    public function approvesupervisorasjudge($slug=null) {
        
        $judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.is_judge' => 2])->first();
        if($judgeD)
        {
            $this->Users->updateAll(['is_judge' => '1','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
            
            $emailId = $judgeD->email_address;
                            
            $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '16'])->first();
            
            $LINK = HTTP_PATH."/users/login/";

            $toRepArray = array('[!SITE_TITLE!]','[!first_name!]','[!LINK!]');
            $fromRepArray = array(SITE_TITLE,$judgeD->first_name,$LINK);

            $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
            $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
            
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
            
            $this->Flash->success('Supervisor account successfully approvedas judge.');
        
        }
        else
        {
            $this->Flash->error('Invalid action.');
        }
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
    
    public function rejectsupervisorasjudge($slug=null) {
        
        $judgeD = $this->Users->find()->where(['Users.slug' => $slug,'Users.is_judge' => 2])->first();
        if($judgeD)
        {
            $this->Users->updateAll(['is_judge' => '3','modified' => date('Y-m-d H:i:s', time())], ["slug"=>$slug]);
            
            $emailId = $judgeD->email_address;
                            
            $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '17'])->first();

            $toRepArray = array('[!SITE_TITLE!]','[!first_name!]');
            $fromRepArray = array(SITE_TITLE,$judgeD->first_name);

            $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
            $messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
            
            echo $messageToSend; exit;
            
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
            
            $this->Flash->success('Judge details approved successfully.');
        
        }
        else
        {
            $this->Flash->error('Invalid action.');
        }
        $this->redirect(['controller'=>'users', 'action' => 'pendingjudges']);
    }
    
}