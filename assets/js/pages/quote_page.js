$(document).ready(function() {

    /* Job Type select2 */
    $('#job-type-select').select2({
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
    $('#concrete-type-select, #mix-type-select').select2({
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
                response = response.map(function(item) {
                    return {
                        id: item.id,
                        text: item.text,
                        charge: item.charge
                    };
                });
                return { results: response };
            },
            cache: true
        },
        placeholder: 'Select type'
    });

    /* Concrete truck select2 */
    $('#truck-select').select2({
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
    $("#truck-select").on("change", function() {
        var truck_id = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getTruckDetails.php",
            type: "POST",
            data: {
                truck_id: truck_id
            },
            success: function(result) {
                var truck_details = JSON.parse(result);
                $("#boom").val(truck_details['boom']); // not visible to user, set by select2
                $("#capacity").val(truck_details['capacity']);
                $("#rate").val(parseFloat(truck_details['hourly_rate']).toFixed(2));
                $("#min").val(parseFloat(truck_details['min']).toFixed(2));
                $("#travel").val(parseFloat(truck_details['travel_rate_km']).toFixed(2));
                $("#washout").val(parseFloat(truck_details['washout']).toFixed(2));
                $("#disposal-fee").val(parseFloat(truck_details['disposal_fee']).toFixed(2));
                $("#establishment-fee").val(truck_details['est_fee']);
                calculateChargeTotals();
            }
        });
    });

    /* Customer select2 */
    $('#customer-select').select2({
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
        placeholder: 'Select customer',
    });

    // When a customer is selected, fill out the form details we don't fill out from PHP
    $("#customer-select").on("change", function() {
        var selected_customer = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getCustomerDetails.php",
            type: "POST",
            data: {
                customer_id: selected_customer,
            },
            success: function(result) {
                var customer_details = JSON.parse(result);
                $("#contact-name").text(customer_details['first_name'] + " " + customer_details['last_name']);
                $("#cust-email").text(customer_details['email']);
                $("#cust-email").attr('href', 'mailto:' + customer_details['email']);
                $("#cust-ph").text(customer_details['contact_ph']);
                $("#cust-ph").attr('href', 'tel:' + customer_details['contact_ph']);
                $("#cust-mob-ph").text(customer_details['contact_mob']);
                $("#cust-mob-ph").attr('href', 'tel:' + customer_details['contact_mob']);
                $("#cust-company-name").text(customer_details['name']);
                $("#cust-addr-1").text(customer_details['address_1'] + " " + customer_details['address_2']);
                $("#cust-addr-2").text(customer_details['suburb']);
                $("#cust-addr-3").text(customer_details['city'] + " " + customer_details['post_code']);
                $("#customer-quote-email-destination").val(customer_details['email']);
                $("#customer-discount").val(customer_details['discount']);
                discountPopoverTextUpdate(); // Set discount message
            }
        });
    });

    /* Operator select2 */
    $('#operator-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getOperators.php',
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
        placeholder: 'Select operator',
    });

    // When a supplier is selected, fill out the form details we don't fill out from PHP
    $("#operator-select").on("change", function() {
        var selected_operator = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getOperatorDetails.php",
            type: "POST",
            data: {
                operator_id: selected_operator,
            },
            success: function(result) {
                var operator_details = JSON.parse(result);
                $("#operator-ph").text(operator_details['contact_ph']);
                $("#operator-ph").attr('href', 'tel:' + operator_details['contact_ph']);
            }
        });
    });

    /* As select2 elements are programmatically changed, the jquery validation doesn't pick up on it until submission, so we call valid() to remove errors */
    $("#truck-select, #concrete-type-select, #customer-select, #operator-select, #job-type-select").on("change", function() {
        $(this).valid();
    });

    var quote_id = $("#quote-id").val();
    $.post('process', "quote_id=" + quote_id + "&getquotedetails=true", function() {})
        .done(function(data) {
            var details = JSON.parse(data)[0];
            populateJobForm(details);
        });

    /* Custom min value validator method */
    $.validator.addMethod("minStrict", function(value, el, param) {
        return value >= param;
    }, "Value must be greater than 0.");

    /* Custom max value validator method */
    $.validator.addMethod("maxStrict", function(value, el, param) {
        return value <= param;
    }, "Value is too high.");

    /* Validation of form */
    var quote_form_validator = $("#quote-form").validate({
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
            "customer-select": {
                required: true
            },
            "job-date": {
                required: !0
            },
            "job-timing": {
                required: !0
            },
            "job-address-1": {
                required: !0
            },
            "truck-select": {
                required: !0
            },
            "job-type-select": {
                required: !0
            },
            "cubics": {
                required: !0,
                number: !0,
                minStrict: 0
            },
            "mpa": {
                required: !0,
                number: !0,
                minStrict: 0
            },
            "job-range": {
                required: !0,
                number: !0,
                minStrict: 0
            },
            "concrete-type-select": {
                required: !0
            },
            "job-post-code": {
                number: !0
            },
            "operator-select": {
                required: !0
            },
            "discount": {
                required: !0,
                number: !0,
                maxStrict: 100
            },
            "establishment-fee": {
                required: !0,
                number: !0
            },
            "cubic-rate": {
                required: !0,
                number: !0
            },
            "travel-fee": {
                required: !0,
                number: !0
            }
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    /* User has clicked print button for quote */
    $('#print-quote').on('click', function() {
        preparePrintQuote(); // Function is in this file
    });

    $('#proxy-update-quote').on('click', function() {
        $("#update-quote").trigger('click'); // Function is in this file
    });

    $("#send-quote-email, #accept-create-job").on('click', function(e) {
        if (!quote_form_validator.checkForm()) {
            $(this).attr("disabled", true);
            if ($("#truck-select").val() == null || $("#truck-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a truck and update the quote" });
            } else if ($("#job-type-select").val() == null || $("#job-type-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a job type and update the quote" });
            } else {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please complete the quote with required fields" });
            }
            e.preventDefault();
            return;
        }
    });

    /* Quote declined, accepted, accepted+create job, add the quote id to the submitted POST data on click of the decline button */
    $('#user-decline-quote, #user-accept-quote-create-job, #user-create-job-from-quote').submit(function() {
        var input = $("<input>").attr("type", "hidden").attr("name", "quote-id").val($("#quote-id").val());
        $(this).append(input);
    });

    /* Call initially on load to show calculated values */
    calculateChargeTotals();
});

