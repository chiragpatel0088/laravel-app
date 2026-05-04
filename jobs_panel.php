<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>
<?php
// Page specific configuration
$dm->l_header_fixed               = false;
?>
<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/datatables/responsive/responsive.dataTables.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables/row-group/rowGroup.dataTables.min.css'); ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/main.css'); ?>
<?php $dm->get_css('css/pages/jobs_panel.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Predetermined filter selected -->
<?php

$userLevel = $session->userinfo['user_level'];
// var_dump($userLevel);

if (isset($_GET['filter'])) {
    echo '<input id="pre-filter" type="hidden" value="' . $_GET['filter'] . '">';
}

?>

<!-- Page Content -->
<div class="content">
    <!-- <div class="container mb-4 ">
        <div class="row" style="justify-content:center">
            <img src="assets\media\photos\map-circle-sm.png" alt="map" class="img-fluid w-auto">
        </div>
    </div> -->
    <!-- Table quick filters -->
    <div class="row gutters-tiny push">
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="160">
            <a class="block text-center bg-xpro h-100" id="incomplete-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalNotCompleteJobsCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Job Pending</div>
            </a>
        </div>
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="175">
            <a class="block text-center bg-warning h-100" id="pending-inspections-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalSiteInspectionPendingCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75"><span class="d-none d-lg-inline">Pending </span> Site Checks</div>
            </a>
        </div>
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="190">
            <a class="block text-center bg-info h-100" id="job-ready-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalReadyJobCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Job Assigned</div>
            </a>
        </div>
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="205">
            <a class="block text-center bg-success h-100" id="completed-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalCompleteJobsCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Complete</div>
            </a>
        </div>
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="220">
            <a class="block text-center bg-gd-aqua h-100" id="ready-invoicing-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalReadyForInvoicingJobsCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Ready<span class="d-none d-lg-inline"> for Invoicing</span></div>
            </a>
        </div>
        <!-- For test -->
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="225">
            <a class="block text-center bg-xwork h-100" id="current-invoiced-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getCurrentCompleteInvoicedJobsCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Current Ivoiced</div>
            </a>
        </div>
        <?php
        ?>
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="235">
            <a class="block text-center bg-xwork h-100" id="invoiced-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalCompleteInvoicedJobsCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">All Invoiced</div>
            </a>
        </div>
        <div class="col-6 col-md col-xl invisible" data-toggle="appear" data-timeout="250">
            <a class="block text-center bg-danger h-100" id="cancelled-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalCancelledJobCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Cancelled</div>
            </a>
        </div>
    </div>
    <!-- END Table quick filters -->

    <!-- All jobs table -->
    <div class="row invisible" data-toggle="appear" data-timeout="345">
        <div class="col-md-12">
            <div class="block block-rounded block-bordered block-mode-loading-refresh">
                <div class="block-header border-bottom">
                    <h3 class="block-title"><span class="d-none d-lg-block">Jobs</span></h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option d-none d-lg-inline" id="refresh-all-jobs-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                            <i class="fa fa-sync"></i>
                        </button>
                        <div class="btn-block-option p-0">
                            <input type="text" class="form-control form-control-sm" id="all-jobs-search-box" name="all-jobs-search-box" placeholder="Search..">
                        </div>
                    </div>
                </div>
                <div class="block-content p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless table-vcenter font-size-sm display responsive" id="js-dataTable-allJobs">
                            <thead>
                                <tr class="text-uppercase">
                                    <th class="font-w700">ID</th>
                                    <th class="font-w700">Customer</th>
                                    <th class="font-w700">Address</th>
                                    <th class="font-w700">Operator</th>
                                    <th class="font-w700">Truck</th>
                                    <th class="font-w700">Supp.</th>
                                    <th class="font-w700">Type</th>
                                    <th class="font-w700">Cubics</th>
                                    <th class="font-w700" style="width: 100px;">Time</th>
                                    <th class="font-w700">Status</th>
                                    <th class="font-w700 text-center" style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END All jobs table -->
</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/datatables/responsive/dataTables.responsive.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/row-group/dataTables.rowGroup.min.js'); ?>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/jobs_panel_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>