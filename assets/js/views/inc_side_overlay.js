/* Password change input validation */

$(document).ready(function() {

    $(".js-validation").validate({
        ignore: [],
        errorClass: "invalid-feedback animated fadeIn",
        errorElement: "div",
        errorPlacement: function(e, r) {
            $(r).addClass("is-invalid"), jQuery(r).parent(".form-group").append(e);
        },
        highlight: function(e) {
            $(e).parents(".form-group").find(".is-invalid").removeClass("is-invalid").addClass("is-invalid");
        },
        success: function(e) {
            $(e).parents(".form-group").find(".is-invalid").removeClass("is-invalid"), $(e).fadeOut();
        },
        rules: {
            "newpass": {
                required: !0,
                minlength: 8
            },
            "conf_newpass": {
                required: !0,
                equalTo: "#val-password"
            }
        },
        messages: {
            "newpass": {
                required: "Please provide a password",
                minlength: "Your password must be at least 8 characters long"
            },
            "conf_newpass": {
                required: "Please provide a password",
                equalTo: "Please enter the same password as above"
            }
        }

    });

    $("#sign-out-button").on('click', function() {
        window.location.href = "process";
    });

});