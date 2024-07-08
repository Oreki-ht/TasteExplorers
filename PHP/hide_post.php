<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : null;
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    
    error_log("Recipe ID: " . $recipe_id);
    error_log("User ID: " . $user_id);

    if (!$recipe_id || !$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit();
    }

    
    if (!$conn) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit();
    }

    $sql = "INSERT INTO hidden_posts (user_id, recipe_id, hidden_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare statement failed: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("ii", $user_id, $recipe_id);
    $response = array();

    if ($stmt->execute()) {
        $response['status'] = 'success';
    } else {
        $response['status'] = 'error';
        $response['message'] = $stmt->error;
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
