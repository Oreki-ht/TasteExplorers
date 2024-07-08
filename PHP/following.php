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

    
    if (isset($_POST['unfollow'])) {
        $unfollow_user = $_POST['unfollow_user'];
        $sql_unfollow = "DELETE FROM follows WHERE follower = '$username' AND following = '$unfollow_user'";
        $conn->query($sql_unfollow);
    }

    
    $sql_following = "SELECT following FROM follows WHERE follower = '$username'";
    $result_following = $conn->query($sql_following);
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
        <section class="following-list">
            <h2>Following</h2>
            <ul>
                <?php
                if ($result_following->num_rows > 0) {
                    while ($row_following = $result_following->fetch_assoc()) {
                        echo "<li>" . htmlspecialchars($row_following['following']);
                        echo "<form method='post' action='' style='display:inline;'>
                                <input type='hidden' name='unfollow_user' value='" . htmlspecialchars($row_following['following']) . "'>
                                <button type='submit' name='unfollow'>Unfollow</button>
                              </form></li>";
                    }
                } else {
                    echo "<p>No users followed yet.</p>";
                }
                ?>
            </ul>
        </section>
    </main>
</body>
</html>
