<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0 cr-es-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-es-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading cr-es-header">
				<span class="cr-es-title">View/Edit Event Submissions</span>
				<?php echo $this->Html->link(' + Submit New Event', ['controller' => 'eventsubmissions', 'action' => 'submitnewevent'], ['escape' => false, 'class' => 'btn btn-primary cr-es-add-btn']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content cr-es-card" id="listID">
				<?php echo $this->element("Eventsubmissions/viewlist"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>