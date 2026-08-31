<script type="text/javascript">
	$(document).ready(function () {
		$("#judgeregister").validate();
		var jrPageSize = 20;
		var jrCurrentPage = 1;

		function jrGetFilteredRows() {
			var query = $.trim($('#jrEventSearch').val()).toLowerCase();
			return $('.jr-table tbody tr').filter(function () {
				var code = $.trim($(this).find('td:eq(1)').text()).toLowerCase();
				var eventName = $.trim($(this).find('td:eq(2)').text()).toLowerCase();
				return query === '' || code.indexOf(query) !== -1 || eventName.indexOf(query) !== -1;
			});
		}

		function jrRenderRows() {
			var filteredRows = jrGetFilteredRows();
			var totalRows = filteredRows.length;
			var totalPages = Math.max(1, Math.ceil(totalRows / jrPageSize));

			if (jrCurrentPage > totalPages) {
				jrCurrentPage = totalPages;
			}

			var startIndex = (jrCurrentPage - 1) * jrPageSize;
			var endIndex = startIndex + jrPageSize;

			$('.jr-table tbody tr').hide();
			filteredRows.slice(startIndex, endIndex).show();

			$('#jrNoResults').toggle(totalRows === 0);
			$('#jrPrevBtn').prop('disabled', jrCurrentPage === 1 || totalRows === 0);
			$('#jrNextBtn').prop('disabled', jrCurrentPage >= totalPages || totalRows === 0);

			if (totalRows === 0) {
				$('#jrPageInfo').text('Showing 0 of 0');
			} else {
				$('#jrPageInfo').text('Showing ' + (startIndex + 1) + '-' + Math.min(endIndex, totalRows) + ' of ' + totalRows);
			}
		}

		$('#jrEventSearch').on('input', function () {
			jrCurrentPage = 1;
			jrRenderRows();
		});

		$('#jrPrevBtn').on('click', function () {
			if (jrCurrentPage > 1) {
				jrCurrentPage--;
				jrRenderRows();
			}
		});

		$('#jrNextBtn').on('click', function () {
			jrCurrentPage++;
			jrRenderRows();
		});

		jrRenderRows();

		$('#judgeregister').on('submit', function () {
			$(this).find('.jr-generated-event').remove();
			$('.jr-judge-choice:checked').each(function () {
				$('<input>', {
					type: 'hidden',
					name: 'Conventionregistrations[judges_event_ids][]',
					value: $(this).data('eventId'),
					class: 'jr-generated-event'
				}).appendTo('#judgeregister');
			});
		});
	});
