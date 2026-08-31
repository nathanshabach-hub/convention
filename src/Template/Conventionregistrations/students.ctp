<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0 cr-students-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-students-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading cr-students-header" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;width:100%;max-width:100%;overflow:hidden;box-sizing:border-box;">
				<span class="cr-students-title" style="min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Student Registration</span>
				<div class="cr-students-actions" style="display:flex; gap:8px; margin-left:auto; flex-wrap:wrap; justify-content:flex-end;">
					<?php echo $this->Html->link(' + Add Student', ['controller' => 'conventionregistrations', 'action' => 'addstudent'], ['escape' => false, 'class' => 'btn btn-primary', 'style' => 'float:none;']); ?>
					<?php if (!empty($resultsReleased)): ?>
					<?php echo $this->Html->link('<i class="fa fa-download"></i> Judges Evaluations', ['controller' => 'conventionregistrations', 'action' => 'downloadallresults', '?' => ['package' => 'judges-evaluations']], ['escape' => false, 'class' => 'btn btn-info', 'target' => '_blank', 'rel' => 'noopener', 'style' => 'float:none;']); ?>
					<?php echo $this->Html->link('<i class="fa fa-download"></i> Download All Results', ['controller' => 'conventionregistrations', 'action' => 'downloadallresults'], ['escape' => false, 'class' => 'btn btn-success', 'target' => '_blank', 'rel' => 'noopener', 'style' => 'float:none;']); ?>
					<?php endif; ?>
				</div>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content cr-students-card" id="listID">
				<?php echo $this->element("Conventionregistrations/students"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>