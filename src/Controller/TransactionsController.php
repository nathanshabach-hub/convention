<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Email;
use Cake\I18n\I18n;

#[AllowDynamicProperties]
class TransactionsController extends AppController {
	public $Conventionregistrationstudents = null;
	public $Transactions = null;
	public $Transactionstudents = null;
	public $Crstudentevents = null;
	public $Transactionteachers = null;

    public function initialize(): void {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->loadModel("Users"); 
		$this->loadModel("Emailtemplates");
		$this->loadModel("Conventions");
		$this->loadModel("Conventionseasons");
		$this->loadModel("Events");
		$this->loadModel("Divisions");
		$this->loadModel("Seasons");
		$this->loadModel("Conventionregistrations");
		$this->loadModel("Conventionregistrationteachers");
		$this->loadModel("Conventionregistrationstudents");
		$this->loadModel("Transactions");
		$this->loadModel("Transactionstudents");
		$this->loadModel("Settings");
		$this->loadModel("Crstudentevents");
		$this->loadModel("Transactionteachers");
    }
	
	public function paymentsummary() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Payment Summary" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('active_cr_students','active');

		$user_id = $this->request->session()->read("user_id");
		$userDetails = null;
		if (!empty($user_id)) {
			$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		}
        $this->set('userDetails', $userDetails);
		
		// to get admin details
		$adminInfo = $this->getAdminInfo();
		
		// to get price structure
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		// to get all the events having discount applicable
		$discountEvents = $this->Events->find()->where(['Events.discount_allowed' => 1])->all();
		$arrEventsDiscount = array();
		foreach($discountEvents as $dicsEv)
		{
			$arrEventsDiscount[] = $dicsEv->id;
		}
		
