<!-- Confirm user copying job -->
<div class="modal fade" id="modal-copy-job" tabindex="-1" role="dialog" aria-labelledby="modal-copy-job"  data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-info">
                    <h3 class="block-title">Copy Job</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <form id="user-copy-job-form" action="process" method="POST">
                    <div class="block-content">
                        <p>You are copying the current job, click 'Copy' to continue.</p>                        
                    </div>
                    <div class="block-content block-content-full text-right bg-light">
                        <input type="hidden" name="job-id" id="job-id" value="<?php echo $job_id ?>">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" name="subcopyjob" class="btn btn-sm btn-info">Copy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Confirm user copying job  -->