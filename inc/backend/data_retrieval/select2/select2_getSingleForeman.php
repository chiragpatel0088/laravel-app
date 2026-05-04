<?php
# AJAX select2 script to get single foreman

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_POST['foreman_id'])) {
    $foreman_id = $_POST['foreman_id'];
    $query = "SELECT id, CONCAT(first_name, ' ', last_name) AS text FROM " . TBL_FOREMEN . " WHERE id = '$foreman_id'";
} else {
    return;
}
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if($result == false) return '';

echo json_encode($result);
