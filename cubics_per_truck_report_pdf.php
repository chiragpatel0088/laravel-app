<?php

/**
 * This report shows job count per truck
 */

require_once('inc/backend/fpdf/fpdf.php');
require_once("inc/backend/session.php");

class TruckJobsReportByYear extends FPDF
{
    private $year;

    private $s_cell = 17.5;
    private $l_cell = 20;
    private $total_cubics_for_year = 0;

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
        $this->Cell(58, 7, "Cubics Per Truck Report", 0, 2, "R");
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

    function drawTruckJobsTableHeaders()
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

    function drawRowHeader($header_text, $y)
    {
        $this->SetXY(2, 55 + $y);
        $this->SetFont('Arial', 'B', 9.5);
        $this->Cell($this->l_cell, 5, $header_text, 0, 0, "L");
        $this->SetXY(2, 59 + $y);
        $this->SetFont('Arial', '', 8.5);
    }

    // Function to draw customer job counts out
    function drawPerTruckCubics()
    {

        global $database;
        $y_adjustment = 0;

        $this->total_cubics_for_year = $database->getTotalCubicsPerTruckForFinancialYear($this->year);

        $this->addTruckCubicsPage();

        // Customer job counts for year and broken into months for chosen year
        $truck_cubic_totals = $database->getTotalTruckCubicsForEachMonth($this->year);
   
        foreach ($truck_cubic_totals as $truck_cubics) {
            $this->SetXY(2, 56 + $y_adjustment);
            $this->setFillColor(255, 255, 255);
            $this->Cell($this->l_cell * 2, 5, html_entity_decode($truck_cubics['number_plate']), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['april_count'], 2, '.', ''), 0, 0, "L", 1);
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['may_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['june_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['july_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['august_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['september_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['october_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['november_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['december_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['january_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['february_count'], 2, '.', ''), 0, 0, "L");
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['march_count'], 2, '.', ''), 0, 0, "L");
            $this->setFillColor(230, 230, 230);
            $this->SetFont('Arial', 'B', 8);
            $this->Cell($this->s_cell, 5, number_format($truck_cubics['total_for_year'], 2, '.', ''), 0, 0, "C", 1);
            $this->SetFont('Arial', '', 8);
            // Percentage of jobs of financial year
            $percentage_of_jobs = $this->total_cubics_for_year != 0 ? number_format(($truck_cubics['total_for_year'] / $this->total_cubics_for_year) * 100, 2, '.', '') : '0.00';
            $this->Cell($this->s_cell, 5,    $percentage_of_jobs . "%", 0, 0, "C");

            $y_adjustment += 5.5;

            // Condition to check if Y has been exceeded! Make new page
            if ($y_adjustment > 116) {
                $this->addTruckCubicsPage();
                $y_adjustment = 0;
            }
        }

     
        // Per month job count
        $y_adjustment += 5.5;
        $this->SetXY(2, 56 + $y_adjustment);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($this->l_cell * 2, 5, "TOTAL CUBICS", 0, 0, "L");
        $this->SetFont('Arial', '', 8);
        $total_cubics_for_financial_year = 0;
        $monthly_cubics_array = array();
        for ($i = 0; $i < 12; $i++) {
            $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
            $monthly_cubics = $database->getTotalTruckCubicsForMonth(strtotime($date));
            $total_cubics_for_financial_year += $monthly_cubics;
            array_push($monthly_cubics_array, $monthly_cubics);
            $this->Cell($this->s_cell, 5, number_format($monthly_cubics, 0), 0, 0, "L");
        }
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($this->s_cell, 5, number_format($this->total_cubics_for_year, 2, '.', ''), 0, 0, "C", 1);
        $this->SetFont('Arial', '', 8);
    }

    // Add page
    function addTruckCubicsPage()
    {
        $this->addNewPage();
        // Draw column headers for data rows
        $this->drawTruckJobsTableHeaders();
        $this->SetXY(2, 56);
        $this->SetFont('Arial', '', 8);
    }
}

$year = intval($_POST["job-report-year"]);
$operator_job_report_by_year = new TruckJobsReportByYear($year);
$operator_job_report_by_year->drawPerTruckCubics();
$operator_job_report_by_year->SetTitle('Truck Cubics ' . $year . '-' . ($year + 1));
$operator_job_report_by_year->Output("I", "Jaxxon_Truck_Jobs_Report.pdf");
