<?php
namespace App\Controller\Admin;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

#[\AllowDynamicProperties]
class PagesController extends AppController{

    public $paginate = ['limit' => 50,'order' => ['Pages.name' => 'asc']];
    //public $helpers = array('Javascript', 'Ajax');
   
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
    }
    
    public function index() {
        $this->set('title', ADMIN_TITLE. 'Manage Content');
        $this->viewBuilder()->setLayout('admin');
        $this->set('staticPages', '1');
        $this->set('pageList', '1');
        
        $separator = array();
        $condition = array();
        
        $separator = implode("/", $separator);
        $this->set('separator',$separator); 
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Pages.static_page_title' => 'ASC']];
        $this->set('pages', $this->paginate($this->Pages));
        if($this->request->is("ajax")){
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Pages');
            $this->render('index');
        }
    }
  
   public function edit($slug=null){  
		$this->set('title', ADMIN_TITLE. 'Edit Page');
		$this->viewBuilder()->setLayout('admin');
       
		$this->set('staticPages', '1');
        $this->set('pageList', '1');
	   
		if($slug){
            $pages1 = $this->Pages->find()->where(['Pages.slug' => $slug])->first();
            $uid = $pages1->id;
		}
		$pages = $this->Pages->get($uid);
		if ($this->request->is(['post', 'put'])) {
            $data = $this->Pages->patchEntity($pages, $this->request->getData(), ['validate' => 'edit']);
            if(count($data->getErrors()) == 0){
                if ($this->Pages->save($data)) {
                    $this->Flash->success('Page details updated successfully.');
                    $this->redirect(['controller'=>'pages', 'action' => 'index']);
                }
            }else{
               // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('pages', $pages); 
    }
 
    public function pageimages($slug=null){  
        $uploadPagesImagePath = defined('UPLOAD_PAGES_IMAGE_PATH') ? constant('UPLOAD_PAGES_IMAGE_PATH') : ROOT . DS . 'webroot' . DS . 'files' . DS . 'pages' . DS;
        $displayPagesImagePath = defined('DISPLAY_PAGES_IMAGE_PATH') ? constant('DISPLAY_PAGES_IMAGE_PATH') : rtrim((string)Configure::read('App.fullBaseUrl'), '/') . '/webroot/files/pages/';
        $imageArray = $_FILES['upload'];
        $returnedUploadImageArray = $this->PImage->upload($imageArray, $uploadPagesImagePath);
        echo "<span style='font-size: 16px;  font-weight: bold;'>Copy below URL and Paste in next screen:</span> <span style='float: left; font-size: 14px; margin: 7px 0 0; width: 100%;'>" . $displayPagesImagePath . $returnedUploadImageArray[0].'</span>'; exit;
        if (file_exists($uploadPagesImagePath . $_FILES["upload"]["name"])){ 
         echo $_FILES["upload"]["name"] . " already exists. ";
        }else{
         move_uploaded_file($_FILES["upload"]["tmp_name"],
         $uploadPagesImagePath . $_FILES["upload"]["name"]);
         echo "Coty below URL and Paste in next screen: " . $displayPagesImagePath . $_FILES["upload"]["name"];
        }
        exit;
    }
    
}

?>