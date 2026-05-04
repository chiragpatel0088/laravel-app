<?php

/*
 * Job edit history editor
 */

// DataTables PHP library
include("../editor/DataTables.php");
require '../job_status.php';

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

$job_id = $_POST['job_id'];
$mode = $_POST['history_type']; // Show which type of history

global $mailer;
global $database;
global $session;

// Build our Editor instance and process the data coming from _POST
Editor::inst($db, 'job_change_logs')
    ->fields(
        Field::inst('job_change_logs.id')->set(false),
        Field::inst('job_change_logs.field_changed')
            ->getFormatter(function ($val, $data, $opts) {
                $human_name = array_key_exists($val, FIELDS) ? FIELDS[$val] : 'Unknown Field';
                return $human_name;
            }),
        Field::inst('job_change_logs.message')
            ->getFormatter(function ($val, $data, $opts) {
                if (is_null($val) || empty($val)) {
                    return nl2br($data['job_change_logs.previous_value'] . " -> " . $data['job_change_logs.new_value']);
                } else return nl2br($val);
            }),
        Field::inst('job_change_logs.user_id'),
        Field::inst('users.user_firstname'),
        Field::inst('users.user_lastname'),
        Field::inst('job_change_logs.date_changed'),
        Field::inst('job_change_logs.reason_changed'),
        Field::inst('job_change_logs.previous_value'),
        Field::inst('job_change_logs.new_value'),
        Field::inst('job_change_logs.status_during_edit'),
        Field::inst('job_statuses.status_name'),
        Field::inst('job_statuses.class_modifiers')
    )
    ->leftJoin('users', 'users.ID', '=', 'job_change_logs.user_id')
    ->leftJoin('job_statuses', 'job_statuses.id', '=', 'job_change_logs.status_during_edit')
    ->where(function ($q) use ($job_id, $mode) {
        $q->where('job_change_logs.job_id', $job_id);
        if ($mode == 0) {
            $q->where(function ($r) {
                $r->where('job_change_logs.status_during_edit', 11);
                $r->or_where('job_change_logs.status_during_edit', 10);
                $r->or_where('job_change_logs.status_during_edit', 8);
            });
        }
    })
    ->process($_POST)
    ->json();
