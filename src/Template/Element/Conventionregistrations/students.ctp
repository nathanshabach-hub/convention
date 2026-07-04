<?php
use Cake\ORM\TableRegistry;
$this->Transactionstudents = TableRegistry::get('Transactionstudents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrationstudents->isEmpty()) { ?>

    <div class="panel-body cr-students-panel">
        
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <!--<div class="topn_left">Ads List</div>-->
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php
                        $pagingParams = $this->Paginator->params();
                        if (!empty($pagingParams)) {
                            $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventionregistrations', 'action'=>'students', $separator]));
                            echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                            echo $this->Paginator->prev('« Prev');
                            echo $this->Paginator->numbers();
                            echo $this->Paginator->next('Next »');
                        }
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing cr-students-table-wrap">
                <table class="table table-bordered table-striped table-condensed cf cr-students-table">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Payment Status</th>
                            <th class="sorting_paging">Registration Date</th>
                            <th class="sorting_paging" style="width:24%">Supervisor</th>
                            <th class="action_dvv">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($conventionregistrationstudents as $datarecord)
                        {
                            // to check if payment has been done for this student or not
                            $condStudentPayment = array();
                            $condStudentPayment[] = "(Transactionstudents.conventionregistration_id = '".$datarecord->conventionregistration_id."')";
                            $condStudentPayment[] = "(Transactionstudents.conventionregistrationstudent_id = '".$datarecord->id."')";
                            $condStudentPayment[] = "(Transactionstudents.student_id = '".$datarecord->student_id."')";
                            
                            $checkStudentPaymentStatus = $this->Transactionstudents->find()->where($condStudentPayment)->first();
                        ?>
                            <tr>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Student"><?php echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];?></td>
                                <td data-title="Payment Status">
                                    <?php
                                    $paymentLabel = 'Not yet paid';
                                    $paymentClass = 'is-unpaid';
                                    if($checkStudentPaymentStatus)
                                    {
                                        if($checkStudentPaymentStatus->status == 0)
                                        {
                                            $paymentLabel = 'Failed';
                                            $paymentClass = 'is-failed';
                                        }
                                        else
                                        if($checkStudentPaymentStatus->status == 1)
                                        {
                                            $paymentLabel = 'Confirmed';
                                            $paymentClass = 'is-confirmed';
                                        }
                                        else
                                        if($checkStudentPaymentStatus->status == 2)
                                        {
                                            $paymentLabel = 'Pending';
                                            $paymentClass = 'is-pending';
                                        }
                                        else
                                        if($checkStudentPaymentStatus->status == 3)
                                        {
                                            $paymentLabel = 'Invoiced';
                                            $paymentClass = 'is-invoiced';
                                        }
                                    }
                                    echo '<span class="cr-pay-badge '.$paymentClass.'">'.$paymentLabel.'</span>';
                                    ?>
                                </td>

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

                                <td data-title="Supervisor">
                                    <?php echo $this->Form->select('Conventionregistrationstudents.teacher_parent_id', $teacherDropDownData, ['id' => 'teacher_parent_id_'.$datarecord->id, 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'empty' => 'Choose', 'value' => $datarecord->teacher_parent_id]); ?>
                                    <script>
                                    $(document).ready(function() {
                                        $('#teacher_parent_id_<?php echo $datarecord->id; ?>').select2();
                                    });
                                    </script>
                                    
                                    <script type="text/javascript">
                                        $(document).ready(function () {
                                            $('#teacher_parent_id_<?php echo $datarecord->id; ?>').change(function () {
                                                
                                                var teacher_parent_id = $("#teacher_parent_id_<?php echo $datarecord->id; ?>").val();
                                                
                                                if(teacher_parent_id == 0 || teacher_parent_id == "")
                                                {
                                                    alert("Please choose teacher.");
                                                    return false;
                                                }
                                                
                                                $.ajax({
                                                    type: 'POST',
                                                    url: "<?php echo HTTP_PATH."/homes/assignteachertostudent/".$datarecord->slug; ?>/"+teacher_parent_id,
                                                    cache: false,
                                                    beforeSend: function () {
                                                    },
                                                    complete: function () {
                                                    },
                                                    success: function (result) {
                                                        alert(result);
                                                    }
                                                });
                                                return false;
                                            });
                                        });
                                    </script>
                                </td>
                                
                                <td data-title="Action" class="cr-action-cell">
                                    <?php
                                    if(!$checkStudentPaymentStatus)
                                    {
                                        echo $this->Html->link('Remove Student', ['controller' => 'conventionregistrations', 'action' => 'removestudent',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove', 'class'=>'cr-action-btn cr-action-remove', 'confirm' => 'Are you sure you want to remove this student from this convention registration ?']);
                                    }
                                    else
                                    {
                                        echo $this->Html->link('Result PDF', ['controller' => 'judgeevaluations', 'action' => 'indrespackprint',$datarecord->slug], [ 'escape' => false, 'title' => 'Download Individual Result Package', 'target'=>'_blank', 'class' => 'cr-action-btn cr-action-result']);  
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
    
    <?php 
    if($checkPriceStructure->price_per_student>0)
    {
        echo $this->Html->link('Proceed to Payment/Invoice', ['controller' => 'transactions', 'action' => 'paymentsummary'], ['escape' => false, 'class' => 'btn btn-success cr-students-footer-btn']);
    }
    else
    {
        echo $this->Html->link('Price Structure', ['controller' => 'conventionregistrations', 'action' => 'pricestructure'], ['escape' => false, 'class' => 'btn btn-info cr-students-footer-btn']);
    }
    ?>
    
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No student found.</div>
<?php }
?>