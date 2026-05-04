<?php
# AJAX select2 script to get operators from the users table

include("../../constants.php");

# MySQL with PDO_MYSQL
$connection = new PDO('mysql:host=' . DB_SERVER . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

# We exclude user level 3 as this is a strictly admin user only, not an operator
if (!isset($_POST['searchTerm'])) {
    $query = "SELECT * FROM " . TBL_USERS . "  WHERE user_level != '3' AND user_activated = '1' And user_linesman='1' ORDER BY user_firstname";
} else {
    $searchTerm = $_POST['searchTerm'];
    $query = "SELECT * FROM " . TBL_USERS . " WHERE (user_firstname LIKE '%" . $searchTerm . "%' OR user_lastname LIKE '%" . $searchTerm . "%') AND user_level != '3' AND user_activated = '1' And user_linesman='1'";
}

$data = array();

$stmt = $connection->prepare($query);
$stmt->execute();

$result = $stmt->fetchAll();

foreach ($result as $row) {
    $data[] = array("id" => $row['ID'], "text" => html_entity_decode($row['user_firstname'] . " " . $row['user_lastname']));
}

echo json_encode($data);
