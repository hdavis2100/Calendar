<?php

header('Content-Type: application/json');
ini_set("session.cookie_httponly", 1);
session_start();
if (!isset($_SESSION['username'])) {
    echo json_encode(array(
        "events" => []
    ));

    exit;
}
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

$username = $_SESSION['username'];

// Events array to return
$events = array(); 

require 'database.php';

// For each date, get all events for that date
for ($i=0; $i< count($json_obj); $i++) {
    $year = $json_obj[$i][0];
    $month = $json_obj[$i][1];
    $day = $json_obj[$i][2];

    // Format date as stored in database
    $date = $year . '-' . $month . '-' . $day;

    // Grab event by username and date
    $stmt = $mysqli->prepare("SELECT event_id, title, time FROM events WHERE username=? AND date=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("ss", $username, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Store event details. Update month = month - 1 to correct for JS Date object
    while ($row = $result->fetch_assoc()) {
        array_push($events, array(
            "year" => $year,
            "month" => $month - 1,
            "day" => $day,
            "title" => $row['title'],
            "time" => $row['time'],
            "id" => $row['event_id']
        ));
    }
    $stmt->close();
}

echo json_encode(array(
    "events" => $events

));

exit();