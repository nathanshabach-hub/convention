<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

#[\AllowDynamicProperties]
class JudgeevaluationsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];

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
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Eventsubmissions');
        $this->loadModel('Users');
		$this->loadModel('Judgeevaluationmarks');
    }
	
	public function index() {

        $this->set('title', ADMIN_TITLE . 'Judge Evaluations');
        $this->viewBuilder()->setLayout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('judgeEvaluationsList', '1');
		
		$conventionsDD = $this->Conventions->find()->where([])->order(['Conventions.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('conventionsDD', $conventionsDD);
		
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'DESC'])->combine('season_year', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);
		
		$eventsDD = array();
		$eventsList = $this->Events->find()->where([])->order(['Events.event_name' => 'DESC'])->all();
		foreach($eventsList as $eventl)
		{
			$eventsDD[$eventl->id] = $eventl->event_name. ' ('.$eventl->event_id_number.')';
		}
		$this->set('eventsDD', $eventsDD);
		
		$separator = array();
        $condition = array();
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			$condition[] = "(Judgeevaluations.conventionseason_id = '".$sess_admin_header_season_id."')";
		}
		
		if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Judgeevaluations->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Judgeevaluations->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Judgeevaluations->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Judgeevaluations']['convention_id']) && $this->request->getData()['Judgeevaluations']['convention_id'] != '') {
                $convention_id = trim($this->request->getData()['Judgeevaluations']['convention_id']);
            }
			if (isset($this->request->getData()['Judgeevaluations']['season_year']) && $this->request->getData()['Judgeevaluations']['season_year'] != '') {
                $season_year = trim($this->request->getData()['Judgeevaluations']['season_year']);
            }
			if (isset($this->request->getData()['Judgeevaluations']['event_id']) && $this->request->getData()['Judgeevaluations']['event_id'] != '') {
                $event_id = trim($this->request->getData()['Judgeevaluations']['event_id']);
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

        /* if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Judgeevaluations.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Judgeevaluations.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        } */
		
		if (isset($event_id) && $event_id != '') {
            $separator[] = 'event_id:' . urlencode($event_id);
            $condition[] = "(Judgeevaluations.event_id = '".addslashes($event_id)."')";
            $this->set('event_id', $event_id);
        }
		
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $judgeEvaluations = $this->Judgeevaluations->find()
            ->contain(['Eventsubmissions','Conventionregistrations','Conventions','Events','Students','Schools','Judge','Judgeevaluationmarks'])
            ->where($condition)
            ->order(['Judgeevaluations.id' => 'DESC'])
            ->all();
        $judgeUserIds = [];
        foreach ($judgeEvaluations as $judgeEvaluation) {
            if (!empty($judgeEvaluation->uploaded_by_user_id)) {
                $judgeUserIds[] = (int)$judgeEvaluation->uploaded_by_user_id;
            }
        }
        $judgeUsersById = [];
        if (!empty($judgeUserIds)) {
            $judgeUsers = $this->Users->find()
                ->where(['Users.id IN' => array_values(array_unique($judgeUserIds))])
                ->all();
            foreach ($judgeUsers as $judgeUser) {
                $nameParts = [
                    trim(isset($judgeUser->title) ? (string)$judgeUser->title : ''),
                    trim(isset($judgeUser->first_name) ? (string)$judgeUser->first_name : ''),
                    trim(isset($judgeUser->middle_name) ? (string)$judgeUser->middle_name : ''),
                    trim(isset($judgeUser->last_name) ? (string)$judgeUser->last_name : ''),
                ];
                $nameParts = array_values(array_filter($nameParts, function ($part) {
                    return $part !== '';
                }));

                $judgeName = trim(implode(' ', $nameParts));
                if ($judgeName === '') {
                    $judgeName = trim(isset($judgeUser->email_address) ? (string)$judgeUser->email_address : '');
                }
                if ($judgeName === '') {
                    $judgeName = 'User #' . $judgeUser->id;
                }

                $judgeUsersById[$judgeUser->id] = $judgeName;
            }
        }
        $this->set('judgeevaluations', $judgeEvaluations);
        $this->set('judgeUsersById', $judgeUsersById);
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Judgeevaluations');
            $this->render('index');
        }
    }
	
	public function removejudgeevaluation($evaluation_slug=null) {
		
		$judgeEvalD = $this->Judgeevaluations->find()->where(['Judgeevaluations.slug' => $evaluation_slug])->first();
		if($judgeEvalD)
		{
			$this->Judgeevaluations->deleteAll(["slug" => $evaluation_slug]);
			
			// remove evaluation questions marks
			$this->Judgeevaluationmarks->deleteAll(["judgeevaluation_id" => $judgeEvalD->id]);
			$this->Flash->success('Judge evaluation removed successfully.');
		}
		else
		{
			$this->Flash->error('Judge evaluation not found.');
		}
		
		$this->redirect(['controller' => 'judgeevaluations', 'action' => 'index']);
    }
	
	public function timesscoreedit($evaluation_slug = null) {
		
		$this->set('title', ADMIN_TITLE . 'Edit Times Score');
        $this->viewBuilder()->setLayout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('judgeEvaluationsList', '1');
		
        if ($evaluation_slug) {
            $judgeEvalD = $this->Judgeevaluations->find()->where(['Judgeevaluations.slug' => $evaluation_slug])->contain(['Events','Schools','Students'])->first();
			$this->set('judgeEvalD', $judgeEvalD);
            $uid = $judgeEvalD->id;
        }
		
        $judgeevaluations = $this->Judgeevaluations->get($uid);
        if ($this->request->is(['post', 'put']))
		{
			//$this->prx($this->request->getData());
			
			$this->Judgeevaluations->updateAll(
			[
				'time_score' 		=> $this->request->getData()['Judgeevaluations']['time_score'],
				'place' 			=> $this->request->getData()['Judgeevaluations']['place'],
				'withdraw_yes_no' 	=> $this->request->getData()['Judgeevaluations']['withdraw_yes_no'],
				'modified'			=> date("Y-m-d H:i:s")
			], 
			['slug' => $evaluation_slug]
			);
			
            $this->Flash->success('Times score updated successfully.');
            $this->redirect(['controller' => 'judgeevaluations', 'action' => 'index']);
        }
        $this->set('judgeevaluations', $judgeevaluations);
    }

}

?>
