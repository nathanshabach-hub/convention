<script type="text/javascript">
$(document).ready(function () {
	$("#editprofile").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 profile-edit-page">
			<h2 class="mt-3 profile-page-title">Edit Profile</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form profile-edit-form">
				<h2 class="form-title">Edit Profile</h2>
				<?php echo $this->Form->create($users, ['id'=>'editprofile', 'type' => 'file', 'class' =>' ']); ?>
					
					<?php
					// for school admin
					if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "School"))
					{
					?>
					<div class="form-group">
						<label for="name">Customer Code</label>
						<?php echo $this->Form->input('Users.customer_code_no_save', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Customer Code', 'value'=>$users->customer_code,'readonly','disabled']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">School/HSSP Name</label>
						<?php echo $this->Form->input('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'School/HSSP Name']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Contact Person</label>
						<?php echo $this->Form->input('Users.middle_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Contact Person']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Telephone 1</label>
						<?php echo $this->Form->input('Users.phone', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Telephone 1']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Telephone 2</label>
						<?php echo $this->Form->input('Users.phone2', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Telephone 2']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Email Address</label>
						<?php echo $this->Form->input('Users.email_address_old', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required email', 'placeholder'=>'Email Address', 'value'=>$users->email_address,'readonly','disabled']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Bill To Street</label>
						<?php echo $this->Form->input('Users.bill_to_street', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Bill To Street']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Bill To Block</label>
						<?php echo $this->Form->input('Users.bill_to_block', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Bill To Block']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Bill To City</label>
						<?php echo $this->Form->input('Users.bill_to_city', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Bill To City']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Bill To Zip</label>
						<?php echo $this->Form->input('Users.bill_to_zip', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Bill To Zip']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Bill To Country</label>
						<?php echo $this->Form->input('Users.bill_to_country', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Bill To Country']); ?>
					</div>
					<?php
					}
					?>
					
					<?php
					// for teacher_parent or supervisor
					if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "Teacher_Parent"))
					{
					?>
					
					<div class="form-group">
						<label for="name">School/HSSP Name</label>
						<?php echo $userDetails->Schools['first_name']; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Email Address</label>
						<?php echo $userDetails->email_address; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Judge ?</label>
						<?php echo ($userDetails->is_judge == 1) ? 'Yes': 'No'; ?>
					</div>

					<div class="form-group profile-volunteer-options">
						<label class="profile-volunteer-heading">Volunteer / responsibility</label>
						<div class="checkbox-group profile-checkbox-group">
							<label><input type="checkbox" name="Users[is_sponsoring_students]" value="1" <?php echo (!empty($users->is_sponsoring_students) || !empty($userDetails->is_sponsoring_students)) ? 'checked' : ''; ?>> <span>I am sponsoring students.</span></label>
							<label><input type="checkbox" name="Users[first_aid_officer]" value="1" <?php echo (!empty($users->first_aid_officer) || !empty($userDetails->first_aid_officer)) ? 'checked' : ''; ?>> <span>I am the First Aid Officer for the school/HSSP.</span></label>
							<label><input type="checkbox" name="Users[volunteer_judge_and_sponsor]" value="1" <?php echo (!empty($users->volunteer_judge_and_sponsor) || !empty($userDetails->volunteer_judge_and_sponsor)) ? 'checked' : ''; ?>> <span>I am volunteering to judge and sponsor my students while judging (if applicable).</span></label>
							<label><input type="checkbox" name="Users[scripture_award_application]" value="1" <?php echo (!empty($users->scripture_award_application) || !empty($userDetails->scripture_award_application)) ? 'checked' : ''; ?>> <span>I have submitted an Application for Scripture Award.</span></label>
						</div>
					</div>

					<div class="form-group profile-experience-options">
						<label class="profile-experience-heading">Your experience with the A.C.E. program:</label>
						<div class="profile-experience-fields">
							<span>Length of Time:</span>
							<label>Years <input type="number" name="Users[ace_experience_years]" min="0" step="1" inputmode="numeric"></label>
							<label>Months <input type="number" name="Users[ace_experience_months]" min="0" max="11" step="1" inputmode="numeric"></label>
						</div>
					</div>
					
					<div class="form-group">
						<label for="name">First Name</label>
						<?php echo $this->Form->input('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'First Name']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Last Name</label>
						<?php echo $this->Form->input('Users.last_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Last Name']); ?>
					</div>
					
					
					<?php
					}
					?>

					<?php
					// for student
					if ($this->request->session()->read("user_id") > 0 && ($this->request->session()->read("user_type") == "Student"))
					{
					?>
					<div class="form-group">
						<label for="name">Login Code</label>
						<?php echo $this->Form->input('Users.customer_code_no_save', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Login Code', 'value'=>$users->customer_code,'readonly','disabled']); ?>
					</div>

					<div class="form-group">
						<label for="name">First Name</label>
						<?php echo $this->Form->input('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'First Name']); ?>
					</div>

					<div class="form-group">
						<label for="name">Middle Name</label>
						<?php echo $this->Form->input('Users.middle_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Middle Name']); ?>
					</div>

					<div class="form-group">
						<label for="name">Last Name</label>
						<?php echo $this->Form->input('Users.last_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Last Name']); ?>
					</div>

					<div class="form-group">
						<label for="name">Email Address</label>
						<?php echo $this->Form->input('Users.email_address', ['label'=>false, 'type'=>'email',  'div'=>false, 'class'=>'form-control required email', 'placeholder'=>'Email Address']); ?>
					</div>
					<?php
					}
					?>
					
					<div class="form-group form-btns">
						<label></label>
						<button type="submit" class="btn btn-secondary">Submit</button>
						<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
						<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'dashboard'], ['class'=>'btn btn-secondary']); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>