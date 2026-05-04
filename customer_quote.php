<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/_global/views/head_start.php'; ?>

<!-- Page CSS -->
<?php $dm->get_css('css/pages/customer_quote.css'); ?>

<?php require 'inc/_global/views/head_end.php'; ?>

<?php

if (isset($_GET['id'])) {
    // Obfuscated backend id
    $quote_id = $database->translateString($_GET['id'], 'd');
    if (!is_numeric($quote_id)) die("Access denied. There's a snake on this plane.");
    $quote_details = $database->getQuoteDetails($quote_id)[0];
    $customer_details = $database->getCustomerDetails($quote_details['customer_id']);
    $truck_details = $database->getTruckDetails($quote_details['truck_id']);
    $job_type = $database->getNameForJobTypeID($quote_details['job_type']);
    $concrete_type = $database->getConcreteDetails($quote_details['concrete_type']);
    if (!is_null($quote_details['mix_type']))
        $mix_type = $database->getConcreteDetails($quote_details['mix_type']);
} else die("Access denied. If you are seeing this in error, please contact your local dog for assistance.");

echo "<input type='hidden' id='quote-details' value='" . htmlspecialchars(json_encode($quote_details), ENT_QUOTES, 'UTF-8') . "'>";
echo "<input type='hidden' id='customer-details' value='" . htmlspecialchars(json_encode($customer_details), ENT_QUOTES, 'UTF-8') . "'>";
echo "<input type='hidden' id='truck-details' value='" . htmlspecialchars(json_encode($truck_details), ENT_QUOTES, 'UTF-8') . "'>";
echo "<input type='hidden' id='job-type' value='" . json_encode($job_type) . "'>";
echo "<input type='hidden' id='concrete-type' value='" . json_encode($concrete_type) . "'>";
if (isset($mix_type))
    echo "<input type='hidden' id='mix-type' value='" . json_encode($mix_type) . "'>";
if (!is_null($quote_details['quote_accepted'])) {
    if ($quote_details['quote_accepted'] == 0) {
        $quote_response_name = "Declined";
        $bg_class = "bg-danger";
    } else if ($quote_details['quote_accepted'] == 1) {
        $quote_response_name = "Accepted";
        $bg_class = "bg-success";
    }
}

?>

<div class="block customer-prompt invisible" data-toggle="appear" data-timeout="100">
    <div class="block-content">
        <div class="row">
            <!-- Conditionals to check if quote has already been responded to -->
            <?php if (is_null($quote_details['quote_accepted'])) { ?>
            <div class="col-lg-12 text-center">
                <p class="lead">Please verify the quote below and ensure job details and specifications are correct, then click <span class="text-primary font-w700">ACCEPT</span> or <span class="text-danger font-w700">DECLINE</span> at the bottom of the page</p>
            </div>
            <?php } else { ?>
            <div class="col-lg-12 text-center text-white animated flipInX">
                <p class="lead p-3 font-w700 <?php echo $bg_class ?>"><?php echo $quote_response_name . ": " . $quote_details['date_quote_response']; ?></p>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Quote print layout -->
<?php require 'quote_print_layout.php'; ?>
<!-- END Quote print layout -->

<?php if (!isset($quote_response_name)) { ?>
<!-- If there isn't a response yet, output the buttons to respond -->
<div class="block quote-controls invisible" data-toggle="appear" data-timeout="1000">
    <div class="block-content">
        <div class="row">
            <div class="col-lg-12 text-center">
                <form action="process" id="customer-quote" method="POST">
                    <input type="hidden" name="accept-quote-id" id="accept-quote-id" value="<?php echo $_GET['id'] ?>">
                    <button type="submit" name="subcustomeracceptquote" class="btn btn-hero-lg btn-square btn-hero-primary mr-1 mb-3">
                        <i class="fa fa-fw fa-check mr-1"></i> Accept Quote
                    </button>
                    <button type="button" id="customer-decline-quote" name="customer-decline-quote" data-toggle="modal" data-target="#modal-customer-decline-quote" class="btn btn-hero-lg btn-square btn-hero-danger mr-1 mb-3">
                        <i class="fa fa-fw fa-times mr-1"></i> Decline Quote
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/datatables/jquery.dataTables.min.js'); ?>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/customer_quote_page.js'); ?>

<!-- Modals -->
<?php require 'inc/modals/customer_decline_quote.php'; ?>

<?php require 'inc/_global/views/footer_end.php'; ?>