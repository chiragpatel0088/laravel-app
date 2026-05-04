<?php
# AJAX select2 script to get suppliers

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_POST['searchTerm'])) {
    $query = "SELECT * FROM " . TBL_SUPPLIERS . " ORDER BY supplier_name LIMIT 50";
} else {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT * FROM " . TBL_SUPPLIERS . " WHERE supplier_name LIKE '%" . $searchTerm . "%'";
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

foreach ($result as $row) {
    $data[] = array("id" => $row['id'], "text" => html_entity_decode($row['supplier_name']));
}

echo json_encode($data);
