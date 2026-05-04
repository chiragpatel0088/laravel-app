<?php
# AJAX select2 script to get foremen from the users table

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

# We exclude user level 3 as this is a strictly admin user only, not an operator
if (!isset($_POST['searchTerm'])) {
    $query = "SELECT id, CONCAT(first_name, ' ', last_name, '(', company, ')') AS text FROM " . TBL_FOREMEN . "  ORDER BY first_name";
} else {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT id, CONCAT(first_name, ' ', last_name, '(', company, ')') AS text FROM " . TBL_FOREMEN . " WHERE (first_name LIKE '%" . $searchTerm . "%' OR last_name LIKE '%" . $searchTerm . "%') ORDER BY first_name";
}

$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
