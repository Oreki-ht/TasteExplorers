<?php
$conn = new mysqli('localhost', 'root', '', 'taste_explorers');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $ingredients = $conn->real_escape_string($_POST['ingredients']);
    $instructions = $conn->real_escape_string($_POST['instructions']);
    $author = $conn->real_escape_string($_POST['author']);

    $sql = "INSERT INTO recipes (title, ingredients, instructions, author) VALUES ('$title', '$ingredients', '$instructions', '$author')";

    if ($conn->query($sql) === TRUE) {
        echo "New recipe posted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
<a href="post_recipe.html">Back to post recipe</a>
