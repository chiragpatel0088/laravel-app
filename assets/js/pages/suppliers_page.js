$(document).ready(function() {

    var suppliersEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allSuppliersEdit.php",
            type: 'POST',
            data: function(d) {

            }
        },
        table: "#js-dataTable-allSuppliers",
        fields: [{
                label: "Supplier Name:",
                name: "suppliers.supplier_name",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Contact First Name:",
                name: "suppliers.supplier_firstname",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Contact Last Name:",
                name: "suppliers.supplier_lastname",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Email:",
                name: "suppliers.email",
                type: "textarea",
                fieldInfo: "For multiple emails, put a semi-colon between each email<br> e.g. bob@email.com; jane@email.com",
                attr: {
                    maxlength: 500
                }
            },
            {
                label: "Contact Phone:",
                name: "suppliers.contact_ph",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Mobile Phone:",
                name: "suppliers.contact_mob",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Code:",
                name: "suppliers.supplier_code",
                attr: {
                    maxlength: 5
                }
            }

        ],
        i18n: {
            remove: {
                button: "Delete Supplier",
                title: "Delete Supplier",
                submit: "Delete"
            }
        }
    });

    var suppliersTable = $('#js-dataTable-allSuppliers').DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allSuppliersEdit.php",
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
            { data: "suppliers.supplier_name" },
            {
                data: null,
                render: function(data, type, row) {
                    return row.suppliers.supplier_firstname + " " + row.suppliers.supplier_lastname;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    var email_arr = row.suppliers.email.split(";");
                    var val = '';
                    for (var i = 0; i < email_arr.length; i++) {
                        val += '<a href="mailto:' + email_arr[i] + '">' + email_arr[i] + '</a>; ';
                    }
                    return val;
                }
            },
            { data: "suppliers.contact_ph" },
            { data: "suppliers.contact_mob" },
            { data: "suppliers.supplier_code" }
        ],
        columnDefs: [],
        order: [
            [2, "asc"]
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
            { extend: "create", editor: suppliersEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new supplier\'s details' },
            { extend: "edit", editor: suppliersEditor, className: "btn btn-sm btn-secondary" }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    /* Refresh option on table */
    $('#refresh-suppliers-table').on('click', function() {
        setTimeout(function() {
            suppliersTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#suppliers-search-box').keyup(function() {
        suppliersTable.search($(this).val()).draw();
    });

});