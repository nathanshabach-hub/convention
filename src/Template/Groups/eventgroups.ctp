<script type="text/javascript">
	$(document).ready(function () {
		$("#addgroup").validate();
	});
</script>
<?php echo $this->Html->script('ajax-pagging.js'); ?>

<?php
use Cake\ORM\TableRegistry;

$this->Users = TableRegistry::get('Users');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');

$condTS = array();
$condTS[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."')";
$condTS[] = "(Crstudentevents.convention_id = '".$conventionRegD->convention_id."')";
$condTS[] = "(Crstudentevents.season_id = '".$conventionRegD->season_id."')";
$condTS[] = "(Crstudentevents.season_year = '".$conventionRegD->season_year."')";
$condTS[] = "(Crstudentevents.event_id = '".$eventD->id."')";

$totalStudentsEvent = $this->Crstudentevents->find()->where($condTS)->count();
$hasStudentOptions = !empty($studentDD);
?>

<style>
.cr-groups-page {
	background: radial-gradient(circle at 10% 0%, rgba(28, 36, 82, 0.08), transparent 28%), linear-gradient(180deg, #f5f8fc 0%, #eef3f8 100%);
	min-height: auto !important;
}
.cr-groups-main {
	padding-top: 14px;
	padding-bottom: 18px;
}
.cr-groups-hero {
	align-items: flex-start;
	background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 250, 255, 0.98) 100%);
	border: 1px solid #d8e3ef;
	border-radius: 18px;
	box-shadow: 0 12px 30px rgba(19, 53, 90, 0.08) !important;
	display: flex;
	flex-wrap: wrap;
	gap: 16px;
	justify-content: space-between;
	margin: 8px 0 16px;
	padding: 18px;
}
.cr-groups-hero-copy {
	max-width: 760px;
	min-width: 0;
}
.cr-groups-kicker {
	color: #6d7f98 !important;
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.08em;
	margin: 0 0 6px;
	text-transform: uppercase;
}
.cr-groups-title {
	color: #16355a !important;
	font-size: 34px !important;
	font-weight: 800 !important;
	line-height: 1.12 !important;
	margin: 0 !important;
}
.cr-groups-subtitle {
	color: #5c708e !important;
	font-size: 14px !important;
	line-height: 1.5 !important;
	margin: 10px 0 0 !important;
}
.cr-groups-stats {
	display: grid;
	gap: 10px;
	grid-template-columns: repeat(2, minmax(110px, 1fr));
	min-width: 240px;
}
.cr-groups-stat {
	background: #fff !important;
	border: 1px solid #d8e3ef !important;
	border-radius: 14px;
	box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
	padding: 12px 14px;
	text-align: center;
}
.cr-groups-stat-label {
	color: #6d7f98 !important;
	display: block;
	font-size: 11px;
	font-weight: 700;
	letter-spacing: 0.08em;
	margin-bottom: 4px;
	text-transform: uppercase;
}
.cr-groups-stat-value {
	color: #17355a !important;
	display: block;
	font-size: 22px !important;
	font-weight: 800 !important;
	line-height: 1;
}
.cr-groups-form-card {
	background: #fff;
	border: 1px solid #d8e3ef;
	border-radius: 18px;
	box-shadow: 0 10px 24px rgba(19, 53, 90, 0.07) !important;
	margin-bottom: 16px;
	overflow: hidden;
	padding: 18px;
}
.cr-groups-section-head {
	border-bottom: 1px solid #e4ebf5;
	margin-bottom: 16px;
	padding-bottom: 12px;
}
.cr-groups-section-note {
	color: #6d7f98 !important;
	font-size: 13px;
	margin: 4px 0 0;
}
.cr-groups-grid {
	margin-left: -8px;
	margin-right: -8px;
}
.cr-groups-col {
	margin-bottom: 16px;
	padding-left: 8px;
	padding-right: 8px;
}
.cr-group-card {
	background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
	border: 1px solid #d8e3ef !important;
	border-radius: 18px;
	box-shadow: 0 10px 24px rgba(19, 53, 90, 0.08) !important;
	height: 100%;
	overflow: hidden;
}
.cr-group-card-body {
	padding: 16px;
}
.cr-group-card .card-title {
	color: #16355a !important;
	font-size: 18px !important;
	margin-bottom: 12px;
}
.cr-group-card-meta {
	align-items: center;
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
	justify-content: space-between;
	margin-bottom: 12px;
}
.cr-group-card-count,
.cr-group-card-status {
	border-radius: 999px;
	font-size: 11px;
	font-weight: 700;
	letter-spacing: 0.03em;
	padding: 5px 9px;
	text-transform: uppercase;
}
.cr-group-card-count {
	background: #edf4ff;
	border: 1px solid #c8dcfa;
	color: #2a4f78;
}
.cr-group-card-status.is-good {
	background: #e7f6ed;
	border: 1px solid #b8dfc6;
	color: #1f6a38;
}
.cr-group-card-status.is-bad {
	background: #fff1ef;
	border: 1px solid #f0c4bd;
	color: #8d3224;
}
.cr-group-card .table-responsive {
	border: 1px solid #e2ebf4;
	border-radius: 12px;
	overflow: hidden;
}
.cr-group-card .table {
	margin-bottom: 0;
}
.cr-group-card .table thead th {
	background: #f6f9fe;
	border-bottom: 1px solid #dce5f3;
	color: #2a416a;
	font-size: 12px;
	font-weight: 700;
}
.cr-group-card .table tbody tr:nth-child(odd) {
	background: #fcfdff;
}
.cr-group-card .table tbody td {
	font-size: 13px;
	vertical-align: middle;
}
.cr-groups-form-card .form-group {
	display: block;
	margin-bottom: 18px;
}
.cr-groups-form-card .form-group > label {
	color: #2d4e6f;
	display: block;
	font-weight: 700;
	margin-bottom: 8px;
	width: auto;
}
.cr-groups-form-card .form-group .input-multiple,
.cr-groups-form-card .form-group .input {
	width: 100%;
}
.cr-groups-form-card .form-group .input-multiple div {
	width: 100%;
}
.cr-groups-student-roster {
	background: #f8fbff;
	border: 1px solid #c7d7e8;
	border-radius: 12px;
	display: grid;
	gap: 8px;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	max-height: 280px;
	overflow-y: auto;
	padding: 10px;
}
.cr-groups-student-option {
	align-items: center;
	background: #fff;
	border: 1px solid #e1eaf4;
	border-radius: 8px;
	color: #294866;
	cursor: pointer;
	display: flex;
	font-size: 13px;
	gap: 8px;
	margin: 0;
	min-height: 38px;
	padding: 7px 9px;
	width: 100% !important;
}
.cr-groups-student-option span {
	flex: 1 1 auto;
	line-height: 1.35;
	min-width: 0;
}
.cr-groups-student-option input[type="checkbox"] {
	appearance: auto;
	flex: 0 0 16px;
	height: 16px !important;
	margin: 0 !important;
	padding: 0 !important;
	width: 16px !important;
}
.cr-groups-student-option:hover {
	background: #f1f7ff;
	border-color: #a9c7e5;
}
.cr-groups-student-option input {
	accent-color: #1f5f8f;
	flex: 0 0 auto;
	height: 16px;
	margin: 0;
	width: 16px;
}
.cr-groups-form-card .select2-container,
.cr-groups-form-card .select2-container--default .select2-selection--multiple {
	width: 100% !important;
}
.cr-groups-form-card .select2-container {
	margin-bottom: 0 !important;
}
.cr-groups-form-card .select2-container--default .select2-selection--multiple {
	border: 1px solid #c7d7e8;
	border-radius: 12px;
	min-height: 46px;
	padding: 6px 10px;
}
.cr-groups-form-card input[name="Groups[group_name]"] {
	border: 1px solid #c7d7e8 !important;
	border-radius: 12px !important;
	height: 50.5556px;
	line-height: 24px;
	padding: 6px 12px !important;
}
.cr-next-group {
	align-items: center;
	background: #f4f8fd;
	border: 1px solid #c7d7e8;
	border-radius: 12px;
	color: #244e77;
	display: flex;
	font-size: 16px;
	font-weight: 700;
	height: 50.5556px;
	padding: 6px 12px;
}
.cr-groups-fill-in-card {
	background: #fffaf0;
	border: 1px solid #f0d79d;
	border-radius: 14px;
	color: #6f4c16;
	display: grid;
	gap: 18px;
	grid-template-columns: minmax(180px, 0.7fr) minmax(0, 2fr);
	margin-bottom: 16px;
	padding: 16px 18px;
}

