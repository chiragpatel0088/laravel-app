<div class="modal fade" id="modal-job-edit-reason" tabindex="-1" role="dialog" aria-labelledby="modal-job-edit-reason" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-primary-dark">
                    <h3 class="block-title">Reason for updating invoice details</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <form id="invoice-update-reason-form" action="#" method="POST">
                    <div class="block-content">
                        <p>Please provide a description of edits and reason(s) for updating the invoice details.</p>
                        <div class="form-group">
                            <label for="invoice-reason-to-edit">Reason and Description</label>
                            <textarea required class="form-control form-control-alt" name="invoice-reason-to-edit" rows="7" placeholder="Reason here.."></textarea>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-right bg-light">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary">Update Invoice Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $dm->get_js('js/modals/modal_reason_edit_invoice.js'); ?>