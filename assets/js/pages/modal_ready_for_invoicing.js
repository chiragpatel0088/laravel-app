$(document).ready(function() {

    // Prevent form submission on enter key press
    $(window).keydown(function(event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });

    // Calculate the actual time on job only if it it's not already filled out. Case for this would be when a newly completed job is to be invoiced
    if ($("#invoice-actual-hours").val() == "")
        updateTimeOnJob();

    // Calculate hourly rate for job if it's empty
    if ($("#invoice-hourly-rate").val() == "")
        updateInvoiceHourlyRate();

    // Set fee messages
    updateFeeMessages();

    // Set the job id up for the form
    $("#invoice-job-id").val($("#job_id").val());

    // Set the concrete charge rate in the table for calcuations
    /* if ($("#invoice-concrete-rate").val() == "")
        setTimeout(function() {
            $("#invoice-concrete-rate").val($("#concrete-charge").val());
        }, 200); */

    /* Validation of form */
    $("#ready-for-invoicing-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function(error, element) {
            return; // Do not show error messages
        },
        rules: {
            "invoice-establishment-fee": {
                required: !0,
                number: !0
            },
            "invoice-range": {
                required: !0,
                number: !0
            },
            "invoice-travel-fee": {
                required: !0,
                number: !0
            },
            "invoice-actual-cubics": {
                required: !0,
                number: !0,
                minStrict: 0
            },
            "invoice-actual-hours": {
                required: !0
            },
            "invoice-cubic-rate": {
                required: !0,
                number: !0,
                minStrict: 0
            },
            "invoice-hourly-rate": {
                required: !0,
                number: !0
            },
            "invoice-washdown-fee": {
                required: !0,
                number: !0
            },
            "invoice-disposal-fee": {
                required: !0,
                number: !0
            },
            "invoice-discount": {
                required: !0,
                number: !0
            },
            "invoice-special-rate": {
                required: !0,
                number: !0
            }
        },
        submitHandler: function(form) {
            form.submit();
        }
    });

    /* Remove not required costs */

    // Remove washdown fee if the operator chose no
    if ($("input:radio[name=onsite-washout]:checked").val() == 0) {
        $("#invoice-washdown-fee").val(0);
    }

    // Remove the disposal fee if the operator chose no
    if ($("input:radio[name=onsite-disposal]:checked").val() == 0) {
        $("#invoice-disposal-fee").val(0);
    }

    /* Recalculation events */

    $("#invoice-range").on("change", function() {
        updateInvoiceTravelFee();
    });

    $("#invoice-actual-cubics, #invoice-concrete-rate").on("change", function() {
        updateInvoiceCubicRate();
        updateCubicDifference();
    });

    // Handle custom input messages when the user manually changes the calculated field to something else that does not equal the given formula output
    $("#invoice-cubic-rate").on("change", function() {
        updateInvoiceCubicRateFormulaText();
    });

    $("#invoice-actual-hours").on("change", function() {
        correctTimeInput($(this));
        updateInvoiceHourlyRate();
        updateTimeOnJobFormulaText();
    });

    $("#invoice-truck-rate").on("change", function() {
        updateInvoiceHourlyRate();
    });

    // Update hourly rate formula message
    $("#invoice-hourly-rate").on("change", function() {
        updateInvoiceHourlyRateFormulaText();
    });

    // Update the formula text (Custom or formula displayed)
    $("#invoice-travel-fee").on("change", function() {
        updateInvoiceTravelFeeFormulaText(calculateTravelFee($("#invoice-range").val(), $("#travel").val()), $("#invoice-travel-fee").val());
    });

    // When the modal opens
    $("#modal-ready-for-invoicing").on("shown.bs.modal", function(e) {
        $("#invoice-actual-cubics").trigger("change");

        // We update the link for Google Maps with the directions to the yard
        var gmap_directions = getGoogleAddressDirectionsFromYard($("#job-address-1").val(), $("#job-address-2").val(), $("#job-suburb").val(), $("#job-city").val(), $("#job-post-code").val());
        $("#g-map-directions-link").attr("href", gmap_directions);
    });

    // Total recalculations
    $("[name=invoice-rate-type], #invoice-special-rate, #invoice-actual-cubics, #invoice-range, #invoice-establishment, #invoice-travel-fee, #invoice-cubic-rate, #invoice-washdown-fee, #invoice-disposal-fee, #invoice-discount, #invoice-, #invoice-hourly-rate, #invoice-actual-hours").on("change", function(e) {
        updateInvoiceTotals();
    });

});