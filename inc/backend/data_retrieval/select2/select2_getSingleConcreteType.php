<?php
# AJAX select2 script to get concrete/mix type

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_POST['concrete_type_id'])) return;

$concrete_type_id = $_POST['concrete_type_id'];
$query = "SELECT * FROM " . TBL_CONCRETE_TYPES . " WHERE id = '$concrete_type_id'";
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

/* Failed to get a concrete type */
if($result == null) return;

/* Return the concrete/mix type option */
echo json_encode(array("id" => $result['id'], "text" => $result['concrete_name'], "charge" => $result['concrete_charge']));
