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
<?php $dm->get_css('js/plugins/datatables/row-reorder/rowReorder.dataTables.min.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-sm-fill font-size-h2 font-w400 mt-2 mb-0 mb-sm-2">Concrete Pump Trucks</h1>
            <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">System</li>
                    <li class="breadcrumb-item">Database</li>
                    <li class="breadcrumb-item active" aria-current="page">Concrete Pump Trucks</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="content">
    <!-- <h2 class="content-heading">Page Subtitle</h2> -->
    <!-- Trucks table -->
    <div class="row">
        <div class="col-md-12">
            <div class="block block-rounded block-bordered block-mode-loading-refresh">
                <div class="block-header border-bottom">
                    <h3 class="block-title">Trucks</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" id="refresh-trucks-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                            <i class="fa fa-sync"></i>
                        </button>
                        <div class="btn-block-option p-0">
                            <input type="text" class="form-control form-control-sm" id="trucks-search-box" name="trucks-search-box" placeholder="Search..">
                        </div>
                    </div>
                </div>
                <div class="block-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless table-vcenter font-size-sm display responsive" id="js-dataTable-allTrucks">
                            <thead>
                                <tr class="text-uppercase">
                                    <th style="width: 4%" class="font-w700"></th>
                                    <th style="width: 4%" class="font-w700"></th>
                                    <th style="width: 4%" class="font-w700"></th>
                                    <th class="font-w700">Truck</th>
                                    <th class="font-w700">Boom</th>
                                    <th class="font-w700">Capacity</th>
                                    <th class="font-w700">Max Speed</th>
                                    <th class="font-w700">Est. Fee</th>
                                    <th class="font-w700">Hourly Rate</th>
                                    <th class="font-w700">Min</th>
                                    <th class="font-w700">Travel Rate</th>
                                    <th class="font-w700">Disposal</th>
                                    <th class="font-w700">Washdown</th>
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
    <!-- END Trucks table -->
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
<?php $dm->get_js('js/plugins/datatables/row-reorder/dataTables.rowReorder.min.js'); ?>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/trucks_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>