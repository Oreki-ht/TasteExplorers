<?php
session_start();

$servername = "localhost";
    $username = "root"; 
    $password_db = ""; 
    $dbname = "user_registration";

    $conn = new mysqli($servername, $username, $password_db, $dbname);

    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    

    

    
    $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php"); 
            exit();
        } else {
            $_SESSION['message'] = "Incorrect password. Please try again.";
            header("Location: login.html"); 
            exit();
        }
    } else {
        $_SESSION['message'] = "User with that email does not exist.";
        header("Location: login.html"); 
        exit();
    }

    $conn->close();
}
?>
