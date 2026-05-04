<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>
<?php require 'inc/_global/views/head_start.php'; ?>
<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-sm-fill font-size-h2 font-w400 mt-2 mb-0 mb-sm-2">Reports</h1>
            <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">System</li>
                    <li class="breadcrumb-item">Database</li>
                    <li class="breadcrumb-item active" aria-current="page">Reports</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="content">
    <!-- Report functions -->
    <div class="row">
        <div class="col-md-12">
            <div class="block block-rounded block-bordered block-mode-loading-refresh">
                <div class="block-header border-bottom">
                    <h3 class="block-title">Reports</h3>
                </div>
                <div class="block-content">
                    <div class="row row-deck">
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-job-report-by-year">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-seedling opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Jobs Overview by Year</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-customer-jobs-by-year">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-user-friends opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Jobs Per Customer</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-jobs-per-operator-report">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-hard-hat opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Jobs Per Operator</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-jobs-per-truck-report">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-truck-monster opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Jobs Per Truck</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-sales-per-operator-report">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-hard-hat opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Sales Per Operator</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-sales-per-truck-report">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-truck-monster opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Sales Per Truck</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-hours-per-truck-report">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-truck-monster opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Hours Per Truck</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-cubics-per-truck-report">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-truck-monster opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Cubics Per Truck</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-4 col-xl-3" data-toggle="modal" data-target="#modal-hours-per-operator-report">
                            <a class="block block-rounded block-link-pop text-center d-flex align-items-center" href="javascript:void(0)">
                                <div class="block-content">
                                    <p class="mb-2 d-none d-sm-block text-primary">
                                        <i class="fa fa-hard-hat opacity-25 fa-2x"></i>
                                    </p>
                                    <p class="font-w600 font-size-sm text-uppercase">Hours Per Operator</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END Report functions -->
</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/reports_page.js'); ?>

<?php require 'inc/modals/report_jobs_by_year.php'; ?>
<?php require 'inc/modals/report_jobs_per_operator.php'; ?>
<?php require 'inc/modals/report_jobs_per_truck.php'; ?>
<?php require 'inc/modals/report_jobs_per_customer.php'; ?>
<?php require 'inc/modals/report_sales_per_operator.php'; ?>
<?php require 'inc/modals/report_sales_per_truck.php'; ?>
<?php require 'inc/modals/report_hours_per_truck.php'; ?>
<?php require 'inc/modals/report_cubics_per_truck.php'; ?>
<?php require 'inc/modals/report_hours_per_operator.php'; ?>

<?php require 'inc/_global/views/footer_end.php'; ?>