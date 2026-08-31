<style>
.pf-print-page {
	background: #fff;
	color: #222;
	font-size: 14px;
	line-height: 1.35;
	margin: 0 auto;
	max-width: 980px;
	padding: 12px;
}

.pf-toolbar {
	display: flex;
	justify-content: flex-end;
	margin-bottom: 10px;
}

.pf-title {
	border: 2px solid #2f2f2f;
	border-radius: 20px;
	color: #222 !important;
	font-size: 24px;
	font-weight: 800;
	margin: 0 0 8px;
	padding: 8px 12px;
	text-align: center;
	text-transform: uppercase;
}

.pf-sub {
	font-size: 13px;
	font-style: italic;
	margin: 0 0 8px;
	text-align: right;
}

.pf-table {
	border: 1px solid #333;
	border-collapse: collapse;
	margin-bottom: 10px;
	width: 100%;
}

.pf-table td,
.pf-table th {
	border: 1px solid #333;
	padding: 6px 8px;
	vertical-align: top;
}

.pf-table th {
	background: #f5f5f5;
	font-weight: 700;
}

.pf-checkline {
	margin: 8px 0;
}

.pf-rule {
	border-top: 1px dashed #777;
	margin: 10px 0;
}

.pf-input {
	background: #fff;
	border: 0;
	border-bottom: 1px solid #555;
	border-radius: 0;
	box-shadow: none;
	padding: 2px 4px;
	width: 100%;
}

.pf-checkline input[type="checkbox"] {
	margin-right: 8px;
	vertical-align: middle;
}

.pf-supervisor-line {
	font-size: 13px;
	font-weight: 700;
	margin: 4px 0 10px;
	text-align: left;
}

.pf-form-break {
	border-top: 2px dashed #aaa;
	margin: 18px 0;
}

@media print {
	@page { margin: 10mm; }
	.pf-toolbar { display: none; }
	.pf-form-break {
		border-top: 0;
		margin: 0;
		page-break-before: always;
	}
}
</style>

