<?php
# AJAX Get a layer's details

include("../constants.php");

$layer_id = $_POST['layer_id'];

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM " . TBL_LAYERS . " WHERE id = '$layer_id'" . " LIMIT 1"; // Just extra assurance we get 1, if it's more than 1.. well... god save your soul
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

// This will only loop once with the LIMIT 1 clause in our query.
$data = array(
    "id" => $result['id'], "layer_name" => $result['layer_name'],
    "contact_ph" => $result['contact_ph'], "contact_mob" => $result['contact_mob'], 
    "email" => $result['email'], "layer_firstname" => $result['layer_firstname'],
    "layer_lastname" => $result['layer_lastname']
);

echo json_encode($data); // There should only be 1 result
