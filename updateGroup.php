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

// Check if member to be added exists
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

if ($count == 0) {
    echo json_encode(array(
        "success" => false,
        "message" => "User not found"
    ));
    exit;
}

// Get event creator username
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

// Creator permissions cannot be changed
if ($user == $memberName) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}

// Verify user has permission to update event permissions. Reference does not exist for creator, so skip this case
if($user != $username){
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM refs WHERE event_id=? AND username=? AND permission='full'");
    if(!$stmt){
        printf("Query Prep Failed: %s\n", $mysqli->error);
        
        exit;
    }
    $stmt->bind_param("is", $id, $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if ($count == 0) {
        echo json_encode(array(
            "success" => false,
        ));
        exit;
    }
}

// Verify member to be updated does not have full permission. Cannot change status of other individuals with full permission
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM refs WHERE event_id=? AND username=? AND permission='full'");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    
    exit;
}
$stmt->bind_param("is", $id, $memberName);
$stmt->execute();

$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
if ($count > 0 && $memberName != $username) {
    echo json_encode(array(
        "success" => false,
    ));
    exit;
}
// Delete existing reference if it exists
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

// Add new reference with specified permission
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
