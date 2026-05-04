function updateInvoiceTotals() {
    var rate_type = $("[name=invoice-rate-type]:checked").val();
    var establishment_fee = parseFloat($("#invoice-establishment-fee").val());
    var travel_fee = parseFloat($("#invoice-travel-fee").val());
    var cubic_rate = parseFloat($("#invoice-cubic-rate").val());
    var hourly_rate = parseFloat($("#invoice-hourly-rate").val());
    var washdown_fee = parseFloat($("#invoice-washdown-fee").val());
    var disposal_fee = parseFloat($("#invoice-disposal-fee").val());
    var special_rate = parseFloat($("#invoice-special-rate").val());

    /* Need to add toggle for hourly rate or cubic rate here */
    if (rate_type == "cubic")
        var rate_to_add = cubic_rate;
    else if (rate_type == "hourly")
        var rate_to_add = hourly_rate;
    else if (rate_type == "special")
        var rate_to_add = special_rate;

    // Sub total
    if (rate_type == "special") {
        var subtotal = rate_to_add;
    } else {
        var subtotal = parseFloat(establishment_fee + travel_fee + rate_to_add + washdown_fee + disposal_fee);
    }

    // Extra costs
    subtotal += +parseFloat($("#invoice-special-1").val()) + parseFloat($("#invoice-special-2").val());

    // Discount handling
    var discount_rate = $("#invoice-discount").val() / 100;
    var subtotal_discounted = parseFloat(subtotal - (subtotal * discount_rate));

    // GST
    var gst_rate = $("[name=gst-rate-at-moment-of-invoice]").length ? $("[name=gst-rate-at-moment-of-invoice]").val() / 100 : 0.15;
    var gst = parseFloat(subtotal_discounted * gst_rate);

    // Final total
    var final_total = parseFloat(parseFloat(gst) + parseFloat(subtotal_discounted));


    // Do not render calculated values if they are NaN. This can happen if user inputs weird characters like alphabet things and '?'
    if (isNaN(subtotal) || isNaN(subtotal_discounted) || isNaN(gst) || isNaN(final_total) || isNaN(hourly_rate)) {
        return;
    }

    $("#invoice-sub-total").html($.fn.dataTable.render.number(',', '.', 2, '$').display(subtotal));
    $("#invoice-sub-total-inc-discount").html($.fn.dataTable.render.number(',', '.', 2, '$').display(subtotal_discounted));
    $("#invoice-gst").html($.fn.dataTable.render.number(',', '.', 2, '$').display(gst));
    $("#invoice-cubic-cost").html($.fn.dataTable.render.number(',', '.', 2, '$').display(final_total));
}

function updateFeeMessages() {
    var washout_required = $("[name=onsite-washout]:checked").val();
    var disposal_required = $("[name=onsite-disposal]:checked").val();

    if (washout_required == 1) {
        $("#washdown-message").html("Washdown fee required");
    } else {
        $("#washdown-message").html("Washdown not required");
    }

    if (disposal_required == 1) {
        $("#disposal-message").html("Concrete disposal fee required");
    } else {
        $("#disposal-message").html("Concrete disposal fee not required");
    }
}

function updateCubicDifference() {
    var cubics = parseFloat($("#cubics").val());
    var actual_cubics = parseFloat($("#invoice-actual-cubics").val());

    var cubic_difference = (cubics - actual_cubics).toFixed(2);

    if (cubic_difference > 0) {
        var formula_text = cubic_difference + "m<sup>3</sup> less than initial estimate";
    } else {
        var formula_text = Math.abs(cubic_difference) + "m<sup>3</sup> more than initial estimate";
    }
    $("#invoice-cubic-difference").html(formula_text);
}

function updateInvoiceTravelFee() {
    var travel_fee = calculateTravelFee($("#invoice-range").val(), $("#travel").val());

    if (isNaN(travel_fee)) return;

    $("#invoice-travel-fee").val(travel_fee);
    updateInvoiceTravelFeeFormulaText();
}

