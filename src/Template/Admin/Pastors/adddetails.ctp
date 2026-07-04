<div class="content-wrapper admin-list-page admin-pastors-page">
    <section class="content-header">
      <h1>
         Add Pastor Details
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Pastors', ['controller'=>'pastors', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active"> Add Details </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Add Details</h3>
            </div>
            <div class="box-body">
                <?php echo $this->Form->create($pastor, ['id' => 'adminForm']); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <?php echo $this->Form->input('Users.first_name', ['label' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => 'First Name']); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <?php echo $this->Form->input('Users.last_name', ['label' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => 'Last Name']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number <span class="text-danger">*</span></label>
                                <?php echo $this->Form->input('Users.phone', ['label' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => 'Contact Number']); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <?php echo $this->Form->input('Users.email_address', ['label' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => 'Email']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Local Church <span class="text-danger">*</span></label>
                                <?php echo $this->Form->input('Users.local_church', ['label' => false, 'class' => 'form-control', 'required' => true, 'placeholder' => 'Local Church']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <?php echo $this->Form->button('<i class="fa fa-save"></i> Save Details', ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller' => 'pastors', 'action' => 'index'], ['class' => 'btn btn-default']); ?>
                    </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </section>
</div>
