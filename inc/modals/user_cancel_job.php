<!-- Confirm user reason for cancelling job -->
<div class="modal fade" id="modal-user-cancel-job" tabindex="-1" role="dialog" aria-labelledby="modal-user-cancel-job"  data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-danger">
                    <h3 class="block-title">Job Cancellation</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <form id="user-cancel-job-form" action="process" method="POST">
                    <div class="block-content">
                        <p>Before cancelling the job, please describe the reason.</p>
                        <div class="mb-5">
                            <div class="form-group row mt-1" id="other-reason-section">
                                <div class="col-12">
                                    <textarea maxlength="250" class="form-control form-control-alt" id="other-reason-textarea" name="other-reason-textarea" rows="3" placeholder="Add reason here.."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-right bg-light">
                        <input type="hidden" name="job-id" id="job-id" value="<?php echo $job_id ?>">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" name="subusercanceljob" class="btn btn-sm btn-danger">Cancel Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Confirm user reason for cancelling job  -->

<?php $dm->get_js('js/modals/modal_user_cancel_job.js'); ?>