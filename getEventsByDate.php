<?php

header('Content-Type: application/json');
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(array(
        "events" => []
    ));

    exit;
}
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$year = $json_obj['year'];
$month = $json_obj['month'];
$day = $json_obj['day'];
$date = $year . '-' . $month . '-' . $day;
$username = $_SESSION['username'];
$events = array(); 

require 'database.php';

$stmt = $mysqli->prepare("SELECT title, time FROM events WHERE username=? AND date=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("ss", $username, $date);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    array_push($events, array(
        "title" => $row['title'],
        "time" => $row['time']
    ));
}

echo json_encode(array(
    "events" => $events

));
$stmt->close();
exit();