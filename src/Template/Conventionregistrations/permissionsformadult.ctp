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

	<h1 class="pf-title">Adult Registration Form</h1>
	<p class="pf-sub">(Please tick the appropriate boxes)</p>

	<table class="pf-table">
		<tr>
			<th style="width:140px;">Name:</th>
			<td><input type="text" class="pf-input"></td>
			<th style="width:120px;">Over 21</th>
			<th style="width:120px;"><label><input type="checkbox"> Male</label> <label><input type="checkbox"> Female</label></th>
		</tr>
		<tr>
			<th>School/HSSP:</th>
			<td><input type="text" class="pf-input" value="<?php echo !empty($conventionRegD->Conventions['convention_name']) ? h($conventionRegD->Conventions['convention_name']) : ''; ?>"></td>
			<th>Cust. Code:</th>
			<td><input type="text" class="pf-input"></td>
		</tr>
		<tr>
			<th>Mobile Phone:</th>
			<td colspan="3"><input type="text" class="pf-input"></td>
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
				<label><input type="checkbox"> Supervisor</label>
				Other: <input type="text" class="pf-input" style="width:240px;display:inline-block;">
			</td>
		</tr>
	</table>

	<p class="pf-checkline"><label><input type="checkbox"> I am sponsoring students.</label></p>
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
		<tr><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td></tr>
		<tr><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td></tr>
		<tr><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td></tr>
		<tr><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td></tr>
		<tr><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td></tr>
		<tr><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td><td><input type="text" class="pf-input"></td></tr>
	</table>

	<table class="pf-table">
		<tr>
			<th style="width:160px;">Signature:</th>
			<td><input type="text" class="pf-input"></td>
			<th style="width:80px;">Date:</th>
			<td style="width:180px;"><input type="text" class="pf-input"></td>
		</tr>
	</table>
	</form>
</div>
