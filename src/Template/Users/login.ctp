<script type="text/javascript">
	$(document).ready(function () {
		$("#login_frm").validate();

		function toggleLoginMode() {
			var selectedType = $("#user_type").val();
			var isStudent = selectedType === "Student";

			if (isStudent) {
				$(".js-student-fields").show();
				$(".js-standard-fields").hide();
				$("#users-email-address, #users-password").removeClass("required email");
				$("#student_code, #student_last4").addClass("required");
			} else {
				$(".js-student-fields").hide();
				$(".js-standard-fields").show();
				$("#users-email-address").addClass("required email");
				$("#users-password").addClass("required");
				$("#student_code, #student_last4").removeClass("required");
			}
		}

		$("#user_type").on("change", toggleLoginMode);
		toggleLoginMode();
	});
</script>
<?php $loginUserTypes = $loginUserTypes ?? []; ?>

<section class="position-relative" style="background: linear-gradient(135deg, #1c2452 0%, #0f1633 60%, #1a3a5c 100%); background-size: 300% 300%; animation: hp-grad-shift 12s ease infinite;">
	<canvas id="hp-particles" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;pointer-events:none;"></canvas>
	<script>
	window.addEventListener('load', function(){
		var canvas = document.getElementById('hp-particles');
		var ctx = canvas.getContext('2d');
		var particles = [];
		var count = 70;
		function resize(){ canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; }
		window.addEventListener('resize', resize);
		resize();
		for(var i=0; i<count; i++){
			particles.push({ x: Math.random()*canvas.width, y: Math.random()*canvas.height, r: Math.random()*2+0.5, dx: (Math.random()-0.5)*0.4, dy: (Math.random()-0.5)*0.4, alpha: Math.random()*0.5+0.1 });
		}
		function draw(){
			ctx.clearRect(0,0,canvas.width,canvas.height);
			particles.forEach(function(p){
				ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI*2);
				ctx.fillStyle = 'rgba(120,160,255,'+p.alpha+')'; ctx.fill();
				p.x += p.dx; p.y += p.dy;
				if(p.x<0) p.x=canvas.width; if(p.x>canvas.width) p.x=0;
				if(p.y<0) p.y=canvas.height; if(p.y>canvas.height) p.y=0;
			});
			requestAnimationFrame(draw);
		}
		draw();
	});
	</script>
	<div class=" left-element w-25">
		<?php echo $this->Html->image('front/left-element.png'); ?>
	</div>
	<div class=" ryt-element w-25">
		<?php echo $this->Html->image('front/ryt-element.png'); ?>
	</div>
	<div class="container">
		<div class="row center-section align-items-center">
			<?php echo $this->element('Homes/home_left_content'); ?>


			<div class="col-lg-6 ">
				<div class="ryt-box bg-white w-100">
					<div class="ryt-box-text form-group">
						<div class="ersu_message">
							<?php echo $this->Flash->render() ?>
						</div>
						<?php echo $this->Form->create(null, ['id' => 'login_frm', 'type' => 'file']); ?>
						<h2 class="mb-4">Login</h2>
						<div>
							<div class="lables">
								<span class="col-4">Choose Type</span>
								<div class="w-100">
									<?php echo $this->Form->select('Users.user_type', $loginUserTypes, ['id' => 'user_type', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
								</div>
							</div>

							<div class="lables js-standard-fields">
								<span class="col-4">Email Address</span>
								<?php echo $this->Form->input('Users.email_address', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required email', 'placeholder' => 'Email Address']); ?>
							</div>

							<div class="lables js-standard-fields">
								<span class="col-4">Password</span>
								<?php echo $this->Form->input('Users.password', ['label' => '', 'type' => 'password', 'label' => false, 'div' => false, 'class' => "form-control required", 'placeholder' => 'Password']); ?>
							</div>

							<div class="lables js-student-fields" style="display:none;">
								<span class="col-4">Student Code</span>
								<?php echo $this->Form->input('Users.student_code', ['id' => 'student_code', 'label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control', 'placeholder' => 'Student Code (e.g. STUAB12)']); ?>
							</div>

							<div class="lables js-student-fields" style="display:none;">
								<span class="col-4">Last Name (first 4 letters)</span>
								<?php echo $this->Form->input('Users.student_last4', ['id' => 'student_last4', 'label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control', 'maxlength' => 4, 'placeholder' => 'First 4 letters of last name']); ?>
							</div>

							<div class="btns col-8 float-end">
								<span class=" mb-3 w-100">
									<?php echo $this->Html->link('Forgot Password?', ['controller' => 'users', 'action' => 'forgotpassword'], ['escape' => false, 'class' => 'text-primary ms-1']); ?>
									<!--<a href="" class="text-primary ms-1">Forgot Password?</a>-->
								</span>
								<button type="submit" class="btn btn-secondary px-3 ms-1">Submit</button>
							</div>
						</div>
						<?php echo $this->Form->end(); ?>
					</div>
				</div>
			</div>


		</div>
	</div>
</section>