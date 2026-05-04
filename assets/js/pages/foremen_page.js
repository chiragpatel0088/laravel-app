$(document).ready(function() {

    var foremenEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/foremenEdit.php",
            type: 'POST'
        },
        table: "#js-dataTable-foremen",
        fields: [{
                label: "Company:",
                name: "foremen.company",
            }, {
                label: "Contact First Name:",
                name: "foremen.first_name",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Contact Last Name:",
                name: "foremen.last_name",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Email:",
                name: "foremen.email",
                type: "textarea",
                fieldInfo: "For multiple emails, put a semi-colon between each email<br> e.g. bob@email.com; jane@email.com",
                attr: {
                    maxlength: 500
                }
            },
            {
                label: "Contact Phone:",
                name: "foremen.contact_ph",
                attr: {
                    maxlength: 49
                }
            }
        ]
    });

    var foremenTable = $('#js-dataTable-foremen').DataTable({
        ajax: "inc/backend/editor_controllers/foremenEdit.php",
        columns: [{
                data: null,
                defaultContent: '',
                className: 'select-checkbox',
                orderable: false
            },
            { data: "foremen.company" },
            {
                data: null,
                render: function(data, type, row) {
                    return row.foremen.first_name + " " + row.foremen.last_name;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    var email_arr = row.foremen.email.split(";");
                    var val = '';
                    for (var i = 0; i < email_arr.length; i++) {
                        val += '<a href="mailto:' + email_arr[i] + '">' + email_arr[i] + '</a>; ';
                    }
                    return val;
                }
            },
            { data: "foremen.contact_ph" }
        ],
        columnDefs: [

            { responsivePriority: 2, targets: 1 },
            { responsivePriority: 3, targets: 2 },
            { responsivePriority: 4, targets: 3 }
        ],
        order: [
            [2, "asc"]
        ],
        select: {
            style: 'os',
            selector: 'td.select-checkbox'
        },
        autoWidth: !1,
        pageLength: 25,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'All']
        ],
        buttons: [
            { extend: "create", editor: foremenEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new foreman\'s details' },
            { extend: "edit", editor: foremenEditor, className: "btn btn-sm btn-secondary" }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    /* Refresh option on table */
    $('#refresh-foremen-table').on('click', function() {
        setTimeout(function() {
            foremenTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#foremen-search-box').keyup(function() {
        foremenTable.search($(this).val()).draw();
    });

});