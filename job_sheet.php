<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/_global/views/head_start.php'; ?>

<?php require 'inc/_global/views/head_end.php'; ?>

<?php

if (isset($_GET['id'])) {
    // Obfuscated backend id
    $job_id = $database->translateString($_GET['id'], 'd');
    if (!is_numeric($job_id)) die("Access denied. There's a snake on this plane.");
    $job_details = $database->getJobDetails($job_id);
    $customer_details = $database->getCustomerDetails($job_details['customer_id']);
    $truck_details = $database->getTruckDetails($job_details['truck_id']);
    $supplier_details = $database->getSupplierDetails($job_details['supplier_id']);
    $layer_details = $database->getLayerDetails($job_details['layer_id']);
    $operator_details = $database->getOperatorDetails($job_details['operator_id']);
    $job_type = $database->getNameForJobTypeID($job_details['job_type']);
    $concrete_type = $database->getConcreteDetails($job_details['concrete_type']);
    if (!is_null($job_details['mix_type']))
        $mix_type = $database->getConcreteDetails($job_details['mix_type']);
} else die("Access denied. If you are seeing this in error, please contact your local dog for assistance.");

echo "<input type='hidden' id='job-details' value='" . htmlspecialchars(json_encode($job_details), ENT_QUOTES, 'UTF-8') . "'>";
echo "<input type='hidden' id='customer-details' value='" . htmlspecialchars(json_encode($customer_details), ENT_QUOTES, 'UTF-8')  . "'>";
echo "<input type='hidden' id='truck-details' value='" . htmlspecialchars(json_encode($truck_details), ENT_QUOTES, 'UTF-8') . "'>";
echo "<input type='hidden' id='supplier-details' value='" . htmlspecialchars(json_encode($supplier_details), ENT_QUOTES, 'UTF-8')  . "'>";
echo "<input type='hidden' id='layer-details' value='" . htmlspecialchars(json_encode($layer_details), ENT_QUOTES, 'UTF-8')  . "'>";
echo "<input type='hidden' id='operator-details' value='" . htmlspecialchars(json_encode($operator_details), ENT_QUOTES, 'UTF-8')  . "'>";
echo "<input type='hidden' id='job-type' value='" . json_encode($job_type) . "'>";
echo "<input type='hidden' id='concrete-type' value='" . json_encode($concrete_type) . "'>";
if (isset($mix_type))
    echo "<input type='hidden' id='mix-type' value='" . json_encode($mix_type) . "'>";

?>

<!-- Job print layout -->
<?php require 'job_print_layout.php'; ?>
<!-- END Job print layout -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/datatables/jquery.dataTables.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/datetime-moment/moment.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/datetime-moment/datetime-moment.js'); ?>

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/job_sheet_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>