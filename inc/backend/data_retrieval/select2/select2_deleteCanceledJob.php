<?php

include("../../session.php");

global $database;


$job_id=$_GET['job_id'];
$photoUrlArrayAll = $database->getPhotoUrlById($job_id);
$deleteFlag=true;
try {
    error_log("$job_id".$job_id);
    foreach($photoUrlArrayAll as $photoUrlArray){
        $photoUrl=$photoUrlArray['site_visit_photo'];
        $photosArray = explode(',', $photoUrl);
        foreach ($photosArray as $row) {
            if($row!=null||$row!=""){
                error_log("row".$row);
                // Access each photo URL in the $row array
                $projectPath = __DIR__;
                $fileToDelete = $_SERVER['DOCUMENT_ROOT'].'/'.$row;
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
        }
    }
    $deleteFlag=$database->deleteCanceledJob($job_id);

}catch (Exception $e){
    Error($e->getMessage());
    $deleteFlag=false;
}
echo json_encode($deleteFlag,true);
