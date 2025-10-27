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
$memberName = $json_obj['memberName'];
$permission = $json_obj['permissionLevel'];
$token = $json_obj['token'];
$id = $json_obj['id'];
if (!hash_equals($_SESSION['token'], $token)) {
    die("Request forgery detected");
    
}


$username = $_SESSION['username'];

require 'database.php';

$stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    
    exit;
}
$stmt->bind_param("s", $memberName);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count === 0) {
    echo json_encode(array(
        "success" => false,
        "message" => "User not found"
    ));
    exit;
}
$stmt = $mysqli->prepare("SELECT username FROM events WHERE event_id=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($user);
$stmt->fetch();
$stmt->close();
if ($user == $memberName) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM refs WHERE username=? AND event_id=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    
    exit;
}
$stmt->bind_param("si", $memberName, $id);
$stmt->execute();
$stmt->close();

if ($permission == "none") {
    echo json_encode(array(
        "success" => true
    ));
    exit;
}
$stmt = $mysqli->prepare("INSERT INTO refs (username, event_id, permission) VALUES (?, ?, ?)");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    
    exit;
}
$stmt->bind_param("sis", $memberName, $id, $permission);
$stmt->execute();
$stmt->close();
echo json_encode(array(
    "success" => true
));
