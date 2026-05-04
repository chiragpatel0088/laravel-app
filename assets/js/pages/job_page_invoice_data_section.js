$(document).ready(function() {
    updateInvoiceTotals();
    updateFeeMessages();
    updateCubicDifference();
    updateTimeOnJobFormulaText();
    updateInvoiceTravelFeeFormulaText();

    setTimeout(function() {
        updateInvoiceCubicRateFormulaText();
        updateInvoiceHourlyRateFormulaText();
    }, 400)

    // We update the link for Google Maps with the directions to the yard
    var gmap_directions = getGoogleAddressDirectionsFromYard($("#job-address-1").val(), $("#job-address-2").val(), $("#job-suburb").val(), $("#job-city").val(), $("#job-post-code").val());
    $("#g-map-directions-link").attr("href", gmap_directions);

    /* Validation of form */
    $("#invoice-job-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        },
        rules: {
            "new-invoice-number": {
                required: !0
            }
        },
        messages: {
            "new-invoice-number": "Please input invoice number from Xero"
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
});