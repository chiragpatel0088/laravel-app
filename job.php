<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>

<?php
// Page specific configuration
$dm->l_header_fixed               = false;
?>

<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/magnific-popup/magnific-popup.css'); ?>
<?php $dm->get_css('js/plugins/datatables/responsive/responsive.dataTables.min.css'); ?>
<?php $dm->get_css('js/plugins/datatables/row-group/rowGroup.dataTables.min.css'); ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/job.css'); ?>
<?php $dm->get_css('css/pages/job_site_inspections.css'); ?>

<!-- Fixes bug where row group responsiveness cell is shown -->
<?php $dm->get_css('css/pages/jobs_panel.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<?php
// No job id is supplied, we can't even get job details, so we stop here by dying.
if ($session->userinfo['user_level'] == 1) {
    header("Location: operator_main");
} else if (!isset($_GET['id'])) {
    header("Location: jobs_panel");
} else {
    $job_id = $_GET['id'];
    $job_details = $database->getJobDetails($job_id);
    $job_status = $job_details['status'];
    // var_dump($job_status);
    $job_status_name = $job_details['status_name'];
    $status_style_class = $job_details['class_modifiers'];
    $operatorId = $job_details['operator_id'];
    $isSentToOperator = $job_details['sent_to_operator'];
    $isLinesmanJob = $job_details['switchJobColor'];
    // var_dump($isLinesmanJob);

    // Check if user_level = 4 and job_status = 2, 10 or 11
    // if ($session->userinfo['user_level'] == 4 && in_array($job_status, ['2', '10', '11'])) {
    //     header("Location: jobs_panel");
    //     exit;
    // }

    //Updated 2024-08-05
    $linesman_jobs_details = $database->getAllLinesmanJobsByJobId($job_id);
    // var_dump($linesman_jobs_details);
    $allLinesmenSent = false; // initial value = false
    if (!empty($linesman_jobs_details)) { // make sure arry is not empty
        // $allLinesmenSent = true; // assume all linesmen have been assigned
        foreach ($linesman_jobs_details as $job) {
            if ($job['sentToLinesman'] != 1) {
                // var_dump($job['sentToLinesman'] );
                $allLinesmenSent = false;
                break; // once there is a sentToLinsman is not 1, mark "Not Assigned"
            } else {
                $allLinesmenSent = true;
            }
        }
    }
    // var_dump($allLinesmenSent);
}
// Non-edit status detection (Complete, Ready for invoicing and invoiced)
$is_non_edit_status = $job_status == 8 || $job_status == 10 || $job_status == 11;

// Check if job edit mode has been requested
if (isset($_POST['edit'])) {
    $edit_mode_enabled = true;
}
?>

<!-- Hidden variables not submitted to backend -->
<input type="hidden" name="customer-discount" id="customer-discount" value="0">
<input type="hidden" name="concrete-charge" id="concrete-charge" value="0">
<input type="hidden" name="mix-charge" id="mix-charge" value="0">
<input type="hidden" id="job-status" value="<?php echo $job_status ?>">
<input type="hidden" name="hide-dt-site-inspection-buttons" value="<?php echo in_array($job_status, no_status_update_array) ?>">
<input type="hidden" id="job-range" name="job-range" value="<?php echo $job_details['job_range'] ?>">
<input type="hidden" class="form-control" id="travel" name="travel" value="<?php echo number_format($job_details['truck_travel_rate_km'], 2, '.', '') ?>" disabled>
<input type="hidden" class="form-control" id="min" name="min" value="<?php echo number_format($job_details['truck_min'], 2, '.', '') ?>" disabled>
<input type="hidden" class="form-control" id="rate" name="rate" value="<?php echo number_format($job_details['truck_rate'], 2, '.', '') ?>" disabled>

<!-- Hero -->
<div class="bg-image" id="hero-section" style="background-image: url('<?php echo $dm->assets_folder; ?>/media/photos/photo11@2x.jpg');">
    <div class="bg-black-50">
        <div class="content content-full">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="flex-fill font-size-h2 text-white my-2">
                    <div class="d-flex align-content-center flex-wrap">
                        <span class="d-none d-lg-block"><i class="fa fa-toolbox text-white-50 mr-1"></i></span> <span class="d-none d-lg-block">Job Sheet</span>
                    </div>
                </h1>
                <div class="text-right font-size-h2 font-w700 text-white my-2 d-flex align-content-center flex-wrap">
                    <span class="flex-fill p-1 <?php echo $status_style_class; ?> text-uppercase" id="job-status-name"><?php echo $job_status_name; ?></span> <span class="flex-fill p-1" id="job-number-formatted">JX-<?php echo str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="content">

    <!-- Job buttons -->
    <div class="block block-transparent" id="button-section">
        <div class="block-content p-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <?php if ($job_details['quote_id'] != null) { ?>
                    <a href="quote?id=<?php echo $job_details['quote_id'] ?>">
                        <button type="button" class="btn btn-hero-primary mr-1 mb-3">
                            <i class="fa fa-fw fa-file-invoice mr-1"></i> Go to Quote
                        </button>
                    </a>
                <?php } ?>
                <?php if ($job_status != 2 && $job_status != 8 && $job_status != 10  && $job_status != 11) { ?>
                    <form action="process" id="user-assign-operator-form" method="POST">
                        <button type="submit" name="subuserassignoperator" id="assign-operator" class="btn btn-hero-success mr-1 mb-3" <?php
                                                                                                                                        if ($job_details['operator_emailed'] != null) echo 'data-toggle="tooltip" data-placement="top" title="Sent: ' . date('d/m/Y H:i', strtotime($job_details['operator_emailed'])) . '"'
                                                                                                                                        ?>>
                            <i class="fa fa-fw fa-user-plus mr-1"></i> Assign<span class="d-none d-md-inline"> to Operator</span>
                            <?php if ($isSentToOperator == '0') { ?>
                                <span class="badge badge-danger">Not Assigned</span>
                            <?php } ?>
                        </button>
                    </form>
                <?php } ?>
                <?php if ($isLinesmanJob == "1" && $job_status != 2 && $job_status != 8 && $job_status != 10  && $job_status != 11) { ?>
                    <button id="assign-linesmen" class="btn btn-hero-warning mr-1 mb-3">
                        <i class="fa fa-fw fa-user-plus mr-1"></i> Assign<span class="d-none d-md-inline"> to Linesman</span>
                        <?php if ($allLinesmenSent != true) { ?>
                            <span class="badge badge-danger">Not Assigned</span>
                        <?php } ?>
                    </button>
                <?php } ?>
                <?php if ($job_status != 8 && $job_status != 10  && $job_status != 11 && ($session->userinfo['user_level'] == 3 || $session->userinfo['user_level'] == 2 || $session->userinfo['user_level'] == 4)) { ?>
                    <div class="dropdown">
                        <button type="button" class="btn btn-hero-info dropdown-toggle mr-1 mb-3" id="dropdown-send-job-emails" disabled data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-fw fa-mail-bulk mr-1"></i> <span class="d-none d-md-inline">Send job via </span>Email
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdown-send-job-emails">
                            <a class="dropdown-item" href="javascript:void(0)" id="email-job-to-customer">
                                <div class="row">
                                    <span class="col-6">Customer</span>
                                    <span class="col-6 text-right">
                                        <?php if ($job_details['customer_emailed'] != null)
                                            echo '<i class="fa fa-check text-success" data-toggle="tooltip" data-placement="right" title="Sent: ' .
                                                date('d/m/Y h:i A', strtotime($job_details['customer_emailed'])) . '"></i>';
                                        ?>
                                    </span>
                                </div>
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" id="email-job-to-layer">
                                <div class="row">
                                    <span class="col-6">Layer</span>
                                    <span class="col-6 text-right">
                                        <?php if ($job_details['layer_emailed'] != null)
                                            echo '<i class="fa fa-check text-success" data-toggle="tooltip" data-placement="right" title="Sent: ' .
                                                date('d/m/Y h:i A', strtotime($job_details['layer_emailed'])) . '"></i>';
                                        ?>
                                    </span>
                                </div>
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" id="email-job-to-supplier">
                                <div class="row">
                                    <span class="col-6">Supplier</span>
                                    <span class="col-6 text-right">
                                        <?php if ($job_details['supplier_emailed'] != null)
                                            echo '<i class="fa fa-check text-success" data-toggle="tooltip" data-placement="right" title="Sent: ' .
                                                date('d/m/Y h:i A', strtotime($job_details['supplier_emailed'])) . '"></i>';
                                        ?>
                                    </span>
                                </div>
                            </a>

                            <a class="dropdown-item" href="javascript:void(0)" id="email-job-to-foreman">
                                <div class="row">
                                    <span class="col-6">Foreman</span>
                                    <span class="col-6 text-right">
                                        <?php if ($job_details['foreman_emailed'] != null)
                                            echo '<i class="fa fa-check text-success" data-toggle="tooltip" data-placement="right" title="Sent: ' .
                                                date('d/m/Y h:i A', strtotime($job_details['foreman_emailed'])) . '"></i>';
                                        ?>
                                    </span>
                                </div>
                            </a>

                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="javascript:void(0)" id="email-job-to-all">Send to All</a>
                        </div>
                    </div>
                <?php } ?>
                <?php if ($job_status != 2 && $job_status != 8 && $job_status != 10  && $job_status != 11) { ?>
                    <button type="submit" name="subusercanceljob" id="user-cancel-job" data-toggle="modal" data-target="#modal-user-cancel-job" class="btn btn-hero-danger mr-1 mb-3">
                        <i class="fa fa-fw fa-times mr-1"></i> Cancel<span class="d-none d-md-inline"> Job</span>
                    </button>
                <?php } ?>
                <button type="button" id="print-job" class="btn btn-hero-secondary mr-1 mb-3" disabled>
                    <i class="fa fa-fw fa-print mr-1"></i> Print
                </button>

                <button type="submit" name="subcopycurrentjob" value="<?php echo $_GET['id'] ?>" class="btn btn-hero-info mr-1 mb-3" data-toggle="modal" data-target="#modal-copy-job">
                    <i class="fa fa-fw fa-copy mr-1"></i> Copy
                </button>

                <?php if (($session->userinfo['user_level'] == 3 || $session->userinfo['user_level'] == 2) && $is_non_edit_status && !isset($edit_mode_enabled)) { ?>
                    <button value="<?php echo $_GET['id'] ?>" class="btn btn-hero-info mr-1 mb-3 bg-xpro" data-toggle="modal" data-target="#modal-uncomplete-job">
                        <i class="fa fa-fw fa-undo-alt mr-1"></i> Undo Completed Job
                    </button>
                <?php } ?>

                <?php if ($job_status == 2 && ($session->userinfo['user_level'] == 3 || $session->userinfo['user_level'] == 2)) { ?>
                    <form action="process" method="POST">
                        <button type="submit" name="subreinstate" value="<?php echo $_GET['id'] ?>" class="btn btn-hero-primary mr-1 mb-3">
                            <i class="fa fa-fw fa-trash-restore-alt mr-1"></i> Reinstate
                        </button>
                    </form>
                <?php } ?>

                <?php if ($job_status == 2 && $session->userinfo['user_level'] == 3) { ?>
                    <button id="cancelJob" value="<?php echo $_GET['id'] ?>" style="background-color: #CC0000" class="btn btn-hero-primary mr-1 mb-3">
                        <i class="fa fa-fw fa-trash-restore-alt mr-1"></i> Cancel
                    </button>
                <?php } ?>

                <!-- Display edit button if the status is complete or above -->
                <?php if (($session->userinfo['user_level'] == 3 || $session->userinfo['user_level'] == 2) && $is_non_edit_status && !isset($edit_mode_enabled)) { ?>

                    <form action="./job?id=<?php echo $_GET['id'] ?>" method="POST">
                        <button type="submit" name="edit" class="btn btn-hero-primary mr-1 mb-3 bg-xsmooth">
                            <i class="fa fa-fw fa-pen-alt mr-1"></i> Edit
                        </button>
                    </form>

                <?php } else if (isset($edit_mode_enabled)) { ?>
                    <a href="./job?id=<?php echo $_GET['id'] ?>">
                        <button type="button" class="btn btn-hero-primary mr-1 mb-3 bg-xsmooth">
                            <i class="fa fa-fw fa-pen-alt mr-1"></i> Exit Edit Mode
                        </button>
                    </a>
                <?php }
                if ($job_status == 8 && in_array($session->userinfo['user_level'], ['3', '2'])) { ?>
                    <button type="button" id="ready-for-invoicing" class="btn btn-hero-info bg-gd-aqua mr-1 mb-3" data-toggle="modal" data-target="#modal-ready-for-invoicing">
                        <i class="fa fa-fw fa-file-invoice-dollar mr-1"></i> Ready for Invoicing
                    </button>
                <?php } ?>
            </div>
        </div>
    </div>
    <!-- END Job buttons -->

    <!-- Invoice data section -->
    <?php
    if (($session->userinfo['user_level'] == 3 || $session->userinfo['user_level'] == 2)) {
        if ($job_status == 10 || $job_status == 11)  require 'invoice_data_section.php';
    }
    ?>
    <!-- END Invoice data section -->

    <div class="block block-rounded block-bordered invisible" data-toggle="appear" data-timeout="200" id="form-section">
        <div class="block-content">
            <form action="process" method="POST" id="job-form">
                <!-- Job details -->
                <div class="row push mb-0">
                    <!-- Job cancelled reason and date -->
                    <?php if ($job_status == 2) { ?>
                        <div class="col-lg-12">
                            <div class="alert alert-danger d-flex align-items-center justify-content-between" role="alert">
                                <div class="flex-fill mr-3">
                                    <p class="mb-0">This job was cancelled. Reason: <strong><?php echo $job_details['cancel_reason'] ?></strong></p>
                                </div>
                                <div class="flex-00-auto">
                                    <i class="fa fa-fw fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <?php if ($job_status == 8 || $job_status == 10 || $job_status == 11) { ?>
                        <!-- Completion date -->
                        <div class="col-lg-12">
                            <div class="alert alert-success text-center" role="alert">
                                <p class="mb-0 font-w600"><span class='d-none d-md-inline'>Job </span>Completed: <?php echo date("d/m/Y h:i A", strtotime($job_details['complete_date'])) ?></p>
                            </div>
                        </div>
                        <!-- END Completion date -->
                    <?php } ?>
                    <?php if (($job_status == 8 || $job_status == 10 || $job_status == 11)  && !is_null($job_details['invoiced_date'])) { ?>
                        <!-- Job Invoiced -->
                        <div class="col-lg-12">
                            <div class="alert alert-success text-center" role="alert">
                                <p class="mb-0 font-w600"><span class='d-none d-md-inline'>Job </span>Invoiced: <?php echo date("d/m/Y h:i A", strtotime($job_details['invoiced_date'])) ?></p>
                            </div>
                        </div>
                        <!-- END Job Invoiced -->
                    <?php } ?>

                    <!-- Update button repeated -->
                    <?php if ($job_status != 2 && $job_status != 10  && $job_status != 11 && $job_status != 8) { ?>
                        <div class="col-lg-12 mb-3">
                            <div class="d-flex justify-content-center">
                                <button type="submit" name="switchJobColor" class="btn <?php echo ($job_details['switchJobColor'] == '0') ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php if ($job_details['switchJobColor'] == 0): ?>
                                        Turn To Linesman Job
                                    <?php elseif ($job_details['switchJobColor'] == 1): ?>
                                        Back To Ordinary Job
                                    <?php endif ?>
                                </button>
                                <button type="submit" name="subupdatejob" class="btn btn-success ml-2">
                                    <i class="fa fa-check-circle mr-1"></i> Update Job
                                </button>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <div class="row">
                                <div class="col">
                                    <div class="custom-control custom-checkbox custom-control-info g-default custom-control-lg custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="am-check" name="am-check" value="1" <?php echo $job_details['am_check'] == 1 ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="am-check">AM Check</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="custom-control custom-checkbox custom-control-dark custom-control-lg custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="pm-check" name="pm-check" value="1" <?php echo $job_details['pm_check'] == 1 ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="pm-check">PM Check</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="custom-control custom-checkbox custom-control-warning custom-control-lg custom-control-inline">
                                        <input type="checkbox" class="custom-control-input" id="passed-check" name="passed-check" value="1" <?php echo $job_details['passed_check'] == 1 ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="passed-check">Passed</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col">
                                <label for="job-date">Job Date <span class="text-danger">*</span></label>
                                <input type="text" class="js-datepicker form-control bg-white" id="job-date" readonly="true" name="job-date" data-week-start="1" data-autoclose="true" data-today-highlight="true" data-date-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" value="<?php echo date("d/m/Y", strtotime($job_details['job_date'])); ?>">
                            </div>
                            <div class="col">
                                <label for="job-timing">Start Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control bg-white" id="job-timing" name="job-timing" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="false" value="<?php echo $job_details['job_timing'] ?>">
                            </div>
                            <!-- Invoice number when job is complete -->
                            <?php if ($job_status == 11) { ?>
                                <div class="col">
                                    <label for="invoice-number">Invoice No.</label>
                                    <input type="text" class="form-control" id="invoice-number" name="invoice-number" maxlength="50" value="<?php echo $job_details['invoice_number'] ?>">
                                </div>
                                <!-- END Invoice number when job is complete -->
                            <?php } ?>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-12">
                                <label for="job-address-1">Address <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-8">
                                <input type="text" class="js-maxlength form-control" id="job-address-1" name="job-address-1" placeholder="Start typing an address.." maxlength="100" value="<?php echo $job_details['job_addr_1'] ?>">
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-address-2" name="job-address-2" placeholder="Address 2" maxlength="100" value="<?php echo $job_details['job_addr_2'] ?>">
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-suburb" name="job-suburb" placeholder="Suburb" maxlength="100" value="<?php echo $job_details['job_suburb'] ?>">
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-city" name="job-city" placeholder="City" maxlength="100" value="<?php echo $job_details['job_city'] ?>">
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-post-code" name="job-post-code" placeholder="Post Code" maxlength="10" value="<?php echo $job_details['job_post_code'] ?>">
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-4">
                                <label for="range-address">Range</label>
                                <input type="text" class="js-maxlength form-control" id="range-address" name="range-address" placeholder="Approx distance (km)" maxlength="100" value="<?php echo $job_details['job_range'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Job details -->

                <!-- Job Specifications section -->
                <div class="row push">
                    <div class="col-lg-12">
                        <div class="form-group form-row">
                            <div class="col-4">
                                <label for="job-type">Job Type <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-4">
                                <label for="cubics">Cubics <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-4">
                                <label for="mpa">MPa <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-4">
                                <select class="js-select2 form-control" id="job-type-select" name="job-type-select" style="width: 100%;">
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="js-maxlength form-control" id="cubics" name="cubics" maxlength="8" value="<?php echo $job_details['cubics'] ?>">
                                    <div class="input-group-append d-none d-xl-block d-lg-block d-md-block d-sm-none d-xs-none">
                                        <span class="input-group-text">
                                            m<sup>3</sup>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="js-maxlength form-control" id="mpa" name="mpa" maxlength="5" value="<?php echo $job_details['mpa'] ?>">
                                    <div class="input-group-append d-none d-xl-block d-lg-block d-md-block d-sm-none d-xs-none">
                                        <span class="input-group-text">
                                            MPa
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-6">
                                <label for="concrete-type-select">Concrete Type <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="concrete-type-select" name="concrete-type-select" style="width: 100%;">
                                </select>
                            </div>
                            <div class="col-6 d-none">
                                <label for="mix-type-select">Extra Concrete Types</label>
                                <select class="js-select2 form-control" id="mix-type-select" name="mix-type-select" style="width: 100%;">
                                </select>
                            </div>

                        </div>
                        <div class="form-group form-row">
                            <div class="col-4">
                                <label for="truck-select">Truck <span class="text-danger">*</span> <?php if ($job_status != 2 && $job_status != 10  && $job_status != 11 && $job_status != 8) { ?><a class="badge badge-danger" id="unassign-truck" href="javascript:void(0)">Unassign Truck</a><?php } ?></label>
                            </div>
                            <div class="col-4">
                                <label for="boom">Boom Size</label>
                            </div>
                            <div class="col-4">
                                <label for="capacity">Capacity Per Hour</label>
                            </div>
                            <div class="col-4">
                                <select class="js-select2 form-control" id="truck-select" name="truck-select" style="width: 100%;">
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="boom" name="boom" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            m
                                        </span>
                                    </div>
                                </div>

                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="capacity" name="capacity" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            m<sup>3</sup>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- END Job Specifications section -->

                <!-- Customer Info -->
                <div class="row push mb-0">
                    <div class="col-lg-12">
                        <div class="form-group form-row">
                            <div class="col-lg-6 col-xl-8">
                                <label for="customer-select">Customer <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="customer-select" name="customer-select" style="width: 100%;">
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-lg-6">
                                <address>
                                    <strong><span id="contact-name">Customer info loading..</span></strong><br>
                                    <a id="cust-email" href="mailto:#"></a><br>
                                    <abbr title="Landline Phone">Phone:</abbr> <a id="cust-ph"></a><br>
                                    <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="cust-mob-ph"></a>
                                </address>
                            </div>
                            <div class="col-lg-6">
                                <!-- Customer details -->
                                <div>
                                    <address>
                                        <strong><span id="cust-company-name"></span></strong><br>
                                        <span id="cust-addr-1"></span><br>
                                        <span id="cust-addr-2"></span><br>
                                        <span id="cust-addr-3"></span>
                                    </address>
                                </div>
                                <!-- END Customer details -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Customer Info -->

                <!-- Supplier and layer Info -->
                <div class="row push mb-0">
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label for="layer-select">Layer <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="layer-select" name="layer-select" style="width: 100%;">
                                </select>
                                <address class="mt-2">
                                    <strong><span id="layer-name">Please select a layer..</span></strong><br>
                                    <a id="layer-email" href="mailto:#"></a><br>
                                    <abbr title="Landline Phone">Phone:</abbr> <a id="layer-ph"></a><br>
                                    <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="layer-mob-ph"></a>
                                </address>
                            </div>
                            <div class="col-lg-6">
                                <label for="supplier-select">Supplier <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="supplier-select" name="supplier-select" style="width: 100%;">
                                </select>
                                <!-- Supplier details -->
                                <address class="mt-2">
                                    <strong><span id="supplier-name">Please select a supplier..</span></strong><br>
                                    <a id="supplier-email" href="mailto:#"></a><br>
                                    <abbr title="Landline Phone">Phone:</abbr> <a id="supplier-ph"></a><br>
                                    <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="supplier-mob-ph"></a>
                                </address>
                                <!-- END Supplier details -->
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Supplier and layer Info -->


                <!-- Operator and Foreman Info -->
                <div class="row push mb-0">
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label for="operator-select">Operator <?php if ($job_status != 2 && $job_status != 10  && $job_status != 11 && $job_status != 8) { ?><a class="badge badge-danger" id="unassign-operator" href="javascript:void(0)">Unassign Operator</a><?php } ?></label>
                                <select class="js-select2 form-control" id="operator-select" name="operator-select" style="width: 100%;">
                                </select>

                                <div class="mt-2">
                                    <abbr title="Contact Phone">Phone:</abbr> <a id="operator-ph"></a><br>
                                    <a id="operator-email" href="mailto:#"></a><br>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <label for="operator-select">Foreman <?php if ($job_status != 2 && $job_status != 10  && $job_status != 11 && $job_status != 8) { ?><a class="badge badge-danger" id="unassign-foreman" href="javascript:void(0)">Unassign Foreman</a><?php } ?></label>
                                <select class="js-select2 form-control" id="foreman-select" name="foreman-select" style="width: 100%;">
                                </select>

                                <div class="mt-2">
                                    <strong><span id="foreman-company"></span></strong><br>
                                    <abbr title="Contact Phone">Phone:</abbr> <a id="foreman-ph"></a><br>
                                    <a id="foreman-email" href="mailto:#"></a><br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Linesman-->
                <?php if ($isLinesmanJob == 1) { ?>
                <!-- <div class="row push mb-0" <?php if ($is_non_edit_status) echo "style='display: none;'" ?>> -->
                <div class="row push mb-0">
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <div class="col-lg-6">
                                <label for="operator-select">Linesman </label>
                                <select class="js-select2 form-control" id="linesman-select" style="width: 100%;" name="linesman-select[]" multiple="multiple">
                                </select>
                                <!--                                <div class="mt-2">-->
                                <!--                                    <abbr title="Contact Phone">Phone:</abbr> <a id="linesman-ph"></a><br>-->
                                <!--                                    <a id="linesman-email" href="mailto:#"></a><br>-->
                                <!--                                </div>-->
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <!-- END Linseman Info -->

                <!-- Optional Info -->
                <div class="row push">
                    <div class="col-lg-12 col-xl-12">
                        <div class="form-group">
                            <label for="job-instructions">Job and OH&S Instructions</label>
                            <textarea class="js-maxlength form-control" id="job-instructions" name="job-instructions" rows="6" placeholder="What job related instructions are there?" maxlength="1000"><?php echo $job_details['job_instructions'] ?></textarea>
                        </div>


                        <!-- Site Inspection section of job -->
                        <div class="form-row">
                            <div class="col-md-12 col-sm-12">
                                <span class="font-w600">Site Inspection(s)</span>
                                <table class="table table-sm table-vcenter" id="js-dataTable-jobSiteInspections">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center" style="width: 50px;"></th>
                                            <th>Assigned Operator</th>
                                            <th>Status</th>
                                            <th class="d-none d-sm-table-cell">Date Created</th>
                                            <th>Open</th>
                                            <th> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- END Site Inspection section of job -->

                        <div class="form-group">
                            <label for="ohs-instructions">Admin Instructions</label>
                            <textarea class="js-maxlength form-control" id="ohs-instructions" name="ohs-instructions" rows="6" placeholder="" maxlength="900"><?php echo $job_details['ohs_instructions'] ?></textarea>
                        </div>
                    </div>
                </div>
                <!-- END Optional Info -->

                <!-- Operator and linesman job section -->
                <!-- Operator section -->
                <?php if ($job_status == 8 || $job_status == 10 || $job_status == 11) { ?>
                    <!-- Onsite job information -->
                    <?php if ($operatorId != null) : ?>
                        <div class="operator-job-section">
                            <h3 class="text-primary">Operator Job:</h3>
                            <div class="row push mb-0">
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="" for="actual-job-timing">
                                            Actual Job Start Time <span class="text-danger">*</span>
                                        </label>
                                        <input type="time" required class="form-control bg-white operator-job-field" id="actual-job-timing" name="actual-job-timing" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="false" value="<?php echo $job_details['actual_job_timing'] ?>">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="first-concrete-mixer-arrival-time">First Mixer Arrival Time <span class="text-danger">*</span></label>
                                        <input type="time" required class="form-control bg-white operator-job-field" id="first-concrete-mixer-arrival-time" name="first-concrete-mixer-arrival-time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="false" value="<?php if (!is_null($job_details['first_mixer_arrival_time'])) echo $job_details['first_mixer_arrival_time']; ?>">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="actual-cubics">
                                            Actual Cubics
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="js-maxlength form-control operator-job-field" id="actual-cubics" name="actual-cubics" maxlength="8" value="<?php if (!is_null($job_details['actual_cubics'])) echo $job_details['actual_cubics'] ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    m<sup>3</sup>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label for="job-finish-time">Finish Time <span class="text-danger">*</span></label>
                                        <input type="time" required class="form-control bg-white operator-job-field" id="job-finish-time" name="job-finish-time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="false" value="<?php if (!is_null($job_details['job_time_finished'])) echo $job_details['job_time_finished']; ?>">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="d-block">Pump Washdown <span class="text-danger">*</span></label>
                                        <div class="custom-control custom-radio custom-control-inline custom-control-lg">
                                            <input type="radio" class="custom-control-input operator-job-field" id="onsite-washout1" value="1" name="onsite-washout" <?php if (!is_null($job_details['onsite_washout']) && $job_details['onsite_washout'] == 1) echo "checked"; ?>>
                                            <label class="custom-control-label" for="onsite-washout1">Yes</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline custom-control-lg">
                                            <input type="radio" class="custom-control-input operator-job-field" id="onsite-washout2" value="0" name="onsite-washout" <?php if (!is_null($job_details['onsite_washout']) && $job_details['onsite_washout'] == 0) echo "checked"; ?>>
                                            <label class="custom-control-label" for="onsite-washout2">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-12">
                                    <div class="form-group">
                                        <label class="d-block">Concrete Disposal <span class="text-danger">*</span></label>
                                        <div class="custom-control custom-radio custom-control-inline custom-control-lg">
                                            <input type="radio" class="custom-control-input operator-job-field" id="onsite-disposal1" value="1" name="onsite-disposal" <?php if (!is_null($job_details['onsite_disposal']) && $job_details['onsite_disposal'] == 1) echo "checked"; ?>>
                                            <label class="custom-control-label" for="onsite-disposal1">Yes</label>
                                        </div>
                                        <div class="custom-control custom-radio custom-control-inline custom-control-lg">
                                            <input type="radio" class="custom-control-input operator-job-field" id="onsite-disposal2" value="0" name="onsite-disposal" <?php if (!is_null($job_details['onsite_disposal']) && $job_details['onsite_disposal'] == 0) echo "checked"; ?>>
                                            <label class="custom-control-label" for="onsite-disposal2">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="operator-job-notes">Job Notes</label>
                                        <textarea class="js-maxlength form-control operator-job-field" id="operator-job-notes" name="operator-job-notes" rows="6" placeholder="Add any notes about this job" maxlength="500"><?php if (!is_null($job_details['operator_notes'])) echo $job_details['operator_notes']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- End Operator job section -->

                    <!-- END Onsite job information -->
                <?php } ?>

                <!-- Linesman jobs section -->
                <?php if ($job_details['isLinesmanJob'] == '1' && $isLinesmanJob == "1") : ?>
                    <div class="linesman-jobs-section">
                        <h3 class="text-primary">Linesman Jobs:</h3>
                        <?php foreach ($linesman_jobs_details as $linesman_job) : ?>
                            <h4 class="text-success"><?php echo $linesman_job['user_firstname'] . " " . $linesman_job['user_lastname'] ?></h3>
                                <!-- Hidden input for linesman_jobs.id -->
                                <input type="hidden" name="linesman_job_id[]" value="<?php echo $linesman_job['id']; ?>">

                                <!-- <div class="row mb-3">
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label class="" for="linesman-actual-job-timing-<?php echo $linesman_job['id']; ?>">
                                                    Actual Job Start Time <span class="text-danger">*</span>
                                                </label>
                                                <input type="time" required class="form-control bg-white operator-job-field" id="linesman-actual-job-timing-<?php echo $linesman_job['id']; ?>" name="linesman-actual-job-timing[<?php echo $linesman_job['id']; ?>]" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="false" value="<?php echo $linesman_job['actual_job_timing'] ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="linesman-job-finish-time-<?php echo $linesman_job['id']; ?>">Finish Time <span class="text-danger">*</span></label>
                                                <input type="time" required class="form-control bg-white operator-job-field" id="linesman-job-finish-time-<?php echo $linesman_job['id']; ?>" name="linesman-job-finish-time[<?php echo $linesman_job['id']; ?>]" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="false" value="<?php if (!is_null($linesman_job['job_time_finished'])) echo $linesman_job['job_time_finished']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12">
                                            <div class="form-group">
                                                <label for="linesman-line-size-select-<?php echo $linesman_job['id']; ?>">Line Size(inch) <span class="text-danger">*</span></label>
                                                <select required class="linesman-line-size-select js-select2 form-control" id="linesman-line-size-select-<?php echo $linesman_job['id']; ?>" name="linesman-line-size-select[<?php echo $linesman_job['id']; ?>]" style="width: 100%;">
                                                    <option value="<?php
                                                                    if (!in_array($linesman_job['line_size_select'], [0, 2, 2.5, 3, 3.5, 4])) {
                                                                        echo $linesman_job['line_size_select'];
                                                                    } else if ($linesman_job['line_size_select'] == 0) {
                                                                        echo "";
                                                                    }
                                                                    ?>" disabled selected>
                                                        <?php
                                                        if (!in_array($linesman_job['line_size_select'], [0, 2, 2.5, 3, 3.5, 4])) {
                                                            echo $linesman_job['line_size_select'];
                                                        } else if ($linesman_job['line_size_select'] == 0) {
                                                            echo "Select line size";
                                                        }
                                                        ?>
                                                    </option>
                                                    <option value="2" <?php echo ($linesman_job['line_size_select'] == '2') ? 'selected' : ''; ?>>2</option>
                                                    <option value="2.5" <?php echo ($linesman_job['line_size_select'] == '2.5') ? 'selected' : ''; ?>>2.5</option>
                                                    <option value="3" <?php echo ($linesman_job['line_size_select'] == '3') ? 'selected' : ''; ?>>3</option>
                                                    <option value="3.5" <?php echo ($linesman_job['line_size_select'] == '3.5') ? 'selected' : ''; ?>>3.5</option>
                                                    <option value="4" <?php echo ($linesman_job['line_size_select'] == '4') ? 'selected' : ''; ?>>4</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="linesman-job-notes-<?php echo $linesman_job['id']; ?>">Job Notes</label>
                                                <textarea class="js-maxlength form-control operator-job-field" id="linesman-job-notes-<?php echo $linesman_job['id']; ?>" name="linesman-job-notes[<?php echo $linesman_job['id']; ?>]" rows="6" placeholder="Add any notes about this job" maxlength="500"><?php if (!is_null($linesman_job['linesman_notes'])) echo $linesman_job['linesman_notes']; ?></textarea>
                                            </div>
                                        </div>
                                    </div> -->
                            <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <!-- End Linesman jobs section -->

                <!-- Charge details -->
                <!-- <h2 class="content-heading pt-0">Job</h2> -->
                <div class="row push d-none">
                    <div class="col-lg-4">
                        <p class="text-muted">
                            Job calculations
                        </p>
                    </div>
                    <div class="col-lg-8 col-xl-6">
                        <div class="form-group row">
                            <div class="col-6">
                                <div class="custom-control custom-checkbox custom-control-primary custom-control-lg mb-1">
                                    <input type="checkbox" class="custom-control-input" id="cubic-charge" name="cubic-charge" <?php echo $job_details['cubic_charge'] == 1 ? "checked" : ""; ?>>
                                    <label class="custom-control-label" for="cubic-charge" id="charge-label">Cubic Metre Charge</label>
                                </div>
                            </div>
                            <div class="col-6">Estimated Pump Time: <strong><span id="estimated-pump-time-text"><?php echo $job_details['estimate_pump_time'] ?></span></strong> minutes</div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-12">
                                <table class="table table-bordered table-vcenter">
                                    <thead>
                                        <tr>
                                            <th> </th>
                                            <th class="d-sm-table-cell text-right" style="width: 30%;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="font-w600">
                                                Establishment Fee
                                            </td>
                                            <td class="d-sm-table-cell text-right">
                                                <input type="text" class="form-control text-right charge-table-input" id="establishment-fee" name="establishment-fee" value="<?php echo $job_details['establishment_fee'] ?>" maxlength="6">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600">
                                                <span class="table-rate-description">** Rate</span> <i class="fa fa-info-circle" id="quoted-rate-tooltip" data-toggle="tooltip" data-placement="right" title="Tooltip"></i>
                                            </td>
                                            <td class="d-sm-table-cell text-right">
                                                <input type="text" class="form-control text-right charge-table-input bg-white" id="cubic-rate" name="cubic-rate" readonly value="<?php echo $job_details['cubic_rate'] ?>" maxlength="10">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600">
                                                Travel Fee <i class="fa fa-info-circle" id="travel-fee-tooltip" data-toggle="tooltip" data-placement="right" title="Tooltip"></i>
                                            </td>
                                            <td class="d-sm-table-cell text-right">
                                                <input type="text" class="form-control text-right charge-table-input" id="travel-fee" name="travel-fee" value="<?php echo $job_details['travel_fee'] ?>" maxlength="10">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600 text-uppercase bg-info-light">
                                                Subtotal
                                            </td>
                                            <td class="d-sm-table-cell text-right bg-info-light">
                                                <span id="sub-total">$0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600">
                                                <a id="customer-discount-tooltip" class="link-fx" data-toggle="tooltip" data-placement="top" title="Click to apply customer discount rate">Discount (%) <i id="discount-alert" class="fa fa-exclamation-circle mr-1 text-info"></i></a>
                                            </td>
                                            <td class="d-sm-table-cell text-right">
                                                <input type="text" class="form-control text-right charge-table-input" maxlength="6" placeholder="0" id="discount" name="discount" value="<?php echo $job_details['discount'] ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600 text-uppercase bg-info-lighter">
                                                Subtotal after Discount
                                            </td>
                                            <td class="d-sm-table-cell text-right bg-info-lighter">
                                                <span id="sub-total-inc-discount">$0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600 text-uppercase">
                                                GST 15%
                                            </td>
                                            <td class="d-sm-table-cell text-right">
                                                <span id="gst">$0.00</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w700 text-uppercase bg-body-light">
                                                Total Cost INC GST
                                            </td>
                                            <td class="d-sm-table-cell text-right font-w700 bg-body-light">
                                                <span id="cubic-cost">$0.00</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="form-group form-row">
                                    <div class="col-12">
                                        <p class="text-right text-primary-dark" style="display: none" id="total-discount"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Charge details -->


                <!-- Submit -->
                <div class="row push">
                    <div class="col-lg-12">
                        <div class="form-group text-center">
                            <?php if ($job_status != 2 && $job_status != 10  && $job_status != 11 && $job_status != 8) { ?>
                                <button type="submit" name="subupdatejob" class="btn btn-success">
                                    <i class="fa fa-check-circle mr-1"></i> Update Job
                                </button>
                            <?php } else if (isset($edit_mode_enabled)) { ?>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-reason-edit-job">
                                    <i class="fa fa-check-circle mr-1"></i> Admin Update
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <!-- END Submit -->

                <!-- Hidden form values to be submitted to the server -->
                <input type="hidden" name="job-id" id="job_id" value="<?php echo $_GET['id'] ?>">
                <input type="hidden" name="estimated-pump-time" id="estimated-pump-time" value="<?php echo $job_details['estimate_pump_time'] ?>">
            </form>
        </div>
    </div>

    <?php if ($is_non_edit_status) { ?>
        <!-- Edit history table -->
        <div class="block block-rounded block-bordered block-themed" id="job-history-log">
            <div class="block-header bg-xsmooth">
                <h3 class="block-title"><i class="fa fa-fw fa-pen-alt mr-1"></i> Edit History</h3>
                <div class="block-options">
                    <div class="block-options-item">
                        <button type="button" class="btn btn-sm btn-primary" id="show-all">Show All</button>
                    </div>
                    <div class="block-options-item">
                        <button type="button" class="btn btn-sm btn-primary" id="show-after-complete">After Job Complete</button>
                    </div>
                    <!-- What edits to show toggle -->
                    <input type="hidden" value="0" id="edit-history-mode">
                </div>
            </div>
            <div class="block-content p-0 mb-0">
                <div class="table-responsive">
                    <table class="table table-sm font-size-sm table-vcenter display responsive mb-0" id="js-dataTable-jobEditHistory">
                        <thead class="thead-dark">
                            <tr>
                                <th>Edit Type</th>
                                <th>Change</th>
                                <th>User</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- END Edit history table -->
    <?php } ?>

</div>
<!-- END Page Content -->

<!-- END Page Content -->

<!-- Job print layout -->
<?php require 'job_print_layout.php'; ?>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/jquery-validation/jquery.validate.min.js'); ?>
<?php $dm->get_js('js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/select/dataTables.select.min.js'); ?>
<?php $dm->get_js('js/plugins/magnific-popup/jquery.magnific-popup.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/responsive/dataTables.responsive.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/row-group/dataTables.rowGroup.min.js'); ?>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/job_page.js'); ?>
<?php $dm->get_js('js/pages/job_page_emailing.js'); ?>
<?php $dm->get_js('js/pages/job_page_site_inspections.js'); ?>
<?php if ($is_non_edit_status) $dm->get_js('js/helpers/invoice_process_helpers.js'); ?>
<?php $dm->get_js('js/helpers/cost_calculations.js'); ?>
<?php if ($is_non_edit_status) $dm->get_js('js/modals/modal_ready_for_invoicing.js'); ?>
<?php if ($is_non_edit_status) $dm->get_js('js/pages/job_page_edit_history.js'); ?>

<!-- Make form elements readonly if it's a complete or higher job, or edit mode is not enabled -->
<?php if ($is_non_edit_status && !isset($edit_mode_enabled)) $dm->get_js('js/helpers/disable_form_elements.js'); ?>

<!-- Ready for invoicing jobs only -->
<?php if ($job_status == 10 || $job_status == 11) $dm->get_js('js/pages/job_page_invoice_data_section.js'); ?>

<!-- Modals -->
<?php require 'inc/modals/user_cancel_job.php'; ?>
<!-- Only shown if it's a complete job -->
<?php if ($job_status == 8) {
    require 'inc/modals/ready_for_invoicing.php';
    require 'inc/modals/uncomplete_job.php';
} ?>
<?php require 'inc/modals/reason_edit_job.php'; ?>
<?php require 'inc/modals/reason_edit_invoice.php'; ?>
<?php require 'inc/modals/copy_job.php'; ?>

<?php require 'inc/_global/views/footer_end.php'; ?>