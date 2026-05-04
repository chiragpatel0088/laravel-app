<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>

<?php
// Page specific configuration
$dm->l_header_fixed               = false;
$dm->l_m_content                  = 'boxed';
?>

<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css'); ?>
<?php $dm->get_css('js/plugins/magnific-popup/magnific-popup.css'); ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/job.css'); ?>
<?php $dm->get_css('css/pages/operator_job.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<?php
// No job id is supplied, we can't even get job details, so we stop here by dying.
if (!isset($_GET['job-id']) && !isset($_GET['enc_id'])) {
    header("Location: operator_main");
} else {
    if (isset($_GET['job-id']) && isset($_GET['linesman-job-id'])) {
        $job_id = $_GET['job-id'];
        $linesman_job_id = $_GET['linesman-job-id'];
    } else {
        $job_id = $database->translateString($_GET['enc_id'], 'd');
    }
    $job_details = $database->getJobDetails($job_id);
    $linesman_job_details = $database->getLinesManJobDetails($linesman_job_id);
    // var_dump($linesman_job_id);
    // var_dump($linesman_job_details);
    $job_status = $job_details['status'];
    $linesman_job_status = $linesman_job_details['isLinesmanComplete'];
    $job_status_name = $job_details['status_name'];
    $status_style_class = $job_details['class_modifiers'];

    // If the job is complete, deny access to the operator
    if (($job_status == 8 && $session->userinfo['user_level'] == 1)) header("Location: operator_main");
    if (($linesman_job_status == 1 && $session->userinfo['user_level'] == 1)) header("Location: operator_main");
    if (($job_status != 6 && $session->userinfo['user_level'] != 1)) header("Location: job?id=" . $job_id);

    $has_site_inspection = $database->jobHasSiteInspection($job_id);
}
?>

<!-- Hidden variables not submitted to backend -->
<input type="hidden" name="customer-discount" id="customer-discount" value="0">
<input type="hidden" name="concrete-charge" id="concrete-charge" value="0">
<input type="hidden" name="mix-charge" id="mix-charge" value="0">
<input type="hidden" class="js-maxlength form-control bg-white" id="job-range" name="job-range" maxlength="10" value="<?php echo $job_details['job_range'] ?>">
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
                        <span class="d-none d-lg-block"><i class="fa fa-toolbox text-white-50 mr-1"></i></span> <span>Linesman Job Sheet</span>
                    </div>
                </h1>
                <div class="text-right font-size-h2 font-w700 text-white my-2 d-flex align-content-center flex-wrap">
                    <span class="flex-fill p-1 <?php echo $status_style_class; ?> text-uppercase" id="job-status"><?php echo $job_status_name; ?></span> <span class="flex-fill p-1" id="job-number-formatted">JX-<?php echo str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Hero -->

<!-- Page Content -->
<div class="content">
    <div class="block block-rounded block-bordered invisible" data-toggle="appear" data-timeout="200" id="form-section">
        <div class="block-content">
            <form action="process" method="POST" id="linesman-job-form">
                <!-- Job details -->
                <div class="row push mb-0">
                    <div class="col-lg-12">
                        <div class="form-group form-row">
                            <div class="col-lg-6 col-6">
                                <label for="job-date">Job Date</label>
                                <input disabled type="text" class="js-datepicker form-control mb-2" id="job-date" name="job-date" data-week-start="1" data-autoclose="true" data-today-highlight="true" data-date-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" value="<?php echo date("d/m/Y", strtotime($job_details['job_date'])); ?>">
                                <label for="job-address-1">Address</label>
                                <a id="address-link" href="" target="_blank">
                                    <address>
                                        <strong><?php echo $job_details['job_addr_1'] . " " . $job_details['job_addr_2'] ?></strong><br>
                                        <span><?php echo $job_details['job_suburb'] ?></span><br>
                                        <span><?php echo $job_details['job_city'] ?></span><br>
                                        <span><?php echo $job_details['job_post_code'] ?></span>
                                        <input type="hidden" name="address_1" value="<?php echo $job_details['job_addr_1'] ?>">
                                        <input type="hidden" name="suburb" value="<?php echo $job_details['job_suburb'] ?>">
                                        <input type="hidden" name="city" value="<?php echo $job_details['job_city'] ?>">
                                        <input type="hidden" name="post_code" value="<?php echo $job_details['job_post_code'] ?>">
                                    </address>
                                </a>
                            </div>
                            <div class="col-lg-6 col-6">
                                <label class="" for="job-timing">
                                    Job Start Time
                                </label>
                                <input disabled type="time" required class="form-control bg-white" id="job-timing" name="job-timing" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="false" value="<?php echo $job_details['job_timing'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Job details -->

                <!-- Job Specifications section -->
                <div class="row push">
                    <div class="col-lg-12">
                        <div class="form-group form-row">
                            <div class="col-lg-4 col-md-4 col-sm-6 mb-2">
                                <label for="job-type">Job Type</label>
                                <select disabled class="js-select2 form-control" id="job-type-select" name="job-type-select" style="width: 100%;">
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-6 mb-2">
                                <label for="cubics">
                                    Cubics
                                </label>
                                <div class="input-group">
                                    <input disabled type="text" class="js-maxlength form-control" id="cubics" name="cubics" maxlength="8" value="<?php echo $job_details['cubics'] ?>">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            m<sup>3</sup>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                                <label for="mpa">MPa</label>
                                <div class="input-group">
                                    <input disabled type="text" class="js-maxlength form-control" id="mpa" name="mpa" maxlength="5" value="<?php echo $job_details['mpa'] ?>">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            MPa
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-6">
                                <label for="concrete-type-select">Concrete Type</label>
                                <select disabled class="js-select2 form-control" id="concrete-type-select" name="concrete-type-select" style="width: 100%;">
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="truck-select">Truck</label>
                                <select disabled class="js-select2 form-control" id="truck-select" name="truck-select" style="width: 100%;">
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-6">
                                <label for="boom">Boom Size</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="boom" name="boom" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            m
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="capacity">Capacity Per Hour</label>
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

                <!-- Customer, supplier and layer Info -->
                <div class="row push mb-0">
                    <div class="col-lg-12">
                        <div class="form-group row">
                            <div class="col-lg-4 col-md-12">
                                <label for="customer-select">Customer</label>
                                <select class="js-select2 form-control d-none invisible" id="customer-select" name="customer-select" style="width: 100%;">
                                </select>
                                <address>
                                    <span id="cust-company-name">Customer info loading..</span><br>
                                    <strong><span id="contact-name">Customer info loading..</span></strong><br>
                                    <abbr title="Landline Phone">Phone:</abbr> <a id="cust-ph"></a><br>
                                    <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="cust-mob-ph"></a>
                                </address>
                            </div>
                            <div class="col-lg-4">
                                <label for="layer-select">Layer</label>
                                <select class="js-select2 form-control" id="layer-select" name="layer-select" style="width: 100%;">
                                </select>
                                <address>
                                    <strong><span id="layer-name">Please select a layer..</span></strong><br>
                                    <a id="layer-email" href="mailto:#"></a><br>
                                    <abbr title="Landline Phone">Phone:</abbr> <a id="layer-ph"></a><br>
                                    <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="layer-mob-ph"></a>
                                </address>
                            </div>
                            <div class="col-lg-4">
                                <label for="supplier-select">Supplier</label>
                                <select class="js-select2 form-control" id="supplier-select" name="supplier-select" style="width: 100%;">
                                </select>
                                <!-- Supplier details -->
                                <address>
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
                <!-- END Customer, supplier and layer Info -->

                <!-- Foreman Details -->
                <div class="row push mb-2">
                    <div class="col-lg-4">
                        <label for="foreman-select">Foreman</label>
                        <select class="js-select2 form-control" id="foreman-select" name="foreman-select" style="width: 100%;">
                        </select>
                        <!-- Foreman details -->
                        <address>
                            <strong><span id="foreman-company"></span></strong><br>
                            <strong><span id="foreman-name"></span></strong><br>
                            <abbr title="Landline Phone">Phone:</abbr> <a id="foreman-ph"></a><br>
                            <a id="foreman-email" href="mailto:#"></a><br>
                        </address>
                        <!-- END Foreman details -->
                    </div>
                    <!--operator-->
                    <div class="col-lg-4">
                        <label for="operator-select">Operator</label>
                        <select class="js-select2 form-control" id="operator-select" name="operator-select" style="width: 100%">
                        </select>
                        <address>
                            <strong><span id="operator-name"></span></strong><br>
                            <a id="operator-email" href="mailto:#"></a><br>
                            <abbr title="Landline Phone">Phone:</abbr> <a id="operator-ph"></a><br>
                        </address>
                    </div>
                </div>
                <!-- END Foreman Details -->

                <!-- Optional Info -->
                <div class="row push mb-0">
                    <div class="col-lg-8 col-xl-12">
                        <div class="form-group">
                            <label for="job-instructions">Job and OH&S Instructions</label>
                            <textarea class="d-none js-maxlength form-control" id="job-instructions" name="job-instructions" placeholder="Nothing here.." maxlength="500"><?php echo $job_details['job_instructions'] . "\n\n" . 'Please complete safety APPS as per site requirements -  Full PPE gear must be worn at all time' ?></textarea>
                            <p class="bg-gray-light" style="white-space: pre-line"><?php echo $job_details['job_instructions'] . "\n\n" . 'Please complete safety APPS as per site requirements -  Full PPE gear must be worn at all time' ?> </p>
                        </div>
                    </div>
                </div>
                <!-- END Optional Info -->

                <!-- Site Inspection section of job -->
                <!--                hidden start-->
                <div class="form-row" id="site-inspection-section">
                    <div class="col-md-12 col-sm-12">
                        <!-- <span class="font-w600">Site Inspection(s)</span> -->
                        <table class="table table-sm table-vcenter" id="js-dataTable-jobSiteInspections">
                            <thead class="thead-light">
                                <tr>
                                    <th>Site Inspection(s)</th>
                                    <th> </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END Site Inspection section of job -->

                <!-- Onsite job information -->
                <!-- <div class="row push mb-0">
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label class="" for="actual-job-timing">
                                Actual Job Start Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" required class="form-control bg-white operator-job-field" id="actual-job-timing" name="actual-job-timing" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="true" value="<?php echo $linesman_job_details['actual_job_timing'] ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="job-finish-time">Finish Time <span class="text-danger">*</span></label>
                            <input type="time" required class="form-control bg-white operator-job-field" id="job-finish-time" name="job-finish-time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="true" value="<?php if (!is_null($linesman_job_details['job_time_finished'])) echo $linesman_job_details['job_time_finished']; ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="form-group">
                            <label for="line-size-select">Line Size(inch) <span class="text-danger">*</span></label>
                            <select class="js-select2 form-control" id="line-size-select" name="line-size-select" style="width: 100%;">
                                <option value="<?php
                                                if (!in_array($linesman_job_details['line_size_select'], [0, 2, 2.5, 3, 3.5, 4])) {
                                                    echo $linesman_job_details['line_size_select'];
                                                } else if ($linesman_job_details['line_size_select'] == 0) {
                                                    echo "";
                                                }
                                                ?>" disabled selected>
                                    <?php
                                    if (!in_array($linesman_job_details['line_size_select'], [0, 2, 2.5, 3, 3.5, 4])) {
                                        echo $linesman_job_details['line_size_select'];
                                    } else if ($linesman_job_details['line_size_select'] == 0) {
                                        echo "Select line size";
                                    }
                                    ?>
                                </option>
                                <option value="2" <?php echo ($linesman_job_details['line_size_select'] == '2') ? 'selected' : ''; ?>>2</option>
                                <option value="2.5" <?php echo ($linesman_job_details['line_size_select'] == '2.5') ? 'selected' : ''; ?>>2.5</option>
                                <option value="3" <?php echo ($linesman_job_details['line_size_select'] == '3') ? 'selected' : ''; ?>>3</option>
                                <option value="3.5" <?php echo ($linesman_job_details['line_size_select'] == '3.5') ? 'selected' : ''; ?>>3.5</option>
                                <option value="4" <?php echo ($linesman_job_details['line_size_select'] == '4') ? 'selected' : ''; ?>>4</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="linesman-job-notes">Job Notes</label>
                            <textarea class="js-maxlength form-control operator-job-field" id="linesman-job-notes" name="linesman-job-notes" rows="6" placeholder="Add any notes about this job" maxlength="500"><?php if (!is_null($linesman_job_details['linesman_notes'])) echo $linesman_job_details['linesman_notes']; ?></textarea>
                        </div>
                    </div>
                </div> -->

                <!--                hidden end-->
                <!-- <div class="row push">
                    <div class="col-lg-12 col-xl-10">
                        <div class="form-group">
                            <label class="d-block">Pump Setup Plan Confirmation</label>
                            <div class="custom-control custom-checkbox custom-control-lg custom-control-inline">
                                <input type="checkbox" class="custom-control-input operator-job-field" id="confirm-completion" name="confirm-completion">
                                <label class="custom-control-label" for="confirm-completion">Is your Pump Setup Plan complete? If not, <a href="https://app.jaxxonconcretepumps.co.nz/setup_plan"> click here to start a setup plan</a></label>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- END Onsite job information -->

                <!-- Hidden form values to be submitted to the server -->
                <input type="hidden" id="type" value="operator">
                <input type="hidden" class="operator-job-field" name="job-id" id="job_id" value="<?php echo $job_id ?>">
                <input type="hidden" class="operator-job-field" name="linesman-job-id" id="linesman-job-id" value="<?php echo $linesman_job_id ?>">
                <input type="hidden" name="estimated-pump-time" id="estimated-pump-time" value="<?php echo $job_details['estimate_pump_time'] ?>">

                <!-- Job buttons -->
                <div class="block block-transparent" id="button-section">
                    <div class="block-content">
                        <!-- <div class="text-center">
                            <?php if ($job_status == 6) { ?>
                                <button type="submit" id="linesman-complete-job" name="subCompleteLinesmanJob" class="btn btn-hero-success mr-1 mb-3">
                                    <i class="fa fa-fw fa-check-circle mr-1"></i> Complete Job
                                </button>
                            <?php } else { ?>
                                <p class="p-3 bg-warning text-white font-w600">You cannot complete a job with pending Site Inspections</p>
                            <?php } ?> -->
            </form>
        </div>
    </div>
</div>
</div>
<!-- END Page Content -->

<!-- Job print layout -->
<?php require 'job_print_layout.php'; ?>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/select/dataTables.select.min.js'); ?>
<?php $dm->get_js('js/plugins/magnific-popup/jquery.magnific-popup.min.js'); ?>

<!-- Page JS Helpers -->
<script>
    jQuery(function() {
        Dashmix.helpers(['select2', 'datepicker']);
    });
</script>

<!-- Page JS Code -->
<?php $dm->get_js('js/helpers/invoice_process_helpers.js'); ?>
<?php $dm->get_js('js/pages/job_page.js'); ?>
<?php $dm->get_js('js/pages/linesman_job_page.js'); ?>
<?php $dm->get_js('js/helpers/cost_calculations.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>