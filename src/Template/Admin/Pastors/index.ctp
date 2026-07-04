<div class="content-wrapper admin-list-page admin-pastors-page">
    <section class="content-header">
      <h1>
         Pastors
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li class="active"> Pastors </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border admin-pastors-header">
                <div class="admin-pastors-header-copy">
                    <h3 class="box-title">Pastors List</h3>
                    <p class="admin-pastors-header-subtitle">Manage pastor contact details and local church information.</p>
                </div>
                <?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Details', ['controller' => 'pastors', 'action' => 'adddetails'], ['escape' => false, 'class' => 'btn btn-success admin-pastors-add-btn']); ?>
            </div>
            <div class="box-body">
                <?php if (!$pastors->isEmpty()) { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped admin-pastors-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Contact Number</th>
                                    <th>Email</th>
                                    <th>Local Church</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pastors as $pastor) { ?>
                                    <tr>
                                        <td><?php echo h(trim(($pastor->first_name ?? '') . ' ' . ($pastor->last_name ?? '')) ?: 'N/A'); ?></td>
                                        <td><?php echo h($pastor->phone ?: 'N/A'); ?></td>
                                        <td><?php echo h($pastor->email_address ?: 'N/A'); ?></td>
                                        <td><?php echo h($pastor->bill_to_street ?: 'N/A'); ?></td>
                                        <td><?php echo !empty($pastor->created) ? $pastor->created->format('M d, Y') : 'N/A'; ?></td>
                                        <td>
                                            <?php echo $this->Html->link('<i class="fa fa-trash-o"></i> Delete', ['controller' => 'pastors', 'action' => 'delete', $pastor->slug], ['escape' => false, 'class' => 'btn btn-danger btn-xs admin-pastors-delete-btn', 'confirm' => 'Are you sure you want to delete this pastor contact?']); ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert admin-pastors-empty-state" style="margin-bottom:0;">
                        No pastor records found yet.
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>
