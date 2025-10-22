<?php

session_start();
require 'database.php';


// Check page authorization
if( !isset($_POST['username']) ){
    header("Location: index.php");
    exit();
}
$username = $_POST['username'];

// Prepare and execute query to get hashed password for the given username

$stmt = $mysqli->prepare("SELECT password FROM users WHERE username=?");
if(!$stmt){
    printf("Query Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($hashed_password);

if (!$stmt->fetch()) {
    // Username not found
    $_SESSION['found'] = false;
    
    header("Location: index.php");
    exit();
}
$stmt->close();

// Check if password matches

if(!password_verify($_POST['password'], $hashed_password)){
    $_SESSION['found'] = false;
    header("Location: index.php");
    exit();
}


// Login successful, set session variables and redirect to news.php
$_SESSION['username'] = $username;
$_SESSION['guest'] = false;
$_SESSION['token'] = bin2hex(random_bytes(32));

header("Location: news.php"); 
exit();
?>