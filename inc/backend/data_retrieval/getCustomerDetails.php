<?php
# AJAX Get a customer's details

include("../constants.php");

$customer_id = $_POST['customer_id'];

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM " . TBL_CUSTOMERS . " WHERE id = '$customer_id'" . " LIMIT 1"; // Just extra assurance we get 1, if it's more than 1.. well... god save your soul
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

// This will only loop once with the LIMIT 1 clause in our query.
$data = array(
    "id" => $result['id'], "name" => html_entity_decode($result['name']),
    "address_1" => $result['address_1'], "address_2" => $result['address_2'],
    "suburb" => $result['suburb'], "city" => $result['city'],
    "post_code" => $result['post_code'], "first_name" => $result['first_name'],
    "last_name" => $result['last_name'], "contact_ph" => $result['contact_ph'],
    "contact_mob" => $result['contact_mob'], "email" => $result['email'],
    "discount" => $result['discount']
);

echo json_encode($data); // There should only be 1 result
