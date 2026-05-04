<?php
# AJAX select2 script to get single layer

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_POST['layer_id']) ) return;

$layer_id = $_POST['layer_id'];
$query = "SELECT * FROM " . TBL_LAYERS . " WHERE id = '$layer_id'";
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch();

/* Failed to get a layer */
if($result == null) return;

/* Return the layer option */
echo json_encode(array("id" => $result['id'], "text" => $result['layer_name']));