function updateInvoiceTravelFeeFormulaText(travel_fee, curr_travel_fee) {
    var travel_fee = parseFloat(calculateTravelFee($("#invoice-range").val(), $("#travel").val()));
    var curr_travel_fee = parseFloat($("#invoice-travel-fee").val());

    if (curr_travel_fee != travel_fee) {
        var formula_text = "Custom travel fee";
        $("#invoice-travel-fee-calculation").html(formula_text);
    } else if ($("#invoice-range").val() < 21) {
        $("#invoice-travel-fee-calculation").text("Range less than 21km, no fee applied");
    } else {
        var formula_text = "(" + $("#invoice-range").val() + "km - 20km) x $" + $("#travel").val();
        $("#invoice-travel-fee-calculation").text(formula_text);
    }
}

function updateInvoiceCubicRate() {
    var cubic_rate = calculateCubicRate($("#cubic-charge"), $("#invoice-actual-cubics").val(), $("#invoice-concrete-rate").val(), $("#min").val(), $("#rate").val());

    if (isNaN(cubic_rate)) return;

    $("#invoice-cubic-rate").val(cubic_rate);

    updateInvoiceCubicRateFormulaText();
}

function updateInvoiceCubicRateFormulaText() {
    if ($("#cubic-charge").is(":checked")) {
        var cubic_rate = calculateCubicRate($("#cubic-charge"), $("#invoice-actual-cubics").val(), $("#invoice-concrete-rate").val(), $("#min").val(), $("#rate").val());
        var curr_cubic_rate = parseFloat($("#invoice-cubic-rate").val());
        if (curr_cubic_rate != cubic_rate) {
            var formula_text = "Custom cubic rate";
            $("#actual-cubic-rate-calculation").html(formula_text);
        } else {
            var formula_text = "Cubics of " + $("#invoice-actual-cubics").val() + "m<sup>3</sup> x $" + $("#invoice-concrete-rate").val();
            $("#actual-cubic-rate-calculation").html(formula_text)
        }
    }
}

function updateInvoiceHourlyRate() {
    var job_hours = getTimeOnJobDecimalValue();
    var hourly_rate = calculateHourlyRate(job_hours, $("#invoice-truck-rate").val());

    if (isNaN(hourly_rate)) return;

    $("#invoice-hourly-rate").val(hourly_rate);

    updateInvoiceHourlyRateFormulaText();
}

function updateInvoiceHourlyRateFormulaText() {
    var job_hours = getTimeOnJobDecimalValue();
    var hourly_rate = calculateHourlyRate(job_hours, $("#invoice-truck-rate").val());

    var curr_hourly_rate = parseFloat($("#invoice-hourly-rate").val());
    if (curr_hourly_rate != hourly_rate) {
        var formula_text = "Custom hourly rate";
        $("#actual-hourly-rate-calculation").html(formula_text);
    } else {
        var job_hours_time_format = $("#invoice-actual-hours").val().split(":");
        var formula_text = "Hourly rate of " + pad(job_hours_time_format[0], 2) + ":" + pad(job_hours_time_format[1], 2) + " x $" + $("#invoice-truck-rate").val();

        $("#actual-hourly-rate-calculation").html(formula_text)
    }
}

function calculateHourlyRate(job_hours, hourly_rate) {
    var hours = parseFloat(job_hours);
    var hourly_rate = parseFloat(hourly_rate);

    return (hours * hourly_rate).toFixed(2);
}

function getGoogleAddressDirectionsFromYard(address_1, address_2, suburb, city, post_code) {
    // Address href modification for origin and destination URL generation
    // Source: https://gearside.com/easily-link-to-locations-and-directions-using-the-new-google-maps/

    // Base link to work off
    // https://maps.google.com?saddr=88+Gargan+Road+Tauriko+Tauranga&daddr=314+Cameron+Road+Tauriko+Tauranga
    var daddr = "";
    if (address_1 != "") daddr += address_1.trim().replace(" ", "+").replace(" ", "+");
    /* if (address_2 != "") daddr += "+" + address_2.trim().replace(" ", "+"); */ // Removed due to conflicts with Google Maps URL generation (Cannot find address)
    if (suburb != "") daddr += "+" + suburb.trim().replace(" ", "+");
    if (city != "") daddr += "+" + city.trim().replace(" ", "+");
    if (post_code != "") daddr += "+" + post_code.trim().replace(" ", "+");

    return "https://maps.google.com?saddr=88+Gargan+Road+Tauriko+Tauranga&daddr=" + daddr;
}

