<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo HTTP_IMAGE ?>/loading.gif',
            closeImage: '<?php echo HTTP_IMAGE ?>/close.png'
        });
    });
</script>
<style>
    #facebox .content {
        width: min(98vw, 1280px);
        max-width: 1280px;
        padding: 0;
    }

    #facebox .students-by-supervisor-modal .nzwh {
        border: 0;
        margin: 0;
    }

    #facebox .students-by-supervisor-modal .head_pop {
        display: block;
        margin: 0;
        padding: 12px 16px;
        border-bottom: 1px solid #e3e8f0;
        background: #f5f8fc;
        font-size: 16px;
        font-weight: 600;
    }

    #facebox .students-by-supervisor-modal .drt {
        max-height: min(68vh, 640px);
        overflow: auto;
        padding: 10px 12px 12px;
    }

    #facebox .students-by-supervisor-modal table {
        margin: 0;
        width: auto;
        min-width: 1080px;
        table-layout: fixed;
    }

    #facebox .students-by-supervisor-modal table.students-grid col.col-idx { width: 52px; }
    #facebox .students-by-supervisor-modal table.students-grid col.col-first { width: 120px; }
    #facebox .students-by-supervisor-modal table.students-grid col.col-middle { width: 120px; }
    #facebox .students-by-supervisor-modal table.students-grid col.col-last { width: 120px; }
    #facebox .students-by-supervisor-modal table.students-grid col.col-gender { width: 120px; }
    #facebox .students-by-supervisor-modal table.students-grid col.col-birth { width: 120px; }
    #facebox .students-by-supervisor-modal table.students-grid col.col-assign { width: 380px; }

    #facebox .students-by-supervisor-modal th,
    #facebox .students-by-supervisor-modal td {
        white-space: normal;
        word-break: break-word;
        vertical-align: top;
    }

    #facebox .students-by-supervisor-modal th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #edf2fb;
        white-space: nowrap;
        word-break: normal;
        overflow-wrap: normal;
    }

    #facebox .students-by-supervisor-modal th,
    #facebox .students-by-supervisor-modal td {
        text-align: left;
    }

    #facebox .students-by-supervisor-modal .students-grid th:nth-child(1),
    #facebox .students-by-supervisor-modal .students-grid td:nth-child(1) {
        width: 52px !important;
    }

    #facebox .students-by-supervisor-modal .students-grid th:nth-child(2),
    #facebox .students-by-supervisor-modal .students-grid td:nth-child(2),
    #facebox .students-by-supervisor-modal .students-grid th:nth-child(3),
    #facebox .students-by-supervisor-modal .students-grid td:nth-child(3),
    #facebox .students-by-supervisor-modal .students-grid th:nth-child(4),
    #facebox .students-by-supervisor-modal .students-grid td:nth-child(4) {
        width: 72px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    #facebox .students-by-supervisor-modal .students-grid th:nth-child(5),
    #facebox .students-by-supervisor-modal .students-grid td:nth-child(5) {
        width: 120px !important;
    }

    #facebox .students-by-supervisor-modal .students-grid th:nth-child(6),
    #facebox .students-by-supervisor-modal .students-grid td:nth-child(6) {
        width: 120px !important;
    }

    /* Facebox injects inner HTML and drops the outer wrapper class, so target the table directly. */
    #facebox .students-grid th:nth-child(1),
    #facebox .students-grid td:nth-child(1) { width: 52px !important; }

    #facebox .students-grid th:nth-child(2),
    #facebox .students-grid td:nth-child(2),
    #facebox .students-grid th:nth-child(3),
    #facebox .students-grid td:nth-child(3),
    #facebox .students-grid th:nth-child(4),
    #facebox .students-grid td:nth-child(4) {
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    #facebox .students-grid td:nth-child(3),
    #facebox .students-grid td:nth-child(4) {
        text-align: left !important;
    }

    #facebox .students-grid th:nth-child(5),
    #facebox .students-grid td:nth-child(5) { width: 120px !important; }

    #facebox .students-grid th:nth-child(6),
    #facebox .students-grid td:nth-child(6) { width: 120px !important; }

    #facebox .students-grid th:nth-child(7),
    #facebox .students-grid td:nth-child(7) { width: 380px !important; }

    #facebox .students-grid .reassign-wrap {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 6px;
        margin: 0;
        width: 100%;
    }

    #facebox .students-grid .reassign-wrap .form-control {
        min-width: 0;
        max-width: none;
        width: 100%;
        height: 26px;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: 400;
    }

    #facebox .students-grid .reassign-wrap .btn {
        white-space: nowrap;
        line-height: 1.2;
        min-width: 58px;
        height: 26px;
        padding: 2px 8px;
    }

    @media (max-width: 768px) {
        #facebox .content {
            width: 96vw;
        }

        #facebox .students-by-supervisor-modal .head_pop {
            font-size: 14px;
            padding: 10px 12px;
        }

        #facebox .students-by-supervisor-modal .drt {
            padding: 8px;
            max-height: 62vh;
        }

        #facebox .students-by-supervisor-modal table {
            font-size: 12px;
        }
    }
