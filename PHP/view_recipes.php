<?php
session_start();
//
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
    $user_id_query = "SELECT id, username FROM users WHERE email = ?";
    $stmt = $conn->prepare($user_id_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_row = $result->fetch_assoc();
    $_SESSION['user_id'] = $user_row['id'];
    $_SESSION['username'] = $user_row['username'];
    $logged_in_user_id = $user_row['id'];
    $logged_in_username = $user_row['username'];
} else {
    // Redirect to login page if user is not logged in
    header("Location: login.html");
    exit();
}

// Fetch recipes from database along with like counts and follow/save status
$sql_recipes = "
    SELECT r.id, r.username, r.title, r.description, r.ingredients, r.instructions, r.image_path, r.created_at, 
           COUNT(rl.id) AS like_count, u.profile_picture,
           CASE WHEN f.follower IS NOT NULL THEN 1 ELSE 0 END AS is_followed,
           CASE WHEN sr.user_id IS NOT NULL THEN 1 ELSE 0 END AS is_saved,
           CASE WHEN rp.recipe_id IS NOT NULL THEN 1 ELSE 0 END AS is_reported
    FROM recipes r
    LEFT JOIN recipe_likes rl ON r.id = rl.recipe_id
    JOIN users u ON r.username = u.username
    LEFT JOIN follows f ON f.follower = ? AND f.following = u.id
    LEFT JOIN saved_recipes sr ON sr.user_id = ? AND sr.recipe_id = r.id
    LEFT JOIN reported_posts rp ON rp.recipe_id = r.id
    LEFT JOIN hidden_posts hp ON hp.recipe_id = r.id AND hp.user_id = ?
    WHERE hp.recipe_id IS NULL
    GROUP BY r.id, r.username, r.title, r.description, r.ingredients, r.instructions, r.image_path, r.created_at, u.profile_picture, is_followed, is_saved, is_reported
    ORDER BY is_reported DESC, r.created_at DESC";

$stmt = $conn->prepare($sql_recipes);
$stmt->bind_param("iii", $logged_in_user_id, $logged_in_user_id, $logged_in_user_id);
$stmt->execute();
$result_recipes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taste Explorer</title>
    <link rel="stylesheet" href="styleview.css"> <!-- Link to external stylesheet -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/timeago.js/4.0.2/timeago.min.js"></script> <!-- Include timeago.js -->
    <script>
        const loggedInUsername = "<?php echo htmlspecialchars($logged_in_username); ?>";
        const loggedInUserId = <?php echo $logged_in_user_id; ?>;
    </script>
