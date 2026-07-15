<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper admin-list-page admin-overallpositions-page">
    <section class="content-header">
      <h1>
			Overall Positions - <?php echo $conventionD->name; ?> > <?php echo $conventionSD->season_year; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false]);?></li>
          <li class="active">Overall Positions</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search overallpositions-toolbar">
                <div class="overallpositions-toolbar-copy">View and export current overall winners for this season.</div>
                <div class="add_new_record">
				
				<?php
				
				echo $this->Html->link('Randomize Result', ['controller'=>'results', 'action'=>'overallpositions',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);
				
				echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller'=>'results', 'action'=>'overallpositionsprint',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);
                echo $this->Html->link('<i class="fa fa-download"></i> Download JSON', ['controller'=>'results', 'action'=>'overallpositionsjson',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default']);
                echo $this->Html->link('<i class="fa fa-file-text-o"></i> Download CSV', ['controller'=>'results', 'action'=>'overallpositionscsv',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default']);
				
				?>
				
				</div>
            </div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Results/overallpositions"); ?>
            </div>
            
        </div>
    </section>
</div>
