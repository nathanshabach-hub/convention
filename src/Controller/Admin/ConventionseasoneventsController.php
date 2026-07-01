<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

#[\AllowDynamicProperties]
class ConventionseasoneventsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Events.name' => 'asc']];

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
		
		$this->loadModel('Divisions');
    }
	
	public function allevents() {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Events');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
		
        $condition = array();
		
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		
		$this->set('convSeasonD', $convSeasonD);
		
		$condition[] = "(Conventionseasonevents.conventionseasons_id = '".$convSeasonD->id."')";
		
		$conventionseasonevents = $this->Conventionseasonevents->find()->contain(['Events'])->where($condition)->order(["Conventionseasonevents.id" => "DESC"])->all();
		$this->set('conventionseasonevents', $conventionseasonevents);
    }
}

?>
