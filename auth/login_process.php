<?php
session_start();

// Include Database Connection file
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. Check if the entered email exists in the database
    $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 2. Verify if the entered password matches the hashed password
        if (password_verify($password, $user['password'])) {
            
            // Save user data in Session variables (Logged-in state)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // 3. Redirect user to the corresponding dashboard based on their role
            switch ($user['role']) {
                case 'citizen':
                    header("Location: ../dashboard/citizen_dashboard.php");
                    break;
                case 'driver':
                    header("Location: ../dashboard/driver_dashboard.php");
                    break;
                case 'authority':
                    header("Location: ../dashboard/admin_dashboard.php");
                    break;
                default:
                    header("Location: ../index.php");
                    break;
            }
            exit();

        } else {
            // Invalid password response
            echo "<script>
                    alert('Invalid Password! Please try again.');
                    window.location.href='login.php';
                  </script>";
            exit();
        }
    } else {
        // Email not found response
        echo "<script>
                alert('No account found with this email!');
                window.location.href='login.php';
              </script>";
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>