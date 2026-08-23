<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$msg = "";
$msgType = "";

// Handle Profile Update Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    if (!empty($fullName) && !empty($email)) {
        $update_query = "UPDATE users SET full_name = '$fullName', email = '$email' WHERE id = '$userId'";
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['full_name'] = $fullName;
            $msg = "Profile updated successfully!";
            $msgType = "success";
        } else {
            $msg = "Failed to update profile.";
            $msgType = "error";
        }
    } else {
        $msg = "All fields are required.";
        $msgType = "error";
    }
}

// Fetch Latest User Details
$user_query = "SELECT * FROM users WHERE id = '$userId'";
$user_result = mysqli_query($conn, $user_query);
$userData = mysqli_fetch_assoc($user_result);

$userName = htmlspecialchars($_SESSION['full_name'] ?? 'Citizen');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | EcoPredict.ai</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f8; color: #2c3e50; display: flex; min-height: 100vh; }

        .dash-sidebar { width: 260px; background-color: #ffffff; height: 100vh; position: fixed; top: 0; left: 0; border-right: 1px solid #e1e1e1; display: flex; flex-direction: column; padding: 25px 20px; z-index: 1000; }
        .dash-sidebar .logo { font-size: 22px; font-weight: 700; color: #27ae60; display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .dash-sidebar .menu-list { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .dash-sidebar .menu-list a { display: flex; align-items: center; gap: 15px; padding: 12px 16px; color: #555555; text-decoration: none; font-size: 14px; font-weight: 500; border-radius: 8px; transition: all 0.2s; }
        .dash-sidebar .menu-list a:hover, .dash-sidebar .menu-list a.active { background-color: #e8f5e9; color: #27ae60; font-weight: 600; }
        .logout-container { margin-top: auto; padding-top: 20px; border-top: 1px solid #eeeeee; }
        .btn-logout-custom { display: flex; align-items: center; justify-content: center; gap: 10px; background-color: #e74c3c; color: #ffffff !important; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 600; }

        .dash-topnav { position: fixed; top: 0; right: 0; left: 260px; height: 70px; background-color: #ffffff; border-bottom: 1px solid #e1e1e1; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; z-index: 900; }
        .user-profile { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 38px; height: 38px; background-color: #27ae60; color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        .dash-main { margin-left: 260px; margin-top: 70px; padding: 40px; width: calc(100% - 260px); }
        .profile-card { background: #ffffff; padding: 30px; border-radius: 12px; border: 1px solid #e1e1e1; max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #cccccc; border-radius: 6px; font-size: 14px; outline: none; }
        .btn-save { background-color: #27ae60; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="dash-sidebar">
        <div class="logo">
            <i class="fa-solid fa-leaf"></i>
            <span>EcoPredict<span style="color:#2c3e50">.ai</span></span>
        </div>

        <ul class="menu-list">
            <li><a href="../dashboard/citizen_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="report_bin.php"><i class="fa-solid fa-dumpster"></i> Report Bin Full</a></li>
            <li><a href="pickup_schedule.php"><i class="fa-solid fa-calendar-days"></i> Pickup Schedule</a></li>
            <li><a href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li><a href="my_profile.php" class="active"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
        </ul>

        <div class="logout-container">
            <a href="../auth/logout.php" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- TOP NAV -->
    <header class="dash-topnav">
        <div style="font-size: 18px; font-weight: 600;">Citizen Portal</div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper($userName[0]); ?></div>
            <span style="font-weight: 600; font-size: 14px;"><?php echo $userName; ?></span>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 26px;">My Profile Settings 👤</h1>
            <p style="color: #7f8c8d;">Manage your account details and contact information.</p>
        </div>

        <div class="profile-card">
            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?php echo $msgType; ?>">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="my_profile.php">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Account Role</label>
                    <input type="text" value="Citizen" disabled style="background-color: #f8f9fa; color: #7f8c8d;">
                </div>

                <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </form>
        </div>
    </main>

</body>
</html>