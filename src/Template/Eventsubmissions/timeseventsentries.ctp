<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.cr-times-page {
	background: linear-gradient(180deg, #f6f9fc 0%, #edf3f8 100%);
	padding-bottom: 24px;
}
.cr-times-page .teachers-top-heading {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 16px;
	margin: 20px 0 12px;
	padding: 16px 18px;
	border: 1px solid #d6e2ee;
	border-radius: 10px;
	background: #ffffff;
	box-shadow: 0 6px 18px rgba(14, 53, 94, 0.06);
}
.cr-times-page .teachers-top-heading > span {
	font-size: 21px;
	font-weight: 700;
	line-height: 1.3;
	color: #17324a;
}
.cr-times-page .teachers-top-heading .btn {
	flex: 0 0 auto;
	border-radius: 8px;
	font-weight: 600;
	padding: 8px 14px;
}
.cr-times-page .m_content {
	margin-bottom: 18px;
}
@media (max-width: 767px) {
	.cr-times-page .teachers-top-heading {
		align-items: flex-start;
		flex-direction: column;
	}
	.cr-times-page .teachers-top-heading > span {
		font-size: 18px;
	}
}
</style>
<div class="container-fluid p-0 cr-times-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading">
				<span>Times Event Entries :: <?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)</span>
				<?php echo $this->Html->link('<< Back', ['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content" id="listID">
				<?php echo $this->element("Eventsubmissions/timeseventsentries"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>