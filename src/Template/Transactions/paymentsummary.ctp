<script type="text/javascript">
$(document).ready(function () {
	$("#addstudent").validate();
});
</script>
<?php
$localCurrText = '<small><b>(Amount shown in local currency to Convention region)</b></small>';
$onlinetPTxt = '&nbsp;&nbsp;<small><b>(Online payment only available for Australian Conventions, all other regions please request invoice)</b></small>';
?>
<div class="container-fluid p-0 cr-paymentsummary-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-paymentsummary-main">
		
		<div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
		
			<h2 class="mt-3 cr-paymentsummary-title">Payment Summary</h2>
			<p class="cr-paymentsummary-subtitle">Review the totals below to see exactly what is pending and what will be charged.</p>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form cr-paymentsummary-card">
				<!--<h2 class="form-title">Payment Summary</h2>-->
				<?php echo $this->Form->create($users, ['id'=>'addstudent', 'type' => 'file', 'class' =>' ']); ?>
					<div class="cr-pay-kpis">
						<div class="cr-pay-kpi">
							<span class="cr-pay-kpi-label">Students Pending</span>
							<span class="cr-pay-kpi-value"><?php echo $pendingPaymentStudents; ?></span>
						</div>
						<div class="cr-pay-kpi">
							<span class="cr-pay-kpi-label">Teachers Pending</span>
							<span class="cr-pay-kpi-value"><?php echo $pendingPaymentTeachers; ?></span>
						</div>
						<div class="cr-pay-kpi is-emphasis">
							<span class="cr-pay-kpi-label">Final Payable Amount</span>
							<span class="cr-pay-kpi-value"><?php echo number_format($payableAmount,2); ?></span>
						</div>
					</div>
					<div class="cr-pay-currency-note"><?php echo $localCurrText; ?></div>

					<div class="cr-pay-summary-grid">
						<div class="cr-pay-two-col">
							<div class="cr-pay-col">
								<h3 class="cr-pay-section-title">1. Student Charges</h3>
								<div class="form-group cr-pay-row">
									<label for="name">Price Structure</label>
									<span class="cr-pay-value"><?php echo $priceStructureCR[$CRDetails->price_structure]; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Total Students Registered</label>
									<span class="cr-pay-value"><?php echo $totalStudentsReg; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Already Paid Students</label>
									<span class="cr-pay-value"><?php echo $alreadyPaidStudents; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Students Awaiting Payment</label>
									<span class="cr-pay-value"><?php echo $pendingPaymentStudents; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Price Per Student</label>
									<span class="cr-pay-value"><?php echo number_format($pricePerStudent,2); ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Student Subtotal</label>
									<span class="cr-pay-value"><?php echo number_format($subTotalPaymentStudents,2); ?></span>
								</div>
								<?php
								if($totalStudentsApplicableDiscount>0)
								{
								?>
								<div class="cr-pay-section-divider" aria-hidden="true"></div>
								<div class="form-group cr-pay-row">
									<label for="name">Total Students Applicable For Discount</label>
									<span class="cr-pay-value"><?php echo $totalStudentsApplicableDiscount; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Discount Per Student</label>
									<span class="cr-pay-value"><?php echo number_format($perStudentDiscountAmount,2); ?>%</span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Total Discount Amount</label>
									<span class="cr-pay-value"><?php echo number_format($totalDiscountAmount,2); ?></span>
								</div>
								<?php
								}
								?>
								<div class="form-group cr-pay-row">
									<label for="name">Student Total After Discounts</label>
									<span class="cr-pay-value"><?php echo number_format($netPayableAmountStudent,2); ?></span>
								</div>
							</div>
							<div class="cr-pay-col">
								<h3 class="cr-pay-section-title">2. Teacher Charges</h3>
								<div class="form-group cr-pay-row">
									<label for="name">Total Teachers Registered</label>
									<span class="cr-pay-value"><?php echo $totalTeachersReg; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Already Paid Teachers</label>
									<span class="cr-pay-value"><?php echo $alreadyPaidTeachers; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Teachers Awaiting Payment</label>
									<span class="cr-pay-value"><?php echo $pendingPaymentTeachers; ?></span>
								</div>
								<div class="form-group cr-pay-row">
									<label for="name">Price Per Teacher</label>
									<span class="cr-pay-value"><?php echo number_format($pricePerTeacher,2); ?></span>
								</div>
							</div>
						</div>
						<div class="cr-pay-section-divider" aria-hidden="true"></div>
						<h3 class="cr-pay-section-title">3. Final Amount</h3>
					
					
					<div class="form-group cr-pay-row cr-pay-total-row">
						<label for="name">Final Payable Amount</label>
						<span class="cr-pay-value"><?php echo number_format($payableAmount,2); ?></span>
					</div>
					</div>
					
					
					
					<?php
					if($pendingPaymentStudents>0)
					{
					?>
					<div class="form-group form-btns cr-pay-actions">
						<button id="btn_online_payment" type="submit" class="btn btn-success cr-pay-btn cr-pay-btn-online">Proceed to online payment</button>
						<button id="btn_invoice" type="submit" class="btn btn-info cr-pay-btn cr-pay-btn-invoice">Request invoice from SCEE</button>
						<?php echo $this->Html->link('Cancel', ['controller'=>'conventionregistrations', 'action' => 'students'], ['class'=>'btn btn-secondary cr-pay-btn cr-pay-btn-cancel']); ?>
						<input type="hidden" name="hidd_pay_type" id="hidd_pay_type" value="" />
					</div>
					<div class="form-group form-btns cr-pay-note-wrap">
						<div class="clear cr-pay-note"><?php echo $onlinetPTxt; ?></div>
					</div>
					<?php
					}
					else
					{
					?>
					<div class="form-group form-btns cr-pay-note-wrap">
						<div class="clear cr-pay-good">No student found pending for payment.</div>
					</div>
					<div class="form-group form-btns cr-pay-actions">
						<?php echo $this->Html->link('Back to students list', ['controller'=>'conventionregistrations', 'action' => 'students'], ['class'=>'btn btn-secondary cr-pay-btn cr-pay-btn-cancel']); ?>
					</div>
					<?php
					}
					?>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () { //alert('ddddd');
		$('#btn_online_payment').click(function(){
			$('#hidd_pay_type').val('online');  
		});
		$('#btn_invoice').click(function(){
			$('#hidd_pay_type').val('invoice');  
		});
		
	});
</script>