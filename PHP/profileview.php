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

if (isset($_GET['username'])) {
    $profile_username = $_GET['username'];

    $sql_user = "SELECT profile_picture, bio FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("s", $profile_username);
    $stmt->execute();
    $result_user = $stmt->get_result();

    if ($result_user->num_rows == 1) {
        $row_user = $result_user->fetch_assoc();
        $profile_picture = $row_user['profile_picture'] ?? 'uploads/default-profile.png';
        $bio = $row_user['bio'] ?? '';
    } else {
        header("Location: 404.html");
        exit();
    }

    // Fetch user's posted recipes from database
    $sql_recipes = "SELECT id, title, description, ingredients, instructions, created_at FROM recipes WHERE username = ?";
    $stmt = $conn->prepare($sql_recipes);
    $stmt->bind_param("s", $profile_username);
    $stmt->execute();
    $result_recipes = $stmt->get_result();

    // Fetch follower and following counts
    $sql_followers = "SELECT COUNT(*) AS followers FROM follows WHERE following = ?";
    $stmt = $conn->prepare($sql_followers);
    $stmt->bind_param("s", $profile_username);
    $stmt->execute();
    $result_followers = $stmt->get_result();
    $followers_count = ($result_followers->num_rows == 1) ? $result_followers->fetch_assoc()['followers'] : 0;

    $sql_following = "SELECT COUNT(*) AS following FROM follows WHERE follower = ?";
    $stmt = $conn->prepare($sql_following);
    $stmt->bind_param("s", $profile_username);
    $stmt->execute();
    $result_following = $stmt->get_result();
    $following_count = ($result_following->num_rows == 1) ? $result_following->fetch_assoc()['following'] : 0;

    // Check if the current user is following the profile user
    if (isset($_SESSION['username'])) {
        $current_username = $_SESSION['username'];
        $sql_is_following = "SELECT COUNT(*) AS is_following FROM follows WHERE follower = ? AND following = ?";
        $stmt = $conn->prepare($sql_is_following);
        $stmt->bind_param("ss", $current_username, $profile_username);
        $stmt->execute();
        $result_is_following = $stmt->get_result();
        $is_following = ($result_is_following->num_rows == 1) ? $result_is_following->fetch_assoc()['is_following'] : 0;
    } else {
        $is_following = 0;
    }
} else {
    header("Location: index.php");
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'nav.html'; ?>
    <main>
        <section class="profile-header">
            <div class="profile-pic-container">
                <div class="profile-pic">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" onerror="this.onerror=null; this.src='default-profile.png';" alt="Profile Picture">
                </div>
            </div>
            <div class="profile-details">
                <h2><?php echo htmlspecialchars($profile_username); ?></h2>
                <p class="bio"><?php echo nl2br(htmlspecialchars($bio)); ?></p>
                <div class="profile-stats">
                    <div><strong><?php echo $result_recipes->num_rows; ?></strong> posts</div>
                    <div><strong id="followers-count"><?php echo $followers_count; ?></strong> followers</div>
                    <div><strong><?php echo $following_count; ?></strong> following</div>
                </div>
                <?php if (isset($current_username) && $current_username != $profile_username): ?>
                    <button id="follow-btn" data-following="<?php echo $is_following ? 'yes' : 'no'; ?>">
                        <?php echo $is_following ? 'Unfollow' : 'Follow'; ?>
                    </button>
                <?php endif; ?>
            </div>
        </section>
        <section class="recipes-list">
            <?php
            if ($result_recipes->num_rows > 0) {
                while ($row_recipe = $result_recipes->fetch_assoc()) {
                    echo "<div class='recipe-container'>";
                    echo "<div class='recipe' data-recipe-id='" . htmlspecialchars($row_recipe['id']) . "'>";
                    echo "<h2>" . htmlspecialchars($row_recipe['title']) . "</h2>";
                    echo "<p><strong>Description:</strong> " . htmlspecialchars($row_recipe['description']) . "</p>";
                    echo "<p><strong>Ingredients:</strong> " . htmlspecialchars($row_recipe['ingredients']) . "</p>";
                    echo "<p><strong>Instructions:</strong> " . htmlspecialchars($row_recipe['instructions']) . "</p>";
                    echo "<p class='timeago' datetime='" . htmlspecialchars($row_recipe['created_at']) . "'></p>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No recipes found.</p>";
            }
            ?>
        </section>
    </main>
    <script>
$(document).ready(function(){
    $('#follow-btn').click(function(){
        var following = $(this).data('following');
        var action = following === 'yes' ? 'unfollow' : 'follow';
        $.post('follow_unfollow.php', {action: action, username: '<?php echo $profile_username; ?>'}, function(response) {
            if (response.success) {
                $('#follow-btn').text(action === 'follow' ? 'Unfollow' : 'Follow');
                $('#follow-btn').data('following', action === 'follow' ? 'yes' : 'no');
                $('#followers-count').text(response.followers_count);
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json');
    });
});
</script>

</body>
</html>
