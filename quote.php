<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/backend/config.php'; ?>

<?php
// Page specific configuration
$dm->l_header_fixed               = false;
?>

<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page JS Plugins CSS -->
<?php $dm->get_css('js/plugins/select2/css/select2.min.css'); ?>
<?php $dm->get_css('js/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css'); ?>
<?php $dm->get_css('js/plugins/flatpickr/flatpickr.min.css'); ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/quote.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<?php
// No quote id is supplied, we can't even get quote details, so we stop here by dying.
if ($session->userinfo['user_level'] == 1) {
    header("Location: operator_main");
} else if (!isset($_GET['id'])) {
    header("Location: jobs_panel");
} else {
    $quote_details = $database->getQuoteDetails($_GET['id'])[0];

    // Work out quote status
    if (!is_null($quote_details['date_quote_sent']) && is_null($quote_details['quote_accepted']) && is_null($quote_details['date_quote_response'])) {
        $quote_status = "Pending";
        $status_style_class = "p-1 text-white bg-warning";
    } else if (is_null($quote_details['date_quote_sent']) && is_null($quote_details['quote_accepted'])) {
        $quote_status = "Not Sent";
        $status_style_class = "p-1 text-white bg-muted";
    } else if ($quote_details['quote_accepted'] == 1) {
        $quote_status = "Accepted";
        $response_date = $quote_details['date_quote_response'];
        $status_style_class = "p-1 text-white bg-success";
        $quote_response_note = "<span class='text-success font-w700'>Quote Accepted: " . date('d/m/Y H:i', strtotime($quote_details['date_quote_response']))  . "</span>";
    } else if ($quote_details['quote_accepted'] == 0) {
        $quote_status = "Declined";
        $response_date = $quote_details['date_quote_response'];
        $status_style_class = "p-1 text-white bg-danger";
        $quote_response_note = "<span class='text-danger font-w700'>Quote Declined: " . date('d/m/Y H:i', strtotime($quote_details['date_quote_response']))  . "</span>";
    }

    if (!is_null($quote_details['link_job_id'])) {
        $link_job_id = $quote_details['link_job_id'];
    }
}
?>

<!-- Hidden variables not submitted to backend -->
<input type="hidden" name="customer-discount" id="customer-discount" value="0">
<input type="hidden" name="concrete-charge" id="concrete-charge" value="0">
<input type="hidden" name="mix-charge" id="mix-charge" value="0">

