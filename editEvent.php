<?php

header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(array(
        "success" => false,
    ));

    exit;
}
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$eventId = $json_obj['id'];
$newTitle = $json_obj['newTitle'];
$newDate = $json_obj['newDate'];
$newTime = $json_obj['newTime'];

$username = $_SESSION['username'];



// Title must be alphanumeric and max 30 chars
if (!preg_match('/^[A-Za-z0-9 ]{0,30}$/', $newTitle) && $newTitle) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}



// Date must be in year-month-day format
if (preg_match('/^[1-9]\d{1,}-([1-9]\d{0,1})-([1-9]\d{0,1})$/', $newDate, $matches)) {
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
if (preg_match('/^(\d{1,2}):(\d{2})$/', $newTime, $matches) && $newTime) {
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

// Grab current event details to fill blank fields
$stmt = $mysqli->prepare("SELECT title, date, time FROM events WHERE event_id=? AND username=?");

if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("is", $eventId, $username);
$stmt->execute();
$stmt->bind_result($currentTitle, $currentDate, $currentTime);
$stmt->fetch();
$stmt->close();

// Fill blank fields
if (!$newTitle) {
    $newTitle = $currentTitle;
}

if (!$newDate) {
    $newDate = $currentDate;
}

if (!$newTime) {
    $newTime = $currentTime;
}

// Update events by event id
$stmt = $mysqli->prepare("UPDATE events SET title=?, date=?, time=? WHERE event_id=? AND username=?");
if (!$stmt) {
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("sssis", $newTitle, $newDate, $newTime, $eventId, $username);
$stmt->execute();
$stmt->close();

echo json_encode(array(
    "success" => true
));
exit();
?>