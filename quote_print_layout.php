<?php
/* Use company serverside details to fill out parts of the sheet */
$configs = $database->getConfigs();
?>

<!-- Quote Layout -->
<div class="block block-fx-shadow d-none" id="quote-print-layout">
    <div class="block-content">
        <div class="p-sm-4 p-xl-7">
            <div class="row mb-5">
                <div class="col-4">
                    <img class="img-fluid" src="<?php echo $dm->assets_folder; ?>/media/various/jaxxon_logo.png" alt="logo" width="100%">
                </div>
                <div class="col-8 text-right">
                    <h1 class="my-2">
                        <span class="font-w700">QUOTE SHEET</span><br>
                        <span class="font-size-h2" id="print-quote-number">JX-<?php echo str_pad($quote_details['unified_id'], 6, "0", STR_PAD_LEFT); ?></span>
                    </h1>
                </div>
            </div>
            <!-- Invoice Info -->

            <div class="row mb-3">
                <!-- Company Info -->
                <div class="col-6">
                    <p class="h3">Client</p>
                    <span class="font-weight-bold" id="print-client-name"></span><br>
                    <span id="print-company-name"></span><br>
                    <span id="print-address-1"></span><br>
                    <span id="print-address-2"></span><br>
                    <span id="print-address-3"></span><br>

                </div>
                <!-- END Company Info -->

                <!-- Client Info -->
                <div class="col-6 text-right">
                    <p class="h3"><?php echo $configs['COMPANY_NAME'] ?></p>
                    <address>
                        <strong>Marlon Harris</strong><br>
                        0274 822 547 <br>sales@jaxxonconcretepumps.co.nz<br>
                        <strong>Karl Hanlon</strong><br>
                        0274 822 545 <br>karl@jaxxonconcretepumps.co.nz
                    </address>
                </div>
                <!-- END Client Info -->
            </div>
            <!-- END Invoice Info -->

            <!-- Job details and specifications -->
            <div class="row mb-3">
                <div class="col-6">
                    <div class="block">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Job <small>Details</small></h3>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="block-content">
                                    <ul class="fa-ul list-icons">
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-map-marker-alt"></i>
                                            </span>
                                            <div class="font-w600">Address</div>
                                            <div id="print-job-address-1" class="text-muted"></div>
                                            <div id="print-job-address-2" class="text-muted"></div>
                                            <div id="print-job-address-3" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                            <div class="font-w600">Job Date</div>
                                            <div id="print-date" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-clock"></i>
                                            </span>
                                            <div class="font-w600">Start Time</div>
                                            <div id="print-time" class="text-muted"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="block-content">
                                    <ul class="fa-ul list-icons">
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-road"></i>
                                            </span>
                                            <div class="font-w600">Travel Distance</div>
                                            <div id="print-range" class="text-muted"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-6">
                    <div class="block">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Job <small>Specifications</small></h3>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="block-content">
                                    <ul class="fa-ul list-icons">
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-landmark"></i>
                                            </span>
                                            <div class="font-w600">Job Type</div>
                                            <div id="print-job-type" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-square"></i>
                                            </span>
                                            <div class="font-w600">Cubics m<sup>3</sup></div>
                                            <div id="print-cubics" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-truck-monster"></i>
                                            </span>
                                            <div class="font-w600">Assigned Truck</div>
                                            <div id="print-pump-truck" class="text-muted"></div>
                                        </li>
                                        <!-- <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-weight"></i>
                                            </span>
                                            <div class="font-w600">Megapascal</div>
                                            <div id="print-megapascal" class="text-muted"></div>
                                        </li> -->
                                    </ul>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="block-content">
                                    <ul class="fa-ul list-icons">
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-vial"></i>
                                            </span>
                                            <div class="font-w600">Concrete Type</div>
                                            <div id="print-concrete-type" class="text-muted"></div>
                                        </li>
                                        <!-- Softly removing mix type -->
                                        <li class="d-none">
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-vials"></i>
                                            </span>
                                            <div class="font-w600 d-none">Extra Concrete Types</div>
                                            <div id="print-mix-type d-none" class="text-muted"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Job details and specifications -->

                <!-- Table -->
                <div class="table-responsive push">
                    <table class="table table-bordered">
                        <thead class="bg-body">
                            <tr>
                                <th class="text-center" style="width: 60px;"></th>
                                <th></th>

                                <th class="text-right" style="width: 120px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">1</td>
                                <td>
                                    <p class="font-w600 mb-1">Establishment Fee</p>

                                </td>
                                <td id="print-est-fee" class="text-right">Error</td>
                            </tr>
                            <tr>
                                <td class="text-center">2</td>
                                <td>
                                    <p class="font-w600 mb-1">Quoted <span class="table-rate-description">** Rate</span></p>
                                    <div id="print-rate-message" class="text-muted"></div>
                                </td>
                                <td id="print-cubic-rate" class="text-right">Error</td>
                            </tr>
                            <tr>
                                <td class="text-center">3</td>
                                <td>
                                    <p class="font-w600 mb-1">Travel Fee</p>
                                    <div id="print-travel-fee-message" class="text-muted"></div>
                                </td>

                                <td id="print-travel-fee" class="text-right">Error</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="font-w600 text-uppercase text-right bg-info-light">Subtotal</td>
                                <td id="print-sub-total" class="text-right bg-info-light">Error</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="font-w600 text-right">
                                    <!-- <small>Applied before GST</small>  -->Discount <span id="print-discount-rate"></span></td>
                                <td id="print-discount-total" class="text-right">There's something wrong, I can feel it</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="font-w600 text-uppercase text-right bg-info-lighter">SUBTOTAL AFTER DISCOUNT</td>
                                <td id="print-sub-total-inc-discount" class="text-right bg-info-lighter">Error</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="font-w600 text-right">GST 15%</td>
                                <td id="print-gst" class="text-right">Error</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="font-w700 text-uppercase text-right bg-body-light">TOTAL QUOTE INC GST</td>
                                <td id="print-cubic-cost" class="font-w700 text-right bg-body-light">ERROR</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END Table -->

            <!-- Footer -->
            <p class="text-muted text-center my-5">
                <?php echo $configs['COMPANY_NAME'] ?> 2020. Quote is valid for 30 days from date of quote and is based on the above information provided.<br>
                A concrete disposal and pump washdown charge may be incurred if there is no wash out facility at the job site.
            </p>
            <!-- END Footer -->
        </div>
    </div>
</div>
<!-- END Quote Layout -->