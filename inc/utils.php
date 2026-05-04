<?php

/**
 * check if all isSentToLinesman of inported linesman job = 1
 *
 * @param array $linesman_jobs_details
 * @return bool
 */
function areAllLinesmenSent($linesman_jobs_details)
{
    foreach ($linesman_jobs_details as $job) {
        if ($job['isSentToLinesman'] != 1) {
            return false;
        }
    }
    return true;
}
