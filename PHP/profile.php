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

    
    $sql_user = "SELECT username, profile_picture, bio FROM users WHERE email = '$email'";
    $result_user = $conn->query($sql_user);

    if ($result_user->num_rows == 1) {
        $row_user = $result_user->fetch_assoc();
        $username = $row_user['username'];
        $profile_picture = $row_user['profile_picture'] ?? 'uploads/default-profile.png';
        $bio = $row_user['bio'] ?? '';
    } else {
        $username = "Unknown";
        $profile_picture = 'uploads/default-profile.png';
        $bio = "";
    }

    
    if (isset($_POST['change'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        
        if ($_FILES["profile_picture"]["size"] > 500000) { 
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        
        } else {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                
                $sql_update = "UPDATE users SET profile_picture = '$target_file' WHERE email = '$email'";
                if ($conn->query($sql_update) === TRUE) {
                    $profile_picture = $target_file;
                } else {
                    echo "Error updating record: " . $conn->error;
                }
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    
    if (isset($_POST['update_bio'])) {
        $new_bio = $conn->real_escape_string($_POST['bio']);
        $sql_update_bio = "UPDATE users SET bio = '$new_bio' WHERE email = '$email'";
        if ($conn->query($sql_update_bio) === TRUE) {
            $bio = $new_bio;
        } else {
            echo "Error updating bio: " . $conn->error;
        }
    }

    
    if (isset($_POST['delete_post'])) {
        $delete_post_id = $conn->real_escape_string($_POST['delete_post_id']);
        $sql_delete_post = "DELETE FROM recipes WHERE id = '$delete_post_id' AND username = '$username'";
        if ($conn->query($sql_delete_post) === TRUE) {
            echo "<p>Post deleted successfully.</p>";
            
            header("Refresh:0");
        } else {
            echo "Error deleting post: " . $conn->error;
        }
    }

    
    $sql_recipes = "SELECT id, title, description, ingredients, instructions, created_at FROM recipes WHERE username = '$username'";
    $result_recipes = $conn->query($sql_recipes);

    
    $sql_followers = "SELECT COUNT(*) AS followers FROM follows WHERE following = '$username'";
    $result_followers = $conn->query($sql_followers);
    $followers_count = ($result_followers->num_rows == 1) ? $result_followers->fetch_assoc()['followers'] : 0;

    $sql_following = "SELECT COUNT(*) AS following FROM follows WHERE follower = '$username'";
    $result_following = $conn->query($sql_following);
    $following_count = ($result_following->num_rows == 1) ? $result_following->fetch_assoc()['following'] : 0;
} else {
    
    header("Location: login.html");
    exit();
}


if (isset($_POST['logout'])) {
    
    session_unset();

    
    session_destroy();

    
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
        <section class="profile-header">
            <div class="profile-pic-container">
                <div class="profile-pic">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" onerror="this.onerror=null; this.src='default-profile.png';" alt="Profile Picture">
                </div>
            </div>
            <div class="profile-details">
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <div class="profile-stats">
                    <div><strong><?php echo $result_recipes->num_rows; ?></strong> posts</div>
                    <div><strong><?php echo $followers_count; ?></strong> <a href="followers.php" style="color: black; text-decoration: none;">followers</a></div>
                    <div><strong><?php echo $following_count; ?></strong> <a href="following.php" style="color: black; text-decoration: none;">following</a></div>
                </div>
                <p class="bio"><?php echo nl2br(htmlspecialchars($bio)); ?></p>
                <?php if (!empty($bio)): ?>
                    <button class="add-bio-button">Change Bio</button>
                <?php else: ?>
                    <button class="add-bio-button">Add Bio</button>
                <?php endif; ?>
                <form method="post" action="" class="update-bio-form">
                    <textarea name="bio" rows="3"><?php echo htmlspecialchars($bio); ?></textarea>
                    <button type="submit" name="update_bio">Update Bio</button>
                </form>
                <form method="post" action="" enctype="multipart/form-data" class="change-image-form">
                    <input type="file" name="profile_picture" accept="image/*">
                    <button type="submit" name="change">Change Picture</button>
                </form>
                <button onclick="document.querySelector('.change-image-form').classList.toggle('show')">Change Image</button>
                
                <a href="edit_profile.php"><button>Edit Profile</button></a>
                <a href="saved_recipes.php"><button>Saved Message</button></a>
                <form method="post" action="">
                    <button type="submit" name="logout">Logout</button>
                </form>
            </div>
        </section>
        <section class="profile-recipes">
            <h3>Your Recipes</h3>
            <div class="recipe-grid">
                <?php
                if ($result_recipes->num_rows > 0) {
                    while ($row_recipe = $result_recipes->fetch_assoc()) {
                        echo "<div class='recipe'>";
                        echo "<h4>" . htmlspecialchars($row_recipe['title']) . "</h4>";
                        echo "<p><strong>Description:</strong> " . htmlspecialchars($row_recipe['description']) . "</p>";
                        echo "<p><strong>Ingredients:</strong> " . htmlspecialchars($row_recipe['ingredients']) . "</p>";
                        echo "<p><strong>Instructions:</strong> " . htmlspecialchars($row_recipe['instructions']) . "</p>";
                        echo "<p><strong>Posted on:</strong> " . htmlspecialchars($row_recipe['created_at']) . "</p>";
                        echo "<form method='post' action=''>";
                        echo "<input type='hidden' name='delete_post_id' value='" . $row_recipe['id'] . "'>";
                        echo "<button type='submit' name='delete_post'>Delete Post</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No recipes posted yet.</p>";
                }
                ?>
            </div>
        </section>
    </main>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.add-bio-button').addEventListener('click', function() {
            document.querySelector('.update-bio-form').classList.toggle('show');
        });
    });
    </script>
</body>
</html>


