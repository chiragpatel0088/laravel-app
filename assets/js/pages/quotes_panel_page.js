$(document).ready(function() {

    /* All Quotes table section */

    var allQuotesTable = generateQuotesTable('#js-dataTable-allQuotes', 'allQuotesEdit.php');

    /* Refresh option on quote table */
    $('#refresh-all-quotes-table').on('click', function() {
        setTimeout(function() {
            allQuotesTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for quotes table */
    $('#all-quotes-search-box').keyup(function() {
        allQuotesTable.search($(this).val()).draw();
    });

    /* Filter the table from clicking boxes */
    $('#awaiting-create-box').on('click', function() {
        allQuotesTable.column(4).search('accepted!').draw();
    });
    $('#pending-box').on('click', function() {
        allQuotesTable.column(4).search('pending').draw();
    });
    $('#not-sent-box').on('click', function() {
        allQuotesTable.column(4).search('not sent').draw();
    });
    $('#declined-box').on('click', function() {
        allQuotesTable.column(4).search('declined').draw();
    });

    /* END All Quotes table section */

    /* We're using tooltips here for the option buttons on each job */
    $("body").tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    /* Pre-click a filter button */
    setTimeout(function() {
        if ($('#pre-filter')) {
            var pre_filter = '#' + $("#pre-filter").val();
            $(pre_filter).trigger("click");
        }
    }, 1);

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

/* Used for all and pending quotes */
function generateQuotesTable(quote_table_id, backend_script) {

    return $(quote_table_id).DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/" + backend_script,
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
                    var full_address = "<strong>" + row.quotes.job_addr_1 + "</strong> " + row.quotes.job_addr_2 + " " + row.quotes.job_suburb + " " +
                        row.quotes.job_city + " " + row.quotes.job_post_code;
                    return full_address;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    var formatted_date_time = moment(row.quotes.job_date + " " + row.quotes.job_timing).format('DD/MM/YYYY hh:mm A');
                    return formatted_date_time;
                },
            },
            {
                data: "quotes.date_quote_sent",
                render: quoteStatus
            },
            {
                data: null,
                className: "text-left",
                orderable: false,
                render: function(data, type, row) {
                    var options = '';
                    options += createRowButton("quote?id=" + row.quotes.id, "", "Open Quote", "fa-file-invoice");
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
            { responsivePriority: 2, targets: 3 }
        ],
        order: [
            [0, "DESC"]
        ],
        createdRow: function(row, data, index) {

            if (data.quotes.quote_accepted == 0 || data.quotes.job_id != null) {
                return;
            }

            var today = new Date();
            var job_date = new Date(data.quotes.job_date);
            var diffTime = (job_date - today);
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            // If our quote is late or coming up
            if (diffDays <= 7 && diffDays >= 1) {
                color_class = "text-warning";
                $(row).children('td').eq(3).addClass('font-w700');
            } else if (diffDays < 1 && data.quotes.date_quote_response == null) {
                color_class = "text-danger";
                $(row).children('td').eq(3).addClass('font-w700');
            } else color_class = "";
            $(row).addClass(color_class);
        },
        autoWidth: !1,
        drawCallback: function(settings) {
            setTimeout(function() {
                $('[data-toggle="popover"]').popover({
                    trigger: 'hover'
                });
            }, 300);
        },
        dom: "<'row p-2'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'>><'row'<'col-sm-12'tr>><'row p-2'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7'p>>"
    });
}