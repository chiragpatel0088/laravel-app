    /* Quote status function to calculate the status based on backend data passed to the function */
    var quoteStatus = function(data, type, row) {
        /* Quote's sent status is null, meaning the status is 'quote to be sent' */
        if (row.quotes.date_quote_sent == null && row.quotes.quote_accepted == null) {
            status_text = "Not Sent";
            popover_config = configurePopover(row.quotes, status_text);
            quote_status = createStatusPill("badge-secondary", "fa-exclamation-circle", status_text, popover_config);
        } else if (row.quotes.date_quote_sent !== null && row.quotes.quote_accepted == null) {
            /* Quote has no response yet */
            status_text = "Pending";
            popover_config = configurePopover(row.quotes, status_text);
            quote_status = createStatusPill("badge-warning", "fa-hourglass", status_text, popover_config);
        } else if (row.quotes.quote_accepted == '1') {
            /* Quote accepted */
            status_text = row.quotes.job_id == null ? "Accepted!" : "Job Created";
            popover_config = configurePopover(row.quotes, status_text);
            quote_status = createStatusPill("badge-success", "fa-check", status_text, popover_config)
        } else if (row.quotes.quote_accepted == '0') {
            /* Quote rejected */
            status_text = "Declined";
            popover_config = configurePopover(row.quotes, status_text);
            quote_status = createStatusPill("badge-danger", "fa-times-circle", status_text, popover_config);
        }

        return quote_status;
    };

    /* Quote status spruce up function */
    function createStatusPill(span_classes, icon_classes, text, popover_config) {
        pill = '<span ' + popover_config + ' class="badge badge-pill ' + span_classes + '">' +
            '<i class="fa fa-fw ' + icon_classes + '"></i> ' +
            text + '</span>';
        return pill;
    }

    /* Popovers for each status to provide additional info */
    function configurePopover(quote, status) {
        if (status == "Pending") {
            sent_datetime = quote.date_quote_sent;
            return 'data-toggle="popover" data-animation="true" data-html="true" data-placement="top" title="Quote Sent via System" data-content="<strong>Sent:</strong> ' + sent_datetime + '"';
        } else if (status == "Declined") {
            declined_datetime = quote.date_quote_response;
            if (quote.customer_decline_reason == null) {
                declined_who = "Declined by Admin";
            } else {
                declined_who = "Declined by Customer";
                reason = quote.customer_decline_reason;
                declined_datetime += "<br><strong>Reason:</strong> " + reason;
            }

            return 'data-toggle="popover" data-animation="true" data-html="true" data-placement="top" title="' + declined_who + '" data-content="<strong>Declined:</strong> ' + declined_datetime + '"';
        } else if (status == "Accepted" || status == "Job Created") {
            accepted_datetime = quote.date_quote_response;
            return 'data-toggle="popover" data-animation="true" data-html="true" data-placement="top" title="Quote Accepted" data-content="<strong>Accepted:</strong> ' + accepted_datetime + '"';
        } else return '';
    }