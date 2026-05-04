<?php
# AJAX Get a customer's details

include("../constants.php");

if (!isset($_POST['operator_id']) || !is_numeric($_POST['operator_id']))
    return false;

$operator_id = $_POST['operator_id'];

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM " . TBL_USERS . " WHERE ID = '$operator_id'" . " LIMIT 1"; // Just extra assurance we get 1, if it's more than 1.. well... god save your soul
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

// This will only loop once with the LIMIT 1 clause in our query.
$data = array(
    "id" => $result['ID'], "name" => $result['user_firstname'] . " " . $result['user_lastname'],
    "contact_ph" => $result['user_phone'],
    "email_address" => $result['user_email']
);

echo json_encode($data); // There should only be 1 result
