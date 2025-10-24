<?php
header("Content-Type: application/json");
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$username = $json_obj['username'];
$password = $json_obj['password'];


// Ensure username is alphanumeric and less than 30 characters
if (!preg_match('/^[A-Za-z0-9]{0,30}$/', $username)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username must contain only letters and numbers and be less than 30 characters."
    ));
    exit;
}


// Hash the password with PASSWORD_DEFAULT
$hashed_password = password_hash($password, PASSWORD_DEFAULT);


require 'database.php';


// Collect number of users with the same username to check if username is already taken
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM users WHERE username=?");
$stmt->bind_param('s', $username);
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    
    exit;
}
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

// If username already exists, return error message
if($count > 0){
    // Username already exists
    echo json_encode(array(
        "success" => false,
        "message" => "Username already taken."
    ));
    exit;
}

// Insert the new user into the database

$stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param('ss', $username, $hashed_password);
if(!$stmt){
	printf("Query Prep Failed: %s\n", $mysqli->error);
    
	exit;
}
$stmt->execute();
$stmt->close();

ini_set("session.cookie_httponly", 1);
session_start();


$_SESSION['username'] = $username;
$_SESSION['token'] = bin2hex(random_bytes(32));
echo json_encode(array(
    "success" => true,
));

exit();
?>