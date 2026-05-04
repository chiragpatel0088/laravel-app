<?php
function getUpdatedJobStatus($job_details, $site_inspections_complete)
{

    $isOperatorComplete = $job_details['isOperatorComplete'];
    $job_assigned_to_operator = $job_details['sent_to_operator'] == 1;

    //2024-09-12 No need to consider has/have linesman/men completed the job or not
    //So commented the code below for now
    //Check if it is a linesman job
    // $isLinesmanJob = $job_details['isLinesmanJob'];
    // if ($isLinesmanJob == 1) {
    //     $isAllLinesmenComplete = !in_array(0, $job_details['isLinesmanComplete']);
    //     // if it is a linesman jobs and operator has completed the job too，return 8
    //     if ($isAllLinesmenComplete && $isOperatorComplete) {
    //         return 8; // Job Complete
    //     }
    //     // If not all linesman jobs are complete,
    //     //check if the job has been assigned to linesmam/men
    //     $linesman_assigned = !empty($job_details['linesman_user_ids']);
    //     $linesman_sent = isset($job_details['sentToLinesmen']) ? in_array(1, $job_details['sentToLinesmen']) : false;

    //     if ($linesman_assigned && $linesman_sent) {
    //         return 6; // Job Assigned
    //     }
    // } else {
    //     // If it is not a linesman jobs，only check has operator completed the job
    //     if ($isOperatorComplete && $job_assigned_to_operator) {
    //         return 8; // Job Complete
    //     }
    // }

    if ($isOperatorComplete && $job_assigned_to_operator) {
        return 8; // Job Complete
    }

    // Check the required fields are complete
    $fields_required_completed = mempty($job_details['customer_id'], $job_details['layer_id'], $job_details['supplier_id'], $job_details['operator_id'], $job_details['job_timing'], $job_details['job_type'], $job_details['cubics'], $job_details['concrete_type']);

    if (!$site_inspections_complete) {
        return 5; // Pending Site Inspection
    } else if (!$fields_required_completed) {
        return 1; // Job Pending, uncomplete form
    } else if ($job_assigned_to_operator) {
        return 6; // Job Assigned
    } else if (!$job_assigned_to_operator) {
        return 7; // Job Pending, but all complete and ready to assign
    }
}

/* Check if multiple variables are not null and empty. https://stackoverflow.com/questions/4993104/using-ifempty-with-multiple-variables-not-in-an-array */
function mempty()
{
    foreach (func_get_args() as $arg)
        if ($arg != '' && $arg != 'NULL')
            continue;
        else
            return false;
    return true;
}
