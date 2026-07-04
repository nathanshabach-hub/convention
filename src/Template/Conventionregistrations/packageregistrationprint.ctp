<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
@media print {
	@page {
		margin: 12mm;
	}

	body {
		background: #fff !important;
	}

	.cr-packreg-print {
		zoom: 0.86;
	}

	.cr-packreg-print h4 {
		font-size: 18px !important;
		margin: 0 0 8px !important;
	}

	.cr-packreg-print .cr-pr-card-header {
		padding: 8px 10px;
	}

	.cr-packreg-print .cr-pr-student-name {
		font-size: 15px !important;
	}

	.cr-packreg-print .cr-pr-student-details {
		font-size: 10px;
		gap: 8px;
	}

	.cr-packreg-print .cr-pr-count {
		font-size: 10px;
		padding: 5px 8px;
	}

	.cr-packreg-print .cr-pr-events {
		gap: 4px;
		padding: 6px 8px 8px;
	}

	.cr-packreg-print .cr-pr-event-row {
		gap: 6px;
		grid-template-columns: 56px minmax(0, 1fr) auto;
		padding: 5px 8px;
	}

	.cr-packreg-print .cr-pr-event-id,
	.cr-packreg-print .cr-pr-event-name {
		font-size: 12px;
		line-height: 1.2;
	}

	.cr-packreg-print .cr-pr-status {
		font-size: 10px;
		padding: 4px 8px;
	}

	.cr-packreg-print .cr-pr-pill {
		font-size: 9px;
		padding: 3px 6px;
	}

	.cr-packreg-print .cr-pr-list .cr-pr-card {
		break-inside: avoid;
		page-break-inside: avoid;
		break-after: page;
		page-break-after: always;
		margin-bottom: 0 !important;
	}

	.cr-packreg-print .cr-pr-list .cr-pr-card:last-child {
		break-after: auto;
		page-break-after: auto;
	}

	.cr-packreg-print .cr-pr-event-row {
		break-inside: avoid;
		page-break-inside: avoid;
	}
}
</style>
<div class="container-fluid p-0">
	<div class="row">
		
		<main class="cr-packreg-print">
			
			<h4 class="mt-4">Registration Check List</h4>
			

			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/packageregistration"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>

<script type="text/javascript">
<!--
window.print();
//-->
</script>