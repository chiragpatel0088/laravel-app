<?php
# AJAX select2 script to get single job type

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['customer_id'])) {
    $customer_id = $_POST['customer_id'];
    $query = "SELECT * FROM " . TBL_CUSTOMERS . " WHERE id = '$customer_id'";
} else {
    return;
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

foreach ($result as $row) {
    $data[] = array("id" => $row['id'], "text" => html_entity_decode($row['name']) . ", " . $row['first_name'] . " " . $row['last_name']);
}

echo json_encode($data[0]);
