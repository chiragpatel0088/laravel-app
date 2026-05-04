<!-- New job form Modal CSS -->
<?php $dm->get_css('css/modals/new_job_form.css'); ?>

<!-- New Job Form modal -->
<div class="modal fade" id="modal-new-job-form" role="dialog" aria-labelledby="modal-new-job-form" data-backdrop="static" data-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="block block-themed block-transparent mb-0">
                <div class="block-header bg-primary-dark">
                    <h3 class="block-title"><i class="fa fa-plus text-success mr-1"></i> New Inquiry</h3>
                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                            <i class="fa fa-fw fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content p-0">
                    <!-- END Post Job Form -->
                    <form action="process" method="POST" id="new-job-form" autocomplete="off">
                        <div class="block block-rounded bg-white">
                            <!-- Job Information section -->
                            <div class="block-content block-content-full">
                                <!-- <h2 class="content-heading">Details</h2> -->
                                <div class="row items-push">
                                    <div class="col-lg-12 mb-0">
                                        <div class="form-group">
                                            <label for="new-customer-select">
                                                Customer <span class="text-danger">*</span>
                                                <table class="font-size-sm d-inline" id="js-dataTable-newCustomer">
                                                    <thead>
                                                        <tr class="text-uppercase">
                                                            <th class="font-w700">Company</th>
                                                            <th class="font-w700">Contact Name</th>
                                                            <th class="font-w700">Email</th>
                                                            <th class="d-none d-sm-table-cell font-w700">Contact Phone</th>
                                                            <th class="font-w700">Mobile</th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </label>
                                            <select class="js-select2 form-control" id="new-customer-select" name="new-customer-select" style="width: 100%;">
                                            </select>
                                        </div>
                                        <div class="form-group form-row">
                                            <div class="col-6">
                                                <label for="new-job-date">Job Date <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-6">
                                                <label for="new-job-timing">Start Time</label>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="js-datepicker form-control bg-white" id="new-job-date" readonly="true" name="new-job-date" data-week-start="1" data-autoclose="true" data-today-highlight="true" data-date-format="dd/mm/yyyy" placeholder="dd/mm/yyyy">
                                            </div>
                                            <div class="col-6">
                                                <input type="time" class="form-control bg-white" id="new-job-timing" name="new-job-timing" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-time_24hr="true">
                                            </div>
                                        </div>
                                        <div class="form-group form-row">
                                            <div class="col-6">
                                                <label for="new-job-address-1">Address <i class="fa fa-info-circle" data-toggle="popover" data-animation="true" data-placement="top" title="Address Autocomplete" data-content="Start typing in your address in the first address field and a autocomplete dropdown will show, click on the suggestion that fits the job to autofill out address fields"></i> <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-6">
                                                <span id="new-approx-range-container" style="display:none">Approx Range: <span id="new-approx-range" class="text-info"></span>km</span>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="js-maxlength form-control" id="new-job-address-1" name="new-job-address-1" placeholder="Start typing an address.." maxlength="100">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="js-maxlength form-control" id="new-job-address-2" name="new-job-address-2" placeholder="Address 2" maxlength="100">
                                            </div>
                                        </div>
                                        <div class="form-group form-row">
                                            <div class="col-4">
                                                <input type="text" class="js-maxlength form-control" id="new-job-suburb" name="new-job-suburb" placeholder="Suburb" maxlength="100">
                                            </div>
                                            <div class="col-4">
                                                <input type="text" class="js-maxlength form-control" id="new-job-city" name="new-job-city" placeholder="City" maxlength="100">
                                            </div>
                                            <div class="col-4">
                                                <input type="text" class="js-maxlength form-control" id="new-job-post-code" name="new-job-post-code" placeholder="Post Code" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="form-group form-row">
                                            <div class="col-4">
                                                <label for="range-address">Range</label>
                                                <input type="text" class="js-maxlength form-control" id="range-address" name="range-address" placeholder="Approx distance (km)" maxlength="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row items-push">
                                    <div class="col-lg-12 mb-0">
                                        <div class="form-group form-row">
                                            <div class="col-4">
                                                <label for="new-job-type">Job Type</label>
                                            </div>
                                            <div class="col-4">
                                                <label for="new-cubics">Cubics</label>
                                            </div>
                                            <div class="col-4">
                                                <label for="new-mpa">MPa</label>
                                            </div>
                                            <div class="col-4">
                                                <select class="js-select2 form-control" id="new-job-type-select" name="new-job-type-select" style="width: 100%;">
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <div class="input-group">
                                                    <input type="text" class="js-maxlength form-control" id="new-cubics" name="new-cubics" maxlength="8">
                                                    <div class="input-group-append d-none d-xl-block d-lg-block d-md-block d-sm-none d-xs-none">
                                                        <span class="input-group-text">
                                                            m<sup>3</sup>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="input-group">
                                                    <input type="text" class="js-maxlength form-control" id="new-mpa" name="new-mpa" value="25" maxlength="5">
                                                    <div class="input-group-append d-none d-xl-block d-lg-block d-md-block d-sm-none d-xs-none">
                                                        <span class="input-group-text">
                                                            MPa
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group form-row">
                                            <div class="col-6">
                                                <label for="new-concrete-type-select">Concrete Type</label>
                                                <select class="js-select2 form-control" id="new-concrete-type-select" name="new-concrete-type-select" style="width: 100%;">
                                                </select>
                                            </div>
                                            <div class="col-6 d-none">
                                                <label for="new-mix-type-select">Mix Type</label>
                                                <select class="js-select2 form-control" id="new-mix-type-select" name="new-mix-type-select" style="width: 100%;">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row items-push">
                                    <div class="col-lg-12 mb-0">
                                        <div class="form-group form-row">
                                            <div class="col-4">
                                                <label for="new-truck-select">Truck</label>
                                            </div>
                                            <div class="col-4">
                                                <label for="new-boom">Boom Size</label>
                                            </div>
                                            <div class="col-4">
                                                <label for="new-capacity">Capacity</label>
                                            </div>
                                            <div class="col-4">
                                                <select class="js-select2 form-control" id="new-truck-select" name="new-truck-select" style="width: 100%;">
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="new-boom" name="new-boom" disabled>
                                                    <div class="input-group-append d-none d-sm-block">
                                                        <span class="input-group-text">
                                                            m
                                                        </span>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="col-4">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="new-capacity" name="new-capacity" disabled>
                                                    <div class="input-group-append d-none d-sm-block">
                                                        <span class="input-group-text">
                                                            m<sup>3</sup>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group form-row">
                                            <div class="col-3">
                                                <label for="new-rate">Hourly Rate</label>
                                                <input type="text" class="form-control" id="new-rate" name="new-rate" disabled>
                                            </div>
                                            <div class="col-3">
                                                <label for="new-min">Minimum</label>
                                                <input type="text" class="form-control" id="new-min" name="new-min" disabled>
                                            </div>
                                            <div class="col-3">
                                                <label for="new-travel">Travel Rate</label>
                                                <input type="text" class="form-control" id="new-travel" name="new-travel" disabled>
                                            </div>
                                            <div class="col-3">
                                                <label for="new-washout">Washdown</label>
                                                <input type="text" class="form-control" id="new-washout" name="new-washout" disabled>
                                            </div>
                                        </div>
                                        <div class="form-group form-row">
                                            <div class="col-3">
                                                <label for="new-disposal-fee">Concrete Disposal Fee</label>
                                                <input type="text" class="form-control" id="new-disposal-fee" name="new-disposal-fee" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- END Job Information section -->

                            <!-- Submit Form -->
                            <div class="block-content block-content-full">
                                <div class="row items-push">
                                    <div class="col-lg-12">
                                        <div class="form-group form-row">
                                            <div class="col-6">
                                                <button type="submit" name="subnewjob" class="btn btn-hero-success mb-3">
                                                    <i class="fa fa-toolbox mr-1"></i> Create Job
                                                </button>
                                            </div>
                                            <div class="col-6 text-right">
                                                <button type="submit" name="subnewquote" class="btn btn-hero-info">
                                                    <i class="fa fa-file-invoice mr-1"></i> Create Quote
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END Submit Form -->
                            </div>

                            <!-- Hidden fields for range calculation -->
                            <input type="hidden" name="latitude-address" id="latitude-address">
                            <input type="hidden" name="longitude-address" id="longitude-address">                            
                    </form>
                    <!-- END Post Job Form -->
                </div>
            </div>
            <div class="block-content block-content-full text-right bg-light">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- END New Job Form modal -->

<!-- ENSURE YOU HAVE THE REQUIRED JS PLUGINS ADDED IN THE PARENT PAGE -->
<script src="https://www.addy.co.nz/scripts/addy.js?key=05c6c9382e3a4165a787c8a996a13a98&loadcss=true&includeRegion=3" async defer></script>
<?php $dm->get_js('js/modals/modal_new_job_form.js'); ?>