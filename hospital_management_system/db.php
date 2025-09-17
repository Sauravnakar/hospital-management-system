<?php
// Database connection file.
// Update the credentials below to match your XAMPP MySQL configuration.
$servername = "localhost";
$username = "root";
$password = "";
$database = "hospital";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>