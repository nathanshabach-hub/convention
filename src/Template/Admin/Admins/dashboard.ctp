<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<div class="content-wrapper admin-dashboard">
    <section class="content-header">
        <h1>
            Dashboard
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <section class="content dashboard-overview">
        <div class="dashboard-hero-card">
            <div class="dashboard-hero-copy">
                <h2>Admin Overview</h2>
                <p>
                    <?php if($sess_admin_header_season_id>0) { ?>
                        Live metrics for the selected convention season.
                    <?php } else { ?>
                        Platform-wide metrics across all conventions and seasons.
                    <?php } ?>
                </p>
            </div>
            <div class="dashboard-hero-icon" aria-hidden="true">
                <i class="fa fa-line-chart"></i>
            </div>
        </div>
    </section>

    <?php
    if($sess_admin_header_season_id>0)
    {
    ?>
    <section class="content">
        <div class="row">
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-navy">
                    <div class="inner">
                        <h3><?php echo $total_students ? $total_students : '0'; ?></h3>
                        <p>Students</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-group"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrationstudents', 'action' => 'allstudents'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-light-blue">
                    <div class="inner">
                        <h3><?php echo $total_teachers_parents ? $total_teachers_parents : '0'; ?></h3>
                        <p>Supervisors</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user-secret"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrationteachers', 'action' => 'allteachers'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3><?php echo $total_schools ? $total_schools : '0'; ?></h3>
                        <p>Schools/Homeschools</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bank"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrations', 'action' => 'allschools'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $total_judges ? $total_judges : '0'; ?></h3>
                        <p>Judges</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bookmark"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrations', 'action' => 'alljudges'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo isset($total_pastors) ? $total_pastors : '0'; ?></h3>
                        <p>Pastors</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'pastors', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-olive">
                    <div class="inner">
                        <h3>&mdash;</h3>
                        <p>Judging List</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-check-square-o"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrations', 'action' => 'judginglist'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3><?php echo isset($total_events_judged) ? $total_events_judged : '0'; ?></h3>
                        <p>Evaluations</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-gavel"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'judgeevaluations', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $total_conv_seas_events ? $total_conv_seas_events : '0'; ?></h3>
                        <p>Total Events Registered</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-puzzle-piece"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionseasonevents', 'action' => 'allevents'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-lime">
                    <div class="inner">
                        <h3><?php echo $total_transactions ? $total_transactions : '0'; ?></h3>
                        <p>Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-dollar"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'transactions', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3><?php echo isset($total_running_events) ? $total_running_events : '0'; ?></h3>
                        <p>Running List</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-list-ol"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'admins', 'action' => 'runninglist'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-black">
                    <div class="inner">
                        <h3><?php echo isset($total_squad247) ? (int)$total_squad247 : 0; ?></h3>
                        <p>24/7 Squad</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-file-text-o"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'admins', 'action' => 'squad247'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>—</h3>
                        <p>Report</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bar-chart"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'admins', 'action' => 'report'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
        </div>
    </section>
    <?php
    }
    else
    {
    ?>
    <section class="content">
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo $total_seasons ? $total_seasons : '0'; ?></h3>
                        <p>Seasons</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bars"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'seasons', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $total_events ? $total_events : '0'; ?></h3>
                        <p>Global Events</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-puzzle-piece"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'events', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo $total_conventions ? $total_conventions : '0'; ?></h3>
                        <p>Conventions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bars"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventions', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3><?php echo $total_divisions ? $total_divisions : '0'; ?></h3>
                        <p>Divisions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-tasks"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'divisions', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3><?php echo $total_schools ? $total_schools : '0'; ?></h3>
                        <p>Schools/Homeschools</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bank"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-light-blue">
                    <div class="inner">
                        <h3><?php echo $total_teachers_parents ? $total_teachers_parents : '0'; ?></h3>
                        <p>Supervisors</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user-secret"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'teachers'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $total_judges ? $total_judges : '0'; ?></h3>
                        <p>Judges</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bookmark"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'judges'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-navy">
                    <div class="inner">
                        <h3><?php echo $total_students ? $total_students : '0'; ?></h3>
                        <p>Students</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-group"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'students'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-maroon">
                    <div class="inner">
                        <h3><?php echo isset($total_pastors) ? $total_pastors : '0'; ?></h3>
                        <p>Pastors</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'pastors', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-olive">
                    <div class="inner">
                        <h3><?php echo $total_registrations ? $total_registrations : '0'; ?></h3>
                        <p>Convention Registrations</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-newspaper-o"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrations', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-lime">
                    <div class="inner">
                        <h3><?php echo $total_transactions ? $total_transactions : '0'; ?></h3>
                        <p>Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-dollar"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'transactions', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-black">
                    <div class="inner">
                        <h3>&mdash;</h3>
                        <p>24/7 Squad</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-file-text-o"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'admins', 'action' => 'squad247'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>—</h3>
                        <p>Report</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bar-chart"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'admins', 'action' => 'report'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
        </div>
    </section>
    <?php
    }
    ?>
</div>