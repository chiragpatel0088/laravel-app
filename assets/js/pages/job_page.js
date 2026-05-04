$(document).ready(function () {
    $('.linesman-line-size-select').each(function () {
        $(this).select2({
            tags: true,  // 允许用户手动输入
            placeholder: "Select or enter size",
            allowClear: true
        });
    });
    /* Job Type select2 */
    $('#job-type-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getJobTypes.php',
            type: "POST",
            dataType: 'json',
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select job type',
        minimumResultsForSearch: -1
    });
    $("#cancelJob").on('click', function () {
        var job_id = $("#cancelJob").val();
        $.ajax({
            url: "inc/backend/data_retrieval/select2/select2_deleteCanceledJob.php?job_id=" + job_id,
            type: 'GET',
        }).then(function (data) {
            var flag = JSON.parse(data);
            if (flag == true) {
                alert("delete success");
                var protocol = window.location.protocol;
                var host = window.location.host;
                var prefix = protocol + '//' + host;
                window.location.href = prefix + "/jobs_panel?refresh=true"
            } else {
                alert("delete failed");
            }
        });
    });
    /* Concrete and Mix Type select2 */
    $('#concrete-type-select, #mix-type-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getConcreteTypes.php',
            type: "POST",
            dataType: 'json',

            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select type',
        minimumResultsForSearch: -1
    });

    /* Concrete truck select2 */
    $('#truck-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getTrucks.php',
            type: "POST",
            dataType: 'json',
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select truck',
        minimumResultsForSearch: -1
    });

    /* Concrete truck details autofill */
    // When a item is selected, autofill form with existing item details
    $("#truck-select").on("change", function () {
        var truck_id = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getTruckDetails.php",
            type: "POST",
            data: {
                truck_id: truck_id
            },
            success: function (result) {
                var truck_details = JSON.parse(result);
                $("#boom").val(truck_details['boom']); // not visible to user, set by select2
                $("#capacity").val(truck_details['capacity']);
                $("#establishment-fee").val(truck_details['est_fee']);
                $("#rate").val(parseFloat(truck_details['hourly_rate']).toFixed(2));
                $("#min").val(parseFloat(truck_details['min']).toFixed(2));
                $("#travel").val(parseFloat(truck_details['travel_rate_km']).toFixed(2));
                calculateChargeTotals();

                // Ready for invoicing modal modification
                $("#invoice-assigned-truck").text(truck_details['number_plate'] + " " + truck_details['brand']);
            }
        });
    });

    /* Customer select2 */
    $('#customer-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getCustomers.php',
            type: "POST",
            dataType: 'json',
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select customer',
    });

    // When a customer is selected, fill out the form details we don't fill out from PHP
    $("#customer-select").on("change", function () {
        var selected_customer = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getCustomerDetails.php",
            type: "POST",
            data: {
                customer_id: selected_customer,
            },
            success: function (result) {
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
                $("#customer-discount").val(customer_details['discount']);
                $("#invoice-discount").val(customer_details['discount']);
            }
        });
    });

    /* Layer select2 */
    $('#layer-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getLayers.php',
            type: "POST",
            dataType: 'json',
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select layer',
    });

    // When a layer is selected, fill out the form details we don't fill out from PHP
    $("#layer-select").on("change", function () {
        var selected_layer = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getLayerDetails.php",
            type: "POST",
            data: {
                layer_id: selected_layer,
            },
            success: function (result) {
                var layer_details = JSON.parse(result);
                $("#layer-name").text(layer_details['layer_firstname'] + ' ' + layer_details['layer_lastname'] + ' (' + layer_details['layer_name'] + ')');
                $("#layer-email").text(layer_details['email']);
                $("#layer-email").attr('href', 'mailto:' + layer_details['email']);
                $("#layer-ph").text(layer_details['contact_ph']);
                $("#layer-ph").attr('href', 'tel:' + layer_details['contact_ph']);
                $("#layer-mob-ph").text(layer_details['contact_mob']);
                $("#layer-mob-ph").attr('href', 'tel:' + layer_details['contact_mob']);
            }
        });
    });

    /* Supplier select2 */
    $('#supplier-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getSuppliers.php',
            type: "POST",
            dataType: 'json',
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select supplier',
    });

    // When a supplier is selected, fill out the form details we don't fill out from PHP
    $("#supplier-select").on("change", function () {
        var selected_supplier = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getSupplierDetails.php",
            type: "POST",
            data: {
                supplier_id: selected_supplier,
            },
            success: function (result) {
                var supplier_details = JSON.parse(result);
                $("#supplier-name").text(supplier_details['supplier_firstname'] + ' ' + supplier_details['supplier_lastname'] + ' (' + supplier_details['supplier_name'] + ')');
                $("#supplier-email").text(supplier_details['email']);
                $("#supplier-email").attr('href', 'mailto:' + supplier_details['email']);
                $("#supplier-ph").text(supplier_details['contact_ph']);
                $("#supplier-ph").attr('href', 'tel:' + supplier_details['contact_ph']);
                $("#supplier-mob-ph").text(supplier_details['contact_mob']);
                $("#supplier-mob-ph").attr('href', 'tel:' + supplier_details['contact_mob']);
            }
        });
    });

    /* Operator select2 */
    $('#operator-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getOperators.php',
            type: "POST",
            dataType: 'json',
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select operator',
        minimumResultsForSearch: -1
    });
    // When a operator is selected, fill out the form details we don't fill out from PHP
    $("#operator-select").on("change", function () {
        var selected_operator = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getOperatorDetails.php",
            type: "POST",
            data: {
                operator_id: selected_operator,
            },
            success: function (result) {
                if (result == '') return;
                var operator_details = JSON.parse(result);
                $("#operator-name").text(operator_details['name']);
                $("#operator-ph").text(operator_details['contact_ph']);
                $("#operator-ph").attr('href', 'tel:' + operator_details['contact_ph']);
                $("#operator-email").text(operator_details['email_address']);
                $("#operator-email").attr('href', 'mailto:' + operator_details['email_address']);
                $("#print-operator-name").text(operator_details['name']);
            }
        });
    });

    /* Foreman select2 */
    $('#foreman-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getForemen.php',
            type: "POST",
            dataType: 'json',
            data: function (params) {
                return {
                    searchTerm: params.term, // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
            cache: true
        },
        placeholder: 'Select foreman',
    });

    // When a foreman is selected, fill out the form details we don't fill out from PHP
    $("#foreman-select").on("change", function () {
        var selected_foreman = $(this).find("option:selected").attr("value");
        $.ajax({
            url: "inc/backend/data_retrieval/getForemanDetails.php",
            type: "POST",
            data: {
                foreman_id: selected_foreman,
            },
            success: function (result) {
                if (result == '') return;
                var foreman_details = JSON.parse(result);
                $("#foreman-company").text(foreman_details['company']);
                $("#foreman-name").text(foreman_details['first_name'] + ' ' + foreman_details['last_name']); // Only appears in operator job page
                $("#foreman-ph").text(foreman_details['contact_ph']);
                $("#foreman-ph").attr('href', 'tel:' + foreman_details['contact_ph']);
                $("#foreman-email").text(foreman_details['email']);
                $("#foreman-email").attr('href', 'mailto:' + foreman_details['email']);
            }
        });
    });


    /* As select2 elements are programmatically changed, the $ validation doesn't pick up on it until submission, so we call valid() to remove errors */
    $("#truck-select, #concrete-type-select, #customer-select, #operator-select, #layer-select, #supplier-select, #job-type-select, #foreman-select").on("change", function () {
        $(this).valid();
    });

    var job_id = $("#job_id").val();
    $.post('process', "job_id=" + job_id + "&getjobdetails=true", function () { })
        .done(function (data) {
            var details = JSON.parse(data);
            // console.log(details);
            populateJobForm(details);
        });

    /* Custom min value validator method */
    $.validator.addMethod("minStrict", function (value, el, param) {
        return value > param;
    }, "Value must be greater than 0.");

    /* Validation of form */
    var job_form_validation = $("#job-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function (error, element) {
            if (element.parent().hasClass("input-group")) {
                error.insertAfter(element.parent());
            } else if (element.hasClass("js-select2")) {
                error.insertAfter(element.parent().find(".select2-container"));
            } else if (element.parent().hasClass("custom-radio")) {
                error.insertAfter(element.parent().parent());
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
            "job-address-1": {
                required: !0
            },
            "cubics": {
                number: !0
            },
            "mpa": {
                required: !0,
                number: !0,
                minStrict: 0
            },
            "job-post-code": {
                number: !0
            },
            "range-address": {
                number: !0
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    var jobUpdateRequired = false;
    setTimeout(function () {
        $("#job-form :input").on("change", function () {
            jobUpdateRequired = true;
        });
    }, 2000);



    $("#assign-operator").on('click', function (e) {

        e.preventDefault();

        if ($("#job-timing").val() == "") {
            $(this).attr("disabled", true);
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "You cannot assign a job without a start time." });
            return;
        }

        // If the job is pending a site inspection, do not allocate it
        if ($("#job-status").val() == 5) {
            $(this).attr("disabled", true);
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "You cannot assign a job with a pending site inspection." });
            return;
        }

        if (job_form_validation.checkForm()) {
            if ($("#operator-select").val() == null || $("#operator-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a operator" });
                return;
            }

            if ($("#layer-select").val() == null || $("#layer-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a layer" });
                return;
            }

            if ($("#supplier-select").val() == null || $("#supplier-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a supplier" });
                return;
            }

            if ($("#concrete-type-select").val() == null || $("#concrete-type-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a concrete type" });
                return;
            }

            if ($("#customer-select").val() == null || $("#customer-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a customer" });
                return;
            }

            if ($("#truck-select").val() == null || $("#truck-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a truck" });
                return;
            }

            if ($("#cubics").val() == null || $("#cubics").val() == "" || $("#cubics").val() <= 0) {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please add a valid cubics value" });
                return;
            }

            if ($("#job-type-select").val() == null || $("#job-type-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a job type" });
                return;
            }

            if (jobUpdateRequired) {
                $(this).attr("disabled", true);
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please click 'Update Job'" });
                return;
            }

            // Only assign and send job if the ajax function returns non-error data value
            $.post('process', "job_id=" + job_id + "&assignoperator=true", function () { })
                .done(function (data) {
                    if (data) {
                        data = JSON.parse(data); // Echoed JSON return data from backend
                        Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: data.error });
                    } else {
                        sendJobToOperator();
                        console.log(sendJobToOperator);
                        Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-user-plus mr-1', message: "Operator assigned and notified" });
                        $('#assign-operator').html(`
                            <i class="fa fa-fw fa-user-plus mr-1"></i> Assign<span class="d-none d-md-inline"> to Operator</span>
                        `);
                    }
                });

        } else {
            $("#job-form").trigger("submit");
            $(this).attr("disabled", true);
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please update the form with all required fields before assigning to operator" });
            return;
        }

        // Prevent click spam
        $("#assign-operator").attr("disabled", true);
        setTimeout(function () {
            $("#assign-operator").attr("disabled", false);
        }, 2000)
    });

    //2024-08-01
    $("#assign-linesmen").on('click', function (e) {
        e.preventDefault();

        // If the job is pending a site inspection, do not allocate it
        if ($("#job-status").val() == 5) {
            $(this).attr("disabled", true);
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "You cannot assign a job with a pending site inspection." });
            return;
        }

        if (job_form_validation.checkForm()) {
            if ($("#linesman-select").val() == null || $("#linesman-select").val() == "") {
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a linesman" });
                return;
            }
            if (jobUpdateRequired) {
                $(this).attr("disabled", true);
                Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please click 'Update'" });
                return;
            }

            // Only assign and send job if the ajax function returns non-error data value
            $.post('process', "job_id=" + job_id + "&assignLinesmen=true", function () { })
                .done(function (data) {
                    console.log(data, "data");
                    if (data) {
                        data = JSON.parse(data); // Echoed JSON return data from backend
                        Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: data.error });
                    } else {
                        sendJobToLinesmen();
                        Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-user-plus mr-1', message: "Linesmen assigned and notified" });
                        $('#assign-linesmen').html(`
                            <i class="fa fa-fw fa-user-plus mr-1"></i> Assign<span class="d-none d-md-inline"> to Linesman</span>
                        `);
                    }
                });

        } else {
            $("#job-form").trigger("submit");
            $(this).attr("disabled", true);
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please update the form with all required fields before assigning to operator" });
            return;
        }
    })


    // function assignFunction(type) {
    //     if ($("#job-timing").val() == "") {
    //         $(this).attr("disabled", true);
    //         Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "You cannot assign a job without a start time." });
    //         return;
    //     }

    //     // If the job is pending a site inspection, do not allocate it
    //     if ($("#job-status").val() == 5) {
    //         $(this).attr("disabled", true);
    //         Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "You cannot assign a job with a pending site inspection." });
    //         return;
    //     }

    //     if (job_form_validation.checkForm()) {
    //         //linesman don't need to check.
    //         if ("operator" == type) {
    //             if ($("#operator-select").val() == null || $("#operator-select").val() == "") {
    //                 Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a operator" });
    //                 return;
    //             }
    //         }
    //         if ($("#layer-select").val() == null || $("#layer-select").val() == "") {
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a layer" });
    //             return;
    //         }

    //         if ($("#supplier-select").val() == null || $("#supplier-select").val() == "") {
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a supplier" });
    //             return;
    //         }

    //         if ($("#concrete-type-select").val() == null || $("#concrete-type-select").val() == "") {
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a concrete type" });
    //             return;
    //         }

    //         if ($("#customer-select").val() == null || $("#customer-select").val() == "") {
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a customer" });
    //             return;
    //         }

    //         if ($("#truck-select").val() == null || $("#truck-select").val() == "") {
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a truck" });
    //             return;
    //         }

    //         if ($("#cubics").val() == null || $("#cubics").val() == "" || $("#cubics").val() <= 0) {
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please add a valid cubics value" });
    //             return;
    //         }

    //         if ($("#job-type-select").val() == null || $("#job-type-select").val() == "") {
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a job type" });
    //             return;
    //         }

    //         if (jobUpdateRequired) {
    //             $(this).attr("disabled", true);
    //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please click 'Update Job'" });
    //             return;
    //         }
    //         //if click linesman need to validation or don't need to
    //         if ("linesman" == type) {
    //             var linesmanIds = $('#linesman-select').val();
    //             var operatorId = $("#operator-select").val();
    //             if (linesmanIds.length <= 0) {
    //                 Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please select a linesman at least" });
    //                 return;
    //             }
    //             for (var i = 0; i < linesmanIds.length; i++) {
    //                 if (linesmanIds[i] === operatorId) {
    //                     Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Linesman and operator cannot be the same." });
    //                     return; // This will exit the entire function if the condition is met
    //                 }
    //             }
    //             sendJobToLinesman();
    //             Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-user-plus mr-1', message: type + " assigned and notified" });
    //             //don't need notification
    //             // // Only assign and send job if the ajax function returns non-error data value
    //             // $.post('process', "job_id=" + job_id + "&assignliensman=true", function() {})
    //             //     .done(function(data) {
    //             //         if (data) {
    //             //             data = JSON.parse(data); // Echoed JSON return data from backend
    //             //             Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: data.error });
    //             //         } else {
    //             //             sendJobToOperator();
    //             //             Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-user-plus mr-1', message: type+" assigned and notified" });
    //             //         }
    //             //     });
    //         } else {
    //             // Only assign and send job if the ajax function returns non-error data value
    //             $.post('process', "job_id=" + job_id + "&assignoperator=true", function () { })
    //                 .done(function (data) {
    //                     if (data) {
    //                         data = JSON.parse(data); // Echoed JSON return data from backend
    //                         Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: data.error });
    //                     } else {
    //                         sendJobToOperator();
    //                         Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-user-plus mr-1', message: type + " assigned and notified" });
    //                     }
    //                 });
    //         }



    //     } else {
    //         $("#job-form").trigger("submit");
    //         $(this).attr("disabled", true);
    //         Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please update the form with all required fields before assigning to " + type });
    //         return;
    //     }

    //     // // Prevent click spam
    //     // $("#assign-operator").attr("disabled", true);
    //     // setTimeout(function() {
    //     //     $("#assign-operator").attr("disabled", false);
    //     // }, 2000)
    // }
    // // Validate form before assigning to operator
    // $("#assign-operator").on('click', function (e) {
    //     e.preventDefault();
    //     assignFunction('operator');
    // });
    // $("#assign-linesman").on('click', function (e) {
    //     e.preventDefault();
    //     assignFunction('linesman');
    // });


    // Validate form before being able to send it out
    $("#dropdown-send-job-emails").on('click', function (e) {
        if (job_form_validation.checkForm()) {
            // Todo
        } else {
            // We disable it to prevent the dropdown event from firing
            $(this).attr('disabled', true);
            $("#job-form").trigger("submit");
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please update the form with all required fields before sending the job via email" });
        }

        // Renable the button after 3 seconds, this is to prevent the dropdown from showing, we could leave it disabled, but we make sure the user knows that it's disabled for a reason
        setTimeout(function () {
            $("#dropdown-send-job-emails").attr('disabled', false);
        }, 3000)
        e.preventDefault();
    });

    /* User has clicked print button for job */
    $('#print-job').on('click', function () {
        if (job_form_validation.checkForm()) {
            preparePrintJob(); // Function is in this file
        } else {
            $(this).attr('disabled', true);
            $("#job-form").trigger("submit");
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please update the form with all required fields before producing a print out" });
        }
    });

    $('#unassign-operator').on('click', function () {
        $("#operator-ph").text('');
        $("#operator-email").text('');
        $("#operator-select").val(null).trigger('change');
    });

    $('#unassign-foreman').on('click', function () {
        $("#foreman-ph").text('');
        $("#foreman-email").text('');
        $("#foreman-select").val(null).trigger('change');
    });

    $('#unassign-truck').on('click', function () {
        $("#boom").val('');
        $("#capacity").val('');
        $("#truck-select").val(null).trigger('change');
    });

    // We delay the print button from enabling as there's ajax calls to wait on/scripts to execute
    setTimeout(function () {
        preventLeaveUnsavedForm(); // Prevent user accidentally leaving form when edits made
        $("#print-job").attr('disabled', false);
        $("#dropdown-send-job-emails").attr('disabled', false);
    }, 1000);

});

