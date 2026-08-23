<?php
session_start();

// Authentication Check for Driver
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

$driverId = $_SESSION['user_id'];
$driverName = htmlspecialchars($_SESSION['full_name'] ?? 'Driver');

// Fetch only RESOLVED/COMPLETED pickups for this driver
$history_query = "SELECT br.*, u.full_name as citizen_name 
                 FROM bin_reports br 
                 LEFT JOIN users u ON br.user_id = u.id 
                 WHERE br.assigned_driver_id = '$driverId' 
                 AND LOWER(br.status) IN ('resolved', 'completed')
                 ORDER BY br.reported_at DESC";

$history_result = mysqli_query($conn, $history_query);
$completed_count = mysqli_num_rows($history_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup History | EcoPredict.ai</title>
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
        .avatar { width: 38px; height: 38px; background-color: #d35400; color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        .dash-main { margin-left: 260px; margin-top: 70px; padding: 40px; width: calc(100% - 260px); }
        .table-card { background-color: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e1e1e1; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; color: #7f8c8d; font-size: 13px; font-weight: 600; }
        .custom-table td { padding: 14px 12px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        .badge-resolved { background-color: #c8e6c9; color: #2e7d32; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; }
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
            <li><a href="driver_dashboard.php"><i class="fa-solid fa-truck"></i> Driver Pickups</a></li>
            <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Route Navigation</a></li>
            <li><a href="pickup_history.php" class="active"><i class="fa-solid fa-clock-rotate-left"></i> Pickup History</a></li>
        </ul>

        <div class="logout-container">
            <a href="../auth/logout.php" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- TOP NAV -->
    <header class="dash-topnav">
        <div style="font-size: 18px; font-weight: 600;">Collection Driver Portal</div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper($driverName[0]); ?></div>
            <span style="font-weight: 600; font-size: 14px;"><?php echo $driverName; ?></span>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <div style="margin-bottom: 25px;">
            <h1 style="font-size: 26px;">Completed Pickup History 📜</h1>
            <p style="color: #7f8c8d;">Review all the bin overflow tasks you have successfully collected and resolved.</p>
        </div>

        <div class="table-card">
            <h2 style="font-size: 17px; margin-bottom: 15px; color: #2c3e50;">
                Total Pickups Completed: <span style="color: #27ae60;"><?php echo $completed_count; ?></span>
            </h2>
            
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Bin Location</th>
                        <th>Waste Type</th>
                        <th>Completed Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($completed_count > 0) {
                        while ($row = mysqli_fetch_assoc($history_result)) {
                            $formattedDate = date('M d, Y - h:i A', strtotime($row['reported_at']));
                            ?>
                            <tr>
                                <td style="font-weight: 700;">#TASK-<?php echo $row['id']; ?></td>
                                <td><i class="fa-solid fa-location-dot" style="color: #e74c3c; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['bin_location']); ?></td>
                                <td><?php echo htmlspecialchars($row['waste_type'] ?? 'General'); ?></td>
                                <td style="color: #7f8c8d; font-size: 13px;"><?php echo $formattedDate; ?></td>
                                <td><span class="badge-resolved">COMPLETED</span></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #a0aec0; padding: 25px;">No completed pickup history found.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>