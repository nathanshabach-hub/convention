<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Resultpositions = TableRegistry::get('Resultpositions');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>
<style>
@media print {
	.irp-results-wrap {
		margin: 18px 20px !important;
	}

	.irp-results-table {
		margin-bottom: 0 !important;
	}

	.irp-results-table td,
	.irp-results-table th {
		padding: 5px 8px !important;
		font-size: 14px !important;
		line-height: 1.3 !important;
	}

	.irp-results-table .student_cp {
		display: inline;
		font-size: 12px !important;
		line-height: 1.2 !important;
	}
}
</style>

<div class="m-4 irp-results-wrap">
	<div class="table-responsive">
		<table class="table table-bordered table-hover align-middle irp-results-table">
			<tbody>
				<tr>
					<td width="40%"><b>Event</b></td>
					<td width="30%"><b>Place</b></td>
					<td><b>Point</b></td>
				</tr>
				<?php
				foreach ($eventsList as $eventrec) {
					$posVal = '';
					$pointsVal = '';
					$commandPerfText = '';
					$checkGroup = null;
					$studentPosition = null;

					if ($eventrec->group_event_yes_no == 0) {
						$condPOS = [];
						$condPOS[] = "(Resultpositions.conventionregistration_id = '" . $conventionRegD->id . "' )";
						$condPOS[] = "(Resultpositions.event_id = '" . $eventrec->id . "' )";
						$condPOS[] = "(Resultpositions.student_id = '" . $convRegStudentD->student_id . "' )";

						$studentPosition = $this->Resultpositions->find()->where($condPOS)->first();
						if (!empty($studentPosition)) {
							$posVal = $studentPosition->position;
							$pointsVal = $studentPosition->points_obtained;
						}
					} else {
						$condGrp = [];
						$condGrp[] = "(Crstudentevents.conventionregistration_id = '" . $conventionRegD->id . "' )";
						$condGrp[] = "(Crstudentevents.event_id = '" . $eventrec->id . "' )";
						$condGrp[] = "(Crstudentevents.student_id = '" . $convRegStudentD->student_id . "' )";
						$checkGroup = $this->Crstudentevents->find()->where($condGrp)->select(['group_name'])->first();
						if (!empty($checkGroup->group_name)) {
							$condPOS = [];
							$condPOS[] = "(Resultpositions.conventionregistration_id = '" . $conventionRegD->id . "' )";
							$condPOS[] = "(Resultpositions.event_id = '" . $eventrec->id . "' )";
							$condPOS[] = "(Resultpositions.group_name = '" . $checkGroup->group_name . "' )";

							$studentPosition = $this->Resultpositions->find()->where($condPOS)->first();
							if (!empty($studentPosition)) {
								$posVal = $studentPosition->position;
								$pointsVal = $studentPosition->points_obtained;
							}
						}
					}

					$checkStudentCP = false;
					if (!empty($studentPosition) && !empty($studentPosition->eventsubmission_id)) {
						$checkStudentCP = $this->Eventsubmissions->find()->where([
							"Eventsubmissions.id" => $studentPosition->eventsubmission_id,
							"Eventsubmissions.command_performance" => 1,
						])->first();
					}
					if (empty($checkStudentCP) && !empty($eventrec->id) && !empty($convRegStudentD->student_id)) {
						$checkStudentCP = $this->Eventsubmissions->find()->where([
							"Eventsubmissions.command_performance" => 1,
							"Eventsubmissions.event_id" => $eventrec->id,
							"Eventsubmissions.conventionseason_id" => $conventionRegD->conventionseason_id,
							"Eventsubmissions.student_id" => $convRegStudentD->student_id,
						])->first();
					}
					if (empty($checkStudentCP) && !empty($eventrec->id) && !empty($convRegStudentD->user_id)) {
						$checkStudentCP = $this->Eventsubmissions->find()->where([
							"Eventsubmissions.command_performance" => 1,
							"Eventsubmissions.event_id" => $eventrec->id,
							"Eventsubmissions.conventionseason_id" => $conventionRegD->conventionseason_id,
							"Eventsubmissions.user_id" => $convRegStudentD->user_id,
						])->first();
					}
					if (empty($checkStudentCP) && !empty($eventrec->id) && !empty($conventionRegD->id)) {
						$checkStudentCP = $this->Eventsubmissions->find()->where([
							"Eventsubmissions.command_performance" => 1,
							"Eventsubmissions.event_id" => $eventrec->id,
							"Eventsubmissions.conventionseason_id" => $conventionRegD->conventionseason_id,
							"Eventsubmissions.conventionregistration_id" => $conventionRegD->id,
						])->first();
					}
					if ($checkStudentCP) {
						$commandPerfText = ' <span class="student_cp">This is event was nominated for a Command Performance.</span>';
					}
					?>
					<tr>
						<td><?php echo $eventrec->event_name; ?> (<?php echo $eventrec->event_id_number; ?>)
						<?php echo $commandPerfText; ?>
						</td>
						<td>
							<?php
							if ($eventrec->group_event_yes_no == 0) {
								if ($posVal >= 1 && $posVal <= 6) {
									echo $posVal;
								}
							} else {
								echo 'Group: ' . $checkGroup->group_name;
								if ($posVal >= 1 && $posVal <= 6) {
									echo ' Place: ' . $posVal;
								}
							}
							?>
						</td>
						<td><?php if ($posVal >= 1 && $posVal <= 6) { echo $pointsVal; } ?></td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>
</div>
