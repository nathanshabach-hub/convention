<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading cr-es-header" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;width:100%;max-width:100%;overflow:hidden;box-sizing:border-box;">
				<a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" class="sidebar-toggle-btn d-md-none" style="background:var(--blue);color:#fff;border:none;border-radius:5px;padding:7px 11px;font-size:18px;line-height:1;cursor:pointer;text-decoration:none;">
					<i class="fa fa-bars"></i>
				</a>
				<span style="flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Distance Event Entries :: <?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)</span>
				<?php echo $this->Html->link('<< Back', ['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug], ['escape' => false, 'class' => 'btn btn-primary', 'style' => 'margin-left:auto;float:none;']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content" id="listID">
				<?php echo $this->element("Eventsubmissions/distanceseventsentries"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>