.cr-groups-fill-in-summary,
.cr-groups-fill-in-list {
	display: flex;
	flex-direction: column;
	gap: 4px;
}
.cr-groups-fill-in-summary {
	justify-content: center;
}
.cr-groups-fill-in-card strong {
	font-size: 14px;
}
.cr-groups-fill-in-card span {
	font-size: 13px;
}
.cr-groups-fill-in-list {
	border-left: 1px solid #efdcae;
	gap: 8px;
	max-height: 220px;
	min-width: 0;
	overflow-y: auto;
	padding-left: 18px;
}
.cr-groups-fill-in-list strong {
	margin-bottom: 2px;
}
.cr-groups-fill-in-candidates {
	display: grid;
	gap: 6px 10px;
	grid-template-columns: repeat(2, minmax(0, 1fr));
}
.cr-groups-fill-in-candidate {
	background: rgba(255, 255, 255, 0.72);
	border: 1px solid #f0dfb8;
	border-radius: 8px;
	padding: 6px 8px;
}
@media (max-width: 767px) {
	.cr-groups-fill-in-card {
		grid-template-columns: 1fr;
	}
	.cr-groups-fill-in-list {
		border-left: 0;
		border-top: 1px solid #efdcae;
		padding-left: 0;
		padding-top: 14px;
	}
	.cr-groups-fill-in-candidates {
		grid-template-columns: 1fr;
	}
}
.cr-groups-form-card .select2-container--default.select2-container--focus .select2-selection--multiple {
	border-color: #1f5f8f;
	box-shadow: 0 0 0 3px rgba(31, 95, 143, 0.12);
}
.cr-groups-form-card .select2-container--open .select2-selection--multiple {
	border-bottom-left-radius: 0;
	border-bottom-right-radius: 0;
	border-bottom-color: #c7d7e8;
}
.cr-groups-form-card .select2-container--open .select2-dropdown {
	border: 1px solid #c7d7e8;
	border-top: 0;
	border-radius: 0 0 12px 12px;
	box-shadow: 0 10px 24px rgba(19, 53, 90, 0.07);
	margin-top: -1px;
}
.cr-groups-form-card .select2-container--default .select2-selection--multiple {
	background: #fff;
}
.cr-groups-form-card .select2-selection__rendered {
	display: flex;
	flex-wrap: wrap;
	gap: 6px;
	padding: 0 !important;
}
.cr-groups-form-card .select2-search--dropdown {
	padding: 6px 10px 4px;
}
.cr-groups-form-card .select2-search--dropdown .select2-search__field {
	border: 1px solid #c7d7e8;
	border-radius: 10px;
	padding: 8px 10px;
	width: 100% !important;
}
.cr-groups-form-card .select2-selection__choice {
	margin: 0;
}
.cr-groups-form-card .select2-results__options {
	padding-top: 0 !important;
	padding-bottom: 6px;
}
.cr-groups-form-card .select2-results__option {
	padding: 6px 12px;
}
.cr-groups-empty-state {
	align-items: center;
	background: #f7fbff;
	border: 1px dashed #c8d8e8;
	border-radius: 12px;
	color: #58708b;
	display: flex;
	gap: 10px;
	min-height: 46px;
	padding: 10px 12px;
}
.cr-groups-empty-state i {
	color: #7c96af;
	font-size: 16px;
}
.cr-groups-empty-state span {
	font-size: 14px;
	font-weight: 600;
}
.cr-groups-form-grid {
	display: grid;
	gap: 18px;
	grid-template-columns: minmax(0, 1fr) minmax(220px, 280px);
	margin-bottom: 6px;
}
.cr-groups-form-grid .form-group {
	margin-bottom: 0;
}
.cr-groups-form-grid .form-group label {
	margin-bottom: 8px;
}
.cr-groups-tools {
	align-items: center;
	background: #f7fbff;
	border: 1px solid #d8e7f5;
	border-radius: 12px;
	display: flex;
	gap: 12px;
	justify-content: space-between;
	margin-bottom: 16px;
	padding: 12px 14px;
}
.cr-groups-tools p {
	color: #58708b;
	font-size: 13px;
	margin: 0;
}
.cr-groups-tools strong {
	color: #244e77;
}
.cr-groups-tools form {
	margin: 0;
}
@media (max-width: 575px) {
	.cr-groups-tools {
		align-items: flex-start;
		flex-direction: column;
	}
}
@media (max-width: 991px) {
	.cr-groups-title {
		font-size: 28px !important;
	}
	.cr-groups-stats {
		grid-template-columns: repeat(2, minmax(0, 1fr));
		width: 100%;
	}
	.cr-groups-hero,
	.cr-groups-form-card {
		padding: 14px;
	}
	.cr-groups-form-grid {
		grid-template-columns: 1fr;
	}
	.cr-groups-student-roster {
		grid-template-columns: 1fr;
	}
}
@media (max-width: 575px) {
	.cr-groups-stats {
		grid-template-columns: 1fr;
	}
	.cr-groups-title {
		font-size: 24px !important;
	}
	.cr-groups-hero {
		padding: 12px;
}
}
</style>

