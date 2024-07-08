<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "user_registration";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    
    if (strlen($password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    
    $default_profile_picture = 'uploads/default-profile.png';

    
    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, username, email, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstname, $lastname, $username, $email, $hashed_password, $default_profile_picture);

    
    $check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_username->bind_param("s", $username);
    $check_username->execute();
    $check_username->store_result();

    if ($check_username->num_rows > 0) {
        die("Username is already taken.");
    } else {
        
        if ($stmt->execute()) {
            
            header("Location: login.html");
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
