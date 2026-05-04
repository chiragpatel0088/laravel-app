/* Calculate estimated pump time */
function calculateEstimatedPumpTime(cubics) {
    var job_cubics = parseFloat(cubics);
    var estimated_pump_time = (job_cubics / 30) * 60;
    $("#estimated-pump-time-text").text(estimated_pump_time.toFixed(0));
    $("#estimated-pump-time").val(estimated_pump_time.toFixed(0));
}

function calculateCubicRate(cubic_charge_checkbox, cubics, concrete_charge, min_charge, hourly_rate) {

    var min_charge = parseFloat(min_charge);
    var hourly_rate = parseFloat(hourly_rate);
    var cubics = parseFloat(cubics);

    if ($(cubic_charge_checkbox).is(":checked")) {
        // Cubic rate calculation
        var cubic_rate = (cubics * concrete_charge);
    } else {
        // Hourly rate
        var cubic_rate = (min_charge / 60) * hourly_rate;
    }

    return parseFloat(cubic_rate).toFixed(2);
}

/* Update cubic rate on job page */
function updateCubicRate(cubic_charge_checkbox) {

    if ($("#invoice-actual-cubics").val() > 0) cubics = $("#invoice-actual-cubics").val()
    else cubics = $("#cubics").val();

    var cubic_rate = calculateCubicRate(cubic_charge_checkbox, cubics, $("#concrete-charge").val(), $("#min").val(), $("#rate").val());
    if ($(cubic_charge_checkbox).is(":checked")) {
        $("#charge-label").text("Cubic Metre Charge");
    } else {
        $("#charge-label").text("Hourly Rate Charge");
    }

    $("#cubic-rate").val(cubic_rate);

    setTextValues();
    calculateChargeTotals();
}

/* Setup tool tips and various text values for the page unrelated to calculations */
function setTextValues() {

    if ($("#concrete-type-select").val() == null || $("#concrete-type-select").val() == "") {
        $("#print-rate-message, #quoted-rate-calculation").text("Please complete the form");
        $("#print-travel-fee-message, #travel-fee-calculation").text("Please complete the form");
        return;
    }

    if ($("#cubic-charge").is(":checked")) {
        var cubic_rate = parseFloat($("#cubics").val());
        var concrete_name = $("#concrete-type-select option:selected").text();
        var concrete_charge = $("#concrete-charge").val();
        $(".table-rate-description").each(function() {
            $(this).text("Cubic Rate");
        });
        // Convert to nice formatting
        concrete_charge = $.fn.dataTable.render.number(',', '.', 2, '$').display(concrete_charge);

        var rate_message = "Cubics of " + cubic_rate + "m3 x " + concrete_charge + " (" + concrete_name + ")";
    } else {
        var min_charge = parseFloat($("#min").val());
        min_charge = $.fn.dataTable.render.number(',', '.', 2, '$').display(min_charge);
        var hourly_rate = parseFloat($("#rate").val());
        hourly_rate = $.fn.dataTable.render.number(',', '.', 2, '$').display(hourly_rate);

        $(".table-rate-description").each(function() {
            $(this).text("Hourly Rate");
        });
        var rate_message = "(" + min_charge + " (Minimum charge) / 60) x " + hourly_rate + " (Hourly Rate)";
    }

    // Apply message for rates
    $("#print-rate-message").text(rate_message);
    $("#quoted-rate-tooltip").attr("data-original-title", rate_message);
    $("#quoted-rate-calculation").text(rate_message); // For new quote layout


    var job_range = $("#job-range").val();
    if (job_range < 21) {
        var range_message = "Travel distance of " + job_range + "km is less than 21km, no travel fee applied";

    } else {
        var travel_rate = parseFloat($("#travel").val());
        travel_rate = $.fn.dataTable.render.number(',', '.', 2, '$').display(travel_rate);
        var range_message = "(" + job_range + "km (Travel distance) - 20km) x " + travel_rate;
    }

    // Apply message for travel fees
    $("#travel-fee-tooltip").attr("data-original-title", range_message);
    $("#print-travel-fee-message").text(range_message);
    $("#travel-fee-calculation").text(range_message); // For new quote layout
}

