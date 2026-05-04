<?php 

include("../../session.php");

global $database;

$pump_numbers = $database->getPumpNumbersOfSiteInspection($_POST['site_inspection_id']);

echo json_encode($pump_numbers, true);