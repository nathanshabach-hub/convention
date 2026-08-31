<style>
@media print {
	.judge-eval-wrap {
		margin: 8px 12px !important;
	}

	.judge-eval-table {
		margin-bottom: 0 !important;
	}

	.judge-eval-table td,
	.judge-eval-table th {
		padding: 4px 7px !important;
		font-size: 13px !important;
		line-height: 1.3 !important;
	}

	.judge-eval-comments {
		font-size: 12px !important;
		line-height: 1.25 !important;
	}

	.judge-eval-event-header {
		margin-bottom: 12px !important;
	}

	.judge-eval-record {
		margin-bottom: 18px !important;
		break-inside: avoid-page;
		page-break-inside: avoid;
	}
}

.judge-eval-event-header {
	display: flex;
	justify-content: space-between;
	gap: 24px;
	border-bottom: 2px solid #d58aa5;
	padding-bottom: 8px;
	margin-bottom: 16px;
}

.judge-eval-event-header h2,
.judge-eval-event-header p {
	margin: 0;
}

.judge-eval-event-header h2 {
	font-size: 20px;
}

.judge-eval-school {
	text-align: right;
}

.judge-eval-judge {
	font-size: 14px;
	margin: 0 0 6px;
}
</style>

<div class="m-4 judge-eval-wrap">
	
	
	<!--<div class="page-break spacer-after-break"></br></div>-->
	
	<?php
	foreach($eventsList as $eventrec)
	{
		// To fetch evaluation filled by judge
		$condJeval = array();
		$condJeval[] = "(Judgeevaluations.conventionregistration_id = '".$conventionRegD->id."' )";
		$condJeval[] = "(Judgeevaluations.event_id = '".$eventrec->id."' )";
		
		if($eventrec->group_event_yes_no == 0)
		{
			$condJeval[] = "(Judgeevaluations.student_id = '".$convRegStudentD->student_id."' )";
		}
		else
		{
			$condGrp = array();
			$condGrp[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."' )";
			$condGrp[] = "(Crstudentevents.event_id = '".$eventrec->id."' )";
			$condGrp[] = "(Crstudentevents.student_id = '".$convRegStudentD->student_id."' )";
			$checkGroup = $this->Crstudentevents->find()->where($condGrp)->select(['group_name'])->first();
			
			if (!empty($checkGroup->group_name)) {
				$condJeval[] = "(Judgeevaluations.group_name = '".$checkGroup->group_name."' )";
			}
		}
		
		$judgeEvaluations = $this->Judgeevaluations->find()
			->where($condJeval)
			->contain(['Judgeevaluationmarks', 'Judge'])
			->order(['Judgeevaluations.id' => 'ASC'])
			->all();
			
		if(!$judgeEvaluations->isEmpty())
		{
	?>
	<div class="judge-evaluation-page">
		<div class="judge-eval-event-header">
			<div>
				<h2><?php echo h($eventrec->event_name); ?></h2>
				<p><?php echo h(trim($convRegStudentD->Students['first_name'].' '.$convRegStudentD->Students['middle_name'].' '.$convRegStudentD->Students['last_name'])); ?></p>
			</div>
			<p class="judge-eval-school"><?php echo h($convRegStudentD->Users['first_name']); ?></p>
		</div>
		<?php foreach($judgeEvaluations as $judgeEvaluationD) { ?>
		<div class="judge-eval-record">
			<?php
			$judgeName = trim(
				(string)($judgeEvaluationD->Judge['first_name'] ?? '').' '.
				(string)($judgeEvaluationD->Judge['last_name'] ?? '')
			);
			?>
			<p class="judge-eval-judge"><b>Judge:</b> <?php echo h($judgeName !== '' ? $judgeName : 'N/A'); ?></p>
		<?php
		if($eventrec->event_judging_type == 'general')
		{
			$judgeEvaluationMarks = array();

			if (!empty($judgeEvaluationD->Judgeevaluationmarks)) {
				$judgeEvaluationMarks = $judgeEvaluationD->Judgeevaluationmarks;
			} elseif (!empty($judgeEvaluationD->judgeevaluationmarks)) {
				$judgeEvaluationMarks = $judgeEvaluationD->judgeevaluationmarks;
			}

			if (empty($judgeEvaluationMarks)) {
				$judgeEvaluationMarks = $this->Judgeevaluationmarks->find()
					->where(['Judgeevaluationmarks.judgeevaluation_id' => $judgeEvaluationD->id])
					->order(['Judgeevaluationmarks.id' => 'ASC'])
					->all();
			}
		?>
		<div class="table-responsive">
			<table class="table table-bordered table-hover align-middle judge-eval-table">
				<tbody>
					<tr>
						<td colspan="4"><b>
						<?php
						if($eventrec->group_event_yes_no == 0)
						{
							echo 'Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')';
						}
						else
						{
							echo 'Group '.$checkGroup->group_name.' [Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')]';
						}
						?>
						</b></td>
					</tr>
					<tr>
						<td colspan="4" class="judge-eval-comments"><b>Comments</b>: <?php echo $judgeEvaluationD->comments ? $judgeEvaluationD->comments : 'N/A'; ?></td>
					</tr>
					<tr>
						<td>#</td>
						<td>Question</td>
						<td>Max Possible Marks</td>
						<td>Marks Obtained</td>
					</tr>
					<?php
					$cntrQ = 1;
					foreach($judgeEvaluationMarks as $judgevalmark)
					{
						$questionD = $this->Evaluationquestions->find()->where(["Evaluationquestions.id" => $judgevalmark->question_id])->first();
					?>

					<tr>
						<td><?php echo $cntrQ; ?></td>
						<td><?php echo $questionD ? $questionD->question : ''; ?></td>
						<td><?php echo $judgevalmark->question_marks_possible; ?></td>
						<td><?php echo $judgevalmark->question_marks_obtained; ?></td>
					</tr>
					<?php
					$cntrQ++;
					}
					?>

					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><?php echo $judgeEvaluationD->total_marks_possible; ?></td>
						<td><?php echo $judgeEvaluationD->total_marks_obtained; ?></td>
					</tr>

				</tbody>
			</table>
		</div>
		<?php
		}
		else if($eventrec->event_judging_type == 'times')
		{
			$formattedTime = '';
			if (!empty($judgeEvaluationD->time_score)) {
				$formattedTime = is_object($judgeEvaluationD->time_score) && method_exists($judgeEvaluationD->time_score, 'format')
					? $judgeEvaluationD->time_score->format('H:i:s.u')
					: (string)$judgeEvaluationD->time_score;
				$formattedTime = rtrim(rtrim($formattedTime, '0'), '.');
			}
			$status = (int)$judgeEvaluationD->did_not_attend === 1
				? 'Did not attend'
				: ((int)$judgeEvaluationD->withdraw_yes_no === 1 ? 'Withdrawn' : 'Completed');
		?>
		<div class="table-responsive">
			<table class="table table-bordered table-hover align-middle judge-eval-table">
				<tr>
					<td colspan="2"><b>Event: <?php echo h($eventrec->event_name.' ('.$eventrec->event_id_number.')'); ?></b></td>
				</tr>
				<tr>
					<td><b>Time</b></td>
					<td><?php echo h($formattedTime !== '' ? $formattedTime : 'N/A'); ?></td>
				</tr>
				<tr>
					<td><b>Status</b></td>
					<td><?php echo h($status); ?></td>
				</tr>
			</table>
		</div>
		<?php
		}
		else if($eventrec->event_judging_type == 'distances')
		{
		?>
		<div class="table-responsive">
			<table class="table table-bordered table-hover align-middle judge-eval-table">
				<tr>
					<td colspan="4"><b>
					<?php
					if($eventrec->group_event_yes_no == 0)
					{
						echo 'Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')';
					}
					else
					{
						echo 'Group '.$checkGroup->group_name.' [Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')]';
					}

					?>
					</b></td>
				</tr>
				<tr>
					<td>1st Attempt</td>
					<td>2nd Attempt</td>
					<td>3rd Attempt</td>
					<td><b>Best Score</b></td>
				</tr>
				<tr>
					<td><?php echo $judgeEvaluationD->distance_attempt_1; ?></td>
					<td><?php echo $judgeEvaluationD->distance_attempt_2; ?></td>
					<td><?php echo $judgeEvaluationD->distance_attempt_3; ?></td>
					<td><b><?php echo $judgeEvaluationD->distance_score; ?></b></td>
				</tr>
			</table>
		</div>
		<?php
		}
		else if($eventrec->event_judging_type == 'scores')
		{
		?>
		<div class="table-responsive">
			<table class="table table-bordered table-hover align-middle judge-eval-table">
				<tr>
					<td colspan="3"><b>
					<?php
					if($eventrec->group_event_yes_no == 0)
					{
						echo 'Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')';
					}
					else
					{
						echo 'Group '.$checkGroup->group_name.' [Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')]';
					}

					?>
					</b></td>
				</tr>


				<tr>
					<td>Position</td>
					<td>Status</td>
					<td>Score</td>
				</tr>
				<?php
				for($cntrP=1;$cntrP<=9;$cntrP++)
				{
					$propYN = 'pos_'.$cntrP.'_yes_no';
					$propC 	= 'pos_'.$cntrP.'_score';
				?>
				<tr>
					<td><?php echo $cntrP; ?></td>
					<td><?php echo $judgeEvaluationD->$propYN ? "Yes" : "No"; ?></td>
					<td><?php echo $judgeEvaluationD->$propC ? $judgeEvaluationD->$propC : ""; ?></td>
				</tr>
				<?php
				}
				?>

				<tr>
					<td colspan="3">Competitors Choice</td>
				</tr>
				<tr>
					<td>X1: <?php echo $judgeEvaluationD->comp_choice_pos_1; ?></td>
					<td><?php echo $judgeEvaluationD->comp_choice_pos_1 ? "Yes" : "No"; ?></td>
					<td><?php echo $judgeEvaluationD->comp_choice_pos_1_score; ?></td>
				</tr>
				<tr>
					<td>X2: <?php echo $judgeEvaluationD->comp_choice_pos_2; ?></td>
					<td><?php echo $judgeEvaluationD->comp_choice_pos_2 ? "Yes" : "No"; ?></td>
					<td><?php echo $judgeEvaluationD->comp_choice_pos_2_score; ?></td>
				</tr>
				<tr>
					<td>X3: <?php echo $judgeEvaluationD->comp_choice_pos_3; ?></td>
					<td><?php echo $judgeEvaluationD->comp_choice_pos_3 ? "Yes" : "No"; ?></td>
					<td><?php echo $judgeEvaluationD->comp_choice_pos_3_score; ?></td>
				</tr>
				<tr>
					<td colspan="3">Total Score: <?php echo $judgeEvaluationD->all_pos_score; ?></td>
				</tr>


			</table>
		</div>
		<?php
		}
		else if($eventrec->event_judging_type == 'soccer_kick')
		{
			$all_kicks = json_decode($judgeEvaluationD->soccer_kick_all_kicks);
		?>
		<div class="table-responsive">
			<table class="table table-bordered table-hover align-middle judge-eval-table">
				<tr>
					<td colspan="4"><b>
					<?php
					if($eventrec->group_event_yes_no == 0)
					{
						echo 'Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')';
					}
					else
					{
						echo 'Group '.$checkGroup->group_name.' [Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')]';
					}

					?>
					</b></td>
				</tr>

				<tr>
					<td>Best Score</td>
					<td><?php echo $judgeEvaluationD->soccer_kick_best_kick; ?>m</td>
					<td></td>
					<td></td>
				</tr>
				<?php
				for($cntrKD=10;$cntrKD<=50;$cntrKD+=5)
				{
				?>
					<tr>
						<td><?php echo $cntrKD; ?>m</td>
						<?php
						for($cntrAtt=1;$cntrAtt<=3;$cntrAtt++)
						{
						?>
						<td>Attempt 1:
						<?php
						if(in_array($cntrKD.'_'.$cntrAtt,$all_kicks))
						{
							echo '<b>Yes</b>';
						}
						else
						{
							echo 'No';
						}
						?>
						</td>
						<?php
						}
						?>
					</tr>
				<?php
				}
				?>

			</table>
		</div>
		<?php
		}
		else if($eventrec->event_judging_type == 'spellings')
		{
		?>
		<div class="table-responsive">
			<table class="table table-bordered table-hover align-middle judge-eval-table">
				<tr>
					<td colspan="2"><b>
					<?php
					if($eventrec->group_event_yes_no == 0)
					{
						echo 'Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')';
					}
					else
					{
						echo 'Group '.$checkGroup->group_name.' [Event: '.$eventrec->event_name.' ('.$eventrec->event_id_number.')]';
					}

					?>
					</b></td>
				</tr>
				<tr>
					<td>Score</td>
					<td><?php echo $judgeEvaluationD->spelling_score; ?></td>
				</tr>
			</table>
		</div>
		<?php
		}
		?>
		</div>
		<?php } ?>
	</div>
	<?php
		}
	?>
	
	<?php
	} // end for loop
	?>
	
</div>





