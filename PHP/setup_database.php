<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 


$conn = new mysqli($servername, $username, $password);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql_create_db = "CREATE DATABASE IF NOT EXISTS user_db";
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}


$conn->select_db("user_db");


$sql_create_users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_users_table) === TRUE) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating table 'users': " . $conn->error . "<br>";
}


$sql_create_recipes_table = "CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    ingredients TEXT NOT NULL,
    instructions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_create_recipes_table) === TRUE) {
    echo "Table 'recipes' created successfully<br>";
} else {
    echo "Error creating table 'recipes': " . $conn->error . "<br>";
}


$conn->close();
?>
