$(document).ready(function () {

    $(".content form#job-form input:not(:radio)").attr('readonly', 'readonly');
    $(".content form#job-form textarea").attr('readonly', 'readonly');
    $(".content form#job-form input[type=radio]:not(:checked)").attr('disabled', true);

    // Make time and date inputs have grey backgrounds as they are disabled, by default they do not behave the same as regular inputs
    $("[type=time], .js-datepicker").addClass("bg-gray-light");

    setTimeout(function () {
        $(".content #job-date").datepicker("destroy");
        $('.content [name=site-visit-required]:not(:checked)').attr('disabled', true);
        // $(".content select").select2("enable", false);

        // disable select2 
        $(".content select").each(function () {
            $(this).prop('disabled', true); // disable select element
            $(this).select2(); // initiate select2
        });
    }, 300);

});