<!-- Hero -->
<div class="bg-image" id="hero-section" style="background-image: url('<?php echo $dm->assets_folder; ?>/media/photos/photo18@2x.jpg');">
    <div class="bg-black-50">
        <div class="content content-full">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="flex-fill font-size-h2 text-white my-2">
                    <div class="d-flex align-content-center flex-wrap">
                        <span class="d-none d-lg-block"><i class="fa fa-file-invoice text-white-50 mr-1"></i> </span> <span>Quote Sheet</span>
                    </div>
                </h1>
                <div class="text-right font-size-h2 font-w700 text-white my-2 d-flex align-content-center flex-wrap">
                    <span class="flex-fill <?php echo $status_style_class; ?> text-uppercase" id="quote-status"><?php echo $quote_status; ?></span> <span class="flex-fill p-1" id="job-number-formatted">JX-<?php echo str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT); ?></span>
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
            <form action="process" method="POST" id="quote-form">
                <!-- Quote details -->
                <div class="row push">
                    <div class="col-lg-12">
                        <h3 class="text-center"><?php if (isset($quote_response_note)) echo $quote_response_note ?></h3>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group form-row">
                            <div class="col-4">
                                <label for="job-date">Job Date <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-4">
                                <label for="job-timing">Start Time <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-4">
                                <label for="job-range">Travel Distance <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-datepicker form-control" id="job-date" readonly="true" name="job-date" data-week-start="1" data-autoclose="true" data-today-highlight="true" data-date-format="dd/mm/yyyy" placeholder="dd/mm/yyyy" value="<?php echo date("d/m/Y", strtotime($quote_details['job_date'])); ?>">
                            </div>
                            <div class="col-4">
                                <input type="time" class="form-control bg-white" id="job-timing" name="job-timing" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="true" value="<?php echo $quote_details['job_timing'] ?>">
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="js-maxlength form-control bg-white" id="job-range" name="job-range" maxlength="10" value="<?php echo $quote_details['quoted_range'] ?>">
                                    <div class="input-group-append d-none d-xl-block d-lg-block d-md-block d-sm-none d-xs-none">
                                        <span class="input-group-text">
                                            km
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-12">
                                <label for="job-address-1">Address <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-6">
                                <input type="text" class="js-maxlength form-control" id="job-address-1" name="job-address-1" placeholder="Start typing an address.." maxlength="100" value="<?php echo $quote_details['job_addr_1'] ?>">
                            </div>
                            <div class="col-6">
                                <input type="text" class="js-maxlength form-control" id="job-address-2" name="job-address-2" placeholder="Address 2" maxlength="100" value="<?php echo $quote_details['job_addr_2'] ?>">
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-suburb" name="job-suburb" placeholder="Suburb" maxlength="100" value="<?php echo $quote_details['job_suburb'] ?>">
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-city" name="job-city" placeholder="City" maxlength="100" value="<?php echo $quote_details['job_city'] ?>">
                            </div>
                            <div class="col-4">
                                <input type="text" class="js-maxlength form-control" id="job-post-code" name="job-post-code" placeholder="Post Code" maxlength="10" value="<?php echo $quote_details['job_post_code'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Quote details -->

                <!-- Customer Info -->
                <div class="row push">
                    <div class="col-lg-12">
                        <div class="form-group form-row">
                            <div class="col-6 col-lg-8">
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
                                <label for="mpa">Megapascal <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-4">
                                <select class="js-select2 form-control" id="job-type-select" name="job-type-select" style="width: 100%;">
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="js-maxlength form-control" id="cubics" name="cubics" maxlength="8" value="<?php echo $quote_details['cubics'] ?>">
                                    <div class="input-group-append d-none d-xl-block d-lg-block d-md-block d-sm-none d-xs-none">
                                        <span class="input-group-text">
                                            m<sup>3</sup>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
                                    <input type="text" class="js-maxlength form-control" id="mpa" name="mpa" maxlength="5" value="<?php echo $quote_details['mpa'] ?>">
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
                                <label for="mix-type-select">Mix Type</label>
                                <select class="js-select2 form-control" id="mix-type-select" name="mix-type-select" style="width: 100%;">
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-4">
                                <label for="truck-select">Truck <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="truck-select" name="truck-select" style="width: 100%;">
                                </select>
                            </div>
                            <div class="col-4">
                                <label for="boom">Boom Size</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="boom" name="boom" value="<?php echo $quote_details['truck_boom'] ?>" disabled>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            m
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <label for="capacity">Capacity Per Hour</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="capacity" name="capacity" value="<?php echo $quote_details['truck_capacity'] ?>" disabled>
                                    <div class="input-group-append d-none d-lg-fle">
                                        <span class="input-group-text">
                                            m<sup>3</sup>/hr
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-3">
                                <label for="rate">Hourly Rate</label>
                                <div class="input-group">
                                    <div class="input-group-prepend d-none d-lg-flex">
                                        <span class="input-group-text">
                                            <i class="fa fa-dollar-sign"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="rate" name="rate" value="<?php echo number_format($quote_details['truck_rate'], 2, '.', '') ?>" disabled>
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="min">Minimum Charge</label>
                                <div class="input-group">
                                    <div class="input-group-prepend d-none d-lg-flex">
                                        <span class="input-group-text">
                                            <i class="fa fa-dollar-sign"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="min" name="min" value="<?php echo number_format($quote_details['truck_min'], 2, '.', '') ?>" disabled>
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="travel">Travel Rate/KM</label>
                                <div class="input-group">
                                    <div class="input-group-prepend d-none d-lg-flex">
                                        <span class="input-group-text">
                                            <i class="fa fa-dollar-sign"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="travel" name="travel" value="<?php echo number_format($quote_details['truck_travel_rate_km'], 2, '.', '') ?>" disabled>
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="washout">Pump Washdown</label>
                                <div class="input-group">
                                    <div class="input-group-prepend d-none d-lg-flex">
                                        <span class="input-group-text">
                                            <i class="fa fa-dollar-sign"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="washout" name="washout" value="<?php echo number_format($quote_details['truck_washout'], 2, '.', '') ?>" disabled>
                                </div>
                            </div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-3">
                            <label for="disposal-fee">Concrete Disposal Fee</label>
                                <div class="input-group">
                                    <div class="input-group-prepend d-none d-lg-flex">
                                        <span class="input-group-text">
                                            <i class="fa fa-dollar-sign"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" id="disposal-fee" name="disposal-fee" value="<?php echo number_format($quote_details['truck_disposal_fee'], 2, '.', '') ?>" disabled>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- END Job Specifications section -->

                <!-- Optional Info -->
                <div class="row push">
                    <div class="col-lg-12 col-xl-12">
                        <div class="form-group">
                            <label for="quote-summary-notes">Quote Summary Notes</label>
                            <textarea class="js-maxlength form-control" id="quote-summary-notes" name="quote-summary-notes" rows="6" placeholder="What notes do you need here?" maxlength="500"><?php echo $quote_details['quote_summary_notes'] ?></textarea>
                        </div>
                    </div>
                </div>
                <?php if ($quote_details['quote_accepted'] == 0) { ?>
                <div class="row push">
                    <div class="col-lg-12 col-xl-12">
                        <div class="form-group">
                            <label>Customer's Decline Reason</label>
                            <p class="text-danger"><?php echo !is_null($quote_details['customer_decline_reason']) ? $quote_details['customer_decline_reason'] : "No response from customer"; ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <!-- END Optional Info -->

                <!-- Charge details -->
                <div class="row push">

                    <div class="col-lg-12 col-xl-12">
                        <div class="form-group row">
                            <div class="col-6">
                                <div class="custom-control custom-checkbox custom-control-primary custom-control-lg mb-1">
                                    <input type="checkbox" class="custom-control-input" id="cubic-charge" name="cubic-charge" <?php echo $quote_details['cubic_charge'] == 1 ? "checked" : ""; ?>>
                                    <label class="custom-control-label" for="cubic-charge" id="charge-label">Cubic Metre Charge</label>
                                </div>
                            </div>
                            <div class="col-6">Estimated Pump Time: <strong><span id="estimated-pump-time-text"><?php echo $quote_details['estimate_pump_time'] ?></span></strong> minutes</div>
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
                                                <input type="text" class="form-control text-right charge-table-input" id="establishment-fee" name="establishment-fee" value="<?php echo $quote_details['establishment_fee'] ?>" maxlength="6">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600">
                                                Quoted <span class="table-rate-description"> Rate</span>
                                                <span class="d-block" id="quoted-rate-calculation"></span>
                                            </td>
                                            <td class="d-sm-table-cell text-right">
                                                <input type="text" class="form-control text-right charge-table-input bg-white" id="cubic-rate" name="cubic-rate" readonly value="<?php echo $quote_details['cubic_rate'] ?>" maxlength="10">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-w600">
                                                Travel Fee
                                                <span class="d-block" id="travel-fee-calculation"></span>
                                            </td>
                                            <td class="d-sm-table-cell text-right">
                                                <input type="text" class="form-control text-right charge-table-input" id="travel-fee" name="travel-fee" value="<?php echo $quote_details['travel_fee'] ?>" maxlength="10">
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
                                                <input type="text" class="form-control text-right charge-table-input" maxlength="6" placeholder="0" id="discount" name="discount" value="<?php echo $quote_details['discount'] ?>">
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
                                                Total Quote INC GST
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
                <?php if ($quote_status != "Accepted" && $quote_status != "Declined") { ?>
                <div class="row push d-none">
                    <div class="col-lg-8 col-xl-5 offset-lg-4">
                        <div class="form-group">
                            <button type="submit" id="update-quote" name="subupdatequote" class="btn btn-success">
                                <i class="fa fa-check-circle mr-1"></i> Update Quote
                            </button>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <!-- END Submit -->

                <!-- Hidden form values to be submitted to the server -->
                <input type="hidden" name="quote-id" id="quote-id" value="<?php echo $_GET['id'] ?>">
                <input type="hidden" name="estimated-pump-time" id="estimated-pump-time" value="<?php echo $quote_details['estimate_pump_time'] ?>">
            </form>
        </div>
    </div>

    <!-- Quote buttons -->
    <div class="block block-transparent" id="button-section">
        <div class="block-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <button type="button" class="btn btn-hero-info mr-1 mb-3" id="send-quote-email" data-toggle="modal" data-target="#modal-confirm-quote-email">
                    <i class="fa fa-fw fa-mail-bulk mr-1"></i> Send Quote via Email
                </button>
                <?php if ($quote_status != "Accepted" && $quote_status != "Declined") { ?>
                <button type="button" id="proxy-update-quote" class="btn btn-hero-success mr-1 mb-3">
                    <i class="fa fa-fw fa-save mr-1"></i> Update Quote
                </button>
                <?php } ?>
                <?php if ($quote_status == "Not Sent" || $quote_status == "Pending") { ?>
                <form action="process" id="user-accept-quote-create-job" method="POST">
                    <button type="submit" name="subuseracceptquoteandcreatejob" id="accept-create-job" class="btn btn-hero-success mr-1 mb-3">
                        <i class="fa fa-fw fa-plus mr-1"></i> Accept and Create Job
                    </button>
                </form>
                <?php } else if ($quote_status == "Accepted" && !isset($link_job_id)) { ?>
                <form action="process" id="user-create-job-from-quote" method="POST">
                    <button type="submit" name="subusercreatejobfromquote" class="btn btn-hero-success mr-1 mb-3">
                        <i class="fa fa-fw fa-plus mr-1"></i> Create Job
                    </button>
                </form>
                <?php } else if (isset($link_job_id)) { ?>
                <a href="job?id=<?php echo $link_job_id ?>">
                    <button type="button" class="btn btn-hero-primary mr-1 mb-3">
                        <i class="fa fa-fw fa-toolbox mr-1"></i> Go to Job
                    </button>
                </a>
                <?php } ?>
                <?php if ($quote_status == "Not Sent" || $quote_status == "Pending") { ?>
                <form action="process" id="user-decline-quote" method="POST">
                    <button type="submit" name="subuserdeclinequote" class="btn btn-hero-danger mr-1 mb-3">
                        <i class="fa fa-fw fa-times mr-1"></i> Decline Quote
                    </button>
                </form>
                <?php } ?>
                <button type="button" id="print-quote" class="btn btn-hero-secondary mr-1 mb-3">
                    <i class="fa fa-fw fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </div>
    <!-- END Quote buttons -->

</div>
<!-- END Page Content -->

<!-- Quote print layout -->
<?php require 'quote_print_layout.php'; ?>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/jquery-validation/jquery.validate.min.js'); ?>
<?php $dm->get_js('js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js'); ?>

<!-- Page JS Helpers -->
<script>
    jQuery(function() {
        Dashmix.helpers(['select2', 'datepicker']);
    });
</script>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/quote_page.js'); ?>
<?php $dm->get_js('js/helpers/cost_calculations.js'); ?>

<!-- Modals -->
<?php require 'inc/modals/email_quote.php'; ?>

<?php require 'inc/_global/views/footer_end.php'; ?>