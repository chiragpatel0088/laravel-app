var job_status_filter = 0;

$(document).ready(function () {

    /* All Jobs editor section */
    var allJobsEditor = new $.fn.dataTable.Editor({
        ajax: "inc/backend/editor_controllers/allJobsEdit.php",
        table: "#js-dataTable-allJobs",
        fields: [{
            label: "Operator:",
            name: "jobs.operator_id",
            type: "select",
            placeholder: "Operator.."
        },
        {
            label: "Truck:",
            name: "jobs.truck_id",
            type: "select",
            placeholder: "Truck.."
        }
        ]
    });

    /* All Jobs table section */
    var allJobsTable = $('#js-dataTable-allJobs').DataTable({
        ajax: {
            url: "inc/backend/editor_controllers/allJobsEdit.php",
            type: 'POST',
            data: function (d) {
                d.filter = job_status_filter;
            }
        },
        columns: [{
            data: "job_quote_link.unified_id",
            render: function (data, type, row) {
                if (data == null) data = "!ERROR";
                return "JX-" + pad(data, 6); // Create styled ID
            }
        },
        {
            data: "customers.name",
            render: function (data, type, row) {
                var am_check = row.jobs.am_check == 1 ? '<i class="fa fa-check-circle text-info" data-toggle="tooltip" data-placement="left" title="AM Check Complete"></i>' : '';
                var pm_check = row.jobs.pm_check == 1 ? '<i class="fa fa-check-circle text-dark" data-toggle="tooltip" data-placement="left" title="PM Check Complete"></i>' : '';
                var passed_check = row.jobs.passed_check == 1 ? '<span class="text-warning font-w700">Passed</span>' : '';
                return data + ' ' + am_check + ' ' + pm_check + ' ' + passed_check;
            }
        },
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
        {
            data: "users.ID",
            editField: "jobs.operator_id",
            render: function (data, type, row) {
                var operator = "";
                if (row.jobs.operator_id == null) { operator += "<em class='text-danger'><strong>No Operator</strong></em>" } else {
                    operator = row.users.user_firstname + " " + row.users.user_lastname;
                }

                sent_to_operator_status_class = row.jobs.sent_to_operator == 1 ? 'text-success' : 'text-danger';
                return '<span class="' + sent_to_operator_status_class + '">' + operator + '</span>';
            }
        },
        {
            data: "trucks.id",
            editField: "jobs.truck_id",
            render: function (data, type, row) {
                var truck = "";

                if (row.jobs.number_plate == null) { truck += "<em class='text-danger'>No Truck</em>"; } else {
                    truck = row.jobs.number_plate;
                }

                sent_to_operator_status_class = row.jobs.sent_to_operator == 1 ? 'text-success' : 'text-danger';
                return '<span class="' + sent_to_operator_status_class + '">' + "<strong>" + truck + '</strong></span>';
            }
        },
        {
            data: null,
            className: "text-center",
            render: function (data, type, row) {
                var tooltip_supplier_code = createTooltipForSupplierCode("", row.suppliers.supplier_name, row.suppliers.supplier_code);
                return '<strong>' + tooltip_supplier_code + '</strong>';
            }
        },
        {
            data: "job_types.type_name"
        },
        {
            data: "jobs.cubics"
        },
        {
            data: "jobs.job_date",
            render: function (data, type, row) {
                if (row.jobs.job_timing != null)
                    var formatted_date_time = "<span class='d-none'>" + row.jobs.job_date + row.jobs.job_timing + "</span>" + moment(row.jobs.job_timing, 'HH:mm:ss').format('hh:mm A');
                else
                    var formatted_date_time = "<span class='d-none'>" + row.jobs.job_date + row.jobs.job_timing + "</span><div class='text-white d-inline bg-warning p-1'><small>NO TIME</small></div>";
                return formatted_date_time;
            }
        },
        {
            data: null,
            className: "text-center",
            render: function (data, type, row) {
                /* Output current status */
                var status = "<a class='badge badge-pill text-white " + row.job_statuses.class_modifiers + " ' href='" + "job?id=" + row.jobs.id + "'>" + row.job_statuses.status_name + "</a>";

                /* Check if the status is one that allows suitable pumps to be shown, add boom lengths of site inspection if so */
                var allowed_statuses = [1, 7]; // Pending(complete form and incomplete) and the job assigned(6) status
                if (allowed_statuses.includes(parseInt(row.jobs.status)) && data.jobs.suitable_pumps != null) {
                    status += '<br><span class="text-center"><small>' + data.jobs.suitable_pumps + '</small></span>';
                } else if (parseInt(row.jobs.status) == 6) {
                    status += '<br><span class="text-center d-inline d-md-none d-lg-none d-xl-none"><small>' + row.jobs.number_plate + '</small></span>' + '<span class="text-center d-none d-md-inline d-lg-inline d-xl-inline"><small>' + data.jobs.suitable_pumps + '</small></span>';
                }

                return status;
            }
        },
        {
            data: null,
            className: "text-left",
            orderable: false,
            render: function (data, type, row) {
                var options = createRowButton("job?id=" + row.jobs.id, "", "Open Job", "fa-toolbox text-success");
                return options;
            }
        }
        ],
        columnDefs: [{
            searchable: false,
            orderable: false,
            targets: 8
        }, {
            visible: false,
            targets: 0
        },
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 2, targets: 8 },
        { responsivePriority: 3, targets: 9 }
        ],
        responsive: true,
        orderFixed: [8, "asc"],
        rowGroup: {
            dataSrc: ["jobs.job_date"],
            startRender: function (rows, group) {
                // Formatting output values
                var formatted_date = moment(group).format('dddd DD MMMM');

                // Gets proper row counts including next page items that are also apart of the group
                var row_group_count = allJobsTable.rows(function (idx, data, node) {
                    return data.jobs.job_date === group ? true : false;
                }, { search: 'applied' }).data().length;
                var job_count = row_group_count > 1 ? row_group_count + " Jobs" : row_group_count + " Job";

                var isToday = moment(group).diff(moment().format('MM-DD-YYYY')) == 0 ? "<i class='far fa-calendar-alt text-xmodern-light'></i> TODAY - " : "";

                var rowBackgroundColor = "#87CEEB;";

                // Add category name to the <tr>. NOTE: Hardcoded colspan
                // This is the row group object to be rendered on page
                return $('<tr class="font-w600 border-0"/>')
                    .append('<td class="p-1" colspan="10" style=" color: white; background-color:' + rowBackgroundColor + ';">'
                        + isToday
                        + formatted_date
                        + ' - '
                        + job_count + '</td>')
                    .attr('data-name', group)
                    .attr('id', group);

            }
        },
        createdRow: function (row, data, index) {
            if (data.jobs.switchJobColor == 1) {
                // $(row).css('background-color', '#F8E29E');
                // $(row).css('background-color', '#E6B800');
                $(row).css('background-color', 'rgba(255, 204, 0, 0.2)');
            }

            // If the job is cancelled, complete, ready to invoice or invoiced, we don't need to add warning colours to it if it's overdue..
            if (data.jobs.status == 2 || data.jobs.status == 8 || data.jobs.status == 10 || data.jobs.status == 11) {
                return;
            }

            var today = new Date();
            var job_date = new Date(data.jobs.job_date);
            var diffTime = (job_date - today);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays < 0) {
                color_class = "text-danger";
                $(row).children('td').eq(7).addClass('font-w700');
            } else color_class = "";
            $(row).addClass(color_class);
        },
        autoWidth: !1,
        lengthMenu: [
            [25, 50, 100, -1],
            [25, 50, 100, "All"]
        ],
        pageLength: -1,
        dom: "<'row p-2'<'col-sm-12 col-md-6 d-none d-md-block'l><'col-sm-12 col-md-6 text-right'>><'row'<'col-sm-12'tr>><'row p-2'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7'p>>"
    });

    /* Allow editing of truck selection inline only if job is not yet complete */
    $('#js-dataTable-allJobs').on('click', 'tbody td:nth-child(3), tbody td:nth-child(4)', function (e) {
        var rowData = allJobsTable.row(this.parentNode).data();
        var job_status = rowData.jobs.status;
        if (job_status == 5 || job_status == 6 || job_status == 1 || job_status == 7) {
            allJobsEditor.inline(this, {
                onBlur: 'submit'
            });
        }
    });

    /* Refresh option on quote table */
    $('#refresh-all-jobs-table').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'asc']
        }).draw();
        applyFilter(allJobsTable, 0);
    });

    /* Enable exterior search box for jobs table */
    $('#all-jobs-search-box').keyup(function () {
        allJobsTable.search($(this).val()).draw();
    });

    $('#job-ready-box, #incomplete-box, #pending-inspections-box, #completed-box, #ready-invoicing-box, #cancelled-box').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'asc'],
            post: [8, 'asc']
        }).draw();
    });

    $('#invoiced-box').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'desc'],
            post: [8, 'desc']
        }).draw();
    });

    /* Filter the table from clicking boxes */
    $('#incomplete-box').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'asc']
        });
        applyFilter(allJobsTable, 1) // Pending jobs
    });
    $('#pending-inspections-box').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'asc']
        });
        applyFilter(allJobsTable, 2); // Site inspections required jobs
    });
    $('#job-ready-box').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'asc']
        });
        applyFilter(allJobsTable, 3); // Assigned jobs
    });
    $('#completed-box').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'asc']
        });
        applyFilter(allJobsTable, 4); // Completed jobs
    });
    $('#ready-invoicing-box').on('click', function () {
        allJobsTable.order.fixed({
            pre: [8, 'asc']
        });
        applyFilter(allJobsTable, 5); // Ready for invoicing jobs
    });
    $('#current-invoiced-box').on('click', function () {
        allJobsTable.page.len(50).draw();
        allJobsTable.order.fixed({
            pre: [8, 'desc']
        }).draw();
        applyFilter(allJobsTable, 8); // Latest 3 months Invoiced jobs
    });
    $('#invoiced-box').on('click', function () {
        allJobsTable.page.len(50).draw();
        allJobsTable.order.fixed({
            pre: [8, 'desc']
        }).draw();
        applyFilter(allJobsTable, 6); // Invoiced jobs
    });
    $('#cancelled-box').on('click', function () {
        allJobsTable.page.len(50).draw();
        allJobsTable.order.fixed({
            pre: [8, 'desc']
        }).draw();
        applyFilter(allJobsTable, 7); // Cancelled jobs
    });

    /* END All Jobs table section */

    /* We're using tooltips here for the option buttons on each job */
    $("body").tooltip({
        selector: '[data-toggle="tooltip"]'
    });

});

/* Filter function to filter jobs by status passed in */
function applyFilter(jobs_datatable, filter_val) {
    Dashmix.block('state_loading', '.block-mode-loading-refresh');
    job_status_filter = filter_val; // Assigned jobs
    jobs_datatable.ajax.reload(function (json) {
        Dashmix.block('state_normal', '.block-mode-loading-refresh');
    });
}

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

/* Create button with link for a row */
function createTooltipForSupplierCode(anchor_classes, tooltip_text, supplier_code) {
    if (supplier_code == null) {
        supplier_code = '';
    }
    button = '<span classes="' + anchor_classes + '" data-toggle="tooltip" data-placement="left" title="' + tooltip_text + '">' +
        supplier_code +
        '</span>';
    return button;
}