/* Populate our generated form for details updating, we use the same one from the start job page so we can reuse code/use familiar interfaces for simplicity */
function populateJobForm(details) {

    /* Preselect job type */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleJobType.php",
        type: 'POST',
        data: jQuery.param({ job_type_id: details['job_type'] }),
        success: function(result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#job-type-select").append(option).trigger('change');
        }
    });

    /* Preselect concrete type */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleConcreteType.php",
        type: 'POST',
        data: jQuery.param({ concrete_type_id: details['concrete_type'] }),
        success: function(result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#concrete-type-select").append(option).trigger('change');
            $("#concrete-charge").val(data.charge);
            $("#concrete-type-select").select2('data')[0].charge = data.charge;

            /* Concrete charge change */
            $('#concrete-type-select').on("change", function() {
                var data = $(this).select2('data')[0];
                $("#concrete-charge").val(data.charge);
                updateCubicRate($("#cubic-charge"));
            });

            $("#concrete-type-select").trigger({
                type: 'select2:select',
                params: {
                    data: data
                }
            });
        }
    });

    /* Preselect mix type */
    if (details['mix_type'] >= 0) {
        $.ajax({
            url: "inc/backend/data_retrieval/select2/select2_getSingleConcreteType.php",
            type: 'POST',
            data: jQuery.param({ concrete_type_id: details['mix_type'] }),
            success: function(result) {
                if (result == "") return; // null check as mix type can be empty
                var data = JSON.parse(result);
                var option = new Option(data.text, data.id, true, true);
                $("#mix-type-select").append(option).trigger('change');
                $("#mix-charge").val(data.charge);
                $("#mix-type-select").select2('data')[0].charge = data.charge;

                /* Mix type charge change */
                $('#mix-type-select').on("change", function() {
                    var data = $(this).select2('data')[0];
                    $("#mix-charge").val(data.charge);
                    updateCubicRate($("#cubic-charge"));
                });

                $("#mix-type-select").trigger({
                    type: 'select2:select',
                    params: {
                        data: data
                    }
                });
            }
        });
    }

    /* Preselect truck */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleTruck.php",
        type: 'POST',
        data: jQuery.param({ truck_id: details['truck_id'] }),
        success: function(result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#truck-select").append(option); // we don't trigger change here as quotes have their own truck details on load
        }
    });

    /* Preselect customer */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleCustomer.php",
        type: 'POST',
        data: jQuery.param({ customer_id: details['customer_id'] }),
        success: function(result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#customer-select").append(option).trigger('change');
        }
    });

    /* Preselect layer */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleLayer.php",
        type: 'POST',
        data: jQuery.param({ layer_id: details['layer_id'] }),
        success: function(result) {
            if (result == "") return; // null check as layer can be empty
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#layer-select").append(option).trigger('change');
        }
    });

    /* Preselect supplier */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleSupplier.php",
        type: 'POST',
        data: jQuery.param({ supplier_id: details['supplier_id'] }),
        success: function(result) {
            if (result == "") return; // null check as supplier can be empty
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#supplier-select").append(option).trigger('change');
        }
    });

    /* Preselect operator */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleOperator.php",
        type: 'POST',
        data: jQuery.param({ operator_id: details['operator_id'] }),
        success: function(result) {
            if (result == "") return; // null check as operator can be empty
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#operator-select").append(option).trigger('change');
        }
    });

    /* Preselect radio for site visit requirement */
    var $site_visit_radios = $("input:radio[name=site-visit-required]");
    $site_visit_radios.filter('[value=' + details['site_visit_required'] + ']').prop('checked', true);

}

