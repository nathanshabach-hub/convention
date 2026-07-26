<?php

namespace App\Controller;
use App\Controller\AppController;

#[\AllowDynamicProperties]
class ConventionsController extends AppController {

    public function initialize(): void {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        // Load Files model

        $this->loadModel("Users");
        $this->loadModel("Emailtemplates");
        $this->loadModel("Conventions");
        $this->loadModel("Conventionseasons");
        $this->loadComponent("RequestHandler");
        $this->loadComponent("Paginator");
		

        // Set the layout
        // $this->layout = 'frontend';
    }

    private function ensureAdminSession() {
        $adminId = $this->request->getSession()->read('admin_id');
        if (!$adminId) {
            return $this->redirect('/admin/admins/login');
        }
        return null;
    }

    public function index() {
        $redirect = $this->ensureAdminSession();
        if ($redirect) {
            return $redirect;
        }

        $this->viewBuilder()->setLayout('admin');
        $this->set('title', defined('ADMIN_TITLE') ? ADMIN_TITLE . 'Manage Conventions' : 'Manage Conventions');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');

        global $conventionTypeDD;
        $this->set('conventionTypeDD', is_array($conventionTypeDD ?? null) ? $conventionTypeDD : []);

        if ($this->request->is('post')) {
            $action = (string)($this->request->getData('action') ?? '');
            $ids = array_filter(array_map('intval', (array)($this->request->getData('chkRecordId') ?? [])));
            if (!empty($ids) && ($action === 'Activate' || $action === 'Deactivate')) {
                $this->Conventions->updateAll(['status' => $action === 'Activate' ? '1' : '0'], ['id IN' => $ids]);
            }
        }

        $this->paginate = ['limit' => 20, 'order' => ['Conventions.id' => 'DESC']];
        $this->set('conventions', $this->paginate($this->Conventions));

        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('');
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Conventions');
            $this->render('index');
        }
    }

    public function activateconvention($slug = null) {
        $redirect = $this->ensureAdminSession();
        if ($redirect) {
            return $redirect;
        }
        if (!$slug) {
            return $this->response->withStatus(400);
        }

        $this->Conventions->updateAll(['status' => '1'], ['slug' => $slug]);
        $html = '<a href="/acp_demo_test/admin/conventions/deactivateconvention/' . h($slug) . '" title="Deactivate"><button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button></a>';
        return $this->response->withType('html')->withStringBody($html);
    }

    public function deactivateconvention($slug = null) {
        $redirect = $this->ensureAdminSession();
        if ($redirect) {
            return $redirect;
        }
        if (!$slug) {
            return $this->response->withStatus(400);
        }

        $this->Conventions->updateAll(['status' => '0'], ['slug' => $slug]);
        $html = '<a href="/acp_demo_test/admin/conventions/activateconvention/' . h($slug) . '" title="Activate"><button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button></a>';
        return $this->response->withType('html')->withStringBody($html);
    }

}

?>
