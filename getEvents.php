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

$username = $_SESSION['username'];
$events = array(); 

require 'database.php';
for ($i=0; $i< count($json_obj); $i++) {
    $year = $json_obj[$i][0];
    $month = $json_obj[$i][1];
    $day = $json_obj[$i][2];
    $date = $year . '-' . $month . '-' . $day;

    $stmt = $mysqli->prepare("SELECT event_id, title, time FROM events WHERE username=? AND date=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("ss", $username, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    

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