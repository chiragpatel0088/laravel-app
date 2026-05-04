<?php

/*
 * Jobs editor
 */

// DataTables PHP library
include("../editor/DataTables.php");
include("../job_status.php");

// Job status filter from client
$filterJs = isset($_POST['filter']) ? $_POST['filter'] : 0;

/**
 * 8 = Job Complete
 * 6 = Job Assigned
 * 5 = Pending Site Inspection
 * 7 = Job Pending, just needs assigning
 * 1 = Job Pending, form is incomplete
 */

switch ($filterJs) {
    case 1:
        $filter = '(1, 7)'; // Pending jobs
        break;
    case 2:
        $filter = '(5)'; // Site inspection required
        break;
    case 3:
        $filter = '(6)'; // Assigned jobs
        break;
    case 4:
        $filter = '(8)'; // Complete jobs
        break;
    case 5:
        $filter = '(10)'; // Ready for invoicing
        break;
    case 6:
        $filter = '(11)'; // Invoiced
        break;
    case 7:
        $filter = '(2)'; // Cancelled
        break;
        // case 8:
        //     $newFilter = '(11)'; // Latest 600 innoiced jobs
        //     break;
    default:
        $filter = '(1, 5, 6, 7)'; // Pending, assigned, site inspection required jobs
        break;
}

global $database;

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
$result = Editor::inst($db, 'jobs')
    ->fields(
        Field::inst('jobs.id')->set(false),
        Field::inst('jobs.quote_id'),
        Field::inst('jobs.status'),
        Field::inst('jobs.isLinesmanJob'),
        Field::inst('jobs.switchJobColor'),
        Field::inst('job_statuses.status_name')
            ->getFormatter(function ($val, $data, $opts) use ($database) { // Formatters here fix issue of resubmission of inline edit if nothing was changed
                if ($data['jobs.status'] == 11 && !is_null($data['jobs.invoiced_date'])) {
                    return $val . ": " . strtoupper($data['jobs.invoice_number']);
                } else if (($data['jobs.status'] == 1 || $data['jobs.status'] == 7) && $database->jobHasCompleteSiteInspections($data['jobs.id'])) {
                    return $val . ' <i class="fa fa-fw fa-check text-warning"></i>';
                }
                return $val;
            }),
        Field::inst('job_statuses.class_modifiers'),
        Field::inst('customers.name'),
        Field::inst('users.user_firstname'),
        Field::inst('users.user_lastname'),
        Field::inst('jobs.operator_id')
            ->options(
                Options::inst()
                    ->table('users')
                    ->value('ID')
                    ->label(array('user_firstname'))
                    ->where(function ($q) {
                        $q->where('users.user_activated', 1);
                        $q->where('users.user_level', 1)
                            ->or_where('users.user_level', 2)
                            ->or_where('users.user_level', 4);
                    })
            )
            ->validator(Validate::dbValues()),
        Field::inst('users.ID'),
        Field::inst('jobs.truck_id')
            ->options(
                Options::inst()
                    ->table('trucks')
                    ->value('id')
                    ->label('number_plate')
                    ->order('row_order ASC')
            )
            ->validator(Validate::dbValues()),
        Field::inst('trucks.id'),
        Field::inst('trucks.number_plate'),
        Field::inst('jobs.supplier_id'),
        Field::inst('suppliers.id'),
        Field::inst('suppliers.supplier_name'),
        Field::inst('suppliers.supplier_code'),
        Field::inst('jobs.sent_to_operator'),
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
        Field::inst('jobs.invoice_number'),
        Field::inst('jobs.invoiced_date'),
        Field::inst('job_quote_link.unified_id'),
        Field::inst('jobs.suitable_pumps')
            ->getValue(function () {}) // Don't get from the database to prevent an error
            ->getFormatter(function ($val, $data, $opts) use ($database) {
                if (in_array($data['jobs.status'], array(1, 5, 6, 7))) { // Only get suitable pumps for the statuses in the array
                    $pump_numbers = $database->getSuitablePumpsForJob($data['jobs.id']);
                    return $pump_numbers;
                } else return array();
            }),
        Field::inst('jobs.am_check'),
        Field::inst('jobs.pm_check'),
        Field::inst('jobs.passed_check')
    )
    ->on('preEdit', function ($editor, $id, $values) use ($database) {
        // Truck ID has changed, we need to update the job's truck details
        if (isset($values['jobs']['truck_id'])) {
            $database->updateJobOrQuoteTruckDetails(TBL_JOBS, $values['jobs']['truck_id'], $id);
        }
    })
    ->on('postEdit', function ($editor, $id, $values, $row) use ($database) {
        // Update the status of the job if it is a updatable status currently	
        if (!in_array($database->getJobStatus($id), no_status_update_array)) {
            // Update the job status if it isn't one of the forbidden ones already
            $site_inspections_complete = $database->isJobSiteInspectionsComplete($id);
            $database->setJobStatus($id, getUpdatedJobStatus($database->getJobDetails($id), $site_inspections_complete));
        }
    })
    ->leftJoin('users', 'users.ID', '=', 'jobs.operator_id')
    ->leftJoin('customers', 'customers.id', '=', 'jobs.customer_id')
    ->leftJoin('job_quote_link', 'job_quote_link.link_job_id', '=', 'jobs.id')
    ->leftJoin('job_statuses', 'jobs.status', '=', 'job_statuses.id')
    ->leftJoin('job_types', 'jobs.job_type', '=', 'job_types.id')
    ->leftJoin('trucks', 'trucks.id', '=', 'jobs.truck_id')
    ->leftJoin('suppliers', 'suppliers.id', '=', 'jobs.supplier_id')
    ->where(function ($q) use ($filter) {
        global $filterJs;
        if ($filterJs == 8) {
            $q->where('jobs.status', '(11)', '=', false);
            //Get the latest data in 3 months
            $q->where('jobs.invoiced_date', 'DATE_SUB(NOW(),INTERVAL 3 MONTH)', ">=", false);
            $q->order('jobs.id', 'DESC');
            // $q->order('invoiced_date', 'ASC');
            // $q->limit(2);
        } else {
            $q->where('jobs.status', $filter, 'IN', false);
        }
    })
    //Latest 600 invoiced job
    // ->where(function ($q) use ($filter) {
    //     if ($filter == 8) {
    //         $q->where('jobs.status', '(11)', 'IN', false);
    //         $q->limit(1);
    //     }
    // })
    ->process($_POST)
    // ->json();
    ->data();

//ignore any error so that datatable can render the tables
echo json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR);