</script>
<style>
.jr-page {
	padding-top: 14px;
	padding-bottom: 18px;
	padding-left: 8px;
	padding-right: 8px;
}
.jr-header {
	background: linear-gradient(135deg, #f7fbff 0%, #edf4ff 100%);
	border: 1px solid #dce8f8;
	border-radius: 14px;
	padding: 18px 20px;
	margin-bottom: 16px;
	box-shadow: 0 8px 20px rgba(30, 70, 120, 0.08);
}
.jr-title {
	margin: 0;
	font-size: 24px;
	line-height: 1.25;
	color: #18314f;
	font-weight: 700;
}
.jr-subtitle {
	margin-top: 6px;
	font-size: 14px;
	color: #4a6380;
}
.jr-form-card {
	background: #ffffff;
	border: 1px solid #dfe6ef;
	border-radius: 14px;
	padding: 18px 18px 16px;
	box-shadow: 0 10px 24px rgba(16, 42, 67, 0.08);
	width: 100%;
	max-width: 100%;
}
.jr-form-card .form-group {
	width: 100%;
}
.jr-form-card .form-group > label {
	float: none;
	width: 100%;
	display: block;
	text-align: left;
	margin-bottom: 8px;
}
.jr-form-card .form-group .input {
	float: none;
	width: 100%;
	max-width: 100%;
}
.jr-form-card .form-btns > label {
	display: none;
}
.jr-section-label {
	display: block;
	font-size: 13px;
	text-transform: uppercase;
	letter-spacing: 0.06em;
	font-weight: 700;
	color: #5d7290;
	margin-bottom: 8px;
}
.jr-toolbar {
	display: flex;
	justify-content: flex-end;
	margin-bottom: 10px;
}
.jr-search {
	max-width: 360px;
	width: 100%;
	border: 1px solid #cddced;
	border-radius: 10px;
	padding: 10px 12px;
	font-size: 14px;
	color: #1e3857;
	background: #fbfdff;
}
.jr-search:focus {
	outline: none;
	border-color: #4d86bd;
	box-shadow: 0 0 0 3px rgba(77, 134, 189, 0.15);
	background: #fff;
}
.jr-table-wrap {
	border: 1px solid #dfe6ef;
	border-radius: 12px;
	overflow: auto;
	margin-top: 8px;
	background: #fff;
}
.jr-table {
	margin-bottom: 0;
	min-width: 1000px;
	width: 100%;
}
.jr-table thead th {
	background: #eef4fb;
	font-size: 12px;
	font-weight: 700;
	color: #26415f;
	text-transform: uppercase;
	letter-spacing: 0.04em;
	padding: 12px 10px;
	border-bottom: 1px solid #d6e2f0;
}
.jr-table tbody td {
	vertical-align: middle;
	padding: 11px 10px;
	border-color: #e6edf7;
}
.jr-table tbody tr:nth-child(even) {
	background: #fbfdff;
}
.jr-table tbody tr:hover {
	background: #f2f8ff;
}
.jr-event-name {
	font-weight: 600;
	color: #1e3857;
}
.jr-choice-cell {
	text-align: center;
}
.jr-judge-choice {
	width: 16px;
	height: 16px;
	cursor: pointer;
	accent-color: #1f6fb5;
}
.jr-interest-choice {
	width: 17px;
	height: 17px;
	cursor: pointer;
	accent-color: #1f6fb5;
}
.jr-slot-taken {
	display: inline-block;
	width: 14px;
	height: 14px;
	border-radius: 50%;
	background: #5a7695;
	box-shadow: inset 0 0 0 2px #d8e3f0;
}
.jr-table tbody tr.jr-event-full {
	background: #fde8e8;
	box-shadow: inset 4px 0 0 #d9534f;
}
.jr-table tbody tr.jr-event-full:hover {
	background: #fbdede;
}
.jr-table tbody tr.jr-event-full .jr-event-name,
.jr-table tbody tr.jr-event-full td {
	color: #7a1f1a;
	font-weight: 600;
}
.jr-help {
	display: block;
	margin-top: 10px;
	color: #4e647f;
}
.jr-no-results {
	display: none;
	margin-top: 8px;
	font-size: 13px;
	color: #7b4a00;
	background: #fff5df;
	border: 1px solid #f1d59e;
	border-radius: 8px;
	padding: 8px 10px;
}
.jr-pagination {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-top: 10px;
	gap: 12px;
}
.jr-page-info {
	font-size: 13px;
	color: #4e647f;
}
.jr-page-actions {
	display: flex;
	gap: 8px;
}
.jr-page-btn {
	border: 1px solid #c8d9ec;
	background: #f7fbff;
	color: #21466f;
	border-radius: 8px;
	padding: 7px 12px;
	font-size: 13px;
	font-weight: 600;
	cursor: pointer;
}
.jr-page-btn:hover {
	background: #eaf3ff;
}
.jr-page-btn:disabled {
	opacity: 0.5;
	cursor: not-allowed;
	background: #f3f6fa;
}
.jr-actions {
	display: flex;
	gap: 10px;
	align-items: center;
	margin-top: 14px;
}

@media (max-width: 767px) {
	.jr-title {
		font-size: 20px;
	}
	.jr-page {
		padding-left: 0;
		padding-right: 0;
	}
	.jr-header,
	.jr-form-card {
		padding: 14px;
	}
	.jr-actions {
		flex-wrap: wrap;
	}
	.jr-toolbar {
		justify-content: stretch;
	}
	.jr-search {
		max-width: 100%;
	}
	.jr-pagination {
		flex-wrap: wrap;
	}
}
</style>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-2 jr-page">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<div class="jr-header">
				<h2 class="jr-title">Register For Convention :: <?php echo $conventionD->name; ?></h2>
				<div class="jr-subtitle">Season: <?php echo $convSeasonD->season_year; ?></div>
			</div>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form jr-form-card">
				<?php echo $this->Form->create($conventionregistrations, ['id' => 'judgeregister', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="name" class="jr-section-label">Choose Events of Interest</label>
					<div class="jr-toolbar">
						<input type="text" id="jrEventSearch" class="jr-search" placeholder="Search by code or event name" autocomplete="off" />
					</div>
					<div class="input jr-table-wrap">
						<table class="table table-bordered table-striped jr-table">
							<thead>
								<tr>
									<th style="width:52px;">#</th>
									<th style="width:100px;">Code</th>
									<th>Event Name</th>
									<th style="width:140px;">Interest</th>
									<th style="width:160px;">Judge 1</th>
									<th style="width:160px;">Judge 2</th>
									<th style="width:160px;">Judge 3</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$selectedJudgeEventIds = isset($selectedJudgeEventIds) && is_array($selectedJudgeEventIds) ? $selectedJudgeEventIds : [];
							$selectedInterestEventIds = isset($selectedInterestEventIds) && is_array($selectedInterestEventIds) ? $selectedInterestEventIds : [];
							$eventJudgeCounts = isset($eventJudgeCounts) && is_array($eventJudgeCounts) ? $eventJudgeCounts : [];
							$eventsList = isset($eventsList) ? $eventsList : [];
							$counter = 0;
							foreach ($eventsList as $eventrec) {
								$counter++;
								$eid = (int)$eventrec->id;
								$isChecked = in_array($eid, $selectedJudgeEventIds);
								$eventSelectedCount = isset($eventJudgeCounts[$eid]) ? (int)$eventJudgeCounts[$eid] : 0;
								$occupiedSlots = min(3, max(0, $eventSelectedCount));
								$otherOccupiedSlots = $isChecked ? max(0, $occupiedSlots - 1) : $occupiedSlots;
								$currentJudgeSlot = $isChecked ? min(3, $otherOccupiedSlots + 1) : 0;
								$isFullyOccupied = ($occupiedSlots >= 3 && !$isChecked);
							?>
								<tr class="<?php echo $isFullyOccupied ? 'jr-event-full' : ''; ?>">
									<td><?php echo $counter; ?></td>
									<td><?php echo h($eventrec->event_id_number); ?></td>
									<td class="jr-event-name"><?php echo h($eventrec->event_name); ?></td>
									<td class="jr-choice-cell">
										<input type="checkbox" class="jr-interest-choice" name="Conventionregistrations[interest_event_ids][]" value="<?php echo $eid; ?>" <?php echo in_array($eid, $selectedInterestEventIds, true) ? 'checked' : ''; ?> />
									</td>
									<td class="jr-choice-cell">
										<?php if ($otherOccupiedSlots >= 1) { ?>
											<span class="jr-slot-taken" title="Already selected"></span>
										<?php } else { ?>
											<input type="radio" class="jr-judge-choice" name="judge_choice_<?php echo $eid; ?>" value="1" data-event-id="<?php echo $eid; ?>" <?php echo ($currentJudgeSlot === 1) ? 'checked' : ''; ?> />
										<?php } ?>
									</td>
									<td class="jr-choice-cell">
										<?php if ($otherOccupiedSlots >= 2) { ?>
											<span class="jr-slot-taken" title="Already selected"></span>
										<?php } else { ?>
											<input type="radio" class="jr-judge-choice" name="judge_choice_<?php echo $eid; ?>" value="2" data-event-id="<?php echo $eid; ?>" <?php echo ($currentJudgeSlot === 2) ? 'checked' : ''; ?> />
										<?php } ?>
									</td>
									<td class="jr-choice-cell">
										<?php if ($otherOccupiedSlots >= 3) { ?>
											<span class="jr-slot-taken" title="Already selected"></span>
										<?php } else { ?>
											<input type="radio" class="jr-judge-choice" name="judge_choice_<?php echo $eid; ?>" value="3" data-event-id="<?php echo $eid; ?>" <?php echo ($currentJudgeSlot === 3) ? 'checked' : ''; ?> />
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
					<div id="jrNoResults" class="jr-no-results">No events match your search.</div>
					<div class="jr-pagination">
						<div id="jrPageInfo" class="jr-page-info">Showing 0 of 0</div>
						<div class="jr-page-actions">
							<button type="button" id="jrPrevBtn" class="jr-page-btn">Previous</button>
							<button type="button" id="jrNextBtn" class="jr-page-btn">Next</button>
						</div>
					</div>
					<span class='help_text jr-help'><small>Note: not all events will be allocated to you. Events are allocated on a needs basis and we do take into consideration event workload.</small></span>
				</div>

			<div class="form-group form-btns jr-actions">
				<label></label>
				<button type="submit" class="btn btn-secondary">Save</button>
				<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
				<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'myregistrations'], ['class' => 'btn btn-secondary']); ?>
			</div>
			<?php echo $this->Form->end(); ?>
	</div>
	<!-- dashboard-section-3 end-->

	</main>
</div>
</div>