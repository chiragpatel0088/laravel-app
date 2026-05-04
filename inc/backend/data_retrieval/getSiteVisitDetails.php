<?php
# AJAX Get a site visit's details

include("../constants.php");

$job_id = $_POST['job_id'];

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM " . TBL_SITE_VISITS . " WHERE job_id = '$job_id'" . " LIMIT 1"; // Just extra assurance we get 1, if it's more than 1.. well... god save your soul
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

// This will only loop once with the LIMIT 1 clause in our query.
$data = array(
    "id" => $result['ID'], "completed" => $result['completed'],
    "notes" => $result['notes']
);

echo json_encode($data); // There should only be 1 result
