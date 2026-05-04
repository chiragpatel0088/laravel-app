<!-- Confirm customer reason for declining quote -->
<div class="modal fade" id="modal-customer-decline-quote" tabindex="-1" role="dialog" aria-labelledby="modal-customer-decline-quote"  data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-primary-dark">
                    <h3 class="block-title">Quote Decline</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <form id="decline-quote-form" action="process" method="POST">
                    <div class="block-content">
                        <p>Before declining the quote, please select a reason.</p>
                        <div class="mb-5">
                            <div id="radio-section" class="form-group row mt-1">
                                <label class="col-12">Reason</label>
                                <div class="col-6">
                                    <div class="custom-control custom-radio custom-control-primary custom-control-lg mb-1">
                                        <input type="radio" class="custom-control-input" id="customer-decline-reason1" value="Job Cancelled" name="customer-decline-reason">
                                        <label class="custom-control-label" for="customer-decline-reason1">Job Cancelled</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-primary custom-control-lg mb-1">
                                        <input type="radio" class="custom-control-input" id="customer-decline-reason2" value="Price" name="customer-decline-reason">
                                        <label class="custom-control-label" for="customer-decline-reason2">Price</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="custom-control custom-radio custom-control-primary custom-control-lg mb-1">
                                        <input type="radio" class="custom-control-input" id="customer-decline-reason3" value="Health and Safety" name="customer-decline-reason">
                                        <label class="custom-control-label" for="customer-decline-reason3">OH&S</label>
                                    </div>
                                    <div class="custom-control custom-radio custom-control-primary custom-control-lg mb-1">
                                        <input type="radio" class="custom-control-input" id="customer-decline-reason4" value="Other" name="customer-decline-reason">
                                        <label class="custom-control-label" for="customer-decline-reason4">Other</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mt-1 d-none" id="other-reason-section">
                                <div class="col-12">
                                    <label for="other-reason-textarea">Please briefly describe..</label>
                                    <textarea disabled maxlength="250" class="form-control form-control-alt" id="other-reason-textarea" name="other-reason-textarea" rows="3" placeholder="Add your other reason here"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-right bg-light">
                        <input type="hidden" name="quote-id" id="quote-id" value="<?php echo $quote_id ?>">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" name="subcustomerdeclinequote" class="btn btn-sm btn-primary">Decline Quote</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Confirm customer reason for declining quote  -->

<?php $dm->get_js('js/modals/modal_customer_decline_quote.js'); ?>