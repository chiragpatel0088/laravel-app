$(document).ready(function() {

    var concreteTypesEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allConcreteTypesEdit.php",
            type: 'POST',
            data: function(d) {

            }
        },
        table: "#js-dataTable-allConcreteTypes",
        fields: [{
                label: "Name:",
                name: "concrete_types.concrete_name",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Charge:",
                name: "concrete_types.concrete_charge"
            }
        ]
    });

    var concreteTypesTable = $('#js-dataTable-allConcreteTypes').DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allConcreteTypesEdit.php",
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
            { data: "concrete_types.concrete_name" },
            {
                data: "concrete_types.concrete_charge",
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
            { extend: "create", editor: concreteTypesEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new supplier\'s details' },
            { extend: "edit", editor: concreteTypesEditor, className: "btn btn-sm btn-secondary" }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    /* Refresh option on table */
    $('#refresh-concrete-types-table').on('click', function() {
        setTimeout(function() {
            concreteTypesTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#concrete-types-search-box').keyup(function() {
        concreteTypesTable.search($(this).val()).draw();
    });

});