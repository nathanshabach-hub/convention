<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo HTTP_IMAGE ?>/loading.gif',
            closeImage: '<?php echo HTTP_IMAGE ?>/close.png'
        })
    })            
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Categories = TableRegistry::get('Categories');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif'); ?></div>
<?php if (!$users->isEmpty()) { ?> 
    <div class="panel-body admin-data-panel">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
    <?php echo $this->Form->create(null, ['id' => 'actionFrom', "method" => "Post"]); ?>
        <section id="no-more-tables" class="lstng-section admin-data-section">
            <div class="topn admin-data-topn">
                <div class="topn_left">Schools List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php
                    echo $this->Paginator->counter(['model' => 'Users']);
                    echo $this->Paginator->prev('« Prev');
                    echo $this->Paginator->numbers();
                    echo $this->Paginator->next('Next »');
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing admin-data-table-wrap">
                <table class="table table-bordered table-striped table-condensed cf admin-data-table">
                    <thead class="cf ajshort">
                        <tr>
                            <th style="width:5%">#</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('id', '#ID'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('customer_code', 'Customer Code'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('first_name', 'School/HSSP Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('middle_name', 'Main Contact Person'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('email_address', 'Email'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('phone', 'Telephone 1'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Sign Up Date'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('activation_status', 'Verified'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) { ?>
                            <tr>
                                <td data-title=""><input type="checkbox" onclick="javascript:isAllSelect(this.form);" name="chkRecordId[]" value="<?php echo $user->id; ?>" /></td>
                                <td data-title="ID"><?php echo $user->id; ?></td>
                                <td data-title="Customer Code"><?php echo $user->customer_code; ?></td>
                                <td data-title="School/HSSP Name"><?php echo $user->first_name; ?></td>
                                <td data-title="Main Contact Person"><?php echo $user->middle_name ? $user->middle_name : 'N/A'; ?></td>
                                <td data-title="Email Address"><?php echo $user->email_address; ?></td>
                                <td data-title="Telephone 1"><?php echo $user->phone; ?></td>
                                
                                <td data-title="Created"><?php echo $user->created->format('M d, Y'); ?></td>
                                <td data-title="Verified">
                                    <?php
                                    if($user->status != 2)
                                    {
                                        if($user->activation_status)  echo '<span class="judge-status-pill judge-status-active">Verified</span>'; else  echo '<span class="judge-status-pill judge-status-inactive">Not verified</span>';
                                    }
                                    ?>
                                </td>
                                
                                <td data-title="Status">
                                    <?php
                                    if($user->status == 0) 
                                        echo '<span class="judge-status-pill judge-status-inactive">Inactive</span>'; 
                                    else if($user->status == 1)
                                        echo '<span class="judge-status-pill judge-status-active">Active</span>';
                                    else if($user->status == 2)
                                        echo '<span class="judge-status-pill judge-status-archived">Archive</span>';
                                    ?>
                                </td>
                                
                                <td data-title="Action">
                                    <div class="admin-data-actions">
                                    <?php
                                    if($user->status == 2)
                                    {
                                        echo $this->Html->link('<i class="fa fa-retweet"></i>', ['controller' => 'users', 'action' => 'restoreuser', $user->slug], [ 'escape' => false, 'title' => 'Restore', 'class' => 'btn btn-danger btn-xs action-list delete-list admin-data-action-btn', 'confirm' => 'Are you sure you want to restore this school?']);
                                    }
                                    else
                                    {
                                    if ($user->activation_status) {
                                        echo $this->Html->link('<i class="fa fa-unlock"></i>', ['controller' => 'users', 'action' => 'unverifyaccount', $user->slug, 'index'], ['escape' => false, 'title' => 'Mark as unverified', 'class' => 'btn btn-warning btn-xs admin-data-action-btn', 'confirm' => 'Mark this account as unverified?']);
                                    } else {
                                        echo $this->Html->link('<i class="fa fa-check-circle"></i>', ['controller' => 'users', 'action' => 'verifyaccount', $user->slug, 'index'], ['escape' => false, 'title' => 'Mark as verified', 'class' => 'btn btn-success btn-xs admin-data-action-btn', 'confirm' => 'Mark this account as verified?']);
                                    }
                                    ?>
                                    <div id="loderstatus<?php echo $user->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <span class="right_acdc" id="status<?php echo $user->id; ?>">
                                        <?php
                                        if ($user->status == '1') {
                                            echo $this->Html->link('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>', ['controller' => 'users', 'action' => 'deactivateuser', $user->slug], [ 'escape' => false, 'title' => 'Deactivate','class'=>'deactivate admin-data-action-btn']);
                                        } else {
                                            echo $this->Html->link('<button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button>', ['controller' => 'users', 'action' => 'activateuser', $user->slug], ['class' => "activate admin-data-action-btn", 'escape' => false, 'title' => 'Activate']);
                                        }
                                        ?>
                                    </span>

                                    <?php
                                    echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'users', 'action' => 'edit', $user->slug], [ 'escape' => false, 'title' => 'Edit', 'class' => 'btn btn-primary btn-xs admin-data-action-btn']);
                                    
                                    echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'users', 'action' => 'archiveuser', $user->slug], [ 'escape' => false, 'title' => 'Archive', 'class' => 'btn btn-danger btn-xs action-list delete-list admin-data-action-btn', 'confirm' => 'Are you sure you want to archive this school?']);
                                    ?>
                                    <?php
                                    }
                                    ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type' => 'select', 'label' => false, 'class' => "small form-control", 'id' => 'action']); ?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Users.keyword', ['label' => false, 'type' => 'hidden', 'value' => $keyword]);
        }
        ?>
    <?php echo $this->Form->end(); ?>
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php } ?>