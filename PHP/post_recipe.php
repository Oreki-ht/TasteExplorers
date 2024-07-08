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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['email'])) {
    
    $email = $_SESSION['email'];
    $sql = "SELECT username FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $username = $row['username'];

        
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $ingredients = $conn->real_escape_string($_POST['ingredients']);
        $instructions = $conn->real_escape_string($_POST['instructions']);

        
        if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
            $target_dir = "uploads/"; 
            $target_file = $target_dir . basename($_FILES["recipe_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            
            $check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
            if ($check !== false) {
                
                if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
                    
                    $sql_insert = "INSERT INTO recipes (username, title, description, ingredients, instructions, image_path) 
                                   VALUES ('$username', '$title', '$description', '$ingredients', '$instructions', '$target_file')";

                    if ($conn->query($sql_insert) === TRUE) {
                        $_SESSION['message'] = "Recipe posted successfully!";
                        header("Location: view_recipes.php"); 
                        exit();
                    } else {
                        $_SESSION['message'] = "Error posting recipe: " . $conn->error;
                        header("Location: post_recipe.html"); 
                        exit();
                    }
                } else {
                    $_SESSION['message'] = "Sorry, there was an error uploading your file.";
                    header("Location: post_recipe.html"); 
                    exit();
                }
            } else {
                $_SESSION['message'] = "File is not an image.";
                header("Location: post_recipe.html"); 
                exit();
            }
        } else {
            $_SESSION['message'] = "No file uploaded or upload error.";
            header("Location: post_recipe.html"); 
            exit();
        }
    } else {
        $_SESSION['message'] = "User not found or session expired.";
        header("Location: login.html"); 
        exit();
    }
} else {
    $_SESSION['message'] = "Unauthorized access.";
    header("Location: login.html"); 
    exit();
}

$conn->close();
?>
