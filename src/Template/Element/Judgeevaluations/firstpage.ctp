<div class="first_page">
	<div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center">
		<div class="header_css mt-4 mb-4">
			<h1 style="color:#000;">Accelerate Educational Ministries</h1>
		</div>
		<div class="logo_m mb-4">
			<img src="<?php echo $signatureImgP 	= HTTP_PATH.'/img/front/ace_logo.png'; ?>" alt="Logo">
		</div>
		<div class="details_middle">
			<div class="header_css mt-2 mb-2 text-center">
				<h5>Presents</h5>
			</div>
			<div class="header_css mt-2 mb-2 text-center">
				<h2 style="color:#000;">Individual Result Package</h2>
			</div>
			<div class="header_css mt-2 mb-2 text-center">
				<h5>For</h5>
			</div>
			<h5 class="text-center" style="font-size: 34px; font-family: Pinyon Script, cursive;"><?php echo $convRegStudentD->Students['first_name'].' '.$convRegStudentD->Students['middle_name'].' '.$convRegStudentD->Students['last_name']; ?></h5>
			<div class="header_css mt-2 mb-2 text-center">
				<h5>Of</h5>
			</div>
			<h5 class="text-center mt-2 mb-2" style="font-size: 28px; font-family: Pinyon Script, cursive;"><?php echo $convRegStudentD->Users['first_name']; ?></h5>

			<h5 class="text-center mt-4" style="font-size: 28px; font-family: Pinyon Script, cursive;">For participation in <?php echo $conventionRegD->Conventions['name']; ?>  <?php echo $conventionRegD->season_year; ?></h5>
		</div>
		<div class="footer_css mt-4 mb-0 text-center">
			<h5>Accelerate Christian Education <span>®</span></h5>
		</div>
	</div>
</div>