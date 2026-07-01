<?php
use Cake\ORM\TableRegistry;
$this->Eventcategories = TableRegistry::get('Eventcategories');
$this->Divisions = TableRegistry::get('Divisions');
$this->Events = TableRegistry::get('Events');
?>
<script type="text/javascript">
	$(document).ready(function () {
		$("#addstudentevent").validate();
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Manage Student Event</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				
				<table id="tbl_header-se" class="table table-striped table-bordered" style="width:100%">
					<thead class="cf ajshort">
						<tr>
							<th width="20%">First Name</th>
							<td width="20%"><?php echo $checkCRS->Students['first_name']; ?></td>
							<th width="15%">Middle Name</th>
							<td width="15%"><?php echo $checkCRS->Students['middle_name']; ?></td>
							<th width="15%">Last Name</th>
							<td width="15%"><?php echo $checkCRS->Students['last_name']; ?></td>
						</tr>
						<tr>
							<th width="20%">School</th>
							<td colspan="3" width="50%"><?php echo $checkCRS->Users['first_name']; ?></td>
							<th width="15%">Year of Birth</th>
							<td width="15%"><?php echo $checkCRS->Students['birth_year']; ?></td>
						</tr>
					</thead>
				</table>
				
				
				<table id="cr_student_events_list" class="table table-striped table-bordered" style="width:100%">
					<?php
					// first show categories
					if(count($arrConvSeasonEventsCats)>0)
					{
						$arrConvSeasonEventsCatsImplode = implode(",",$arrConvSeasonEventsCats);
						
						$condEvCats = array();
						$condEvCats[] = "(Eventcategories.id IN ($arrConvSeasonEventsCatsImplode) )";
						$eventCatList = $this->Eventcategories->find()->where($condEvCats)->order(['Eventcategories.name' => 'ASC'])->all();
						foreach($eventCatList as $eventcat)
						{
					?>
					<thead>
					<tr>
						<th colspan="3" class="text-center"><h3><?php echo $eventcat->name; ?> - <?php echo $eventcat->max_events; ?> max</h3></th>
					</tr>
					</thead>
					<tbody>
						<?php
						// fetch all divisions for each category
						$arrConvSeasonEventsDivsImplode = implode(",",$arrConvSeasonEventsDivs);
						
						$condEvDivs = array();
						$condEvDivs[] = "(Divisions.eventcategory_id = '".$eventcat->id."')";
						$condEvDivs[] = "(Divisions.id IN ($arrConvSeasonEventsDivsImplode) )";
						$eventDivList = $this->Divisions->find()->where($condEvDivs)->order(['Divisions.name' => 'ASC'])->all();
						foreach($eventDivList as $eventdiv)
						{
						?>
						<tr><td colspan="3" class="text-center"><h2><?php echo $eventdiv->name; ?> - <?php echo $eventdiv->max_events; ?> max</h2></td></tr>
						
							<?php
							// now fetch all events for this Division
							$arrConvSeasonEventsListImplode = implode(",",$arrConvSeasonEventsList);
							
							$condEvents = array();
							$condEvents[] = "(Events.division_id = '".$eventdiv->id."')";
							$condEvents[] = "(Events.id IN ($arrConvSeasonEventsListImplode) )";
							$eventList = $this->Events->find()->where($condEvents)->order(['Events.event_name' => 'ASC'])->all();
							foreach($eventList as $event)
							{
							?>
							<tr>
								<td width="40%" class="text-center"><?php echo $event->event_name; ?></td>
								<td width="40%" class="text-center"><?php echo $event->event_id_number; ?></td>
								<td width="20%" class="text-center"><input type="checkbox" /></td>
							</tr>
						
						<?php
							}
						?>
						<tr><td colspan="3" class="text-center">&nbsp;</td></tr>
						<?php
						} // end div
						?>
						<tr><td colspan="3" class="text-center">&nbsp;</td></tr>
					<?php
						} // end categories
					}// end if
					?>
					</tbody>
				</table>
				
				
				 
				<?php echo $this->Form->create($conventionregistrationstudents, ['id' => 'addstudentevent', 'type' => 'file', 'class' => ' ']); ?>



				

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary">Save</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>
<script>
$(document).ready(function() {
$('#cr_student_events_list').dataTable({
    //"bPaginate": false,
    "bLengthChange": false,
	"pageLength": 5,
	"searching": true,
	//order: [[6, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#cr_student_events_list').dataTable.search(this.value).draw();
    }); */
});
</script>
<?php echo $this->element("jquery_datatable_code"); ?>