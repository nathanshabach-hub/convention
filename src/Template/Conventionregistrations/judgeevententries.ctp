<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.jee-page { padding-top: 14px; padding-bottom: 18px; }
.jee-header {
    align-items: center;
    background: linear-gradient(135deg, #f7fbff 0%, #edf4ff 100%);
    border: 1px solid #dce8f8;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(30,70,120,0.08);
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between;
    margin-bottom: 14px;
    padding: 14px 18px;
}
.jee-header-title {
    color: #18314f;
    font-size: 20px;
    font-weight: 700;
    margin: 0;
}
.jee-header-sub {
    color: #4a6380;
    font-size: 13px;
    margin-top: 3px;
}
.jee-card {
    background: #fff;
    border: 1px solid #dfe6ef;
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(16,42,67,0.08);
    overflow: hidden;
    padding: 0;
    width: 100%;
}
.jee-card .dataTables_wrapper { padding: 12px 14px 0; }
.jee-card .dataTables_filter input,
.jee-card .dataTables_length select {
    border: 1px solid #c9d8e8;
    border-radius: 8px;
    height: 34px;
    padding: 5px 10px;
}
.jee-card table.dataTable thead th {
    background: #eef4fb;
    color: #26415f;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.04em;
    padding: 10px 8px;
    text-transform: uppercase;
    white-space: nowrap;
}
.jee-card table.dataTable tbody td {
    border-color: #e6edf7;
    font-size: 13px;
    padding: 10px 8px;
    vertical-align: middle;
}
.jee-card table.dataTable tbody tr:nth-child(even) { background: #fbfdff; }
.jee-card table.dataTable tbody tr:hover { background: #f0f7ff; }
.jee-action-group { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }
.jee-action-group a.btn-outline-secondary {
    background: transparent !important;
    color: #6c757d !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: auto !important;
    border: 1px solid #6c757d !important;
}
.jee-action-group a.btn-primary {
    color: #fff !important;
}
.jee-action-group a.btn-primary i {
    color: #fff !important;
}
.jee-action-group a.jee-dna-btn {
    background: transparent !important;
    color: #6c757d !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    width: auto !important;
}
.jee-score-badge {
    background: #eaf4e8;
    border: 1px solid #b8dcb4;
    border-radius: 999px;
    color: #236620;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 9px;
    white-space: nowrap;
}
.jee-judged-chip {
    background: #e8f3ff;
    border: 1px solid #b8d8f8;
    border-radius: 999px;
    color: #1a5294;
    font-size: 11px;
    padding: 2px 8px;
    white-space: nowrap;
}
</style>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 jee-page">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<div class="jee-header">
				<div>
					<div class="jee-header-title">Event Entries :: <?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)</div>
					<div class="jee-header-sub">Select an entry below to submit your judge evaluation.</div>
				</div>
				<?php echo $this->Html->link('&laquo; Back', ['controller' => 'conventionregistrations', 'action' => 'judgeevents',$convBackBtn->slug], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>

			<div class="jee-card">
				<div class="m_content" id="listID">
					<?php echo $this->element("Conventionregistrations/judgeevententries"); ?>
				</div>
			</div>

		</main>
	</div>
</div>