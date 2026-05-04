<!-- Ready for invoicing modal -->
<div class="modal fade" id="modal-ready-for-invoicing" tabindex="-1" role="dialog" aria-labelledby="modal-ready-for-invoicing" data-keyboard="false" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="ready-for-invoicing-form" action="process" method="POST" autocomplete="off">
                <input type="hidden" name="job-id" id="invoice-job-id">
                <div class="block block-themed block-transparent mb-0">
                    <div class="block-header bg-gd-aqua">
                        <h3 class="block-title">Job Ready for Invoicing</h3>
                        <div class="block-options">
                            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                                <i class="fa fa-fw fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="block-content">
                        <p>Check over the details before making the job ready for invoicing.</p>
                        <div class="form-group">
                            <label class="d-block">Rate Type</label>
                            <div class="custom-control custom-radio custom-control-inline custom-control-lg">
                                <input type="radio" class="custom-control-input" id="invoice-rate-type1" value="cubic" name="invoice-rate-type" checked="">
                                <label class="custom-control-label" for="invoice-rate-type1">Cubic Rate</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline custom-control-lg">
                                <input type="radio" class="custom-control-input" id="invoice-rate-type2" value="hourly" name="invoice-rate-type">
                                <label class="custom-control-label" for="invoice-rate-type2">Hourly Rate</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline custom-control-lg">
                                <input type="radio" class="custom-control-input" id="invoice-rate-type3" value="special" name="invoice-rate-type">
                                <label class="custom-control-label" for="invoice-rate-type3">Special Rate</label>
                            </div>
                        </div>
                        <div class="mb-5">
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
                                            <span class="p-2 font-w700" id="invoice-assigned-truck">ACF233 - Isuzu</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Establishment Fee
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-establishment-fee" name="invoice-establishment-fee" value="<?php echo $job_details['establishment_fee'] ?>" maxlength="6">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Range <a id="g-map-directions-link" class="badge badge-primary" target="_blank" href="https://maps.google.com?saddr=88+Gargan+Road+Tauriko+Tauranga&daddr=314+Cameron+Road+Tauriko+Tauranga"><i class="fa fa-map-marked-alt"></i> Google Maps</a>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
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
                                            <div id="invoice-travel-fee-calculation" class="text-muted font-w300 d-block">Please input range..</div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input " id="invoice-travel-fee" name="invoice-travel-fee" value="" maxlength="10">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Actual Cubics
                                            <div id="invoice-cubic-difference" class="font-w300 text-muted d-block"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-actual-cubics" name="invoice-actual-cubics" value="<?php echo $job_details['actual_cubics'] ?>" maxlength="10">
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
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-concrete-rate" name="invoice-concrete-rate" value="" maxlength="6">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Cubic Rate
                                            <div id="actual-cubic-rate-calculation" class="d-block font-w300 text-muted"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input bg-white" id="invoice-cubic-rate" name="invoice-cubic-rate" value="">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Job Hours
                                            <div id="actual-hours-calculation" class="d-block text-muted font-w300"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-actual-hours" name="invoice-actual-hours" onkeypress="timeInputValidation(event)" maxlength="10" placeholder="0:00">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Truck Hourly Rate
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-truck-rate" name="invoice-truck-rate" value="<?php echo $job_details['truck_rate'] ?>" maxlength="6">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Hourly Rate
                                            <div id="actual-hourly-rate-calculation" class="d-block text-muted font-w300"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-hourly-rate" name="invoice-hourly-rate" maxlength="10">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="washdown-fee-row">
                                        <td class="font-w600">
                                            Washdown Fee
                                            <div id="washdown-message" class="d-block text-muted font-w300"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-washdown-fee" name="invoice-washdown-fee" value="<?php if($job_details['onsite_washout'] == 1) echo $job_details['truck_washout']; else echo '0'; ?>" maxlength="10">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="disposal-fee-row">
                                        <td class="font-w600">
                                            Concrete Disposal Fee
                                            <div id="disposal-message" class="d-block text-muted font-w300"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-disposal-fee" name="invoice-disposal-fee" value="<?php if($job_details['onsite_disposal'] == 1) echo $job_details['truck_disposal_fee']; else echo '0'; ?>" maxlength="10">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Special Rate
                                            <div id="special-rate-message" class="d-block text-muted font-w300">Use if job has special rate</div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-special-rate" name="invoice-special-rate" value="0" maxlength="10">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Extra Cost #1
                                            <div class="d-block text-muted font-w300"><input type="text" class="form-control-plaintext form-control-sm p-0 text-muted" id="extra-cost-name-1" name="extra-cost-name-1" placeholder="Describe extra cost.." maxlength="250"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-special-1" name="invoice-special-1" value="0" maxlength="10">
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="font-w600">
                                            Extra Cost #2
                                            <div class="d-block text-muted font-w300"><input type="text" class="form-control-plaintext form-control-sm p-0 text-muted" id="extra-cost-name-2" name="extra-cost-name-2" placeholder="Describe extra cost.." maxlength="250"></div>
                                        </td>
                                        <td class="d-sm-table-cell text-right">
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        $
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm text-right charge-table-input" id="invoice-special-2" name="invoice-special-2" value="0" maxlength="10">
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
                                            <input type="text" autocomplete="off" class="form-control form-control-sm text-right charge-table-input" maxlength="6" placeholder="Input a discount value" id="invoice-discount" name="invoice-discount" value="<?php echo $job_details['discount'] ?>">
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
                                            GST 15%
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
                        </div>
                    </div>
                    <div class="block-content block-content-full text-right bg-light">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" name="jobreadyforinvoicing" class="btn btn-sm text-white btn bg-gd-aqua">Ready for Invoicing</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- END Ready for invoicing modal -->
<?php $dm->get_js('js/plugins/datatables/datetime-moment/moment.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/datetime-moment/datetime-moment.js'); ?>