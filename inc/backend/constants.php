<?php

/* Database connection credential constants */
require 'db_cred_constants.php';

/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */
define("TBL_USERS", "users");
define("TBL_ACTIVE_USERS",  "active_users");
define("TBL_ACTIVE_GUESTS", "active_guests");
define("TBL_BANNED_USERS",  "banned_users");
define("TBL_CONFIGURATION", "configuration");
define("TBL_USER_LEVELS", "user_levels");
// Application specific constants
define("TBL_CONCRETE_TYPES", "concrete_types");
define("TBL_CUSTOMERS", "customers");
define("TBL_JOBS", "jobs");
define("TBL_JOB_STATUSES", "job_statuses");
define("TBL_JOB_TYPES", "job_types");
define("TBL_LAYERS", "layers");
define("TBL_NOTIFICATIONS", "notifications");
define("TBL_QUOTES", "quotes");
define("TBL_SUPPLIERS", "suppliers");
define("TBL_TRUCKS", "trucks");
define("TBL_SITE_VISITS", "site_visits");
define("TBL_JOB_QUOTE_LINK", "job_quote_link");
define("TBL_INVOICE_DATA", "invoice_data");
define("TBL_JOB_CHANGE_LOGS", "job_change_logs");
define("TBL_FOREMEN", "foremen");
define("TBL_LINESMAN_JOBS", "linesman_jobs");

/**
 * Special Names and Level Constants - the admin
 * page will only be accessible to the user with
 * the admin name and also to those users at the
 * admin user level. Feel free to change the names
 * and level constants as you see fit, you may
 * also add additional level specifications.
 * Levels must be digits between 0-9.
 */
define("ADMIN_NAME", "admin");
define("GUEST_NAME", "Guest");
define("ADMIN_LEVEL", 3);
define("OFFICER_LEVEL", 2);
define("OPERATOR_LEVEL", 1);
define("ADMIN_ACT", 2);
define("ACT_EMAIL", 1);
define("GUEST_LEVEL", 0);

/**
 * Timeout Constants - these constants refer to
 * the maximum amount of time (in minutes) after
 * their last page fresh that a user and guest
 * are still considered active visitors.
 */
define("USER_TIMEOUT", 2);
define("GUEST_TIMEOUT", 5);
date_default_timezone_set('Pacific/Auckland');

/* Only level 4, 3 and 2 users */
const admin_user_levels = array(2, 3, 4);

/* Do not allow update statuses on the following status values */

/**
 * 8 = Job Complete
 * 6 = Job Assigned
 * 5 = Pending Site Inspection
 * 7 = Job Pending, just needs assigning
 * 1 = Job Pending, form is incomplete
 */

const no_status_update_array = [2, 8, 10, 11]; // Don't update job status if it's in this array

/* Field name translations */
const FIELDS = [
    'job_date' => 'Job Date',
    'job_timing' => 'Job Time',
    'job_addr_1' => 'Address 1',
    'job_addr_2' => 'Address 2',
    'job_suburb' => 'Suburb',
    'job_city' => 'City',
    'job_suburb' => 'Suburb',
    'job_city' => 'City',
    'job_post_code' => 'Post Code',
    'job_type' => 'Job Type',
    'cubics' => 'Cubics',
    'mpa' => 'MPa',
    'concrete_type' => "Concrete Type",
    'truck_id' => 'Truck',
    'customer_id' => 'Customer',
    'layer_id' => 'Layer',
    'supplier_id' => 'Supplier',
    'operator_id' => 'Operator',
    'job_instructions' => 'Job and OH&S Instructions',
    'ohs_instructions' => 'Admin Instructions',
    'actual_job_timing' => 'Actual Timing',
    'first_mixer_arrival_time' => 'Mixer Arrival Time',
    'actual_cubics' => 'Actual Cubics',
    'job_time_finished' => 'Time Finished',
    'onsite_washout' => 'Pump Washdown',
    'onsite_disposal' => 'Concrete Disposal',
    'operator_notes' => 'Operator Notes',
    'rate_type' => 'Rate Type',
    'establishment_fee' => 'Establishment Fee',
    'travel_fee' => 'Travel Fee',
    'actual_cubics' => 'Actual Cubics',
    'concrete_charge' => 'Concrete Charge',
    'truck_hourly_rate' => 'Truck Hourly Rate',
    'cubic_rate' => 'Cubic Rate',
    'hourly_rate' => 'Hourly Rate',
    'actual_job_hours' => 'Actual Hours',
    'washdown_fee' => 'Washdown Fee',
    'disposal_fee' => 'Concrete Disposal Fee',
    'special_rate' => 'Special Rate',
    'special_rate_1' => 'Extra Cost 1',
    'special_rate_2' => 'Extra Cost 2',
    'special_cost_description_1' => 'Extra Cost Description 1',
    'special_cost_description_2' => 'Extra Cost Description 2',
    'discount' => 'Discount',
    'invoice_number' => 'Invoice Number',
    'foreman_id' => 'Foreman',
    'linesman_actual_job_timing' => 'Linesman Actual Job Timing',
    'linesman_job_time_finished' => 'Linesman Job Time Finished',
    'line_size_select' => 'Line Size Select',
    'linesman_job_notes' => 'Linesman Job Notes'
];


/* GST Rate */
define("GST", 15);

/* Site inspection location */
define("UPLOAD_DIR", "site_inspection_photos/");
