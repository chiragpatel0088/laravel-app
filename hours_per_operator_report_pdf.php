<?php

/**
 * Hours per operator report
 */

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once('inc/backend/fpdf/fpdf.php');
require_once("inc/backend/session.php");

class HoursPerOperatorReportByYear extends FPDF
{
    private $year;

    private $s_cell = 17.5;
    private $l_cell = 20;
    private $total_hours_for_year = 0;

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
        $this->Cell(58, 7, "Operator Hours Report", 0, 2, "R");
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

    function drawTableHeaders()
    {
        // Set Titles
        $this->SetXY(2, 50);
        $this->SetFont('Arial', 'B', 9.5);
        $this->Cell($this->l_cell, 5, "Operator", 0, 0, "L");
        $this->Cell($this->l_cell, 5, '', 0, 0, "L");
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
        $this->Cell($this->s_cell, 5, "%", 0, 0, "C");
    }

    // Function to draw per operator
    function drawPerOperatorHours()
    {

        global $database;
        $y_adjustment = 0;

        $this->total_hours_for_year = $database->getTotalHoursForFinancialYear($this->year);

        $this->addOperatorHoursPage();

        // Storage for per month hour totals
        $report_sub_totals = array_fill(0, 12, "0:00");

        // Get operator hours per month
        $hours_per_operator = $database->getOperatorHoursForFinancialYear($this->year);

        foreach ($hours_per_operator as $operator_hours) {
            $this->SetXY(2, 56 + $y_adjustment);
            $this->setFillColor(255, 255, 255);
            $this->Cell($this->l_cell * 2, 5, html_entity_decode($operator_hours['fullname']), 0, 0, "L");
            $this->Cell($this->s_cell, 5, $operator_hours['april_count'], 0, 0, "L", 1);
            $report_sub_totals[0] = sum_the_time($report_sub_totals[0], $operator_hours['april_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['may_count'], 0, 0, "L");
            $report_sub_totals[1] = sum_the_time($report_sub_totals[1], $operator_hours['may_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['june_count'], 0, 0, "L");
            $report_sub_totals[2] = sum_the_time($report_sub_totals[2], $operator_hours['june_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['july_count'], 0, 0, "L");
            $report_sub_totals[3] = sum_the_time($report_sub_totals[3], $operator_hours['july_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['august_count'], 0, 0, "L");
            $report_sub_totals[4] = sum_the_time($report_sub_totals[4], $operator_hours['august_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['september_count'], 0, 0, "L");
            $report_sub_totals[5] = sum_the_time($report_sub_totals[5], $operator_hours['september_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['october_count'], 0, 0, "L");
            $report_sub_totals[6] = sum_the_time($report_sub_totals[6], $operator_hours['october_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['november_count'], 0, 0, "L");
            $report_sub_totals[7] = sum_the_time($report_sub_totals[7], $operator_hours['november_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['december_count'], 0, 0, "L");
            $report_sub_totals[8] = sum_the_time($report_sub_totals[8], $operator_hours['december_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['january_count'], 0, 0, "L");
            $report_sub_totals[9] = sum_the_time($report_sub_totals[9], $operator_hours['january_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['february_count'], 0, 0, "L");
            $report_sub_totals[10] = sum_the_time($report_sub_totals[10], $operator_hours['february_count']);
            $this->Cell($this->s_cell, 5, $operator_hours['march_count'], 0, 0, "L");
            $report_sub_totals[11] = sum_the_time($report_sub_totals[11], $operator_hours['march_count']);
            $this->setFillColor(230, 230, 230);
            $this->SetFont('Arial', 'B', 8);
            $this->Cell($this->s_cell, 5, $operator_hours['total_for_year'], 0, 0, "C", 1);
            $this->SetFont('Arial', '', 8);

            // Percentage of hours of financial year
            $percentage_of_jobs = $this->total_hours_for_year != 0 ? number_format(($operator_hours['total_for_sorting'] / $this->total_hours_for_year) * 100, 2, '.', '') : '0.00';
            $this->Cell($this->s_cell, 5,    $percentage_of_jobs . "%", 0, 0, "C");

            $y_adjustment += 5.5;

            // Condition to check if Y has been exceeded! Make new page
            if ($y_adjustment > 116) {
                $this->addOperatorHoursPage();
                $y_adjustment = 0;
            }
        }

        // Per month and total hours
        $y_adjustment += 5.5;
        $this->SetXY(2, 56 + $y_adjustment);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($this->l_cell * 2, 5, "TOTAL HOURS", 0, 0, "L");
        $this->SetFont('Arial', '', 8);
        foreach ($report_sub_totals as $total) {
            $this->Cell($this->s_cell, 5, $total, 0, 0, "L");
        }
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($this->s_cell, 5, str_replace('.', ':', $this->total_hours_for_year), 0, 0, "C", 1);
        $this->SetFont('Arial', '', 8);
    }

    // Add page
    function addOperatorHoursPage()
    {
        $this->addNewPage();
        // Draw column headers for data rows
        $this->drawTableHeaders();
        $this->SetXY(2, 56);
        $this->SetFont('Arial', '', 8);
    }
}

$year = intval($_POST["job-report-year"]);
$operator_job_report_by_year = new HoursPerOperatorReportByYear($year);
$operator_job_report_by_year->drawPerOperatorHours();
$operator_job_report_by_year->SetTitle('Operator Hours ' . $year . '-' . ($year + 1));
$operator_job_report_by_year->Output("I", "Jaxxon_operator_Hours_Report.pdf");


function sum_the_time($time1, $time2)
{
    $times = array($time1, $time2);
    $seconds = 0;
    foreach ($times as $time) {
        list($hour, $minute) = explode(':', $time);
        $seconds += $hour * 3600;
        $seconds += $minute * 60;
    }
    $hours = floor($seconds / 3600);
    $seconds -= $hours * 3600;
    $minutes  = floor($seconds / 60);
    $seconds -= $minutes * 60;
    if ($seconds < 9) {
        $seconds = "0" . $seconds;
    }
    if ($minutes < 9) {
        $minutes = "0" . $minutes;
    }
    if ($hours < 9) {
        $hours = "0" . $hours;
    }
    return "{$hours}:{$minutes}";
}
