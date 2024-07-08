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

    
    $stmt_user = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows == 1) {
        $row_user = $result_user->fetch_assoc();
        $user_id = $row_user['id'];
        $username = $row_user['username'];
    } else {
        $user_id = 0;
        $username = "Unknown";
    }
    $stmt_user->close();

    $notifications = [];

    
    $stmt_follow_notifications = $conn->prepare("
        SELECT follower AS user, 'started following you' AS action, created_at 
        FROM follows 
        WHERE following = ?
        ORDER BY created_at DESC
    ");
    $stmt_follow_notifications->bind_param("s", $username);
    $stmt_follow_notifications->execute();
    $result_follow_notifications = $stmt_follow_notifications->get_result();
    
    while ($row_follow = $result_follow_notifications->fetch_assoc()) {
        $notifications[] = $row_follow;
    }
    $stmt_follow_notifications->close();

    
    $stmt_like_notifications = $conn->prepare("
        SELECT users.username AS user, CONCAT('liked your post ', recipes.title) AS action, recipe_likes.created_at 
        FROM recipe_likes
        JOIN recipes ON recipe_likes.recipe_id = recipes.id
        JOIN users ON recipe_likes.user_id = users.id
        WHERE recipes.user_id = ?
        ORDER BY recipe_likes.created_at DESC
    ");
    $stmt_like_notifications->bind_param("i", $user_id);
    $stmt_like_notifications->execute();
    $result_like_notifications = $stmt_like_notifications->get_result();

    if ($result_like_notifications === false) {
        echo "Error in like notifications query: " . $conn->error;
    } else {
        while ($row_like = $result_like_notifications->fetch_assoc()) {
            $notifications[] = $row_like;
        }
    }
    $stmt_like_notifications->close();

    
    $stmt_comment_notifications = $conn->prepare("
        SELECT users.username AS user, CONCAT('commented on your post ', recipes.title, ': ', comments.comment) AS action, comments.created_at 
        FROM comments
        JOIN recipes ON comments.recipe_id = recipes.id
        JOIN users ON comments.user_id = users.id
        WHERE recipes.user_id = ?
        ORDER BY comments.created_at DESC
    ");
    $stmt_comment_notifications->bind_param("i", $user_id);
    $stmt_comment_notifications->execute();
    $result_comment_notifications = $stmt_comment_notifications->get_result();

    if ($result_comment_notifications === false) {
        echo "Error in comment notifications query: " . $conn->error;
    } else {
        while ($row_comment = $result_comment_notifications->fetch_assoc()) {
            $notifications[] = $row_comment;
        }
    }
    $stmt_comment_notifications->close();

    
    usort($notifications, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

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
    <link rel="stylesheet" href="stylenotification.css"> <!-- Replace with your stylesheet -->
</head>
<body>

<?php include 'nav.html'; ?>

    <main>
        <section class="notifications">
            <h2>New Notifications</h2>

            <?php
            if (!empty($notifications)) {
                foreach ($notifications as $notification) {
                    echo "<div class='notification'>";
                    echo "<p><strong>" . htmlspecialchars($notification['user']) . "</strong> " . htmlspecialchars($notification['action']) . " on " . htmlspecialchars($notification['created_at']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>No new notifications.</p>";
            }
            ?>
        </section>
    </main>

</body>
</html>
