$(document).ready(function() {

    var customersEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allCustomersEdit.php",
            type: 'POST',
            data: function(d) {

            }
        },
        table: "#js-dataTable-allCustomers",
        fields: [{
                label: "Company Name:",
                name: "customers.name",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Contact First Name:",
                name: "customers.first_name",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Contact Last Name:",
                name: "customers.last_name",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Address 1:",
                name: "customers.address_1",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Address 2:",
                name: "customers.address_2",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "City:",
                name: "customers.city",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Suburb:",
                name: "customers.suburb",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Post Code:",
                name: "customers.post_code",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Email:",
                name: "customers.email",
                type: "textarea",
                fieldInfo: "For multiple emails, put a semi-colon between each email<br> e.g. bob@email.com; jane@email.com",
                attr: {
                    maxlength: 500
                }
            },
            {
                label: "Contact Phone:",
                name: "customers.contact_ph",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Mobile Phone:",
                name: "customers.contact_mob",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Discount (%):",
                name: "customers.discount",
                attr: {
                    maxlength: 5
                }
            },
            {
                label: "Tax Number:",
                name: "customers.tfn"
            }


        ],
        i18n: {
            edit: {
                button: "Edit/View",
                title: "Edit/View Customer Details",
                submit: "Update Customer"
            },
            remove: {
                button: "Delete Customer",
                title: "Delete Customer",
                submit: "Delete"
            }
        }
    });

    var customersTable = $('#js-dataTable-allCustomers').DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allCustomersEdit.php",
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
            { data: "customers.name" },
            {
                data: null,
                render: function(data, type, row) {
                    return row.customers.first_name + " " + row.customers.last_name;
                }
            },
            {
                data: null,
                className: "address",
                render: function(data, type, row) {
                    /* Output the full address using provided backend data from the ajax script */
                    var full_address = "<span><strong>" + row.customers.address_1 + "</strong> " + row.customers.address_2 + " " + row.customers.suburb + " " +
                        row.customers.city + " " + row.customers.post_code + "</span>";
                    return full_address;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    var email_arr = row.customers.email.split(";");
                    var val = '';
                    for (var i = 0; i < email_arr.length; i++) {
                        val += '<a href="mailto:' + email_arr[i] + '">' + email_arr[i] + '</a>; ';
                    }
                    return val;
                }
            },
            { data: "customers.contact_ph" },
            { data: "customers.contact_mob" }
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
            { extend: "create", editor: customersEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new customer\'s details' },
            { extend: "edit", editor: customersEditor, className: "btn btn-sm btn-secondary" }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    /* Refresh option on table */
    $('#refresh-customers-table').on('click', function() {
        setTimeout(function() {
            customersTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#customers-search-box').keyup(function() {
        customersTable.search($(this).val()).draw();
    });

});

/* Initialise autocomplete addresses on the datatables editor form*/
function initAddy() {

    setTimeout(function() {
        // On click for dt-buttons as the form does not exist until buttons are clicked
        $(".dt-button").on("click", function() {
            /* Addy address autocomplete */
            var addyComplete = new AddyComplete(document.getElementById("DTE_Field_customers-address_1"));

            addyComplete.fields = {
                address1: document.getElementById("DTE_Field_customers-address_1"),
                address2: document.getElementById("DTE_Field_customers-address_2"),
                suburb: document.getElementById("DTE_Field_customers-suburb"),
                city: document.getElementById("DTE_Field_customers-city"),
                postcode: document.getElementById("DTE_Field_customers-post_code")
            }
        });
    }, 500);
}