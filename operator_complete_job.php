<?php require 'inc/_global/config.php'; ?>
<?php require 'inc/_global/views/head_start.php'; ?>
<?php require 'inc/_global/views/head_end.php'; ?>
<?php require 'inc/_global/views/page_start.php'; ?>

<?php 

if(!isset($_GET['job_no'])) {
    if($session->userinfo['user_level'] == 1)
    header("Location: operator_main");
    else header("Location: jobs_panel");
} else {
    $job_number_formatted = $_GET['job_no'];
}

?>

<!-- Page Content -->
<div class="bg-image" style="background-image: url('<?php echo $dm->assets_folder; ?>/media/photos/photo8@2x.jpg');">
    <div class="hero bg-black-75">
        <div class="hero-inner">
            <div class="content content-full">
                <div class="px-3 py-5 text-center">
                    <div class="display-1 text-success font-w700 invisible" data-toggle="appear" data-class="animated fadeInDown">Job <?php echo $job_number_formatted ?> Completed</div>
                    <h1 class="h2 font-w700 text-white mt-5 mb-3 invisible" data-toggle="appear" data-class="animated fadeInUp">Thank you</h1>
                    <h2 class="h3 font-w400 text-white-75 mb-5 invisible" data-toggle="appear" data-class="animated fadeInUp">Returning to the dashboard in 10 seconds..</h2>
                    <div class="invisible" data-toggle="appear" data-class="animated fadeInUp" data-timeout="600">
                        <a class="btn btn-hero-secondary" href="operator_main">
                            <i class="fa fa-arrow-left mr-1"></i> Go back NOW!
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END Page Content -->

<meta http-equiv="refresh" content="11;url=operator_main" />

<?php require 'inc/_global/views/page_end.php'; ?>
<?php require 'inc/_global/views/footer_start.php'; ?>
<?php require 'inc/_global/views/footer_end.php'; ?>