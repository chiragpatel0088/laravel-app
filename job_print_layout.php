<?php
/* Use company serverside details to fill out parts of the sheet */
$configs = $database->getConfigs();
?>
<!-- Page CSS -->
<?php $dm->get_css('css/pages/job_print_layout.css'); ?>

<!-- Job Layout -->
<div class="block block-fx-shadow d-none" id="job-print-layout">
    <div class="block-content">
        <div class="p-sm-4 p-xl-7">
            <div class="row mb-5">
                <div class="col-xl-4 col-lg-4 col-md-4 col-xs-12">
                    <img class="img-fluid" src="<?php echo $dm->assets_folder; ?>/media/various/jaxxon_logo.png" alt="logo" width="100%">
                </div>
                <div class="col-xl-8 col-lg-8 col-md-8 col-xs-12 text-right responsive-center">
                    <h1 class="my-2">
                        <span class="font-w700">JOB SHEET</span><br>
                        <span class="font-size-h2" id="print-job-number">JX-<?php echo str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT); ?></span>
                    </h1>
                </div>
            </div>
            <!-- Invoice Info -->

            <div class="row mb-5">
                <!-- Company Info -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xl-6">
                    <p class="h3">Client</p>
                    <span class="font-weight-bold print-client-name"></span>,
                    <span id="print-company-name"></span><br>
                    <span id="print-address-1"></span><br>
                    <span id="print-address-2"></span><br>
                    <span id="print-address-3"></span><br>
                    <span id="print-client-phone" class="text-muted"></span>
                    <span id="print-client-mobile" class="text-muted"></span>
                    <div id="print-client-email" class="text-muted"></div>

                </div>
                <!-- END Company Info -->

                <!-- Client Info -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xl-6 text-right responsive-left">
                    <p class="h3"><?php echo $configs['COMPANY_NAME'] ?></p>
                    <address>
                        <strong>Cameron Locket</strong><br>
                        0274 822 547 <br>
                        <strong>Marlon Harris</strong><br>
                        0274 822 548 <br>sales@jaxxonconcretepumps.co.nz<br>
                        <!-- <strong>Karl Hanlon</strong><br>
                        0274 822 545 <br>karl@jaxxonconcretepumps.co.nz -->
                    </address>
                </div>
                <!-- END Client Info -->
            </div>
            <!-- END Invoice Info -->

            <!-- Job details and specifications -->
            <div class="row mb-2">
                <div class="col-xl-4 col-lg-4 col-md-4 col-xs-12">
                    <div class="block">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Job <small>Details</small></h3>
                        </div>
                        <div class="row">
                            <div class="col-12">
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
                <div class="col-xl-4 col-lg-4 col-md-4 col-xs-12">
                    <div class="block">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Job <small>Specifications</small></h3>
                        </div>
                        <div class="row">
                            <div class="col-12">
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
                                                <i class="fa fa-vial"></i>
                                            </span>
                                            <div class="font-w600">Concrete Type</div>
                                            <div id="print-concrete-type" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-weight"></i>
                                            </span>
                                            <div class="font-w600">MPa</div>
                                            <div id="print-megapascal" class="text-muted"></div>
                                        </li>                                        
                                        <!-- Softly removing mix type -->
                                        <li class="d-none">
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-vials"></i>
                                            </span>
                                            <div class="font-w600">Extra Concrete Types</div>
                                            <div id="print-mix-type" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-truck-monster"></i>
                                            </span>
                                            <div class="font-w600">Assigned Truck</div>
                                            <div id="print-pump-truck" class="text-muted"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-4 col-md-4 col-xs-12">
                    <div class="block">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Contact <small>Information</small></h3>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="block-content">
                                    <ul class="fa-ul list-icons">
                                        <!-- <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-landmark"></i>
                                            </span>
                                            <div class="font-w600">Client</div>
                                            <span class="print-client-name" class="text-muted"></span>
                                            <div id="print-client-phone" class="text-muted"></div>
                                            <div id="print-client-mobile" class="text-muted"></div>
                                            <div id="print-client-email" class="text-muted"></div>
                                        </li> -->
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-box-open"></i>
                                            </span>
                                            <div class="font-w600">Supplier</div>
                                            <span id="print-supplier-name" class="text-muted"></span>
                                            <div id="print-supplier-phone" class="text-muted"></div>
                                            <div id="print-supplier-mobile" class="text-muted"></div>
                                            <div id="print-supplier-email" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-hard-hat"></i>
                                            </span>
                                            <div class="font-w600">Layer</div>
                                            <span id="print-layer-name" class="text-muted"></span>
                                            <div id="print-layer-phone" class="text-muted"></div>
                                            <div id="print-layer-mobile" class="text-muted"></div>
                                            <div id="print-layer-email" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-user-alt"></i>
                                            </span>
                                            <div class="font-w600">Operator</div>
                                            <span id="print-operator-name" class="text-muted"></span>
                                            <div id="print-operator-phone" class="text-muted"></div>
                                            <div id="print-operator-email" class="text-muted"></div>
                                        </li>
                                        <li>
                                            <span class="fa-li text-primary">
                                                <i class="fa fa-user-tie"></i>
                                            </span>
                                            <div class="font-w600">Foreman</div>
                                            <span id="print-foreman-name" class="text-muted"></span>
                                            <div id="print-foreman-phone" class="text-muted"></div>
                                            <div id="print-foreman-email" class="text-muted"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END Job details and specifications -->
            </div>

            <!-- Footer -->
            <p class="text-muted text-center mt-4 my-2">
                <?= $configs['COMPANY_NAME'] . ' ' . date('Y') ?> <br>
            </p>
            <!-- END Footer -->
        </div>
    </div>
</div>
<!-- END Job Layout -->