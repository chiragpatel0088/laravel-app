$(document).ready(function() {

    var layersEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allLayersEdit.php",
            type: 'POST',
            data: function(d) {

            }
        },
        table: "#js-dataTable-allLayers",
        fields: [{
                label: "Layer Name:",
                name: "layers.layer_name",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Contact First Name:",
                name: "layers.layer_firstname",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Contact Last Name:",
                name: "layers.layer_lastname",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Email:",
                name: "layers.email",
                type: "textarea",
                fieldInfo: "For multiple emails, put a semi-colon between each email<br> e.g. bob@email.com; jane@email.com",
                attr: {
                    maxlength: 500
                }
            },
            {
                label: "Contact Phone:",
                name: "layers.contact_ph",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Mobile Phone:",
                name: "layers.contact_mob",
                attr: {
                    maxlength: 49
                }
            }

        ],
        i18n: {
            remove: {
                button: "Delete Layer",
                title: "Delete Layer",
                submit: "Delete"
            }
        }
    });

    var layersTable = $('#js-dataTable-allLayers').DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allLayersEdit.php",
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
            { data: "layers.layer_name" },
            {
                data: null,
                render: function(data, type, row) {
                    return row.layers.layer_firstname + " " + row.layers.layer_lastname;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    var email_arr = row.layers.email.split(";");
                    var val = '';
                    for (var i = 0; i < email_arr.length; i++) {
                        val += '<a href="mailto:' + email_arr[i] + '">' + email_arr[i] + '</a>; ';
                    }
                    return val;
                }
            },
            { data: "layers.contact_ph" },
            { data: "layers.contact_mob" }
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
            { extend: "create", editor: layersEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new layer\'s details' },
            { extend: "edit", editor: layersEditor, className: "btn btn-sm btn-secondary" }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    /* Refresh option on table */
    $('#refresh-layers-table').on('click', function() {
        setTimeout(function() {
            layersTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#layers-search-box').keyup(function() {
        layersTable.search($(this).val()).draw();
    });

});