<div class="pf-print-page">
	<div class="pf-toolbar">
		<button type="submit" class="btn btn-primary" form="adult-permission-form"><i class="fa fa-check-circle"></i> Submit Form</button>
	</div>
	<?php
	$schoolName = '';
	if (!empty($userDetails->school_name)) {
		$schoolName = $userDetails->school_name;
	} elseif (!empty($userDetails->first_name)) {
		$schoolName = $userDetails->first_name;
	} elseif (!empty($conventionRegD->Users['school_name'])) {
		$schoolName = $conventionRegD->Users['school_name'];
	} elseif (!empty($conventionRegD->Users['first_name'])) {
		$schoolName = $conventionRegD->Users['first_name'];
	} elseif (!empty($userDetails->first_name) || !empty($userDetails->last_name)) {
		$schoolName = trim(($userDetails->first_name ?? '') . ' ' . ($userDetails->last_name ?? ''));
	}

	$customerCode = !empty($userDetails->customer_code) ? $userDetails->customer_code : '';
	$today = date('d/m/Y');
	$formRows = !empty($adultForms) ? $adultForms : [['supervisor' => null, 'assignedStudents' => []]];
	?>

	<?php foreach ($formRows as $formIndex => $formRow): ?>
		<?php
		$supervisor = !empty($formRow['supervisor']) ? $formRow['supervisor'] : null;
		$assignedStudents = !empty($formRow['assignedStudents']) ? $formRow['assignedStudents'] : [];

		$supervisorName = '';
		$supervisorGender = '';
		$supervisorPhone = '';
		if (!empty($supervisor)) {
			$supervisorName = trim(($supervisor->first_name ?? '') . ' ' . ($supervisor->middle_name ?? '') . ' ' . ($supervisor->last_name ?? ''));
			$supervisorGender = strtoupper((string)($supervisor->gender ?? ''));
			$supervisorPhone = !empty($supervisor->phone_no) ? $supervisor->phone_no : (!empty($supervisor->mobile_no) ? $supervisor->mobile_no : '');
		}

		$isMale = in_array($supervisorGender, ['M', 'MALE'], true);
		$isFemale = in_array($supervisorGender, ['F', 'FEMALE'], true);
		$studentRowCount = max(6, count($assignedStudents));
		?>

		<?php if ($formIndex > 0): ?>
			<div class="pf-form-break"></div>
		<?php endif; ?>

		<form id="adult-permission-form" method="post" action="#">
			<h1 class="pf-title">Adult Registration Form</h1>
			<p class="pf-sub">(Please tick the appropriate boxes)</p>
			<p class="pf-supervisor-line">Supervisor <?php echo (int)($formIndex + 1); ?> of <?php echo (int)count($formRows); ?></p>

			<table class="pf-table">
				<tr>
					<th style="width:140px;">Name:</th>
					<td><input type="text" class="pf-input" value="<?php echo h($supervisorName); ?>"></td>
					<th style="width:120px;"><label><input type="checkbox" checked> Over 21</label></th>
					<th style="width:120px;"><label><input type="checkbox" <?php echo $isMale ? 'checked' : ''; ?>> Male</label> <label><input type="checkbox" <?php echo $isFemale ? 'checked' : ''; ?>> Female</label></th>
				</tr>
				<tr>
					<th>School/HSSP:</th>
					<td><input type="text" class="pf-input" value="<?php echo h($schoolName); ?>"></td>
					<th>Cust. Code:</th>
					<td><input type="text" class="pf-input" value="<?php echo h($customerCode); ?>"></td>
				</tr>
				<tr>
					<th>Mobile Phone:</th>
					<td colspan="3"><input type="text" class="pf-input" value="<?php echo h($supervisorPhone); ?>"></td>
				</tr>
			</table>

			<p><strong>Your experience with the A.C.E. program:</strong></p>
			<table class="pf-table">
				<tr>
					<th style="width:180px;">Length of Time:</th>
					<td>Years: <input type="text" class="pf-input" style="width:140px;display:inline-block;">  Months: <input type="text" class="pf-input" style="width:140px;display:inline-block;"></td>
				</tr>
				<tr>
					<th>Position Held:</th>
					<td>
						<label><input type="checkbox"> Pastor</label>
						<label><input type="checkbox"> Principal</label>
						<label><input type="checkbox"> Parent</label>
						<label><input type="checkbox"> Monitor</label>
						<label><input type="checkbox"> Coach</label>
						<label><input type="checkbox" checked> Supervisor</label>
						Other: <input type="text" class="pf-input" style="width:240px;display:inline-block;">
					</td>
				</tr>
			</table>

			<p class="pf-checkline"><label><input type="checkbox" <?php echo !empty($assignedStudents) ? 'checked' : ''; ?>> I am sponsoring students.</label></p>
			<p class="pf-checkline"><label><input type="checkbox"> I am the First Aid Officer for the school/HSSP.</label></p>
			<p class="pf-checkline"><label><input type="checkbox"> I am volunteering to judge and sponsor my students while judging (if applicable).</label></p>
			<p class="pf-checkline"><label><input type="checkbox"> I have submitted an Application for Scripture Award.</label></p>

			<div class="pf-rule"></div>
			<p><strong>Please list the full names of the student(s) from your school/family for whom you are responsible:</strong></p>
			<table class="pf-table">
				<tr>
					<th style="width:80px;">M/F</th>
					<th>Full Name</th>
					<th style="width:90px;">Age</th>
				</tr>
				<?php for ($rowIndex = 0; $rowIndex < $studentRowCount; $rowIndex++): ?>
					<?php
					$student = isset($assignedStudents[$rowIndex]) ? $assignedStudents[$rowIndex] : null;
					$studentName = '';
					$studentGenderShort = '';
					$studentAge = '';

					if (!empty($student)) {
						$studentName = trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? ''));
						$studentGenderRaw = strtoupper((string)($student->gender ?? ''));
						if (in_array($studentGenderRaw, ['F', 'FEMALE'], true)) {
							$studentGenderShort = 'F';
						} elseif (in_array($studentGenderRaw, ['M', 'MALE'], true)) {
							$studentGenderShort = 'M';
						}

						if (!empty($student->birth_year) && is_numeric($student->birth_year)) {
							$studentAge = (string)(date('Y') - (int)$student->birth_year);
						}
					}
					?>
					<tr>
						<td><input type="text" class="pf-input" value="<?php echo h($studentGenderShort); ?>"></td>
						<td><input type="text" class="pf-input" value="<?php echo h($studentName); ?>"></td>
						<td><input type="text" class="pf-input" value="<?php echo h($studentAge); ?>"></td>
					</tr>
				<?php endfor; ?>
			</table>

			<table class="pf-table">
				<tr>
					<th style="width:160px;">Signature:</th>
					<td><input type="text" class="pf-input" value="<?php echo h($supervisorName); ?>"></td>
					<th style="width:80px;">Date:</th>
					<td style="width:180px;"><input type="text" class="pf-input" value="<?php echo h($today); ?>"></td>
				</tr>
			</table>
		</form>
	<?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var adultForm = document.getElementById('adult-permission-form');
	if (!adultForm) {
		return;
	}

	adultForm.addEventListener('submit', function (event) {
		event.preventDefault();

		var storageKey = 'acp_permission_form_submissions';
		var counts = {};
		try {
			counts = JSON.parse(localStorage.getItem(storageKey) || '{}');
		} catch (error) {
			counts = {};
		}

		var existingCount = parseInt(counts.adult_registration, 10) || 0;
		var nextCount = existingCount + 1;
		counts.adult_registration = nextCount;
		localStorage.setItem(storageKey, JSON.stringify(counts));

		if (window.opener && !window.opener.closed) {
			window.opener.dispatchEvent(new window.opener.CustomEvent('permissionFormSubmitted', {
				detail: {
					formKey: 'adult_registration',
					count: nextCount
				}
			}));
		}

		var message = document.createElement('div');
		message.className = 'pf-submit-confirmation';
		message.textContent = 'Form submitted successfully.';
		message.style.cssText = 'margin-top: 12px; background: #e9f9ef; border: 1px solid #bfe6ce; color: #246b3a; padding: 10px 12px; border-radius: 8px; font-weight: 600;';
		adultForm.appendChild(message);

		setTimeout(function () {
			if (window && window.close) {
				window.close();
			}
		}, 700);
	});
});
</script>
