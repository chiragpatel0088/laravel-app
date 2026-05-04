$(document).ready(function () {
    $("#email-job-to-supplier").on('click', function (e) {
        sendJobToSupplier();
    });
    $("#email-job-to-customer").on('click', function (e) {
        sendJobToCustomer();
    });
    $("#email-job-to-layer").on('click', function (e) {
        sendJobToLayer();
    });
    $("#email-job-to-foreman").on('click', function (e) {
        sendJobToForeman();
    });
    $("#email-job-to-all").on('click', function (e) {
        sendJobToLayer();
        sendJobToCustomer();
        sendJobToSupplier();
        sendJobToForeman();
    });
});

function sendJobToLinesmen() {
    var linesmenIds = $("#linesman-select").val();
    if (!linesmenIds || linesmenIds.length === 0) {
        Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "No linesmen selected." });
        return;
    }

    Dashmix.layout('header_loader_on');

    var promises = [];

    linesmenIds.forEach(function (linesmanId) {

        // console.log(linesmanId);

        var promise = $.ajax({
            url: "process.php",
            data: "job-id=" + $("#job_id").val() + "&subsendjobtolinesmen=true&linesman-id=" + linesmanId,
            type: 'POST'
        }).done(function (response) {
            response = JSON.parse(response);
            // console.log(response, "response");
            if (response['status'] == "error") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: response['message'] });
            } else if (response['status'] == "success") {
                Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-check mr-1', message: response['message'] });
            } else {
                Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Invalid return code received #MAIL033' });
            }
        });

        promises.push(promise);
    });

    // Wait for all AJAX requests to complete
    $.when.apply($, promises).then(function () {
        Dashmix.layout('header_loader_off');
    });
}


function sendJobToOperator() {
    Dashmix.layout('header_loader_on');
    $.ajax({
        url: "process.php",
        data: "job-id=" + $("#job-id").val() + "&subsendjobtooperator=true&operator-id=" + $("#operator-select").val(),
        type: 'POST'
    }).done(function (response) {
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

function sendJobToSupplier() {
    Dashmix.layout('header_loader_on');
    $.ajax({
        url: "process.php",
        data: "job-id=" + $("#job-id").val() + "&subsendjobtosupplier=true&supplier-id=" + $("#supplier-select").val(),
        type: 'POST'
    }).done(function (response) {
        response = JSON.parse(response);
        if (response['status'] == "error") {
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: response['message'] });
        } else if (response['status'] == "success") {
            Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-check mr-1', message: response['message'] });
        } else {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Invalid return code recieved #MAIL033' });
        }

        /* Display email addresses job not sent to if they exist */
        if (typeof response.not_sent_to !== 'undefined') {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Job not sent to following emails due to error: ' + response.not_sent_to });
        }
        Dashmix.layout('header_loader_off');
    });
}

function sendJobToCustomer() {
    Dashmix.layout('header_loader_on');
    $.ajax({
        url: "process.php",
        data: "job-id=" + $("#job-id").val() + "&subsendjobtocustomer=true&customer-id=" + $("#customer-select").val(),
        type: 'POST'
    }).done(function (response) {
        response = JSON.parse(response);
        if (response['status'] == "error") {
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: response['message'] });
        } else if (response['status'] == "success") {
            Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-check mr-1', message: response['message'] });
        } else {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Invalid return code recieved #MAIL033' });
        }

        /* Display email addresses job not sent to if they exist */
        if (typeof response.not_sent_to !== 'undefined') {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Job not sent to following emails due to error: ' + response.not_sent_to });
        }
        Dashmix.layout('header_loader_off');
    });
}

function sendJobToLayer() {
    Dashmix.layout('header_loader_on');
    $.ajax({
        url: "process.php",
        data: "job-id=" + $("#job-id").val() + "&subsendjobtolayer=true&layer-id=" + $("#layer-select").val(),
        type: 'POST'
    }).done(function (response) {
        response = JSON.parse(response);
        if (response['status'] == "error") {
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: response['message'] });
        } else if (response['status'] == "success") {
            Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-check mr-1', message: response['message'] });
        } else {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Invalid return code recieved #MAIL033' });
        }

        /* Display email addresses job not sent to if they exist */
        if (typeof response.not_sent_to !== 'undefined') {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Job not sent to following emails due to error: ' + response.not_sent_to });
        }
        Dashmix.layout('header_loader_off');
    });
}

function sendJobToForeman() {
    Dashmix.layout('header_loader_on');
    $.ajax({
        url: "process.php",
        data: "job-id=" + $("#job-id").val() + "&subsendjobtoforeman=true&foreman-id=" + $("#foreman-select").val(),
        type: 'POST'
    }).done(function (response) {
        response = JSON.parse(response);
        if (response['status'] == "error") {
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: response['message'] });
        } else if (response['status'] == "success") {
            Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-check mr-1', message: response['message'] });
        } else {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Invalid return code recieved #MAIL033' });
        }

        /* Display email addresses job not sent to if they exist */
        if (typeof response.not_sent_to !== 'undefined') {
            Dashmix.helpers('notify', { type: 'warning', icon: 'fa fa-exclamation mr-1', message: 'Job not sent to following emails due to error: ' + response.not_sent_to });
        }
        Dashmix.layout('header_loader_off');
    });
}