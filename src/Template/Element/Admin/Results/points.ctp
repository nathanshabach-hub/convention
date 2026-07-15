<?php
use Cake\ORM\TableRegistry;
$this->Divisions = TableRegistry::get('Divisions');
$this->Users = TableRegistry::get('Users');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php //if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

        <?php if (!empty($trophyWinners)): ?>
        <section class="lstng-section" style="margin-bottom:30px;">
            <div class="topn"><div class="topn_left">🏆 Divisional Trophy &amp; Certificate Eligibility</div></div>
            <p style="font-size:12px;color:#555;margin:6px 0 12px;">
                Requirements: ≥24 points in division · must have a solo/individual entry · highest score wins.<br>
                Athletics, Sports, Music Vocal &amp; Platform award separate Male/Female trophies.
                Students who only entered group Tambourine events in Instrumental are ineligible.
            </p>
            <div class="tbl-resp-listing">
                <table class="table table-bordered table-condensed" style="font-size:13px;">
                    <thead style="background:#1c2452;color:#fff;">
                        <tr>
                            <th>Division</th>
                            <th>Category</th>
                            <th>Trophy Winner(s)</th>
                            <th>Points</th>
                            <th>School</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($trophyWinners as $divId => $winnerData):
                        $divisionD = $this->Divisions->find()->where(['Divisions.id' => $divId])->first();
                        $divName   = $divisionD ? $divisionD->name : 'Division '.$divId;
                        $isGenderSplit = in_array($divId, $genderSplitDivs);

                        if ($isGenderSplit):
                            foreach (['Male','Female'] as $gender):
                                if (empty($winnerData[$gender])) continue;
                                $sids  = $winnerData[$gender]['students'];
                                $pts   = $winnerData[$gender]['points'];
                                foreach ($sids as $idx => $sid):
                                    $studentD = $this->Users->find()->where(['Users.id' => $sid])->contain(['Schools'])->first();
                                    $name = $studentD ? trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name) : '#'.$sid;
                                    $school = $studentD && $studentD->Schools ? $studentD->Schools['first_name'] : '-';
                                    $isTied = count($sids) > 1;
                    ?>
                        <tr style="background:<?php echo $gender==='Male'?'#eef3fb':'#fdeef8'; ?>">
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?php echo count($sids); ?>" style="font-weight:700;vertical-align:middle;"><?php echo h($divName); ?></td>
                            <td rowspan="<?php echo count($sids); ?>" style="vertical-align:middle;">
                                <span style="background:<?php echo $gender==='Male'?'#1c2452':'#a4186b'; ?>;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;"><?php echo $gender; ?></span>
                            </td>
                            <?php endif; ?>
                            <td><?php echo h($name); ?><?php if($isTied): ?> <span style="color:#e67e22;font-size:10px;">★ Tied</span><?php endif; ?></td>
                            <td style="font-weight:700;color:#1c2452;"><?php echo $pts; ?></td>
                            <td><?php echo h($school); ?></td>
                        </tr>
                    <?php
                                endforeach;
                            endforeach;
                        else:
                            $sids  = $winnerData['students'];
                            $pts   = $winnerData['points'];
                            foreach ($sids as $idx => $sid):
                                $studentD = $this->Users->find()->where(['Users.id' => $sid])->contain(['Schools'])->first();
                                $name = $studentD ? trim($studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name) : '#'.$sid;
                                $school = $studentD && $studentD->Schools ? $studentD->Schools['first_name'] : '-';
                                $isTied = count($sids) > 1;
                    ?>
                        <tr style="background:#f0f7f0;">
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?php echo count($sids); ?>" style="font-weight:700;vertical-align:middle;"><?php echo h($divName); ?></td>
                            <td rowspan="<?php echo count($sids); ?>" style="vertical-align:middle;">
                                <span style="background:#27ae60;color:#fff;padding:2px 8px;border-radius:3px;font-size:11px;">Overall</span>
                            </td>
                            <?php endif; ?>
                            <td><?php echo h($name); ?><?php if($isTied): ?> <span style="color:#e67e22;font-size:10px;">★ Tied</span><?php endif; ?></td>
                            <td style="font-weight:700;color:#1c2452;"><?php echo $pts; ?></td>
                            <td><?php echo h($school); ?></td>
                        </tr>
                    <?php
                            endforeach;
                        endif;
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>
        <?php echo $this->Form->create(NULL, ['id' => 'addresults', 'type' => 'file', 'class' => ' ']); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left"> View Points By Divisions
				</div>  
            </div>   

            <div class="tbl-resp-listing">
                <table id="results_table" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Division</th>
							<th class="sorting_paging">Points</th>
							<th class="sorting_paging">School</th>
                            <th class="sorting_paging">Student Name</th>
                            <th class="sorting_paging">Gender</th>
							<th class="sorting_paging">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach($arrAllResults as $division_id => $student_ids)
						{
							$divisionD = $this->Divisions->find()->where(["Divisions.id" => $division_id])->first();
							
							foreach($student_ids as $student_id => $pointsS)
							{
								$studentD = $this->Users->find()->where(["Users.id" => $student_id])->contain(['Schools'])->first();
							
						?> 
                            <tr>
                                <td data-title="Division"><?php echo $divisionD->name;?></td>
								<td data-title="Points"><?php echo $pointsS;?></td>
                                <td data-title="School"><?php echo $studentD->Schools['first_name'];?></td>
                                <td data-title="Student Name"><?php echo $studentD->first_name;?> <?php echo $studentD->middle_name;?> <?php echo $studentD->last_name;?> (#<?php echo $student_id;?>)</td>
                                <td data-title="Gender"><?php echo $studentD->gender;?></td>
								<td data-title="Division Winner Certificate">
									<?php
									echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>', ['controller' => 'results', 'action' => 'divisionwinnercertificatepdf',$slug_convention_season,$divisionD->slug,$studentD->slug], [ 'escape' => false, 'title' => 'Generate Division Winner Certificate', 'target'=>'_blank']);
									?>
								</td>
                            </tr>
                        <?php
							}
						}
						?>
						
							
                    </tbody>
                </table>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php //} else { ?> 
<?php //}
?>

<script>
$(document).ready(function() {
$('#results_table').dataTable({
    "bPaginate": true,
    "bInfo": false,
    "bLengthChange": false,
	"pageLength": 100,
	order: [[0, 'asc'],[1, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#results_table').dataTable.search(this.value).draw();
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