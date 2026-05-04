<?php
# AJAX select2 script to get single job type

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['supplier_id'])) {
    $supplier_id = $_POST['supplier_id'];
    $query = "SELECT * FROM " . TBL_SUPPLIERS . " WHERE id = '$supplier_id'";
} else {
    return;
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

if(sizeof($result) == 0) return;

foreach ($result as $row) {
    $data[] = array("id" => $row['id'], "text" => $row['supplier_name']);
}

echo json_encode($data[0]);
