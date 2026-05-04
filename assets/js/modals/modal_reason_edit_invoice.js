$(document).ready(function() {
    /* Validate the form inside the reason input modal */
    $("#invoice-update-reason-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function(error, element) {
            error.insertAfter($(element));
        },
        rules: {
            "invoice-reason-to-edit": {
                required: true
            }
        },
        submitHandler: function(form) {

            // Add hidden element so the backend knows this is a admin update
            $('<input>').attr({
                type: 'hidden',
                name: 'updateinvoicedetails'
            }).appendTo('#ready-for-invoicing-form');

            // Add reason for updating job to the submitted form (THIS IS NOT SUBMITTED)
            $('textarea[name=invoice-reason-to-edit]').appendTo('#ready-for-invoicing-form');

            $("#ready-for-invoicing-form").submit();
            return false;
        }
    });

});