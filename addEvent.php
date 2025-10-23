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

// Title must be alphanumeric and max 30 chars
if (!preg_match('/^[A-Za-z0-9 ]{0,30}$/', $newTitle) && $newTitle) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}

// Date must be in year-month-day format
if (!preg_match('/^[1-9]\d{1,}-[1-9]\d{0,1}-[1-9]\d{0,1}$/', $newDate) && $newDate) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}

// Time must be in hour:minute format
if (!preg_match('/^\d{1,2}:\d{2}$/', $newTime) && $newTime) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}
require 'database.php';

// Insert event into database
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