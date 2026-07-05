<?php echo $this->Html->script('ajax-pagging.js'); ?>
<?php
$conventionSD = $conventionSD ?? (object)['Conventions' => ['name' => '', 'slug' => ''], 'Seasons' => ['season_year' => '']];
$slug_convention_season = $slug_convention_season ?? '';
$pendingEventsToRoomsList = $pendingEventsToRoomsList ?? [];
$conventionseasonroomevents = $conventionseasonroomevents ?? [];
?>
<div class="content-wrapper admin-list-page admin-roomevents-page">
    <section class="content-header">
      <h1>
			Manage Room Events - <?php echo $conventionSD->Conventions['name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$conventionSD->Conventions['slug']], ['escape'=>false]);?></li>
          <li class="active">Manage Room Events - <?php echo $conventionSD->Conventions['name']; ?></li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
			<div class="admin_search roomevents-toolbar">
				<div class="roomevents-toolbar-title">Assign events to rooms for this season.</div>
				<div class="add_new_record">
					<?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Room Event', ['controller'=>'conventions', 'action'=>'addroomevents',$slug_convention_season], ['escape'=>false, 'class'=>'btn btn-default']);?>
					<?php
					if(count($pendingEventsToRoomsList)>0)
					{
					?>
					<button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModalPendingEvents">Pending Events (<?php echo count($pendingEventsToRoomsList); ?>)</button>
					<?php
					}
					?>
				</div>
			</div>
			
            <div class="m_content" id="listID">
				<?php echo $this->element("Admin/Conventions/roomevents", [
					'conventionseasonroomevents' => $conventionseasonroomevents,
					'slug_convention_season' => $slug_convention_season,
				]); ?>
            </div>
			 
				
			 
            
        </div>
    </section>
</div>
 
<?php
if(count($pendingEventsToRoomsList)>0)
{
?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<div class="modal fade" id="myModalPendingEvents" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Pending Events <?php echo count($pendingEventsToRoomsList); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				$cntrPE = 1;
				foreach($pendingEventsToRoomsList as $pendingev)
				{
				?>
					<p>
						<?php
						echo $cntrPE.'.&nbsp;&nbsp;';
						echo $pendingev;
						?>
					</p>
				<?php
				$cntrPE++;
				}
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php
}
?>
