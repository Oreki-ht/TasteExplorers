<?php
$servername = "localhost";
$username = "root";
$password = ""; // Add your MySQL password if needed
$dbname = "user_registration";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