/* Populate our generated form for details updating, we use the same one from the start job page so we can reuse code/use familiar interfaces for simplicity */
function populateJobForm(details) {

    /* Preselect job type */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleJobType.php",
        type: 'POST',
        data: $.param({ job_type_id: details['job_type'] }),
        success: function (result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#job-type-select").append(option).trigger('change');
        }
    });

    /* Preselect concrete type */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleConcreteType.php",
        type: 'POST',
        data: $.param({ concrete_type_id: details['concrete_type'] }),
        success: function (result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#concrete-type-select").append(option).trigger('change');
            $("#concrete-charge").val(data.charge);
            $("#concrete-type-select").select2('data')[0].charge = data.charge;

            /* Concrete charge change */
            $('#concrete-type-select').on("change", function () {
                var data = $(this).select2('data')[0];
                $("#concrete-charge").val(data.charge);
                updateCubicRate($("#cubic-charge"));
            });

            /* Invoice data default set */
            if ($("#invoice-concrete-rate").val() == "")
                $("#invoice-concrete-rate").val($("#concrete-charge").val());

            $("#concrete-type-select").trigger({
                type: 'select2:select',
                params: {
                    data: data
                }
            });
        }
    });

    /* Preselect mix type */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleConcreteType.php",
        type: 'POST',
        data: $.param({ concrete_type_id: details['mix_type'] }),
        success: function (result) {
            if (result == "") return; // null check as mix type can be empty
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#mix-type-select").append(option).trigger('change');
            $("#mix-charge").val(data.charge);
            $("#mix-type-select").select2('data')[0].charge = data.charge;

            /* Mix type charge change */
            $('#mix-type-select').on("change", function () {
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

    /* Preselect truck */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleTruck.php",
        type: 'POST',
        data: $.param({ truck_id: details['truck_id'] }),
        success: function (result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#truck-select").append(option).trigger('change');
        }
    });

    /* Preselect customer */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleCustomer.php",
        type: 'POST',
        data: $.param({ customer_id: details['customer_id'] }),
        success: function (result) {
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#customer-select").append(option).trigger('change');
        }
    });

    /* Preselect layer */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleLayer.php",
        type: 'POST',
        data: $.param({ layer_id: details['layer_id'] }),
        success: function (result) {
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
        data: $.param({ supplier_id: details['supplier_id'] }),
        success: function (result) {
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
        data: $.param({ operator_id: details['operator_id'] }),
        success: function (result) {
            if (result == "") return; // null check as operator can be empty
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#operator-select").append(option).trigger('change');
        }
    });

    /* Preselect foreman */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getSingleForeman.php",
        type: 'POST',
        data: $.param({ foreman_id: details['foreman_id'] }),
        success: function (result) {
            if (result == "") return; // null check as operator can be empty
            var data = JSON.parse(result);
            var option = new Option(data.text, data.id, true, true);
            $("#foreman-select").append(option).trigger('change');
        }
    });

    //preselect linesman
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getLinesman.php",
        type: 'POST',
        success: function (result) {
            var selectedIds = details['linesman_user_ids'];
            if (result == "") return; // null check as operator can be empty
            var data = JSON.parse(result);
            // console.log(data, "data");
            if (!selectedIds || selectedIds.length === 0) {
                data.forEach(function (linesman) {
                    var newOption = new Option(linesman.text, linesman.id, false, false);
                    $('#linesman-select').append(newOption).trigger('change');
                });
            } else {
                data.forEach(function (linesman) {
                    var isSelected = selectedIds.includes(linesman.id);
                    var newOption = new Option(linesman.text, linesman.id, isSelected, isSelected);
                    $('#linesman-select').append(newOption).trigger('change');
                });
            }

        }
    });
}