function getGoogleAddressDirectionsFromCurrentLocation(address_1, address_2, suburb, city, post_code) {
    // Address href modification for origin and destination URL generation
    // Source: https://gearside.com/easily-link-to-locations-and-directions-using-the-new-google-maps/

    // Base link to work off
    // https://maps.google.com?saddr=88+Gargan+Road+Tauriko+Tauranga&daddr=314+Cameron+Road+Tauriko+Tauranga
    var daddr = "";
    if (address_1 != "") daddr += address_1.trim().replace(" ", "+").replace(" ", "+");
    /* if (address_2 != "") daddr += "+" + address_2.trim().replace(" ", "+"); */ // Removed due to conflicts with Google Maps URL generation (Cannot find address)
    if (suburb != "") daddr += "+" + suburb.trim().replace(" ", "+");
    if (city != "") daddr += "+" + city.trim().replace(" ", "+");
    if (post_code != "") daddr += "+" + post_code.trim().replace(" ", "+");

    return "https://maps.google.com?saddr=Current+Location&daddr=" + daddr;
}

/* Padding function from https://stackoverflow.com/questions/10073699/pad-a-number-with-leading-zeros-in-javascript */
function pad(n, width, z) {
    z = z || '0';
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

/* Get difference between 2 time values */
function calculateTimeOnJob() {
    // Actual hours for job using actual start and finish time
    var startTime = moment($("#actual-job-timing").val(), "HH:mm:ss");
    var finishTime = moment($("#job-finish-time").val(), "HH:mm:ss");

    // calculate total duration
    var duration = moment.duration(finishTime.diff(startTime));

    // duration in hours
    var hours = parseInt(duration.asHours());

    // duration in minutes
    var minutes = parseInt(duration.asMinutes()) % 60;

    return [Math.abs(hours), Math.abs(minutes)];
}

function getTimeOnJobDecimalValue() {
    var job_hours = $("#invoice-actual-hours").val().split(":");

    return parseInt(job_hours[0]) + parseFloat(job_hours[1] / 60);
}

function updateTimeOnJob() {
    var time_on_job = calculateTimeOnJob();
    $("#invoice-actual-hours").val(pad(time_on_job[0], 2) + ":" + pad(time_on_job[1], 2));

    updateTimeOnJobFormulaText();
}

function updateTimeOnJobFormulaText() {
    var time_on_job = calculateTimeOnJob();
    var curr_time_on_job = $("#invoice-actual-hours").val().split(":");
    curr_time_on_job[0] = parseInt(curr_time_on_job[0]);
    curr_time_on_job[1] = parseInt(curr_time_on_job[1]);

    if (time_on_job[0] != curr_time_on_job[0] || time_on_job[1] != curr_time_on_job[1]) {
        var formula_text = "Custom amount of hours";
        $("#actual-hours-calculation").html(formula_text);
    } else {
        var formula_text = "Difference between start " + moment($("#actual-job-timing").val().slice(0, -3), "HH:mm").format("hh:mm A") + " and finish " + moment($("#job-finish-time").val().slice(0, -3), "HH:mm").format("hh:mm A");
        $("#actual-hours-calculation").text(formula_text);
    }
}


/* Hours and minutes input for job */
function timeInputValidation(event) {
    var theEvent = event || window.event;

    // Handle paste
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
        // Handle key press
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    var regex = /[0-9]|\:/;
    var colon_check = $(theEvent.srcElement).val().includes(":") && key == ":" ? true : false;

    if (!regex.test(key) || colon_check) {
        theEvent.returnValue = false;
        if (theEvent.preventDefault) theEvent.preventDefault();
    }
}

function correctTimeInput(element) {
    var time = element.val().split(":");

    if (time[0] == "" || typeof time[0] == 'undefined') time[0] = "0";
    if (time[1] == "" || typeof time[1] == 'undefined') time[1] = "00";

    if (parseInt(time[1]) == 60) {
        time[0] = parseInt(time[0]) + 1;
        time[1] = 0;
    } else if (parseInt(time[1]) > 60) {
        actual_minutes = parseInt(parseInt(time[1]) % 60);
        extra_hours = parseInt(parseInt(time[1]) / 60);
        time[1] = actual_minutes;
        time[0] = parseInt(time[0]) + extra_hours;
    }

    element.val(parseInt(time[0]) + ":" + pad(parseInt(time[1]), 2, '0'));
}