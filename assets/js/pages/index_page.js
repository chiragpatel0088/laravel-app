$(document).ready(function() {
    $(".js-validation-signin").validate({
        errorClass: "invalid-feedback animated fadeIn",
        errorElement: "div",
        errorPlacement: function(e, n) {
            $(n).addClass("is-invalid"), jQuery(n).parents(".form-group").append(e)
        },
        highlight: function(e) {
            $(e).parents(".form-group").find(".is-invalid").removeClass("is-invalid").addClass("is-invalid")
        },
        success: function(e) {
            $(e).parents(".form-group").find(".is-invalid").removeClass("is-invalid"), $(e).remove()
        },
        rules: {
            "user": {
                required: !0
            },
            "pass": {
                required: !0
            }
        },
        messages: {
            "login-username": {
                required: "Please enter a username"
            },
            "login-password": {
                required: "Please provide a password"
            }
        }
    });
    microsoftTeams.initialize();
    var test = microsoftTeams.getContext(function(context) {
        if (context === null || context === undefined) {
            $("#test-text").text('Not Teams Session');
        } else {
            console.log("Teams");
            $("#test-text").text('Teams');
        }
    });

});