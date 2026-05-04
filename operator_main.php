<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>
<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/datatables/dataTables.bootstrap4.css'); ?>
<?php $dm->get_css('js/plugins/datatables/responsive/responsive.dataTables.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables-editor/css/editor.dataTables.css'); ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/operator_main.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Hero -->
<div class="bg-header-dark d-none d-lg-block">
    <div class="content content-full">
        <div class="row pt-3">
            <div class="col-md py-3 d-md-flex align-items-md-center text-center">
                <h1 class="text-white mb-0">
                    <span class="font-w300">Operator Dashboard</span>
                    <span class="font-w400 font-size-lg text-white-75 d-block d-md-inline-block">Welcome <?php echo $session->userinfo['user_firstname']; ?></span>
                </h1>
            </div>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="bg-white">
    <div class="content content-full">
        <div class="col-xl-12">

            <!-- Operator Jobs table -->
            <div class="row invisible" data-toggle="appear" data-timeout="100">
                <div class="col-md-12">
                    <div class="block block-rounded block-bordered block-mode-loading-refresh">
                        <div class="block-header border-bottom">
                            <h3 class="block-title"><span class="d-none d-lg-inline">My </span>Jobs</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option d-none d-lg-inline" id="refresh-operator-jobs-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                                    <i class="fa fa-sync"></i>
                                </button>
                                <div class="btn-block-option p-0">
                                    <input type="text" class="form-control form-control-sm" id="operator-jobs-search-box" name="operator-jobs-search-box" placeholder="Search..">
                                </div>
                            </div>
                        </div>
                        <div class="block-content p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-borderless table-vcenter display responsive font-size-sm" id="js-dataTable-operatorJobs">
                                    <thead>
                                        <tr class="text-uppercase">
                                            <th class="font-w700">ID</th>
                                            <th class="font-w700">Customer</th>
                                            <th class="font-w700">Address</th>
                                            <th class="font-w700">Truck</th>
                                            <th class="font-w700">Type</th>
                                            <th class="font-w700">Cubics</th>
                                            <th class="font-w700">Date Time</th>
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
            <!-- END Operator Jobs table -->

            <!-- Site Inspections table -->
            <div class="row invisible" data-toggle="appear" data-timeout="200">
                <div class="col-md-12">
                    <div class="block block-rounded block-bordered block-mode-loading-refresh">
                        <div class="block-header border-bottom">
                            <h3 class="block-title"><span class="d-none d-lg-block">Site Inspections</span><span class="d-lg-none">Site Ins.</span></h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option d-none d-lg-inline" id="refresh-operator-site-inspections-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                                    <i class="fa fa-sync"></i>
                                </button>
                                <div class="btn-block-option p-0">
                                    <input type="text" class="form-control form-control-sm" id="operator-site-inspections-search-box" name="operator-site-inspections-search-box" placeholder="Search..">
                                </div>
                            </div>
                        </div>
                        <div class="block-content p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-borderless table-vcenter font-size-sm display responsive" id="js-dataTable-operatorSiteInspections">
                                    <thead>
                                        <tr class="text-uppercase">
                                            <th class="font-w700">ID</th>
                                            <th class="font-w700">Customer</th>
                                            <th class="font-w700">Address</th>
                                            <th class="font-w700">Job Date Time</th>
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
            <!-- END Site Inspections table -->

            <!-- Linesman job table -->
            <div class="row invisible" data-toggle="appear" data-timeout="100">
                <div class="col-md-12">
                    <div class="block block-rounded block-bordered block-mode-loading-refresh">
                        <div class="block-header border-bottom">
                            <h3 class="block-title"><span class="d-none d-lg-inline">Linesman </span>Jobs</h3>
                            <div class="block-options">
                                <button type="button" class="btn-block-option d-none d-lg-inline" id="refresh-linesman-jobs-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                                    <i class="fa fa-sync"></i>
                                </button>
                                <div class="btn-block-option p-0">
                                    <input type="text" class="form-control form-control-sm" id="linesman-jobs-search-box" name="linesman-jobs-search-box" placeholder="Search..">
                                </div>
                            </div>
                        </div>
                        <div class="block-content p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-borderless table-vcenter display responsive font-size-sm" id="js-dataTable-linesmanJobs">
                                    <thead>
                                        <tr class="text-uppercase">
                                            <th class="font-w700">ID</th>
                                            <th class="font-w700">Customer</th>
                                            <th class="font-w700">Address</th>
                                            <th class="font-w700">Truck</th>
                                            <th class="font-w700">Type</th>
                                            <th class="font-w700">Cubics</th>
                                            <th class="font-w700">Date Time</th>
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
            <!-- END Site Inspections table -->

        </div>
    </div>
</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/datatables/jquery.dataTables.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/dataTables.bootstrap4.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables-editor/dataTables.editor.js'); ?>
<?php $dm->get_js('js/plugins/datatables/responsive/dataTables.responsive.min.js'); ?>
<!-- Needs to be before datetime-moment include to define 'moment' -->
<?php $dm->get_js('js/plugins/datatables/datetime-moment/moment.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/datetime-moment/datetime-moment.js'); ?>


<!-- Page JS Code -->
<?php $dm->get_js('js/pages/operator_main_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>