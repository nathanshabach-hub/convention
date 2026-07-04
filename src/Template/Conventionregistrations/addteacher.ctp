<script type="text/javascript">
	$(document).ready(function () {
		$("#addteacher").validate();
	});
</script>
<div class="container-fluid p-0 cr-addteacher-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-addteacher-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3 cr-addteacher-title">Add Supervisor Info</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form cr-addteacher-card">
				<h2 class="form-title">Add Supervisors Info</h2>
				<?php echo $this->Form->create($conventionregistrationteachers, ['id' => 'addteacher', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="name">Choose Supervisor(s)</label>
					<div class="input-multiple">
					<div class="cr-addteacher-quick-actions">
						<button type="button" id="select_all_supervisors" class="btn btn-default btn-sm">Select all</button>
						<button type="button" id="clear_all_supervisors" class="btn btn-default btn-sm">Clear all</button>
					</div>
					<?php echo $this->Form->select('Conventionregistrationteachers.teacher_ids', $teacherSchoolDD, ['id' => 'teacher_ids', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required', 'autocomplete' => 'off', 'multiple' => 'multiple']); ?>
					<em>You can select multiple supervisors at once.</em>
					<script>
						$(document).ready(function () {
							$('#teacher_ids').select2({
								placeholder: 'Choose supervisor(s)',
								width: '100%'
							});

							$('#select_all_supervisors').on('click', function () {
								var allValues = $('#teacher_ids option').map(function () {
									return $(this).val();
								}).get();
								$('#teacher_ids').val(allValues).trigger('change');
							});

							$('#clear_all_supervisors').on('click', function () {
								$('#teacher_ids').val(null).trigger('change');
							});
						});
					</script>
				</div>
			</div>

			<div class="form-group form-btns">
				<label></label>
				<button type="submit" class="btn btn-secondary">Save</button>
				<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
				<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'teachers'], ['class' => 'btn btn-secondary']); ?>
			</div>
			<?php echo $this->Form->end(); ?>
	</div>
	<!-- dashboard-section-3 end-->

	</main>
</div>
</div>