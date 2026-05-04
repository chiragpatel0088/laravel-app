<?php
# AJAX Get a supplier's details

include("../constants.php");

$supplier_id = $_POST['supplier_id'];

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM " . TBL_SUPPLIERS . " WHERE id = '$supplier_id'" . " LIMIT 1"; // Just extra assurance we get 1, if it's more than 1.. well... god save your soul
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

// This will only loop once with the LIMIT 1 clause in our query.
$data = array(
    "id" => $result['id'], "supplier_name" => $result['supplier_name'],
    "contact_ph" => $result['contact_ph'], "contact_mob" => $result['contact_mob'], 
    "email" => $result['email'], "supplier_firstname" => $result['supplier_firstname'],
    "supplier_lastname" => $result['supplier_lastname']
);

echo json_encode($data); // There should only be 1 result
