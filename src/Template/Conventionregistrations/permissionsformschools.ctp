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
	width: 24%;
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

	<h1 class="pf-title">Permission For Participation Form - Schools</h1>
	<p class="pf-note">Schools using the A.C.E. program should keep a signed participation form for each participating student.</p>

	<table class="pf-table">
		<tr>
			<td style="width:140px;"><strong>School Name:</strong></td>
			<td><input type="text" class="pf-input" value="<?php echo !empty($conventionRegD->Conventions['convention_name']) ? h($conventionRegD->Conventions['convention_name']) : ''; ?>"></td>
		</tr>
	</table>

	<p class="pf-box-title">Please initial inside each box</p>
	<table class="pf-table">
		<tr>
			<td style="width:42px;"></td>
			<td>All attending students from the above school have parent/guardian permission and consent to attend and participate in convention events of the <input type="text" class="pf-input pf-inline-input"> (year <input type="text" class="pf-input pf-inline-input">).</td>
		</tr>
		<tr>
			<td></td>
			<td>Participation in convention activities is a privilege and not a right and may be revoked at any time for cause.</td>
		</tr>
	</table>

	<table class="pf-table">
		<tr><td>Participants acknowledge photographs and video footage may be used for promotional and social media purposes.</td></tr>
		<tr><td>Students under protection orders must be identified to SCEE staff upon check-in at convention.</td></tr>
	</table>

	<table class="pf-table">
		<tr><td style="width:42px;"></td><td>Participants from the above school agree to assume all risks associated with convention participation.</td></tr>
		<tr><td></td><td>Activities prior to, during, and after convention week.</td></tr>
		<tr><td></td><td>Transportation to and from the convention and to off-site events.</td></tr>
		<tr><td></td><td>Participation in competitions and activities, including physical interactions.</td></tr>
		<tr><td></td><td>Direct responsibility of participating students to their nominated sponsor.</td></tr>
	</table>

	<table class="pf-table">
		<tr><td>The school releases Southern Cross Educational Enterprises Ltd. from liability for injury resulting from voluntary participation.</td></tr>
	</table>

	<table class="pf-table">
		<tr>
			<th style="width:220px;">Special Student Notes:</th>
			<td><textarea class="pf-textarea"></textarea></td>
		</tr>
	</table>

	<table class="pf-table">
		<tr>
			<th style="width:220px;">First Aid Officer:</th>
			<td><input type="text" class="pf-input"></td>
			<th style="width:200px;">Training Updated:</th>
			<td><input type="text" class="pf-input"></td>
		</tr>
		<tr>
			<th>School Signatories:</th>
			<td colspan="3"><input type="text" class="pf-input"></td>
		</tr>
		<tr>
			<th>Position:</th>
			<td colspan="3"><input type="text" class="pf-input"></td>
		</tr>
		<tr>
			<th>Phone:</th>
			<td><input type="text" class="pf-input"></td>
			<th>Date:</th>
			<td><input type="text" class="pf-input"></td>
		</tr>
	</table>
	</form>
</div>
