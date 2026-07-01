<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"><?php echo $this->Flash->render() ?></div>
			<h2 class="mt-3">My Events</h2>
			<div class="dashboard-form mt-4">
				<?php if (!empty($eventRows) && count($eventRows) > 0) { ?>
					<div class="tbl-resp-listing">
						<table class="table table-bordered table-striped table-condensed cf">
							<thead class="cf">
								<tr>
									<th>Event No.</th>
									<th>Event Name</th>
									<th>Group</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($eventRows as $eventRow) { ?>
									<tr>
										<td data-title="Event No."><?php echo h($eventRow->event_id_number ?? ''); ?></td>
										<td data-title="Event Name"><?php echo h($eventRow->Events->event_name ?? 'N/A'); ?></td>
										<td data-title="Group"><?php echo !empty($eventRow->group_name) ? h($eventRow->group_name) : '-'; ?></td>
										<td data-title="Status"><?php echo ((int)($eventRow->status ?? 0) === 1) ? 'Active' : 'Pending'; ?></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } else { ?>
					<div class="admin_no_record">No events found.</div>
				<?php } ?>
			</div>
		</main>
	</div>
</div>