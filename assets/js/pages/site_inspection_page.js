$(document).ready(function () {
    var job_id = $("#job_id").val();

    /* Concrete truck select2 */
    var truck_select = $('#truck-select').select2({
        ajax: {
            url: 'inc/backend/data_retrieval/select2/select2_getTrucksWithBoomLength.php',
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
        closeOnSelect: false,
        placeholder: 'Select pumps that can be on this job',
        minimumResultsForSearch: -1
    });

    /* Preselect truck numbers for this site inspeciton that just opened */
    $.ajax({
        url: "inc/backend/data_retrieval/select2/select2_getPumpNumbersForSiteInspection.php",
        type: 'POST',
        data: $.param({ site_inspection_id: $("#site_inspection_id").val() })
    }).then(function (data) {
        var pump_numbers_array = JSON.parse(data);
        for (let i = 0; i < pump_numbers_array.length; i++) {
            var option = new Option(pump_numbers_array[i].boom + ' ' + pump_numbers_array[i].number_plate, pump_numbers_array[i].id, true, true);
            truck_select.append(option).trigger('change');
        }

        // manually trigger the `select2:select` event
        truck_select.trigger({
            type: 'select2:select',
            params: {
                data: data
            }
        });
    });

    /* Validate the form inside the send site inspection modal */
    var site_inspection_form_validation = $("#site-inspection-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function (error, element) {
            error.insertAfter(element.parent().parent());
        },
        rules: {
            "truck-select[]": {
                required: true
            },
            "confirm-completion": {
                required: true
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });
    // validate for photos
    $('input[id^="site-photo-input"]').each(function () {
        $(this).rules('add', {
            extension: 'jpg|jpeg|gif|png|bmp',
            messages: {
                extension: 'Invalid photo file type'
            }
        });
    });

    /* If the upload of the photo did not work, we'll output the message here */
    if ($("#upload-error").length) {
        Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: $("#upload-error").val() });
    }
    $('#deleteCanceled').on('click', function () {
        $.ajax({
            url: "inc/backend/data_retrieval/select2/select2_getPhotoUrl.php",
            type: 'POST',
            data: $.param({ $status: 2 })
        }).then(function (data) {
            var flag = JSON.parse(data);
            if (flag == true) {
                alert("delete success");
            } else {
                alert("delete failed");
            }
        });
    });

    var jobUpdateRequired = false;
    setTimeout(function () {
        $("#site-inspection-form :input").on("change", function () {
            jobUpdateRequired = true;
        });
    }, 2000);

    //2024-08-01
    $("#assign-linesmen").on('click', function (e) {
        e.preventDefault();

        if (site_inspection_form_validation.checkForm()) {
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
                    }
                });

        } else {
            $("#job-form").trigger("submit");
            $(this).attr("disabled", true);
            Dashmix.helpers('notify', { type: 'danger', icon: 'fa fa-times mr-1', message: "Please update the form with all required fields before assigning to operator" });
            return;
        }
    })

    $("#siteInspectionSwitchJobColor").on("click", function (e) {
        var jobID = document.getElementById("job_id").value;
        var siteInspectionId = document.getElementById("site_inspection_id").value;
        e.preventDefault();
        $.post('process.php', {
            switchJobColor: true, "job-id": jobID, "siteInspectionId": siteInspectionId, "isSiteInspection": true
        }, function () { }).done(function (response) {
            var jsonResponse = JSON.parse(response);
            // console.log(jsonResponse);
            var button = $("#siteInspectionSwitchJobColor");

            // if (jsonResponse.redirectUrl) {
            //     window.location.href = jsonResponse.redirectUrl;
            // }

            if (jsonResponse.statusColor == 0) {
                button.removeClass('btn-primary').addClass('btn-warning');
                button.text('Turn To Linesman Job');
            } else if (jsonResponse.statusColor == 1) {
                button.removeClass('btn-warning').addClass('btn-primary');
                button.text('Back To Ordinary Job');
            }
        });
    })
});