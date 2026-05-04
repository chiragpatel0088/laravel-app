<?php

$user_level = $session->userinfo['user_level'];
$invoice_details = $database->getJobInvoiceData($job_id);
$customer_details = $database->getCustomerDetails($job_details['customer_id']);
$invoice_editable = isset($_POST['edit']);
$readonly_status = ($user_level == 3 || $job_status == 11) && !$invoice_editable;

$address_heading = '<small><strong>' . $job_details['job_addr_1'] . '</strong>';
if (!empty($job_details['job_suburb'])) $address_heading .= ', ' . $job_details['job_suburb'];
if (!empty($job_details['job_city'])) $address_heading .= ', ' . $job_details['job_city'];
if (!empty($job_details['job_post_code'])) $address_heading .= ', ' . $job_details['job_post_code'];

?>
<div id="invoice-details-section" class="block block-rounded block-bordered <?php if ($job_status == 11 && !$invoice_editable) echo "block-mode-hidden" ?>">
    <div class="block-header block-header-default">
        <h3 class="block-title"><span class="d-none d-lg-inline-block"><?php echo $customer_details['name'] . ' (' . $customer_details['first_name'] . ')' . ' - ' . $address_heading ?></small></span></h3>
        <div class="block-options">
            <div class="block-options-item">Invoice Details</div>
            <button type="button" class="btn-block-option" data-toggle="block-option" data-action="pinned_toggle">
                <i class="si si-pin"></i>
            </button>
            <button type="button" class="btn-block-option" data-toggle="block-option" data-action="content_toggle"><i class="si si-arrow-up"></i></button>
        </div>
    </div>
    <div class="block-content">
        <div class="mb-3">
            <form id="ready-for-invoicing-form" action="process" method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="d-block">Rate Type</label>
                    <?php if ($readonly_status) echo ucfirst($invoice_details['rate_type']) ?>
                    <div class="custom-control custom-radio custom-control-inline custom-control-lg <?php if ($readonly_status) echo 'd-none' ?>">
                        <input type="radio" class="custom-control-input" id="invoice-rate-type1" value="cubic" <?php if ($invoice_details['rate_type'] == "cubic") echo 'checked'; ?> name="invoice-rate-type">
                        <label class="custom-control-label" for="invoice-rate-type1">Cubic Rate</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline custom-control-lg <?php if ($readonly_status) echo 'd-none' ?>">
                        <input type="radio" class="custom-control-input" id="invoice-rate-type2" value="hourly" <?php if ($invoice_details['rate_type'] == "hourly") echo 'checked'; ?> name="invoice-rate-type">
                        <label class="custom-control-label" for="invoice-rate-type2">Hourly Rate</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline custom-control-lg <?php if ($readonly_status) echo 'd-none' ?>">
                        <input type="radio" class="custom-control-input" id="invoice-rate-type3" value="special" <?php if ($invoice_details['rate_type'] == "special") echo 'checked'; ?> name="invoice-rate-type">
                        <label class="custom-control-label" for="invoice-rate-type3">Special Rate</label>
                    </div>
                </div>
                <table class="table table-bordered table-vcenter table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th> </th>
                            <th class="d-sm-table-cell text-right" style="width: 30%;"><span class="p-2">Total</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-w600">
                                Assigned Truck
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <span class="p-2 font-w700" id="invoice-assigned-truck">Truck..</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Establishment Fee
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-establishment-fee">$<?php echo number_format($invoice_details['establishment_fee'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-establishment-fee" name="invoice-establishment-fee" value="<?php echo $invoice_details['establishment_fee'] ?>" maxlength="6">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Range <a id="g-map-directions-link" class="badge badge-primary" target="_blank" href="https://maps.google.com?saddr=88+Gargan+Road+Tauriko+Tauranga&daddr=314+Cameron+Road+Tauriko+Tauranga"><i class="fa fa-map-marked-alt"></i> Google Maps</a>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-range"><?php echo $job_details['job_range'] ?>km</span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-range" name="invoice-range" value="<?php echo $job_details['job_range'] ?>" maxlength="6">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            km
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Travel Fee
                                <div id="invoice-travel-fee-calculation" class="text-muted font-w300 d-block"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-travel-fee">$<?php echo number_format($invoice_details['travel_fee'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input " id="invoice-travel-fee" name="invoice-travel-fee" value="<?php echo $invoice_details['travel_fee'] ?>" maxlength="10">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Actual Cubics
                                <div id="invoice-cubic-difference" class="font-w300 text-muted d-block"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-actual-cubics"><?php echo $invoice_details['actual_cubics'] ?>m<sup>3</sup></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-actual-cubics" name="invoice-actual-cubics" value="<?php echo $invoice_details['actual_cubics'] ?>" maxlength="10">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            m<sup>3</sup>
                                        </span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Concrete Rate
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-concrete-rate">$<?php echo number_format($invoice_details['concrete_charge'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-concrete-rate" name="invoice-concrete-rate" value="<?php echo $invoice_details['concrete_charge'] ?>" maxlength="6">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Cubic Rate
                                <div id="actual-cubic-rate-calculation" class="d-block font-w300 text-muted"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-cubic-rate">$<?php echo number_format($invoice_details['cubic_rate'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input bg-white" id="invoice-cubic-rate" name="invoice-cubic-rate" value="<?php echo $invoice_details['cubic_rate'] ?>">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Job Hours
                                <div id="actual-hours-calculation" class="d-block text-muted font-w300"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-job-hours"><?php echo $invoice_details['actual_job_hours'] ?></span>
                                <?php } ?>
                                <input type="text" class="form-control form-control-sm text-right charge-table-input <?php if ($readonly_status) echo 'd-none' ?>" id="invoice-actual-hours" name="invoice-actual-hours" onkeypress="timeInputValidation(event)" value="<?php echo $invoice_details['actual_job_hours'] ?>" maxlength="10">
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Truck Hourly Rate
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-truck-rate">$<?php echo number_format($invoice_details['truck_hourly_rate'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-truck-rate" name="invoice-truck-rate" value="<?php echo $invoice_details['truck_hourly_rate'] ?>" maxlength="6">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Hourly Rate
                                <div id="actual-hourly-rate-calculation" class="d-block font-w300 text-muted"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-hourly-rate">$<?php echo number_format($invoice_details['hourly_rate'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input bg-white" id="invoice-hourly-rate" name="invoice-hourly-rate" value="<?php echo $invoice_details['hourly_rate'] ?>">
                                </div>
                            </td>
                        </tr>
                        <tr id="washdown-fee-row">
                            <td class="font-w600">
                                Washdown Fee
                                <div id="washdown-message" class="d-block text-muted font-w300"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-washdown-fee">$<?php echo number_format($invoice_details['washdown_fee'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-washdown-fee" name="invoice-washdown-fee" value="<?php echo $invoice_details['washdown_fee'] ?>" maxlength="10">
                                </div>
                            </td>
                        </tr>
                        <tr id="disposal-fee-row">
                            <td class="font-w600">
                                Concrete Disposal Fee
                                <div id="disposal-message" class="d-block text-muted font-w300"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-disposal-fee">$<?php echo number_format($invoice_details['disposal_fee'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-disposal-fee" name="invoice-disposal-fee" value="<?php echo $invoice_details['disposal_fee'] ?>" maxlength="10">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Special Rate
                                <div class="d-block text-muted font-w300">Use if job has special rate</div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-special-rate">$<?php echo number_format($invoice_details['special_rate'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-special-rate" name="invoice-special-rate" value="<?php echo $invoice_details['special_rate'] ?>" maxlength="10">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Extra Cost #1
                                <div class="d-block text-muted font-w300"><input type="text" <?php if ($readonly_status) echo 'readonly=""' ?> class="form-control-plaintext form-control-sm p-0 text-muted" id="extra-cost-name-1" name="extra-cost-name-1" value="<?php echo $invoice_details['special_cost_description_1'] ?>" maxlength="250"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-special-rate-1">$<?php echo number_format($invoice_details['special_rate_1'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-special-1" name="invoice-special-1" value="<?php echo $invoice_details['special_rate_1'] ?>" maxlength="10">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Extra Cost #2
                                <div class="d-block text-muted font-w300"><input type="text" <?php if ($readonly_status) echo 'readonly=""' ?> class="form-control-plaintext form-control-sm p-0 text-muted" id="extra-cost-name-2" name="extra-cost-name-2" value="<?php echo $invoice_details['special_cost_description_2'] ?>" maxlength="250"></div>
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-special-rate-2">$<?php echo number_format($invoice_details['special_rate_2'], 2, '.', ',') ?></span>
                                <?php } ?>
                                <div class="input-group input-group-sm <?php if ($readonly_status) echo 'd-none' ?>">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            $
                                        </span>
                                    </div>
                                    <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-special-2" name="invoice-special-2" value="<?php echo $invoice_details['special_rate_2'] ?>" maxlength="10">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600 text-uppercase bg-info-light">
                                Subtotal
                            </td>
                            <td class="d-sm-table-cell text-right bg-info-light">
                                <span class="p-2" id="invoice-sub-total">$0.00</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600">
                                Discount (%)
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <?php if ($readonly_status) { ?>
                                <span class="p-2" id="display-discount"><?php echo $invoice_details['discount'] ?>%</span>
                                <?php } ?>
                                <input type="text" autocomplete="off" class="form-control form-control-sm text-right charge-table-input <?php if ($readonly_status) echo 'd-none' ?>" maxlength="6" placeholder="Input a discount value" id="invoice-discount" name="invoice-discount" value="<?php echo $invoice_details['discount'] ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600 text-uppercase bg-info-lighter">
                                Subtotal after Discount
                            </td>
                            <td class="d-sm-table-cell text-right bg-info-lighter">
                                <span class="p-2" id="invoice-sub-total-inc-discount">$0.00</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w600 text-uppercase">
                                <input type="hidden" name="gst-rate-at-moment-of-invoice" value="<?php echo $invoice_details['gst_rate'] ?>">
                                GST <?php echo $invoice_details['gst_rate'] ?>%
                            </td>
                            <td class="d-sm-table-cell text-right">
                                <span class="p-2" id="invoice-gst">$0.00</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-w700 text-uppercase bg-body-light">
                                Total Cost INC GST
                            </td>
                            <td class="d-sm-table-cell text-right font-w700 bg-body-light">
                                <span class="p-2" id="invoice-cubic-cost">$0.00</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php if (($job_status != 11 && $user_level == 2) || $invoice_editable) { ?>
                <div class="block-content block-content-full text-right bg-light">
                    <div class="form-row">
                        <div class="col">
                            <input type="hidden" name="job-id" value="<?php echo $job_id ?>">
                            <?php if ($invoice_editable && $user_level == 3) { ?>
                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal-job-edit-reason">Admin Update Details</button>
                            <?php } else { ?>
                            <button type="submit" name="updateinvoicedetails" class="btn btn-sm btn-success">Update Details</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </form>
            <!-- Only if the job is not complete and the user is an admin -->
            <?php if ($job_status != 11 && $user_level == 3) { ?>
            <div class="block-content block-content-full text-right bg-light">
                <form id="invoice-job-form" action="process" method="POST" autocomplete="off">
                    <div class="form-row">
                        <div class="col">
                            <div class="form-group row mb-0">
                                <label class="col-sm-4 col-form-label d-none d-lg-inline-block" for="new-invoice-number">Invoice No.</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control bg-white" id="new-invoice-number" placeholder="Invoice number.." name="new-invoice-number" maxlength="50">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <input type="hidden" name="job-id" value="<?php echo $job_id ?>">
                            <button type="submit" name="jobinvoiced" class="btn btn-sm btn-success">Job Invoiced</button>
                        </div>
                    </div>
                </form>
            </div>
            <?php } ?>
        </div>
    </div>
</div>