</head>
<body>
    
    <?php include 'nav.html'; ?>

    <main>
        <section class="recipes-list">
            <?php
            if ($result_recipes->num_rows > 0) {
                while ($row_recipe = $result_recipes->fetch_assoc()) {
                    echo "<div class='recipe-container'>";
                    echo "<div class='recipe' data-recipe-id='" . htmlspecialchars($row_recipe['id']) . "'>";
                    echo "<div class='recipe-header'>";
                    echo "<img src='" . htmlspecialchars($row_recipe['profile_picture']) . "' alt='Profile Picture' class='profile-pic'>";
                    echo "<div class='user-info'>";
                    echo "<p><strong><a href='profileview.php?username=" . htmlspecialchars($row_recipe['username']) . "' class='username-link' data-username='" . htmlspecialchars($row_recipe['username']) . "'>" . htmlspecialchars($row_recipe['username']) . "</a></strong></p>";
                    echo "<p class='timeago' datetime='" . htmlspecialchars($row_recipe['created_at']) . "'></p>";
                    if ($row_recipe['is_reported'] && $logged_in_user_id == 1) {
                        echo "<p class='reported-status'>Reported</p>";
                    }
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
                    echo "<p><strong>Likes:</strong> <span class='like-count'>" . htmlspecialchars($row_recipe['like_count']) . "</span></p>";
                    echo "<button class='like-button'>Like</button>";
                    echo "<button class='save-button' data-is-saved='" . htmlspecialchars($row_recipe['is_saved']) . "'>" . ($row_recipe['is_saved'] ? 'Unsave' : 'Save') . "</button>";
                    
                    if ($logged_in_user_id != 1) {
                        echo "<button class='report-button'>Report Post</button>";
                    }
                    
                    echo "<button class='hide-button'>Hide Post</button>";

                    if ($logged_in_user_id == 1) {
                        echo "<button class='delete-button'>Delete</button>";
                    }

                    $recipe_id = $row_recipe['id'];
                    $comments_query = "
                        SELECT c.comment, c.created_at, u.username, u.profile_picture
                        FROM comments c
                        JOIN users u ON c.user_id = u.id
                        WHERE c.recipe_id = ?
                        ORDER BY c.created_at DESC";
                    $stmt = $conn->prepare($comments_query);
                    $stmt->bind_param("i", $recipe_id);
                    $stmt->execute();
                    $comments_result = $stmt->get_result();

                    echo "<div class='comments-section'>";
                    echo " <button class='toggle-comments'>Comments</button>";
                    echo "<div class='comments-content' style='display: none;'>";
                    if ($comments_result->num_rows > 0) {
                        while ($comment_row = $comments_result->fetch_assoc()) {
                            $comment_profile_picture = htmlspecialchars($comment_row['profile_picture']);
                            $comment_username = htmlspecialchars($comment_row['username']);
                            $comment_text = htmlspecialchars($comment_row['comment']);
                            $comment_created_at = htmlspecialchars($comment_row['created_at']);
                
                            echo "<div class='comment'>";
                            echo "<img src='$comment_profile_picture' alt='Profile Picture' class='profile-pic'>";
                            echo "<div class='comment-text'>";
                            echo "<p><strong>$comment_username:</strong> $comment_text</p>";
                            echo "<p class='timeago' datetime='$comment_created_at'></p>";
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p>No comments yet.</p>";
                    }
                    echo "<form class='comment-form'>";
                    echo "<textarea name='comment' placeholder='Add a comment...'></textarea>";
                    echo "<button type='button' class='submit-comment'>Submit</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
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
    document.addEventListener('DOMContentLoaded', function() {
        timeago.register('custom', function(number, index, total_sec) {
            if (total_sec < 60) return ['a few seconds ago', 'in a few seconds'];
            if (total_sec < 60 * 5) return ['1 minute ago', 'in 1 minute'];
            if (total_sec < 60 * 10) return ['5 minutes ago', 'in 5 minutes'];
            if (total_sec < 60 * 15) return ['10 minutes ago', 'in 10 minutes'];
            if (total_sec < 60 * 30) return ['15 minutes ago', 'in 15 minutes'];
            if (total_sec < 60 * 60) return ['30 minutes ago', 'in 30 minutes'];
            if (total_sec < 60 * 60 * 2) return ['1 hour ago', 'in 1 hour'];
            if (total_sec < 60 * 60 * 5) return ['a few hours ago', 'in a few hours'];
            if (total_sec < 60 * 60 * 12) return ['half a day ago', 'in half a day'];
            if (total_sec < 60 * 60 * 24) return ['1 day ago', 'in 1 day'];
            if (total_sec < 60 * 60 * 24 * 2) return ['yesterday', 'tomorrow'];
            if (total_sec < 60 * 60 * 24 * 7) return ['this week', 'in this week'];
            if (total_sec < 60 * 60 * 24 * 30) return ['this month', 'in this month'];
            if (total_sec < 60 * 60 * 24 * 365) return ['this year', 'in this year'];
            return ['a long time ago', 'in a long time'];
        });

        timeago.render(document.querySelectorAll('.timeago'), 'custom');

        document.querySelectorAll('.toggle-comments').forEach(button => {
            button.addEventListener('click', () => {
                const commentsContent = button.nextElementSibling;
                commentsContent.style.display = commentsContent.style.display === 'none' ? 'block' : 'none';
            });
        });

        document.querySelectorAll('.submit-comment').forEach(button => {
        button.addEventListener('click', () => {
            const form = button.closest('.comment-form');
            const textarea = form.querySelector('textarea');
            const comment = textarea.value.trim();
            if (comment !== '') {
                const recipeId = form.closest('.recipe').dataset.recipeId;
                fetch('add_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        recipe_id: recipeId,
                        comment: comment
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Parsed response:', data);
                    if (data.status === 'success') {
                        const newComment = document.createElement('div');
                        newComment.classList.add('comment');
                        newComment.innerHTML = `
                            <img src="${data.profile_picture}" alt="Profile Picture" class="profile-pic">
                            <div class="comment-text">
                                <p><strong>${data.username}:</strong> ${data.comment_text}</p>
                                <p class="timeago" datetime="${data.comment_time}"></p>
                            </div>
                        `;
                        
                        // Insert new comment at the beginning of comments section
                        const commentsSection = form.closest('.comments-section').querySelector('.comments-content');
                        commentsSection.insertBefore(newComment, commentsSection.firstChild);

                        // Clear textarea after successful submission
                        textarea.value = '';

                        // Re-render timeago for new comment
                        timeago.render(newComment.querySelectorAll('.timeago'), 'custom');
                    } else {
                        console.error('Failed to add comment:', data.message);
                        alert('Failed to add comment: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });





        document.querySelectorAll('.like-button').forEach(button => {
            button.addEventListener('click', () => {
                const recipeId = button.closest('.recipe').dataset.recipeId;
                fetch('like_recipe.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        recipe_id: recipeId,
                        user_id: loggedInUserId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const likeCountElement = button.previousElementSibling.querySelector('.like-count');
                        likeCountElement.textContent = data.like_count;
                    } else {
                        alert('Failed to like recipe');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });

        document.querySelectorAll('.save-button').forEach(button => {
    button.addEventListener('click', () => {
        const recipeId = button.closest('.recipe').dataset.recipeId;
        const isSaved = button.dataset.isSaved === '1';
        fetch('save_recipe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                recipe_id: recipeId,
                user_id: loggedInUserId,
                action: isSaved ? 'unsave' : 'save'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.textContent = isSaved ? 'Save' : 'Unsave';
                button.dataset.isSaved = isSaved ? '0' : '1';
            } else {
                alert('Failed to update save status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});


document.querySelectorAll('.report-button').forEach(button => {
        button.addEventListener('click', () => {
            const recipeId = button.closest('.recipe').dataset.recipeId;
            fetch('report_recipe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    recipe_id: recipeId
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Report response:', data); // Debug logging
                if (data.status === 'success') {
                    button.textContent = 'Reported';
                    button.disabled = true;
                } else {
                    alert('Failed to report recipe: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });

    document.querySelectorAll('.hide-button').forEach(button => {
    button.addEventListener('click', () => {
        const recipeId = button.closest('.recipe').dataset.recipeId;

        fetch('hide_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                recipe_id: recipeId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                button.closest('.recipe-container').remove();
            } else {
                alert('Failed to hide recipe: ' + data.message);
                console.error('Hide error:', data.message);
            }
        })
        .catch(error => {
            alert('Failed to hide recipe. Please try again.');
            console.error('Hide error:', error);
        });
    });
});




        document.querySelectorAll('.delete-button').forEach(button => {
    button.addEventListener('click', () => {
        const recipeId = button.closest('.recipe').dataset.recipeId;
        fetch('delete_recipe.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                recipe_id: recipeId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                button.closest('.recipe-container').remove();
            } else {
                alert('Failed to delete recipe: ' + data.message);
                console.error('Delete error:', data.message);
            }
        })
        .catch(error => {
            alert('Failed to delete recipe. Please try again.');
            console.error('Delete error:', error);
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
