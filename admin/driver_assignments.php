<?php
session_start();

// 1. Authentication Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'authority' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

$adminName = htmlspecialchars($_SESSION['full_name'] ?? 'Admin1');

// 2. Fetch all drivers with assigned reports count & status
$drivers_query = "SELECT u.id, u.full_name, u.email, 
                  COUNT(br.id) as total_assigned,
                  SUM(CASE WHEN LOWER(br.status) = 'assigned' THEN 1 ELSE 0 END) as active_tasks,
                  SUM(CASE WHEN LOWER(br.status) IN ('resolved', 'completed') THEN 1 ELSE 0 END) as completed_tasks
                  FROM users u
                  LEFT JOIN bin_reports br ON u.id = br.assigned_driver_id
                  WHERE LOWER(u.role) = 'driver'
                  GROUP BY u.id, u.full_name, u.email";

$drivers_result = mysqli_query($conn, $drivers_query);

// 3. Fetch all assigned tasks detailed list
$assignments_query = "SELECT br.*, 
                             u.full_name as citizen_name, 
                             d.full_name as driver_name 
                      FROM bin_reports br 
                      LEFT JOIN users u ON br.user_id = u.id 
                      LEFT JOIN users d ON br.assigned_driver_id = d.id 
                      WHERE br.assigned_driver_id IS NOT NULL 
                      ORDER BY br.reported_at DESC";

