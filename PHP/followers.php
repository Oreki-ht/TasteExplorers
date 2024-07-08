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


if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];

    
    $sql_user = "SELECT username FROM users WHERE email = '$email'";
    $result_user = $conn->query($sql_user);

    if ($result_user->num_rows == 1) {
        $row_user = $result_user->fetch_assoc();
        $username = $row_user['username'];
    } else {
        
        header("Location: login.html");
        exit();
    }

    
    $sql_followers = "SELECT follower FROM follows WHERE following = '$username'";
    $result_followers = $conn->query($sql_followers);
} else {
    
    header("Location: login.html");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taste Explorer</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<?php include 'nav.html'; ?>
    <main>
        <section class="followers-list">
            <h2>Followers</h2>
            <ul>
                <?php
                if ($result_followers->num_rows > 0) {
                    while ($row_follower = $result_followers->fetch_assoc()) {
                        echo "<li>" . htmlspecialchars($row_follower['follower']) . "</li>";
                    }
                } else {
                    echo "<p>No followers yet.</p>";
                }
                ?>
            </ul>
        </section>
    </main>
</body>
</html>
