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
if (!preg_match('/^[A-Za-z0-9 ]{0,30}$/', $newTitle) && $newTitle) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}

if (!preg_match('/^\d{1,}-\d{1,}-\d{1,}$/', $newDate) && $newDate) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}
if (!preg_match('/^\d{1,2}:\d{2}$/', $newTime) && $newTime) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}


require 'database.php';
$stmt = $mysqli->prepare("SELECT title, date, time FROM events WHERE event_id=?");

if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->bind_result($currentTitle, $currentDate, $currentTime);
$stmt->fetch();
$stmt->close();


if (!$newTitle) {
    $newTitle = $currentTitle;
}

if (!$newDate) {
    $newDate = $currentDate;
}

if (!$newTime) {
    $newTime = $currentTime;
}

$stmt = $mysqli->prepare("UPDATE events SET title=?, date=?, time=? WHERE event_id=?");
if (!$stmt) {
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("sssi", $newTitle, $newDate, $newTime, $eventId);
$stmt->execute();
$stmt->close();

echo json_encode(array(
    "success" => true
));
exit();
?>