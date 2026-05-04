// https://datatables.net/examples/api/row_details.html



/* Formatting function for row details */
function format(d) {
    // `d` is the original data object for the row
    // Image/site photo cell
    if(d.site_visits.site_visit_photo==null||d.site_visits.site_visit_photo==''){
        var photo = '<td>No Photo Supplied</td>';
    }else{
        var photoArray=d.site_visits.site_visit_photo.split(',');
        var photo='';
        for(var i=0;i<photoArray.length;i++){
            photo+='<tr><td class="font-w600">Photo'+(i+1)+':</td></tr>';
            if (photoArray[i] != '' && photoArray[i] != null){
                 photo += '<td  colspan="5" class="js-gallery img-fluid-100"><div class="animated fadeIn"><a class="img-link img-link-zoom-in img-thumb img-lightbox" href="./' + photoArray[i] + '">' + '<img class="img-fluid" src="./' + photoArray[i] + '" alt="">' + '</a></div></td>';
            }else{
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
        '<td class="font-w600">Pump Numbers:&nbsp;'+d.jobs.date_created +'</td>' +
        '</tr>' +
        photo+
        '</table>';
}

$(document).ready(function() {
    var siteInspectionsEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/jobSiteInspections.php",
            type: 'POST',
            data: function(d) {
                d.job_id = $("#job-id").val();
            }
        },
        table: "#js-dataTable-jobSiteInspections",
        fields: [{
            label: "Operator:",
            name: "site_visits.site_visit_assigned_operator",
            type: "select",
            placeholder: 'Select operator'
        }],
        i18n: {
            create: {
                button: "New<span class='d-none d-md-inline'> Site Inspection</span>",
                title: "Assign New Site Inspection",
                submit: "Assign"
            },
            edit: {
                button: "Update Operator",
                title: "Change Assigned Operator",
                submit: "Assign"
            },
            remove: {
                button: "Remove<span class='d-none d-md-inline'> Site Inspection</span>",
                title: "Delete Site Inspection",
                submit: "Delete"
            }
        }
    });

    // When a site inspection is created
    siteInspectionsEditor.on('postCreate', function(e) {
        // In general, postCreate means a new site inspection was assigned
        $("#job-status").val(5); // We set it to 5 because the job is now requiring a site inspection
        Dashmix.helpers('notify', { type: 'success', icon: 'fa fa-check mr-1', message: 'Site inspection assigned and operator notified!' });
    });

    var siteInspectionsTable = $('#js-dataTable-jobSiteInspections').DataTable({
        ajax: {
            url: "inc/backend/editor_controllers/jobSiteInspections.php",
            type: 'POST',
            data: function(d) {
                d.job_id = $("#job-id").val();
            }
        },
        columns: [{
            data: null,
            defaultContent: '',
            className: 'select-checkbox',
            orderable: false
        },
            {
                data: 'users.user_firstname',
                editField: 'site_visits.site_visit_operator_assigned',
                render: function(data, type, row) {
                    return row.users.user_firstname + " " + row.users.user_lastname;
                }
            },
            {
                data: 'site_visits.site_visit_completed',
                render: function(data, type, row) {
                    if (row.site_visits.site_visit_completed == null || row.site_visits.site_visit_completed == '') {
                        return '<span class="text-danger text-uppercase font-w700">' +
                            '<i class="fa fa-fw fa-times-circle"></i> ' +
                            '<span class="d-none d-lg-inline">Not Complete</span>' + '</span>';
                    } else
                        return '<span class="text-success text-uppercase font-w700">' +
                            '<i class="fa fa-fw fa-check-circle"></i> ' +
                            '<span class="d-none d-lg-inline">Complete</span>' + '</span>';
                }
            },
            {
                data: 'site_visits.date_created',
                className: 'd-none d-sm-table-cell'
            },
            {
                data: null,
                render: function(data, type, row) {
                    return "<a href='./site_inspection?id=" + row.site_visits.id + "'><i class='far fa-eye'></i></a>";
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
        select: {
            style: 'os',
            selector: 'td.select-checkbox'
        },
        autoWidth: !1,
        pageLength: -1,
        order: [2, 'desc'],
        lengthMenu: [
            [5, 10, -1],
            [5, 10, 'All']
        ],
        buttons: [
            { extend: "create", editor: siteInspectionsEditor, className: "btn btn-sm btn-primary", formMessage: 'The operator is automatically sent a email and system notification of their newly assigned site inspection.' },
            { extend: "edit", editor: siteInspectionsEditor, className: "btn btn-sm btn-secondary" },
            { extend: "remove", editor: siteInspectionsEditor, className: "btn btn-sm btn-danger", formMessage: 'Are you sure you want to remove this pending site inspection for this job?' }
        ],
        language: {
            "emptyTable": "No site inspection(s) required"
        },
        dom: "<'row'<'dt-buttons-group col-md-12 text-center'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'><'col-sm-12 col-md-7 d-none d-lg-block'>>",
        drawCallback: function(settings) {
            // Makes details/child row show by default
            $("#js-dataTable-jobSiteInspections").DataTable().rows().every(function() {
                var tr = $(this.node());
                row = siteInspectionsTable.row(tr);
                row.child(format(row.data())).show();
                tr.addClass('shown');
            });

            // Init gallery on initial draw of table
            initGalleryForSiteInspectionPhotos();
        }
    });

    // Add event listener for opening and closing details
    $('#js-dataTable-jobSiteInspections tbody').on('click', 'td.details-control', function() {
        var tr = $(this).closest('tr');
        var row = siteInspectionsTable.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child(format(row.data())).show();
            initGalleryForSiteInspectionPhotos();
            tr.addClass('shown');
        }
    });

    // Destroy the buttons if the job is in a uneditable state
    if ($("[name=hide-dt-site-inspection-buttons]").val() == 1) {
        $(".dt-buttons-group").remove();
    }

});

function initGalleryForSiteInspectionPhotos() {
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