</style>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrationteachers->isEmpty()) { ?> 
    <div class="panel-body admin-data-panel admin-cr-teachers-panel">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section admin-data-section">
            <div class="topn admin-data-topn">
                <div class="topn_left">Supervisors List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        echo $this->Paginator->counter(['model' => 'Conventionregistrationteachers', 'format' => '{{page}} of {{pages}} &nbsp;']);
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
                            <th class="sorting_paging">Title</th>
                            <th class="sorting_paging">First Name</th>
                            <th class="sorting_paging">Last Name</th>
                            <th class="sorting_paging">Email Address</th>
                            <th class="sorting_paging">Gender</th>
                            <th class="sorting_paging">Judge?</th>
                            <th class="sorting_paging">Students</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Registration Date'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cntrT = 0;
                        foreach ($conventionregistrationteachers as $datarecord)
                        {
                            $cntrT++;
                            $teacherMapKey = (int)$datarecord->conventionregistration_id . '_' . (int)$datarecord->teacher_id;
                            $assignedStudents = isset($teacherStudentsMap[$teacherMapKey]) ? $teacherStudentsMap[$teacherMapKey] : [];
                        ?>
                            <tr>
                                <td data-title="#"><?php echo $cntrT;?></td>
                                <td data-title="Title"><?php echo $datarecord->Teachers['title'];?></td>
                                <td data-title="First Name"><?php echo $datarecord->Teachers['first_name'];?></td>
                                <td data-title="Last Name"><?php echo $datarecord->Teachers['last_name'];?></td>
                                <td data-title="Email Address"><?php echo $datarecord->Teachers['email_address'];?></td>
                                <td data-title="Gender"><?php echo $datarecord->Teachers['gender'];?></td>
                                <td data-title="Judge"><?php if($datarecord->Teachers['is_judge'] == 1) echo 'Yes'; else echo 'No'; ?></td>
                                <td data-title="Students">
                                    <?php if (!empty($assignedStudents)) { ?>
                                        <a href="#studentsBySupervisor<?php echo $datarecord->id; ?>" rel="facebox" title="View Assigned Students" class="btn btn-info btn-xs eyee"><i class="fa fa-users"></i></a>
                                    <?php } else { ?>
                                        <span class="btn btn-default btn-xs disabled" title="No students assigned"><i class="fa fa-users"></i></span>
                                    <?php } ?>
                                </td>
                                
                                <td data-title="Registration Date"><?php echo $datarecord->created->format('M d, Y'); ?></td>
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
<?php } ?>

<?php foreach ($conventionregistrationteachers as $datarecord) {
    $teacherMapKey = (int)$datarecord->conventionregistration_id . '_' . (int)$datarecord->teacher_id;
    $assignedStudents = isset($teacherStudentsMap[$teacherMapKey]) ? $teacherStudentsMap[$teacherMapKey] : [];
    if (empty($assignedStudents)) {
        continue;
    }
?>
    <div id="studentsBySupervisor<?php echo $datarecord->id; ?>" class="students-by-supervisor-modal" style="display: none;">
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
                    <?php echo $datarecord->Teachers['first_name'].' '.$datarecord->Teachers['last_name']; ?> [Assigned Students: <?php echo count($assignedStudents); ?>]
                </legend>
                <div class="drt">
                    <table class="table table-bordered table-striped table-condensed cf students-grid">
                        <colgroup>
                            <col class="col-idx">
                            <col class="col-first">
                            <col class="col-middle">
                            <col class="col-last">
                            <col class="col-gender">
                            <col class="col-birth">
                            <col class="col-assign">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>First Name</th>
                                <th>Middle&nbsp;Name</th>
                                <th>Last Name</th>
                                <th>Gender</th>
                                <th>Birth Year</th>
                                <th>Reassign Supervisor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $studentCounter = 0; ?>
                            <?php foreach ($assignedStudents as $studentRecord) { ?>
                                <?php $studentCounter++; ?>
                                <tr>
                                    <td><?php echo $studentCounter; ?></td>
                                    <td><?php echo !empty($studentRecord->Students['first_name']) ? $studentRecord->Students['first_name'] : ''; ?></td>
                                    <td><?php echo !empty($studentRecord->Students['middle_name']) ? $studentRecord->Students['middle_name'] : ''; ?></td>
                                    <td><?php echo !empty($studentRecord->Students['last_name']) ? $studentRecord->Students['last_name'] : ''; ?></td>
                                    <td><?php echo !empty($studentRecord->Students['gender']) ? $studentRecord->Students['gender'] : ''; ?></td>
                                    <td><?php echo !empty($studentRecord->Students['birth_year']) ? $studentRecord->Students['birth_year'] : ''; ?></td>
                                    <td>
                                        <?php
                                            $rowSupervisorOptions = isset($seasonSupervisorOptions) && is_array($seasonSupervisorOptions) ? $seasonSupervisorOptions : [];
                                            $currentTeacherId = (int)$studentRecord->teacher_parent_id;
                                            if ($currentTeacherId > 0 && !isset($rowSupervisorOptions[$currentTeacherId])) {
                                                $rowSupervisorOptions[$currentTeacherId] = 'Current Supervisor #' . $currentTeacherId;
                                            }
                                        ?>
                                        <?php echo $this->Form->create(null, [
                                            'url' => ['controller' => 'conventionregistrations', 'action' => 'reassignstudentsupervisor'],
                                            'class' => 'reassign-wrap'
                                        ]); ?>
                                        <?php echo $this->Form->hidden('student_registration_id', ['value' => $studentRecord->id]); ?>
                                        <?php echo $this->Form->hidden('return_slug', ['value' => isset($slug) ? $slug : '']); ?>
                                        <?php echo $this->Form->select('new_teacher_parent_id', $rowSupervisorOptions, [
                                            'value' => $currentTeacherId,
                                            'label' => false,
                                            'class' => 'form-control'
                                        ]); ?>
                                        <?php echo $this->Form->button('<i class="fa fa-save"></i> Save', [
                                            'type' => 'submit',
                                            'escapeTitle' => false,
                                            'class' => 'btn btn-primary btn-xs'
                                        ]); ?>
                                        <?php echo $this->Form->end(); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>