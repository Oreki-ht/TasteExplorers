<?php
session_start();
header('Content-Type: application/json');

$response = array('status' => 'error', 'message' => 'An unknown error occurred.');

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipe_id = $_POST['recipe_id'];
    $user_id = $_SESSION['user_id'];
    $comment = trim($_POST['comment']);

    if (empty($comment)) {
        $response['message'] = 'Comment cannot be empty.';
        echo json_encode($response);
        exit();
    }

    // Database connection
    $servername = "localhost";
    $username = "root"; // Replace with your MySQL username
    $password = ""; // Replace with your MySQL password
    $dbname = "user_registration";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed: ' . $conn->connect_error;
        echo json_encode($response);
        exit();
    }

    $sql = "INSERT INTO comments (recipe_id, user_id, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $response['message'] = 'Failed to prepare statement: ' . $conn->error;
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("iis", $recipe_id, $user_id, $comment);
    if ($stmt->execute()) {
        // Fetch user info to return in the response
        $user_info_query = "SELECT username, profile_picture FROM users WHERE id = ?";
        $stmt_user_info = $conn->prepare($user_info_query);
        $stmt_user_info->bind_param("i", $user_id);
        $stmt_user_info->execute();
        $user_info_result = $stmt_user_info->get_result();
        $user_info = $user_info_result->fetch_assoc();

        $response = array(
            'status' => 'success',
            'message' => 'Comment added successfully.',
            'username' => $user_info['username'],
            'profile_picture' => $user_info['profile_picture'],
            'comment_text' => htmlspecialchars($comment),
            'comment_time' => date(DATE_ATOM) // ISO 8601 format date
        );
    } else {
        $response['message'] = 'Failed to add comment: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
