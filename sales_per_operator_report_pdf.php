<?php

/**
 * Sales per operator report
 */

require_once('inc/backend/fpdf/fpdf.php');
require_once("inc/backend/session.php");

class OperatorSalesReportByYear extends FPDF
{
    private $year;

    private $s_cell = 17.5;
    private $l_cell = 20;
    private $total_turnover_for_year = 0;

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
        $this->Cell(58, 7, "Operator Sales Report", 0, 2, "R");
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

    // Function to draw customer job counts out
    function drawPerOperatorSales()
    {

        global $database;
        $y_adjustment = 0;

        $this->total_turnover_for_year = $database->getTotalTurnoverForFinancialYear($this->year);

        $this->addOperatorSalesPage();

        // Storage for per month sales totals
        $report_sub_totals = array_fill(0, 12, 0);

        // Get operator's monthly/annual sales data, calculate but not render
        $operators = $database->getOperators();
        $operator_turnover_arr = array();
        foreach ($operators as $operator) {
            $data_arr = array();

            // Operator id
            array_push($data_arr, $operator['id']);

            // Full name
            $name = html_entity_decode($operator['fullname']);
            array_push($data_arr, $name);

            $operator_total_turnover = 0;
            for ($i = 0; $i < 12; $i++) {
                $date = date('Y-m-d', strtotime($this->year . '-4-1' . "+" . $i . " months"));
                $monthly_turnover = $database->getJobTurnoverForMonthAndOperator($operator['id'], strtotime($date));
                $operator_total_turnover += $monthly_turnover;

                $report_sub_totals[$i] += $monthly_turnover;

                array_push($data_arr, $monthly_turnover);
            }

            array_push($data_arr, $operator_total_turnover);

            // Percentage of total turnover of financial year
            $percentage_of_total_turnover = $this->total_turnover_for_year != 0 ? number_format(($operator_total_turnover / $this->total_turnover_for_year) * 100, 2, '.', '') : '0.00';
            array_push($data_arr, $percentage_of_total_turnover);
            array_push($operator_turnover_arr, $data_arr);
        }

        // Sort the operator data by total sales desc
        usort($operator_turnover_arr, function ($a, $b) {
            return $b[14] - $a[14];
        });

        // Render all data calculated in previous loop
        foreach ($operator_turnover_arr as $operator_turnover_data) {
            $this->SetXY(2, 56 + $y_adjustment);
            $this->setFillColor(255, 255, 255);
            // Render name
            $this->Cell($this->l_cell * 2, 5, $operator_turnover_data[1], 0, 0, "L");

            for ($i = 0; $i < 12; $i++) {
                $monthly_turnover = $operator_turnover_data[$i + 2];
                // Render monthly sales figures
                $this->Cell($this->s_cell, 5, '$' . number_format($monthly_turnover, 0), 0, 0, "L", 1);
            }

            $this->setFillColor(230, 230, 230);
            $this->SetFont('Arial', 'B', 8);
            // Render total sales for year
            $this->Cell($this->s_cell, 5, '$' . number_format($operator_turnover_data[14], 0), 0, 0, "C", 1);
            $this->SetFont('Arial', '', 8);

            // Render percentage of sales for the year
            $this->Cell($this->s_cell, 5, $operator_turnover_data[15] . "%", 0, 0, "C");

            $y_adjustment += 5.5;

            // Condition to check if Y has been exceeded! Make new page
            if ($y_adjustment > 116) {
                $this->addOperatorSalesPage();
                $y_adjustment = 0;
            }
        }

        // Per month and total turnover
        $y_adjustment += 5.5;
        $this->SetXY(2, 56 + $y_adjustment);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($this->l_cell * 2, 5, "TOTAL SALES", 0, 0, "L");
        $this->SetFont('Arial', '', 8);
        foreach ($report_sub_totals as $total) {
            $this->Cell($this->s_cell, 5, '$' . number_format($total, 0), 0, 0, "L");
        }
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($this->s_cell, 5, '$' . number_format($this->total_turnover_for_year, 0), 0, 0, "C", 1);
        $this->SetFont('Arial', '', 8);
    }

    // Add page
    function addOperatorSalesPage()
    {
        $this->addNewPage();
        // Draw column headers for data rows
        $this->drawTableHeaders();
        $this->SetXY(2, 56);
        $this->SetFont('Arial', '', 8);
    }
}

$year = intval($_POST["job-report-year"]);
$operator_sales_report = new OperatorSalesReportByYear($year);
$operator_sales_report->drawPerOperatorSales();
$operator_sales_report->SetTitle('Operator Sales ' . $year . '-' . ($year + 1));
$operator_sales_report->Output("I", "Jaxxon_Operator_Sales_Report.pdf");
