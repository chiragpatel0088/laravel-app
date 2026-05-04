<?php

/*
 * Site inspection editor
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
Editor::inst($db, 'site_visits')
    ->fields(
        Field::inst('site_visits.id'),
        Field::inst('site_visits.site_visit_job_id'),
        Field::inst('site_visits.site_visit_completed'),
        Field::inst('customers.name'),
        Field::inst('jobs.status'),
        Field::inst('jobs.job_date'),
        Field::inst('jobs.job_timing'),
        Field::inst('jobs.job_addr_1'),
        Field::inst('jobs.job_addr_2'),
        Field::inst('jobs.job_suburb'),
        Field::inst('jobs.job_city'),
        Field::inst('jobs.job_post_code'),
        Field::inst('job_quote_link.unified_id')
    )
    ->leftJoin('jobs', 'jobs.id', '=', 'site_visits.site_visit_job_id')
    ->leftJoin('customers', 'jobs.customer_id', '=', 'customers.id')
    ->leftJoin('job_quote_link', 'job_quote_link.link_job_id', '=', 'site_visits.site_visit_job_id')
    ->where('site_visits.site_visit_completed', null)
    ->where('jobs.status', 2, '!=') // 2 is cancelled
    ->process($_POST)
    ->json();

    