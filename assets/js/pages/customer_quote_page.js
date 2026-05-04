$(document).ready(function() {
    $("#quote-print-layout").toggleClass('d-none');
    $("#quote-print-layout").hide();
    fillOutQuoteSheet();

    $("#quote-print-layout").fadeIn(2000);
});

/* Prepare print layout with information of quote */
function fillOutQuoteSheet() {

    var quote = JSON.parse($("#quote-details").val());
    var customer = JSON.parse($("#customer-details").val());
    var truck_details = JSON.parse($("#truck-details").val());
    var job_type = JSON.parse($("#job-type").val());
    var concrete_type = JSON.parse($("#concrete-type").val());
    if ($("#mix-type").length > 0)
        var mix_type = JSON.parse($("#mix-type").val());

    // Customer info
    $("#print-client-name").text(customer.first_name + " " + customer.last_name);
    $("#print-company-name").text(customer.name);
    $("#print-address-1").text(customer.address_1 + " " + customer.address_2);
    $("#print-address-2").text(customer.suburb);
    $("#print-address-3").text(customer.city + " " + customer.post_code);

    // Job details
    var job_date = new Date(quote.job_date);
    job_date = job_date.toLocaleDateString("en-NZ");
    $("#print-date").text(job_date);
    $("#print-time").text(quote.job_timing);
    $("#print-range").text(quote.quoted_range + "km");

    // Job address
    $("#print-job-address-1").text(quote.job_addr_1 + " " + quote.job_addr_2);
    $("#print-job-address-2").text(quote.job_suburb);
    $("#print-job-address-3").text(quote.job_city + " " + quote.job_post_code);

    // Job specifications
    $("#print-job-type").text(job_type.type_name);
    $("#print-cubics").text(quote.cubics);
    $("#print-megapascal").text(quote.mpa);

    // Truck
    $("#print-pump-truck").text(truck_details['number_plate'] + ' ' + truck_details['brand']);

    $("#print-concrete-type").text(concrete_type.concrete_name);
    // Mix type is optional, so just check it's been selected first
    if ($("#mix-type").length > 0)
        $("#print-mix-type").text(mix_type.concrete_name);
    else $("#print-mix-type").parent().remove();

    // Charge
    var establishment_fee = parseFloat(quote.establishment_fee);
    var cubic_rate = parseFloat(quote.cubic_rate);
    var travel_fee = parseFloat(quote.travel_fee);
    var discount = parseFloat(quote.discount);

    var sub_total = establishment_fee + cubic_rate + travel_fee;
    var discount_total = sub_total * (discount / 100);
    var gst = (sub_total - discount_total) * 0.15;

    var cubic_cost = sub_total - discount_total + gst;

    /* Conditional text */
    // Travel fee text
    if (quote.quoted_range < 21) {
        var range_message = "Travel distance of " + quote.quoted_range + "km is less than 21km, no travel fee applied";
    } else {
        var travel_rate = parseFloat(quote.truck_travel_rate_km);
        var range_message = "(" + quote.quoted_range + "km (Travel distance) - 20km) x " + $.fn.dataTable.render.number(',', '.', 2, '$').display(travel_rate);
    }
    $("#print-travel-fee-message").text(range_message); // print message below travel fee

    // Cubic rate charge or hourly rate
    if (quote.cubic_charge == 1) {
        var cubics = parseFloat(quote.cubics);

        if ($("#mix-type").length > 0) {
            var mix_or_concrete_name = concrete_type.concrete_charge > mix_type.concrete_charge ? concrete_type.concrete_name : mix_type.concrete_name;
            var highest_concrete_charge = concrete_type.concrete_charge > mix_type.concrete_charge ? concrete_type.concrete_charge : mix_type.concrete_charge;
        } else {
            var mix_or_concrete_name = concrete_type.concrete_name
            var highest_concrete_charge = concrete_type.concrete_charge;
        }

        $(".table-rate-description").each(function() {
            $(this).text("Cubic Rate");
        });

        // Convert to nice formatting
        highest_concrete_charge = $.fn.dataTable.render.number(',', '.', 2, '$').display(highest_concrete_charge);

        var rate_message = "Cubics of " + cubics + "m3 x " + highest_concrete_charge + " (" + mix_or_concrete_name + ")";
    } else {
        var min_charge = parseFloat(quote.truck_min);
        min_charge = $.fn.dataTable.render.number(',', '.', 2, '$').display(min_charge);
        var hourly_rate = parseFloat(quote.truck_rate);
        hourly_rate = $.fn.dataTable.render.number(',', '.', 2, '$').display(hourly_rate);

        $(".table-rate-description").each(function() {
            $(this).text("Hourly Rate");
        });

        var rate_message = "(" + min_charge + " (Minimum charge) / 60) x " + hourly_rate + " (Hourly Rate)";
    }
    $("#print-rate-message").text(rate_message); // print message below travel fee
    /* END Conditional text */

    $("#print-est-fee").text($.fn.dataTable.render.number(',', '.', 2, '$').display(establishment_fee));
    $("#print-cubic-rate").text($.fn.dataTable.render.number(',', '.', 2, '$').display(cubic_rate));
    $("#print-travel-fee").text($.fn.dataTable.render.number(',', '.', 2, '$').display(travel_fee));

    $("#print-sub-total").text($.fn.dataTable.render.number(',', '.', 2, '$').display(sub_total));
    $("#print-discount-total").text($.fn.dataTable.render.number(',', '.', 2, '$').display(discount_total));

    $("#print-sub-total-inc-discount").text($.fn.dataTable.render.number(',', '.', 2, '$').display(sub_total - discount_total));

    $("#print-discount-rate").text(discount + "%");

    $("#print-gst").text($.fn.dataTable.render.number(',', '.', 2, '$').display(gst));

    $("#print-cubic-cost").text($.fn.dataTable.render.number(',', '.', 2, '$').display(cubic_cost));
}