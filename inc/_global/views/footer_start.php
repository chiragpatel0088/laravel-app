<?php

/**
 * footer_start.php
 *
 * Author: Micro Solutions Ltd / Pixelcave
 *
 * All vital JS scripts are included here
 *
 */
?>

<!--
    Dashmix JS Core

    Vital libraries and plugins used in all pages. You can choose to not include this file if you would like
    to handle those dependencies through webpack. Please check out assets/_es6/main/bootstrap.js for more info.

    If you like, you could also include them separately directly from the assets/js/core folder in the following
    order. That can come in handy if you would like to include a few of them (eg jQuery) from a CDN.

    assets/js/core/jquery.min.js
    assets/js/core/bootstrap.bundle.min.js
    assets/js/core/simplebar.min.js
    assets/js/core/jquery-scrollLock.min.js
    assets/js/core/jquery.appear.min.js
    assets/js/core/js.cookie.min.js
-->
<script src="<?php echo $dm->assets_folder; ?>/js/dashmix.core.min.js"></script>

<!--
    Dashmix JS

    Custom functionality including Blocks/Layout API as well as other vital and optional helpers
    webpack is putting everything together at assets/_es6/main/app.js
-->
<script src="<?php echo $dm->assets_folder; ?>/js/dashmix.app.min.js"></script>

<!-- Global JS plugins -->
<?php $dm->get_js('js/plugins/datatables/jquery.dataTables.min.js'); ?>
<?php $dm->get_js('js/plugins/select2/js/select2.full.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/buttons/dataTables.buttons.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables-editor/dataTables.editor.js'); ?>
<?php $dm->get_js('js/plugins/pwstrength-bootstrap/pwstrength-bootstrap.min.js'); ?>
<?php $dm->get_js('js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js'); ?>
<?php $dm->get_js('js/plugins/jquery-validation/jquery.validate.min.js'); ?>
<?php $dm->get_js('js/plugins/bootstrap-notify/bootstrap-notify.min.js'); ?>
<?php $dm->get_js('js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/dataTables.bootstrap4.min.js'); ?>

<!-- Needs to be before datetime-moment include to define 'moment' -->
<?php $dm->get_js('js/plugins/datatables/datetime-moment/moment.min.js'); ?>
<?php $dm->get_js('js/plugins/datatables/datetime-moment/datetime-moment.js'); ?>
<!-- END Global JS plugins -->

<!-- Page JS Helpers -->
<script>
    jQuery(function() {
        Dashmix.helpers(['select2', 'datepicker', 'maxlength', 'pw-strength', 'notify']);
    });
</script>

<!-- Custom JS for notifications really lol -->
<?php $dm->get_js('js/views/inc_header.js'); ?>

<!-- For password validation -->
<?php $dm->get_js('js/views/inc_side_overlay.js'); ?>