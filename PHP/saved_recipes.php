<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "user_registration";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql_saved_recipes = "
    SELECT r.id, r.username, r.title, r.description, r.ingredients, r.instructions, r.image_path, r.created_at, 
           COUNT(rl.id) AS like_count, u.profile_picture
    FROM saved_recipes sr
    JOIN recipes r ON sr.recipe_id = r.id
    LEFT JOIN recipe_likes rl ON r.id = rl.recipe_id
    JOIN users u ON r.username = u.username
    WHERE sr.user_id = ?
    GROUP BY r.id, r.username, r.title, r.description, r.ingredients, r.instructions, r.image_path, r.created_at, u.profile_picture";

$stmt = $conn->prepare($sql_saved_recipes);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_saved_recipes = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taste Explorer</title>
    <link rel="stylesheet" href="styleview.css"> <!-- Link to external stylesheet -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/timeago.js/4.0.2/timeago.min.js"></script> <!-- Include timeago.js -->
</head>
<body>
    
    <?php include 'nav.html'; ?>

    <main>
        <section class="recipes-list">
            <?php
            if ($result_saved_recipes->num_rows > 0) {
                while ($row_recipe = $result_saved_recipes->fetch_assoc()) {
                    echo "<div class='recipe-container'>";
                    echo "<div class='recipe' data-recipe-id='" . htmlspecialchars($row_recipe['id']) . "'>";
                    echo "<div class='recipe-header'>";
                    echo "<img src='" . htmlspecialchars($row_recipe['profile_picture']) . "' alt='Profile Picture' class='profile-pic'>";
                    echo "<div class='user-info'>";
                    echo "<p><strong>" . htmlspecialchars($row_recipe['username']) . "</strong></p>";
                    echo "<p class='timeago' datetime='" . htmlspecialchars($row_recipe['created_at']) . "'></p>";
                    echo "</div>";
                    echo "</div>";
                    if (!empty($row_recipe['image_path'])) {
                        echo "<img src='" . htmlspecialchars($row_recipe['image_path']) . "' alt='Recipe Image' class='recipe-image'>";
                    }
                    echo "<div class='recipe-body'>";
                    echo "<h2>" . htmlspecialchars($row_recipe['title']) . "</h2>";
                    echo "<p><strong>Description:</strong> " . htmlspecialchars($row_recipe['description']) . "</p>";
                    echo "<p><strong>Ingredients:</strong> " . htmlspecialchars($row_recipe['ingredients']) . "</p>";
                    echo "<p><strong>Instructions:</strong> " . htmlspecialchars($row_recipe['instructions']) . "</p>";
                    echo "<button class='save-button' data-recipe-id='" . htmlspecialchars($row_recipe['id']) . "'>Unsave</button>";
                    echo "</div>"; // Close recipe-body
                    echo "</div>"; // Close recipe
                    echo "</div>"; // Close recipe-container
                }
            } else {
                echo "<p>No saved recipes found.</p>";
            }
            ?>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Custom locale for timeago.js
    timeago.register('custom', function(number, index, total_sec) {
        return [
            ['a few seconds ago', 'in a few seconds'],
            ['1 minute ago', 'in 1 minute'],
            ['%s minutes ago', 'in %s minutes'],
            ['1 hour ago', 'in 1 hour'],
            ['%s hours ago', 'in %s hours'],
            ['1 day ago', 'in 1 day'],
            ['%s days ago', 'in %s days'],
            ['1 week ago', 'in 1 week'],
            ['%s weeks ago', 'in %s weeks'],
            ['1 month ago', 'in 1 month'],
            ['%s months ago', 'in %s months'],
            ['1 year ago', 'in 1 year'],
            ['%s years ago', 'in %s years']
        ][index];
    });

    // Initialize timeago.js with custom locale
    timeago.render(document.querySelectorAll('.timeago'), 'custom');

    document.querySelectorAll('.save-button').forEach(button => {
        button.addEventListener('click', function() {
            const recipeId = this.getAttribute('data-recipe-id');
            const action = this.textContent.trim() === 'Unsave' ? 'unsave' : 'save';

            fetch('save_recipe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `recipe_id=${recipeId}&action=${action}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'save') {
                        this.textContent = 'Unsave';
                    } else {
                        this.closest('.recipe-container').remove();
                    }
                } else {
                    alert('An error occurred: ' + data.message);
                }
            });
        });
    });
});

    </script>
</body>
</html>

<?php
$conn->close();
?>
