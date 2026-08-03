<div class="container-fluid p-0 cr-permforms-page">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 cr-permforms-main">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<div class="cr-permforms-hero">
				<div>
					<span class="cr-permforms-kicker">Documentation</span>
					<h1 class="cr-permforms-title">Permission Forms</h1>
					<span class="cr-permforms-subtitle">Access and print the required convention permission documents for students, schools, and adults.</span>
				</div>
			</div>

			<div class="cr-permforms-grid">
				<div class="cr-permforms-card">
					<div class="cr-permforms-card-head">
						<h2>HSSP Students Form</h2>
					</div>
					<p>Permission for Participation Form for HSSP students and parents/guardians.</p>
					<div class="cr-permforms-actions">
						<?php echo $this->Html->link('<i class="fa fa-file-text"></i> Open Fillable Form', ['controller' => 'conventionregistrations', 'action' => 'permissionsformhssp'], ['escape' => false, 'class' => 'btn btn-outline-primary btn-sm js-permform-popup', 'data-title' => 'HSSP Students Form']); ?>
					</div>
				</div>

				<div class="cr-permforms-card">
					<div class="cr-permforms-card-head">
						<h2>Schools Form</h2>
					</div>
					<p>School-level participation permission form covering all attending students.</p>
					<div class="cr-permforms-actions">
						<?php echo $this->Html->link('<i class="fa fa-file-text"></i> Open Fillable Form', ['controller' => 'conventionregistrations', 'action' => 'permissionsformschools'], ['escape' => false, 'class' => 'btn btn-outline-primary btn-sm js-permform-popup', 'data-title' => 'Schools Form']); ?>
					</div>
				</div>

				<div class="cr-permforms-card">
					<div class="cr-permforms-card-head">
						<h2>Adult Registration Form</h2>
					</div>
					<p>Adult registration and sponsorship responsibility declaration form.</p>
					<div class="cr-permforms-actions">
						<?php echo $this->Html->link('<i class="fa fa-file-text"></i> Open Fillable Form', ['controller' => 'conventionregistrations', 'action' => 'permissionsformadult'], ['escape' => false, 'class' => 'btn btn-outline-primary btn-sm js-permform-popup', 'data-title' => 'Adult Registration Form']); ?>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var links = document.querySelectorAll('.js-permform-popup');
	links.forEach(function (link) {
		link.addEventListener('click', function (event) {
			event.preventDefault();
			var href = link.getAttribute('href');
			var title = (link.getAttribute('data-title') || 'Permission Form').replace(/\s+/g, '_');
			var popup = window.open(
				href,
				title,
				'width=1100,height=900,menubar=no,toolbar=no,location=no,status=no,resizable=yes,scrollbars=yes'
			);
			if (popup && popup.focus) {
				popup.focus();
			}
		});
	});
});
</script>

<style>
.cr-permforms-page {
	background:
		radial-gradient(circle at 10% 0%, rgba(28, 36, 82, 0.08), transparent 30%),
		linear-gradient(180deg, #f5f8fc 0%, #eef3f8 100%);
	min-height: 100vh;
}

.cr-permforms-main {
	padding-top: 14px;
	padding-bottom: 16px;
}

.cr-permforms-hero {
	background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 250, 255, 0.98) 100%);
	border: 1px solid #d8e3ef;
	border-radius: 18px;
	box-shadow: 0 12px 30px rgba(19, 53, 90, 0.08);
	margin: 8px 0 16px;
	padding: 18px;
}

.cr-permforms-kicker {
	color: #6d7f98;
	display: block;
	font-size: 12px;
	font-weight: 700;
	letter-spacing: 0.08em;
	margin: 0 0 6px;
	text-transform: uppercase;
}

.cr-permforms-title {
	color: #16355a;
	font-size: 32px;
	font-weight: 800;
	line-height: 1.15;
	margin: 0;
}

.cr-permforms-subtitle {
	color: #2f4f73;
	display: block;
	font-size: 14px;
	line-height: 1.5;
	margin: 8px 0 0;
	max-width: 760px;
}

.cr-permforms-grid {
	display: grid;
	gap: 14px;
	grid-template-columns: repeat(3, minmax(0, 1fr));
}

.cr-permforms-card {
	background: #fff;
	border: 1px solid #d8e3ef;
	border-radius: 16px;
	box-shadow: 0 10px 24px rgba(19, 53, 90, 0.07);
	padding: 16px;
}

.cr-permforms-card-head h2 {
	color: #17355a;
	font-size: 18px;
	font-weight: 700;
	margin: 0 0 8px;
}

.cr-permforms-card p {
	color: #314f72;
	font-size: 13px;
	line-height: 1.5;
	margin: 0 0 12px;
}

.cr-permforms-actions .btn {
	border-radius: 8px;
	font-weight: 600;
}

.cr-permforms-actions .btn.btn-outline-primary {
	background: #eef5ff;
	border-color: #2f7fbc;
	color: #1b5785 !important;
	text-decoration: none;
}

.cr-permforms-actions .btn.btn-outline-primary:hover,
.cr-permforms-actions .btn.btn-outline-primary:focus {
	background: #2f7fbc;
	border-color: #2f7fbc;
	color: #ffffff !important;
}

@media (max-width: 991px) {
	.cr-permforms-title {
		font-size: 28px;
	}

	.cr-permforms-grid {
		grid-template-columns: 1fr;
	}
}

@media (max-width: 575px) {
	.cr-permforms-title {
		font-size: 24px;
	}

	.cr-permforms-hero,
	.cr-permforms-card {
		padding: 12px;
	}
}
</style>
