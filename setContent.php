<?php

// Indicate to js if user is logged in to handle setting content
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();
if (isset($_SESSION['username'])) {
    
    
    
    echo json_encode(array(
        "success" => true,
        "username" => $_SESSION['username'],
        "token" => $_SESSION['token']
    ));
} else {
    echo json_encode(array(
        "success" => false
    ));
}



exit();
?>