<div class="container-fluid p-0">
	<style>
		.schedule-row-complete td {
			background: #dff0d8 !important;
			color: #1f4d20;
		}

		.schedule-row-complete:hover td {
			background: #d0e9c6 !important;
		}
	</style>
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"><?php echo $this->Flash->render() ?></div>
			<h2 class="mt-3">My Schedule</h2>
			<div class="dashboard-form mt-4">
				<?php if (!empty($scheduleRows) && count($scheduleRows) > 0) { ?>
					<div class="tbl-resp-listing">
						<table class="table table-bordered table-striped table-condensed cf">
							<thead class="cf">
								<tr>
									<th>Day</th>
									<th>Start</th>
									<th>Event</th>
									<th>Location</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($scheduleRows as $scheduleRow) { ?>
									<tr class="<?php echo !empty($scheduleRow->is_judged_complete) ? 'schedule-row-complete' : ''; ?>">
										<td data-title="Day"><?php echo h($scheduleRow->day ?? 'TBD'); ?></td>
										<td data-title="Start"><?php echo !empty($scheduleRow->start_time) ? date('h:i A', strtotime((string)$scheduleRow->start_time)) : 'TBD'; ?></td>
										<td data-title="Event"><?php echo h($scheduleRow->Events->event_name ?? 'N/A'); ?><?php echo !empty($scheduleRow->Events->event_id_number) ? ' (' . h($scheduleRow->Events->event_id_number) . ')' : ''; ?></td>
										<td data-title="Location"><?php echo h($scheduleRow->Conventionrooms->room_name ?? 'TBD'); ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } else { ?>
					<div class="admin_no_record">No schedule found.</div>
				<?php } ?>
			</div>
		</main>
	</div>
</div>