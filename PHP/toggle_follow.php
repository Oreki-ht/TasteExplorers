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


if (isset($_SESSION['email']) && isset($_POST['follow_user'])) {
    $email = $_SESSION['email'];
    $follow_user = $_POST['follow_user'];

    
    $sql_user = "SELECT username FROM users WHERE email = '$email'";
    $result_user = $conn->query($sql_user);

    if ($result_user->num_rows == 1) {
        $row_user = $result_user->fetch_assoc();
        $username = $row_user['username'];
    } else {
        $username = "Unknown";
    }

    
    $sql_check = "SELECT * FROM follows WHERE follower = '$username' AND following = '$follow_user'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        
        $sql_unfollow = "DELETE FROM follows WHERE follower = '$username' AND following = '$follow_user'";
        $conn->query($sql_unfollow);
    } else {
        
        $sql_follow = "INSERT INTO follows (follower, following) VALUES ('$username', '$follow_user')";
        $conn->query($sql_follow);
    }
}


header("Location: people_you_may_know.php");
exit();

$conn->close();
?>
