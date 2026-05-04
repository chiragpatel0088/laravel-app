$(document).ready(function() {
    $("#job-print-layout").toggleClass('d-none');
    $("#job-print-layout").hide();
    fillOutjobSheet();

    $("#job-print-layout").fadeIn(2000);
});

/* Prepare print layout with information of job */
function fillOutjobSheet() {

    var job = JSON.parse($("#job-details").val());
    var customer = JSON.parse($("#customer-details").val());
    var truck_details = JSON.parse($("#truck-details").val());
    var supplier = JSON.parse($("#supplier-details").val());
    var layer = JSON.parse($("#layer-details").val());
    var operator = JSON.parse($("#operator-details").val());
    var job_type = JSON.parse($("#job-type").val());
    var concrete_type = JSON.parse($("#concrete-type").val());
    if ($("#mix-type").length > 0)
        var mix_type = JSON.parse($("#mix-type").val());

    // Customer info
    $(".print-client-name").each(function() {
        $(this).text(customer.first_name + " " + customer.last_name);
    });
    $("#print-company-name").text(customer.name);
    $("#print-address-1").text(customer.address_1 + " " + customer.address_2);
    $("#print-address-2").text(customer.suburb);
    $("#print-address-3").text(customer.city + " " + customer.post_code);
    $("#print-client-phone").text("Ph: " + customer.contact_ph);
    $("#print-client-mobile").text("Mob: " + customer.contact_mob);
    $("#print-client-email").text(customer.email);

    // Job details
    var job_date = new Date(job.job_date);
    job_date = job_date.toLocaleDateString("en-NZ");
    $("#print-date").text(job_date);
    $("#print-time").text(moment(job.job_timing, 'H:m:s').format('hh:mm A'));
    $("#print-range").text(job.job_range + "km");

    // Job address
    $("#print-job-address-1").text(job.job_addr_1 + " " + job.job_addr_2);
    $("#print-job-address-2").text(job.job_suburb);
    $("#print-job-address-3").text(job.job_city + " " + job.job_post_code);

    // Job specifications
    $("#print-job-type").text(job_type.type_name);
    $("#print-cubics").text(job.cubics);
    $("#print-megapascal").text(job.mpa);

    $("#print-concrete-type").text(concrete_type.concrete_name);
    // Mix type is optional, so just check it's been selected first
    if ($("#mix-type").length > 0)
        $("#print-mix-type").text(mix_type.concrete_name);
    else $("#print-mix-type").parent().remove();

    // Truck
    $("#print-pump-truck").text(truck_details['number_plate'] + ' ' + truck_details['brand']);

    /* Contact details */

    // Supplier
    $("#print-supplier-name").text(supplier.supplier_firstname + ' ' + supplier.supplier_lastname + ' (' + supplier.supplier_name + ')');
    $("#print-supplier-phone").text(supplier.contact_ph);
    $("#print-supplier-mobile").text(supplier.contact_mob);
    $("#print-supplier-email").text(supplier.email);

    // Layer
    $("#print-layer-name").text(layer.layer_firstname + ' ' + layer.layer_lastname + ' (' + layer.layer_name + ')');
    $("#print-layer-phone").text(layer.contact_ph);
    $("#print-layer-mobile").text(layer.contact_mob);
    $("#print-layer-email").text(layer.email);

    // Operator
    $("#print-operator-name").text(operator.user_firstname + ' ' + operator.user_lastname);
    $("#print-operator-phone").text(operator.user_phone);
    $("#print-operator-email").text(operator.user_email);

    // Charge
    var establishment_fee = parseFloat(job.establishment_fee);
    var cubic_rate = parseFloat(job.cubic_rate);
    var travel_fee = parseFloat(job.travel_fee);
    var discount = parseFloat(job.discount);

    var sub_total = establishment_fee + cubic_rate + travel_fee;
    var discount_total = sub_total * (discount / 100);
    var gst = (sub_total - discount_total) * 0.15;

    var cubic_cost = sub_total - discount_total + gst;

    /* Conditional text */
    // Travel fee text
    if (job.job_range < 21) {
        var range_message = "Travel distance of " + job.job_range + "km is less than 21km, no travel fee applied";
    } else {
        var travel_rate = parseFloat(job.truck_travel_rate_km);
        var range_message = "(" + job.job_range + "km (Travel distance) - 20km) x " + travel_rate;
    }
    $("#print-travel-fee-message").text(range_message); // print message below travel fee

    // Cubic rate charge or hourly rate
    if (job.cubic_charge == 1) {
        var cubic_rate = parseFloat(job.cubics);

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

        var rate_message = "Cubics of " + cubic_rate + "m3 x " + highest_concrete_charge + " (" + mix_or_concrete_name + ")";
    } else {
        var min_charge = parseFloat(job.truck_min);
        min_charge = $.fn.dataTable.render.number(',', '.', 2, '$').display(min_charge);
        var hourly_rate = parseFloat(job.truck_rate);
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