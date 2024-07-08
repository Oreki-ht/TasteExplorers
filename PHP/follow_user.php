<?php
session_start();

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "user_registration";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id']) && isset($_POST['followed_id']) && isset($_POST['action'])) {
    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['followed_id'];
    $action = $_POST['action'];

    if ($action === 'follow') {
        $sql = "INSERT INTO follows (follower, following, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $follower_id, $followed_id);
    } else {
        $sql = "DELETE FROM follows WHERE follower = ? AND following = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $follower_id, $followed_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>
