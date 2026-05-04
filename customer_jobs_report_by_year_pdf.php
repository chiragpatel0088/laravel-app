<?php

/**
 * This report shows various useful values and metrics of customer jobs for a given year
 */

require_once('inc/backend/fpdf/fpdf.php');
require_once("inc/backend/session.php");

class CustomerJobsReportByYear extends FPDF
{
    private $year;

    private $s_cell = 18.5;
    private $l_cell = 30;
    private $y_adjustment_between_rows = 10;

    public function __construct($year)
    {
        $this->year = $year;
        parent::__construct();
    }

    function header()
    {
        // Business logo or image
        $this->Image('assets/media/various/jaxxon_logo.png', 3, 3, 100, 35);
        $this->SetFont('Arial', '', 18);
        $this->SetXY(238, 7);
        $this->Cell(58, 7, "Customer Jobs Report", 0, 2, "R");
        $this->Cell(58, 7, "Year " . $this->year . '-' . ($this->year + 1), 0, 2, "R");
        $this->SetFont('Arial', '', 9);
        $this->SetXY(238, 25);
        $this->Cell(58, 4, "Jaxxon Concrete Pumps", 0, 2, "R");
        $this->Cell(58, 4, "30 Taitimu Street", 0, 2, "R");
        $this->Cell(58, 4, "Tauriko", 0, 2, "R");
        $this->Cell(58, 3, "Tauranga 3110", 0, 2, "R");
        $this->Cell(58, 4, "", 0, 2, "R");
        $this->Line(2, 45, 295, 45);
    }

    function footer()
    {
        $this->SetXY(3, -15);
        $this->SetFont('Arial', '', 8);
        $this->Cell(60, 10, "Jaxxon Concrete Pumps Report - Generated: ", 0, 0, "L");
        $this->Cell(100, 10, date("l jS F Y"), 0, 0, "L");
        $this->text(185, 288, 'Page ' . $this->PageNo() . " of {totalPages}");
    }

    function addNewPage()
    {
        $this->AddPage('L', 'A4');
        $this->header();
        $this->SetFont('Arial', '', 8);
    }

    function drawTableColumnHeaders()
    {

        // Previous 2 financial year text
        $year_1 = $this->year - 2; // 2nd to last financial year
        $year_2 = $this->year - 1; // Last financial year

        // Set Titles
        $this->SetXY(2, 50);
        $this->SetFont('Arial', 'B', 9.5);
        //$this->Cell($this->l_cell, 5, $year_1 . '/' .  $year_2, 0, 0, "L");
        $this->Cell($this->l_cell, 5, $year_2 . '/' .  $this->year, 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Apr", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "May", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Jun", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Jul", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Aug", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Sept", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Oct", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Nov", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Dec", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Jan", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Feb", 0, 0, "L");
        $this->Cell($this->s_cell, 5, "Mar", 0, 0, "L");
        $this->Cell($this->s_cell, 5, $this->year . '/' . ($this->year + 1), 0, 0, "L"); // Current financial year
    }


    function drawMainReportData()
    {
        global $database;
        $y_adjustment = 0;

        // Draw column headers for data rows
        $this->drawTableColumnHeaders();

        /* Output turnover data row */
        $this->drawRowHeader("Turnover", $y_adjustment);
        //$second_to_last_financial_year_turnover = $database->getTotalTurnoverForFinancialYear($this->year - 2);
        $last_financial_year_turnover = $database->getTotalTurnoverForFinancialYear($this->year - 1);
        //$this->CellFitScale($this->l_cell, 5, "$" . number_format($second_to_last_financial_year_turnover, 0), 0, 0, "L");
        $this->CellFitScale($this->l_cell, 5, "$" . number_format($last_financial_year_turnover, 0), 0, 0, "L");

        // Monthly breakdown of chosen fiscal year
        $total_chosen_financial_year_turnover = 0;
        $monthly_turnover_array = array();
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $monthly_turnover = $database->getJobTurnoverForMonth(strtotime($date));
            $total_chosen_financial_year_turnover += $monthly_turnover;
            array_push($monthly_turnover_array, $monthly_turnover);
            $this->Cell($this->s_cell, 5, "$" . number_format($monthly_turnover, 0), 0, 0, "L");
        }

