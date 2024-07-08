<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $ingredients = htmlspecialchars($_POST['ingredients']);
    $directions = htmlspecialchars($_POST['directions']);
    $cooking_time = $_POST['cooking_time'];
    $cuisine_type = $_POST['cuisine_type'];
    $meal_type = $_POST['meal_type'];
    $image = '';

    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif','webp'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        if (in_array($ext, $allowed)) {
            $image = 'uploads/' . basename($_FILES['image']['name']);
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
                echo "Failed to upload image.";
                exit;
            }
        } else {
            echo "Invalid file type.";
            exit;
        }
    }

    
    $stmt = $conn->prepare("INSERT INTO recipes_home (title, description, ingredients, directions, cooking_time, cuisine_type, meal_type, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    
    $stmt->bind_param("ssssisss", $title, $description, $ingredients, $directions, $cooking_time, $cuisine_type, $meal_type, $image);

    
    if ($stmt->execute()) {
        echo "New recipe created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    
    $stmt->close();
    $conn->close();
}
?>