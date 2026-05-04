$(document).ready(function() {
    getAllNotifications(); // Call initially, then start the interval cycle.

    if (Number.isInteger(parseInt($("#user_id").val()))) {
        setInterval(function() {
            // If unread count changed, run below function, else wait again.
            getAllNotifications();
        }, 1000 * 60 * 0.16); // this is about 8 or 10 seconds.       

        $(".dropdown").on("hidden.bs.dropdown", function() {
            $.post('process.php', "submarknotificationsasread=true", function() {})
                .done(function() {
                    $(".notification-count").html('0');
                    $(".notification-count").addClass("badge-success");
                    $(".notification-count").removeClass("badge-danger");
                });
        });
    }

});

// Handling notifications to create locally. Handle each row of data.
function generateNotification(item) {

    var notif_data = item;
    var readStatusIndicator;

    // Unread and read notifications have different styling rules, see inc_header.php
    if (notif_data.unread != null) {
        readStatusIndicator = "read-notification";
    } else readStatusIndicator = "unread-notification";

    var notification = '<li class="' + readStatusIndicator + '"><a class="text-dark media py-2" href="./' + notif_data.type + '?id=' + notif_data.type_id + '">';
    notification += '<div class="mx-3">';
    notification += '<i class="' + notif_data.class_modifiers + '"></i>';
    notification += '</div>';
    notification += '<div class="media-body font-size-sm pr-2">';
    notification += '<div class="font-w600">' + notif_data.message + '</div>';
    notification += '<div class="text-muted font-italic">' + notif_data.date_created + '</div>';
    notification += '</div></a></li>';
    $(".notification-container").append(notification);
}

function getAllNotifications() {
    $.ajax({
        url: "inc/backend/data_retrieval/getAllNotificationsForUser.php?id=" + $("#user_id").val(),
        success: function(result) {
            results_obj = JSON.parse(result);
            var unread_count = results_obj['unread_count'];

            // If the unread count hasn't changed, then don't update the DOM
            if (unread_count == $(".notification-count").html()) { return; }

            $(".notification-container").empty();
            results_obj['notifications'].forEach(generateNotification);

            var unread_count = results_obj['unread_count'];

            $(".notification-count").html(unread_count);
            if (unread_count > 0) {
                $(".notification-count").removeClass("badge-success");
                $(".notification-count").addClass("badge-danger");
            } else {
                $(".notification-count").addClass("badge-success");
                $(".notification-count").removeClass("badge-danger");
            }

        }
    });
}