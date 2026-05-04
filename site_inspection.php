<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>

<?php
// Page specific configuration
$dm->l_m_content                  = 'boxed';
?>

<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/select2/css/select2.min.css'); ?>
<?php $dm->get_css('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css'); ?>
<?php $dm->get_css('js/plugins/flatpickr/flatpickr.min.css'); ?>
<?php $dm->get_css('js/plugins/sweetalert2/sweetalert2.min.css'); ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/job.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<?php
// No job id is supplied, we can't even get job details, so we stop here by dying.
if (!isset($_GET['id'])) {
    header("Location: jobs_panel");
} else {
    $site_inspection_id = $_GET['id'];
    $site_inspection_details = $database->getSiteInspectionDetails($site_inspection_id);
    // var_dump($site_inspection_details);
    $delete_flag = $database->getDeleteFlag();
    $photo_count = $database->getPhotoCount();
    if (!$site_inspection_details) {
        die("Site inspection does not exist or was deleted!");
    }
    $assigned_operator_details = $database->getOperatorDetails($site_inspection_details['site_visit_assigned_operator']);
    $site_visit_status = $site_inspection_details['site_visit_completed'];

    if ($site_visit_status != null) {
        $site_visit_status_name = 'COMPLETE';
        $status_style_class = 'bg-success';
    } else {
        $site_visit_status_name = 'NOT COMPLETE';
        $status_style_class = 'bg-danger';
    }

    $has_site_inspection = $database->jobHasSiteInspection($site_inspection_id);

    if (isset($_GET['edit'])) {
        if (($session->userinfo['user_level'] != 3 && $session->userinfo['user_level'] != 2)) header("Location: site_inspection?id=" . $site_inspection_id);
    }
}
?>

<?php if (isset($_SESSION['upload_error'])) {
    // Upload photo errors go here
    echo '<input type="hidden" id="upload-error" value="' . $_SESSION['upload_error'] . '">';
    unset($_SESSION['upload_error']);
} ?>

<!-- Hidden variables not submitted to backend -->
<input type="hidden" id="photoCount" value="<?php echo $photo_count ?>">
<input type="hidden" name="customer-discount" id="customer-discount" value="0">
<input type="hidden" name="concrete-charge" id="concrete-charge" value="0">
<input type="hidden" name="mix-charge" id="mix-charge" value="0">
<input type="hidden" class="js-maxlength form-control bg-white" id="job-range" name="job-range" maxlength="10" value="<?php echo $site_inspection_details['job_range'] ?>">
<input type="hidden" class="form-control" id="travel" name="travel" value="<?php echo number_format($site_inspection_details['truck_travel_rate_km'], 2, '.', '') ?>" disabled>
<input type="hidden" class="form-control" id="min" name="min" value="<?php echo number_format($site_inspection_details['truck_min'], 2, '.', '') ?>" disabled>
<input type="hidden" class="form-control" id="rate" name="rate" value="<?php echo number_format($site_inspection_details['truck_rate'], 2, '.', '') ?>" disabled>

