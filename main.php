<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>

<?php
// Redirect
header("Location: jobs_panel");

// Page specific configuration
$dm->l_header_fixed               = false;
?>

<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/main.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Hero -->
<div class="bg-header-dark">
    <div class="content content-full">
        <div class="row pt-3">
            <div class="col-md py-3 d-md-flex align-items-md-center text-center d-none d-lg-flex">
                <h1 class="text-white mb-0">
                    <span class="font-w300">Dashboard</span>
                    <span class="font-w400 font-size-lg text-white-75 d-block d-md-inline-block">Welcome <?php echo $session->userinfo['user_firstname']; ?></span>
                </h1>
            </div>
            <div class="col-md py-3 d-md-flex align-items-md-center justify-content-md-end text-center">
                <button type="button" class="btn btn-hero-primary mr-1" data-toggle="modal" data-target="#modal-new-job-form">
                    <i class="fa fa-plus mr-1"></i> New Inquiry
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="bg-white">
    <div class="content content-full">

        <!-- Table quick filters -->
        <div class="row gutters-tiny push">
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="250">
                <a class="block text-center bg-primary h-100" id="incomplete-box" href="jobs_panel?filter=incomplete-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalNotCompleteJobsCount() ?></div>
                    <div class="font-w600 text-uppercase text-white-75">Job Pending</div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="200">
                <a class="block text-center bg-info h-100" id="job-ready-box" href="jobs_panel?filter=job-ready-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalReadyJobCount() ?></div>
                    <div class="font-w600 text-uppercase text-white-75">Job Assigned</div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="275">
                <a class="block text-center bg-warning h-100" id="pending-inspections-box" href="jobs_panel?filter=pending-inspections-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalSiteInspectionPendingCount() ?></div>
                    <div class="font-w600 text-uppercase text-white-75"><span class="d-none d-lg-inline">Site </span>Inspections</div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="295">
                <a class="block text-center bg-success h-100" id="complete-box" href="jobs_panel?filter=completed-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalCompleteJobsCount() ?></div>
                    <div class="font-w600 text-uppercase text-white-75">Job Complete</div>
                </a>
            </div>
        </div>
        <!-- END Table quick filters -->

        <!-- Quotes filter buttons -->
        <div class="row gutters-tiny push">
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="200">
                <a class="block text-center bg-success h-100" id="awaiting-create-box" href="quotes_panel?filter=awaiting-create-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalAcceptedQuotesWithoutJobs() ?></div>
                    <div class="font-w600 text-uppercase text-white-75"><span class="d-none d-lg-inline">Awaiting </span>Job Create</div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="250">
                <a class="block text-center bg-warning h-100" id="pending-box" href="quotes_panel?filter=pending-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalPendingQuoteCount() ?></div>
                    <div class="font-w600 text-uppercase text-white-75">Pending<span class="d-none d-lg-inline"> Response</span></div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="275">
                <a class="block text-center bg-secondary h-100" id="not-sent-box" href="quotes_panel?filter=not-sent-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalNotSentQuoteCount() ?></div>
                    <div class="font-w600 text-uppercase text-white-75">Quote Not Sent</div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="295">
                <a class="block text-center bg-danger h-100" id="declined-box" href="quotes_panel?filter=declined-box">
                    <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalDeclinedQuoteCount() ?></div>
                    <div class="font-w600 text-uppercase text-white-75">Quote Declined</div>
                </a>
            </div>
        </div>
        <!-- END Quotes filter buttons -->

    </div>
    <!-- END Page Content -->

    <?php require 'inc/_global/views/page_end.php'; ?>
    <?php require 'inc/_global/views/footer_start.php'; ?>

    <!-- Page JS Plugins -->
    <?php $dm->get_js('js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js'); ?>

    <!-- Page JS Code -->
    <?php $dm->get_js('js/pages/main_page.js'); ?>

    <?php require 'inc/_global/views/footer_end.php'; ?>