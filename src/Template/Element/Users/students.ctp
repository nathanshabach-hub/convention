<?php
$users = $users ?? [];
$separator = $separator ?? '';
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (count($users) > 0) { ?> 
    <div class="panel-body">
        
            <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
    <section id="no-more-tables" class="lstng-section">
        <div class="topn">
            <!--<div class="topn_left">Ads List</div>-->
            <div class="topn_right ajshort" id="pagingLinks" align="right"></div>
        </div>   

        <div class="tbl-resp-listing">
            <table class="table table-bordered table-striped table-condensed cf">
                <thead class="cf ajshort">
                    <tr>
                        <th class="sorting_paging">First Name</th>
                        <th class="sorting_paging">Middle Name</th>
                        <th class="sorting_paging">Last Name</th>
                        <th class="sorting_paging">Login Code</th>
                        <th class="sorting_paging">Birth Year</th>
                        <th class="sorting_paging">Age</th>
                        <th class="sorting_paging">Gender</th>
                        <th class="sorting_paging">Sign Up Date</th>
                        <th class="sorting_paging">Status</th>
                        <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $datarecord) { ?>
                        <?php //pr($datarecord); exit;?> 
                        <tr>
                            <td data-title="First Name"><?php echo $datarecord->first_name;?></td>
                            <td data-title="Middle Name"><?php echo $datarecord->middle_name ? $datarecord->middle_name : 'N/A'; ?></td>
                            <td data-title="Last Name"><?php echo $datarecord->last_name;?></td>
                            <td data-title="Login Code">
                                <?php
                                if (!empty($datarecord->customer_code)) {
                                    echo $datarecord->customer_code;
                                    echo '<br />';
                                    echo $this->Html->link('Regenerate', ['controller' => 'users', 'action' => 'generatestudentlogincode', $datarecord->slug], [
                                        'class' => 'btn btn-sm btn-warning mt-1',
                                        'onclick' => "return confirm('Regenerate login code for this student?') && confirm('Are you sure you want to regenerate code? The old code will stop working.');"
                                    ]);
                                } else {
                                    echo '<span style="font-style:italic;">not set</span>';
                                    echo '<br />';
                                    echo $this->Html->link('Generate', ['controller' => 'users', 'action' => 'generatestudentlogincode', $datarecord->slug], ['class' => 'btn btn-sm btn-primary mt-1']);
                                }
                                ?>
                            </td>
                            <td data-title="Birth Year"><?php echo $datarecord->birth_year;?></td>
                            <td data-title="Age"><?php echo date("Y")-$datarecord->birth_year;?></td>
                            
                            <td data-title="Gender"><?php echo $datarecord->gender ? $datarecord->gender : 'N/A'; ?></td>
                            <td data-title="Sign Up Date">
                                <?php
                                $createdDisplay = 'N/A';

                                if (!empty($datarecord->created) && $datarecord->created !== '0000-00-00 00:00:00') {
                                    if (is_object($datarecord->created) && method_exists($datarecord->created, 'format')) {
                                        $createdDisplay = $datarecord->created->format('M d, Y');
                                    } else {
                                        $createdTs = strtotime((string)$datarecord->created);
                                        if ($createdTs !== false && $createdTs > 0) {
                                            $createdDisplay = date('M d, Y', $createdTs);
                                        }
                                    }
                                }

                                echo $createdDisplay;
                                ?>
                            </td>
                            <td data-title="Status">
                                <?php
                                if($datarecord->status == 0) 
                                    echo 'Inactive'; 
                                else
                                if($datarecord->status == 1)
                                    echo 'Active';
                                else
                                if($datarecord->status == 2)
                                    echo 'Archive';
                                ?>
                            </td>
                            <td data-title="Action">
                                <?php
                                if($datarecord->status != 2)
                                {
                                    echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'users', 'action' => 'editstudent',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'']);
                                }
                                // to show only to school admin only
                                if($this->request->session()->read("user_id") >0 && ($this->request->session()->read("user_type") == "School"))
                                {
                                    if($datarecord->status == 2)
                                    {
                                        echo $this->Html->link('<i class="fa fa-retweet"></i>', ['controller' => 'users', 'action' => 'restorestudent', $datarecord->slug], [ 'escape' => false, 'title' => 'Restore', 'class' => 't', 'confirm' => 'Are you sure you want to restore this student?']);
                                    }
                                    else
                                    {
                                        echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'users', 'action' => 'archivestudent',$datarecord->slug], [ 'escape' => false, 'title' => 'Archive', 'class'=>'', 'confirm' => 'Are you sure you want to archive this student ?']);
                                    }
                                }
                                ?>
                                <?php //echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'mediafiles', 'action' => 'viewlist',$datarecord->slug], [ 'escape' => false, 'title' => 'Pictures', 'class'=>'']); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php echo $this->Form->end(); ?>

</div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>
