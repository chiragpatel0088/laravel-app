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
        Field::inst('job_quote_link.unified_id')
    )
    ->leftJoin('users', 'users.ID', '=', 'jobs.operator_id')
    ->leftJoin('customers', 'customers.id', '=', 'jobs.customer_id')
    ->leftJoin('job_quote_link', 'job_quote_link.link_job_id', '=', 'jobs.id')
    ->leftJoin('job_statuses', 'jobs.status', '=', 'job_statuses.id')
    ->leftJoin('job_types', 'jobs.job_type', '=', 'job_types.id')
    ->where('jobs.operator_id', $operator_id)
    ->where('jobs.sent_to_operator', '1', '=')
    ->where('jobs.status', '6', '=')
    ->where('jobs.isOperatorComplete', '0', '=')
    ->process($_POST)
    ->json();
