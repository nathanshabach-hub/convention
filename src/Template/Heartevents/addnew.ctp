<script type="text/javascript">
	$(document).ready(function () {
		$("#addeventsheart").validate();
	});
</script>
<style>
	.eoh-page-shell {
		padding-top: 18px;
		padding-bottom: 20px;
	}

	.eoh-content-wrap {
		width: 100%;
		max-width: none;
	}

	.eoh-page-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 14px;
		margin-bottom: 16px;
		flex-wrap: wrap;
	}

	.eoh-title {
		margin: 0;
		font-size: 28px;
		line-height: 1.2;
		color: #17324a;
	}

	.eoh-subtitle {
		margin: 4px 0 0;
		color: #4d667b;
		font-size: 14px;
	}

	.eoh-back-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		margin-top: 8px;
		padding: 10px 16px;
		border-radius: 10px;
		border: 1px solid #1f3f5b !important;
		background: #1f3f5b !important;
		color: #ffffff !important;
		font-weight: 600;
		font-size: 14px;
		text-decoration: none;
		box-shadow: 0 4px 10px rgba(21, 50, 76, 0.22);
	}

	.eoh-back-btn:hover,
	.eoh-back-btn:focus {
		background: #163149 !important;
		border-color: #163149 !important;
		color: #ffffff !important;
	}

	.eoh-card .form-group,
	.eoh-card .form-group .input {
		width: 100%;
	}

	.eoh-card .form-group label {
		float: none !important;
		display: block !important;
		width: auto !important;
		max-width: none !important;
		line-height: 1.35;
	}

	.eoh-card .form-group input,
	.eoh-card .form-group select,
	.eoh-card .form-group textarea,
	.eoh-card .form-group .select2,
	.eoh-card .form-group .select2-container,
	.eoh-card .form-group .select2-selection,
	.eoh-card .form-group .custom-file,
	.eoh-card .form-group .custom-file-input,
	.eoh-card .form-group .custom-file-label {
		float: none !important;
		width: 100% !important;
		max-width: 100% !important;
	}

	.eoh-card {
		background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
		border: 1px solid #d5e4f1;
		border-radius: 14px;
		padding: 24px;
		box-shadow: 0 8px 22px rgba(22, 53, 90, 0.08);
	}

	.eoh-form-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 16px;
	}

	.eoh-form-grid .form-group {
		margin-bottom: 0;
	}

	.eoh-form-grid .eoh-span-2 {
		grid-column: 1 / -1;
	}

	.eoh-card .form-group label {
		font-weight: 600;
		margin-bottom: 7px;
		color: #17324a;
	}

	.eoh-card .help_text {
		display: inline-block;
		margin-top: 8px;
		font-size: 13px;
		color: #4d667b;
	}

	.eoh-actions {
		display: flex;
		justify-content: flex-end;
		gap: 10px;
		margin-top: 10px;
	}

	.eoh-actions .btn {
		min-width: 120px;
	}

	@media (max-width: 991px) {
		.eoh-content-wrap {
			width: 100%;
			max-width: 100%;
		}

		.eoh-card {
			padding: 18px;
		}

		.eoh-back-btn {
			width: 100%;
		}

		.eoh-page-header {
			gap: 10px;
		}

		.eoh-form-grid {
			grid-template-columns: 1fr;
		}

		.eoh-actions {
			justify-content: stretch;
		}

		.eoh-actions .btn {
			flex: 1 1 auto;
			min-width: 0;
		}
	}
</style>
<div class="container-fluid p-0 eoh-page-shell">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="eoh-content-wrap">

			<div class="eoh-page-header">
				<div>
					<h2 class="eoh-title">Add Events of the Heart</h2>
					<p class="eoh-subtitle">Upload and assign a document to a student for Events of the Heart.</p>
				</div>
				<?php echo $this->Html->link('Back to List', ['controller' => 'heartevents', 'action' => 'viewlist'], ['class' => 'eoh-back-btn']); ?>
			</div>

			<div class="dashboard-form eoh-card">
				<?php echo $this->Form->create($heartevents, ['id' => 'addeventsheart', 'type' => 'file']); ?>
				<div class="eoh-form-grid">

				<div class="form-group">
					<label for="student_id">Choose Student</label>
					<div class="input">
						<?php echo $this->Form->select('Heartevents.student_id', $studentSchoolDD, ['id' => 'student_id', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required ', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						<script>
							$(document).ready(function () {
								$('#student_id').select2();
							});
						</script>
					</div>
				</div>

				<div class="form-group">
					<label for="mediafile_title">Document Title</label>
					<?php echo $this->Form->input('Heartevents.mediafile_title', ['id' => 'mediafile_title', 'label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required']); ?>
				</div>

				<div class="form-group eoh-span-2">
					<label for="event_document">Upload File</label>
					<div class="input">
					<?php echo $this->Form->input('Heartevents.event_document', ['id' => 'event_document', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidation()']); ?>
					<span class="help_text">pdf, doc, docx, png, jpg and jpeg files allowed.</span>
					</div>
				</div>

				<div class="form-group eoh-actions eoh-span-2">
					<button type="submit" class="btn btn-secondary">Save</button>
					<?php echo $this->Html->link('Cancel', ['controller' => 'heartevents', 'action' => 'viewlist'], ['class' => 'btn btn-secondary']); ?>
				</div>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			</div>

		</main>
	</div>
</div>

<script>
	function in_array(needle, haystack) {
		for (var i = 0, j = haystack.length; i < j; i++) {
			if (needle == haystack[i])
				return true;
		}
		return false;
	}

	function getExt(filename) {
		var dot_pos = filename.lastIndexOf(".");
		if (dot_pos == -1)
			return;
		return filename.substr(dot_pos + 1).toLowerCase();
	}

	function imageValidation() {
		var filename = document.getElementById("event_document").value;
		var filetype = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];
		
		if (filename != '') {
			var ext = getExt(filename);
			ext = ext.toLowerCase();
			var checktype = in_array(ext, filetype);
			if (!checktype) {
				alert(ext + " file not allowed.");
				document.getElementById("event_document").value = "";
				return false;
			} else {
				/*
				var fi = document.getElementById('mediafile_file');
				var filesize = fi.files[0].size;//check uploaded file size
				if (filesize > 2097152) {
					alert('Maximum 2MB file size allowed for product image .');
					return false;
				}
				*/
			}
		}
		return true;
	}
</script>