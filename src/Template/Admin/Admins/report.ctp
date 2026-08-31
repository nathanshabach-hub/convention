<div class="content-wrapper admin-report">
    <section class="content-header">
        <h1>
            Convention Report
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Report</li>
        </ol>
    </section>

    <section class="content report-overview">
        <div class="dashboard-hero-card">
            <div class="report-header-row">
                <div class="dashboard-hero-copy">
                </div>
                <div class="dashboard-download-btn">
                    <a href="<?php echo $this->Url->build(['action' => 'previewReport', '?' => ['convention_year' => $selectedReportYear]]); ?>" class="btn btn-info" target="_blank">
                        <i class="fa fa-eye"></i> Preview Report
                    </a>
                    <a href="<?php echo $this->Url->build(['action' => 'downloadReport', '?' => ['convention_year' => $selectedReportYear]]); ?>" class="btn btn-primary">
                        <i class="fa fa-download"></i> Download Report
                    </a>
                </div>
            </div>
        </div>

        <div class="report-filter-card">
            <?php echo $this->Form->create(null, ['type' => 'get', 'url' => ['action' => 'report'], 'class' => 'report-filter-form']); ?>
                <div class="report-filter-group">
                    <label for="convention-year-id">Convention Year</label>
                    <?php
                    echo $this->Form->control('convention_year', [
                        'id' => 'convention-year-id',
                        'type' => 'select',
                        'label' => false,
                        'options' => $reportYearOptions,
                        'empty' => 'Select Convention Year',
                        'value' => $selectedReportYear,
                        'class' => 'form-control'
                    ]);
                    ?>
                </div>
                <button type="submit" class="btn btn-success report-filter-btn">Load Report</button>
            <?php echo $this->Form->end(); ?>
        </div>
    </section>

    <section class="content">
        <?php if (empty($hasReportData)) { ?>
            <div class="alert alert-info">Please select a convention year to view report data.</div>
        <?php } ?>

        <?php if (!empty($hasReportData)) { ?>
        <!-- Place Winners Section -->
        <div class="row">
            <div class="col-md-12">
                <h4 class="section-title"><i class="fa fa-trophy"></i> Place Winners</h4>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-trophy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">1st Place Winners</span>
                        <span class="info-box-number"><?php echo isset($first_place_winners) ? $first_place_winners : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-trophy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">2nd Place Winners</span>
                        <span class="info-box-number"><?php echo isset($second_place_winners) ? $second_place_winners : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box info-box-third-place">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-trophy"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">3rd Place Winners</span>
                        <span class="info-box-number"><?php echo isset($third_place_winners) ? $third_place_winners : '0'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registrations Section -->
        <div class="row">
            <div class="col-md-12">
                <h4 class="section-title"><i class="fa fa-users"></i> Convention Registrations</h4>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-navy"><i class="fa fa-bank"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Schools</span>
                        <span class="info-box-number"><?php echo isset($total_schools) ? $total_schools : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-maroon"><i class="fa fa-group"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Students</span>
                        <span class="info-box-number"><?php echo isset($total_students) ? $total_students : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-light-blue"><i class="fa fa-user-secret"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Supervisors</span>
                        <span class="info-box-number"><?php echo isset($total_supervisors) ? $total_supervisors : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-bookmark"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Judges</span>
                        <span class="info-box-number"><?php echo isset($total_judges) ? $total_judges : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-purple"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Visitors</span>
                        <span class="info-box-number"><?php echo isset($total_visitors) ? $total_visitors : '0'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Event Registration Distribution -->
        <div class="row">
            <div class="col-md-12">
                <h4 class="section-title"><i class="fa fa-line-chart"></i> Student Event Registration Distribution</h4>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Students with 20 Events</span>
                        <span class="info-box-number"><?php echo isset($students_20_events) ? $students_20_events : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-teal"><i class="fa fa-star-half-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Students with 11-15 Events</span>
                        <span class="info-box-number"><?php echo isset($students_11_15_events) ? $students_11_15_events : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-olive"><i class="fa fa-star-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Students with 5-10 Events</span>
                        <span class="info-box-number"><?php echo isset($students_5_10_events) ? $students_5_10_events : '0'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Awards Section -->
        <div class="row">
            <div class="col-md-12">
                <h4 class="section-title"><i class="fa fa-book"></i> Scripture Reading Awards</h4>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-book"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Silver Apple Readings</span>
                        <span class="info-box-number"><?php echo isset($silver_apple_count) ? $silver_apple_count : '0'; ?></span>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Golden Awards</span>
                        <span class="info-box-number"><?php echo isset($golden_awards_count) ? $golden_awards_count : '0'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Entries Section -->
        <div class="row">
            <div class="col-md-12">
                <h4 class="section-title"><i class="fa fa-list-ul"></i> Event Registrations</h4>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-teal"><i class="fa fa-pencil-square-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Event Entries</span>
                        <span class="info-box-number"><?php echo isset($total_entries) ? $total_entries : '0'; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </section>
</div>

<style>
    .admin-report {
        padding: 18px;
        background-color: #f5f5f5;
        min-height: 100vh;
    }

    .section-title {
        color: #333;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        margin-top: 0;
        padding-bottom: 10px;
        border-bottom: 3px solid #007bff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #007bff;
    }

    .report-overview {
        margin-bottom: 14px;
        padding: 0;
        min-height: auto;
    }

    .dashboard-hero-card {
        padding: 16px 20px;
        margin: 0;
    }

    .report-header-row {
        align-items: flex-start;
        display: flex;
        gap: 16px;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .dashboard-hero-copy h2 {
        margin: 0 0 5px;
        font-size: 24px;
    }

    .dashboard-hero-copy p {
        margin: 0;
        font-size: 14px;
    }

    .dashboard-download-btn {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dashboard-download-btn .btn {
        padding: 8px 16px;
        font-size: 14px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .dashboard-download-btn .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
    }

    .dashboard-download-btn .btn-primary:hover {
        background-color: #0056b3;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .dashboard-download-btn .btn-info {
        background-color: #17a2b8;
        color: white;
        border: none;
    }

    .dashboard-download-btn .btn-info:hover {
        background-color: #138496;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .admin-report .content {
        padding: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .report-filter-card {
        background: #fff;
        border: 1px solid #d9e3ef;
        border-radius: 8px;
        margin: 12px 0 0;
        padding: 14px;
    }

    .report-filter-form {
        align-items: flex-end;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .report-filter-group {
        display: flex;
        flex: 1 1 320px;
        flex-direction: column;
        gap: 6px;
    }

    .report-filter-group label {
        color: #2f4d68;
        font-size: 13px;
        font-weight: 700;
        margin: 0;
    }

    .report-filter-btn {
        min-width: 120px;
        padding: 8px 14px;
    }

    @media (max-width: 767px) {
        .admin-report {
            padding: 12px;
        }

        .dashboard-download-btn {
            width: 100%;
        }

        .dashboard-download-btn .btn {
            flex: 1 1 auto;
            justify-content: center;
        }
    }

    .info-box {
        display: flex;
        align-items: stretch;
        margin-bottom: 12px;
        border-radius: 4px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .info-box-third-place {
        background: #fff !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    }

    .info-box-third-place:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
    }

    .info-box-third-place .info-box-icon {
        background: #fff !important;
        color: #ffc107 !important;
        border: 2px solid #ffc107 !important;
    }

    .info-box-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 90px;
        color: #fff;
        font-size: 24px;
        border-radius: 4px 0 0 4px;
    }

    .info-box-content {
        flex: 1;
        padding: 15px 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .info-box-text {
        color: #666;
        font-size: 13px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-box-number {
        font-size: 32px;
        font-weight: 700;
        color: #333;
        margin-top: 5px;
    }

    .bg-navy {
        background: linear-gradient(135deg, #001a4d 0%, #003d99 100%);
    }

    .bg-maroon {
        background: linear-gradient(135deg, #8B0000 0%, #C41E3A 100%);
    }

    .bg-light-blue {
        background: linear-gradient(135deg, #00A1DE 0%, #5DADE2 100%);
    }

    .bg-green {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .bg-purple {
        background: linear-gradient(135deg, #6f42c1 0%, #9b59b6 100%);
    }

    .bg-aqua {
        background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
    }

    .bg-teal {
        background: linear-gradient(135deg, #20B2AA 0%, #48D1CC 100%);
    }

    .bg-olive {
        background: linear-gradient(135deg, #808000 0%, #CDDC39 100%);
    }

    .bg-red {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    }

    .bg-yellow {
        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    }

    .bg-blue {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    /* Responsive layouts */
    @media (max-width: 1200px) {
        /* 3 columns on medium screens */
    }

    @media (max-width: 768px) {
        .info-box-icon {
            width: 70px;
            font-size: 20px;
        }
        
        .info-box-number {
            font-size: 24px;
        }
    }

    @media (max-width: 576px) {
        .section-title {
            font-size: 16px;
        }
        
        .info-box-icon {
            width: 60px;
            font-size: 18px;
        }
    }

    /* Smooth content flow */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-left: -15px;
        margin-right: -15px;
        margin-bottom: 0;
    }

    .row > [class*='col-'] {
        padding-left: 15px;
        padding-right: 15px;
    }
</style>
