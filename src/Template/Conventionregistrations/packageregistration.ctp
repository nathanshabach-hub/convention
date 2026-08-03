<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.cr-packreg-main .cr-packreg-hero {
	background:
		radial-gradient(circle at 88% 6%, rgba(59, 122, 176, 0.12), transparent 30%),
		linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
	border: 1px solid #cedef0;
	border-radius: 18px;
	box-shadow: 0 12px 28px rgba(20, 51, 86, 0.1);
	padding: 18px;
}

.cr-packreg-main .cr-pr-overall {
	background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
	border: 1px solid #c6daee;
	border-radius: 14px;
	box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
	padding: 14px;
	position: relative;
}

.cr-packreg-main .cr-pr-overall::before {
	background: linear-gradient(90deg, #1f5f8f 0%, #2f7fbc 100%);
	border-radius: 999px;
	content: "";
	height: 4px;
	left: 14px;
	position: absolute;
	right: 14px;
	top: 0;
}

.cr-packreg-main .cr-pr-overall-meta {
	align-items: center;
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
	justify-content: space-between;
	margin-bottom: 10px;
}

.cr-packreg-main .cr-pr-overall-meta > div {
	display: flex;
	flex-direction: column;
	gap: 3px;
	min-width: 0;
}

.cr-packreg-main .cr-pr-overall-title {
	color: #173b61;
	font-size: 13px;
	font-weight: 700;
	letter-spacing: 0.04em;
	text-transform: uppercase;
}

.cr-packreg-main .cr-packreg-subtitle {
	color: #5d738f;
	font-size: 14px;
	line-height: 1.45;
	margin: 0;
}

.cr-packreg-main .cr-pr-overall-count {
	background: linear-gradient(180deg, #f3f9ff 0%, #e8f2ff 100%);
	border: 1px solid #bfd4e9;
	border-radius: 999px;
	color: #194d78;
	font-size: 12px;
	font-weight: 700;
	padding: 7px 12px;
	white-space: nowrap;
}

.cr-packreg-main .cr-pr-progress.cr-pr-progress-overall {
	background: linear-gradient(180deg, #e6eff9 0%, #dce8f6 100%);
	border: 1px solid #c3d4e8;
	border-radius: 999px;
	height: 28px;
	overflow: hidden;
}

.cr-packreg-main .cr-pr-progress-bar {
	align-items: center;
	border-radius: inherit;
	display: flex;
	font-size: 13px;
	font-weight: 700;
	height: 100%;
	justify-content: center;
	line-height: 1;
	text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
	transition: width 0.25s ease;
}

@media (max-width: 767px) {
	.cr-packreg-main .cr-pr-overall-meta {
		align-items: flex-start;
		flex-direction: column;
	}

	.cr-packreg-main .cr-pr-overall-count {
		align-self: flex-start;
	}
}
</style>
<div class="container-fluid p-0 cr-packreg-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-packreg-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="cr-packreg-hero">
				<div class="cr-packreg-header">
					<div>
						<span class="cr-packreg-title">Registration Checklist</span>
					</div>
					<?php echo $this->Html->link('<i class="fa fa-print"></i> Print Checklist', ['controller' => 'conventionregistrations', 'action' => 'packageregistrationprint'], ['escape' => false, 'class' => 'btn btn-primary cr-packreg-print-btn', 'target' => '_blank']); ?>
				</div>
				<div class="cr-pr-overall" id="cr-pr-overall">
					<div class="cr-pr-overall-meta">
						<div>
							<span class="cr-pr-overall-title">Overall Progress</span>
							<span class="cr-packreg-subtitle">All registrations and event uploads across the checklist.</span>
						</div>
						<span class="cr-pr-overall-count">
							<span id="cr-pr-overall-submitted">0</span>/<span id="cr-pr-overall-total">0</span> Submitted
						</span>
					</div>
					<div class="cr-pr-progress cr-pr-progress-overall" aria-hidden="true">
						<span class="cr-pr-progress-bar" id="cr-pr-overall-bar" style="width:0%;"></span>
					</div>
				</div>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content cr-packreg-card" id="listID">
				<?php echo $this->element("Conventionregistrations/packageregistration", ['enableAccordion' => true]); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>