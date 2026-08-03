<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.cr-grouping-page {
	background:
		radial-gradient(circle at 10% 0%, rgba(28, 36, 82, 0.08), transparent 30%),
		linear-gradient(180deg, #f5f8fc 0%, #eef3f8 100%);
	min-height: 100vh;
}

.cr-grouping-main {
	padding-top: 14px;
	padding-bottom: 16px;
}

.cr-grouping-hero {
	align-items: flex-start;
	background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 250, 255, 0.98) 100%);
	border: 1px solid #d8e3ef;
	border-radius: 18px;
	box-shadow: 0 12px 30px rgba(19, 53, 90, 0.08);
	display: flex;
	flex-wrap: wrap;
	gap: 14px;
	justify-content: space-between;
	margin: 8px 0 16px;
	padding: 18px;
}

.cr-grouping-kicker {
	color: #6d7f98;
	display: block;
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.08em;
	margin: 0 0 6px;
	text-transform: uppercase;
}

.cr-grouping-title {
	color: #16355a;
	font-size: 32px;
	font-weight: 800;
	line-height: 1.15;
	margin: 0;
}

.cr-grouping-subtitle {
	color: #5c708e;
	display: block;
	font-size: 14px;
	line-height: 1.5;
	margin: 8px 0 0;
	max-width: 760px;
}

.cr-grouping-cta {
	border-radius: 10px;
	font-weight: 700;
	padding: 8px 14px;
	white-space: nowrap;
}

.cr-grouping-list-shell {
	background: #fff;
	border: 1px solid #d8e3ef;
	border-radius: 18px;
	box-shadow: 0 12px 30px rgba(19, 53, 90, 0.08);
	overflow: hidden;
	padding: 14px;
}

.cr-grouping-panel {
	padding: 0;
}

.cr-grouping-table-wrap {
	border: 1px solid #dfe9f4;
	border-radius: 14px;
	overflow: hidden;
}

.cr-grouping-table {
	margin: 0 !important;
}

.cr-grouping-table thead th {
	background: linear-gradient(180deg, #f4f8fd 0%, #edf4fc 100%);
	border-bottom: 1px solid #d6e2f0 !important;
	color: #294669;
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.02em;
	text-transform: uppercase;
	vertical-align: middle;
}

.cr-grouping-table tbody td {
	border-color: #e5edf6 !important;
	color: #244869;
	font-size: 13px;
	vertical-align: middle;
}

.cr-grouping-table tbody tr:nth-child(odd) {
	background: #fcfdff;
}

.cr-grouping-table tbody tr:hover {
	background: #f5f9ff;
}

.cr-badge {
	border-radius: 999px;
	display: inline-block;
	font-size: 11px;
	font-weight: 700;
	letter-spacing: 0.03em;
	line-height: 1;
	padding: 6px 9px;
	text-transform: uppercase;
}

.cr-badge.is-yes {
	background: #e7f6ed;
	border: 1px solid #b8dfc6;
	color: #1f6a38;
}

.cr-badge.is-no {
	background: #f1f4f8;
	border: 1px solid #d4deea;
	color: #5a6d84;
}

.cr-badge.is-good {
	background: #e7f6ed;
	border: 1px solid #b8dfc6;
	color: #1f6a38;
}

.cr-badge.is-warning {
	background: #fff1ef;
	border: 1px solid #f0c4bd;
	color: #8d3224;
}

.cr-badge.is-muted {
	background: #f2f6fb;
	border: 1px solid #d7e1ec;
	color: #647b94;
}

.cr-action-link {
	background: #eef5ff;
	border: 1px solid #c7dbf3;
	border-radius: 8px;
	color: #1f5f8f !important;
	font-size: 12px;
	font-weight: 700;
	padding: 5px 9px;
	text-decoration: none;
}

.cr-action-link:hover {
	background: #1f5f8f;
	border-color: #1f5f8f;
	color: #fff !important;
}

.cr-action-icon {
	align-items: center;
	border-radius: 999px;
	display: inline-flex;
	font-size: 14px;
	height: 22px;
	justify-content: center;
	line-height: 1;
	margin-left: 6px;
	padding: 4px;
	vertical-align: middle;
	width: 22px;
}

.cr-grouping-table td .cr-action-icon i {
	color: inherit !important;
	display: inline-block !important;
	line-height: 1;
	margin-right: 0 !important;
	text-align: center;
	width: 1em;
}

.cr-action-icon.is-alert {
	background: #fff1ef;
	color: #b5402f;
}

.cr-action-icon.is-disabled {
	background: #f4f7fb;
	color: #6f8197;
}

.cr-grouping-list-shell .dataTables_filter,
.cr-grouping-list-shell .dataTables_length {
	margin-bottom: 10px;
}

.cr-grouping-list-shell .dataTables_filter label,
.cr-grouping-list-shell .dataTables_length label {
	color: #4e6785;
	font-size: 13px;
	font-weight: 600;
}

.cr-grouping-list-shell .dataTables_filter input,
.cr-grouping-list-shell .dataTables_length select {
	border: 1px solid #c7d7e8;
	border-radius: 8px;
	box-shadow: none;
	padding: 4px 8px;
}

.cr-grouping-list-shell .dataTables_paginate .paginate_button {
	border-radius: 8px !important;
	margin: 0 1px;
}

@media (max-width: 991px) {
	.cr-grouping-title {
		font-size: 28px;
	}

	.cr-grouping-hero {
		padding: 14px;
	}
}

@media (max-width: 575px) {
	.cr-grouping-title {
		font-size: 24px;
	}

	.cr-grouping-hero,
	.cr-grouping-list-shell {
		padding: 12px;
	}

	.cr-grouping-table-wrap {
		overflow-x: auto;
	}
}
</style>
<div class="container-fluid p-0 cr-grouping-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-grouping-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="cr-grouping-hero">
				<div>
					<span class="cr-grouping-kicker">Group Management</span>
					<h1 class="cr-grouping-title">Student Grouping</h1>
					<span class="cr-grouping-subtitle">Review each group event, check ungrouped students, and jump directly into team setup.</span>
				</div>
				<?php echo $this->Html->link('<i class="fa fa-plus"></i> Combined Team/Group Events', ['controller' => 'combinerequests', 'action' => 'addrequest'], ['escape' => false, 'class' => 'btn btn-primary cr-grouping-cta']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content cr-grouping-list-shell" id="listID">
				<?php echo $this->element("Groups/viewlist"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>