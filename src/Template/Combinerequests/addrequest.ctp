<script type="text/javascript">
	$(document).ready(function () {
		$("#addrequest").validate();
		$('#event_id').on('change', function () {
			var eventId = $(this).val() || '';
			var targetUrl = '<?php echo $this->Url->build(['controller' => 'combinerequests', 'action' => 'addrequest']); ?>';
			if (eventId !== '') {
				targetUrl += '?event_id=' + encodeURIComponent(eventId);
			}
			window.location.href = targetUrl;
		});
	});
</script>
<style>
.cr-addrequest-wrap {
	background: linear-gradient(180deg, #f7fafc 0%, #eff4f8 100%);
	padding: 16px 0 28px;
}
.cr-addrequest-card {
	width: 100%;
	max-width: none;
	padding: 24px 24px 18px;
	margin: 20px 0 30px;
	border: 1px solid #c9d9e6;
	border-radius: 14px;
	background: #ffffff;
	box-shadow: 0 10px 24px rgba(14, 53, 94, 0.08);
}
.cr-addrequest-title {
	font-size: 24px;
	line-height: 1.2;
	margin: 0 0 6px;
	color: #16324a;
	font-weight: 700;
}
.cr-addrequest-subtitle {
	margin: 0 0 18px;
	color: #4a6174;
	font-size: 14px;
}
.cr-addrequest-form .form-group {
	margin-bottom: 18px;
	display: grid;
	grid-template-columns: 220px minmax(0, 1fr);
	column-gap: 12px;
	align-items: start;
}
.cr-addrequest-form .form-group > label {
	display: block;
	width: 100%;
	font-size: 14px;
	font-weight: 600;
	color: #1f3f5c;
	margin: 9px 0 0;
}
.cr-addrequest-form .form-group .input {
	width: 100%;
}
.cr-addrequest-form .form-control {
	border-radius: 10px;
	border-color: #bacddd;
	min-height: 42px;
}
.cr-addrequest-form .select2-container--default .select2-selection--single {
	height: 42px;
	border-radius: 10px;
	border-color: #bacddd;
	padding-top: 6px;
}
.cr-addrequest-form .select2-container--default .select2-selection--single .select2-selection__arrow {
	height: 40px;
}
.cr-student-list {
	border: 1px solid #d5e0ea;
	border-radius: 10px;
	padding: 10px 12px;
	max-height: 230px;
	overflow-y: auto;
	background: #fbfdff;
}
.cr-student-item {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 7px 4px;
	margin: 0;
	font-weight: 500;
	color: #24445f;
	border-bottom: 1px solid #eef3f7;
}
.cr-student-item:last-child {
	border-bottom: 0;
}
.cr-student-item input[type="checkbox"] {
	width: 16px;
	height: 16px;
	margin: 0;
}
.cr-student-empty {
	margin: 0;
	color: #5e7486;
	font-size: 14px;
}
.cr-addrequest-form .form-btns {
	display: flex;
	align-items: center;
	gap: 10px;
	padding-top: 4px;
	margin-left: 232px;
}
.cr-addrequest-form .form-btns > label {
	display: none;
}
.cr-addrequest-form .form-btns button {
	margin-right: 0;
}
.cr-addrequest-form .btn {
	min-width: 112px;
	border-radius: 10px;
	font-weight: 600;
	padding: 9px 14px;
}
.cr-addrequest-form .btn.btn-secondary {
	background: #2f4659;
	border-color: #2f4659;
}
.cr-addrequest-form .btn.btn-secondary:hover {
	background: #223949;
	border-color: #223949;
}

@media (max-width: 767px) {
	.cr-addrequest-card {
		padding: 16px;
		border-radius: 10px;
	}
	.cr-addrequest-title {
		font-size: 20px;
	}
	.cr-addrequest-form .form-group {
		display: block;
	}
	.cr-addrequest-form .form-group > label {
		margin-top: 0;
		margin-bottom: 8px;
	}
	.cr-addrequest-form .form-btns {
		margin-left: 0;
	}
	.cr-student-list {
		max-height: none;
	}
}
</style>
<div class="container-fluid p-0 cr-addrequest-wrap">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form cr-addrequest-card">
				<h2 class="cr-addrequest-title">Combined Team or Group Event Request</h2>
				<p class="cr-addrequest-subtitle">Choose an event, pick the partner school, then tick registered students.</p>
				<?php echo $this->Form->create($combinerequests, ['id' => 'addrequest', 'type' => 'file', 'class' => 'cr-addrequest-form']); ?>

				<div class="form-group">
					<label for="name">Choose Event</label>
					<div class="input">
						<?php echo $this->Form->select('Combinerequests.event_id', $eventNameIDDD, ['id' => 'event_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose', 'value' => (int)$selectedEventId]); ?>
						<script>
							$(document).ready(function () {
								$('#event_id').select2();
							});
						</script>
					</div>
				</div>

				<div class="form-group">
					<label for="name">Choose School To Combine With</label>
					<div class="input">
						<?php echo $this->Form->select('Combinerequests.combine_with_user_id', $schoolNamesDD, ['id' => 'combine_with_user_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose', 'value' => (int)$selectedCombineWithUserId]); ?>
						<script>
							$(document).ready(function () {
								$('#combine_with_user_id').select2();
							});
						</script>
					</div>
				</div>
				
				<div class="form-group">
					<label for="name">Student Name/s</label>
					<div class="input">
						<?php if ((int)$selectedEventId <= 0) { ?>
							<p class="cr-student-empty">Please choose an event to load registered students.</p>
						<?php } elseif (empty($eventRegisteredStudents)) { ?>
							<p class="cr-student-empty">No students are currently registered for this event.</p>
						<?php } else { ?>
							<div class="cr-student-list">
							<?php foreach ($eventRegisteredStudents as $studentId => $studentName) { ?>
								<label class="cr-student-item">
									<input type="checkbox" name="selected_student_ids[]" value="<?php echo (int)$studentId; ?>" <?php echo in_array((int)$studentId, (array)$selectedStudentIds, true) ? 'checked' : ''; ?> />
									<?php echo h($studentName); ?>
								</label>
							<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary">Save</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('Cancel', ['controller' => 'combinerequests', 'action' => 'viewlist'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>