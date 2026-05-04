<?php
# AJAX select2 script to get single operator type user

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['operator_id'])) {
    $operator_id = $_POST['operator_id'];
    $query = "SELECT * FROM " . TBL_USERS . " WHERE ID = '$operator_id'";
} else {
    return;
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

if(sizeof($result) == 0) return;

foreach ($result as $row) {
    $data[] = array("id" => $row['ID'], "text" => $row['user_firstname'] . " " . $row['user_lastname']);
}

echo json_encode($data[0]);
