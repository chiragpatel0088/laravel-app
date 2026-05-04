<!-- Job report based on year selected -->
<div class="modal fade" id="modal-sales-per-operator-report" tabindex="-1" role="dialog" aria-labelledby="modal-sales-per-operator-report" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header">
                    <h3 class="block-title">Choose Year for Sales per Operator Report</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <form action="sales_per_operator_report_pdf" target="_blank" method="POST">
                    <div class="block-content">
                        <div class="mb-5">
                            <div class="form-group row mt-1">
                                <div class="col-12">
                                    <label for="job-report-year">Fiscal Year</label>
                                    <!-- SOURCE: https://dcblog.dev/financial-year-select-menu -->
                                    <select name="job-report-year" class="form-control" required>
                                        <option value="">Select..</option>
                                        <?php
                                        $dates = range('2020', date('Y'));
                                        foreach ($dates as $date) {
                                            $year = $date . '-' . ($date + 1);
                                            echo "<option value='$date'>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full text-right bg-light">
                        <input type="hidden" name="job-id" id="job-id" value="<?php echo $job_id ?>">
                        <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Job report based on year selected  -->