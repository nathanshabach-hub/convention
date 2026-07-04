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
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <div class="panel-body admin-data-panel admin-esub-panel">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php $convRegSlug = isset($slug) ? $slug : null; ?>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section admin-data-section">
            <div class="topn admin-data-topn">
                <div class="topn_left">Event submissions</div>
            </div>   

            <div class="tbl-resp-listing admin-data-table-wrap">
                <table id="event_submissions" class="table table-bordered table-striped table-condensed cf admin-data-table">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('event_id_number', 'Event Number'); ?></th>
                            <th class="sorting_paging">Event Name</th>
                            <th class="sorting_paging">Group Event?</th>
                            <th class="sorting_paging">Group</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Submitted By</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Submitted Date'); ?></th>
                            <th class="sorting_paging">File</th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventsubmissions as $datarecord) { ?>
                            <tr>
                                <td data-title="ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Convention"><?php echo isset($datarecord->Conventions['name']) ? $datarecord->Conventions['name'] : '-';?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
                                <td data-title="Event Name"><?php echo isset($datarecord->Events['event_name']) ? $datarecord->Events['event_name'] : '-';?></td>
                                <td data-title="Group Event?"><?php echo (isset($datarecord->Events['group_event_yes_no']) && $datarecord->Events['group_event_yes_no'] == 1) ? "Yes" : "No"; ?></td>
                                <td data-title="Submitted For Group">
                                    <?php echo !empty($datarecord->group_name) ? "Group ".$datarecord->group_name : '-'; ?>
                                </td>
                                <td data-title="Submitted For Student">
                                    <?php
                                        if ($datarecord->student_id > 0 && !empty($datarecord->Students)) {
                                            $studentName = trim(
                                                (isset($datarecord->Students['first_name']) ? $datarecord->Students['first_name'] : '') . ' ' .
                                                (isset($datarecord->Students['middle_name']) ? $datarecord->Students['middle_name'] : '') . ' ' .
                                                (isset($datarecord->Students['last_name']) ? $datarecord->Students['last_name'] : '')
                                            );
                                            echo !empty($studentName) ? $studentName : '-';
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td data-title="Submitted By"><?php echo trim((isset($datarecord->Uploadeduser['first_name']) ? $datarecord->Uploadeduser['first_name'] : '') . ' ' . (isset($datarecord->Uploadeduser['last_name']) ? $datarecord->Uploadeduser['last_name'] : ''));?></td>
                                <td data-title="Submitted Date"><?php echo $datarecord->created->format('M d, Y'); ?></td>
                                <td data-title="File">
                                <?php
                                    $files = [
                                        $datarecord->mediafile_file_system_name => (isset($datarecord->Events['upload_type']) ? $datarecord->Events['upload_type'] : 'Upload'),
                                        $datarecord->report => "Report",
                                        $datarecord->score_sheet => "Score Sheet",
                                        $datarecord->additional_documents => "Additional Documents"
                                    ];
                                    foreach ($files as $file => $title) {
                                        if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$file) && !empty($file)) {
                                            echo '<a class="btn btn-info btn-xs" target="_blank" title="'.$title.'" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$file.'"><i class="fa fa-cloud-download"></i></a> ';
                                        }
                                    }
                                ?>
                                </td>
                                <td data-title="Action">
                                    <?php
                                    $checkJudgeEval = $this->Judgeevaluations->find()->where(['Judgeevaluations.eventsubmission_id' => $datarecord->id,'Judgeevaluations.conventionregistration_id' => $datarecord->conventionregistration_id])->first();
                                    if($checkJudgeEval) {
                                        echo '<i title="Evaluation submitted by judge" class="fa fa-gavel"></i>';
                                    } else {
                                        if (!empty($convRegSlug)) {
                                            echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'eventsubmissions', 'action' => 'removesubmission', $datarecord->slug, $convRegSlug], [ 'escape' => false, 'title' => 'Remove', 'class'=>'btn btn-danger btn-xs', 'confirm' => 'Are you sure you want to remove this submission?']);
                                        } else {
                                            echo '<span class="btn btn-default btn-xs disabled" title="Remove unavailable"><i class="fa fa-trash-o"></i></span>';
                                        }
                                    }
                                    ?>
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

<script>
$(document).ready(function() {
    $('#event_submissions').dataTable({
        "bPaginate": true,
        "bLengthChange": false,
        "pageLength": 100,
        order: [[0, 'asc']]
    });
});
</script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style type="text/css">
    .page-link { color: #1c2452 !important; background-color: #fff !important; }
    .active>.page-link, .page-link.active { background-color: #1c2452 !important; border-color: #1c2452 !important; color: #fff !important; }
    .pagination { border-radius: 0rem !important; }
</style>