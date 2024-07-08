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
        $username = "Unknown";
    }

    
    if (isset($_POST['search_query']) && !empty($_POST['search_query'])) {
        $search_query = $conn->real_escape_string($_POST['search_query']);
        $sql_suggestions = "
            SELECT u.username, u.profile_picture
            FROM users u
            WHERE u.username LIKE '%$search_query%'
            AND u.username != '$username'
            AND u.username NOT IN (SELECT following FROM follows WHERE follower = '$username')
        ";
    } else {
        
        $sql_suggestions = "
            SELECT u.username, u.profile_picture, COUNT(f.following) AS follower_count
            FROM users u
            LEFT JOIN follows f ON u.username = f.following
            WHERE u.username != '$username'
            AND u.username NOT IN (SELECT following FROM follows WHERE follower = '$username')
            GROUP BY u.username, u.profile_picture
            ORDER BY follower_count DESC
        ";
    }

    $result_suggestions = $conn->query($sql_suggestions);
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
    <link rel="stylesheet" href="stylepeople.css"> <!-- Replace with your stylesheet -->
</head>
<body>
<?php include 'nav.html'; ?>
    

    <main>
        <section class="suggestions">
            <h2>Suggested Users:</h2>
            <!-- Search form -->
            <form method="post" action="" class="search-form">
                <input type="text" name="search_query" placeholder="Search usernames..." class="search-input">
                <button type="submit" class="search-button">Search</button>
            </form>
            
            <?php
            if ($result_suggestions->num_rows > 0) {
                while ($row_suggestion = $result_suggestions->fetch_assoc()) {
                    echo "<div class='user'>";
                    echo "<img src='" . htmlspecialchars($row_suggestion['profile_picture']) . "' alt='Profile Picture' class='profile-pic'>";
                    echo "<p><a href='profileview.php?username=" . urlencode($row_suggestion['username']) . "'>" . htmlspecialchars($row_suggestion['username']) . "</a></p>";
                    echo "<form method='post' action='toggle_follow.php'>";
                    echo "<input type='hidden' name='follow_user' value='" . htmlspecialchars($row_suggestion['username']) . "'>";
                    echo "<button type='submit'>Follow</button>";
                    echo "</form>";
                    echo "</div>";
                }
            } else {
                echo "<p>No suggestions available.</p>";
            }
            ?>
        </section>
    </main>
</body>
</html>
