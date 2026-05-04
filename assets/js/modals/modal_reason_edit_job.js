$(document).ready(function() {
    /* Validate the form inside the reason input modal */
    $("#job-update-reason-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function(error, element) {
            error.insertAfter($(element));
        },
        rules: {
            "reason-to-edit": {
                required: true
            }
        },
        submitHandler: function(form) {

            // Add hidden element so the backend knows this is a admin update
            $('<input>').attr({
                type: 'hidden',
                name: 'subadminupdatejob'
            }).appendTo('#job-form');

            // Add reason for updating job to the submitted form (THIS IS NOT SUBMITTED)
            $('#reason-to-edit').appendTo('#job-form');

            $("#job-form").submit();
            return false;
        }
    });

});