<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$divisions->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Divisions List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        echo $this->Paginator->counter(['model' => 'Divisions', 'format' => '{{page}} of {{pages}} &nbsp;']);
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Event Category</th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('name', 'Division Name'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('max_events', 'Max. Events'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('modified', 'Last Modified'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($divisions as $datarecord) { ?>
                            <?php //print_r($datarecord); exit;?> 
                            <tr>
                                <td data-title="Event Category"><?php echo $datarecord->Eventcategories['name'];?></td>
								<td data-title="Division Name"><?php echo $datarecord->name;?></td>
								<td data-title="Max. Events"><?php echo $datarecord->max_events;?></td>
                                <td data-title="Created">
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
                                <td data-title="Last Modified">
                                <?php
                                $modifiedDisplay = 'N/A';

                                if (!empty($datarecord->modified) && $datarecord->modified !== '0000-00-00 00:00:00') {
                                    if (is_object($datarecord->modified) && method_exists($datarecord->modified, 'format')) {
                                        $modifiedDisplay = $datarecord->modified->format('M d, Y');
                                    } else {
                                        $modifiedTs = strtotime((string)$datarecord->modified);
                                        if ($modifiedTs !== false && $modifiedTs > 0) {
                                            $modifiedDisplay = date('M d, Y', $modifiedTs);
                                        }
                                    }
                                }

                                echo $modifiedDisplay;
                                ?>
								</td>
                                <td data-title="Action">
                                    
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'divisions', 'action' => 'edit',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'divisions', 'action' => 'deletedivision',$datarecord->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm" style="display:none;">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
                //'Delete' => "Delete",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Divisions.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>