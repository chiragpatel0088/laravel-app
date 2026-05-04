<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>
<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/select2/css/select2.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables/select/select.dataTables.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables/dataTables.bootstrap4.css'); ?>
<?php $dm->get_css('js/plugins/datatables/buttons-bs4/buttons.bootstrap4.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables-editor/css/editor.dataTables.css'); ?>
<?php $dm->get_css('js/plugins/datatables/responsive/responsive.dataTables.min.css'); ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/site_inspection_panel.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Page Content -->
<div class="content">
    <!-- Site inspections all -->
    <div class="row invisible" data-toggle="appear" data-timeout="100">
        <div class="col-md-12">
            <div class="block block-rounded block-bordered block-mode-loading-refresh">
                <div class="block-header border-bottom">
                    <h3 class="block-title"><span class="d-none d-lg-inline">Site Inspections</span></h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option d-none d-lg-inline" id="refresh-all-site-inspections-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                            <i class="fa fa-sync"></i>
                        </button>
                        <div class="btn-block-option p-0">
                            <input type="text" class="form-control form-control-sm" id="all-site-inspections-search-box" name="all-site-inspections-search-box" placeholder="Search..">
                        </div>
                    </div>
                </div>
                <div class="block-content">
                    <div class="table-responsive">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-borderless table-vcenter font-size-sm display responsive" id="js-dataTable-allSiteInspections">
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
    </div>
    <!-- END Site inspections all -->
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
<?php $dm->get_js('js/pages/site_inspections_panel_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>