<?php
# AJAX select2 script to get job types

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_POST['searchTerm'])) {
    $query = "SELECT * FROM " . TBL_JOB_TYPES . " ORDER BY id";
} else {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT * FROM " . TBL_JOB_TYPES . " WHERE type_name LIKE '%" . $searchTerm . "%'";
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

foreach ($result as $row) {
    $data[] = array("id" => $row['id'], "text" => html_entity_decode($row['type_name']));
}

echo json_encode($data);
