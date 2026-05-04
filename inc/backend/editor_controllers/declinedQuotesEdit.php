<?php

/*
 * Quotes editor
 */

// DataTables PHP library
include("../editor/DataTables.php");

// Alias Editor classes so they are easy to use
use
    DataTables\Editor,
    DataTables\Editor\Field,
    DataTables\Editor\Format,
    DataTables\Editor\Mjoin,
    DataTables\Editor\Options,
    DataTables\Editor\Upload,
    DataTables\Editor\Validate,
    DataTables\Editor\ValidateOptions;

// Build our Editor instance and process the data coming from _POST
Editor::inst($db, 'quotes')
    ->fields(
        Field::inst('quotes.id')->set(false),
        Field::inst('quotes.job_id'),
        Field::inst('customers.name'),
        Field::inst('quotes.date_quote_sent'),
        Field::inst('quotes.quote_accepted'),
        Field::inst('quotes.date_quote_response'),
        Field::inst('quotes.job_addr_1'),
        Field::inst('quotes.job_addr_2'),
        Field::inst('quotes.job_suburb'),
        Field::inst('quotes.job_city'),
        Field::inst('quotes.job_post_code'),
        Field::inst('quotes.job_type'),
        Field::inst('quotes.customer_decline_reason'),
        Field::inst('job_quote_link.unified_id')
    )
    ->leftJoin('customers', 'customers.id', '=', 'quotes.customer_id')
    ->leftJoin('job_quote_link', 'job_quote_link.quote_id', '=', 'quotes.id')
    ->where( function ($q) { 
        $q->where('quotes.date_quote_sent', null, '!=');
        $q->and_where('quotes.quote_accepted', 0);
    })
    ->process($_POST)
    ->json();