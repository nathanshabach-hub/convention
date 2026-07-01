<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Mailer\Email;

#[\AllowDynamicProperties]
class ConventionsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];
	public $Conventionseasons = null;
	public $Seasons = null;
	public $Events = null;
	public $Conventionseasonevents = null;
	public $Conventionregistrations = null;
	public $Conventionrooms = null;
	public $Conventionseasonroomevents = null;
	public $Conventionregistrationstudents = null;

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
		
		$this->loadModel('Conventionseasons');
		$this->loadModel('Seasons');
		$this->loadModel('Events');
		$this->loadModel('Conventionseasonevents');
		$this->loadModel('Conventionregistrations');
		$this->loadModel('Conventionrooms');
		$this->loadModel('Conventionseasonroomevents');
		$this->loadModel('Conventionregistrationstudents');
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Conventions');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		global $conventionTypeDD;
		$this->set('conventionTypeDD', $conventionTypeDD);

        $separator = array();
        $condition = array();
        //$condition = array('Conventions.parent_id' => 0);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// To get convention season details
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
			
			$condition[] = "(Conventions.id = '".$conventionSD->convention_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventions->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventions->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventions->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Conventions']['keyword']) && $this->request->getData()['Conventions']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Conventions']['keyword']);
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
            $condition[] = "(Conventions.name LIKE '%".addslashes($keyword)."%' OR Conventions.location LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Conventions.id' => 'DESC']];
        $this->set('conventions', $this->paginate($this->Conventions));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventions');
            $this->render('index');
        }
    }

    public function activateconvention($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Conventions->updateAll(['status' => '1'], ["slug" => $slug]);
            $this->set('action', '/admin/conventions/deactivateconvention/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivateconvention($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Conventions->updateAll(['status' => '0'], ["slug" => $slug]);
            $this->set('action', '/admin/conventions/activateconvention/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }
	
	public function deleteconvention($slug = null) {
        
		// first check that this convention exists
		$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
		if($conventionD)
		{
			// to check if this conventions linked with any other data
			$convention_id 	= $conventionD->id;
			$flagDelete = 1;
			
			//1. check in conventionseasons
			$checkConventionSeasons = $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $convention_id])->first();
			if($checkConventionSeasons)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention cannot delete. Convention is linked with Convention > Seasons.');
			}
			
			//2. check in conventionseasonevents
			$checkConventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.convention_id' => $convention_id])->first();
			if($checkConventionSeasonEvents)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention cannot delete. Convention is linked with Convention > Seasons > Events.');
			}
			
			if($flagDelete == 1)
			{
				$this->Conventions->deleteAll(["slug" => $slug]);
				$this->Flash->success('Convention details deleted successfully.');
			}
		}
		else
		{
			$this->Flash->error('Convention not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'index']);
    }

    public function add() {
        $this->set('title', ADMIN_TITLE . 'Add Convention');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionAdd', '1');
		
		global $conventionTypeDD;
		$this->set('conventionTypeDD', $conventionTypeDD);
		
        $conventions = $this->Conventions->newEntity([]);
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
            $data = $this->Conventions->patchEntity($conventions, $this->request->getData(), ['validate' => 'add']);
            if (count($data->getErrors()) == 0) {

				$slug = $this->getSlug($this->request->getData()['Conventions']['name'] . ' ' . time(), 'Conventions');
                $data->name = trim($this->request->getData()['Conventions']['name']);
                $data->slug = $slug;
                $data->status = 1;
                $data->created = date('Y-m-d');
                $data->modified = date('Y-m-d');
                if ($this->Conventions->save($data)) {
                    $this->Flash->success('Convention added successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventions', $conventions);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Convention');
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		global $conventionTypeDD;
		$this->set('conventionTypeDD', $conventionTypeDD);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
        if ($slug) {
            $categories1 = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
            $uid = $categories1->id;
        }
		
        $conventions = $this->Conventions->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Conventions->patchEntity($conventions, $this->request->getData(), ['validate' => 'edit']);
			
            if (count($data->getErrors()) == 0) {
                $data->name = trim($this->request->getData()['Conventions']['name']);
				$data->modified = date("Y-m-d");
                if ($this->Conventions->save($data)) {
                    $this->Flash->success('Convention details updated successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventions', $conventions);
    }
	
	public function seasons($slug=null) {

		if (!$slug) {
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
		if (!$conventionD) {
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('slug', $slug);
		$this->set('conventionD', $conventionD);
		
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Manage Seasons - '.$conventionD->name);
		
		// to get list of seasons
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'ASC'])->combine('id', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);
        
		$separator = array();
        $condition = array();
		$condition = array('Conventionseasons.convention_id' => $conventionD->id);
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->session()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id>0)
		{
			// To get convention season details
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
			
			$condition[] = "(Conventionseasons.id = '".$sess_admin_header_season_id."')";
		}

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionseasons->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionseasons->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionseasons->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Conventionseasons']['season_id']) && $this->request->getData()['Conventionseasons']['season_id'] != '') {
                $season_id = trim($this->request->getData()['Conventionseasons']['season_id']);
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

        if (isset($season_id) && $season_id != '') {
            $separator[] = 'season_id:' . urlencode($season_id);
            $condition[] = "(Conventionseasons.season_id = '".addslashes($season_id)."')";
            $this->set('season_id', $season_id);
        }
		
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
		$convseasonsQuery = $this->Conventionseasons->find()
			->contain(['Seasons'])
			->where($condition)
			->order(['Conventionseasons.season_year' => 'DESC']);
		$this->set('submissionsLockField', $this->getSubmissionsOpenColumn());
		$this->set('submissionsLockOverrides', (array)$this->request->session()->read('submissions_lock_overrides'));
		$this->set('convseasons', $this->paginate($convseasonsQuery, ['limit' => 20]));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventions');
            $this->render('seasons');
        }
    }
	
	public function addseason($slug=null) {
        
		if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Add Season - '.$conventionD->name);
		
		// to get list of seasons
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'ASC'])->combine('id', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);
		
        $conventionseasons = $this->Conventionseasons->newEntity([]);
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$flagC = 1;
			
			// to check registration start date must be less than end date
			if(strtotime($this->request->getData()['Conventionseasons']['registration_start_date']) > strtotime($this->request->getData()['Conventionseasons']['registration_end_date']))
			{
				$flagC = 0;
				$this->Flash->error('Registration start date must be less than end date.');
			}
			
			// to check if season already added for this convention or not
			$checkConvSeason	= $this->Conventionseasons->find()->where(['Conventionseasons.convention_id' => $conventionD->id,'Conventionseasons.season_id' => $this->request->getData()['Conventionseasons']['season_id']])->first();
			//$this->prx($checkConvSeason);
			if($checkConvSeason)
			{
				$flagC = 0;
				$getSeasonY = $this->Seasons->find()->where(['Seasons.id' => $this->request->getData('Conventionseasons.season_id')])->first();
				$this->Flash->error('Season '.$getSeasonY->season_year.' already added for this convention.');
			}
			
            $data = $this->Conventionseasons->patchEntity($conventionseasons, $this->request->getData());
            if (count($data->getErrors()) == 0 && $flagC == 1)
			{
				if (empty($data->season_id)) {
					$this->Flash->error('Please select a season.');
					$flagC = 0;
				}

				if ($flagC == 1) {
					// to get season details from selected season from dropdown
					$seasonD 							= $this->Seasons->find()->where(['Seasons.id' => $data->season_id])->first();
					if (!$seasonD) {
						$this->Flash->error('Selected season not found.');
						$flagC = 0;
					}
				}

				if ($flagC == 0) {
					$this->set('conventionseasons', $conventionseasons);
					return;
				}
				
				$data->slug 						= 'convention-season-'.$conventionD->id.'-'.$seasonD->season_year.'-'.time().'-'.rand(10,100000);
                $data->convention_id 				= $conventionD->id;
                $data->season_year 					= $seasonD->season_year;
                $data->status 						= 1;
                $data->created 						= date('Y-m-d H:i:s');
                $data->modified 					= NULL;
				
				$data->registration_start_date 		= date("Y-m-d",strtotime($data->registration_start_date));
				$data->registration_end_date 		= date("Y-m-d",strtotime($data->registration_end_date));
				
                if ($this->Conventionseasons->save($data)) {
                    $this->Flash->success('Season succesfully added to convention.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionseasons', $conventionseasons);
    }
	
	public function deleteconventionsseason($slug_convention_season = null,$slug_convention = null) {
        
		// first check that this convention season exists
		$conventionSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		if($conventionSeasonD)
		{
			// to check if this conventions seasons linked with any other data
			$convention_id 	= $conventionSeasonD->convention_id;
			$season_id 		= $conventionSeasonD->season_id;
			$flagDelete = 1;
			
			//check in conventionseasonevents
			$checkConventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSeasonD->id])->first();
			if($checkConventionSeasonEvents)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention season cannot delete. Convention season is linked with Convention > Seasons > Events.');
			}
			
			// to check if any registration received for this
			$checkConventionRegistrations = $this->Conventionregistrations->find()->where(['Conventionregistrations.convention_id' => $convention_id,'Conventionregistrations.season_id' => $season_id])->first();
			if($checkConventionRegistrations)
			{
				$flagDelete = 0;
				$this->Flash->error('Convention season cannot delete. Registration exists for this convention season.');
			}
			
			if($flagDelete == 1)
			{
				$this->Conventionseasons->deleteAll(["slug" => $slug_convention_season]);
				$this->Flash->success('Convention successfully unlinked from season '.$conventionSeasonD->season_year.'.');
			}
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug_convention]);
    }
	
	public function events($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		
		global $eventTypeDD;
		$this->set('eventTypeDD', $eventTypeDD);
		
		$data = array();
		
        $conventionSD = null;
        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		$season_id = $conventionSD->season_id;
		$seasonD   = $this->Seasons->get($season_id);
		$this->set('conventionSD', $conventionSD);
		
		$conventionD = null;
		if ($slug_convention) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		$convention_id = $conventionD->id;
		$this->set('conventionD', $conventionD);
		
		$this->set('title', ADMIN_TITLE . 'Events > '.$conventionD->name.' > Season '.$conventionSD->season_year);
		
		// to get previous season name
		$prevSeasonConventionFound = 0;
		$previousSeasonD 		= $this->Seasons->find()->where(['Seasons.season_year <' => $seasonD->season_year])->first();
		if($previousSeasonD)
		{
			// to check if this convention found in previous season
			$checkConventionPY = $this->Conventionseasons->find()->where(['Conventionseasons.season_id' => $previousSeasonD->id,'Conventionseasons.convention_id' => $convention_id])->first();
			if($checkConventionPY)
			{
				$this->set('prevSeasonConventionFound', 1);
				$this->set('prevConvSeasonAutoID', $checkConventionPY->id);
			}
		}
				
		$totalEventsConventions = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->count();
		$this->set('totalEventsConventions', $totalEventsConventions);
		
		$separator = array();
        $condition = array();
        $condition = array('Conventionseasonevents.conventionseasons_id' => $conventionSD->id);

        if ($this->request->is('post')) {
            if ($this->request->getData('action') !== null) {
                $idList = implode(',', $this->request->getData('chkRecordId'));
                $action = $this->request->getData('action');
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionseasonevents->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionseasonevents->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionseasonevents->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if ($this->request->getData('Conventionseasonevents.keyword') !== null && $this->request->getData('Conventionseasonevents.keyword') != '') {
                $keyword = trim($this->request->getData('Conventionseasonevents.keyword'));
            }
        } elseif ($this->request->getAttribute('params')) {
            if (!empty($this->request->getParam('pass', []))) {
                $searchArr = $this->request->getParam('pass');
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
            $condition[] = "(Conventionseasonevents.name LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
		
		//$conventionseasonevents	= $this->Conventionseasonevents->contains(['Conventions','Seasons','Events'])->where($condition)->order(['Conventionseasonevents.id' => 'ASC'])->all();
		
		$conventionseasonevents 		= $this->Conventionseasonevents->find()->where($condition)->contain(['Conventions','Seasons','Events'])->order(['Conventionseasonevents.id' => 'ASC'])->all();
		
		$this->set('conventionseasonevents', $conventionseasonevents);
    }
	
	public function judges($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		
		$data = array();
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Judges > '.$conventionD->name.' > Season '.$conventionSD->season_year);
		
		$separator = array();
        $condition = array();
        $condition = array('Conventionregistrations.conventionseason_id' => $conventionSD->id);
		
		$judgeslist 		= $this->Conventionregistrations->find()->where($condition)->contain(['Users'])->order(["Conventionregistrations.id" => "DESC"])->all();
		$this->set('judgeslist', $judgeslist);

    }
	
	public function importeventsfromglobal($slug_convention_season = null,$slug_convention = null) {
		
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		// Build import query based on convention type.
		$eventsQuery = $this->Events->find();
		$conventionType = (int)$conventionD->convention_type;

		if ($conventionType === 0)
		{
			// In Person: all regular events except U11-only group.
			$eventsQuery->where(["(Events.event_grp_name <> '5' OR Events.event_grp_name IS NULL OR Events.event_grp_name = '')"]);
		}
		elseif ($conventionType === 1)
		{
			// Online: only upload-capable event types, and never U11-only group.
			$eventsQuery->where(["(Events.event_type = '1' OR Events.event_type = '2')"]);
			$eventsQuery->where(["(Events.event_grp_name <> '5' OR Events.event_grp_name IS NULL OR Events.event_grp_name = '')"]);
		}
		elseif ($conventionType === 3)
		{
			// Small Convention: import only event IDs listed in the dedicated CSV.
			$projectRoot = dirname(dirname(dirname(__DIR__)));
			$candidateCsvPaths = array(
				$projectRoot . '/Events List 2026 Small Convention.csv',
				BASE_PATH . '/Events List 2026 Small Convention.csv',
				'/var/www/html/Events List 2026 Small Convention.csv',
				$projectRoot . '/webroot/files/csv_files/Events List 2026 Small Convention.csv',
			);

			$csvFilePath = '';
			foreach($candidateCsvPaths as $candidateCsvPath)
			{
				if($candidateCsvPath && file_exists($candidateCsvPath) && is_readable($candidateCsvPath))
				{
					$csvFilePath = $candidateCsvPath;
					break;
				}
			}
			$smallConventionEventIds = array();

			if ($csvFilePath === '')
			{
				$this->Flash->error('Small Convention CSV not found. Checked: ' . implode(' | ', $candidateCsvPaths));
				$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
				return;
			}

			if (($handle = fopen($csvFilePath, 'r')) !== false)
			{
				$rowNo = 0;
				while (($row = fgetcsv($handle, 2000, ',')) !== false)
				{
					$rowNo++;
					if ($rowNo === 1)
					{
						continue;
					}

					if (!isset($row[0]))
					{
						continue;
					}

					$eventIdNumber = trim((string)$row[0]);
					if ($eventIdNumber === '')
					{
						continue;
					}

					$smallConventionEventIds[$eventIdNumber] = $eventIdNumber;

					// Normalize numeric IDs such as 001 -> 1 to match DB values.
					if (ctype_digit($eventIdNumber))
					{
						$normalizedId = (string)((int)$eventIdNumber);
						if ($normalizedId !== '')
						{
							$smallConventionEventIds[$normalizedId] = $normalizedId;
						}
					}
				}
				fclose($handle);
			}

			if (count($smallConventionEventIds) === 0)
			{
				$this->Flash->error('No event IDs found in Small Convention CSV.');
				$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
				return;
			}

			$eventsQuery->where(['Events.event_id_number IN' => array_values($smallConventionEventIds)]);
		}

		// Get filtered event list.
		$eventsAll = $eventsQuery->order(["Events.id" => "ASC"])->all();
		
		foreach($eventsAll as $event)
		{
			$conventionseasonevents = $this->Conventionseasonevents->newEntity([]);
			$dataCSE = $this->Conventionseasonevents->patchEntity($conventionseasonevents, $this->request->getData());

			$dataCSE->slug 						= "cse-".$convention_id."-".$season_id."-".$event->id."-".time();
			$dataCSE->conventionseasons_id 		= $conventionSD->id;
			$dataCSE->convention_id				= $convention_id;
			$dataCSE->season_id					= $season_id;
			$dataCSE->season_year				= $conventionSD->season_year;
			$dataCSE->event_id					= $event->id;
			
			$dataCSE->created 					= date('Y-m-d H:i:s');
			$dataCSE->modified 					= date('Y-m-d H:i:s');

			$resultCSE = $this->Conventionseasonevents->save($dataCSE);
		}
		
		$this->Flash->success('Event successfully import from global events list.');
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function reseteventlist($slug_convention_season = null,$slug_convention = null) {
		
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		
		// to check if any events associated with this convention & season
		$conventionSeasonEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->order(['Conventionseasonevents.id' => 'ASC'])->contain(['Conventions','Seasons','Events'])->all();
		if($conventionSeasonEvents)
		{
			$this->Conventionseasonevents->deleteAll(["conventionseasons_id" => $conventionSD->id]);
			$this->Flash->success('Events removed from this season and convention.');
		}
		else
		{
			$this->Flash->error('Sorry, no event found.');
		}
		
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
    }
	
	public function seasonresultrelease($convention_season_slug = null, $convention_slug = null) {
        
		// first check that this convention season exists
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if($convSeasonD)
		{
			// release result
			$this->Conventionseasons->updateAll(['results_release' => '1'], ["slug" => $convention_season_slug]);
			$this->Flash->success('Results released succesfully.');
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$convention_slug]);
    }
	
	public function seasonresultreleasestop($convention_season_slug = null, $convention_slug = null) {
        
		// first check that this convention season exists
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if($convSeasonD)
		{
			// release result
			$this->Conventionseasons->updateAll(['results_release' => '0'], ["slug" => $convention_season_slug]);
			$this->Flash->success('Results stopped to released succesfully.');
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$convention_slug]);
    }

	public function locksubmissions($convention_season_slug = null, $convention_slug = null) {

		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if ($convSeasonD) {
			$submissionsColumn = $this->getSubmissionsOpenColumn();
			if (!$submissionsColumn) {
				$this->Flash->error('Submissions lock column not found.');
				return $this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convention_slug]);
			}

			$currentValue = $this->normalizeSubmissionFlagValue($convSeasonD->get($submissionsColumn));
			if ($currentValue === 0) {
				$this->Flash->success('Submissions are already locked.');
			} else {
				$updated = $this->Conventionseasons->updateAll([$submissionsColumn => '0'], ['slug' => $convention_season_slug]);
				if ($updated) {
					$this->Flash->success('Submissions locked successfully.');
				} else {
					$this->Flash->error('Unable to lock submissions.');
				}
			}
		} else {
			$this->Flash->error('Convention season not found.');
		}

		$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convention_slug]);
	}

	public function unlocksubmissions($convention_season_slug = null, $convention_slug = null) {

		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if ($convSeasonD) {
			$submissionsColumn = $this->getSubmissionsOpenColumn();
			if (!$submissionsColumn) {
				$this->Flash->error('Submissions lock column not found.');
				return $this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convention_slug]);
			}

			$updated = $this->Conventionseasons->updateAll([$submissionsColumn => '1'], ['slug' => $convention_season_slug]);
			if ($updated) {
				$this->Flash->success('Submissions unlocked successfully.');
			} else {
				$this->Flash->error('Unable to unlock submissions.');
			}
		} else {
			$this->Flash->error('Convention season not found.');
		}

		$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convention_slug]);
	}

	public function togglesubmissions($convention_season_slug = null, $convention_slug = null) {

		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->first();
		if ($convSeasonD) {
			$submissionsColumn = $this->getSubmissionsOpenColumn();
			if (!$submissionsColumn) {
				$this->Flash->error('Submissions lock column not found.');
				return $this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convention_slug]);
			}

			$overrideMap = (array)$this->request->session()->read('submissions_lock_overrides');
			if (isset($overrideMap[$convention_season_slug])) {
				$currentValue = (int)$overrideMap[$convention_season_slug];
			} else {
				$currentValue = $this->normalizeSubmissionFlagValue($convSeasonD->get($submissionsColumn));
			}
			$newValue = ($currentValue === 1) ? 0 : 1;
			$updated = 0;
			try {
				$connection = ConnectionManager::get('default');
				$statement = $connection->execute(
					"UPDATE conventionseasons SET {$submissionsColumn} = :newValue WHERE slug = :seasonSlug",
					['newValue' => (string)$newValue, 'seasonSlug' => $convention_season_slug],
					['newValue' => 'string', 'seasonSlug' => 'string']
				);
				$updated = $statement->rowCount();
			} catch (\Exception $ex) {
				$this->Flash->error('Unable to update submissions lock status. '.$ex->getMessage());
				return $this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convention_slug]);
			}

			if ($updated) {
				$overrideMap = (array)$this->request->session()->read('submissions_lock_overrides');
				$overrideMap[$convention_season_slug] = $newValue;
				$this->request->session()->write('submissions_lock_overrides', $overrideMap);

				if ($newValue === 0) {
					$this->Flash->success('Submissions locked successfully.');
				} else {
					$this->Flash->success('Submissions unlocked successfully.');
				}
			} else {
				$overrideMap = (array)$this->request->session()->read('submissions_lock_overrides');
				$overrideMap[$convention_season_slug] = $newValue;
				$this->request->session()->write('submissions_lock_overrides', $overrideMap);

				if ($newValue === 0) {
					$this->Flash->success('Submissions locked successfully.');
				} else {
					$this->Flash->success('Submissions unlocked successfully.');
				}
			}
		} else {
			$this->Flash->error('Convention season not found.');
		}

		$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convention_slug]);
	}

	private function getSubmissionsOpenColumn() {
		$columns = $this->Conventionseasons->getSchema()->columns();
		if (in_array('submissions_open', $columns, true)) {
			return 'submissions_open';
		}
		if (in_array('submission_open', $columns, true)) {
			return 'submission_open';
		}

		try {
			$connection = ConnectionManager::get('default');
			$columnsMeta = $connection->execute('SHOW COLUMNS FROM conventionseasons')->fetchAll('assoc');
			foreach ($columnsMeta as $col) {
				$field = isset($col['Field']) ? strtolower($col['Field']) : '';
				if (strpos($field, 'submission') !== false && (strpos($field, 'open') !== false || strpos($field, 'lock') !== false || strpos($field, 'status') !== false)) {
					return $col['Field'];
				}
			}
		} catch (\Exception $ex) {
			return null;
		}

		return null;
	}

	private function normalizeSubmissionFlagValue($value) {
		if (is_string($value) && strlen($value) === 1) {
			$byte = ord($value);
			if ($byte === 0 || $byte === 1) {
				return $byte;
			}
		}

		return (int)$value;
	}

	public function certificates($slug_convention_season = null, $slug_convention = null) {
		$this->viewBuilder()->setLayout('admin');
		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');

		if (!$slug_convention_season || !$slug_convention) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		$conventionD  = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();

		if (!$conventionSD || !$conventionD) {
			$this->Flash->error('Convention or season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', ADMIN_TITLE . 'Generate Certificate - ' . $conventionD->name . ' - ' . $conventionSD->season_year);
		$this->set(compact('conventionSD', 'conventionD', 'slug_convention_season', 'slug_convention'));

		$certTypes = [
			'participation' => 'Participation Certificate',
			'achievement'   => 'Achievement Certificate',
			'excellence'    => 'Excellence Certificate',
			'appreciation'  => 'Appreciation Certificate',
			'custom'        => 'Custom Certificate',
		];
		$this->set('certTypes', $certTypes);
	}

	public function certificatespdf($slug_convention_season = null, $slug_convention = null) {
		$this->viewBuilder()->disableAutoLayout();

		if (!$slug_convention_season || !$slug_convention) {
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		$conventionD  = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();

		if (!$conventionSD || !$conventionD) {
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$cert_type   = $this->request->getData()('Certificates.cert_type');
		$name        = trim((string)$this->request->getData()('Certificates.name'));
		$description = trim((string)$this->request->getData()('Certificates.description'));

		$certTypes = [
			'participation' => 'Participation Certificate',
			'achievement'   => 'Achievement Certificate',
			'excellence'    => 'Excellence Certificate',
			'appreciation'  => 'Appreciation Certificate',
			'custom'        => 'Custom Certificate',
		];
		$cert_type_label = isset($certTypes[$cert_type]) ? $certTypes[$cert_type] : 'Certificate';

		$arrCertData = [
			'convention_name' => $conventionD->name,
			'season_year'     => $conventionSD->season_year,
			'name'            => $name,
			'description'     => $description,
			'cert_type'       => $cert_type,
			'cert_type_label' => $cert_type_label,
		];

		$this->set(compact('arrCertData', 'conventionSD', 'conventionD', 'slug_convention_season', 'slug_convention'));
	}
	
	public function importeventsfromprevyear($slug_convention_season = null,$slug_convention = null) {
		
		$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		
	}
	
	public function changeprices($conv_season_slug = null,$slug = null) {
        $this->set('title', ADMIN_TITLE . 'Change Prices');
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		if ($conv_season_slug) {
            $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $conv_season_slug])->first();
			$uid = $convSeasonD->id;
			$this->set('conv_season_slug', $conv_season_slug);
			$this->set('convSeasonD', $convSeasonD);
        }
		else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
		}
		
		if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
		}
		
        $conventionseasons = $this->Conventionseasons->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Conventionseasons->patchEntity($conventionseasons, $this->request->getData());
			
            if (count($data->getErrors()) == 0) {
				
				//$this->prx($data);
				
				$data->registration_start_date 		= date("Y-m-d",strtotime($data->registration_start_date));
				$data->registration_end_date 		= date("Y-m-d",strtotime($data->registration_end_date));
                
				$data->modified = date("Y-m-d");
                if ($this->Conventionseasons->save($data)) {
                    $this->Flash->success('Convention season prices updated successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'seasons',$slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionseasons', $conventionseasons);
    }
	
	/* Manage Rooms for convention */
	public function rooms($slug=null) {

        if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Manage Rooms - '.$conventionD->name);
		$showAll = ((int)$this->request->getQuery('all') === 1);
        
		$separator = array();
        $condition = array();
		$condition = array('Conventionrooms.convention_id' => $conventionD->id);

        if ($this->request->is('post')) {
			if (isset($this->request->getData()['all'])) {
				$showAll = ((int)$this->request->getData()['all'] === 1);
			}
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Conventionrooms->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Conventionrooms->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Conventionrooms->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Conventionrooms']['keyword']) && $this->request->getData()['Conventionrooms']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Conventionrooms']['keyword']);
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
            $condition[] = "(Conventionrooms.room_name LIKE '%".addslashes($keyword)."%' OR Conventionrooms.short_description LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
		
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
		$this->set('showAll', $showAll);
		$limit = $showAll ? 1000 : 20;
		$this->paginate = ['conditions' => $condition, 'limit' => $limit, 'order' => ['Conventionrooms.room_name' => 'ASC']];
        $this->set('convrooms', $this->paginate($this->Conventionrooms));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventions');
            $this->render('rooms');
        }
    }
	
	public function addroom($slug=null) {
        
		if ($slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug])->first();
			$this->set('slug', $slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Add Room - '.$conventionD->name);
		
        $conventionrooms = $this->Conventionrooms->newEntity([]);
        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$flagC = 1;
			
			// to check if same room name added in this convention
			$checkConvRoom = $this->Conventionrooms->find()->where(['Conventionrooms.convention_id' =>$conventionD->id, 'Conventionrooms.room_name' => $this->request->getData()['Conventionrooms']['room_name']])->first();
			if($checkConvRoom)
			{
				$flagC = 0;
				$this->Flash->error('Room name already exists for this convention. Please use some another room name.');
			}
			
            $data = $this->Conventionrooms->patchEntity($conventionrooms, $this->request->getData());
            if (count($data->getErrors()) == 0 && $flagC == 1)
			{
				if (!empty($data->season_id)) {
					$seasonD = $this->Seasons->find()->where(['Seasons.id' => $data->season_id])->first();
					if (!$seasonD) {
						$this->Flash->error('Selected season not found.');
						$this->set('conventionrooms', $conventionrooms);
						return;
					}
				}
				
				$data->slug 						= 'convention-room-'.$conventionD->id.'-'.time().'-'.rand(10,100000);
                $data->convention_id 				= $conventionD->id;
                $data->status 						= 1;
                $data->created 						= date('Y-m-d H:i:s');
                $data->modified 					= NULL;
				
                if ($this->Conventionrooms->save($data)) {
                    $this->Flash->success('Room succesfully added to convention.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'rooms',$slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionrooms', $conventionrooms);
    }
	
	public function editroom($room_slug = null,$convention_slug = null) {
        
		if ($convention_slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
			$this->set('convention_slug', $convention_slug);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Edit Room - '.$conventionD->name);
        
        if ($room_slug) {
            $convRoomD = $this->Conventionrooms->find()->where(['Conventionrooms.slug' => $room_slug])->first();
            $uid = $convRoomD->id;
        }
		
        $conventionrooms = $this->Conventionrooms->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Conventionrooms->patchEntity($conventionrooms, $this->request->getData());
			
			$flagC = 1;
			
			// to check if same room name added in this convention
			$checkConvRoom = $this->Conventionrooms->find()->where(['Conventionrooms.id !=' =>$convRoomD->id, 'Conventionrooms.convention_id' =>$conventionD->id, 'Conventionrooms.room_name' => $this->request->getData()['Conventionrooms']['room_name']])->first();
			if($checkConvRoom)
			{
				$flagC = 0;
				$this->Flash->error('Room name already exists for this convention. Please use some another room name.');
			}
			
            if (count($data->getErrors()) == 0 && $flagC == 1) {
                $data->name = trim($this->request->getData()['Conventionrooms']['name']);
				$data->modified = date("Y-m-d");
                if ($this->Conventionrooms->save($data)) {
                    $this->Flash->success('Room details updated successfully.');
                    $this->redirect(['controller' => 'conventions', 'action' => 'rooms', $convention_slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('conventionrooms', $conventionrooms);
    }
	
	public function deleteroom($room_slug = null,$convention_slug = null) {
        
		$flagDel = 1;
		
		if ($convention_slug) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $convention_slug])->first();
			if(!$conventionD)
			{
				$flagDel = 0;
			}
        }
		else
		{
			$flagDel = 0;
		}
		
		if ($room_slug) {
            $roomD = $this->Conventionrooms->find()->where(['Conventionrooms.slug' => $room_slug])->first();
			if(!$roomD)
			{
				$flagDel = 0;
			}
        }
		else
		{
			$flagDel = 0;
		}
		
		//echo $flagDel;exit;
		
		if($flagDel == 1)
		{
			$this->Conventionrooms->deleteAll(["id" => $roomD->id, "convention_id" => $conventionD->id]);
			$this->Flash->success('Room details deleted successfully.');
			$this->redirect(['controller' => 'conventions', 'action' => 'rooms', $convention_slug]);
		}
		else
		{
			$this->Flash->error('Error deleting convention room.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
    }
	
	public function roomevents($slug_convention_season = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Room Events > '.$conventionSD->Conventions['name'].' > Season '.$conventionSD->Seasons['season_year']);
		
		
		// to get a list of peding events that are not assigned to any room
		$pendingEventsToRoomsList = array();
		$convSeasonAllEvents 		= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->all();
		foreach($convSeasonAllEvents as $convsallev)
		{
			if($convsallev->Events['needs_schedule'] == 1)
			{
				// to check that each event is assigned to a room or not
				$event_id_check = $convsallev->Events['id'];
				$condCheckE = array();
				$condCheckE[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."')";
				$condCheckE[] = "(Conventionseasonroomevents.event_ids = '".$event_id_check."' OR 
								Conventionseasonroomevents.event_ids LIKE '".$event_id_check.",%' OR 
								Conventionseasonroomevents.event_ids LIKE '%,".$event_id_check.",%' OR 
								Conventionseasonroomevents.event_ids LIKE '%,".$event_id_check."')";
				$getEventRoom 			= $this->Conventionseasonroomevents->find()->where($condCheckE)->first();
				if(!$getEventRoom)
				{
					$pendingEventsToRoomsList[] = $convsallev->Events['event_name'].' ('.$convsallev->Events['event_id_number'].')';
				}
			}
		}
		$this->set('pendingEventsToRoomsList', $pendingEventsToRoomsList);
		//$this->prx($pendingEventsToRoomsList);
		
		
		
		$separator = array();
        $condition = array('Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id);

        
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
		$roomeventsQuery = $this->Conventionseasonroomevents->find()
			->contain(['Conventions', 'Seasons', 'Conventionrooms'])
			->where($condition)
			->order(['Conventionseasonroomevents.id' => 'ASC']);
		$this->set('conventionseasonroomevents', $this->paginate($roomeventsQuery, ['limit' => 1000000000]));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventions');
            $this->render('roomevents');
        }
    }
	
	public function addroomevents($slug_convention_season=null) {
        
		$this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Add Room Events > '.$conventionSD->Conventions['name'].' > Season '.$conventionSD->Seasons['season_year']);
		
		// to get list of rooms for which events already added
		$alreadyRoomArr = array();
		$alreadyRoomArr[] = 0;
		$alreadyAddedRooms = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id,'Conventionseasonroomevents.convention_id' => $conventionSD->convention_id,'Conventionseasonroomevents.season_id' => $conventionSD->season_id,'Conventionseasonroomevents.season_year' => $conventionSD->season_year])->all();
		foreach($alreadyAddedRooms as $alreadyAddedRoom)
		{
			$alreadyRoomArr[] = $alreadyAddedRoom->room_id;
		}
		$alreadyRoomArrImplode = implode(",",$alreadyRoomArr);
		//$this->prx($alreadyRoomArr);
        		
		
		// to get the room allocated for this convention
		$condConvRooms = array();
		$condConvRooms[] = "(Conventionrooms.convention_id = '".$conventionSD->convention_id."' AND Conventionrooms.id NOT IN ($alreadyRoomArrImplode) )";
		$convRooms 		= $this->Conventionrooms->find()->where($condConvRooms)->order(['Conventionrooms.room_name' => 'ASC'])->combine('id', 'room_name')->toArray();
		$this->set('convRooms', $convRooms);
		
		// to get events list for this season
		$convSeasEventDD = array();
		$convSeasonEvents 		= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->order(['Conventionseasonevents.id' => 'ASC'])->all();
		foreach($convSeasonEvents as $convSeasonEvent)
		{
			// to check that this event required scheduling
			if($convSeasonEvent->Events['needs_schedule'] == 1)
			{
				$convSeasEventDD[$convSeasonEvent->event_id] = $convSeasonEvent->Events['event_name']." (".$convSeasonEvent->Events['event_id_number'].")";
			}
		}
		$this->set('convSeasEventDD', $convSeasEventDD);
		

        if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$room_id 	= $this->request->getData()['Conventionseasonroomevents']['room_id'];
			$event_ids 	= $this->request->getData()['Conventionseasonroomevents']['event_ids'];
			
			if(count($event_ids))
			{
				$event_ids_implode = implode(",",$event_ids);
				
				$conventionseasonroomevents = $this->Conventionseasonroomevents->newEntity([]);
				$data = $this->Conventionseasonroomevents->patchEntity($conventionseasonroomevents, $this->request->getData());
				
				$slug = "conv-season-room-event-".time()."-".rand(100,10000);
				$data->name = trim($this->request->getData()['Conventions']['name']);
				$data->slug = $slug;
				
				$data->conventionseasons_id 	= $conventionSD->id;
				$data->convention_id 			= $conventionSD->convention_id;
				$data->season_id 				= $conventionSD->season_id;
				$data->season_year 				= $conventionSD->season_year;
				$data->room_id 					= $room_id;
				$data->event_ids 				= $event_ids_implode;
				$data->created 					= date('Y-m-d');
				$data->modified 				= date('Y-m-d');
				$this->Conventionseasonroomevents->save($data);
				
				$this->Flash->success('Event successfully added to convention room.');
				$this->redirect(['controller' => 'conventions', 'action' => 'roomevents',$slug_convention_season]);
			}
			else
			{
				$this->Flash->error('Please choose event.');
			}
			
			
        }
    }
	
	public function deleteroomevents($slug = null,$slug_convention_season=null) {
        
		// first check that this convention season exists
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
		if($conventionSD)
		{	
			//1. check in convention room events
			$checkConventionRoomEvents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.slug' => $slug,'Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->first();
			if($checkConventionRoomEvents)
			{
				$this->Conventionseasonroomevents->deleteAll(["slug" => $slug]);
				$this->Flash->success('Convention room events deleted successfully.');
			}
		}
		else
		{
			$this->Flash->error('Convention season not found.');
		}
		
		
        $this->redirect(['controller' => 'conventions', 'action' => 'roomevents',$slug_convention_season]);
    }
	
	public function editroomevents($slug = null,$slug_convention_season=null) {
        
		$this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug', $slug);
		$this->set('slug_convention_season', $slug_convention_season);
		
        if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
			
			// To get convention season room details
			$conventionSRoomD 			= $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.slug' => $slug])->contain(["Conventionrooms"])->first();
			$this->set('conventionSRoomD', $conventionSRoomD);
			
			$roomEventIDS = 0;
			$checkRoomEVIDS = array();
			if($conventionSRoomD->event_ids != '' && $conventionSRoomD->event_ids != NULL)
			{
				$roomEventIDS 	= $conventionSRoomD->event_ids;
				$checkRoomEVIDS = explode(",",$conventionSRoomD->event_ids);
			}
			// To get list of events of this Room
			$condREvents = array();
			$condREvents[] = "(Events.id IN ($roomEventIDS))";
			
			$roomEventsL = $this->Events->find()->where($condREvents)->order(["Events.event_name" => "ASC"])->all();
			$this->set('roomEventsL', $roomEventsL);
			//$this->prx($roomEventsL);
			
			
			// to get events list for this season
			$convSeasEventDD = array();
			$convSeasonEvents 		= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->order(['Conventionseasonevents.id' => 'ASC'])->all();
			foreach($convSeasonEvents as $convSeasonEvent)
			{
				// to check that this event required scheduling
				if($convSeasonEvent->Events['needs_schedule'] == 1 && !in_array($convSeasonEvent->Events['id'],$checkRoomEVIDS))
				{
					$convSeasEventDD[$convSeasonEvent->event_id] = $convSeasonEvent->Events['event_name']." (".$convSeasonEvent->Events['event_id_number'].")";
				}
			}
			$this->set('convSeasEventDD', $convSeasEventDD);
			
			
			
			
		if ($this->request->is('post')) {
		
			//$this->prx($this->request->getData());
			$new_event_ids 	= $this->request->getData()['Conventionseasonroomevents']['event_ids'];
			
			if(count($new_event_ids))
			{
				
				// there is already events in this conventin room, so we need to merge them as well
				if($conventionSRoomD->event_ids != '' && $conventionSRoomD->event_ids != NULL)
				{
					$old_event_ids = explode(",",$conventionSRoomD->event_ids);
					
					$merged_events = array_merge($new_event_ids, $old_event_ids);
				}
				else
				{
					$merged_events = $new_event_ids;
				}
				
				$merged_events_implode = implode(",",$merged_events);
				
				// To update room Events
				$this->Conventionseasonroomevents->updateAll(
				[
					'event_ids' => $merged_events_implode,
					'modified' => date("Y-m-d H:i:s"),
				], 
				[
					'id' => $conventionSRoomD->id
				]);
				
				$this->Flash->success('Event successfully added to convention room.');
			}
			else
			{
				$this->Flash->error('Please choose event.');
			}
			
			$this->redirect(['controller' => 'conventions', 'action' => 'editroomevents',$slug,$slug_convention_season]);
			
			
		}
			
			
			
			
			
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Edit Room Events > '.$conventionSD->Conventions['name'].' > Season '.$conventionSD->Seasons['season_year']);
		
		
    }
	
	public function deleteeventfromroom($slug = null,$slug_convention_season=null, $event_id=NULL) {
		
		if ($slug_convention_season) {
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(["Conventions","Seasons"])->first();
			
			// To get convention season room details
			$conventionSRoomD 			= $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.slug' => $slug])->contain(["Conventionrooms"])->first();
			
			if($conventionSRoomD->event_ids != '' && $conventionSRoomD->event_ids != NULL)
			{
				$roomEventIDSArr = explode(",",$conventionSRoomD->event_ids);
				
				// Check if event exists
				if (in_array($event_id, $roomEventIDSArr)) {
					// Find the key of the value
					$key = array_search($event_id, $roomEventIDSArr);

					// Remove the value
					unset($roomEventIDSArr[$key]);
					
					if(count($roomEventIDSArr)>0)
					{
						$roomEventIDS = implode(",",$roomEventIDSArr);
					}
					else
					{
						$roomEventIDS = NULL;
					}
					
					// To update room Events
					$this->Conventionseasonroomevents->updateAll(
					[
						'event_ids' => $roomEventIDS,
						'modified' => date("Y-m-d H:i:s"),
					], 
					[
						'id' => $conventionSRoomD->id
					]);
					
					$this->Flash->success('Event successfully removed from convention room.');
				}
			}
			
		}	
        else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->redirect(['controller' => 'conventions', 'action' => 'editroomevents',$slug,$slug_convention_season]);
		
	}
	
	
	// to show list of schools for scripture award
	public function scriptureawardslist($slug_convention_season = null,$slug_convention = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);
		
		$data = array();
		
        if ($slug_convention_season)
		{
            $conventionSD 			= $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
            $season_id 				= $conventionSD->season_id;
			$this->set('conventionSD', $conventionSD);
        }
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD 		= $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
            $convention_id 		= $conventionD->id;
			$this->set('conventionD', $conventionD);
        }
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Scripture Awards List > '.$conventionD->name.' > Season '.$conventionSD->season_year);
		
		// to get list of events in which certificate print is allowed
		$arrEventCP = array();
		$eventCP = $this->Events->find()->where(['Events.certificate_print' => 1])->all();
		foreach($eventCP as $evcp)
		{
			$arrEventCP[] = $evcp->id;
		}
		$this->set('arrEventCP', $arrEventCP);
		
		
		$finalSchoolsList 		= array();
		$finalSchoolsEventsList = array();
		
		
		// to get all schools registered for this convention season
		$conventionRegList 		= $this->Conventionregistrations->find()->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])->all();
		foreach($conventionRegList as $convreg)
		{
			// to check if any student of this school having any of the event for scripture award
			$convRegStudents = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.conventionregistration_id' => $convreg->id])->all();
			foreach($convRegStudents as $concregstudent)
			{
				// now check events of student and match with scripture award events // add event to school array
				if(isset($concregstudent->event_ids) && !empty($concregstudent->event_ids))
				{
					$studentEventExplode = explode(",",$concregstudent->event_ids);
					foreach($studentEventExplode as $steventid)
					{
						if(in_array($steventid,(array)$arrEventCP))
						{
							if(!in_array($convreg->user_id,(array)$finalSchoolsList))
							{
								// add school to list
								$finalSchoolsList[] = $convreg->user_id;
							}
							
							if(!in_array($steventid,(array)$finalSchoolsEventsList[$convreg->user_id]))
							{
								// add school to list
								$finalSchoolsEventsList[$convreg->user_id][] = $steventid;
							}
						}
					}
				}
				
				
				
			}
			
			//$this->prx($convRegStudents);
		}
		
		//$this->pr($finalSchoolsList);
		//$this->prx($finalSchoolsEventsList);
		
		$this->set('finalSchoolsList', $finalSchoolsList);
		$this->set('finalSchoolsEventsList', $finalSchoolsEventsList);
		
    }

	// to show scripture reading list page
	public function scripturereadinglist($slug_convention_season = null,$slug_convention = null) {

		$this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);

		if ($slug_convention_season)
		{
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('conventionSD', $conventionSD);
		}
		if (!$conventionSD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('conventionD', $conventionD);
		}
		if (!$conventionD)
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('title', ADMIN_TITLE . 'Scripture Reading List > '.$conventionD->name.' > Season '.$conventionSD->season_year);

		$readingListRows = $this->buildScriptureReadingListRows($conventionSD);
		$goldenAwardEventNumbers = $this->getGoldenAwardEventNumbers();
		if (!empty($goldenAwardEventNumbers)) {
			$readingListRows = array_values(array_filter($readingListRows, function ($row) use ($goldenAwardEventNumbers) {
				$eventCode = isset($row['event_ids']) ? trim((string)$row['event_ids']) : '';
				return !in_array($eventCode, $goldenAwardEventNumbers, true);
			}));
		}
		$groupedReadingList = $this->groupReadingListRowsByPlaceAndSchool($readingListRows);

		$this->set('readingListRows', $readingListRows);
		$this->set('groupedReadingList', $groupedReadingList);
	}

	// to show printable scripture reading list in old grouped format
	public function scripturereadinglistprint($slug_convention_season = null,$slug_convention = null) {

		$this->viewBuilder()->disableAutoLayout();

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);

		if ($slug_convention_season)
		{
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('conventionSD', $conventionSD);
		}
		if (!$conventionSD)
		{
			return $this->response->withStringBody('Convention season not found.');
		}

		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('conventionD', $conventionD);
		}
		if (!$conventionD)
		{
			return $this->response->withStringBody('Convention not found.');
		}

		$readingListRows = $this->buildScriptureReadingListRows($conventionSD);
		$groupedReadingList = $this->groupReadingListRowsByPlaceAndSchool($readingListRows);

		$this->set('readingListRows', $readingListRows);
		$this->set('groupedReadingList', $groupedReadingList);
	}

	// to show printable golden awards criteria list
	public function goldenawardslistprint($slug_convention_season = null,$slug_convention = null) {

		$this->viewBuilder()->disableAutoLayout();

		$this->set('slug_convention_season', $slug_convention_season);
		$this->set('slug_convention', $slug_convention);

		if ($slug_convention_season)
		{
			$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('conventionSD', $conventionSD);
		}
		if (!$conventionSD)
		{
			return $this->response->withStringBody('Convention season not found.');
		}

		if ($slug_convention) {
			$conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('conventionD', $conventionD);
		}
		if (!$conventionD)
		{
			return $this->response->withStringBody('Convention not found.');
		}

		$this->loadModel('Users');
		$this->loadModel('Conventionregistrationstudents');

		$goldenAwardRules = $this->getGoldenAwardRules();
		$eventNumberList = [];
		foreach ($goldenAwardRules as $rule) {
			$eventNumberList[] = (string)$rule['u16_event_number'];
			$eventNumberList[] = (string)$rule['open_event_number'];
		}

		$events = $this->Events->find()
			->select(['id', 'event_id_number'])
			->where(['Events.event_id_number IN' => $eventNumberList])
			->enableHydration(false)
			->all();

		$eventIdToAwardMap = [];
		foreach ($events as $eventRow) {
			$eventNumber = trim((string)$eventRow['event_id_number']);
			foreach ($goldenAwardRules as $awardKey => $rule) {
				if ($eventNumber === (string)$rule['u16_event_number']) {
					$eventIdToAwardMap[(int)$eventRow['id']] = [
						'award_key' => $awardKey,
						'division' => 'U16',
					];
					break;
				}
				if ($eventNumber === (string)$rule['open_event_number']) {
					$eventIdToAwardMap[(int)$eventRow['id']] = [
						'award_key' => $awardKey,
						'division' => 'Open',
					];
					break;
				}
			}
		}

		$awardSections = [];
		foreach ($goldenAwardRules as $awardKey => $rule) {
			$awardSections[$awardKey] = [
				'award_title' => $rule['award_title'],
				'books_title' => $rule['books_title'],
				'divisions' => [
					'U16' => [],
					'Open' => [],
				],
			];
		}

		$conventionRegistrations = $this->Conventionregistrations->find()
			->select(['id', 'user_id'])
			->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])
			->enableHydration(false)
			->all();

		$registrationToSchoolMap = [];
		$schoolIds = [];
		foreach ($conventionRegistrations as $registration) {
			$registrationToSchoolMap[(int)$registration['id']] = (int)$registration['user_id'];
			$schoolIds[(int)$registration['user_id']] = (int)$registration['user_id'];
		}

		$schoolNameMap = [];
		if (!empty($schoolIds)) {
			$schools = $this->Users->find()
				->select(['id', 'first_name'])
				->where(['Users.id IN' => array_values($schoolIds)])
				->enableHydration(false)
				->all();

			foreach ($schools as $school) {
				$schoolNameMap[(int)$school['id']] = trim((string)$school['first_name']);
			}
		}

		if (!empty($registrationToSchoolMap) && !empty($eventIdToAwardMap)) {
			$studentRows = $this->Conventionregistrationstudents->find()
				->where(['Conventionregistrationstudents.conventionregistration_id IN' => array_keys($registrationToSchoolMap)])
				->contain(['Students'])
				->order([
					'Students.first_name' => 'ASC',
					'Students.last_name' => 'ASC',
				])
				->all();

			foreach ($studentRows as $studentRow) {
				$studentEventIdsRaw = trim((string)$studentRow->event_ids);
				if ($studentEventIdsRaw === '') {
					continue;
				}

				$studentEventIds = array_filter(array_map('intval', explode(',', $studentEventIdsRaw)));
				if (empty($studentEventIds)) {
					continue;
				}

				$studentName = trim((string)$studentRow->Students['first_name']).' '.trim((string)$studentRow->Students['last_name']);
				$studentName = trim(preg_replace('/\s+/', ' ', $studentName));
				if ($studentName === '') {
					continue;
				}

				$registrationId = (int)$studentRow->conventionregistration_id;
				if (!isset($registrationToSchoolMap[$registrationId])) {
					continue;
				}

				$schoolId = $registrationToSchoolMap[$registrationId];
				$schoolName = isset($schoolNameMap[$schoolId]) && $schoolNameMap[$schoolId] !== ''
					? $schoolNameMap[$schoolId]
					: 'Unknown School';

				foreach ($studentEventIds as $studentEventId) {
					if (!isset($eventIdToAwardMap[$studentEventId])) {
						continue;
					}

					$awardKey = $eventIdToAwardMap[$studentEventId]['award_key'];
					$division = $eventIdToAwardMap[$studentEventId]['division'];

					if (!isset($awardSections[$awardKey]['divisions'][$division][$schoolName])) {
						$awardSections[$awardKey]['divisions'][$division][$schoolName] = [];
					}

					if (!in_array($studentName, $awardSections[$awardKey]['divisions'][$division][$schoolName], true)) {
						$awardSections[$awardKey]['divisions'][$division][$schoolName][] = $studentName;
					}
				}
			}
		}

		$orderedAwardSections = [];
		foreach ($goldenAwardRules as $awardKey => $rule) {
			$section = $awardSections[$awardKey];
			$hasEntries = false;

			foreach (['U16', 'Open'] as $division) {
				if (!empty($section['divisions'][$division])) {
					ksort($section['divisions'][$division], SORT_NATURAL | SORT_FLAG_CASE);
					foreach ($section['divisions'][$division] as $schoolName => $studentNames) {
						sort($studentNames, SORT_NATURAL | SORT_FLAG_CASE);
						$section['divisions'][$division][$schoolName] = $studentNames;
					}
					$hasEntries = true;
				}
			}

			if ($hasEntries) {
				$orderedAwardSections[] = $section;
			}
		}

		$this->set('goldenAwardSections', $orderedAwardSections);
	}

	private function getGoldenAwardRules() {
		return [
			'christian_worker' => [
				'award_title' => 'Christian Worker Award',
				'books_title' => 'Colossians, 1 & 2 Thessalonians, 1 & 2 Timothy, Titus, Philemon, Hebrews, James, 1 & 2 Peter, 1, 2, & 3 John',
				'u16_event_number' => '1004',
				'open_event_number' => '1054',
			],
			'golden_apple' => [
				'award_title' => 'Golden Apple Award',
				'books_title' => 'Proverbs',
				'u16_event_number' => '1000',
				'open_event_number' => '1050',
			],
			'golden_harp' => [
				'award_title' => 'Golden Harp Award',
				'books_title' => 'Psalms',
				'u16_event_number' => '1002',
				'open_event_number' => '1052',
			],
			'golden_lamb' => [
				'award_title' => 'Golden Lamb Award',
				'books_title' => 'John',
				'u16_event_number' => '1001',
				'open_event_number' => '1051',
			],
			'christian_soldier' => [
				'award_title' => 'Christian Soldier Award',
				'books_title' => 'Romans, Galatians, Ephesians and Philippians',
				'u16_event_number' => '1003',
				'open_event_number' => '1053',
			],
		];
	}

	private function getGoldenAwardEventNumbers() {
		$numbers = [];
		foreach ($this->getGoldenAwardRules() as $rule) {
			$numbers[] = (string)$rule['u16_event_number'];
			$numbers[] = (string)$rule['open_event_number'];
		}

		return array_values(array_unique($numbers));
	}

	private function buildScriptureReadingListRows($conventionSD) {

		$this->loadModel('Eventsubmissions');
		$this->loadModel('Books');

		$scriptureEvents = $this->Events->find()
			->select(['id', 'event_id_number'])
			->where(['Events.certificate_print' => 1])
			->enableHydration(false)
			->all();

		$scriptureEventIds = [];
		$scriptureEventCodeMap = [];
		foreach ($scriptureEvents as $scriptureEvent) {
			$eventId = (int)$scriptureEvent['id'];
			$scriptureEventIds[] = $eventId;
			$scriptureEventCodeMap[$eventId] = !empty($scriptureEvent['event_id_number']) ? (string)$scriptureEvent['event_id_number'] : (string)$eventId;
		}

		$readingListRows = [];
		if (!empty($scriptureEventIds)) {
			$registrationIds = $this->Conventionregistrations->find()
				->select(['id'])
				->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])
				->enableHydration(false)
				->all()
				->extract('id')
				->toList();

			if (!empty($registrationIds)) {
				$eventSubmissions = $this->Eventsubmissions->find()
					->where([
						'Eventsubmissions.convention_id' => $conventionSD->convention_id,
						'Eventsubmissions.season_id' => $conventionSD->season_id,
						'Eventsubmissions.season_year' => $conventionSD->season_year,
						'Eventsubmissions.event_id IN' => $scriptureEventIds,
					])
					->order(['Eventsubmissions.id' => 'DESC'])
					->enableHydration(false)
					->all();

				$latestSubmissionMap = [];
				$allBookIds = [];

				foreach ($eventSubmissions as $submission) {
					$mapKey = $submission['conventionregistration_id'].'_'.$submission['student_id'].'_'.$submission['event_id'];
					if (isset($latestSubmissionMap[$mapKey])) {
						continue;
					}
					$latestSubmissionMap[$mapKey] = $submission;

					$bookIdsRaw = trim((string)($submission['book_ids'] ?? ''));
					if ($bookIdsRaw !== '') {
						foreach (explode(',', $bookIdsRaw) as $bookId) {
							$bookId = (int)trim($bookId);
							if ($bookId > 0) {
								$allBookIds[$bookId] = $bookId;
							}
						}
					}
				}

				$bookNameMap = [];
				if (!empty($allBookIds)) {
					$books = $this->Books->find()
						->select(['id', 'book_name'])
						->where(['Books.id IN' => array_values($allBookIds)])
						->enableHydration(false)
						->all();

					foreach ($books as $book) {
						$bookNameMap[(int)$book['id']] = $book['book_name'];
					}
				}

				$students = $this->Conventionregistrationstudents->find()
					->where(['Conventionregistrationstudents.conventionregistration_id IN' => $registrationIds])
					->contain(['Students', 'Users'])
					->order([
						'Users.first_name' => 'ASC',
						'Students.first_name' => 'ASC',
						'Students.last_name' => 'ASC',
					])
					->all();

				foreach ($students as $studentRow) {
					$eventIdsRaw = trim((string)$studentRow->event_ids);
					if ($eventIdsRaw === '') {
						continue;
					}

					$studentEventIds = array_filter(array_map('intval', explode(',', $eventIdsRaw)));
					$matchedScriptureEvents = array_values(array_intersect($studentEventIds, $scriptureEventIds));
					if (empty($matchedScriptureEvents)) {
						continue;
					}

					$studentName = trim((string)$studentRow->Students['first_name']);
					if (!empty($studentRow->Students['middle_name'])) {
						$studentName .= ' '.trim((string)$studentRow->Students['middle_name']);
					}
					if (!empty($studentRow->Students['last_name'])) {
						$studentName .= ' '.trim((string)$studentRow->Students['last_name']);
					}

					foreach ($matchedScriptureEvents as $eventId) {
						$submissionKey = $studentRow->conventionregistration_id.'_'.$studentRow->student_id.'_'.$eventId;
						if (!isset($latestSubmissionMap[$submissionKey])) {
							continue;
						}

						$submissionBookNames = [];
						$bookIdsRaw = trim((string)($latestSubmissionMap[$submissionKey]['book_ids'] ?? ''));
						if ($bookIdsRaw !== '') {
							foreach (explode(',', $bookIdsRaw) as $bookId) {
								$bookId = (int)trim($bookId);
								if ($bookId > 0 && isset($bookNameMap[$bookId])) {
									$submissionBookNames[$bookNameMap[$bookId]] = $bookNameMap[$bookId];
								}
							}
						}

						if (empty($submissionBookNames)) {
							continue;
						}

						$eventCode = isset($scriptureEventCodeMap[$eventId]) ? (string)$scriptureEventCodeMap[$eventId] : (string)$eventId;
						$autoFirstPlaceEvents = ['1000', '1050', '1001', '1051', '1002', '1052', '1003', '1053', '1004', '1054'];
						$derivedPlace = in_array($eventCode, $autoFirstPlaceEvents, true)
							? '1'
							: $this->derivePlaceFromBookNames(array_values($submissionBookNames));

						$readingListRows[] = [
							'student_name' => trim($studentName),
							'event_ids' => $eventCode,
							'book_names' => implode(', ', array_values($submissionBookNames)),
							'place' => $derivedPlace,
							'school_name' => trim((string)$studentRow->Users['first_name']),
						];
					}
				}
			}
		}

		return $readingListRows;
	}

	private function getScriptureBookStatsMap() {
		return [
			'GENESIS' => ['chapters' => 50, 'verses' => 1533],
			'EXODUS' => ['chapters' => 40, 'verses' => 1213],
			'LEVITICUS' => ['chapters' => 27, 'verses' => 859],
			'NUMBERS' => ['chapters' => 36, 'verses' => 1263],
			'DEUTERONOMY' => ['chapters' => 34, 'verses' => 959],
			'JOSHUA' => ['chapters' => 24, 'verses' => 658],
			'JUDGES' => ['chapters' => 21, 'verses' => 618],
			'RUTH' => ['chapters' => 4, 'verses' => 85],
			'1 SAMUEL' => ['chapters' => 31, 'verses' => 810],
			'2 SAMUEL' => ['chapters' => 24, 'verses' => 672],
			'1 KINGS' => ['chapters' => 22, 'verses' => 816],
			'2 KINGS' => ['chapters' => 25, 'verses' => 719],
			'1 CHRONICLES' => ['chapters' => 29, 'verses' => 941],
			'2 CHRONICLES' => ['chapters' => 36, 'verses' => 821],
			'EZRA' => ['chapters' => 10, 'verses' => 280],
			'NEHEMIAH' => ['chapters' => 13, 'verses' => 406],
			'ESTHER' => ['chapters' => 10, 'verses' => 167],
			'JOB' => ['chapters' => 42, 'verses' => 1049],
			'ECCLESIASTES' => ['chapters' => 12, 'verses' => 222],
			'SONG OF SOLOMON' => ['chapters' => 8, 'verses' => 117],
			'ISAIAH' => ['chapters' => 66, 'verses' => 1264],
			'JEREMIAH' => ['chapters' => 52, 'verses' => 1363],
			'LAMENTATIONS' => ['chapters' => 5, 'verses' => 154],
			'EZEKIEL' => ['chapters' => 48, 'verses' => 1273],
			'DANIEL' => ['chapters' => 12, 'verses' => 357],
			'HOSEA' => ['chapters' => 14, 'verses' => 197],
			'JOEL' => ['chapters' => 3, 'verses' => 73],
			'AMOS' => ['chapters' => 9, 'verses' => 146],
			'OBADIAH' => ['chapters' => 1, 'verses' => 21],
			'JONAH' => ['chapters' => 4, 'verses' => 48],
			'MICAH' => ['chapters' => 7, 'verses' => 105],
			'NAHUM' => ['chapters' => 3, 'verses' => 47],
			'HABAKKUK' => ['chapters' => 3, 'verses' => 56],
			'ZEPHANIAH' => ['chapters' => 3, 'verses' => 53],
			'HAGGAI' => ['chapters' => 2, 'verses' => 38],
			'ZECHARIAH' => ['chapters' => 14, 'verses' => 211],
			'MALACHI' => ['chapters' => 4, 'verses' => 55],
			'MATTHEW' => ['chapters' => 28, 'verses' => 1071],
			'MARK' => ['chapters' => 16, 'verses' => 678],
			'LUKE' => ['chapters' => 24, 'verses' => 1151],
			'ACTS' => ['chapters' => 28, 'verses' => 1007],
			'ROMANS' => ['chapters' => 16, 'verses' => 433],
			'1 CORINTHIANS' => ['chapters' => 16, 'verses' => 437],
			'2 CORINTHIANS' => ['chapters' => 13, 'verses' => 239],
			'GALATIANS' => ['chapters' => 6, 'verses' => 149],
			'EPHESIANS' => ['chapters' => 6, 'verses' => 155],
			'PHILIPPIANS' => ['chapters' => 4, 'verses' => 104],
			'COLOSSIANS' => ['chapters' => 4, 'verses' => 95],
			'1 THESSALONIANS' => ['chapters' => 5, 'verses' => 89],
			'2 THESSALONIANS' => ['chapters' => 3, 'verses' => 47],
			'1 TIMOTHY' => ['chapters' => 6, 'verses' => 98],
			'2 TIMOTHY' => ['chapters' => 4, 'verses' => 83],
			'TITUS' => ['chapters' => 3, 'verses' => 46],
			'PHILEMON' => ['chapters' => 1, 'verses' => 25],
			'HEBREWS' => ['chapters' => 13, 'verses' => 303],
			'JAMES' => ['chapters' => 5, 'verses' => 108],
			'1 PETER' => ['chapters' => 5, 'verses' => 105],
			'2 PETER' => ['chapters' => 3, 'verses' => 61],
			'1 JOHN' => ['chapters' => 5, 'verses' => 105],
			'2 JOHN' => ['chapters' => 1, 'verses' => 13],
			'3 JOHN' => ['chapters' => 1, 'verses' => 14],
			'JUDE' => ['chapters' => 1, 'verses' => 25],
			'REVELATION' => ['chapters' => 21, 'verses' => 404],
		];
	}

	private function normalizeBookNameForPlace($bookName) {
		$normalized = strtoupper(trim((string)$bookName));
		$normalized = preg_replace('/\s+/', ' ', $normalized);
		return $normalized;
	}

	private function derivePlaceFromBookNames($bookNames) {
		$bookStatsMap = $this->getScriptureBookStatsMap();
		$places = [];
		$totalVerses = 0;
		$totalChapters = 0;
		$resolvedBooks = 0;

		foreach ((array)$bookNames as $bookName) {
			$normalized = $this->normalizeBookNameForPlace($bookName);
			if ($normalized === '') {
				continue;
			}

			if (isset($bookStatsMap[$normalized])) {
				$resolvedBooks++;
				$chapters = (int)$bookStatsMap[$normalized]['chapters'];
				$verses = (int)$bookStatsMap[$normalized]['verses'];
				$totalChapters += $chapters;
				$totalVerses += $verses;
			}
		}

		if ($resolvedBooks > 0 && $totalVerses >= 300) {
			$places[1] = 1;
		} elseif ($resolvedBooks > 0 && $totalChapters > 1) {
			$places[2] = 2;
		} elseif ($resolvedBooks > 0) {
			$places[3] = 3;
		}

		if (empty($places)) {
			return '-';
		}

		sort($places, SORT_NUMERIC);
		return implode(', ', $places);
	}

	private function placeToLabel($placeValue) {
		$placeRaw = trim((string)$placeValue);
		if ($placeRaw === '' || $placeRaw === '-') {
			return 'Unplaced';
		}

		if (ctype_digit($placeRaw)) {
			$place = (int)$placeRaw;
			if ($place % 100 >= 11 && $place % 100 <= 13) {
				$suffix = 'th';
			} else {
				switch ($place % 10) {
					case 1:
						$suffix = 'st';
						break;
					case 2:
						$suffix = 'nd';
						break;
					case 3:
						$suffix = 'rd';
						break;
					default:
						$suffix = 'th';
				}
			}
			return $place.$suffix;
		}

		return $placeRaw;
	}

	private function groupReadingListRowsByPlaceAndSchool($rows) {
		$grouped = [];

		foreach ($rows as $row) {
			$placeLabel = $this->placeToLabel($row['place'] ?? '-');
			$schoolName = trim((string)($row['school_name'] ?? ''));
			if ($schoolName === '') {
				$schoolName = 'Unknown School';
			}

			if (!isset($grouped[$placeLabel])) {
				$grouped[$placeLabel] = [];
			}
			if (!isset($grouped[$placeLabel][$schoolName])) {
				$grouped[$placeLabel][$schoolName] = [];
			}

			$grouped[$placeLabel][$schoolName][] = $row;
		}

		uksort($grouped, function ($a, $b) {
			$aNumeric = preg_match('/^\d+(st|nd|rd|th)$/', $a);
			$bNumeric = preg_match('/^\d+(st|nd|rd|th)$/', $b);

			if ($aNumeric && $bNumeric) {
				return (int)$a - (int)$b;
			}
			if ($aNumeric) {
				return -1;
			}
			if ($bNumeric) {
				return 1;
			}

			return strcasecmp($a, $b);
		});

		foreach ($grouped as $place => $schools) {
			ksort($schools, SORT_NATURAL | SORT_FLAG_CASE);
			$grouped[$place] = $schools;
		}

		return $grouped;
	}
	
	
	/* To show list of events of a judge from a convention registration */
	public function judgesevents($conv_reg_slug = null) {
        
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('conv_reg_slug', $conv_reg_slug);
		
		$data = array();
		
        if ($conv_reg_slug) {
            $conventionRegD 			= $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions','Conventionseasons','Users'])->first();
            $season_id 				= $conventionRegD->season_id;
			$this->set('conventionRegD', $conventionRegD);
        }
		if (!$conventionRegD)
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		$this->set('title', ADMIN_TITLE . 'Judges events > '.$conventionRegD->Conventions['name'].' > Season '.$conventionRegD->season_year);
		
		$judges_event_ids_explode = [];
		if($conventionRegD->judges_event_ids)
		{
			$judges_event_ids_explode = explode(",",$conventionRegD->judges_event_ids);
		}
        
		$this->set('judges_event_ids', $judges_event_ids_explode);

    }
	
	public function sendremindertojudge($conv_reg_slug = null, $event_slug = null)
	{ 	
        if ($conv_reg_slug)
		{
            $conventionRegD	= $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $conv_reg_slug])->contain(['Conventions','Conventionseasons','Users'])->first();
			
			$eventD	= $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			
			if($conventionRegD && $eventD)
			{
				//to remind them when/which events still need to be judged
				$emailId = $conventionRegD->Users['email_address'];
									
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '26'])->first();

				$toRepArray = array('[!first_name!]','[!convention_name!]','[!season_year!]','[!event_name!]');
				$fromRepArray = array($conventionRegD->Users['first_name'],$conventionRegD->Conventions['name'],$conventionRegD->season_year,$eventD->event_name);

				$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
				$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
				
				//echo $messageToSend; exit;
				
				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();
					
				$this->Flash->success('Reminder notification sent successfully to judge..');
				
			}
        }
		
		$this->redirect(['controller'=>'conventions', 'action' => 'judgesevents',$conv_reg_slug]);

    }
	
	
	public function qualifyingdata($slug_convention_season = null,$slug_convention=null,$event_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Edit Convention');
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('slug_convention_season', $slug_convention_season);
        $this->set('slug_convention', $slug_convention);
		
        if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->contain(['Conventions'])->first();
            $this->set('conventionSD', $conventionSD);
        }
		
		if ($event_slug) {
            $eventD = $this->Events->find()->where(['Events.slug' => $event_slug])->first();
			$this->set('eventD', $eventD);
        }
		
		// Now get conv season event Record
		$convSeasEventD = $this->Conventionseasonevents->find()
				->where([
				'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
				'Conventionseasonevents.event_id' => $eventD->id
				])->first();
		$this->set('convSeasEventD', $convSeasEventD);
		
		
		if ($this->request->is(['post']))
		{
			//$this->prx($eventD);
			$msgS = 'Qualifying criteria saved successfully.';
			
			if($eventD->event_judging_type == 'times')
			{
				$qualifying_time_score = $this->request->getData()['qualifying_time_score'];
			
				// Now update
				$this->Conventionseasonevents->updateAll(
				[
					'qualifying_time_score' 		=> $qualifying_time_score
				], 
				[
					"id" => $convSeasEventD->id]
				);
				
				$msgS = "Qualifying time saved successfully.";
			}
			
			if($eventD->event_judging_type == 'distances')
			{
				$msgS = "Qualifying criteria saved successfully.";
				$qualifying_distance = $this->request->getData()['qualifying_distance'];
			
				// Now update
				$this->Conventionseasonevents->updateAll(
				[
					'qualifying_distance' 		=> $qualifying_distance
				], 
				[
					"id" => $convSeasEventD->id]
				);
				
				$msgS = "Qualifying distance saved successfully.";
			}
			
			if($eventD->event_judging_type == 'scores')
			{
				$qualifying_score = $this->request->getData()['qualifying_score'];
			
				// Now update
				$this->Conventionseasonevents->updateAll(
				[
					'qualifying_score' 		=> $qualifying_score
				], 
				[
					"id" => $convSeasEventD->id]
				);
				
				$msgS = "Qualifying score saved successfully.";
			}
			
			$this->Flash->success($msgS);
			$this->redirect(['controller' => 'conventions', 'action' => 'events',$slug_convention_season,$slug_convention]);
		}
		
        
    }
	
	public function brokenrecordcertificate($slug_convention_season=null,$slug_convention=null) {
        
		if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('slug_convention_season', $slug_convention_season);
			$this->set('conventionSD', $conventionSD);
        }
		else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('slug_convention', $slug_convention);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
        
		$this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
		$this->set('title', ADMIN_TITLE . 'Broken Record Certificate - '.$conventionD->name.' - '.$conventionSD->season_year);
		
		// To get list of all events of this convention season
		$eventCS = array();
		$convSEvents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($convSEvents as $convsev)
		{
			$eventCS[] = $convsev->event_id;
		}
		
		
		$eventCSImplode = implode(",",$eventCS);
		
		// Now fetch Events
		$eventNI = array();
		$condEvents = array();
		$condEvents[] = "(Events.id IN ($eventCSImplode) )";
		$eventsList = $this->Events->find()->where($condEvents)->order(['Events.event_name' => 'ASC'])->all();
		foreach($eventsList as $eventrec)
		{
			$eventNI[$eventrec->id] = $eventrec->event_name.' ('.$eventrec->event_id_number.')';
		}
		$this->set('eventNI', $eventNI);
		//$this->prx($eventNI);
    }
	
	public function brokenrecordcertificatepdf($slug_convention_season=null,$slug_convention=null)
	{
		$this->viewBuilder()->disableAutoLayout();
		
		if ($slug_convention_season) {
            $conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $slug_convention_season])->first();
			$this->set('slug_convention_season', $slug_convention_season);
			$this->set('conventionSD', $conventionSD);
        }
		else
		{
			$this->Flash->error('Convention season not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		if ($slug_convention) {
            $conventionD = $this->Conventions->find()->where(['Conventions.slug' => $slug_convention])->first();
			$this->set('slug_convention', $slug_convention);
			$this->set('conventionD', $conventionD);
        }
		else
		{
			$this->Flash->error('Convention not found.');
			$this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		
		
		//$this->prx($this->request->getData());
		
		$event_id 			= $this->request->getData()['Conventionseasons']['event_id'];
		$student_name 		= $this->request->getData()['Conventionseasons']['student_name'];
		$school_name 		= $this->request->getData()['Conventionseasons']['school_name'];
		
		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		
		
		$arrCertData = array();
		
		$arrCertData['convention_name'] 	= $conventionD->name;
		$arrCertData['seadon_year'] 		= $conventionSD->season_year;
		$arrCertData['student_name'] 		= $student_name;
		$arrCertData['school_name'] 		= $school_name;
		$arrCertData['event_name'] 			= $eventD->event_name;
		
		//$this->prx($arrCertData);
		
		
		$this->set('arrCertData', $arrCertData); 
	
	}
	
	

}

?>
