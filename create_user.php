<?php
require_once "config/database.php";

$database = new Database();
$conn = $database->getConnection();


$username = "admin";
$password = "123456";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user into the database
$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
if ($stmt->execute([$username, $hashed_password])) {
    echo "User created successfully.";
} else {
    echo "Error creating user.";
}
?>