/* Calculate the travel fee */
function calculateTravelFee(range, travel_rate) {
    var job_range = parseFloat(range);
    var travel_rate = parseFloat(travel_rate);
    if (job_range < 21) {
        var travel_fee = parseFloat(0).toFixed(2);
    } else {
        var travel_fee = ((job_range - 20) * travel_rate).toFixed(2);
    }

    return travel_fee;
}

/* Update travel fee text */
function updateTravelFeeText(range) {

    var travel_fee = calculateTravelFee(range, $("#travel").val());
    $("#travel-fee").val(travel_fee);

    setTextValues();
    calculateChargeTotals();
}

/* Calculates the charge total values */
function calculateChargeTotals() {

    /* Set text values on delay due to ajax delay */
    setTimeout(function() {
        setTextValues();
    }, 1000);
    if ($("#concrete-type-select").val() == null || $("#concrete-type-select").val() == "") return;

    var establishment_fee = parseFloat($("#establishment-fee").val());
    var cubic_rate = parseFloat($("#cubic-rate").val());
    var travel_fee = parseFloat($("#travel-fee").val());
    var discount_percentage = parseFloat($("#discount").val()) / 100;

    /* Calculate sub total */
    var sub_total = establishment_fee + cubic_rate + travel_fee;

    $("#sub-total").text($.fn.dataTable.render.number(',', '.', 2, '$').display(sub_total));

    /* Calculate discount*/
    var discount = discount_percentage * sub_total;
    var discounted_cost = sub_total - discount;

    $("#sub-total-inc-discount").text($.fn.dataTable.render.number(',', '.', 2, '$').display(discounted_cost));
    /* GST is 15% here */
    var gst = 0.15 * (sub_total - discount);
    $("#gst").text($.fn.dataTable.render.number(',', '.', 2, '$').display(gst));

    /* Calculate quoted/cubic cost */
    var cubic_cost = discounted_cost + gst;

    discount > 0 ? $("#total-discount").text("Total Discount: " + $.fn.dataTable.render.number(',', '.', 2, '$').display(discount)).slideDown() : $("#total-discount").slideUp();
    $("#cubic-cost").text($.fn.dataTable.render.number(',', '.', 2, '$').display(cubic_cost));

    /* Set text values on delay due to ajax delay */
    setTimeout(function() {
        setTextValues();
    }, 1000);
}

/* Customer discount message */
function discountPopoverTextUpdate() {
    if (parseFloat($("#discount").val()).toFixed(2) == parseFloat($("#customer-discount").val()).toFixed(2)) {
        title = "The customer's usual discount is currently applied";
        $("#discount-alert").slideUp();
    } else {
        title = "Click to apply the customer's usual discount of " + $("#customer-discount").val() + "%";
        $("#discount-alert").slideDown();
    }
    $('#customer-discount-tooltip').prop('title', title);
    $('#customer-discount-tooltip').attr('data-original-title', title);
}

$(document).ready(function() {

    /* If cubics change, we need to recalculate cubic charge and the estimated pump time */
    $("#cubics").on("change", function() {
        calculateEstimatedPumpTime($(this).val());
        updateCubicRate($("#cubic-charge"));
    });


    /* If the cubic charge checkbox changes, we use different calculations depending on it's state */
    $("#cubic-charge").on("change", function() {
        updateCubicRate($(this));
    });

    /* If range changes, check if meets condition to recalculate travel fee under different formula */
    $("#job-range").on("change", function() {
        updateTravelFeeText($(this).val());
    });

    /* If any of the charge values are changed, trigger a recalculation of the subtotal and cubic cost */
    $("#establishment-fee, #cubic-rate, #travel-fee, #discount").on("change", function() {
        calculateChargeTotals();
    });

    $("#discount").on("change", function() {
        discountPopoverTextUpdate();
    });

    setTimeout(function() {
        discountPopoverTextUpdate();

        // Move here due to fighting change events
        $("#concrete-type-select").on("change", function() {
            calculateEstimatedPumpTime($(this).val());
            updateCubicRate($("#cubic-charge"));
        });

        /* Call initially on load to show calculated values */
        calculateChargeTotals();
    }, 300)

});