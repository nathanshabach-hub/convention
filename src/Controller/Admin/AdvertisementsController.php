<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

#[\AllowDynamicProperties]
class AdvertisementsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Advertisements.name' => 'asc']];

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
		
		$this->loadModel("Amenities");
        $this->loadModel("Cities");
    }

    private function getLatLng($location) {
        if (empty($location)) {
            return [];
        }

        $endpoint = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode((string)$location) . '&sensor=false';
        $response = @file_get_contents($endpoint);
        if ($response === false) {
            return [];
        }

        $payload = json_decode($response, true);
        if (empty($payload['results'][0]['geometry']['location'])) {
            return [];
        }

        $geo = $payload['results'][0]['geometry']['location'];

        return [$geo['lat'] ?? null, $geo['lng'] ?? null];
    }

    public function index() {

        $this->set('title', ADMIN_TITLE . 'Manage Ads');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageAds', '1');
        $this->set('adsList', '1');

        $separator = array();
        $condition = array();
        //$condition = array('Advertisements.parent_id' => 0);

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Advertisements->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Advertisements->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Advertisements->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Advertisements']['keyword']) && $this->request->getData()['Advertisements']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Advertisements']['keyword']);
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
            $condition[] = "(Advertisements.ad_title_en LIKE '%".addslashes($keyword)."%' OR Advertisements.ad_title_greek LIKE '%".addslashes($keyword)."%' OR Advertisements.ad_description_en LIKE '%".addslashes($keyword)."%' OR Advertisements.ad_description_greek LIKE '%".addslashes($keyword)."%')";
            $this->set('keyword', $keyword);
        }
        //pr($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $this->paginate = ['conditions' => $condition, 'limit' => 20, 'order' => ['Advertisements.name' => 'ASC']];
        $this->set('advertisements', $this->paginate($this->Advertisements));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Advertisements');
            $this->render('index');
        }
    }

    public function activatead($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Advertisements->updateAll(['status' => '1'], ["slug" => $slug]);
            $this->set('action', '/admin/advertisements/deactivatead/' . $slug);
            $this->set('status', 1);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deactivatead($slug = null) {
        if ($slug != '') {
            $this->viewBuilder()->setLayout("");
            $this->Advertisements->updateAll(['status' => '0'], ["slug" => $slug]);
            $this->set('action', '/admin/advertisements/activatead/' . $slug);
            $this->set('status', 0);
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin');
            $this->render('update_status');
        }
    }

    public function deletead($slug = null) {
        
		// to get details of category
		$catDetails = $this->Advertisements->find()->where(['Advertisements.slug' => $slug])->first();
		
		$this->Advertisements->deleteAll(["slug" => $slug]);
        $this->Flash->success('Ads details deleted successfully.');
        $this->redirect(['controller' => 'advertisements', 'action' => 'index']);
    }

    public function edit($slug = null) {
        $this->set('title', ADMIN_TITLE . 'Edit Ad');
        $this->viewBuilder()->setLayout('admin');
        
		$this->set('manageAds', '1');
        $this->set('adsList', '1');
		
		global $adsActivity;
		$this->set('adsActivity', $adsActivity);
		
		global $adsHousingType;
		$this->set('adsHousingType', $adsHousingType);
		
		global $adsFurnishTypes;
		$this->set('adsFurnishTypes', $adsFurnishTypes);
		
		global $adsSellingType;
		$this->set('adsSellingType', $adsSellingType);
		
		global $yesNoDD;
		$this->set('yesNoDD', $yesNoDD);
		
		global $adsSellingCondition;
		$this->set('adsSellingCondition', $adsSellingCondition);
		
		$amenitiesDD = $this->Amenities->find()->where(['Amenities.status' => 1])->order(['Amenities.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('amenitiesDD', $amenitiesDD);

        $cities = $this->Cities->find()->where(['Cities.status' => 1])->order(['Cities.name' => 'ASC'])->combine('id', 'name')->toArray();
		$this->set('cities', $cities);
		
        if ($slug) {
            $categories1 = $this->Advertisements->find()->where(['Advertisements.slug' => $slug])->first();
            $uid = $categories1->id;
        }
		
        $advertisements = $this->Advertisements->get($uid);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Advertisements->patchEntity($advertisements, $this->request->getData());
			
            if (count($data->getErrors()) == 0) {
                //$data->name = trim($this->request->getData()['Advertisements']['name']);
				
				$data->date_available = date("Y-m-d",strtotime($data->date_available));
				
				$renting_amenities = $this->request->getData()['Advertisements']['renting_amenities'];
				if(count($renting_amenities))
					$rentingAmenities = implode(",",$renting_amenities);
				else
					$rentingAmenities = '';
				
				$data->renting_amenities = $rentingAmenities;
				
				$data->modified = date("Y-m-d");
				
				if($data->activity_sell_rent == "Selling")
				{
					if($data->housing_type == "Plots of land")
					{
						$data->selling_condition 	= '';
						$data->furnished_status 	= '';
						$data->bedrooms 			= '';
						$data->bathrooms 			= '';
						$data->parking_available 	= '';
					}
					else
					{
						$data->selling_housing_type_land_parcel_number 	= '';
					}
				}
				
				// to get lat long of each ad
                $msgLL = '';
                if (!empty($this->request->getData()['latitude']) && !empty($this->request->getData()['longitude'])) {
                    $data->latitude 	= $this->request->getData()['latitude'];
					$data->longitude 	= $this->request->getData()['longitude'];
                } else {
                    $addressArr = array();
				    if(!empty($data->location))
					    $addressArr[] = $data->location;
				
				    $location_Full = implode(" ",$addressArr);
				    $location = str_replace(" ", "+", $location_Full);
				
				    $latLongArr = $this->getLatLng($location);
				
				    //$this->prx($latLongArr);
				
				    if(!empty($latLongArr[0]) && !empty($latLongArr[1]))
				    {
					    $data->latitude 	= $latLongArr[0];
					    $data->longitude 	= $latLongArr[1];
				    }
				    else
				    {
					    $msgLL = " Error :: Latitude and logitude does not seems to be correct.";
				    }
                }
				
				
                if ($this->Advertisements->save($data)) {
                    $this->Flash->success('Ad details updated successfully. '.$msgLL);
                    $this->redirect(['controller' => 'advertisements', 'action' => 'index']);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('advertisements', $advertisements);
    }


    public function updateSoldStatus($id, $status) {
        $this->Advertisements->updateAll(['sold_status' => $status], ["id" => $id]);
        exit;
    }
}

?>
