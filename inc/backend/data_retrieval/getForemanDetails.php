<?php
# AJAX Get a foreman's details

include("../constants.php");

$foreman_id = $_POST['foreman_id'];

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM " . TBL_FOREMEN . " WHERE id = '$foreman_id'"; // Just extra assurance we get 1, if it's more than 1.. well... god save your soul
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($result);
