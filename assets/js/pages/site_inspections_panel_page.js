$(document).ready(function() {


    /* All Site inspections table section */

    var allSiteInspectionsTable = $("#js-dataTable-allSiteInspections").DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allSiteInspections.php",
        columns: [{
                data: "job_quote_link.unified_id",
                render: function(data, type, row) {
                    if (data == null) data = "!ERROR";
                    return "JX-" + pad(data, 6); // Create styled ID
                }
            },
            { data: "customers.name" },
            {
                data: null,
                render: function(data, type, row) {
                    /* Output the full address using provided backend data from the ajax script */
                    var full_address = "<span><strong>" + row.jobs.job_addr_1 + "</strong> " + row.jobs.job_addr_2 + " " + row.jobs.job_suburb + " " +
                        row.jobs.job_city + " " + row.jobs.job_post_code + "</span>";
                    return full_address;
                }
            },
            {
                data: null,
                className: "datetime",
                render: function(data, type, row) {
                    if (row.jobs.job_timing != null)
                        var formatted_date_time = moment(row.jobs.job_date + " " + row.jobs.job_timing).format('DD/MM/YYYY hh:mm A');
                    else var formatted_date_time = moment(row.jobs.job_date).format('DD/MM/YYYY');
                    var date_time = '<span>' + formatted_date_time + '</span>';
                    return date_time;
                }
            },
            {
                data: null,
                className: "text-center",
                render: function(data, type, row) {
                    var status = row.site_visits.site_visit_completed;

                    if (status != null) status = '<span class="text-success text-uppercase font-w700">' +
                        '<i class="fa fa-fw fa-check-circle"></i> ' +
                        '<span class="d-none d-lg-inline">Complete</span>' + '</span>';
                    else status = '<span class="text-danger text-uppercase font-w700">' +
                        '<i class="fa fa-fw fa-times-circle"></i> ' +
                        '<span class="d-none d-lg-inline">Not Complete</span>' + '</span>';

                    return status;
                }
            },
            {
                data: null,
                className: "text-left",
                orderable: false,
                render: function(data, type, row) {
                    var options = '';
                    /* options += createRowButton("job?id=" + row.site_visits.id, "", "Open Job", "fa-toolbox text-success"); */
                    options += createRowButton("site_inspection?id=" + row.site_visits.id, "", "Open Site Inspection", "fa-eye text-warning");
                    return options;
                }
            }
        ],
        columnDefs: [{
                searchable: false,
                orderable: false,
                targets: 5
            },
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: 3 },
            { responsivePriority: 3, targets: 4 }

        ],
        order: [
            [0, "DESC"]
        ],
        autoWidth: !1,
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7'p>>"
    });

    /* Refresh option on quote table */
    $('#refresh-all-site-inspections-table').on('click', function() {
        setTimeout(function() {
            allSiteInspectionsTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for site_visits table */
    $('#all-site-inspections-search-box').keyup(function() {
        allSiteInspectionsTable.search($(this).val()).draw();
    });

    /* END All site_visits table section */

    /* We're using tooltips here for the option buttons on each site inspection row */
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
    button = '<a href="' + url + '" classes="' + anchor_classes + '" data-toggle="tooltip" data-placement="left" title="' + tooltip_text + '">' +
        '<i class="fa fa-lg fa-fw ' + icon_classes + '"></i> <span class="d-inline d-sm-none font-w700">OPEN </span>' +
        '</a>';
    return button;
}