<?php

/**
 * This report shows various useful values and metrics of customer jobs for a given year
 */

require_once('inc/backend/fpdf/fpdf.php');
require_once("inc/backend/session.php");

class CustomerJobCountsForYear extends FPDF
{
    private $year;

    private $s_cell = 17.5;
    private $l_cell = 23;

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
        $this->Cell(58, 7, "Customer Jobs for Year Report", 0, 2, "R");
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

    function drawCustomerTableColumnHeaders()
    {
        // Set Titles
        $this->SetXY(2, 50);
        $this->SetFont('Arial', 'B', 9.5);
        $this->Cell($this->l_cell, 5, "Customer", 0, 0, "L");
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
    }    

    // Function to draw customer job counts out
    function drawPerCustomerJobCounts()
    {

        global $database;
        $y_adjustment = 0;

        $this->addCustomerJobCountPage();

        // Customer job counts for year and broken into months for chosen year
        $customer_job_counts = $database->getCustomerJobCountsForFinancialYear($this->year);
        foreach ($customer_job_counts as $customer_job_count) {
            $this->SetXY(2, 56 + $y_adjustment);
            $this->setFillColor(255, 255, 255);
            $this->CellFitScale($this->l_cell * 2, 5, html_entity_decode($customer_job_count['name']), 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['april_count'], 0, 0, "L", 1);
            $this->Cell($this->s_cell, 5, $customer_job_count['may_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['june_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['july_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['august_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['september_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['october_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['november_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['december_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['january_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['february_count'], 0, 0, "L");
            $this->Cell($this->s_cell, 5, $customer_job_count['march_count'], 0, 0, "L");
            $this->setFillColor(230, 230, 230);
            $this->SetFont('Arial', 'B', 8);
            $this->Cell($this->s_cell, 5, $customer_job_count['total_for_year'], 0, 0, "C", 1);
            $this->SetFont('Arial', '', 8);

            $y_adjustment += 5.5;

            // Condition to check if Y has been exceeded! Make new page
            if ($y_adjustment > 127) {
                $this->addCustomerJobCountPage();
                $y_adjustment = 0;
            }
        }
    }   

    // Add new customer numbers page
    function addCustomerJobCountPage()
    {
        $this->addNewPage();
        // Draw column headers for data rows
        $this->drawCustomerTableColumnHeaders();
        $this->SetXY(2, 56);
        $this->SetFont('Arial', '', 8);
    }
}

$year = intval($_POST["job-report-year"]);
$jobs_report_by_year = new CustomerJobCountsForYear($year);
$jobs_report_by_year->drawPerCustomerJobCounts();
$jobs_report_by_year->SetTitle('Customer Jobs ' . $year . '-' . ($year + 1));
$jobs_report_by_year->Output("I", "Jaxxon_Customer_Jobs_For_Year_Report.pdf");
