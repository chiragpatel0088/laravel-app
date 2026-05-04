<!-- Confirm user copying job -->
<div class="modal fade" id="modal-uncomplete-job" tabindex="-1" role="dialog" aria-labelledby="modal-uncomplete-job"  data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-xpro">
                    <h3 class="block-title">Undo Completed Job</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <form id="user-undo-complete-job-form" action="process" method="POST">
                    <div class="block-content">
                        <p>You are undoing a currently completed job, click 'Undo' to continue.</p>                        
                        <p><em>The job completion details (Finish time, actual cubics etc.) from the operator will be cleared. Take note of completion details if required.</em></p>
                    </div>
                    <div class="block-content block-content-full text-right bg-light">
                        <input type="hidden" name="job-id" id="job-id" value="<?php echo $job_id ?>">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" name="subundocompletejob" class="btn btn-sm btn-info bg-xpro">Undo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Confirm user copying job  -->