$(document).ready(function () {

    /* Jobs table section */

    /* Date time sorting */
    $.fn.dataTable.moment('DD/MM/YYYY HH:mm');

    var operatorJobsTable = $('#js-dataTable-operatorJobs').DataTable({
        dom: "rt",
        ajax: {
            url: "inc/backend/editor_controllers/operatorJobsEdit.php",
            type: "POST",
            data: function (d) {
                d.operator_id = $("#user_id").val();
            }
        },
        columns: [{
            data: "job_quote_link.unified_id",
            render: function (data, type, row) {
                if (data == null) data = "!ERROR";
                return "JX-" + pad(data, 6); // Create styled ID
            }
        },
        { data: "customers.name" },
        {
            data: null,
            className: "address",
            render: function (data, type, row) {
                /* Output the full address using provided backend data from the ajax script */
                var full_address = "<span><strong>" + row.jobs.job_addr_1 + "</strong> " + row.jobs.job_addr_2 + " " + row.jobs.job_suburb + " " +
                    row.jobs.job_city + " " + row.jobs.job_post_code + "</span>";
                return full_address;
            }
        },
        { data: "jobs.number_plate" },
        {
            data: "job_types.type_name"
        },
        {
            data: "jobs.cubics"
        },
        {
            data: "jobs.job_date",
            render: function (data, type, row) {
                return "<span class='d-none'>" + row.jobs.job_date + "</span> " + moment(row.jobs.job_date + " " + row.jobs.job_timing).format('DD/MM/YYYY hh:mm A');
            },
        },
        {
            data: null,
            className: "text-left",
            orderable: false,
            render: function (data, type, row) {
                var options = '';
                options += createRowButton("operator_job?id=" + row.jobs.id, "", "Open Job", "fa-toolbox text-success");
                return options;
            }
        }
        ],
        columnDefs: [{
            searchable: false,
            orderable: false,
            targets: 7
        },
        {
            visible: false,
            targets: 0
        },
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 2, targets: 6 }
        ],
        createdRow: function (row, data, index) {
            var today = new Date();
            var job_date = new Date(data.jobs.job_date);
            var diffTime = (job_date - today);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            // If our quote is late or coming up
            if (diffDays <= 1 && diffDays >= 0) {
                color_class = "text-white bg-info font-w700";
            } else color_class = "";
            $(row).addClass(color_class);
        },
        order: [
            [6, "asc"]
        ],
        pageLength: -1,
        language: {
            "emptyTable": "You have no jobs to complete"
        },
        autoWidth: !1,
        drawCallback: function (settings) {
            setTimeout(function () {
                $('[data-toggle="popover"]').popover({
                    trigger: 'hover'
                });

            }, 300);
        }
    });

    /* Refresh option on quote table */
    $('#refresh-operator-jobs-table').on('click', function () {
        setTimeout(function () {
            operatorJobsTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for quotes table */
    $('#operator-jobs-search-box').keyup(function () {
        operatorJobsTable.search($(this).val()).draw();
    });

    /* END Jobs table section */

    /* Linesman Jobs table section */
    var linesmanJobsTable = $('#js-dataTable-linesmanJobs').DataTable({
        dom: "rt",
        ajax: {
            url: "inc/backend/editor_controllers/linesmanJobsEdit.php",
            type: "POST",
            data: function (d) {
                d.operator_id = $("#user_id").val();
            }
        },
        columns: [{
            data: "job_quote_link.unified_id",
            render: function (data, type, row) {
                if (data == null) data = "!ERROR";
                return "JX-" + pad(data, 6); // Create styled ID
            }
        },
        { data: "customers.name" },
        {
            data: null,
            className: "address",
            render: function (data, type, row) {
                /* Output the full address using provided backend data from the ajax script */
                var full_address = "<span><strong>" + row.jobs.job_addr_1 + "</strong> " + row.jobs.job_addr_2 + " " + row.jobs.job_suburb + " " +
                    row.jobs.job_city + " " + row.jobs.job_post_code + "</span>";
                return full_address;
            }
        },
        { data: "jobs.number_plate" },
        {
            data: "job_types.type_name"
        },
        {
            data: "jobs.cubics"
        },
        {
            data: "jobs.job_date",
            render: function (data, type, row) {
                return "<span class='d-none'>" + row.jobs.job_date + "</span> " + moment(row.jobs.job_date + " " + row.jobs.job_timing).format('DD/MM/YYYY hh:mm A');
            },
        },
        {
            data: null,
            className: "text-left",
            orderable: false,
            render: function (data, type, row) {
                var options = '';
                // options += createRowButton("operator_job?id=" + row.jobs.id, "", "Open Job", "fa-toolbox text-success");
                // options += createRowButton("linesman_job?id=" + row.jobs.id, "", "Open Job", "fa-toolbox text-success");
                options += createRowButton("linesman_job?job-id=" + row.jobs.id + "&linesman-job-id=" + row.linesman_jobs.id, "", "Open Job", "fa-toolbox text-success");
                return options;
            }
        }
        ],
        columnDefs: [{
            searchable: false,
            orderable: false,
            targets: 7
        },
        {
            visible: false,
            targets: 0
        },
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 2, targets: 6 }
        ],
        createdRow: function (row, data, index) {
            var today = new Date();
            var job_date = new Date(data.jobs.job_date);
            var diffTime = (job_date - today);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            // If our quote is late or coming up
            if (diffDays <= 1 && diffDays >= 0) {
                color_class = "text-white bg-info font-w700";
            } else color_class = "";
            $(row).addClass(color_class);
        },
        order: [
            [6, "asc"]
        ],
        pageLength: -1,
        language: {
            "emptyTable": "You have no jobs to complete"
        },
        autoWidth: !1,
        drawCallback: function (settings) {
            setTimeout(function () {
                $('[data-toggle="popover"]').popover({
                    trigger: 'hover'
                });

            }, 300);
        }
    });

    /* Refresh option on quote table */
    $('#refresh-linesman-jobs-table').on('click', function () {
        setTimeout(function () {
            linesmanJobsTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for quotes table */
    $('#linesman-jobs-search-box').keyup(function () {
        linesmanJobsTable.search($(this).val()).draw();
    });

    /* END Linesman Jobs table section */


    /* Operator Site inspections table section */

    var operatorSiteInspectionsTable = $("#js-dataTable-operatorSiteInspections").DataTable({
        dom: "rt",
        ajax: {
            url: "inc/backend/editor_controllers/operatorSiteInspections.php",
            type: "POST",
            data: function (d) {
                d.operator_id = $("#user_id").val();
            }
        },
        columns: [{
            data: "job_quote_link.unified_id",
            render: function (data, type, row) {
                if (data == null) data = "!ERROR";
                return "JX-" + pad(data, 6); // Create styled ID
            }
        },
        { data: "customers.name" },
        {
            data: null,
            className: "address",
            render: function (data, type, row) {
                /* Output the full address using provided backend data from the ajax script */
                var full_address = "<span><strong>" + row.jobs.job_addr_1 + "</strong> " + row.jobs.job_addr_2 + " " + row.jobs.job_suburb + " " +
                    row.jobs.job_city + " " + row.jobs.job_post_code +
                    "</span>";
                return full_address;
            }
        },
        {
            data: null,
            render: function (data, type, row) {
                return "<span class='d-none'>" + row.jobs.job_date + "</span> " + moment(row.jobs.job_date + " " + row.jobs.job_timing).format('DD/MM/YYYY hh:mm A');
            }
        },
        {
            data: null,
            render: function (data, type, row) {
                var status = row.site_visits.site_visit_completed;

                if (status != null) status = '<span class="text-success text-uppercase font-w700">' +
                    '<i class="fa fa-fw fa-check-circle"></i> ' +
                    'Complete' + '</span>';
                else status = '<span class="text-danger text-uppercase font-w700">' +
                    '<i class="fa fa-fw fa-times-circle"></i> ' +
                    'Not Complete' + '</span>';

                return status;
            }
        },
        {
            data: null,
            className: "text-left",
            orderable: false,
            render: function (data, type, row) {
                var options = '';
                options += createRowButton("site_inspection?id=" + row.site_visits.id, "", "Open Site Inspection", "fa-eye text-warning");
                return options;
            }
        }
        ],
        createdRow: function (row, data, index) {
            var today = new Date();
            var job_date = new Date(data.jobs.job_date);
            var diffTime = (job_date - today);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            // If our job is today, style it differently
            if (diffDays <= 1 && diffDays >= 0) {
                color_class = "bg-info text-white";
            } else color_class = "";
            $(row).addClass(color_class);
        },
        columnDefs: [{
            visible: false,
            targets: 0
        },
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 2, targets: 3 }
        ],
        order: [
            [3, "asc"]
        ],
        pageLength: -1,
        language: {
            "emptyTable": "You have no site inspections to complete"
        },
        autoWidth: !1
    });

    /* Refresh option on operator site inspections table */
    $('#refresh-operator-site-inspections-table').on('click', function () {
        setTimeout(function () {
            operatorSiteInspectionsTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for operator site inspections table */
    $('#operator-site-inspections-search-box').keyup(function () {
        operatorSiteInspectionsTable.search($(this).val()).draw();
    });

    /* END operator site inspections table section */

    /* We're using tooltips here for the option buttons on each job */
    $("body").tooltip({
        selector: '[data-toggle="tooltip"]'
    });
});

/* Padding function from https://stackoverflow.com/questions/10073699/pad-a-number-with-leading-zeros-in-javascript */
function pad(n, width, z) {
    z = z || '0';
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

/* Create button with link for a row */
function createRowButton(url, anchor_classes, tooltip_text, icon_classes) {
    button = '<a href="' + url + '" classes="' + anchor_classes + '">' +
        '<i class="fa fa-lg fa-fw ' + icon_classes + '"></i> <span class="d-inline d-sm-none font-w700">OPEN</span>' +
        '</a>';
    return button;
}