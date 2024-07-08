<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "user_registration";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_POST['recipe_id'];

$sql_report = "INSERT INTO reported_posts (user_id, recipe_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql_report);
$stmt->bind_param("ii", $user_id, $recipe_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to report post']);
}

$stmt->close();
$conn->close();
?>
