<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

#[\AllowDynamicProperties]
class ConventionregistrationteachersController extends AppController {

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
		$this->loadModel("Users");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Conventionregistrationstudents");
    }
	
	public function allteachers() {

        $this->set('title', ADMIN_TITLE . 'Convention Registrations Teachers');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
		
        $condition = array();
		
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		
		$condition[] = "(Conventionregistrationteachers.convention_id = '".$convSeasonD->convention_id."' AND Conventionregistrationteachers.season_id = '".$convSeasonD->season_id."' AND Conventionregistrationteachers.season_year = '".$convSeasonD->season_year."')";
		
		$conventionregistrationteachers = $this->Conventionregistrationteachers->find()->contain(['Users','Teachers'])->where($condition)->order(["Conventionregistrationteachers.id" => "DESC"])->all();

		// Deduplicate: keep only the latest registration per teacher (highest id comes first)
		$seen = [];
		$unique = [];
		foreach ($conventionregistrationteachers as $record) {
			if (!in_array($record->teacher_id, $seen)) {
				$seen[] = $record->teacher_id;
				$unique[] = $record;
			}
		}
		$this->set('conventionregistrationteachers', new \Cake\Collection\Collection($unique));
    }
}

?>
