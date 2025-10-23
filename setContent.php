<?php
header("Content-Type: application/json");
session_start();
if (isset($_SESSION['username'])) {
    // Get calendar Content
    
    
    echo json_encode(array(
        "success" => true,
        "username" => $_SESSION['username']
    ));
} else {
    echo json_encode(array(
        "success" => false
    ));
}



exit();
?>