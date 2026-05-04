<?php
# AJAX Get a truck's details

include("../constants.php");

$truck_id = $_POST['truck_id'];

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM " . TBL_TRUCKS . " WHERE id = '$truck_id'" . " LIMIT 1"; // Just extra assurance we get 1, if it's more than 1.. well... god save your soul

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetch();

// This will only loop once with the LIMIT 1 clause in our query.
$data = array(
    "id" => $result['id'], "number_plate" => $result['number_plate'],
    "brand" => $result['brand'], "tare" => $result['tare'],
    "boom" => $result['boom'], "capacity" => $result['capacity'],
    "max_speed" => $result['max_speed'], "est_fee" => $result['est_fee'],
    "hourly_rate" => $result['hourly_rate'], "min" => $result['min'],
    "travel_rate_km" => $result['travel_rate_km'], "washout" => $result['washout'],
    "disposal_fee" => $result['disposal_fee']
);

echo json_encode($data); // There should only be 1 result