function preparePrintJob() {

    // Customer/client info
    $(".print-client-name").each(function () {
        $(this).text($("#contact-name").text());
    });
    $("#print-company-name").text($("#cust-company-name").text());
    $("#print-address-1").text($("#cust-addr-1").text());
    $("#print-address-2").text($("#cust-addr-2").text());
    $("#print-address-3").text($("#cust-addr-3").text());
    $("#print-client-phone").text("Ph: " + $("#cust-ph").text());
    $("#print-client-mobile").text("Mob: " + $("#cust-mob-ph").text());
    $("#print-client-email").text($("#cust-email").text());

    // Job details
    $("#print-date").text($("#job-date").datepicker({ dateFormat: 'dd,MM,yyyy' }).val());
    $("#print-time").text(moment($("#job-timing").val(), "H,mm:ss").format("hh:mm A"));
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

    var concrete_type_data = $('#concrete-type-select').select2('data');
    $("#print-concrete-type").text(concrete_type_data[0].text);

    var mix_type_data = $('#mix-type-select').select2('data');
    if (mix_type_data.length > 0) {
        $("#print-mix-type").text(mix_type_data[0].text);
        $("#print-mix-type").parent().show();
    } else $("#print-mix-type").parent().hide();

    // Truck
    var pump_truck_data = $('#truck-select').select2('data');
    $("#print-pump-truck").text(pump_truck_data[0].text);

    /* Contact details */

    // Supplier
    $("#print-supplier-name").text($("#supplier-name").text());
    $("#print-supplier-phone").text($("#supplier-ph").text());
    $("#print-supplier-mobile").text($("#supplier-mob-ph").text());
    $("#print-supplier-email").text($("#supplier-email").text());

    // Layer
    $("#print-layer-name").text($("#layer-name").text());
    $("#print-layer-phone").text($("#layer-ph").text());
    $("#print-layer-mobile").text($("#layer-mob-ph").text());
    $("#print-layer-email").text($("#layer-email").text());

    // Operator
    /*  $("#print-operator-name").text($("#operator-name").text()); */
    $("#print-operator-phone").text($("#operator-ph").text());
    $("#print-operator-email").text($("#operator-email").text());

    // Foreman
    $("#print-foreman-phone").text($("#foreman-ph").text());
    $("#print-foreman-email").text($("#foreman-email").text());


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

function preventLeaveUnsavedForm() {
    var $form = $('#job-form'),
        origForm = $form.serialize();

    // Remove the message for prevent leave when submit is clicked.
    $('form#job-form').submit(function () {
        window.onbeforeunload = null;
    });

    // When form is changed, add the onbeforeload event.
    $('form#job-form :input').on('change input', function () {
        if ($form.serialize() !== origForm) {
            window.onbeforeunload = function () {
                return "Leaving this page will discard the changes you have made!";
            }
        }
    });
}