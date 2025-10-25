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

$event_id = $json_obj['id'];
$tag = $json_obj['tag'];
$username = $_SESSION['username'];
$token = $json_obj['token'];
if (!hash_equals($_SESSION['token'], $token)) {
    die("Request forgery detected");
}

require 'database.php';


if ($tag == "") {
    $stmt = $mysqli->prepare("UPDATE events SET tag=NULL WHERE event_id=? AND username=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("is", $event_id, $username);
    $stmt->execute();
    $stmt->close();
} 
else {
    $stmt = $mysqli->prepare("UPDATE events SET tag=? WHERE event_id=? AND username=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("sis", $tag, $event_id, $username);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(array(
    "success" => true
));

exit;
?>

