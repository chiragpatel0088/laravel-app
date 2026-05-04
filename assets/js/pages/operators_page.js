$(document).ready(function() {

    var operatorsEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allOperatorsEdit.php",
            type: 'POST',
            data: function(d) {

            }
        },
        table: "#js-dataTable-allOperators",
        fields: [{
                label: "Username:",
                name: "users.user_login",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "First Name:",
                name: "users.user_firstname",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Last Name:",
                name: "users.user_lastname",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Email:",
                name: "users.user_email",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Contact Phone:",
                name: "users.user_phone",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Activated:",
                type: "checkbox",
                name: "users.user_activated",
                separator: "|",
                options: [
                    { label: '', value: 1 }
                ]
            },
            {
                label: "Linesman:",
                type: "checkbox",
                name: "users.user_linesman",
                separator: "|",
                options: [
                    { label: '', value: 1 }
                ]
            }
        ],
        i18n: {
            edit: {
                button: "Edit",
                title: "Edit Operator Details",
                submit: "Update Operator"
            },
            remove: {
                button: "Delete Operator",
                title: "Delete Operator",
                submit: "Delete"
            }
        }
    });

    var operatorsTable = $('#js-dataTable-allOperators').DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allOperatorsEdit.php",
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
            { data: "users.user_login" },
            {
                data: null,
                render: function(data, type, row) {
                    return row.users.user_firstname + " " + row.users.user_lastname;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return '<a href="mailto:' + row.users.user_email + '">' + row.users.user_email + '</a>';
                }
            },
            { data: "users.user_phone" },
            {
                data: "users.user_activated",
                render: function(data, type, row) {
                    if (type === 'display') {
                        var id = "activation-switch-" + row["DT_RowId"];
                        return '<div class="custom-control custom-switch custom-control-inline"><input type="checkbox" class="custom-control-input editor-user-activated" id="' + id + '" name="' + id + '"><label style="width: 0;" class="custom-control-label" for="' + id + '"></label></div>';
                    }
                    return data;
                },
                className: "text-center",
                orderable: false
            }
        ],
        columnDefs: [

            { responsivePriority: 2, targets: 1 },
            { responsivePriority: 3, targets: 2 },
            { responsivePriority: 4, targets: 3 }
        ],
        order: [
            [2, "DESC"]
        ],
        select: {
            style: 'os',
            selector: 'td.select-checkbox'
        },
        autoWidth: !1,
        pageLength: 10,
        lengthMenu: [
            [10, 25, -1],
            [10, 25, 'All']
        ],
        buttons: [
            { extend: "create", editor: operatorsEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new operator\'s details' },
            { extend: "edit", editor: operatorsEditor, className: "btn btn-sm btn-secondary" }
        ],
        rowCallback: function(row, data) {
            // Set the checked state of the checkbox in the table
            $('input.editor-user-activated', row).prop('checked', data.users.user_activated == 1);
        },
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    // Custom switch for activation
    $('#js-dataTable-allOperators').on('change', 'input.editor-user-activated', function() {
        operatorsEditor
            .edit($(this).closest('tr'), false)
            .set('users.user_activated', $(this).prop('checked') ? 1 : 0)
            .submit();
    });

    /* Refresh option on table */
    $('#refresh-operators-table').on('click', function() {
        setTimeout(function() {
            operatorsTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#operators-search-box').keyup(function() {
        operatorsTable.search($(this).val()).draw();
    });

});