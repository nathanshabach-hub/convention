<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>

<!-- Breach Reason Modal -->
<div id="breachReasonModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:8px;width:420px;max-width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);overflow:hidden;">
    <div style="background:#f5f5f5;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd;">
      <strong>Breach Reason</strong>
      <span onclick="document.getElementById('breachReasonModal').style.display='none'" style="cursor:pointer;font-size:1.3em;color:#888;">&times;</span>
    </div>
    <div style="padding:20px 24px;">
      <p id="breachReasonText" style="margin:0;color:#555;font-style:italic;"></p>
    </div>
  </div>
</div>
<script>
function showBreachReason(reason) {
    document.getElementById('breachReasonText').innerText = reason || 'No reason provided.';
    document.getElementById('breachReasonModal').style.display = 'flex';
}
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Guideline Breach</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        echo $this->Paginator->counter(['model' => 'Eventsubmissions', 'format' => '{{page}} of {{pages}} &nbsp;']);
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="guideline_breach" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Event</th>
                            <th class="sorting_paging">Group</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Judge</th>
                            <th class="sorting_paging">Status</th>
                            <th class="sorting_paging">Reason</th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventsubmissions as $datarecord) { ?>
                            <tr>
                                <td data-title="#ID"><?php echo $datarecord->id;?></td>
                                <td data-title="School"><?php echo $datarecord->Uploadeduser['first_name'].' '.$datarecord->Uploadeduser['last_name'];?></td>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Event"><?php echo $datarecord->event_id_number.' '.$datarecord->Events['event_name'];?></td>
                                <td data-title="Group"><?php echo !empty($datarecord->group_name) ? "Group ".$datarecord->group_name : '-'; ?></td>
                                <td data-title="Student"><?php echo ($datarecord->student_id > 0) ? $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'] : '-'; ?></td>
                                <td data-title="Judge"><?php echo $datarecord->Judge['first_name'].' '.$datarecord->Judge['last_name']; ?></td>
                                <td data-title="Status">
                                    <?php if($datarecord->guideline_breach == 1): ?>
                                        <span style="background:#e67e22;color:#fff;padding:3px 10px;border-radius:4px;font-size:12px;">Pending</span>
                                    <?php elseif($datarecord->guideline_breach == 2): ?>
                                        <span style="background:#27ae60;color:#fff;padding:3px 10px;border-radius:4px;font-size:12px;">Approved</span>
                                    <?php endif; ?>
                                </td>
                                <td data-title="Reason">
                                    <?php
                                    $reason = trim((string)($datarecord->guideline_breach_reason ?? ''));
                                    $displayReason = $reason !== '' ? $reason : 'No reason provided.';
                                    ?>
                                    <button type="button"
                                        onclick="showBreachReason('<?php echo addslashes(h($displayReason)); ?>')"
                                        style="background:#27ae60;color:#fff;border:none;border-radius:4px;padding:4px 8px;cursor:pointer;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                                <td data-title="Action">
                                    <?php if($datarecord->guideline_breach == 1): ?>
                                        <?php echo $this->Html->link('<i class="fa fa-check"></i>', ['controller' => 'eventsubmissions', 'action' => 'approveguidelinebreach', $datarecord->slug], ['escape' => false, 'title' => 'Approve', 'class' => 'btn btn-success btn-xs', 'confirm' => 'Are you sure you want to approve this guideline breach?']); ?>
                                        <?php echo ' '.$this->Html->link('<i class="fa fa-times"></i>', ['controller' => 'eventsubmissions', 'action' => 'rejectguidelinebreach', $datarecord->slug], ['escape' => false, 'title' => 'Reject', 'class' => 'btn btn-danger btn-xs', 'confirm' => 'Are you sure you want to reject this guideline breach?']); ?>
                                    <?php elseif($datarecord->guideline_breach == 2): ?>
                                        <?php echo $this->Html->link('<i class="fa fa-trash"></i>', ['controller' => 'eventsubmissions', 'action' => 'deleteguidelinebreach', $datarecord->slug], ['escape' => false, 'title' => 'Delete', 'class' => 'btn btn-danger btn-xs', 'confirm' => 'Are you sure you want to delete this guideline breach?']); ?>
                                    <?php endif; ?>
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
    <div class="admin_no_record">No record found.</div>
<?php } ?>