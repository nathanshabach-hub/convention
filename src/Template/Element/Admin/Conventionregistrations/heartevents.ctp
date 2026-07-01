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
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$heartevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Events of the heart</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
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
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Title</th>
                            <th class="sorting_paging">Document</th>
                            <th class="sorting_paging">Uploaded By</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Uploaded Date'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($heartevents as $datarecord) { ?>
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Student"><?php echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];?></td>
                                <td data-title="Title"><?php echo $datarecord->mediafile_title;?></td>
                                <td data-title="Document"><?php echo $datarecord->mediafile_original_file_name;?></td>
                                <td data-title="Uploaded By"><?php echo $datarecord->Uploadeduser['first_name'].' '.$datarecord->Uploadeduser['last_name'];?></td>
                                <td data-title="Uploaded Date"><?php echo $datarecord->created->format('M d, Y'); ?></td>
                                <td data-title="Action">
                                    <?php
                                    $imgToShow = $datarecord->mediafile_file_system_name;
                                    if(file_exists(UPLOAD_EVENTS_HEART_PATH.$imgToShow) && !empty($imgToShow)) {
                                        echo '<a target="_blank" title="Click to view/download" href="'.DISPLAY_EVENTS_HEART_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
                                    }
                                    echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventionregistrations', 'action' => 'removedocument',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Remove', 'confirm' => 'Are you sure you want to remove this document?']);
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

<?php if (isset($conventionregistrationstudents)) { 
    foreach ($conventionregistrationstudents as $datarecord) { ?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
                    <?php echo $datarecord->Students['first_name'].' '.$datarecord->Students['last_name'];?> [Total Events: <?php echo !empty($datarecord->event_ids) ? count(explode(",",$datarecord->event_ids)) : 0; ?>]
                </legend>
                <div class="drt">
                    <table class="table table-bordered table-striped table-condensed cf">
                    <?php if(!empty($datarecord->event_ids)) { 
                        $eIds = explode(',', $datarecord->event_ids);
                        $eventsL = $this->Events->find()->where(['Events.id IN' => $eIds])->all();
                    ?>
                        <tr><td>Event Number</td><td>Event Name</td></tr>
                        <?php foreach($eventsL as $eventd) { ?>
                        <tr>
                            <td><?php echo $eventd->event_id_number; ?></td>
                            <td><?php echo $eventd->event_name; ?></td>
                        </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr><td>Sorry, no event found.</td></tr>
                    <?php } ?>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
<?php } } ?>