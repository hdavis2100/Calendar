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
$currView = $json_obj[0][3];
$username = $_SESSION['username'];


// Events array to return
$events = array();
$views = array();

require 'database.php';

// For each date, get all events for that date
if ($currView){

    $username = $currView;
}

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

$username = $_SESSION['username'];
$stmt = $mysqli->prepare("SELECT username FROM users WHERE username!=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$users = array();
while ($row = $result->fetch_assoc()) {
    $users[$row['username']] = false;
}
$stmt->close();

$stmt = $mysqli->prepare("SELECT dest FROM shares WHERE source=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute(); 
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[$row['dest']] = true;
}
$stmt->close();

$stmt = $mysqli->prepare("SELECT source FROM shares WHERE dest=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    array_push($views, $row['source']);
}
$stmt->close();

array_push($views, $username);

echo json_encode(array(
    "events" => $events,
    "users" => $users,
    "views" => $views
));

exit();
?>