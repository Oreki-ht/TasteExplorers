<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipe_id = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : null;

    if (!$recipe_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid recipe ID.']);
        exit();
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
        exit();
    }

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "user_registration";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit();
    }

    $delete_recipe_query = "DELETE FROM recipes WHERE id = ?";
    $stmt = $conn->prepare($delete_recipe_query);
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete recipe.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed.']);
}
