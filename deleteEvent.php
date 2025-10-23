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

require 'database.php';

// Delete event by event id
$stmt = $mysqli->prepare("DELETE FROM events WHERE event_id=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->close();

echo json_encode(array(
    "success" => true
));

exit();
?>