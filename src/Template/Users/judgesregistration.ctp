<script type="text/javascript">
	$(document).ready(function () {
		var judgesRegValidator = $("#judgesreg_frm").validate({
			rules: {
				'Users[confirm_password]': {
					equalTo: '#password'
				}
			}
		});

		$('#confirm_password')
			.removeAttr('equalto')
			.removeAttr('data-rule-equalto')
			.rules('add', {
				equalTo: '#password'
			});
	});
</script>

<section class="hp-hero position-relative" style="background: linear-gradient(135deg, #1c2452 0%, #0f1633 60%, #1a3a5c 100%); background-size: 300% 300%; animation: hp-grad-shift 12s ease infinite;">
	<canvas id="hp-particles" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;pointer-events:none;"></canvas>
	<script>
	(function(){
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
	})();
	</script>
	<div class="hp-wave hp-wave-left left-element w-25">
		<?php echo $this->Html->image('front/left-element.png'); ?>
	</div>
	<div class="hp-wave hp-wave-right ryt-element w-25">
		<?php echo $this->Html->image('front/ryt-element.png'); ?>
	</div>
	<div class="container hp-hero-inner">
		<div class="row center-section align-items-center">
			<?php echo $this->element('Homes/home_left_content'); ?>


			<div class="col-lg-6 ">
				<div class="ryt-box bg-white w-100">
					<div class="ryt-box-text form-group">
						<div class="ersu_message">
							<?php echo $this->Flash->render() ?>
						</div>
						<?php echo $this->Form->create($users, ['id' => 'judgesreg_frm', 'type' => 'file']); ?>
						<h2 class="mb-4">Judges Registration</h2>
						<div>
							
							<div class="lables">
								<span class="col-4">First Name</span>
								<?php echo $this->Form->input('Users.first_name', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'First Name']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Surname</span>
								<?php echo $this->Form->input('Users.last_name', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'Surname']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Email Address</span>
								<?php echo $this->Form->input('Users.email_address', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required email', 'placeholder' => 'Email Address']); ?>
							</div>

							<div class="lables">
								<span class="col-4">Password</span>
								<?php echo $this->Form->input('Users.password', ['id' => 'password', 'label' => '', 'type' => 'password', 'label' => false, 'div' => false, 'class' => "form-control required", 'placeholder' => 'Password', 'minlength'=>6]); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Confirm Password</span>
								<?php echo $this->Form->input('Users.confirm_password', ['id' => 'confirm_password','label' => '', 'type' => 'password', 'label' => false, 'div' => false, 'class' => "form-control required", 'placeholder' => 'Confirm Password', 'equalTo'=>'#password']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Previous convention experience ? </span>
								<?php echo $this->Form->input('Users.previous_convention_experience', ['id' => '', 'label' => false, 'type' => 'textarea', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'What is your previous convention experience?']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Other non-convention experience? </span>
								<?php echo $this->Form->input('Users.non_convention_experience', ['label' => false, 'type' => 'textarea', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'What other non-convention experience do you have that would assist in your area of interest for judging?']); ?>
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