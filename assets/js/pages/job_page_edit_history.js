$(document).ready(function() {

    var historyTable = $('#js-dataTable-jobEditHistory').DataTable({
        ajax: {
            url: "inc/backend/editor_controllers/jobEditHistory.php",
            type: 'POST',
            data: function(d) {
                d.job_id = $("#job-id").val();
                d.history_type = $("#edit-history-mode").val();
            }
        },
        columns: [{
                className: 'font-w600',
                data: 'job_change_logs.field_changed'
            },
            {
                data: 'job_change_logs.message',
                render: function(data, type, row) {
                    return '<code>' + data + '</code>';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.users.user_firstname;
                }
            },
            {
                data: 'job_change_logs.reason_changed'
            },
            {
                data: null,
                className: "text-center",
                render: function(data, type, row) {
                    return '<span class="badge badge-pill badge-primary ' + row.job_statuses.class_modifiers + '">' + row.job_statuses.status_name + '</span>'
                }
            },
            {
                data: 'job_change_logs.date_changed',
                render: function(data, type, row) {
                    return '<span class="d-none">' + data + '</span>' + moment(data).format('h:mma');
                }
            }
        ],
        autoWidth: !1,
        pageLength: -1,
        rowGroup: {
            dataSrc: function(row) {
                return moment(row.job_change_logs.date_changed).format("YYYY/MM/DD");
            },
            startRender: function(rows, group) {
                // Formatting output values
                var formatted_date = moment(group).format('dddd DD MMMM YYYY');

                // Add category name to the <tr>. NOTE: Hardcoded colspan
                // This is the row group object to be rendered on page
                return $('<tr class="font-w600 border-0"/>')
                    .append('<td class="p-1" colspan="8">' + formatted_date + '</td>')
                    .attr('data-name', group)
                    .attr('id', group);

            }
        },
        order: [5, 'desc'],
        lengthMenu: [
            [5, 10, -1],
            [5, 10, 'All']
        ],
        language: {
            "emptyTable": "No administrative edit history"
        },
        dom: "<'row mt-2'<'col-md-6 pl-1'l><'col-md-6'f>t>",
        columnDefs: [
            { width: 450, targets: 1 }
        ],
        fixedColumns: true
    });

    $('#show-all').on('click', function() {
        $("#edit-history-mode").val(1);
        historyTable.ajax.reload();
    });

    $('#show-after-complete').on('click', function() {
        $("#edit-history-mode").val(0);
        historyTable.ajax.reload();
    });
});