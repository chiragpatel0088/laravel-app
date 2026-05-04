<?php

/**
 * backend/views/inc_side_overlay.php
 *
 * Author: Micro Solutions Ltd / Pixelcave
 *
 * The side overlay of each page (Backend pages)
 *
 */


?>

<!-- Side Overlay-->
<aside id="side-overlay">
    <!-- Side Header -->
    <div class="bg-image" style="background-image: url('<?php echo $dm->assets_folder; ?>/media/various/bg_side_overlay_header.jpg');">
        <div class="bg-primary-op">
            <div class="content-header">
                <!-- User Avatar -->
                <a class="img-link mr-1" href="#">
                    <?php $dm->get_avatar(10, '', 48); ?>
                </a>
                <!-- END User Avatar -->

                <!-- User Info -->
                <div class="ml-2">
                    <a class="text-white font-w600" href="#"><?php echo $session->userinfo['user_firstname'] . ' ' . $session->userinfo['user_lastname']; ?></a>
                    <div class="text-white-75 font-size-sm font-italic">Level: <?php echo $database->userLevelToName($session->userinfo['user_level']); ?></div>
                </div>
                <!-- END User Info -->

                <!-- Close Side Overlay -->
                <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
                <a class="ml-auto text-white" href="javascript:void(0)" data-toggle="layout" data-action="side_overlay_close">
                    <i class="fa fa-times-circle"></i>
                </a>
                <!-- END Close Side Overlay -->
            </div>
        </div>
    </div>
    <!-- END Side Header -->

    <!-- Side Content -->
    <div class="content-side">
        <!-- Side Overlay Tabs -->
        <div class="block block-transparent pull-x pull-t">
            <ul class="nav nav-tabs nav-tabs-block nav-justified" data-toggle="tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#so-profile">
                        <i class="far fa-fw fa-edit"></i>
                    </a>
                </li>
                <li class="">
                    <a class="nav-link" id="sign-out-button" href="process">
                        <i class="fa fa-fw fa-arrow-alt-circle-left"></i> Sign Out
                    </a>
                </li>
            </ul>
            <div class="block-content tab-content overflow-hidden">
                <!-- Settings Tab -->
                <div class="tab-pane pull-x fade fade-up" id="so-settings" role="tabpanel">
                    <div class="block mb-0">
                        <!-- Content/Other settings go here. Refer to Dashmix docs for styling/structure -->
                    </div>
                </div>
                <!-- END Settings Tab -->
                <!-- Profile -->
                <div class="tab-pane pull-x fade fade-up show active" id="so-profile" role="tabpanel">
                    <form class="js-validation" action="process" method="post">
                        <div class="block mb-0">
                            <!-- Personal -->
                            <div class="block-content block-content-sm block-content-full bg-body">
                                <span class="text-uppercase font-size-sm font-w700">Personal</span>
                            </div>
                            <div class="block-content block-content-full">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input type="text" disabled class="form-control" value="<?php echo $session->username; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="so-profile-name">Name</label>
                                    <input type="text" disabled class="form-control" value="<?php echo $session->userinfo['user_firstname'] . ' ' . $session->userinfo['user_lastname']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="so-profile-phone">Phone</label>
                                    <input type="text" disabled class="form-control" value="<?php echo $session->userinfo['user_phone']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="so-profile-phone">Email</label>
                                    <input type="text" disabled class="form-control" value="<?php echo $session->userinfo['user_email']; ?>">
                                </div>                                
                            </div>
                            <!-- END Personal -->



                        </div>
                    </form>
                </div>
                <!-- END Profile -->
            </div>
        </div>
        <!-- END Side Overlay Tabs -->
    </div>
    <!-- END Side Content -->
</aside>
<!-- END Side Overlay -->