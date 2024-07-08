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
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_username = $_POST['username'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];

        // Start a transaction
        $conn->begin_transaction();

        try {
            // Update the username in the users table
            $sql_update_user = "UPDATE users SET username='$new_username', firstname='$firstname', lastname='$lastname' WHERE email='$email'";
            if ($conn->query($sql_update_user) !== TRUE) {
                throw new Exception("Error updating user record: " . $conn->error);
            }

            // Update the username in the recipes table
            $sql_update_recipes = "UPDATE recipes SET username='$new_username' WHERE username='{$_SESSION['username']}'";
            if ($conn->query($sql_update_recipes) !== TRUE) {
                throw new Exception("Error updating recipes: " . $conn->error);
            }

            // Update the following field in the follows table
            $sql_update_following = "UPDATE follows SET following='$new_username' WHERE following='{$_SESSION['username']}'";
            if ($conn->query($sql_update_following) !== TRUE) {
                throw new Exception("Error updating follows (following): " . $conn->error);
            }

            // Update the follower field in the follows table
            $sql_update_follower = "UPDATE follows SET follower='$new_username' WHERE follower='{$_SESSION['username']}'";
            if ($conn->query($sql_update_follower) !== TRUE) {
                throw new Exception("Error updating follows (follower): " . $conn->error);
            }

            // Commit the transaction
            $conn->commit();

            // Update session username
            $_SESSION['username'] = $new_username;

            // Redirect to profile page
            header("Location: profile.php");
            exit();

        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            $conn->rollback();
            echo $e->getMessage();
        }
    } else {
        $sql_user = "SELECT username, firstname, lastname FROM users WHERE email = '$email'";
        $result_user = $conn->query($sql_user);
        if ($result_user->num_rows == 1) {
            $row_user = $result_user->fetch_assoc();
            $username = $row_user['username'];
            $firstname = $row_user['firstname'];
            $lastname = $row_user['lastname'];
        }
    }
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
        <section class="edit-profile">
            <h2>Edit Profile</h2>
            <form method="post" action="edit_profile.php">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>
                
                <button type="submit">Update Profile</button>
            </form>
        </section>
    </main>
    
</body>
</html>
