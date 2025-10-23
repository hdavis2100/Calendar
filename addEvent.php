<?php
header("Content-Type: application/json");
session_start();
if (!isset($_SESSION['username'])) {
    
    exit;
}
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$title = $json_obj['title'];
$date = $json_obj['date'];
$time = $json_obj['time'];
$username = $_SESSION['username'];
if (!preg_match('/^[A-Za-z0-9 ]{0,30}$/', $title)) {
    exit;
}

if (!preg_match('/^\d{1,}-\d{1,}-\d{1,}$/', $date)) {
    exit;
}
if (!preg_match('/^\d{1,2}:\d{2}$/', $time)) {
    exit;
}
require 'database.php';
$stmt = $mysqli->prepare("INSERT INTO events (username, title, date, time) VALUES (?, ?, ?, ?)");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param('ssss', $username, $title, $date, $time);
$stmt->execute();
$stmt->close();
echo json_encode(array(
    "success" => true
));

exit();
?>