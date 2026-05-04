$(document).ready(function() {
    /* Customer select2 */
    $('#new-customer-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getCustomers.php',
            type: "POST",
            dataType: 'json',

            data: function(params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select customer'
    });

    /* Job Type select2 */
    $('#new-job-type-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getJobTypes.php',
            type: "POST",
            dataType: 'json',

            data: function(params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select job type'
    });

    /* Concrete and Mix Type select2 */
    $('#new-concrete-type-select, #new-mix-type-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getConcreteTypes.php',
            type: "POST",
            dataType: 'json',

            data: function(params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select type'
    });

    /* Concrete truck select2 */
    $('#new-truck-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getTrucks.php',
            type: "POST",
            dataType: 'json',

            data: function(params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select truck'
    });

    /* Concrete truck details autofill */
    // When a item is selected, autofill form with existing item details
    $("#new-truck-select").on("change", function() {
        var truck_id = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getTruckDetails.php",
            type: "POST",
            data: {
                truck_id: truck_id
            },
            success: function(result) {
                var truck_details = JSON.parse(result);
                $("#new-boom").val(truck_details['boom']); // not visible to user, set by select2
                $("#new-capacity").val(truck_details['capacity']);
                $("#new-rate").val("$" + parseFloat(truck_details['hourly_rate']).toFixed(2));
                $("#new-min").val("$" + parseFloat(truck_details['min']).toFixed(2));
                $("#new-travel").val("$" + parseFloat(truck_details['travel_rate_km']).toFixed(2));
                $("#new-washout").val("$" + parseFloat(truck_details['washout']).toFixed(2));
                $("#new-disposal-fee").val("$" + parseFloat(truck_details['disposal_fee']).toFixed(2));
            }
        });
    });

    /* As select2 elements are programmatically changed, the jquery validation doesn't pick up on it until submission, so we call valid() to remove errors */
    $("#new-truck-select, #new-concrete-type-select, #new-customer-select, #new-job-type-select").on("change", function() {
        $(this).valid();
    });

    /* Custom min value validator method */
    $.validator.addMethod("minStrict", function(value, el, param) {
        return value > param;
    }, "Value must be greater than 0.");

    /* Validation of form */
    $("#new-job-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function(error, element) {
            if (element.parent().hasClass("input-group")) {
                error.insertAfter(element.parent());
            } else if (element.hasClass("js-select2")) {
                error.insertAfter(element.parent().find(".select2-container"));
            } else
                error.insertAfter(element);
        },
        rules: {
            "new-customer-select": {
                required: true
            },
            "new-job-date": {
                required: !0
            },
            "new-job-timing": {
                /* required: !0 */
            },
            "new-job-address-1": {
                required: !0
            },
            "new-truck-select": {
                /* required: !0 */
            },
            "new-job-type-select": {
                /* required: !0 */
            },
            "new-cubics": {
                /* required: !0, */
                number: !0,
                /* minStrict: 0 */
            },
            "new-mpa": {
                /* required: !0, */
                number: !0,
                /* minStrict: 0 */
            },
            "new-concrete-type-select": {
                /* required: !0 */
            },
            "new-job-post-code": {
                number: !0
            },
            "range-address": {
                number: !0
            }
        },
        submitHandler: function(form) {
            form.submit();
        }
    });


    /* Code to add add customer button to form */
    var addCustomerEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allCustomersEdit.php",
            type: 'POST'
        },
        table: "#js-dataTable-newCustomer",
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
                attr: {
                    maxlength: 250
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
            create: {
                button: "<i class='fa fa-fw fa-plus'></i> Customer",
                title: "Add Customer",
                submit: "Add"
            }
        }
    });

    /* Hidden table to support the buttons */
    $('#js-dataTable-newCustomer').DataTable({
        dom: "B",
        ajax: "inc/backend/editor_controllers/allCustomersEdit.php",
        columns: [{
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
                render: function(data, type, row) {
                    return '<a href="mailto:' + row.customers.email + '">' + row.customers.email + '</a>';
                }
            },
            { data: "customers.contact_ph" },
            { data: "customers.contact_mob" }
        ],
        columnDefs: [{
            searchable: false,
            orderable: false,
            visible: false,
            targets: [0, 1, 2, 3, 4, 5]
        }],
        buttons: [
            { extend: "create", editor: addCustomerEditor, className: "btn btn-sm text-primary", formMessage: 'Enter your new customer\'s details' },
        ],
        dom: "B"
    });


    /* Modal event when closed, clear form */
    $('#modal-new-job-form').on('hidden.bs.modal', function(e) {
        $("#new-job-form")[0].reset();
        $("#new-customer-select").val('').trigger('change');
        $("#new-truck-select").val('').trigger('change');
        $("#new-job-type-select").val('').trigger('change');
        $("#new-concrete-type-select").val('').trigger('change');
    });
});

/* Initialise autocomplete address form */
function initAddy() {

    /* Addy address autocomplete */
    var addyComplete = new AddyComplete(document.getElementById("new-job-address-1"));

    // When address is selected, calculate the approximate range and show it above the address field
    addyComplete.addressSelected = function(address) {
        $("#approx-range-container").hide();
        var approximate_range = calculateDistanceBetweenLatLong(-37.755367, 176.094595, $("#longitude-address").val(), $("#latitude-address").val()) * 1.15;
        $("#approx-range").text(approximate_range.toFixed(2));
        //$("#range-address").val(approximate_range.toFixed(2));
        $("#approx-range-container").fadeIn();
    }

    addyComplete.fields = {
        address1: document.getElementById("new-job-address-1"),
        address2: document.getElementById("new-job-address-2"),
        suburb: document.getElementById("new-job-suburb"),
        city: document.getElementById("new-job-city"),
        postcode: document.getElementById("new-job-post-code"),
        x: document.getElementById("latitude-address"),
        y: document.getElementById("longitude-address")
    }

    /**
     * On click for dt-buttons as the form does not exist until buttons are clicked
     * This is for the add customer form
     * Added delay because the dt-buttons do not exist on init of addy
     *  */
    setTimeout(function() {
        $(".dt-button").on("click", function() {
            /* Addy address autocomplete */
            var customerAddyComplete = new AddyComplete(document.getElementById("DTE_Field_customers-address_1"));

            customerAddyComplete.fields = {
                address1: document.getElementById("DTE_Field_customers-address_1"),
                address2: document.getElementById("DTE_Field_customers-address_2"),
                suburb: document.getElementById("DTE_Field_customers-suburb"),
                city: document.getElementById("DTE_Field_customers-city"),
                postcode: document.getElementById("DTE_Field_customers-post_code")
            }
        });
    }, 500);

}

/* https://www.movable-type.co.uk/scripts/latlong.html */
function calculateDistanceBetweenLatLong(lat1, lon1, lat2, lon2) {
    const R = 6371e3; // metres
    const φ1 = lat1 * Math.PI / 180; // φ, λ in radians
    const φ2 = lat2 * Math.PI / 180;
    const Δφ = (lat2 - lat1) * Math.PI / 180;
    const Δλ = (lon2 - lon1) * Math.PI / 180;

    const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
        Math.cos(φ1) * Math.cos(φ2) *
        Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    const d = R * c; // meters

    return d / 1000; // km
}