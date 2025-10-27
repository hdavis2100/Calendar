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

$event_id = $json_obj['id'];
$tag = $json_obj['tag'];
$username = $_SESSION['username'];
$token = $json_obj['token'];

if (!hash_equals($_SESSION['token'], $token)) {
    die("Request forgery detected");
}

require 'database.php';

if (!preg_match('/^[A-Za-z0-9 ]{0,30}$/', $tag) && $tag != "") {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}

$stmt = $mysqli->prepare("SELECT username FROM events WHERE event_id=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$stmt->bind_result($creator);
$stmt->fetch();
$stmt->close();
// Verify user has permission to tag event

if ($username != $creator) {
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM refs WHERE event_id=? AND username=? AND permission='full'");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt->bind_param("is", $event_id, $username);
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
}

// If tag is empty we are removing the tag, otherwise we are adding the tag

$stmt = $mysqli->prepare("UPDATE events SET tag=? WHERE event_id=? AND username=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param("sis", $tag, $event_id, $creator);
$stmt->execute();
$stmt->close();


echo json_encode(array(
    "success" => true
));

exit;
?>