$assignments_result = mysqli_query($conn, $assignments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Assignments | EcoPredict.ai</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f8; color: #2c3e50; display: flex; min-height: 100vh; }

        /* SIDEBAR STYLING */
        .dash-sidebar { width: 260px; background-color: #ffffff; height: 100vh; position: fixed; top: 0; left: 0; border-right: 1px solid #e1e1e1; display: flex; flex-direction: column; padding: 25px 20px; z-index: 1000; }
        .dash-sidebar .logo { font-size: 22px; font-weight: 700; color: #27ae60; display: flex; align-items: center; gap: 10px; margin-bottom: 40px; }
        .dash-sidebar .menu-list { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .dash-sidebar .menu-list a { display: flex; align-items: center; gap: 15px; padding: 12px 16px; color: #555555; text-decoration: none; font-size: 14px; font-weight: 500; border-radius: 8px; transition: all 0.2s; }
        .dash-sidebar .menu-list a:hover, .dash-sidebar .menu-list a.active { background-color: #e8f5e9; color: #27ae60; font-weight: 600; }
        .logout-container { margin-top: auto; padding-top: 20px; border-top: 1px solid #eeeeee; }
        .btn-logout-custom { display: flex; align-items: center; justify-content: center; gap: 10px; background-color: #e74c3c; color: #ffffff !important; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 600; }

        /* TOP NAV STYLING */
        .dash-topnav { position: fixed; top: 0; right: 0; left: 260px; height: 70px; background-color: #ffffff; border-bottom: 1px solid #e1e1e1; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; z-index: 900; }
        .user-profile { display: flex; align-items: center; gap: 12px; }
        .avatar { width: 38px; height: 38px; background-color: #2c3e50; color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        /* MAIN CONTENT STYLING */
        .dash-main { margin-left: 260px; margin-top: 70px; padding: 40px; width: calc(100% - 260px); }
        .welcome-sec { margin-bottom: 25px; }

        /* GRID FOR DRIVER CARDS */
        .driver-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .driver-card { background: #ffffff; padding: 20px; border-radius: 12px; border: 1px solid #e1e1e1; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .driver-header { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .driver-avatar { width: 45px; height: 45px; background-color: #e8f5e9; color: #27ae60; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .driver-stats { display: flex; justify-content: space-between; border-top: 1px solid #edf2f7; padding-top: 12px; margin-top: 10px; font-size: 13px; }

        /* TABLE STYLING */
        .table-card { background-color: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e1e1e1; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; color: #7f8c8d; font-size: 13px; font-weight: 600; }
        .custom-table td { padding: 14px 12px; border-bottom: 1px solid #edf2f7; font-size: 14px; }

        .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-resolved { background-color: #c8e6c9; color: #2e7d32; }
        .badge-assigned { background-color: #bbdefb; color: #1565c0; }
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
            <li><a href="../dashboard/admin_dashboard.php"><i class="fa-solid fa-chart-pie"></i> Authority Overview</a></li>
            <li><a href="bin_reports.php"><i class="fa-solid fa-dumpster"></i> Bin Reports Management</a></li>
            <li><a href="driver_assignments.php" class="active"><i class="fa-solid fa-truck"></i> Driver Assignments</a></li>
            <li><a href="registered_citizens.php"><i class="fa-solid fa-users"></i> Registered Citizens</a></li>
            <li><a href="#"><i class="fa-solid fa-brain"></i> AI Predictions</a></li>
        </ul>

        <div class="logout-container">
            <a href="../auth/logout.php" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- TOP NAV -->
    <header class="dash-topnav">
        <div style="font-size: 18px; font-weight: 600;">Municipal Authority Control Panel</div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper($adminName[0]); ?></div>
            <span style="font-weight: 600; font-size: 14px;"><?php echo $adminName; ?></span>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <div class="welcome-sec">
            <h1 style="font-size: 26px;">Driver Assignments & Workload 🚚</h1>
            <p style="color: #7f8c8d;">Track collection drivers, active assignments, and completed tasks.</p>
        </div>

        <!-- DRIVERS OVERVIEW CARDS -->
        <h2 style="font-size: 18px; margin-bottom: 15px; color: #2c3e50;">Active Drivers Summary</h2>
        <div class="driver-grid">
            <?php 
            if ($drivers_result && mysqli_num_rows($drivers_result) > 0) {
                while ($drv = mysqli_fetch_assoc($drivers_result)) {
                    ?>
                    <div class="driver-card">
                        <div class="driver-header">
                            <div class="driver-avatar"><i class="fa-solid fa-user-gear"></i></div>
                            <div>
                                <h3 style="font-size: 16px;"><?php echo htmlspecialchars($drv['full_name']); ?></h3>
                                <p style="font-size: 12px; color: #7f8c8d;"><?php echo htmlspecialchars($drv['email']); ?></p>
                            </div>
                        </div>
                        <div class="driver-stats">
                            <span>Active Tasks: <strong style="color: #e67e22;"><?php echo $drv['active_tasks']; ?></strong></span>
                            <span>Completed: <strong style="color: #27ae60;"><?php echo $drv['completed_tasks']; ?></strong></span>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p style='color: #7f8c8d;'>No drivers registered in the system.</p>";
            }
            ?>
        </div>

        <!-- ASSIGNED TASKS TABLE -->
        <div class="table-card">
            <h2 style="font-size: 18px; margin-bottom: 15px; color: #2c3e50;">Assigned Task Details</h2>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Assigned Driver</th>
                        <th>Bin Location</th>
                        <th>Waste Type</th>
                        <th>Reported Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($assignments_result && mysqli_num_rows($assignments_result) > 0) {
                        while ($row = mysqli_fetch_assoc($assignments_result)) {
                            $status = strtolower($row['status'] ?? 'assigned');
                            $badgeClass = ($status === 'resolved' || $status === 'completed') ? 'badge-resolved' : 'badge-assigned';
                            $formattedDate = date('M d, Y - h:i A', strtotime($row['reported_at']));
                            ?>
                            <tr>
                                <td style="font-weight: 700;">#REP-<?php echo $row['id']; ?></td>
                                <td><i class="fa-solid fa-truck" style="color: #3498db; margin-right: 6px;"></i> <strong><?php echo htmlspecialchars($row['driver_name']); ?></strong></td>
                                <td style="font-weight: 500;"><i class="fa-solid fa-location-dot" style="color: #e74c3c; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['bin_location']); ?></td>
                                <td><?php echo htmlspecialchars($row['waste_type'] ?? 'General'); ?></td>
                                <td style="color: #7f8c8d; font-size: 13px;"><?php echo $formattedDate; ?></td>
                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo strtoupper($status); ?></span></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #a0aec0; padding: 25px;">No driver assignments found.</td>
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