/* Prepare print layout with information of quote */
function preparePrintQuote() {

    // Customer info
    $("#print-client-name").text($("#contact-name").text());
    $("#print-company-name").text($("#cust-company-name").text());
    $("#print-address-1").text($("#cust-addr-1").text());
    $("#print-address-2").text($("#cust-addr-2").text());
    $("#print-address-3").text($("#cust-addr-3").text());

    // Job details
    $("#print-date").text($("#job-date").datepicker({ dateFormat: 'dd,MM,yyyy' }).val());
    $("#print-time").text($("#job-timing").val());
    $("#print-range").text($("#job-range").val() + "km");

    // Job address
    $("#print-job-address-1").text($("#job-address-1").val() + " " + $("#job-address-2").val());
    $("#print-job-address-2").text($("#job-suburb").val());
    $("#print-job-address-3").text($("#job-city").val() + " " + $("#job-post-code").val());

    // Job specifications
    var job_type_data = $('#job-type-select').select2('data');
    $("#print-job-type").text(job_type_data[0].text);

    $("#print-cubics").text($("#cubics").val());
    $("#print-megapascal").text($("#mpa").val());

    var pump_truck_data = $('#truck-select').select2('data');
    $("#print-pump-truck").text(pump_truck_data[0].text);

    var concrete_type_data = $('#concrete-type-select').select2('data');
    $("#print-concrete-type").text(concrete_type_data[0].text);

    var mix_type_data = $('#mix-type-select').select2('data');
    if (mix_type_data.length > 0) {
        $("#print-mix-type").text(mix_type_data[0].text);
        $("#print-mix-type").parent().show();
    } else $("#print-mix-type").parent().hide();

    // Charge
    var establishment_fee = $.fn.dataTable.render.number(',', '.', 2, '$').display($("#establishment-fee").val());
    var cubic_rate = $.fn.dataTable.render.number(',', '.', 2, '$').display($("#cubic-rate").val());
    var travel_fee = $.fn.dataTable.render.number(',', '.', 2, '$').display($("#travel-fee").val());
    $("#print-est-fee").text(establishment_fee);
    $("#print-cubic-rate").text(cubic_rate);
    $("#print-travel-fee").text(travel_fee);

    var sub_total = parseFloat($("#establishment-fee").val()) + parseFloat($("#cubic-rate").val()) + parseFloat($("#travel-fee").val());
    $("#print-sub-total").text($.fn.dataTable.render.number(',', '.', 2, '$').display(sub_total));

    $("#print-discount-rate").text($("#discount").val() + "%");
    var discount_total = sub_total * (parseFloat($("#discount").val()) / 100);
    $("#print-discount-total").text($.fn.dataTable.render.number(',', '.', 2, '$').display(discount_total));

    var sub_total_inc_discount = sub_total - discount_total;
    $("#print-sub-total-inc-discount").text($.fn.dataTable.render.number(',', '.', 2, '$').display(sub_total_inc_discount));

    var gst = (sub_total_inc_discount) * 0.15;
    $("#print-gst").text($.fn.dataTable.render.number(',', '.', 2, '$').display(gst));

    $("#print-cubic-cost").text($("#cubic-cost").text());

    Dashmix.helpers('print');
}