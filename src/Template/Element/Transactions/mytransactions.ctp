<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$transactions->isEmpty()) { ?> 
    <div class="panel-body">
        
        <form id="actionFrom" method="post" action="<?php echo HTTP_PATH; ?>/transactions/mytransactions">
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <!--<div class="topn_left">Ads List</div>-->
                <div class="topn_right ajshort" id="pagingLinks" align="right"></div>
            </div>   

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Price Structure</th>
                            <th class="sorting_paging">Amount</th>
                            <th class="sorting_paging">Status</th>
                            <th class="sorting_paging">Transaction Date</th>
                            <th class="sorting_paging">Transaction ID</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
								<td data-title="Season Year"><?php echo $datarecord->season_year; ?></td>
								<td data-title="Price Structure"><?php echo $priceStructureCR[$datarecord->price_structure]; ?></td>
								<td data-title="Amount"><?php echo number_format($datarecord->total_amount,2); ?></td>
								<td data-title="Status"><?php echo $paymentStatus[$datarecord->status]; ?></td>
                                <td data-title="Transaction Date"><?php echo date('M d, Y H:i A', strtotime($datarecord->created)); ?></td>
                                <td data-title="Transaction ID"><?php echo $datarecord->transaction_id_received ? $datarecord->transaction_id_received : 'N/A'; ?></td>
								<td data-title="Action">
                                    <?php echo $this->Html->link('<i class="fa fa-eye"></i>', ['controller' => 'transactions', 'action' => 'viewdetails',$datarecord->slug], [ 'escape' => false, 'title' => 'View Details', 'class'=>'']); ?>
                                </td>
								</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

         
        
        </form>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>
