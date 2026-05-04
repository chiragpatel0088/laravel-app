<?php

/*
 * Jobs editor
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

$operator_id = $_POST['operator_id'];

// Build our Editor instance and process the data coming from _POST
Editor::inst($db, 'jobs')
    ->fields(
        Field::inst('jobs.id')->set(false),
        Field::inst('jobs.quote_id'),
        Field::inst('jobs.status'),
        Field::inst('job_statuses.status_name'),
        Field::inst('job_statuses.class_modifiers'),
        Field::inst('customers.name'),
        Field::inst('users.user_firstname'),
        Field::inst('users.user_lastname'),
        Field::inst('jobs.operator_id'),
        Field::inst('jobs.number_plate'),
        Field::inst('jobs.job_date'),
        Field::inst('jobs.job_timing'),
        Field::inst('jobs.job_addr_1'),
        Field::inst('jobs.job_addr_2'),
        Field::inst('jobs.job_suburb'),
        Field::inst('jobs.job_city'),
        Field::inst('jobs.job_post_code'),
        Field::inst('jobs.cubics'),
        Field::inst('jobs.job_type'),
        Field::inst('job_types.type_name'),
        Field::inst('job_quote_link.unified_id'),
        Field::inst('linesman_jobs.id')
    )
    ->leftJoin('linesman_jobs', 'linesman_jobs.job_id', '=', 'jobs.id')
    ->leftJoin('users', 'users.ID', '=', 'linesman_jobs.user_id')
    ->leftJoin('customers', 'customers.id', '=', 'jobs.customer_id')
    ->leftJoin('job_quote_link', 'job_quote_link.link_job_id', '=', 'jobs.id')
    ->leftJoin('job_statuses', 'jobs.status', '=', 'job_statuses.id')
    ->leftJoin('job_types', 'jobs.job_type', '=', 'job_types.id')
    ->where('jobs.isLinesmanJob', '1', '=')
    ->where('linesman_jobs.user_id', $operator_id, '=')
    ->where('linesman_jobs.sentToLinesman', '1', '=')
    //no need to consider if isLinesmanComplete
    //if the job is complete, then do not show linesman job in the table for linesman
    // ->where('linesman_jobs.isLinesmanComplete', '0', '=')
    // ->where("jobs.isOperatorComplete", '0', '=')
    ->where("jobs.status", '6', '=')
    ->where('jobs.switchJobColor', '1', '=')
    // ->where(function ($q) {
    //     $q->where('jobs.status', '1', '=');
    //     //   ->or_where('jobs.status', '1', '=');
    // })
    ->process($_POST)
    ->json();
