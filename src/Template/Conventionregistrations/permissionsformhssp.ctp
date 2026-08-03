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

.pf-toolbar .btn {
	border-radius: 8px;
	font-weight: 600;
}

.pf-title {
	border: 2px solid #2f2f2f;
	border-radius: 20px;
	font-size: 24px;
	font-weight: 800;
	margin: 0 0 8px;
	padding: 8px 12px;
	text-align: center;
	text-transform: uppercase;
}

.pf-note {
	font-size: 16px;
	margin: 4px 0 10px;
	text-align: center;
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

.pf-small {
	font-size: 12px;
}

.pf-input,
.pf-textarea {
	background: #fff;
	border: 0;
	border-bottom: 1px solid #555;
	border-radius: 0;
	box-shadow: none;
	padding: 2px 4px;
	width: 100%;
}

.pf-inline-input {
	display: inline-block;
	min-width: 120px;
	width: 22%;
}

.pf-textarea {
	border: 1px solid #777;
	min-height: 64px;
	resize: vertical;
}

.pf-box-title {
	color: #c12323;
	font-size: 12px;
	font-weight: 700;
	margin: 0 0 2px;
}

.pf-lines div {
	border-bottom: 1px solid #777;
	height: 22px;
}

@media print {
	@page { margin: 10mm; }
	.pf-toolbar { display: none; }
}
</style>

<div class="pf-print-page">
	<div class="pf-toolbar">
		<button class="btn btn-primary" onclick="window.print()"><i class="fa fa-print"></i> Print / Save PDF</button>
	</div>
	<form>

	<h1 class="pf-title">Permission For Participation Form - HSSP Students</h1>
	<p class="pf-note">This form is required for all HSSP students. Please list the students' names below.</p>

	<table class="pf-table">
		<tr>
			<td style="width:80px;"><strong>HSSP:</strong></td>
			<td><input type="text" class="pf-input" value="<?php echo !empty($conventionRegD->Conventions['convention_name']) ? h($conventionRegD->Conventions['convention_name']) : ''; ?>"></td>
		</tr>
		<tr>
			<td colspan="2" class="pf-small" style="text-align:center;">(Please list all the attendees from your group below)</td>
		</tr>
		<tr><td colspan="2"><input type="text" class="pf-input"></td></tr>
		<tr><td colspan="2"><input type="text" class="pf-input"></td></tr>
		<tr><td colspan="2"><input type="text" class="pf-input"></td></tr>
		<tr><td colspan="2"><input type="text" class="pf-input"></td></tr>
		<tr><td colspan="2"><input type="text" class="pf-input"></td></tr>
	</table>

	<p class="pf-box-title">Please initial inside each box</p>
	<table class="pf-table">
		<tr>
			<td style="width:42px;"></td>
			<td>As the parent/guardian and legal representative of the above named student, I give my consent and permission for my child to attend and participate in the events of the <input type="text" class="pf-input pf-inline-input"> (year <input type="text" class="pf-input pf-inline-input">).</td>
		</tr>
		<tr>
			<td></td>
			<td>I understand that participation is a privilege and not a right and may be revoked for cause at any time at the discretion of Student Convention officials.</td>
		</tr>
	</table>

	<table class="pf-table">
		<tr><td>Participants acknowledge that photographs and video footage may be used for promotional or social media purposes.</td></tr>
		<tr><td>Students under protection orders must be identified to convention staff upon check-in.</td></tr>
	</table>

	<table class="pf-table">
		<tr><td style="width:42px;"></td><td>Participants understand and agree to assume all risks which may be encountered with participation in the convention and related activities.</td></tr>
		<tr><td></td><td>Transportation to and from the convention and to off-site events during competition week.</td></tr>
		<tr><td></td><td>Participation in activities including social, emotional, mental and physical interactions.</td></tr>
		<tr><td></td><td>Direct responsibility of the child to their nominated sponsor and related interactions.</td></tr>
		<tr><td></td><td>I release Southern Cross Educational Enterprises Ltd. from liability for injury that may result from voluntary participation.</td></tr>
	</table>

	<table class="pf-table">
		<tr>
			<th style="width:220px;">Special Medical Notes:</th>
			<td><textarea class="pf-textarea"></textarea></td>
		</tr>
	</table>

	<table class="pf-table">
		<tr>
			<th style="width:220px;">Parent/Guardian Signatures:</th>
			<td><input type="text" class="pf-input"></td>
		</tr>
		<tr>
			<th>Address:</th>
			<td><input type="text" class="pf-input"></td>
		</tr>
		<tr>
			<th>Phone:</th>
			<td><input type="text" class="pf-input"></td>
		</tr>
		<tr>
			<th>Date:</th>
			<td><input type="text" class="pf-input"></td>
		</tr>
	</table>
	</form>
</div>
