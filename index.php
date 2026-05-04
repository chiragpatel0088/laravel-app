<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/_global/views/head_start.php'; ?>
<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>
<?php if($session->logged_in){ header("Location: jobs_panel"); } // IF user is already logged in, take em to the dashboard ?>

<!-- Page Content -->
<div class="row no-gutters justify-content-center bg-body-dark">
    <div class="hero-static col-sm-10 col-md-8 col-xl-6 d-flex align-items-center p-2 px-sm-0">
        <!-- Sign In Block -->
        <div class="block block-rounded block-transparent block-fx-pop w-100 mb-0 overflow-hidden bg-image" style="background-image: url('<?php echo $dm->assets_folder; ?>/media/photos/erw766_photo.jpg');">
            <div class="row no-gutters">
                <div class="col-md-6 order-md-1 bg-white">
                    <div class="block-content block-content-full px-lg-5 py-md-5 py-lg-6">
                        <!-- Header -->
                        <div class="mb-2 text-center">
                            <a class="link-fx font-w700 font-size-h1" href="index">
                                <span class="text-dark">Spindle</span><span class="text-primary"> System</span>
                            </a>
                            <p class="text-uppercase font-w700 font-size-sm text-muted">Sign In</p>
                        </div>
                        <!-- END Header -->

                        <!-- Sign In Form -->
                        <!-- jQuery Validation (.js-validation-signin class is initialized in js/pages/op_auth_signin.js) -->
                        <!-- For more info and examples you can check out https://github.com/jzaefferer/jquery-validation -->
                        <form class="js-validation-signin" action="process" method="post">
                            <div class="form-group">
                                <input type="text" class="form-control form-control-alt" id="login-username" name="user" placeholder="Username">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-alt" id="login-password" name="pass" placeholder="Password">
                            </div>

                            <?php
                                if ($form->num_errors > 0) {
                                    $errmsg = $form->getErrorArray();
                                    ?>
                            <div class="alert alert-danger alert-dismissable" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <p class="mb-0"><?php echo $errmsg['user']; ?></p>
                            </div>

                            <?php
                                }
                                ?>

                            <div class="form-group">
                                <input type="hidden" name="sublogin" value="1">
                                <button type="submit" class="btn btn-block btn-hero-primary">
                                    <i class="fa fa-fw fa-sign-in-alt mr-1"></i> Sign In
                                </button>
                                <p class="mt-3 mb-0 d-lg-flex justify-content-lg-between">
                                    <div class="custom-control custom-checkbox custom-control-dark custom-control-inline mb-1">
                                        <input type="checkbox" class="custom-control-input" id="example-cb-custom-dark-lg2" name="remember" checked="">
                                        <label class="custom-control-label" for="example-cb-custom-dark-lg2">Keep me signed in!</label>
                                    </div>
                                </p>
                            </div>
                        </form>
                        <!-- END Sign In Form -->
                    </div>
                </div>
                <div class="col-md-6 order-md-0 bg-primary-dark-op d-flex align-items-center">
                    <div class="block-content block-content-full px-lg-5 py-md-5 py-lg-6">
                        <div class="media">
                            <div class="media-body">
                                <div class="align-items-lg-center justify-content-lg-center text-center pb-4">
                                    <div class="js-animation-object">
                                        <img class="img-fluid" src="<?php echo $dm->assets_folder; ?>/media/various/jaxxon_logo.png" alt="logo" width="100%">
                                        <p class="text-white">Jaxxon Concrete Pumps - Job Scheduling System</p>
                                        <span id="test-text"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END Sign In Block -->
    </div>
</div>
<!-- END Page Content -->

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>

<!-- Page JS Plugins -->
<?php $dm->get_js('js/plugins/jquery-validation/jquery.validate.min.js'); ?>

<!-- Microsoft Teams JavaScript API (via CDN) -->
<script src="https://statics.teams.microsoft.com/sdk/v1.5.2/js/MicrosoftTeams.min.js" crossorigin="anonymous"></script>
 

<!-- Page JS Code -->
<?php $dm->get_js('js/pages/index_page.js'); ?>

<?php require 'inc/_global/views/footer_end.php'; ?>

<!-- Developed by MicroSolution Ltd -->