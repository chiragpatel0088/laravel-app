<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>
<?php
// Page specific configuration
$dm->l_header_fixed               = false;
?>
<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/select2/css/select2.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables/select/select.dataTables.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables/dataTables.bootstrap4.css'); ?>
<?php $dm->get_css('js/plugins/datatables/buttons-bs4/buttons.bootstrap4.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables-editor/css/editor.dataTables.css'); ?>
<?php $dm->get_css('js/plugins/datatables/responsive/responsive.dataTables.min.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Predetermined filter selected -->
<?php 

if(isset($_GET['filter'])) {
    echo '<input id="pre-filter" type="hidden" value="' . $_GET['filter'] . '">';
}

?>

<!-- Page Content -->
<div class="content">
    <!-- Quotes filter buttons -->
    <div class="row gutters-tiny push">
        <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="200">
            <a class="block text-center bg-success h-100" id="awaiting-create-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalAcceptedQuotesWithoutJobs() ?></div>
                <div class="font-w600 text-uppercase text-white-75"><span class="d-none d-lg-inline">Awaiting </span>Job Create</div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="250">
            <a class="block text-center bg-warning h-100" id="pending-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalPendingQuoteCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Pending<span class="d-none d-lg-inline"> Response</span></div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="275">
            <a class="block text-center bg-secondary h-100" id="not-sent-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalNotSentQuoteCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Not Sent</div>
            </a>
        </div>
        <div class="col-6 col-md-3 col-xl-3 invisible" data-toggle="appear" data-timeout="295">
            <a class="block text-center bg-danger h-100" id="declined-box" href="javascript:void(0)">
                <div class="font-size-h1 font-w300 text-primary-lighter"><?php echo $database->getTotalDeclinedQuoteCount() ?></div>
                <div class="font-w600 text-uppercase text-white-75">Declined</div>
            </a>
        </div>
    </div>
    <!-- END Quotes filter buttons -->

    <!-- All quotes table -->
    <div class="row invisible" data-toggle="appear" data-timeout="700">
        <div class="col-md-12">
            <div class="block block-rounded block-bordered block-mode-loading-refresh">
                <div class="block-header border-bottom">
                    <h3 class="block-title"><span class="d-none d-lg-block">All Quotes</span></h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option d-none d-lg-inline" id="refresh-all-quotes-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                            <i class="fa fa-sync"></i>
                        </button>
                        <div class="btn-block-option p-0">
                            <input type="text" class="form-control form-control-sm" id="all-quotes-search-box" name="all-quotes-search-box" placeholder="Search..">
                        </div>
                    </div>
                </div>
                <div class="block-content p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless table-vcenter font-size-sm display responsive" id="js-dataTable-allQuotes">
                            <thead>
                                <tr class="text-uppercase">
                                    <th class="font-w700">ID</th>
                                    <th class="font-w700">Customer</th>
                                    <th class="font-w700">Address</th>
                                    <th class="font-w700">Date Time</th>
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
    <!-- END All quotes table -->
</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/select2/js/select2.full.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/jquery.dataTables.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/buttons/dataTables.buttons.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/dataTables.bootstrap4.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/select/dataTables.select.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables-editor/dataTables.editor.js'); ?>
<?php $dm->get_js('js/plugins/datatables/responsive/dataTables.responsive.min.js'); ?>
<!-- Needs to be before datetime-moment include to define 'moment' -->
<?php $dm->get_js('js/plugins/datatables/datetime-moment/moment.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/datetime-moment/datetime-moment.js'); ?>

<!-- Page JS Code -->
<?php $dm->get_js('js/helpers/quotes_statuses.js'); ?>
<?php $dm->get_js('js/pages/quotes_panel_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>