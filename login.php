<?php
header("Content-Type: application/json");
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$username = $json_obj['username'];
$password = $json_obj['password'];


require 'database.php';






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
    echo json_encode(array(
		"success" => false,
		"message" => "Incorrect Username or Password"
	));
    $stmt->close();
	exit;
    
}



$stmt->close();

// Check if password matches

if(!password_verify($password, $hashed_password)){
    echo json_encode(array(
		"success" => false,
		"message" => "Incorrect Username or Password"
	));
	exit;
}


// Login successful, set session variables
ini_set("session.cookie_httponly", 1);
session_start();
$_SESSION['username'] = $username;
$_SESSION['token'] = bin2hex(random_bytes(32));

echo json_encode(array(
    "success" => true,
));
exit();
?>