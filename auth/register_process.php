<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize user inputs
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Check if user email already exists
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        echo "<script>
                alert('This email address is already registered!');
                window.location.href='register.php';
              </script>";
        exit();
    }

    // Secure password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "<script>
                alert('Account created successfully! Please login.');
                window.location.href='login.php';
              </script>";
    } else {
        echo "<script>
                alert('Error processing registration. Please try again.');
                window.location.href='register.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>