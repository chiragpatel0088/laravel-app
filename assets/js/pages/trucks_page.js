$(document).ready(function() {

    var trucksEditor = new $.fn.dataTable.Editor({
        ajax: {
            url: "inc/backend/editor_controllers/allTrucksEdit.php",
            type: 'POST',
            data: function(d) {

            }
        },
        table: "#js-dataTable-allTrucks",
        fields: [{
                label: 'Order:',
                name: 'trucks.row_order',
                fieldInfo: 'This field can only be edited via click and drag row reordering.',
                type: "hidden"
            }, {
                label: "Number Plate:",
                name: "trucks.number_plate",
                attr: {
                    maxlength: 49
                }
            },
            {
                label: "Brand:",
                name: "trucks.brand",
                attr: {
                    maxlength: 250
                }
            },
            {
                label: "Tare:",
                name: "trucks.tare"
            },
            {
                label: "Boom:",
                name: "trucks.boom"
            },
            {
                label: "Capacity:",
                name: "trucks.capacity"
            },
            {
                label: "Max Speed:",
                name: "trucks.max_speed"
            },
            {
                label: "Establishment Fee:",
                name: "trucks.est_fee"
            },
            {
                label: "Hourly Rate:",
                name: "trucks.hourly_rate"
            },
            {
                label: "Min:",
                name: "trucks.min"
            },
            {
                label: "Travel Rate:",
                name: "trucks.travel_rate_km"
            },
            {
                label: "Disposal Fee:",
                name: "trucks.disposal_fee"
            },
            {
                label: "Washdown Fee:",
                name: "trucks.washout"
            }
        ]
    });

    var trucksTable = $('#js-dataTable-allTrucks').DataTable({
        dom: "rt",
        ajax: "inc/backend/editor_controllers/allTrucksEdit.php",
        columns: [{
                data: null,
                defaultContent: '',
                className: 'control',
                orderable: false
            },
            {
                data: null,
                defaultContent: '',
                className: 'select-checkbox',
                orderable: false
            },
            {
                data: 'trucks.row_order',
                className: 'reorder',
                render: function(data, type, row) {
                    // If display or filter data is requested, output below html
                    if (type === 'display' || type === 'filter') {
                        return "<i class='fa fa-grip-lines'></i>";
                    }

                    // Otherwise the data type requested (`type`) is type detection or
                    // sorting data, for which we want to use the integer, so just return
                    // that, unaltered
                    return data;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    var truck_identification = row.trucks.number_plate + " " + row.trucks.brand;
                    return truck_identification;
                }
            },
            {
                data: "trucks.boom",
                render: function(data, type, row) {
                    return data + "m"
                }
            },
            {
                data: "trucks.capacity",
                render: function(data, type, row) {
                    return data + "m3"
                }
            },
            {
                data: "trucks.max_speed",
                render: function(data, type, row) {
                    return data + "km"
                }
            },
            {
                data: "trucks.est_fee",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            },
            {
                data: "trucks.hourly_rate",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            },
            {
                data: "trucks.min",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            },
            {
                data: "trucks.travel_rate_km",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            },
            {
                data: "trucks.disposal_fee",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            },
            {
                data: "trucks.washout",
                render: $.fn.dataTable.render.number(',', '.', 2, '$')
            }
        ],
        columnDefs: [
            { orderable: false, targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] }
        ],
        order: [
            [2, 'asc']
        ],
        select: {
            style: 'os',
            selector: 'td.select-checkbox'
        },
        rowReorder: {
            dataSrc: 'trucks.row_order',
            editor: trucksEditor,
            selector: "td:nth-child(3)"
        },
        autoWidth: !1,
        pageLength: -1,
        lengthMenu: [
            [5, 10, -1],
            [5, 10, 'All']
        ],
        buttons: [
            { extend: "create", editor: trucksEditor, className: "btn btn-sm btn-primary", formMessage: 'Enter your new truck\'s details' },
            { extend: "edit", editor: trucksEditor, className: "btn btn-sm btn-secondary" }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-none d-lg-block'l><'col-sm-12 col-md-6 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5 d-none d-lg-block'i><'col-sm-12 col-md-7 d-none d-lg-block'p>>"
    });

    trucksEditor.on('initCreate', function() {
            // Enable order for create
            trucksEditor.field('trucks.row_order').enable();
            trucksEditor.field('trucks.number_plate').enable();
            trucksEditor.field('trucks.boom').enable();
            trucksEditor.field('trucks.capacity').enable();
            trucksEditor.field('trucks.max_speed').enable();
            trucksEditor.field('trucks.est_fee').enable();

        })
        .on('initEdit', function() {
            // Disable for edit (re-ordering is performed by click and drag)
            trucksEditor.field('trucks.row_order').disable();
            trucksEditor.field('trucks.number_plate').disable();
            trucksEditor.field('trucks.boom').disable();
            trucksEditor.field('trucks.capacity').disable();
            trucksEditor.field('trucks.max_speed').disable();
            trucksEditor.field('trucks.est_fee').disable();

        });

    /* Refresh option on table */
    $('#refresh-trucks-table').on('click', function() {
        setTimeout(function() {
            trucksTable.ajax.reload();
        }, Math.floor((Math.random() * 500) + 500));
    });

    /* Enable exterior search box for table */
    $('#trucks-search-box').keyup(function() {
        trucksTable.search($(this).val()).draw();
    });
});