<?php
session_start();
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "user_registration";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipe_id = $_POST['recipe_id'];
    $user_id = $_SESSION['user_id']; 
    $action = $_POST['action'];

    if ($action == 'save') {
        $sql = "INSERT INTO saved_recipes (user_id, recipe_id) VALUES (?, ?)";
    } else if ($action == 'unsave') {
        $sql = "DELETE FROM saved_recipes WHERE user_id = ? AND recipe_id = ?";
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $recipe_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'action' => $action]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update save status']);
    }

    $stmt->close();
}

$conn->close();
?>