<div class="container-fluid p-0 cr-groups-page">
	<div class="row align-items-start">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-groups-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="cr-groups-hero">
				<div class="cr-groups-hero-copy">
					<p class="cr-groups-kicker">Group event workspace</p>
					<h1 class="cr-groups-title">Event Groups :: <?php echo h($eventD->event_name); ?></h1>
					<p class="cr-groups-subtitle">Create balanced groups, review current allocations, and keep the event within the required range.</p>
				</div>
				<div class="cr-groups-stats">
					<div class="cr-groups-stat">
						<span class="cr-groups-stat-label">Min</span>
						<span class="cr-groups-stat-value"><?php echo (int)$eventD->min_no; ?></span>
					</div>
					<div class="cr-groups-stat">
						<span class="cr-groups-stat-label">Max</span>
						<span class="cr-groups-stat-value"><?php echo (int)$eventD->max_no; ?></span>
					</div>
					<div class="cr-groups-stat">
						<span class="cr-groups-stat-label">Students</span>
						<span class="cr-groups-stat-value"><?php echo (int)$totalStudentsEvent; ?></span>
					</div>
					<div class="cr-groups-stat">
						<span class="cr-groups-stat-label">Groups</span>
						<span class="cr-groups-stat-value"><?php echo !empty($stGArr) && is_array($stGArr) ? count($stGArr) : 0; ?></span>
					</div>
				</div>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="dashboard-form cr-groups-form-card">
				<div class="cr-groups-section-head">
					<h2 class="form-title">Create Group</h2>
					<p class="cr-groups-section-note">Event ID Number: <?php echo h($eventD->event_id_number); ?></p>
				</div>
				<?php if ((int)$eventD->group_event_yes_no === 1 && (int)$eventD->min_no <= 1 && (int)$eventD->max_no <= 2 && $hasStudentOptions) { ?>
					<div class="cr-groups-tools">
						<p><strong>Variable group event:</strong> create one-person groups for all currently ungrouped students.</p>
						<?php echo $this->Form->create(null, ['url' => ['controller' => 'groups', 'action' => 'eventgroups', $event_slug], 'class' => 'cr-groups-tools-form']); ?>
						<?php echo $this->Form->hidden('Groups.action', ['value' => 'auto_solo_groups']); ?>
						<button type="submit" class="btn btn-secondary" onclick="return confirm('Create one-person groups for all ungrouped students?');">Auto-create solo groups</button>
						<?php echo $this->Form->end(); ?>
					</div>
				<?php } ?>
				<?php echo $this->Form->create(NULL, ['id' => 'addgroup', 'type' => 'file', 'class' => '', 'autocomplete' => 'off']); ?>
				<div class="cr-groups-form-grid">
					<div class="form-group">
						<label>Choose students for this group</label>
						<div class="input-multiple">
							<?php if ($hasStudentOptions) { ?>
								<div class="cr-groups-student-roster">
									<?php foreach ($studentDD as $studentId => $studentLabel) { ?>
										<label class="cr-groups-student-option">
											<input type="checkbox" name="Groups[student_id][]" value="<?php echo (int)$studentId; ?>">
											<span><?php echo h($studentLabel); ?></span>
										</label>
									<?php } ?>
								</div>
							<?php } else { ?>
								<div class="cr-groups-empty-state">
									<i class="fa fa-info-circle"></i>
									<span>All students for this event are already grouped.</span>
								</div>
							<?php } ?>
						</div>
					</div>

					<div class="form-group">
						<label for="group_name">Group number</label>
						<?php echo $this->Form->control('Groups.group_name', [
							'id' => 'group_name',
							'label' => false,
							'type' => 'number',
							'min' => 1,
							'required' => true,
							'value' => $nextGroupName,
							'class' => 'form-control required',
						]); ?>
					</div>
				</div>

				<div class="form-group form-btns" style="padding-top:10px;">
					<label></label>
					<button type="submit" class="btn btn-secondary" <?php echo $hasStudentOptions ? '' : 'disabled'; ?>>Create Group</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('<< Back', ['controller' => 'groups', 'action' => 'viewlist'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>

						<?php if (!empty($showFillInPanel)) { ?>
				<div class="cr-groups-fill-in-card">
					<div class="cr-groups-fill-in-summary">
						<strong>Incomplete event</strong>
						<span><?php echo (int)$fillInNeededCount; ?> more student(s) needed.</span>
					</div>
					<?php if (!empty($eligibleFillInStudents)) { ?>
						<div class="cr-groups-fill-in-list">
							<strong>Eligible students from this school</strong>
							<div class="cr-groups-fill-in-candidates">
								<?php foreach ($eligibleFillInStudents as $candidate) { ?>
									<span class="cr-groups-fill-in-candidate"><?php echo h($candidate['name']); ?> (<?php echo (int)$candidate['age']; ?> years, <?php echo (int)$candidate['events']; ?> events)</span>
								<?php } ?>
							</div>
						</div>
					<?php } else { ?>
						<span>No eligible students found within this school.</span>
					<?php } ?>
				</div>
			<?php } ?>



			<div class="container-fluid p-0 cr-groups-list-shell">
				<div class="row" style="display:none;">
					<div class="col-sm-4">col-sm-4</div>
					<div class="col-sm-4">col-sm-4</div>
					<div class="col-sm-4">col-sm-4</div>
					<div class="col-sm-4">col-sm-4</div>
				</div>

				<?php
				if (count($stGArr) > 0) {
					?>
					<div class="row cr-groups-grid">
						<?php
						foreach ($stGArr as $stgname => $studentids) {
							?>
							<div class="col-lg-4 col-md-6 cr-groups-col">
								<div class="card cr-group-card">
									<div class="card-body cr-group-card-body">
										<h4 class="card-title">
											<b>Group
												<?php echo $stgname; ?>
											</b>
											<?php
											//echo count($studentids);
											if (count($studentids) >= $eventD->min_no && count($studentids) <= $eventD->max_no) {
												echo '<i class="fa fa-check-circle pull-right" style="color:green;"></i>';
											} else {
												echo '<i class="fa fa-times-circle pull-right" style="color:red;" title="Group does not fulfil min/max criteria."></i>';
											}
											?>
										</h4>

										<div class="cr-group-card-meta">
											<span class="cr-group-card-count"><?php echo count($studentids); ?> students</span>
											<?php if (count($studentids) >= $eventD->min_no && count($studentids) <= $eventD->max_no) { ?>
												<span class="cr-group-card-status is-good">Within range</span>
											<?php } else { ?>
												<span class="cr-group-card-status is-bad">Outside range</span>
											<?php } ?>
										</div>

										<div class="table-responsive table-bordered tablescroll">
											<table class="table table-striped-columns">
												<tbody>
													<?php
													if (count($studentids))
														$implodeStIDS = implode(",", $studentids);
													else
														$implodeStIDS = 0;

													$condStudentE = array();
													$condStudentE[] = "(Users.id IN ($implodeStIDS) )";

													$studentsDList = $this->Users->find()->where($condStudentE)->order(["Users.first_name" => "ASC", "Users.middle_name" => "ASC"])->all();

													foreach ($studentsDList as $studentrecord) {
														?>
														<tr>
															<td class="">
																<?php echo $studentrecord->first_name; ?>
																<?php echo $studentrecord->middle_name; ?>
																<?php echo $studentrecord->last_name; ?> 
																(<?php echo date("Y") - $studentrecord->birth_year; ?> Yrs)
															</td>
															<td width="30%" style="text-align: center">
																<?php
																echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'groups', 'action' => 'removestudentfromgroup', $event_slug, $studentrecord->id], ['escape' => false, 'title' => 'Remove student from group ' . $stgname, 'class' => '', 'confirm' => 'Are you sure you want to remove this student from group ' . $stgname . '?']);
																?>
															</td>
														</tr>
														<?php
													}
													?>

												</tbody>
												<!-- end tbody -->
											</table>
											<!-- end table -->
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
						<!-- end col -->
					</div>
					<?php
				}
				?>





			</div>

		</main>
	</div>
</div>