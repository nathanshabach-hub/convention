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
<?php if (!$conventionregistrationstudents->isEmpty()) { ?> 
    <div class="panel-body admin-data-panel admin-cr-students-panel">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section admin-data-section">
            <div class="topn admin-data-topn">
                <div class="topn_left">Students List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        echo $this->Paginator->counter(['model' => 'Conventionregistrationstudents', 'format' => '{{page}} of {{pages}} &nbsp;']);
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
                            <th class="sorting_paging">#</th>
                            <th class="sorting_paging">First Name</th>
                            <th class="sorting_paging">Middle Name</th>
                            <th class="sorting_paging">Last Name</th>
                            <th class="sorting_paging">Birth Year</th>
                            <th class="sorting_paging">Gender</th>
                            <th class="sorting_paging">Supervisor</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Registration Date'); ?></th>
                            <th class="sorting_paging">Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cntrS = 0;
                        foreach ($conventionregistrationstudents as $datarecord)
                        {
                            $cntrS++;
                        ?>
                            <tr>
                                <td data-title="#"><?php echo $cntrS;?></td>
                                <td data-title="First Name"><?php echo $datarecord->Students['first_name'];?></td>
                                <td data-title="Middle Name"><?php echo $datarecord->Students['middle_name'];?></td>
                                <td data-title="Last Name"><?php echo $datarecord->Students['last_name'];?></td>
                                <td data-title="Birth Year"><?php echo $datarecord->Students['birth_year'];?></td>
                                <td data-title="Gender"><?php echo $datarecord->Students['gender'];?></td>
                                <td data-title="Supervisor"><?php echo $datarecord->Teachers['first_name'].' '.$datarecord->Teachers['last_name'];?></td>
                                <td data-title="Registration Date"><?php echo $datarecord->created->format('M d, Y'); ?></td>
                                <td data-title="Events">
                                    <?php if(!empty($datarecord->event_ids)) { ?>
                                        <a href="#info<?php echo $datarecord->id; ?>" rel="facebox" title="View" class="btn btn-info btn-xs eyee"><i class="fa fa-eye "></i></a>
                                    <?php } ?>
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

<?php foreach ($conventionregistrationstudents as $datarecord) { ?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
                    <?php echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];?> [Total Events: <?php echo !empty($datarecord->event_ids) ? count(explode(",",$datarecord->event_ids)) : 0; ?>]
                </legend>
                <div class="drt">
                    <table class="table table-bordered table-striped table-condensed cf">
                        <thead>
                            <tr>
                                <th>Event Number</th>
                                <th>Event Name</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if(!empty($datarecord->event_ids)) { 
                            $eIds = explode(',', $datarecord->event_ids);
                            $eventsL = $this->Events->find()->where(['Events.id IN' => $eIds])->all();
                            foreach($eventsL as $eventd) { ?>
                                <tr>
                                    <td><?php echo $eventd->event_id_number; ?></td>
                                    <td><?php echo $eventd->event_name; ?></td>
                                </tr>
                            <?php } 
                        } else { ?>
                            <tr><td colspan="2">No events found.</td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>