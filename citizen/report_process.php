<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $bin_location = trim($_POST['bin_location']);
    $waste_type = trim($_POST['waste_type']);
    $description = trim($_POST['description']);

    // Insert bin report into database
    $stmt = $conn->prepare("INSERT INTO bin_reports (user_id, bin_location, waste_type, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $bin_location, $waste_type, $description);

    if ($stmt->execute()) {
        echo "<script>
                alert('Bin report submitted successfully!');
                window.location.href='../dashboard/citizen_dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Failed to submit report. Please try again.');
                window.location.href='report_bin.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>