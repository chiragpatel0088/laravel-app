<!-- Confirm email before sending quote link -->
<div class="modal fade" id="modal-confirm-quote-email" tabindex="-1" role="dialog" aria-labelledby="modal-confirm-quote-email" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-primary-dark">
                    <h3 class="block-title">Confirm Email Address</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <form id="send-quote-form" action="#" method="POST" onsubmit="return false;">
                <div class="block-content">
                    <p>Before sending the quote via email, please confirm the customer's email address is correct.</p>
                    <p>You can only send a quote to 1 email address at a time. You may choose to send the quote to another email address if required.</p>
                    <div class="mb-5">
                        <div class="form-group">
                            <label for="customer-quote-email-destination">Recipient Email</label>
                            <input type="email" class="form-control" id="customer-quote-email-destination" name="customer-quote-email-destination" placeholder="Customer Email..">
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full text-right bg-light">
                    <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                    <button type="submit" name="subsemailquote" class="btn btn-sm btn-primary">Send Quote</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Confirm email before sending quote link -->

<?php $dm->get_js('js/modals/modal_email_quote.js'); ?>