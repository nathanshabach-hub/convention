<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Resultpositions = TableRegistry::get('Resultpositions');

$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
$this->Judgeevaluationmarks = TableRegistry::get('Judgeevaluationmarks');
$this->Evaluationquestions = TableRegistry::get('Evaluationquestions');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
$evaluationsOnly = ((int)$this->request->getQuery('evaluations_only', 0) === 1);
?>
<?php if ((int)$this->request->getQuery('autoprint', 1) === 1): ?>
<script type="text/javascript">
<!--
window.print();
//-->
</script>
<?php endif; ?>
<style>
@media print {
	html,
	body {
		margin: 0 !important;
		padding: 0 !important;
	}

	.page-break {
		page-break-after: always;
		break-after: page;
	}

	.pack-page {
		clear: both;
		page-break-after: always;
		break-after: page;
		break-inside: avoid-page;
		page-break-inside: avoid;
	}

	.pack-page:last-child {
		page-break-after: auto;
		break-after: auto;
	}

	.judge-evaluation-page {
		clear: both;
		page-break-before: always;
		break-before: page;
		page-break-after: always;
		break-after: page;
		page-break-inside: auto;
		break-inside: auto;
	}

	.judge-evaluation-page:last-child {
		page-break-after: auto;
		break-after: auto;
	}

	.table-responsive {
		overflow: visible !important;
	}

	table {
		page-break-inside: auto;
	}

	tr,
	td,
	th,
	img {
		page-break-inside: avoid;
		break-inside: avoid;
	}

	.spacer-after-break {
		display: none !important;
		height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
	}
}
.pinyon-script-regular {
	font-family: "Pinyon Script", cursive;
	font-weight: 400;
	font-style: normal;
	}
@page {
	size: A4 <?php echo $evaluationsOnly ? 'portrait' : 'landscape'; ?>;
	margin:0cm;
}
</style>

<?php if (!$evaluationsOnly): ?>
<!-- create first page with some details -->
<div class="pack-page">
	<?php echo $this->element('Judgeevaluations/firstpage'); ?>
</div>

<div class="pack-page">
	<?php echo $this->element('Judgeevaluations/indrespackprint'); ?>
</div>

<div class="pack-page">
	<?php echo $this->element('Judgeevaluations/participationcertificatepdf'); ?>
</div>

<div class="pack-page">
	<?php echo $this->element('Judgeevaluations/placecertificatepdf'); ?>
</div>
<?php endif; ?>

<?php if ($evaluationsOnly): ?>
	<?php echo $this->element('Judgeevaluations/evaluationformpdf'); ?>
<?php else: ?>
	<div class="pack-page">
		<?php echo $this->element('Judgeevaluations/evaluationformpdf'); ?>
	</div>
<?php endif; ?>







