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

$response = ['success' => false, 'message' => '', 'followers_count' => 0];

if (isset($_SESSION['username']) && isset($_POST['action']) && isset($_POST['username'])) {
    $current_username = $_SESSION['username'];
    $profile_username = $_POST['username'];
    $action = $_POST['action'];

    if ($action === 'follow') {
        
        $sql_follow = "INSERT INTO follows (follower, following) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_follow);
        $stmt->bind_param("ss", $current_username, $profile_username);
        $stmt->execute();
    } elseif ($action === 'unfollow') {
        
        $sql_unfollow = "DELETE FROM follows WHERE follower = ? AND following = ?";
        $stmt = $conn->prepare($sql_unfollow);
        $stmt->bind_param("ss", $current_username, $profile_username);
        $stmt->execute();
    }

    
    $sql_followers = "SELECT COUNT(*) AS followers FROM follows WHERE following = ?";
    $stmt = $conn->prepare($sql_followers);
    $stmt->bind_param("s", $profile_username);
    $stmt->execute();
    $result_followers = $stmt->get_result();
    $followers_count = ($result_followers->num_rows == 1) ? $result_followers->fetch_assoc()['followers'] : 0;

    $response['success'] = true;
    $response['followers_count'] = $followers_count;
} else {
    $response['message'] = 'Invalid request';
}

$conn->close();

echo json_encode($response);
?>
