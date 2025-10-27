<?php

header('Content-Type: application/json');
ini_set("session.cookie_httponly", 1);
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(array(
        "success" => false,
    ));

    exit;
}

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$source = $_SESSION['username'];
$dest = $json_obj['dest'];
$shareStatus = $json_obj['shareStatus'];
$token = $json_obj['token'];
if (!hash_equals($_SESSION['token'], $token)) {
    die("Request forgery detected");
    
}

require 'database.php';

// Check if dest user exists (Should always exist from frontend input button format)
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param('s', $dest);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
if ($count == 0) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}



if (!$shareStatus) {
    // Add share into database
    $stmt = $mysqli->prepare("INSERT INTO shares (source, dest) VALUES (?, ?)");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('ss', $source, $dest);
    $stmt->execute();
    $stmt->close();
} else {
    // Remove share from database
    $stmt = $mysqli->prepare("DELETE FROM shares WHERE source=? AND dest=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param('ss', $source, $dest);
    $stmt->execute();
    $stmt->close();
}
echo json_encode(array(
    "success" => true
));
exit();
?>