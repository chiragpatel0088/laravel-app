<?php
# AJAX select2 script to get customers

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_POST['searchTerm'])) {
    $query = "SELECT * FROM " . TBL_CUSTOMERS . " ORDER BY name LIMIT 50";
} else {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT * FROM " . TBL_CUSTOMERS . " WHERE name LIKE '%" . $searchTerm . "%' OR first_name LIKE '%" . $searchTerm . "%' OR first_name LIKE '%" . $searchTerm . "%'";
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

foreach ($result as $row) {
    $data[] = array("id" => $row['id'], "text" => html_entity_decode($row['name'] . ", " . $row['first_name'] . " " . $row['last_name']));
}

echo json_encode($data);
