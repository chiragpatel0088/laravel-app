/* Formatting function for row details */
function format(d) {
    // `d` is the original data object for the row

    if (d.site_visits.site_visit_photo == null || d.site_visits.site_visit_photo == '') {
        var photo = '<td>No Photo Supplied</td>';
    } else {
        var photoArray = d.site_visits.site_visit_photo.split(',');
        var photo = '';
        for (var i = 0; i < photoArray.length; i++) {
            photo += '<tr><td class="font-w600">Photo' + (i + 1) + ':</td></tr>';
            if (photoArray[i] != '' && photoArray[i] != null) {
                photo += '<td  colspan="5" class="js-gallery img-fluid-100"><div class="animated fadeIn"><a class="img-link img-link-zoom-in img-thumb img-lightbox" href="./' + photoArray[i] + '">' + '<img class="img-fluid" src="./' + photoArray[i] + '" alt="">' + '</a></div></td>';
            } else {
                photo += '<td  colspan="5"> No Photo Supplied</div>';
            }

        }
    }
    // Site visit complete date cell
    if (d.site_visits.site_visit_completed == '' || d.site_visits.site_visit_completed == null)
        d.site_visits.site_visit_completed = 'Not Complete';

    // Site visit completed which operator cell
    if (d.site_visits.site_visit_completed_by == '' || d.site_visits.site_visit_completed_by == null)
        d.site_visits.site_visit_completed_by = 'Not Complete';

    return '<table cellpadding="7" cellspacing="0" border="0" style="padding-left:50px;" class="table table-vcenter table-borderless table-sm">' +
        '<tr>' +
        '<td class="font-w600">Photo:</td>' +
        photo +
        '</tr>' +
        '</table>';
}

$(document).ready(function () {
    $("linesman-job-form :input").prop("disabled", true);
    $(".operator-job-field").prop("disabled", false);

    $('#line-size-select').select2({
        tags: true,  // allow user entery manually
        placeholder: "Select or enter size",
        allowClear: true
    });

    // Listen for the clear event on the Select2 element
    $('#line-size-select').on('select2:clear', function () {
        // Set the value to 0 when cleared
        $('#line-size-select').val('').trigger('change');
        sendAjaxRequest(); // Send AJAX request after clearing
    });

    var job_form_validator = $("#linesman-job-form").validate({
        ignore: [],
        errorClass: "is-invalid text-danger",
        errorPlacement: function (error, element) {
            if (element.parent().hasClass("input-group")) {
                error.insertAfter(element.parent());
            } else if (element.hasClass("js-select2")) {
                error.insertAfter(element.parent().find(".select2-container"));
            } else if (element.parent().hasClass("custom-radio")) {
                error.insertAfter(element.parent().parent());
            } else if (element.hasClass("custom-control-input")) {
                //error.insertAfter(element.parent().parent());
            } else if (element.attr("type") == "hidden") {
                //error.insertAfter(element.parent().parent());
            } else
                error.insertAfter(element);
        },
        rules: {
            "actual-job-timing": {
                required: !0,
                step: false
            },
            "line-size-select": {
                required: true,
            },
            "confirm-completion": {
                required: !0
            }
        },
        submitHandler: function (form) {
            form.submit();
        }
    });


    /* Site inspections for the job */
    var siteInspectionsTable = $('#js-dataTable-jobSiteInspections').DataTable({
        ajax: {
            url: "inc/backend/editor_controllers/jobSiteInspections.php",
            type: 'POST',
            data: function (d) {
                d.job_id = $("#job_id").val();
            }
        },
        columns: [{
            data: 'site_visits.site_visit_completed',
            className: '',
            render: function (data, type, row) {
                return 'By <strong>' + row.site_visits.site_visit_completed_by + '</strong> on ' + data;
            }
        },
        {
            data: null,
            defaultContent: '',
            className: 'details-control',
            orderable: false
        }

        ],
        columnDefs: [],
        autoWidth: !1,
        pageLength: -1,
        order: [0, 'desc'],
        lengthMenu: [
            [5, 10, -1],
            [5, 10, 'All']
        ],

        language: {
            "emptyTable": "No site inspection(s) required"
        },
        dom: "<'row'<'dt-buttons-group col-md-12 text-center'>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'><'col-sm-12 col-md-7 d-none d-lg-block'>>",
        drawCallback: function (settings) {
            // Makes details/child row show by default
            $("#js-dataTable-jobSiteInspections").DataTable().rows().every(function () {
                var tr = $(this.node());
                row = siteInspectionsTable.row(tr);
                row.child(format(row.data())).show();
                tr.addClass('shown');
            });

        }
    });

    // Hide site inspection section if there is none
    $("#site-inspection-section").fadeOut(0);
    setTimeout(function () {
        if (siteInspectionsTable.data().count() > 0) {
            $("#site-inspection-section").slideDown();
            // Init the popup because it's not rendered at ready
            $('.js-gallery').magnificPopup({
                type: 'image',
                closeOnContentClick: true,
                closeBtnInside: false,
                fixedContentPos: true,
                mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
                image: {
                    verticalFit: true
                },
                delegate: 'a'
            });
        }
    }, 200);

    // Function to handle form input changes and send AJAX request
    function sendAjaxRequest() {
        $.ajax({
            type: "POST",
            url: "process",
            data: $("#linesman-job-form").serialize() + "&subLinesmanUpdateJob=true", // only input
            success: function (response) {
                // Handle success response
            }
        });
    }

    // Listen for changes on other form inputs
    setTimeout(function () {
        $('form#linesman-job-form :input').on('change input', function () {
            sendAjaxRequest();
        });
    }, 500);

    var address_url = getGoogleAddressDirectionsFromCurrentLocation($("[name=address_1]").val(), "", $("[name=suburb]").val(), $("[name=city]").val(), $("[name=post_code]").val());
    $("#address-link").attr("href", address_url);

});