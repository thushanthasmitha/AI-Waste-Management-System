<?php
// Database configuration details
$host = "localhost";
$port = 3307; // Custom XAMPP MySQL port
$user = "root";
$pass = "";
$dbname = "ecopredict_db";

// Create database connection using MySQLi
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Check if connection was successful
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>