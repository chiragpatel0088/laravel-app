<?php

/**
 * dashboards/corporate_slim/views/inc_header.php
 *
 * Author: pixelcave
 *
 * The header of each page
 *
 */
?>

<?php
/* Use company serverside details to fill out parts of the sheet */
$configs = $database->getConfigs();
?>


<!-- CSS for notification dropdown -->
<style>
    .notification-dropdown {
        max-height: 60vh;
        overflow-y: scroll;
    }

    .notification-dropdown::-webkit-scrollbar {
        width: 3px;
    }

    .notification-dropdown::-webkit-scrollbar-track {
        webkit-box-shadow: inset 0 0 6px #636363;
        -webkit-border-radius: 10px;
        border-radius: 10px;
    }

    .notification-dropdown::-webkit-scrollbar-thumb {
        -webkit-border-radius: 10px;
        border-radius: 10px;
        background: #636363;
        -webkit-box-shadow: none;
    }

    .notification-dropdown::-webkit-scrollbar-thumb:window-inactive {
        background: #636363;

    }

    /* CSS for animating unread notifications */
    .unread-notification {
        background: linear-gradient(45deg, #f3f5f9 22%, #b8b8b8 33%, #b8b8b8 33%, #f3f5f9 55%);
        /* linear-gradient(45deg, #f3f5f9 33%, #f3f5f9 34%, #e2e2e2 33%, #e2e2e2 33%, #f3f5f9 43%, #e2e2e2 22%, #f3f5f9 2%); */
        background-size: 700% 600%;
        -webkit-animation: Gradient 5s ease infinite;
        -moz-animation: Gradient 5s ease infinite;
        animation: Gradient 5s ease infinite;
    }

    @-webkit-keyframes Gradient {
        0% {
            background-position: 0% 50%
        }

        50% {
            background-position: 100% 50%
        }

        100% {
            background-position: 0% 50%
        }
    }

    @-moz-keyframes Gradient {
        0% {
            background-position: 0% 50%
        }

        50% {
            background-position: 100% 50%
        }

        100% {
            background-position: 0% 50%
        }
    }

    @keyframes Gradient {
        0% {
            background-position: 0% 50%
        }

        50% {
            background-position: 100% 50%
        }

        100% {
            background-position: 0% 50%
        }
    }
</style>

<input type="hidden" id="user_id" value='<?php echo $session->userinfo['ID'] ?>'>

<!-- Header -->
<header id="page-header">
    <!-- Header Content -->
    <div class="content-header">
        <!-- Left Section -->
        <div class="d-flex align-items-center">
            <!-- Logo -->
            <a class="text-dual link-fx" href="index.php">
                <i class="fa fa-kiwi-bird mr-1"></i> <span class="d-none d-lg-inline"><?php echo explode(" ", $configs['COMPANY_NAME'])[0]; ?><span class="font-w700"> <?php echo substr(strstr($configs['COMPANY_NAME'], " "), 1); ?></span></span>
            </a>
            <!-- END Logo -->

            <!-- Menu -->
            <ul class="nav-main nav-main-horizontal nav-main-hover d-none d-lg-block ml-4">
                <?php $dm->build_nav(); ?>
            </ul>
            <!-- END Menu -->
        </div>
        <!-- END Left Section -->

        <!-- Right Section -->
        <div>
            <?php if($session->userinfo['user_level'] != 1) { ?>
            <!-- New inquiry button -->
            <div class="dropdown d-inline-block ml-1">
                <button type="button" class="btn btn-dual dropdown-item" data-toggle="modal" data-target="#modal-new-job-form" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-fw fa-plus"></i>
                </button>
            </div>
            <!-- END New inquiry button  -->            
            <?php } ?>
            <!-- Search form in larger screens -->
            <div class="dropdown d-inline-block ml-1">
                <!-- Toggle Side Overlay -->
                <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
                <button type="button" class="btn btn-dual dropdown-item" data-toggle="layout" data-action="side_overlay_toggle" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-fw fa-cog"></i>
                </button>
            </div>
            <!-- END Search form in larger screens -->

            <!-- Notifications Dropdown -->
            <div class="dropdown d-inline-block ml-1">
                <button type="button" class="btn btn-dual" id="page-header-notifications-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-fw fa-bell"></i>
                    <span data-toggle="appear" data-class="animated flipInX" class="invisible badge badge-secondary badge-pill notification-count">...</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg p-0" aria-labelledby="page-header-notifications-dropdown">
                    <div class="bg-primary-darker rounded-top font-w600 text-white text-center p-3">
                        Notifications
                    </div>
                    <div class="notification-dropdown">
                        <ul class="nav-items my-2 notification-container">
                            <p>No notifications... </p>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- END Notifications Dropdown -->

            <!-- Toggle Sidebar -->
            <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
            <button type="button" class="btn btn-dual d-lg-none ml-1" data-toggle="layout" data-action="sidebar_toggle">
                <i class="fa fa-fw fa-bars"></i>
            </button>
            <!-- END Toggle Sidebar -->
        </div>
        <!-- END Right Section -->
    </div>
    <!-- END Header Content -->

    <!-- Header Loader -->
    <!-- Please check out the Loaders page under Components category to see examples of showing/hiding it -->
    <div id="page-header-loader" class="overlay-header bg-primary-darker">
        <div class="content-header">
            <div class="w-100 text-center">
                <i class="fa fa-fw fa-2x fa-sun fa-spin text-white"></i>
            </div>
        </div>
    </div>
    <!-- END Header Loader -->
</header>
<!-- END Header -->