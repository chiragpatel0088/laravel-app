$(document).ready(function() {
    /* Validate the form inside the send quote modal */
    $("#user-cancel-job-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function(error, element) {
            /* console.log(element);
            error.insertAfter($("#radio-section")); */
        },
        rules: {
            "other-reason-textarea": {
                required: true
            }
        },
        submitHandler: function(form) {
            $("#user-cancel-job-form").submit(form);
            return true;
        }
    });

    // Radio for decline reason
    $('input[type=radio][name=user-cancel-job-reason]').change(function() {
        var reason_value = $(this).val();
        if (reason_value == "Other") {
            $("#other-reason-section").removeClass("d-none");
            $("#other-reason-textarea").prop("disabled", false);
        } else {
            $("#other-reason-section").addClass("d-none");
            $("#other-reason-textarea").prop("disabled", true);
        }
    });

});