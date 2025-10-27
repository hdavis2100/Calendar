<?php

header('Content-Type: application/json');
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

$username = $_SESSION['username'];
$eventId = $json_obj['id'];
$token = $json_obj['token'];
if (!hash_equals($_SESSION['token'], $token)) {
    die("Request forgery detected");
    
}

require 'database.php';

// Get event creator username
$stmt = $mysqli->prepare("SELECT username FROM events WHERE event_id=?");
$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->bind_result($eventUsername);
$stmt->fetch();
$stmt->close();


// the creater does not have a reference
if ($eventUsername != $username){

    // Check if user has a reference to this event

    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM refs WHERE event_id=? AND username=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("is", $eventId, $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if ($count == 0) {
        echo json_encode(array(
            "success" => false
        ));
        exit;
    }


    // Delete reference to event for this user
    $stmt = $mysqli->prepare("DELETE FROM refs WHERE event_id=? AND username=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("is", $eventId, $username);
    $stmt->execute();
    $stmt->close();
    echo json_encode(array(
        "success" => true
    ));
    exit();
}

// If the user is the creator, delete all references and then the event
else{


    $stmt = $mysqli->prepare("DELETE FROM refs WHERE event_id=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }

    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("DELETE FROM events WHERE event_id=? AND username=?");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("is", $eventId, $username);
    $stmt->execute();
    $stmt->close();

    echo json_encode(array(
        "success" => true
    ));

    exit();
}
?>