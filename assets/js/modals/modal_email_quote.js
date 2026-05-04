$(document).ready(function() {
    /* Validate the form inside the send quote modal */
    $("#send-quote-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        rules: {
            "customer-quote-email-destination": {
                required: true,
                email: true
            }
        },
        submitHandler: function(form) {
            Dashmix.layout('header_loader_on');
            sendQuote(form);
            $('#modal-confirm-quote-email').modal('hide');
        }
    });
});

function sendQuote(form) {
    $.ajax({
        url: "process.php",
        data: "quote-id=" + $("#quote-id").val() + "&" + $(form).serialize(),
        type: 'POST'
    }).done(function(response) {
        response = JSON.parse(response);
        if (response['status'] == "error") {
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: response['message'] });
        } else if (response['status'] == "success") {
            Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-check mr-1', message: response['message'] });
        } else {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Invalid return code recieved #MAIL033' });
        }
        Dashmix.layout('header_loader_off');
    });
}