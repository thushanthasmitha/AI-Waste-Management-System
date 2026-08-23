<?php
session_start();

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

$userId = $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['full_name'] ?? 'Citizen');

// Fetch Resolved or Assigned reports as notifications for this user
$notif_query = "SELECT br.*, d.full_name as driver_name 
               FROM bin_reports br 
               LEFT JOIN users d ON br.assigned_driver_id = d.id 
               WHERE br.user_id = '$userId' 
               ORDER BY br.reported_at DESC";

$notif_result = mysqli_query($conn, $notif_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | EcoPredict.ai</title>
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
        .notif-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e1e1e1; margin-bottom: 15px; display: flex; align-items: center; gap: 20px; }
        .notif-icon { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .bg-resolved { background-color: #e8f5e9; color: #27ae60; }
        .bg-assigned { background-color: #e3f2fd; color: #1976d2; }
        .bg-pending { background-color: #fff3e0; color: #f57c00; }
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
            <li><a href="notifications.php" class="active"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li><a href="my_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
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
            <h1 style="font-size: 26px;">Notifications 🔔</h1>
            <p style="color: #7f8c8d;">Updates on your bin overflow reports and collection status.</p>
        </div>

        <div style="max-width: 800px;">
            <?php 
            if ($notif_result && mysqli_num_rows($notif_result) > 0) {
                while ($row = mysqli_fetch_assoc($notif_result)) {
                    $status = strtolower($row['status']);
                    $formattedDate = date('M d, Y - h:i A', strtotime($row['reported_at']));

                    if ($status === 'resolved' || $status === 'completed') {
                        $iconClass = "fa-solid fa-circle-check";
                        $bgClass = "bg-resolved";
                        $title = "Bin Issue Resolved!";
                        $msg = "Your report for <strong>" . htmlspecialchars($row['bin_location']) . "</strong> has been collected and resolved.";
                    } elseif (!empty($row['driver_name'])) {
                        $iconClass = "fa-solid fa-truck";
                        $bgClass = "bg-assigned";
                        $title = "Driver Assigned";
                        $msg = "Driver <strong>" . htmlspecialchars($row['driver_name']) . "</strong> has been assigned to your report at <strong>" . htmlspecialchars($row['bin_location']) . "</strong>.";
                    } else {
                        $iconClass = "fa-solid fa-clock";
                        $bgClass = "bg-pending";
                        $title = "Report Submitted";
                        $msg = "Your report for <strong>" . htmlspecialchars($row['bin_location']) . "</strong> is currently pending authority review.";
                    }
                    ?>
                    <div class="notif-card">
                        <div class="notif-icon <?php echo $bgClass; ?>">
                            <i class="<?php echo $iconClass; ?>"></i>
                        </div>
                        <div style="flex-grow: 1;">
                            <h4 style="font-size: 15px; color: #2c3e50; margin-bottom: 4px;"><?php echo $title; ?></h4>
                            <p style="font-size: 13px; color: #555;"><?php echo $msg; ?></p>
                            <span style="font-size: 11px; color: #95a5a6; margin-top: 5px; display: block;"><?php echo $formattedDate; ?></span>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='color: #7f8c8d;'>No notifications available yet.</p>";
            }
            ?>
        </div>
    </main>

</body>
</html>