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

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-sm-fill font-size-h2 font-w400 mt-2 mb-0 mb-sm-2">Suppliers</h1>
            <nav class="flex-sm-00-auto ml-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">System</li>
                    <li class="breadcrumb-item">Database</li>
                    <li class="breadcrumb-item active" aria-current="page">Suppliers</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="content">
    <!-- <h2 class="content-heading">Page Subtitle</h2> -->
    <!-- suppliers table -->
    <div class="row">
        <div class="col-md-12">
            <div class="block block-rounded block-bordered block-mode-loading-refresh">
                <div class="block-header border-bottom">
                    <h3 class="block-title">Suppliers</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" id="refresh-suppliers-table" data-toggle="block-option" data-action="state_toggle" data-action-mode="demo">
                            <i class="fa fa-sync"></i>
                        </button>
                        <div class="btn-block-option p-0">
                            <input type="text" class="form-control form-control-sm" id="suppliers-search-box" name="suppliers-search-box" placeholder="Search..">
                        </div>
                    </div>
                </div>
                <div class="block-content">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-borderless table-vcenter font-size-sm display responsive" id="js-dataTable-allSuppliers">
                            <thead>
                                <tr class="text-uppercase">
                                    <th style="width: 4%" class="font-w700"></th>
                                    <th style="width: 4%" class="font-w700"></th>
                                    <th class="font-w700">Supplier</th>
                                    <th class="font-w700">Contact</th>
                                    <th class="font-w700">Email</th>
                                    <th class="font-w700">Contact Phone</th>
                                    <th class="font-w700">Mobile</th>
                                    <th class="font-w700">Code</th>
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
    <!-- END suppliers table -->
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

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/suppliers_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>