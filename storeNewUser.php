<?php
session_start();
require 'database.php';


$username = trim($_POST['username']);
$password = $_POST['password'];


// Ensure username is alphanumeric and less than 30 characters
if (!preg_match('/^[A-Za-z0-9]{0,30}$/', $username)) {
    $_SESSION['error'] = "Username must contain only letters and numbers and be less than 30 characters.";
    
    exit();
}


// Hash the password with PASSWORD_DEFAULT
$hashed_password = password_hash($password, PASSWORD_DEFAULT);




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

// If username already exists, redirect to index.php with error message
if($count > 0){
    // Username already exists
    $_SESSION['error'] = "Username already taken.";

    
    exit();
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

// Registration successful, set session variables and redirect to index.php
$_SESSION['username'] = $username;
$_SESSION['guest'] = false;
$_SESSION['token'] = bin2hex(random_bytes(32));


exit();
?>