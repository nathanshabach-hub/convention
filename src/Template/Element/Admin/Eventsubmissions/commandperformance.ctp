<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>

<!-- Command Reason Modal -->
<div id="commandReasonModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:8px;width:420px;max-width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);overflow:hidden;">
    <div style="background:#f5f5f5;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #ddd;">
      <strong>Command Performance Reason</strong>
      <span onclick="document.getElementById('commandReasonModal').style.display='none'" style="cursor:pointer;font-size:1.3em;color:#888;">&times;</span>
    </div>
    <div style="padding:20px 24px;">
      <p id="commandReasonText" style="margin:0;color:#555;font-style:italic;"></p>
    </div>
  </div>
</div>
<script>
function showCommandReason(reason) {
    document.getElementById('commandReasonText').innerText = reason || 'No reason provided.';
    document.getElementById('commandReasonModal').style.display = 'flex';
}
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <?php
    $paginationParams = $this->Paginator->params();
    $totalEntries = (int)($paginationParams['count'] ?? count($eventsubmissions));
    ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Command Performance</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <span style="font-weight:600;margin-right:10px;">Total entries: <?php echo number_format($totalEntries); ?></span>
                    <?php 
                        echo $this->Paginator->counter(['model' => 'Eventsubmissions', 'format' => '{{page}} of {{pages}} &nbsp;']);
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="command_performance" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Event</th>
                            <th class="sorting_paging">Group</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Judge</th>
                            <th class="sorting_paging">Mark Date</th>
                            <th class="sorting_paging">Submitted Time</th>
                            <th class="sorting_paging">Reason</th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventsubmissions as $datarecord) { ?>
                            <tr>
                                <td data-title="#ID"><?php echo $datarecord->id;?></td>
                                <td data-title="School">
                                    <?php
                                    $schoolName = trim((string)($datarecord->Users['first_name'] ?? '') . ' ' . (string)($datarecord->Users['last_name'] ?? ''));
                                    if ($schoolName === '') {
                                        $schoolName = trim((string)($datarecord->Uploadeduser['first_name'] ?? '') . ' ' . (string)($datarecord->Uploadeduser['last_name'] ?? ''));
                                    }
                                    echo $schoolName !== '' ? h($schoolName) : '-';
                                    ?>
                                </td>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Event"><?php echo $datarecord->event_id_number.' '.$datarecord->Events['event_name'];?></td>
                                <td data-title="Group"><?php echo !empty($datarecord->group_name) ? "Group ".$datarecord->group_name : '-'; ?></td>
                                <td data-title="Student"><?php echo ($datarecord->student_id > 0) ? $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'] : '-'; ?></td>
                                <td data-title="Judge"><?php echo $datarecord->Judgecommand['first_name'].' '.$datarecord->Judgecommand['last_name']; ?></td>
                                <td data-title="Mark Date"><?php echo $datarecord->modified->format('M d, Y'); ?></td>
                                <?php
                                $submittedTs = 0;
                                $submittedTimeDisplay = '-';
                                if (!empty($datarecord->modified)) {
                                    if (is_object($datarecord->modified) && method_exists($datarecord->modified, 'getTimestamp')) {
                                        $submittedTs = (int)$datarecord->modified->getTimestamp();
                                        $submittedTimeDisplay = $datarecord->modified->format('h:i:s A');
                                    } else {
                                        $submittedTs = strtotime((string)$datarecord->modified);
                                        if ($submittedTs !== false && $submittedTs > 0) {
                                            $submittedTimeDisplay = date('h:i:s A', $submittedTs);
                                        } else {
                                            $submittedTs = 0;
                                        }
                                    }
                                }
                                ?>
                                <td data-title="Submitted Time" data-order="<?php echo $submittedTs; ?>"><?php echo $submittedTimeDisplay; ?></td>
                                <td data-title="Reason">
                                    <?php
                                    $cmdReason = trim((string)($datarecord->command_performance_reason ?? ''));
                                    $displayCmdReason = $cmdReason !== '' ? $cmdReason : 'No reason provided.';
                                    ?>
                                    <button type="button"
                                        onclick="showCommandReason('<?php echo addslashes(h($displayCmdReason)); ?>')"
                                        style="background:#27ae60;color:#fff;border:none;border-radius:4px;padding:4px 8px;cursor:pointer;">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                                <td data-title="Action">
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'eventsubmissions', 'action' => 'removecommandperformance',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove command performance', 'class'=>'btn btn-danger btn-xs', 'confirm' => 'Are you sure you want to remove this command performance?']); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top:10px;display:flex;justify-content:flex-end;align-items:center;">
                <span style="font-weight:600;">Total entries: <?php echo number_format($totalEntries); ?></span>
            </div>
        </section>
        <?php echo $this->Form->end(); ?>
    </div>
<?php } else { ?>
    <div class="admin_no_record">No record found.</div>
<?php } ?>