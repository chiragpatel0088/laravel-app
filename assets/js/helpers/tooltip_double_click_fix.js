//init bootstrap tooltips
$('[data-toggle="tooltip"]').tooltip({
    trigger: 'click'
});

//listen for the show event on any triggered elements
$('[data-toggle="tooltip"]').on('show.bs.tooltip', function() {

    //get a reference to the current element that is showing the tooltip
    var triggeredElement = $(this);

    //loop through all tooltips elements
    $('[data-toggle="tooltip"]').each(function() {

        //if they are not the currently triggered element and have a tooltip, 
        //trigger a click to close them
        if ($(this) !== triggeredElement && $(this).next().hasClass('tooltip')) {
            $(this).click();
        }
    })
});