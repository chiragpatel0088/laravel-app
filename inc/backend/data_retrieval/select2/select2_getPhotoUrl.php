<?php

include("../../session.php");

global $database;

$photoUrl = $database->getPhotoUrl();
$status=$_POST['$status'];
$deleteFlag=true;
try {
    foreach ($photoUrl as $row) {
        // Access each photo URL in the $row array
        //$fileToDelete = realpath().$row['site_visit_photo'];
        $projectPath = __DIR__;
        $fileToDelete = $_SERVER['DOCUMENT_ROOT'].'/'.$row['site_visit_photo'];
        // Perform actions with $url as needed
        // Example: Print the URL
        error_log($fileToDelete);

        if (file_exists($fileToDelete)) {
                error_log($fileToDelete);
            if (unlink($fileToDelete)) {

            } else {
                error_log('fail');
            }
        } else {
            error_log(55555555555);
            continue;
        }
    }
}catch (Exception $e){
    Error($e->getMessage());
    $deleteFlag=false;
}

echo json_encode($deleteFlag, true);