		// to get % of discount applicable
		$settingsDiscount = $this->Settings->find()->where(['Settings.id' => 1])->first();
		$sess_selected_convention_registration_id = null;
		$CRDetails = null;
		$ConvSeasonD = null;
		
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
			$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $sess_selected_convention_registration_id])->first();
			
			// to get price of supervisor registration
			$ConvSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $CRDetails->conventionseason_id])->first();
			
			// to check price structure
			if(!($CRDetails->price_per_student>0))
			{
				$this->Flash->error('Please choose price structure before payment.');
				return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'pricestructure']);
			}
			
			$this->set('CRDetails', $CRDetails);
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			return $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		// now check total students registered in this convention registration
		$totalStudentsReg = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('totalStudentsReg', $totalStudentsReg);
		
		// now calculate for how many students already paid for
		$alreadyPaidStudents = $this->Transactionstudents->find()->where(['Transactionstudents.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('alreadyPaidStudents', $alreadyPaidStudents);
		
		// now calculate
		$pendingPaymentStudents = $totalStudentsReg-$alreadyPaidStudents;
		$this->set('pendingPaymentStudents', $pendingPaymentStudents);
		
		// price per student
		$pricePerStudent = $CRDetails->price_per_student;
		$this->set('pricePerStudent', $pricePerStudent);
		
		$subTotalPaymentStudents = ($pendingPaymentStudents*$pricePerStudent);
		$this->set('subTotalPaymentStudents', ($pendingPaymentStudents*$pricePerStudent));
		
		
		/* Now Calculate Discount applicable for which students - Starts */
		$studentApplicableForDiscount = array();
		
		// 1. To get list of all students
		$allStudentsRegD = $this->Conventionregistrationstudents->find()->where(["Conventionregistrationstudents.conventionregistration_id" => $sess_selected_convention_registration_id])->order(["id" => "ASC"])->all();
		//$this->prx($allStudentsRegD);
		foreach($allStudentsRegD as $convregdisc)
		{
			// to check if this student is having an event that is applicable for discount or not
			if(!empty($convregdisc->event_ids) && $convregdisc->event_ids != NULL)
			{
				$thisStudentEventsExplode = explode(",",$convregdisc->event_ids);
				
				// check each event of this student, if its allowed for discount
				foreach($thisStudentEventsExplode as $st_event_id)
				{
					if(in_array($st_event_id,(array)$arrEventsDiscount))
					{
						// push student id in array, if not exists
						if(!in_array($convregdisc->student_id,(array)$studentApplicableForDiscount))
						{
							// to check if this student already get discount benefit or not
							$checkStDiscount = $this->Transactionstudents->find()->where(['Transactionstudents.conventionregistration_id' => $CRDetails->id, 'Transactionstudents.conventionregistrationstudent_id' => $convregdisc->id, 'Transactionstudents.student_id' => $convregdisc->student_id])->first();
							
							if(!$checkStDiscount)
							{
								$studentApplicableForDiscount[] = $convregdisc->student_id;
							}
						}
					}
				}
			}
		}
		//$this->prx($settingsDiscount);
		
		$totalDiscountAmount = 0;
		$totalStudentsApplicableDiscount = 0;
		if(count($studentApplicableForDiscount))
		{
			$totalStudentsApplicableDiscount = count((array)$studentApplicableForDiscount);
			$totalDiscountAmount = (($totalStudentsApplicableDiscount*$pricePerStudent*$settingsDiscount->scripture_trophy_discount)/100);
		}
		
		$this->set('totalStudentsApplicableDiscount', $totalStudentsApplicableDiscount);
		$this->set('perStudentDiscountAmount', $settingsDiscount->scripture_trophy_discount);
		$this->set('totalDiscountAmount', $totalDiscountAmount);
		
		
		
		
		$netPayableAmountStudent = ($subTotalPaymentStudents)-($totalDiscountAmount);
		$this->set('netPayableAmountStudent', $netPayableAmountStudent);
		
		/* End to calculate Discount */
		
		
		
		
		
		
		
		
		
		
		
		
		//now check for total supervisors registered for this convention registration
		$totalTeachersReg = $this->Conventionregistrationteachers->find()->where(['Conventionregistrationteachers.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('totalTeachersReg', $totalTeachersReg);
		
		// now calculate for how many teachers already paid for
		$alreadyPaidTeachers = $this->Transactionteachers->find()->where(['Transactionteachers.conventionregistration_id' => $sess_selected_convention_registration_id])->count();
		$this->set('alreadyPaidTeachers', $alreadyPaidTeachers);
		
		// now calculate
		$pendingPaymentTeachers = $totalTeachersReg-$alreadyPaidTeachers;
		$this->set('pendingPaymentTeachers', $pendingPaymentTeachers);
		
		// price per teacher
		$pricePerTeacher = $ConvSeasonD->supervisor_registration_fees;
		$this->set('pricePerTeacher', $pricePerTeacher);
		
		
		// calculate payable amount
		$payableAmount = ($netPayableAmountStudent)+($pendingPaymentTeachers*$pricePerTeacher);
		$this->set('payableAmount', $payableAmount);
		
		
		
		
		
		
		
		
		
		if ($this->request->is('post'))
		{
			//$this->prx($this->request->getData());
			
			$payType = $this->request->getData()['hidd_pay_type'];
			$transactionStatus = 0;
			
			if($payType == "online")
			{
				$transactionStatus = 2;
			}
			else
			if($payType == "invoice")
			{
				$transactionStatus = 3;
			}
			
			// Step 1:: Add 1 record into transactions
			$transactions = $this->Transactions->newEntity([]);
			$dataT = $this->Transactions->patchEntity($transactions, array());

			$dataT->slug 										= "transaction-cr-".$sess_selected_convention_registration_id.'-'.time();
			$dataT->conventionregistration_id					= $sess_selected_convention_registration_id;
			$dataT->conventionseason_id							= $CRDetails->conventionseason_id;
			$dataT->convention_id								= $CRDetails->convention_id;
			$dataT->user_id										= $CRDetails->user_id;
			$dataT->season_id 									= $CRDetails->season_id;
			$dataT->season_year 								= $CRDetails->season_year;
			
			$dataT->price_structure 							= $CRDetails->price_structure;
			$dataT->price_per_student 							= $pricePerStudent;
			$dataT->price_per_teacher 							= $pricePerTeacher;
			$dataT->payable_amount 								= $payableAmount;
			$dataT->tax_percent 								= $adminInfo->tax_percent;
			$dataT->tax_amount 									= ($payableAmount*$adminInfo->tax_percent)/100;
			$dataT->total_amount 								= $payableAmount+(($payableAmount*$adminInfo->tax_percent)/100);
			
			// discount related
			$dataT->total_students_applicable_for_discount 		= $totalStudentsApplicableDiscount;
			$dataT->discount_per_student 						= $settingsDiscount->scripture_trophy_discount;
			$dataT->total_discount_applied 						= $totalDiscountAmount;
			$dataT->final_amount_paid 							= $payableAmount;
			
			$dataT->status 										= $transactionStatus;
			$dataT->created 									= date('Y-m-d H:i:s');
			
			$resultT 											= $this->Transactions->save($dataT);
			
			$transaction_id 									= $resultT->id;
			$transaction_slug 									= $resultT->slug;
			
			
			
			// Step 2:: Add multiple records into transactionstudents
			// to get list of total students registered
			$condAllStudReg = array();
			$condAllStudReg[] = "(Conventionregistrationstudents.conventionregistration_id = '".$sess_selected_convention_registration_id."')";
			$allStudentsReg = $this->Conventionregistrationstudents->find()->where($condAllStudReg)->all();
			
			$cntrPendingStudents = 0;
			foreach($allStudentsReg as $allst)
			{
				// to check if amount paid for this student or not
				$checkStudentF = $this->Transactionstudents->find()->where(['Transactionstudents.conventionregistration_id' => $sess_selected_convention_registration_id,'Transactionstudents.student_id' => $allst->student_id])->first();
				if(!$checkStudentF)
				{
					$applicableForDiscount 		= 0;
					$percentDiscountApplied 	= 0;
					$amountDiscountApplied 		= 0;
					$finalPaidAmount 			= $pricePerStudent;
					
					// to check if this student is applicable for discount or not
					if(in_array($allst->student_id,(array)$studentApplicableForDiscount))
					{
						$applicableForDiscount 		= 1;
						$percentDiscountApplied 	= $settingsDiscount->scripture_trophy_discount;
						$amountDiscountApplied 		= (($pricePerStudent*$settingsDiscount->scripture_trophy_discount)/100);
						$finalPaidAmount 			= $pricePerStudent-$amountDiscountApplied;
					}
					
					// add new record to transactionstudents table
					$transactionstudents = $this->Transactionstudents->newEntity([]);
					$dataTS = $this->Transactionstudents->patchEntity($transactionstudents, $this->request->getData());
					
					$dataTS->transaction_id							= $transaction_id;
					$dataTS->conventionregistration_id				= $sess_selected_convention_registration_id;
					$dataTS->conventionregistrationstudent_id		= $allst->id;
					$dataTS->student_id								= $allst->student_id;
					$dataTS->user_id								= $CRDetails->user_id;
					$dataTS->season_id 								= $CRDetails->season_id;
					$dataTS->season_year 							= $CRDetails->season_year;
					
					$dataTS->paid_amount 							= $pricePerStudent;
					
					$dataTS->applicable_for_discount 				= $applicableForDiscount;
					$dataTS->percent_discount_applied 				= $percentDiscountApplied;
					$dataTS->amount_discount_applied 				= $amountDiscountApplied;
					$dataTS->final_paid_amount 						= $finalPaidAmount;
					
					$dataTS->status 								= $transactionStatus;
					$dataTS->created 								= date('Y-m-d H:i:s');

					$resultTS = $this->Transactionstudents->save($dataTS);
					
					$cntrPendingStudents++;
				}
			}
			
			// Step 3:: Add multiple records into transactionteachers
			// to get list of total teachers registered
			$condAllTeachersReg = array();
			$condAllTeachersReg[] = "(Conventionregistrationteachers.conventionregistration_id = '".$sess_selected_convention_registration_id."')";
			$allTeachersReg = $this->Conventionregistrationteachers->find()->where($condAllTeachersReg)->all();
			
			$cntrPendingTeachers = 0;
			foreach($allTeachersReg as $alltea)
			{
				// to check if amount paid for this teacher or not
				$checkTeacherF = $this->Transactionteachers->find()->where(['Transactionteachers.conventionregistration_id' => $sess_selected_convention_registration_id,'Transactionteachers.teacher_id' => $alltea->teacher_id])->first();
				if(!$checkTeacherF)
				{
					// add new record to Transactionteachers table
					$transactionteachers = $this->Transactionteachers->newEntity([]);
					$dataTT = $this->Transactionteachers->patchEntity($transactionteachers, $this->request->getData());
					
					$dataTT->transaction_id							= $transaction_id;
					$dataTT->conventionregistration_id				= $sess_selected_convention_registration_id;
					$dataTT->conventionregistrationteacher_id		= $alltea->id;
					$dataTT->teacher_id								= $alltea->teacher_id;
					$dataTT->user_id								= $CRDetails->user_id;
					$dataTT->season_id 								= $CRDetails->season_id;
					$dataTT->season_year 							= $CRDetails->season_year;
					
					$dataTT->paid_amount 							= $pricePerTeacher;
					$dataTT->status 								= $transactionStatus;
					$dataTT->created 								= date('Y-m-d H:i:s');

					$resultTT = $this->Transactionteachers->save($dataTT);
					
					$cntrPendingTeachers++;
				}
			}
			
			
			
			// to get all students who already
			if($cntrPendingStudents>0 || $cntrPendingTeachers>0)
			{
				// to check payment type
				if($payType == "online")
				{
					$this->redirect(['controller' => 'transactions', 'action' => 'paymentprocess',$transaction_slug]);
				}
				else
				if($payType == "invoice")
				{
					$this->redirect(['controller' => 'transactions', 'action' => 'invoiceprocess',$transaction_slug]);
				}
				
				$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
			}
			else
			{
				$this->Flash->error('Invalid payment amount.');
				$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
			}
			
		}
		
    } // end function
	
	public function paymentprocess($transaction_slug = null) {		
		
		$this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
		$user_id = $this->request->session()->read("user_id");
		$userDetails = null;
		if (!empty($user_id)) {
			$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		}
        $this->set('userDetails', $userDetails);
		
		// to get admin details
		$settingsInfo = $this->getSettingsInfo();
		
		if($this->request->session()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->session()->read("sess_selected_convention_registration_id");
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			return $this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
        $transactionInfo    =   $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug, 'Transactions.conventionregistration_id' => $sess_selected_convention_registration_id])->contain(['Conventions','Seasons','Users'])->first();
		
		//$this->prx($bookingInfo);
		
        if(empty($transactionInfo))
		{
            $this->Flash->error('Invalid transaction information.');
			return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
        }
		else
		{
            // to get details for convention registration
			
			$totalAmount 	= $transactionInfo->final_amount_paid;
            $transactionId 	= $transactionInfo->id;
			
			/* As we discussed on 12-May-2023, when a user is approaching for online payment, 
			need to send an email to accounts and events team */
			$settingsD	= $this->Settings->find()->where(['Settings.id' => 1])->first();
				
			$emailId = $settingsD->accounts_team_email;
						
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '10'])->first();

			$toRepArray = array('[!school_name!]','[!customer_code!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]');
			$fromRepArray = array($transactionInfo->Users['first_name'],$transactionInfo->Users['customer_code'],$transactionInfo->Conventions['name'],$transactionInfo->season_year,CURR,number_format($transactionInfo->final_amount_paid,2));

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
			
			
			
			
			// to count number of students for which this amount paid for
			$total_students_paid_for = $this->Transactionstudents->find()->where(['Transactionstudents.transaction_id' => $transactionId])->count();
			
			if(PAYPAL_MODE == "Sandbox")
				$paypalURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			else
				$paypalURL = 'https://www.paypal.com/cgi-bin/webscr';
			
			//echo $paypalURL;exit;
			
			$itemName 	= SITE_TITLE." Payment for ".$total_students_paid_for." student(s) for convention [".$transactionInfo->Conventions['name']."] and season [".$transactionInfo->Seasons['season_year']."] by school [".$userDetails->first_name."] Customer Code: [".$userDetails->customer_code."]";
			$itemNumber = $transactionInfo->slug;

			$this->viewBuilder()->disableAutoLayout();
			$this->set(compact('paypalURL', 'settingsInfo', 'itemName', 'itemNumber', 'totalAmount', 'transaction_slug'));
			return $this->render('paymentprocess');
        }
	} // end function
	
	public function paymentsuccess($transaction_slug=null){
		$requestData = $this->request->getData() + $this->request->getQueryParams();

		if($transaction_slug)
		{
			$transactionD = $this->Transactions->find()
				->where(['Transactions.slug' => $transaction_slug])
				->contain(["Conventions","Users","Seasons"])
				->first();

			if($transactionD)
			{
				$paymentStatusReceived = $requestData['payment_status'] ?? null;
				$itemNumber = $requestData['item_number'] ?? null;

				if($transaction_slug == $itemNumber)
				{
					$this->Transactions->updateAll([
						'transaction_data' => json_encode($requestData),
						'modified' => date('Y-m-d H:i:s'),
					], ["slug" => $transaction_slug]);
					$this->Flash->success("Your payment confirmed successfully. You will receive confirmation email shortly.");
				}
				else
				{
					$this->Flash->error("Transaction mismatch.");
				}
			}
			else
			{
				$this->Flash->error("Transaction not found.");
			}
		}
		else
		{
			$this->Flash->error("Invalid information received");
		}

		$emailId = 'voizacinc@gmail.com';
		$subjectToSend = 'Function - paymentsuccess - To test live payment ' . time();
		$messageToSend = 'Request data = ' . json_encode($requestData);

		$email = new Email();
		$email->template('default', 'admintemplate')
			->emailFormat('html')
			->to($emailId)
			->cc(HEADERS_CC)
			->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
			->subject($subjectToSend)
			->viewVars(['content_for_layout' => $messageToSend])
			->send();

		return $this->redirect(['controller'=>'users', 'action' => 'dashboard']);
	}

	public function transactioninfo() {
		$this->Crstudentevents->updateAll(['conventionseason_id' => 0], ["id > " => 0]);

		$this->autoRender = false;
		return $this->response->withStringBody('OK');
	}

	public function inpnotify(){
		$debug = true;
		$useSandbox = true;
		$logFile = 'ipn.log';
		$requestData = $this->request->getData();
		$rawPostData = file_get_contents('php://input');
		$rawPostArray = explode('&', $rawPostData);

		$emailId = 'voizacinc@gmail.com';
		$subjectToSend = 'Function - inpnotify - 1 - To test live payment ' . time();
		$messageToSend = 'raw_post_array = ' . json_encode($rawPostArray);
		$messageToSend .= '<br><br>Request data = ' . json_encode($requestData);

		$email = new Email();
		$email->template('default', 'admintemplate')
			->emailFormat('html')
			->to($emailId)
			->cc(HEADERS_CC)
			->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
			->subject($subjectToSend)
			->viewVars(['content_for_layout' => $messageToSend])
			->send();

		$myPost = array();
		foreach ($rawPostArray as $keyval) {
			$keyval = explode('=', $keyval);
			if (count($keyval) == 2) {
				$myPost[$keyval[0]] = urldecode($keyval[1]);
			}
		}

		$req = 'cmd=_notify-validate';
		foreach ($myPost as $key => $value) {
			$req .= '&' . $key . '=' . urlencode($value);
		}

		$paypalUrl = $useSandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$ch = curl_init($paypalUrl);
		if ($ch === false) {
			$this->autoRender = false;
			return $this->response->withStatus(502)->withStringBody('IPN init failed');
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		if ($debug) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		$res = curl_exec($ch);
		if (curl_errno($ch) != 0) {
			if ($debug) {
				error_log(date('[Y-m-d H:i e] ') . "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, $logFile);
			}
			curl_close($ch);
			$this->autoRender = false;
			return $this->response->withStatus(502)->withStringBody('IPN validation failed');
		}

		if ($debug) {
			error_log(date('[Y-m-d H:i e] ') . 'HTTP request of validation request:' . curl_getinfo($ch, CURLINFO_HEADER_OUT) . " for IPN payload: $req" . PHP_EOL, 3, $logFile);
			error_log(date('[Y-m-d H:i e] ') . "HTTP response of validation request: $res" . PHP_EOL, 3, $logFile);
		}
		curl_close($ch);

		$tokens = explode("\r\n\r\n", trim((string)$res));
		$res = trim((string)end($tokens));

		$emailId = 'voizacinc@gmail.com';
		$subjectToSend = 'Function - inpnotify - 2 - To test live payment ' . time();
		$messageToSend = 'res = ' . json_encode($res);
		$messageToSend .= '<br><br>tokens = ' . json_encode($tokens);

		$email = new Email();
		$email->template('default', 'admintemplate')
			->emailFormat('html')
			->to($emailId)
			->cc(HEADERS_CC)
			->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
			->subject($subjectToSend)
			->viewVars(['content_for_layout' => $messageToSend])
			->send();

		if (strcmp($res, 'VERIFIED') == 0) {
			$paymentStatus = $requestData['payment_status'] ?? null;
			$txnId = $requestData['txn_id'] ?? null;
			$transactionSlug = $requestData['item_number'] ?? null;

			if ($paymentStatus == 'Completed') {
				$emailId = 'voizacinc@gmail.com';
				$subjectToSend = 'Function - inpnotify - 3 - Inside strcmp VERIFIED - To test live payment ' . time();
				$messageToSend = 'payment_status = ' . json_encode($paymentStatus);
				$messageToSend .= '<br><br>res = ' . json_encode($res);

				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(HEADERS_CC)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();

				$transactionD = $this->Transactions->find()->where(['Transactions.slug' => $transactionSlug])->contain(['Conventions','Users'])->first();
				if ($transactionD) {
					$this->Transactions->updateAll([
						'status' => '1',
						'modified' => date("Y-m-d H:i:s"),
						'transaction_id_received' => (string)$txnId,
						'transaction_data' => $rawPostData,
					], ["id" => $transactionD->id]);

					$this->Transactionstudents->updateAll(['status' => '1', 'modified' => date("Y-m-d H:i:s")], ["transaction_id" => $transactionD->id]);
					$this->Transactionteachers->updateAll(['status' => '1', 'modified' => date("Y-m-d H:i:s")], ["transaction_id" => $transactionD->id]);

					$emailId = $transactionD->Users['email_address'];
					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '5'])->first();
					$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!customer_code!]');
					$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->total_amount,2),$transactionD->Users['customer_code']);
					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);

					$email = new Email();
					$email->template('default', 'admintemplate')
						->emailFormat('html')
						->to($emailId)
						->cc(HEADERS_CC)
						->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
						->subject($subjectToSend)
						->viewVars(['content_for_layout' => $messageToSend])
						->send();

					$settingsD = $this->Settings->find()->where(['Settings.id' => 1])->first();
					$emailId = $settingsD->accounts_team_email;
					$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '6'])->first();
					$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);

					$email = new Email();
					$email->template('default', 'admintemplate')
						->emailFormat('html')
						->to($emailId)
						->cc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
						->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
						->subject($subjectToSend)
						->viewVars(['content_for_layout' => $messageToSend])
						->send();
				}
			}
		} elseif (strcmp($res, 'INVALID') == 0 && $debug) {
			error_log(date('[Y-m-d H:i e] ') . "Invalid IPN: $req" . PHP_EOL, 3, $logFile);
		}

		$this->autoRender = false;
		return $this->response->withStringBody('OK');
	}
	
	public function cancelbooking($transaction_slug=null){
		
		if($transaction_slug)
		{
			$transactionD 		= $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug])->first();
			
			if($transactionD)
			{
				$this->Transactions->deleteAll(["slug" => $transaction_slug]);
				$this->Transactionstudents->deleteAll(["transaction_id" => $transactionD->id]);
				$this->Transactionteachers->deleteAll(["transaction_id" => $transactionD->id]);
				$this->Flash->error("Your transaction has been cancelled.");
			}
			else
			{
				$this->Flash->success("Transaction not found.");
			}
		}
		else
		{
			$this->Flash->success("Invalid transaction information.");
		}
		
		$this->redirect(['controller'=>'conventionregistrations', 'action' => 'students']);
	}
	
	public function invoiceprocess($transaction_slug=null){
		
		if($transaction_slug)
		{
			$transactionD 		= $this->Transactions->find()->where(['Transactions.slug' => $transaction_slug])->contain(["Conventions","Users","Seasons"])->first();
			if($transactionD)
			{
				//$this->prx($transactionD);
				
				/* EMAIL CODE STARTED */
				
				/* 1. Send invoice request received confirmation email to school admin */
				$emailId = $transactionD->Users['email_address'];
						
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '7'])->first();

				$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!customer_code!]');
				$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->final_amount_paid,2),$transactionD->Users['customer_code']);

				$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
				$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
				
				//echo $messageToSend; exit;
				
				$email = new Email();
				$email->template('default', 'admintemplate')
					->emailFormat('html')
					->to($emailId)
					->cc(HEADERS_CC)
					->from([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
					->subject($subjectToSend)
					->viewVars(['content_for_layout' => $messageToSend])
					->send();
					
				
				/* 2. Send invoice request received email to accounts team */
				$settingsD	= $this->Settings->find()->where(['Settings.id' => 1])->first();
				
				$emailId = $settingsD->accounts_team_email;
						
				$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '8'])->first();

				$toRepArray = array('[!school_name!]','[!convention_name!]','[!season_year!]','[!CURR!]','[!total_amount!]','[!customer_code!]');
				$fromRepArray = array($transactionD->Users['first_name'],$transactionD->Conventions['name'],$transactionD->season_year,CURR,number_format($transactionD->final_amount_paid,2),$transactionD->Users['customer_code']);

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
				
				/* EMAIL CODE ends */
				
				$this->Flash->success("We have successfully received your invoice request. We will review and send you an email with invoice.");
			}
			else
			{
				$this->Flash->error("Transaction not found.");
			}
		}
		else
		{
			$this->Flash->error("Invalid information received");
		}

		$this->redirect(['controller'=>'users', 'action' => 'dashboard']);
	}
	
	public function mytransactions() {

        $this->userLoginCheck();
        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Transactions List" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('active_transactions','active');
		
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		global $paymentStatus;
		$this->set('paymentStatus', $paymentStatus);
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$userDetails = null;
		if (!empty($user_id)) {
			$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		}
        $this->set('userDetails', $userDetails);

        $separator = array();
        $condition = array();
		
		$condition[] = "(Transactions.user_id = '".$user_id."')";

        if ($this->request->is('post')) {
            if (isset($this->request->getData()['action'])) {
                $idList = implode(',', $this->request->getData()['chkRecordId']);
                $action = $this->request->getData()['action'];
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Transactions->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Transactions->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Transactions->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if (isset($this->request->getData()['Transactions']['keyword']) && $this->request->getData()['Transactions']['keyword'] != '') {
                $keyword = trim($this->request->getData()['Transactions']['keyword']);
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

		$query = $this->Transactions->find()
			->contain(['Conventions', 'Users'])
			->where($condition);

		if (isset($keyword) && $keyword != '') {
			$separator[] = 'keyword:' . urlencode($keyword);
			$query = $query->where(function ($exp) use ($keyword) {
				return $exp->like('Transactions.name', '%' . addslashes($keyword) . '%');
			});
			$this->set('keyword', $keyword);
		}

		$separator = implode("/", $separator);
		$this->set('separator', $separator);

		$this->paginate = [
			'limit' => 50,
			'order' => ['Transactions.id' => 'DESC']
		];
		$this->set('transactions', $this->paginate($query));

		if (strtolower($this->request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
			$this->viewBuilder()->disableAutoLayout();
			$this->viewBuilder()->setTemplatePath('Element' . DS . 'Transactions');
			return $this->render('mytransactions');
		}
    }
	
	public function viewdetails($slug = null) {
		
		$this->userLoginCheck();
		$this->schoolAdminLoginCheck();
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewBuilder()->setLayout("home");
        $this->set("title_for_layout", "Transaction Details " . TITLE_FOR_PAGES);
		
		$this->set('active_transactions','active');
		
		global $priceStructureCR;
		$this->set('priceStructureCR', $priceStructureCR);
		
		global $paymentStatus;
		$this->set('paymentStatus', $paymentStatus);
		
        $msgString = '';

		$user_id = $this->request->session()->read("user_id");
		$userDetails = null;
		if (!empty($user_id)) {
			$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		}
        $this->set('userDetails', $userDetails);
		
		if ($slug)
		{
            $transactionD = $this->Transactions->find()->where(['Transactions.slug' => $slug])->contain(['Conventions','Users'])->first();
			$this->set('transactionD', $transactionD);
            
			if($transactionD)
			{
				// to get the students list of this transaction
				$transactionStudents = $this->Transactionstudents->find()->where(['Transactionstudents.transaction_id' => $transactionD->id])->order(["Transactionstudents.id" => "ASC"])->contain(['Users'])->all();
				$this->set('transactionStudents', $transactionStudents);
				
				// to get the teachers list of this transaction
				$transactionTeachers = $this->Transactionteachers->find()->where(['Transactionteachers.transaction_id' => $transactionD->id])->order(["Transactionteachers.id" => "ASC"])->contain(['Users'])->all();
				$this->set('transactionTeachers', $transactionTeachers);
			}
			else
			{
				$this->Flash->error('Transaction not found.');
				$this->redirect(['controller' => 'transactions', 'action' => 'index']);
			}
        }
		else
		{
			$this->Flash->error('Invalid transaction.');
			$this->redirect(['controller' => 'transactions', 'action' => 'index']);
		}
		
    }

}

?>
