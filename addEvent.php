<?php
header("Content-Type: application/json");
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
$title = $json_obj['title'];
$date = $json_obj['date'];
$time = $json_obj['time'];
$token = $json_obj['token'];
if (!hash_equals($_SESSION['token'], $token)) {
    die("Request forgery detected");
    
}
$username = $_SESSION['username'];

// Title must be alphanumeric and max 30 chars
if (!preg_match('/^[A-Za-z0-9 ]{0,30}$/', $title) && $title) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}



// Date must be in year-month-day format
if (preg_match('/^[1-9]\d{1,}-([1-9]\d{0,1})-([1-9]\d{0,1})$/', $date, $matches) && $date) {
    $month = (int)$matches[1];
    $day = (int)$matches[2];

    if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
        echo json_encode(array(
            "success" => false,
        ));
        exit;
    }
} else if ($newDate) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}

// Time must be in hour:minute format
if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches) && $time) {
    $hour = (int)$matches[1];
    $minute = (int)$matches[2];
    if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
        echo json_encode(array(
            "success" => false,
        ));
        exit;
    }
}
else if ($newTime) {
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