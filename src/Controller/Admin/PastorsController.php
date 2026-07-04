<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Utility\Text;

#[\AllowDynamicProperties]
class PastorsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');

        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action !== 'forgotPassword' && $action !== 'logout') {
            if (!$loggedAdminId && $action !== 'login' && $action !== 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }

        $this->Users = $this->loadModel('Users');
    }

    public function index()
    {
        $this->set('title', ADMIN_TITLE . 'Pastors');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $pastors = $this->Users->find()
            ->where([
                'Users.user_type' => 'Teacher_Parent',
                'LOWER(TRIM(Users.title))' => 'pastor'
            ])
            ->order(['Users.id' => 'DESC'])
            ->all();

        $this->set('pastors', $pastors);
    }

    public function adddetails()
    {
        $this->set('title', ADMIN_TITLE . 'Add Pastor Details');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');

        $pastor = $this->Users->newEntity([]);

        if ($this->request->is('post')) {
            $postData = $this->request->getData('Users', []);
            $firstName = trim((string)($postData['first_name'] ?? ''));
            $lastName = trim((string)($postData['last_name'] ?? ''));
            $phone = trim((string)($postData['phone'] ?? ''));
            $email = trim((string)($postData['email_address'] ?? ''));
            $localChurch = trim((string)($postData['local_church'] ?? ''));

            if ($firstName === '' || $lastName === '' || $phone === '' || $email === '' || $localChurch === '') {
                $this->Flash->error('Please complete all required fields.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->Flash->error('Please enter a valid email address.');
            } else {
                $existing = $this->Users->find()->where(['Users.email_address' => $email])->first();
                if ($existing) {
                    $this->Flash->error('Email address already exists.');
                } else {
                    $randomPassword = Text::uuid();
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($randomPassword, '$2a$07$' . $salt . '$');

                    $generatedSlug = strtolower(trim((string)preg_replace('/[^a-z0-9]+/i', '-', $firstName . '-' . $lastName . '-' . time()), '-'));

                    $saveData = [
                        'slug' => $generatedSlug,
                        'user_type' => 'Teacher_Parent',
                        'title' => 'Pastor',
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'phone' => $phone,
                        'email_address' => $email,
                        // Reuse existing free-text location field for local church in this schema.
                        'bill_to_street' => $localChurch,
                        'password' => $password,
                        'status' => 1,
                        'activation_status' => 1,
                        'is_judge' => 0,
                        'created' => date('Y-m-d H:i:s'),
                        'modified' => date('Y-m-d H:i:s'),
                    ];

                    $pastor = $this->Users->patchEntity($pastor, $saveData);

                    if ($this->Users->save($pastor)) {
                        $this->Flash->success('Pastor details added successfully.');
                        return $this->redirect(['controller' => 'pastors', 'action' => 'index']);
                    }

                    $this->Flash->error('Pastor details could not be saved. Please try again.');
                }
            }
        }

        $this->set('pastor', $pastor);
    }

    public function delete($slug = null)
    {
        if (empty($slug)) {
            $this->Flash->error('Invalid pastor record.');
            return $this->redirect(['controller' => 'pastors', 'action' => 'index']);
        }

        $pastor = $this->Users->find()
            ->where([
                'Users.slug' => $slug,
                'Users.user_type' => 'Teacher_Parent',
                'LOWER(TRIM(Users.title))' => 'pastor'
            ])
            ->first();

        if (!$pastor) {
            $this->Flash->error('Pastor record not found.');
            return $this->redirect(['controller' => 'pastors', 'action' => 'index']);
        }

        if ($this->Users->delete($pastor)) {
            $this->Flash->success('Pastor contact deleted successfully.');
        } else {
            $this->Flash->error('Pastor contact could not be deleted. Please try again.');
        }

        return $this->redirect(['controller' => 'pastors', 'action' => 'index']);
    }
}
