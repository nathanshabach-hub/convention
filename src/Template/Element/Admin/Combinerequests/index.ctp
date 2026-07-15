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
$this->Events = TableRegistry::get('Events');
$this->Heartevents = TableRegistry::get('Heartevents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$combinerequests->isEmpty()) { ?> 
    <div class="panel-body combinereq-panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn combinereq-topbar">
                <div class="topn_left combinereq-title">Combined Team/Group Events List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        echo $this->Paginator->counter(['model' => 'Combinerequests', 'format' => '{{page}} of {{pages}} &nbsp;']);
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="convention_registraions" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Convention</th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('season_year', 'Season Year'); ?></th>
                            <th class="sorting_paging">Event Number</th>
                            <th class="sorting_paging">Event</th>
                            <th class="sorting_paging">Request By School</th>
                            <th class="sorting_paging">Combine With School</th>
                            <th class="sorting_paging">Student Name</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Request Date'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($combinerequests as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
								<td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Event Number"><?php echo $datarecord->Events['event_id_number'];?></td>
                                <td data-title="Event"><?php echo $datarecord->Events['event_name'];?></td>
                                <td data-title="Request By School"><?php echo $datarecord->Users['first_name'];?></td>
                                <td data-title="Combine With School School"><?php echo $datarecord->Combineduser['first_name'];?></td>
                                <td data-title="Student Name"><?php echo $datarecord->student_name;?></td>
                                <td data-title="Request Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Status">
                                <?php
                                $statusLabel = 'Unknown';
                                $statusClass = 'status-unknown';
                                if ($datarecord->status == 0) {
                                    $statusLabel = 'Declined';
                                    $statusClass = 'status-declined';
                                } elseif ($datarecord->status == 1) {
                                    $statusLabel = 'Approved';
                                    $statusClass = 'status-approved';
                                } elseif ($datarecord->status == 2) {
                                    $statusLabel = 'Pending';
                                    $statusClass = 'status-pending';
                                }
                                echo '<span class="status-pill ' . $statusClass . '">' . h($statusLabel) . '</span>';
                                ?>
                                </td>
								
                                <td data-title="Action">
                                    
                                    <?php
									if($datarecord->status == 2)
									{
                                        echo $this->Html->link('<i class="fa fa-check"></i> Approve', ['controller' => 'combinerequests', 'action' => 'approverequest', $datarecord->slug], [ 'escape' => false, 'title' => 'Approve', 'class' => 'btn btn-success btn-xs action-btn', 'confirm' => 'Are you sure you want to approve this request?']);
										
                                        echo $this->Html->link('<i class="fa fa-times"></i> Decline', ['controller' => 'combinerequests', 'action' => 'declinerequest', $datarecord->slug], [ 'escape' => false, 'title' => 'Decline', 'class' => 'btn btn-default btn-xs action-btn', 'confirm' => 'Are you sure you want to decline this request?']);
                                    } else {
                                        echo '<span class="action-muted">No actions</span>';
									}
									?>
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
            echo $this->Form->input('Conventionregistrations.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>


<?php
foreach ($conventionregistrations as $datarecord)
{
	if(!empty($datarecord->judges_event_ids))
	{
?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
				Events Selected By: <?php echo $datarecord->Users['first_name'].' '.$datarecord->Users['last_name']; ?>
                </legend>
                <div class="drt">
					
					<?php
					$cntrE = 1;
					$condEv = array();
					$condEv[] = "(Events.id IN (".$datarecord->judges_event_ids.") )";
					$judgeEvents = $this->Events->find()->where($condEv)->order(["Events.event_name" =>"ASC"])->all();
					foreach($judgeEvents as $judgeevent)
					{
					?>
					<div class="admin_pop">
                        <span><?php echo $cntrE.'. '; echo $judgeevent->event_name; ?> </span>  <label>
						<?php echo $judgeevent->event_id_number; ?>
						</label>
                    </div>
					<?php
					$cntrE++;
					}
					?>
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php }} ?>


<script>
$(document).ready(function() {
$('#convention_registraions').dataTable({
    "bPaginate": true,
    //"bInfo": false,
    "bLengthChange": false,
	"pageLength": 100,
	order: [[0, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#convention_registraions').dataTable.search(this.value).draw();
    }); */
});
</script>

<!--
<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
-->
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
    .combinereq-panel-body {
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #dfe6f1;
        box-shadow: 0 6px 16px rgba(20, 34, 66, 0.08);
        padding: 14px;
    }

    .combinereq-topbar {
        align-items: center;
        border-bottom: 1px solid #e6ebf5;
        margin-bottom: 12px;
        padding-bottom: 10px;
    }

    .combinereq-title {
        color: #1b315f;
        font-size: 18px;
        font-weight: 700;
    }

    #convention_registraions {
        border-collapse: separate;
        border-spacing: 0;
    }

    #convention_registraions thead th {
        background: #1f3e72;
        color: #ffffff;
        border-color: #1f3e72;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        vertical-align: middle;
    }

    #convention_registraions tbody tr:nth-child(even) {
        background: #f8fbff;
    }

    #convention_registraions tbody td {
        vertical-align: middle;
    }

    .status-pill {
        border-radius: 999px;
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.03em;
        min-width: 78px;
        padding: 4px 10px;
        text-align: center;
        text-transform: uppercase;
    }

    .status-approved {
        background: #e8f7ee;
        color: #1e7c43;
    }

    .status-declined {
        background: #fdecec;
        color: #aa2b2b;
    }

    .status-pending {
        background: #fff5db;
        color: #8a6400;
    }

    .status-unknown {
        background: #eceff3;
        color: #556070;
    }

    .action-btn {
        margin-right: 6px;
        padding: 4px 10px;
    }

    .action-muted {
        color: #8b96a9;
        font-size: 12px;
        font-style: italic;
    }

    #convention_registraions_filter {
        margin-bottom: 10px;
    }

    #convention_registraions_filter input {
        border: 1px solid #c9d5e7;
        border-radius: 4px;
        box-shadow: none;
        margin-left: 6px;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #1f3e72 !important;
        border: 1px solid #1f3e72 !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #edf3ff !important;
        border: 1px solid #d7e3fb !important;
        color: #1f3e72 !important;
    }

    .page-link {
        color: #1c2452 !important;
        background-color: #fff !important;
    }

    .active>.page-link,
    .page-link.active {
        background-color: #1c2452 !important;
        border-color: #1c2452 !important;
        color: #fff !important;
    }

    .pagination {
        border-radius: 0rem !important;
    }
</style>