<?php

/*
 * Job's site inspections editor
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

global $mailer;
global $database;
global $session;

// Build our Editor instance and process the data coming from _POST
Editor::inst($db, 'site_visits')
    ->fields(
        Field::inst('site_visits.id')->set(false),
        Field::inst('site_visits.site_visit_job_id'),
        Field::inst('site_visits.site_visit_completed_by')
            ->getFormatter(function ($val, $data, $opts) use ($database) {
                if (!is_null($val)) {
                    $operator_details = $database->getOperatorDetails($val);
                    return $operator_details['user_firstname'] . ' ' . $operator_details['user_lastname'];
                }
            }),
        Field::inst('site_visits.site_visit_completed')
            ->getFormatter(function ($val, $data, $opts) {
                if (!is_null($val))
                    return date('d/m/Y h:i A', strtotime($val));
            }),
        Field::inst('site_visits.site_visit_photo'),
        Field::inst('site_visits.site_visit_notes'),
        Field::inst('site_visits.site_visit_assigned_operator')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A operator is required')))
            ->options(
                Options::inst()
                    ->table('users')
                    ->value('users.ID')
                    ->label(['users.user_firstname', 'users.user_lastname'])
                    ->render(function ($row) {
                        return $row['users.user_firstname'] . " " . $row['users.user_lastname'];
                    })
                    ->where(function ($q) {
                        $q->where(function ($r) {
                            $r->where('users.user_level', 2);
                            $r->where('users.user_activated', 1);
                        })
                            ->or_where(
                                function ($r) {
                                    $r->where('users.user_level', 1);
                                    $r->where('users.user_activated', 1);
                                }
                            )
                            ->or_where(
                                function ($r) {
                                    $r->where('users.user_level', 4);
                                    $r->where('users.user_activated', 1);
                                }
                            ) 
                        ;
                    })
            )
            ->validator(Validate::dbValues()),
        Field::inst('site_visits.date_updated'),
        Field::inst('site_visits.date_created')
            ->getFormatter(function ($val, $data, $opts) {
                return date('d/m/Y', strtotime($val));
            }),
        Field::inst('customers.name'),
        Field::inst('users.ID'),
        Field::inst('users.user_firstname'),
        Field::inst('users.user_lastname'),
        Field::inst('jobs.id'),
        Field::inst('jobs.status'),
        Field::inst('jobs.job_date'),
        Field::inst('jobs.job_timing'),
        Field::inst('jobs.job_addr_1'),
        Field::inst('jobs.job_addr_2'),
        Field::inst('jobs.job_suburb'),
        Field::inst('jobs.job_city'),
        Field::inst('jobs.job_post_code'),
        Field::inst('job_quote_link.unified_id'),
        Field::inst('jobs.date_created')
            ->getFormatter(function ($val, $data, $opts) use ($database) {
                $pump_numbers = $database->getSuitablePumpsForJob($data['jobs.id']);
                return $pump_numbers;
            })
    )
    ->on('preCreate', function ($editor, $values) use ($job_id, $database) {
        // Check the job the site inspection is being added to is not already complete
        $job_details = $database->getJobDetails($job_id);
        if (!is_null($job_details['complete_date'])) throw new Exception("You cannot create a site inspection for a completed job");

        $editor->field('site_visits.site_visit_job_id')->setValue($job_id);
    })
    ->on('postCreate', function ($editor, $id, $values) use ($database, $job_id, $mailer, $session) {

        // Update the job status, it will always go to 5 here, but I put this here for consistency
        $site_inspections_complete = $database->isJobSiteInspectionsComplete($job_id);
        if (!in_array($database->getJobStatus($job_id), no_status_update_array)) {
            // Update the job status if it isn't one of the forbidden ones already
            $database->setJobStatus($job_id, getUpdatedJobStatus($database->getJobDetails($job_id), $site_inspections_complete));
        }

        $operator_id = $values['site_visits']['site_visit_assigned_operator'];
        // Notification for admins
        $job_details = $database->getJobDetails($job_id);
        $database->insertNewJobNotification(
            admin_user_levels,
            'site_inspection',
            $id,
            'fa fa-fw fa-eye text-info',
            "Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " site inspection was assigned"
        );

        // Notification for operator assigned
        if ($operator_id != $session->userinfo['ID']) {
            $operator_details = $database->getOperatorDetails($operator_id);
            $database->insertNewNotificationForOperator(
                $operator_details['ID'],
                'site_inspection',
                $id,
                'fa fa-fw fa-eye text-info',
                "Site Inspection JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to you"
            );
        }

        // Send email notification if it was not assigned to yourself
        if ($operator_id != $session->userinfo['ID'])
            $mailer->sendSiteInspection($job_id, $id, $operator_id);
    })
    ->on('preEdit', function ($editor, $id, $values) use ($database, $job_id) {
        $site_inspection_complete = $database->isSiteInspectionComplete($id);
        if ($site_inspection_complete) {
            throw new Exception("You cannot edit a complete site inspection!");
        }

        // Check operator has actually changed
        if (!$database->isSiteInspectionOperatorDifferent($id, $values['site_visits']['site_visit_assigned_operator']))
            throw new Exception("Same operator was assigned");
    })
    ->on('postEdit', function ($editor, $id, $values) use ($database, $job_id, $mailer, $session) {

        $operator_id = $values['site_visits']['site_visit_assigned_operator'];
        // Notification for admins
        $job_details = $database->getJobDetails($job_id);
        $database->insertNewJobNotification(
            admin_user_levels,
            'site_inspection',
            $id,
            'fa fa-fw fa-eye text-info',
            "Job JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " site inspection was reassigned"
        );

        if ($operator_id != $session->userinfo['ID']) {
            // Notification for operator assigned
            $operator_details = $database->getOperatorDetails($operator_id);
            $database->insertNewNotificationForOperator(
                $operator_details['ID'],
                'site_inspection',
                $id,
                'fa fa-fw fa-eye text-info',
                "Site Inspection JX-" . str_pad($job_details['unified_id'], 6, "0", STR_PAD_LEFT) . " was assigned to you"
            );
        }

        // Send email notification
        $mailer->sendSiteInspection($job_id, $id, $operator_id);
    })
    ->on('postRemove', function ($editor, $id, $values) use ($database, $job_id) {
        $site_inspections_complete = $database->isJobSiteInspectionsComplete($job_id);
        if (!in_array($database->getJobStatus($job_id), no_status_update_array)) {
            // Update the job status if it isn't one of the forbidden ones already
            $database->setJobStatus($job_id, getUpdatedJobStatus($database->getJobDetails($job_id), $site_inspections_complete));
        }
    })
    ->leftJoin('jobs', 'jobs.id', '=', 'site_visits.site_visit_job_id')
    ->leftJoin('users', 'users.ID', '=', 'site_visits.site_visit_assigned_operator')
    ->leftJoin('customers', 'jobs.customer_id', '=', 'customers.id')
    ->leftJoin('job_quote_link', 'job_quote_link.link_job_id', '=', 'site_visits.site_visit_job_id')
    ->where('site_visits.site_visit_job_id', $job_id)
    ->process($_POST)
    ->json();
