$(document).ready(function() {

    var jobTypesEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allJobTypesEdit.php",
            type: 'POST',
            data: function(d) {

            }
        },
        table: "#js-dataTable-allJobTypes",
        fields: [{
                label: "Name:",
                name: "job_types.type_name",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Charge:",
                name: "job_types.type_charge"
            }
        ]
    });

    var jobTypesTable = $('#js-dataTable-allJobTypes').DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allJobTypesEdit.php",
        columns: [{
                data: null,
                defaultContent: '',
                className: 'control',
                orderable: false
            },
            {
                data: null,
                defaultContent: '',
                className: 'select-checkbox',
                orderable: false
            },
            { data: "job_types.type_name" },
            {
                data: "job_types.type_charge",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            }
        ],
        columnDefs: [],
        order: [
            [2, "DESC"]
        ],
        select: {
            style: 'os',
            selector: 'td.select-checkbox'
        },
        autoWidth: !1,
        pageLength: -1,
        lengthMenu: [
            [5, 10, -1],
            [5, 10, 'All']
        ],
        buttons: [
            { extend: "create", editor: jobTypesEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new job type\'s details' },
            { extend: "edit", editor: jobTypesEditor, className: "btn btn-sm btn-secondary" }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    /* Refresh option on table */
    $('#refresh-job-types-table').on('click', function() {
        setTimeout(function() {
            jobTypesTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#job-types-search-box').keyup(function() {
        jobTypesTable.search($(this).val()).draw();
    });

});