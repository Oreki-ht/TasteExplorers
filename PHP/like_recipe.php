<?php
session_start();


$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "user_registration";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
}


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$recipe_id = $_POST['recipe_id'];


if (empty($recipe_id) || empty($user_id)) {
    echo json_encode(['success' => false, 'message' => "Invalid recipe ID or user ID"]);
    exit();
}


$check_like_query = "SELECT id FROM recipe_likes WHERE recipe_id = ? AND user_id = ?";
$stmt = $conn->prepare($check_like_query);
$stmt->bind_param("ii", $recipe_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    
    $delete_like_query = "DELETE FROM recipe_likes WHERE recipe_id = ? AND user_id = ?";
    $stmt = $conn->prepare($delete_like_query);
    $stmt->bind_param("ii", $recipe_id, $user_id);
    if ($stmt->execute()) {
        
        $like_count_query = "SELECT COUNT(*) AS like_count FROM recipe_likes WHERE recipe_id = ?";
        $stmt = $conn->prepare($like_count_query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'like_count' => $row['like_count']]);
    } else {
        echo json_encode(['success' => false, 'message' => "Failed to unlike recipe"]);
    }
} else {
    
    $insert_like_query = "INSERT INTO recipe_likes (recipe_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_like_query);
    $stmt->bind_param("ii", $recipe_id, $user_id);
    if ($stmt->execute()) {
        
        $like_count_query = "SELECT COUNT(*) AS like_count FROM recipe_likes WHERE recipe_id = ?";
        $stmt = $conn->prepare($like_count_query);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'like_count' => $row['like_count']]);
    } else {
        echo json_encode(['success' => false, 'message' => "Failed to like recipe"]);
    }
}

$stmt->close();
$conn->close();
?>
