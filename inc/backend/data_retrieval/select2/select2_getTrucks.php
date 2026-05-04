<?php
# AJAX select2 script to get trucks

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_POST['searchTerm'])) {
    $query = "SELECT * FROM " . TBL_TRUCKS . " ORDER BY row_order ASC";
} else {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT * FROM " . TBL_TRUCKS . " WHERE number_plate LIKE '%" . $searchTerm . "%' OR brand LIKE '%" . $searchTerm . "%' ORDER BY row_order ASC";
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

foreach ($result as $row) {
    $data[] = array("id" => $row['id'], "text" => $row['number_plate'] . " " . $row['brand']);
}

echo json_encode($data);