        // Chosen fiscal year total
        $this->Cell($this->s_cell, 5, "$" . number_format($total_chosen_financial_year_turnover, 0), 0, 0, "L");
        /* END Output turnover data row */

        // Output total jobs data
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("TOTAL JOBS", $y_adjustment);
        //$second_to_last_financial_year_total_jobs = $database->getTotalJobsForFinancialYear($this->year - 2);
        $last_financial_year_total_jobs = $database->getTotalJobsForFinancialYear($this->year - 1);
        //$this->Cell($this->l_cell, 5, number_format($second_to_last_financial_year_total_jobs, 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($last_financial_year_total_jobs, 0), 0, 0, "L");

        $total_jobs_for_chosen_financial_year = 0;
        $monthly_jobs_array = array();
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $monthly_jobs = $database->getTotalJobsForMonth(strtotime($date));
            $total_jobs_for_chosen_financial_year += $monthly_jobs;
            array_push($monthly_jobs_array, $monthly_jobs);
            $this->Cell($this->s_cell, 5, number_format($monthly_jobs, 0), 0, 0, "L");
        }

        // Chosen fiscal year total job count
        $this->Cell($this->s_cell, 5, number_format($total_jobs_for_chosen_financial_year, 0), 0, 0, "L");

        // Output average turnover per job
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("Average Turnover Per Job", $y_adjustment);
        //$second_to_last_financial_year_average_turnover = $second_to_last_financial_year_total_jobs <= 0 ? 0 : $second_to_last_financial_year_turnover / $second_to_last_financial_year_total_jobs;
        $last_financial_year_average_turnover = $last_financial_year_total_jobs <= 0 ? 0 : $last_financial_year_turnover / $last_financial_year_total_jobs;
        //$this->Cell($this->l_cell, 5, "$" . number_format($second_to_last_financial_year_average_turnover, 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, "$" . number_format($last_financial_year_average_turnover, 0), 0, 0, "L");

        for ($i = 0; $i < sizeof($monthly_jobs_array); $i++) {
            $average_turnover_per_job_for_month = $monthly_jobs_array[$i] <= 0 ? 0 : $monthly_turnover_array[$i] / $monthly_jobs_array[$i];
            $this->Cell($this->s_cell, 5, "$" . number_format($average_turnover_per_job_for_month, 0), 0, 0, "L");
        }

        $average_turnover_per_job_for_year = $total_jobs_for_chosen_financial_year <= 0 ? 0 : $total_chosen_financial_year_turnover / $total_jobs_for_chosen_financial_year;
        $this->Cell($this->s_cell, 5, "$" . number_format($average_turnover_per_job_for_year, 0), 0, 0, "L");

        // Output Base Up % of total jobs
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("Base Up %age of jobs", $y_adjustment);

        // Base up jobs for last 2 financial years
        //$second_to_last_financial_year_baseup_proportion = $second_to_last_financial_year_total_jobs <= 0 ? 0 : $database->getTotalJobsOfClientForFinancialYear("BaseUp", $this->year - 2) / $second_to_last_financial_year_total_jobs;
        $last_financial_year_baseup_proportion = $last_financial_year_total_jobs <= 0 ? 0 : $database->getTotalJobsOfClientForFinancialYear("BaseUp", $this->year - 1) / $last_financial_year_total_jobs;

        //$this->Cell($this->l_cell, 5, number_format($second_to_last_financial_year_baseup_proportion * 100, 0) . "%", 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($last_financial_year_baseup_proportion * 100, 0) . "%", 0, 0, "L");

        $total_jobs_from_baseup_from_chosen_financial_year = 0;
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $monthly_total_jobs_from_baseup = $database->getTotalJobsOfClientForMonth('BaseUp', strtotime($date));
            $total_jobs_from_baseup_from_chosen_financial_year += $monthly_total_jobs_from_baseup;
            $proportion_of_monthly_jobs_from_baseup = $monthly_jobs_array[$i] <= 0 ? 0 : $monthly_total_jobs_from_baseup / $monthly_jobs_array[$i];
            $this->Cell($this->s_cell, 5, number_format($proportion_of_monthly_jobs_from_baseup * 100, 0) . "%", 0, 0, "L");
        }

        $portion_of_jobs_from_baseup_for_year = $total_jobs_for_chosen_financial_year <= 0 ? 0 : $total_jobs_from_baseup_from_chosen_financial_year / $total_jobs_for_chosen_financial_year;
        $this->Cell($this->s_cell, 5, number_format($portion_of_jobs_from_baseup_for_year * 100, 0) . "%", 0, 0, "L");


        // Output average jobs per day, working week only!
        /* TODO: Add option to calculate by Mon to Sat */
        $y_adjustment += $this->y_adjustment_between_rows;
        $weekday_jobs_for_year = $database->getTotalWeekdayJobsForFinancialYear($this->year);
        $weekday_jobs_for_last_year = $database->getTotalWeekdayJobsForFinancialYear($this->year - 1);
        //$weekday_jobs_for_2nd_last_year = $database->getTotalWeekdayJobsForFinancialYear($this->year - 2);
        $this->drawRowHeader("Average jobs per day (Weekdays only)", $y_adjustment);
        //$this->Cell($this->l_cell, 5, number_format($weekday_jobs_for_2nd_last_year / 250, 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($weekday_jobs_for_last_year / 250, 0), 0, 0, "L");

        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $average_jobs_per_day = $database->getTotalWeekdayJobsForMonth(strtotime($date)) / get_weekdays(date('m', strtotime($date)), date('Y', strtotime($date))); // Assuming 5 day weekend here, not including public holidays! - need alternate option to include saturday
            $this->Cell($this->s_cell, 5, number_format($average_jobs_per_day, 1), 0, 0, "L");
        }

        $this->Cell($this->s_cell, 5, number_format($weekday_jobs_for_year / 250, 0), 0, 0, "L"); // Abitrary number of 250 to make average (Source: https://newzealand.workingdays.org/workingdays_holidays_2021.htm)

        // Output average jobs per day, working week plus saturday
        $y_adjustment += $this->y_adjustment_between_rows;
        $weekday_plus_sat_jobs_for_year = $database->getTotalWeekdayIncludingSatJobsForFinancialYear($this->year);
        $weekday_plus_sat_jobs_for_last_year = $database->getTotalWeekdayIncludingSatJobsForFinancialYear($this->year - 1);
        //$weekday_plus_sat_jobs_for_2nd_last_year = $database->getTotalWeekdayIncludingSatJobsForFinancialYear($this->year - 2);
        $this->drawRowHeader("Average jobs per day (Weekdays and Saturdays)", $y_adjustment);
        //$this->Cell($this->l_cell, 5, number_format($weekday_plus_sat_jobs_for_2nd_last_year / 302, 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($weekday_plus_sat_jobs_for_last_year / 302, 0), 0, 0, "L");

        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $average_jobs_per_day = $database->getTotalWeekdayIncludingSatJobsForMonth(strtotime($date)) / get_weekdays_plus_saturdays(date('m', strtotime($date)), date('Y', strtotime($date))); // Weekdays + saturdays for a month   
            $this->Cell($this->s_cell, 5, number_format($average_jobs_per_day, 1), 0, 0, "L");
        }

        $this->Cell($this->s_cell, 5, number_format($weekday_plus_sat_jobs_for_year / 302, 0), 0, 0, "L");

        // Output total customers
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("TOTAL CUSTOMERS", $y_adjustment);
        //$this->Cell($this->l_cell, 5, number_format($database->getTotalCustomersAddedByFinancialYear($this->year - 2), 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($database->getTotalCustomersAddedByFinancialYear($this->year - 1), 0), 0, 0, "L");

        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $this->Cell($this->s_cell, 5, number_format($database->getTotalCustomersAddedByDate(strtotime($date)), 0), 0, 0, "L");
        }

        // Total customers for the selected financial year
        $this->Cell($this->s_cell, 5, number_format($database->getTotalCustomersAddedByFinancialYear($this->year), 0), 0, 0, "L");

        // Total customers sold to YTD
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("Total Customers sold to YTD", $y_adjustment);
        //$this->Cell($this->l_cell, 5, number_format($database->getTotalUniqueCustomersSoldToForYear($this->year - 2), 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($database->getTotalUniqueCustomersSoldToForYear($this->year - 1), 0), 0, 0, "L");

        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $this->Cell($this->s_cell, 5, number_format($database->getTotalUniqueCustomersSoldToForMonthYTD($this->year, strtotime($date)), 0), 0, 0, "L");
        }

        $this->Cell($this->s_cell, 5, number_format($database->getTotalUniqueCustomersSoldToForYear($this->year), 0), 0, 0, "L");

        // New customers
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("New Customers", $y_adjustment);
        //$this->Cell($this->l_cell, 5, $database->getTotalNewCustomersForFinancialYear($this->year - 2), 0, 0, "L");
        $this->Cell($this->l_cell, 5, $database->getTotalNewCustomersForFinancialYear($this->year - 1), 0, 0, "L");
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $this->Cell($this->s_cell, 5, number_format($database->getTotalNewCustomersForMonth(strtotime($date)), 0), 0, 0, "L");
        }
        $this->Cell($this->s_cell, 5, number_format($database->getTotalNewCustomersForFinancialYear($this->year), 0), 0, 0, "L");

        // Customers not yet sold to
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("Customers not sold to yet", $y_adjustment);
        //$this->Cell($this->l_cell, 5, number_format($database->getTotalCustomersWithNoJobsForFinancialYear($this->year - 2), 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($database->getTotalCustomersWithNoJobsForFinancialYear($this->year - 1), 0), 0, 0, "L");
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $this->Cell($this->s_cell, 5, number_format($database->getTotalCustomersWithNoJobsForMonth($this->year, strtotime($date)), 0), 0, 0, "L");
        }
        $this->Cell($this->s_cell, 5, number_format($database->getTotalCustomersWithNoJobsForFinancialYear($this->year), 0), 0, 0, "L");

        // Customers added from the last year with 4 or more jobs not sold to yet
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("Previous year customers with 4 or more jobs not sold to yet", $y_adjustment);
        $customers_with_more_than_four_jobs_previous_year = $database->getCustomersWithMoreThanFourJobsForFinancialYear($this->year - 1); // Previous year customers > 4 jobs
        $customers_with_more_than_four_jobs_previous_two_year = $database->getCustomersWithMoreThanFourJobsForFinancialYear($this->year - 2); // Previous year customers > 4 jobs
        //$customers_with_more_than_four_jobs_previous_three_years = $database->getCustomersWithMoreThanFourJobsForFinancialYear($this->year - 3);
        //$this->Cell($this->l_cell, 5, number_format($this->getCustomersThatHaveFourJobsLastYearButNoneForNext($customers_with_more_than_four_jobs_previous_three_years, $this->year - 2), 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($this->getCustomersThatHaveFourJobsLastYearButNoneForNext($customers_with_more_than_four_jobs_previous_two_year, $this->year - 1), 0), 0, 0, "L");
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $this->Cell($this->s_cell, 5, number_format($this->getCustomersThatHaveJobsForDate($customers_with_more_than_four_jobs_previous_year, strtotime($date)), 0), 0, 0, "L");
        }
        $this->Cell($this->s_cell, 5, number_format($this->getCustomersThatHaveFourJobsLastYearButNoneForNext($customers_with_more_than_four_jobs_previous_year, $this->year), 0), 0, 0, "L");

        // Customers with 4 or more jobs in the selected year
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("Customers with 4 or more jobs", $y_adjustment);
        //$this->Cell($this->l_cell, 5, number_format($database->getTotalCustomersWithMoreThanFourJobsForFinancialYear($this->year - 2), 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($database->getTotalCustomersWithMoreThanFourJobsForFinancialYear($this->year - 1), 0), 0, 0, "L");
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $this->Cell($this->s_cell, 5, number_format($database->getTotalCustomersWithMoreThanFourJobsForMonth($this->year, strtotime($date)), 0), 0, 0, "L");
        }
        $this->Cell($this->s_cell, 5, number_format($database->getTotalCustomersWithMoreThanFourJobsForFinancialYear($this->year), 0), 0, 0, "L");


        // 80% jobs to total customers YTD
        $y_adjustment += $this->y_adjustment_between_rows;
        $this->drawRowHeader("80% Jobs to total customers", $y_adjustment);
        //$this->Cell($this->l_cell, 5, number_format($this->getCustomersFromTop80PercentOfJobs($this->year - 2), 0), 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($this->getCustomersFromTop80PercentOfJobs($this->year - 1), 0), 0, 0, "L");
        $this->Cell($this->s_cell * 12, 5, '', 0, 0, "L");
        $this->Cell($this->l_cell, 5, number_format($this->getCustomersFromTop80PercentOfJobs($this->year), 0), 0, 0, "L");

        // 80% jobs to total customers all time
        $y_adjustment += 8;
        $this->drawRowHeader("80% Jobs to total customers for all time: " . number_format($this->getCustomersFromTop80PercentOfJobsForAllTime(), 0), $y_adjustment);
    }

    function drawRowHeader($header_text, $y)
    {
        $this->SetXY(2, 55 + $y);
        $this->SetFont('Arial', 'B', 9.5);
        $this->Cell($this->l_cell, 5, $header_text, 0, 0, "L");
        $this->SetXY(2, 59 + $y);
        $this->SetFont('Arial', '', 8.5);
    }

    function getCustomersThatHaveJobsForDate($customers, $date)
    {
        global $database;
        $temp_customers = $customers;
        foreach ($customers as $key => $customer) {
            if ($database->checkCustomerHasJobsForMonth($customer['customer_id'], $date, $this->year)) {
                unset($temp_customers[$key]);
            }
        }
        return count($temp_customers);
    }

    // Customers array should be array of customers of 4 jobs or more previous year
    function getCustomersThatHaveFourJobsLastYearButNoneForNext($customers, $year)
    {
        global $database;
        $temp_customers = $customers;
        foreach ($customers as $key => $customer) {
            if ($database->checkCustomerHasJobsForFinancialYear($customer['customer_id'], $year)) {
                unset($temp_customers[$key]);
            }
        }
        return count($temp_customers);
    }   

    // Get first 80% of job customers
    function getCustomersFromTop80PercentOfJobs($year)
    {
        global $database;

        $total_jobs_for_year_80_percent = $database->getTotalJobsForFinancialYear($year) * 0.8; // Get 80% threshold for job count
        $customer_job_counts_per_customer = $database->getCustomerJobCountForFinancialYear($year);
        $temp_count = 0;
        $top_80_percent_customers = array();
        foreach ($customer_job_counts_per_customer as $customer_count) {
            $temp_count += $customer_count['count'];
            array_push($top_80_percent_customers, $customer_count['customer_id']);
            if ($temp_count >= $total_jobs_for_year_80_percent) {
                break;
            }
        }

        return count($top_80_percent_customers);
    }

    // Get first 80% of customers for all time
    function getCustomersFromTop80PercentOfJobsForAllTime()
    {
        global $database;
        $total_jobs_80_percent = $database->getTotalInvoicedJobsForAllTime() * 0.8;
        $customer_job_counts = $database->getAllCustomersJobCount();
        $temp_count = 0;
        $top_80_percent_customers = array();
        foreach ($customer_job_counts as $customer_count) {
            $temp_count += $customer_count['count'];
            array_push($top_80_percent_customers, $customer_count['customer_id']);
            if ($temp_count >= $total_jobs_80_percent) {
                break;
            }
        }

        return count($top_80_percent_customers);
    }

}

$year = intval($_POST["job-report-year"]);
$jobs_report_by_year = new CustomerJobsReportByYear($year);
$jobs_report_by_year->addNewPage();
$jobs_report_by_year->drawMainReportData();
$jobs_report_by_year->SetTitle('Customer Jobs ' . $year . '-' . ($year + 1));
$jobs_report_by_year->Output("I", "Jaxxon_Customer_Jobs_Report.pdf");


function get_weekdays($m, $y)
{
    $lastday = date("t", mktime(0, 0, 0, $m, 1, $y));
    $weekdays = 0;
    for ($d = 29; $d <= $lastday; $d++) {
        $wd = date("w", mktime(0, 0, 0, $m, $d, $y));
        if ($wd > 0 && $wd < 6) $weekdays++;
    }
    return $weekdays + 20;
}

function get_weekdays_plus_saturdays($m, $y)
{
    $lastday = date("t", mktime(0, 0, 0, $m, 1, $y));
    $weekdays = 0;
    for ($d = 29; $d <= $lastday; $d++) {
        $wd = date("w", mktime(0, 0, 0, $m, $d, $y));
        if ($wd > 0 && $wd <= 6) $weekdays++;
    }
    return $weekdays + 24;
}
