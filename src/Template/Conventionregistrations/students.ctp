<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0 cr-students-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-students-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading cr-students-header">
				<span class="cr-students-title">Student Registration</span>
				<?php echo $this->Html->link(' + Add Student', ['controller' => 'conventionregistrations', 'action' => 'addstudent'], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content cr-students-card" id="listID">
				<?php echo $this->element("Conventionregistrations/students"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>