<!-- Hero -->
<div class="bg-image" id="hero-section" style="background-image: url('<?php echo $dm->assets_folder; ?>/media/photos/photo5@2x.jpg');">
    <div class="bg-black-50">
        <div class="content content-full">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="flex-fill font-size-h2 text-white my-2">
                    <div class="d-flex align-content-center flex-wrap">
                        <span class="d-none d-lg-block"><i class="fa fa-eye text-white-50 mr-1"></i></span> <span>Site Inspection<?php echo $site_visit_status_name; ?></span>
                    </div>
                </h1>
                <div class="text-right font-size-h2 font-w700 text-white my-2 d-flex align-content-center flex-wrap">
                    <span class="flex-fill p-1 <?php echo $status_style_class; ?> text-uppercase" id="job-status"><?php echo $site_visit_status_name; ?></span> <span class="flex-fill p-1" id="job-number-formatted">JX-<?php echo str_pad($site_inspection_details['unified_id'], 6, "0", STR_PAD_LEFT); ?></span>
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
            <form action="process" method="POST" id="site-inspection-form" enctype="multipart/form-data">
                <!-- Job details -->
                <div class="row push">
                    <div class="col-lg-4">
                        <?php if (isset($site_inspection_id) && ($session->userinfo['user_level'] == 3 || $session->userinfo['user_level'] == 2) && !is_null($site_visit_status) && !isset($_GET['edit'])) { ?>
                            <a href="./site_inspection?id=<?php echo $site_inspection_id ?>&edit=true">
                                <button type="button" class="btn btn-hero-info bg-xsmooth"><i class="fa fa-fw fa-pen-alt mr-1"></i> Edit</button>
                            </a>
                        <?php } else if (isset($_GET['edit'])) { ?>
                            <a href="./site_inspection?id=<?php echo $site_inspection_id ?>">
                                <button type="button" class="btn btn-hero-info bg-xsmooth"><i class="fa fa-fw fa-pen-alt mr-1"></i> Exit Edit Mode</button>
                            </a>
                        <?php }
                        ?>
                    </div>
                    <div class="col-lg-6">
                        <p>Assigned Operator: <?php echo $assigned_operator_details['user_firstname'] . ' ' . $assigned_operator_details['user_lastname'] ?><?php if ($delete_flag == "1") echo '<input type="button" value="delete" id="deleteCanceled"></input>' ?></p>
                        <?php if ($site_visit_status != null) {
                            echo '<p class="font-w700">Site inspection was completed: ' . date('d/m/Y h:i A', strtotime($site_inspection_details['site_visit_completed'])) . '</p>';

                            $completing_user = $database->getOperatorDetails($site_inspection_details['site_visit_completed_by']);
                            echo '<p class="font-w700">Completed by: ' . $completing_user['user_firstname'] . ' ' . $completing_user['user_lastname'] . '</p>';
                        } ?>
                        <div class="form-group form-row">
                            <div class="col-6">
                                <label for="job-date">Job Date <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-6">
                                <label for="job-timing">Start Time <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-6">
                                <input type="text" class="js-datepicker form-control" id="job-date" name="job-date" data-week-start="1" data-autoclose="true" data-today-highlight="true" data-date-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" value="<?php echo date("d/m/Y", strtotime($site_inspection_details['job_date'])); ?>" disabled>
                            </div>
                            <div class="col-6">
                                <input type="time" class="form-control bg-white" id="job-timing" name="job-timing" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="true" value="<?php echo $site_inspection_details['job_timing'] ?>" readonly disabled>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-12">
                                <label for="job-address-1">Address <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-6">
                                <input type="text" class="js-maxlength form-control" id="job-address-1" name="job-address-1" placeholder="Start typing an address.." maxlength="100" value="<?php echo $site_inspection_details['job_addr_1'] ?>" disabled>
                            </div>
                            <div class="col-6">
                                <input type="text" class="js-maxlength form-control" id="job-address-2" name="job-address-2" placeholder="Address 2" maxlength="100" value="<?php echo $site_inspection_details['job_addr_2'] ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-suburb" name="job-suburb" placeholder="Suburb" maxlength="100" value="<?php echo $site_inspection_details['job_suburb'] ?>" disabled>
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-city" name="job-city" placeholder="City" maxlength="100" value="<?php echo $site_inspection_details['job_city'] ?>" disabled>
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-post-code" name="job-post-code" placeholder="Post Code" maxlength="10" value="<?php echo $site_inspection_details['job_post_code'] ?>" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <!-- Extra details added as requested as plain text -->
                                <div class="form-group">
                                    <label>Job Type</label>
                                    <div class="form-control"><?php echo $site_inspection_details['type_name']; ?></div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Concrete Type</label>
                                    <div class="form-control"><?php echo $site_inspection_details['concrete_name']; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <!-- Extra details added as requested as plain text -->
                                <div class="form-group">
                                    <label>Cubics</label>
                                    <div class="form-control"><?php echo $site_inspection_details['cubics']; ?></div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>MPA</label>
                                    <div class="form-control"><?php echo $site_inspection_details['mpa']; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <!-- Customer details -->
                                    <div>
                                        <address>
                                            <strong><span id="contact-name"><?php echo $site_inspection_details['name'] ?></span></strong><br>
                                            <span><?php echo $site_inspection_details['first_name'] . ' ' . $site_inspection_details['last_name'] ?></span><br>
                                            <abbr title="Landline Phone">Phone:</abbr> <a id="cust-ph" href="tel:<?php echo $site_inspection_details['contact_ph'] ?>"><?php echo $site_inspection_details['contact_ph'] ?></a><br>
                                            <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="cust-mob-ph" href="tel:<?php echo $site_inspection_details['contact_mob'] ?>"><?php echo $site_inspection_details['contact_mob'] ?></a>
                                        </address>
                                    </div>
                                    <!-- END Customer details -->
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Layer</label>
                                    <!-- Layer details -->
                                    <div>
                                        <address>
                                            <strong><span id="contact-name"><?php echo $site_inspection_details['layer_name'] ?></span></strong><br>
                                            <span><?php echo $site_inspection_details['layer_firstname'] . ' ' . $site_inspection_details['layer_lastname'] ?></span><br>
                                            <abbr title="Landline Phone">Phone:</abbr> <a id="cust-ph" href="tel:<?php echo $site_inspection_details['layer_ph'] ?>"><?php echo $site_inspection_details['layer_ph'] ?></a><br>
                                            <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="cust-mob-ph" href="tel:<?php echo $site_inspection_details['layer_mob'] ?>"><?php echo $site_inspection_details['layer_mob'] ?></a>
                                        </address>
                                    </div>
                                    <!-- END Layer details -->
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label>Supplier</label>
                                    <!-- Supplier details -->
                                    <div>
                                        <address>
                                            <strong><span id="contact-name"><?php echo $site_inspection_details['supplier_name'] ?></span></strong><br>
                                            <span><?php echo $site_inspection_details['supplier_firstname'] . ' ' . $site_inspection_details['supplier_lastname'] ?></span><br>
                                            <abbr title="Landline Phone">Phone:</abbr> <a id="cust-ph" href="tel:<?php echo $site_inspection_details['supplier_ph'] ?>"><?php echo $site_inspection_details['supplier_ph'] ?></a><br>
                                            <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="cust-mob-ph" href="tel:<?php echo $site_inspection_details['supplier_mob'] ?>"><?php echo $site_inspection_details['supplier_mob'] ?></a>
                                        </address>
                                    </div>
                                    <!-- END Supplier details -->
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>Foreman</label>
                                    <!-- Foreman details -->
                                    <div>
                                        <address>
                                            <strong><span id="contact-name"><?php echo $site_inspection_details['foremen_company'] ?></span></strong><br>
                                            <span><?php echo $site_inspection_details['foremen_firstname'] . ' ' . $site_inspection_details['foremen_lastname'] ?></span><br>
                                            <abbr title="Landline Phone">Phone:</abbr> <a id="cust-ph" href=""></a><br>
                                            <abbr title="Mob Phone">Mobile Phone:</abbr> <a id="cust-mob-ph" href="tel:<?php echo $site_inspection_details['foremen_phone'] ?>"><?php echo $site_inspection_details['foremen_phone'] ?></a>
                                        </address>
                                    </div>
                                    <!-- END Foreman details -->
                                </div>
                            </div>
                        </div>
                        <!--Linesman-->
                        <!-- <div class="row push mb-0">
                            <div class="col-lg-12">
                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <label for="operator-select">Linesman </label>
                                        <select <?php if ($site_visit_status != null && !isset($_GET['edit'])) echo 'disabled' ?> class="js-select2 form-control" id="linesman-select" style="width: 100%;" name="linesman-select[]" multiple="multiple">
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        <button id="assign-linesmen" class="btn btn-primary">Assign Linesmen</button>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- Job instructions in plain text -->
                        <div class="form-group">
                            <label>Job Instructions</label>
                            <p>
                                <?php echo $site_inspection_details['job_instructions'] ?>
                            </p>
                        </div>
                        <!-- Site Visit photo section -->
                        <?php
                        $photos = explode(',', $site_inspection_details['site_visit_photo']);
                        for ($i = 0; $i < $photo_count; $i++) {
                        ?>
                            <div class="form-group">
                                <label>Site Photo<?php echo $i + 1 ?></label>
                                <?php if ($site_visit_status == null) { ?>
                                    <!-- Output input element -->
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" data-toggle="custom-file-input" id="site-photo-input-<?php echo $i; ?>" name="site-photo-input-<?php echo $i; ?>">
                                        <label class="custom-file-label" for="site-photo-input-<?php echo $i; ?>">Add photo..</label>
                                    </div>
                                <?php } else if ($photos[$i] != null) { ?>
                                    <!-- Output the image -->
                                    <img class="img-fluid" src="<?php echo $photos[$i] ?>" alt="Site Photo">
                                <?php } else {
                                    echo '<span class="d-block text-warning">No photo was provided.</span>';
                                } ?>
                            </div>
                        <?php
                        }
                        ?>
                        <!-- Pump number/boom lengths that can be used for job -->
                        <div class="form-group form-row">
                            <div class="col-lg-8 col-xl-12">
                                <label for="truck-select">Pump Number(s)</label>
                                <?php $suitable_pumps = $database->getSuitablePumpsForJob($site_inspection_details['site_visit_job_id']);

                                if ($suitable_pumps == null || isset($_GET['edit'])) { ?>
                                    <select class="js-select2 form-control" id="truck-select" name="truck-select[]" multiple style="width: 100%;">
                                    </select>
                                <?php  } else echo implode(', ', $database->getSuitablePumpsForJob($site_inspection_details['site_visit_job_id'])) ?>
                            </div>
                        </div>
                        <!-- END Pump number/boom lengths that can be used for job -->

                        <div class="form-group form-row">
                            <div class="col-lg-8 col-xl-12">
                                <div class="form-group">
                                    <label for="site-visit-notes">Site Inspection Notes</label>
                                    <textarea <?php if ($site_visit_status != null && !isset($_GET['edit'])) echo 'disabled' ?> class="js-maxlength form-control" id="site-visit-notes" name="site-visit-notes" rows="6" placeholder="Add notes here (optional)" maxlength="500"><?php echo $site_inspection_details['site_visit_notes'] ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mb-3">
                            <div class="d-flex justify-content-center">
                                <button type="button" id="siteInspectionSwitchJobColor" class="btn <?php echo ($site_inspection_details['switchJobColor'] == '0') ? 'btn-warning' : 'btn-primary'; ?>">
                                    <?php if ($site_inspection_details['switchJobColor'] == 0): ?>
                                        Turn To Linesman Job
                                    <?php elseif ($site_inspection_details['switchJobColor'] == 1): ?>
                                        Back To Ordinary Job
                                    <?php endif ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Job details -->

                <?php if ($site_visit_status == null) { ?>
                    <!-- Submit -->
                    <div class="row push">
                        <div class="col-lg-12 col-xl-8 offset-lg-4">
                            <div class="form-group">
                                <label class="d-block">Completion Confirmation</label>
                                <div class="custom-control custom-checkbox custom-control-lg custom-control-inline">
                                    <input type="checkbox" class="custom-control-input" id="confirm-completion" name="confirm-completion">
                                    <label class="custom-control-label" for="confirm-completion">I, <?php echo $session->userinfo['user_firstname'] . ' ' . $session->userinfo['user_lastname'] ?>, confirm I have completed this site inspection</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row push">
                        <div class="col-lg-8 col-xl-5 offset-lg-4">
                            <div class="form-group">
                                <button type="submit" name="subcompletesiteinspection" class="btn btn-success">
                                    <i class="fa fa-check-circle mr-1"></i> Complete Site Visit
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- END Submit -->

                <?php } else if (isset($_GET['edit'])) { ?>
                    <!-- Update site inspection -->
                    <div class="row push">
                        <div class="col-lg-12 col-xl-8 offset-lg-4">

                        </div>
                    </div>
                    <div class="row push">
                        <div class="col-lg-8 col-xl-5 offset-lg-4">
                            <div class="form-group">
                                <button type="submit" name="subupdatesiteinspection" class="btn btn-success">
                                    <i class="fa fa-save mr-1"></i> Update
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- END Update site inspection -->
                <?php } ?>


                <!-- Hidden form values to be submitted to the server -->
                <input type="hidden" name="site-inspection-id" id="site_inspection_id" value="<?php echo $site_inspection_details['id'] ?>">
                <input type="hidden" name="job-id" id="job_id" value="<?php echo $site_inspection_details['site_visit_job_id'] ?>">
            </form>
        </div>
    </div>
</div>
<!-- END Page Content -->

<!-- Job print layout -->
<?php require 'job_print_layout.php'; ?>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/select2/js/select2.full.min.js'); ?>
<?php $dm->get_js('js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js'); ?>
<?php $dm->get_js('js/plugins/es6-promise/es6-promise.auto.min.js'); ?>
<?php $dm->get_js('js/plugins/sweetalert2/sweetalert2.min.js'); ?>

<!-- Page JS Helpers -->
<script>
    jQuery(function() {
        Dashmix.helpers(['select2', 'datepicker']);
    });
</script>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/site_inspection_page.js'); ?>
<?php //$dm->get_js('js/pages/job_page.js'); ?>
<?php //$dm->get_js('js/pages/job_page_emailing.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>
<?php $dm->get_js('js/plugins/jquery-validation/additional-methods.js'); // Used for file extension validation. We do this because of the global inclusion of jquery validate. Fml.
?>