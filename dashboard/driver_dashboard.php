<?php
session_start();

// Check if user is logged in and if the role is 'driver'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

// Include database connection
require_once '../config/db.php';

$driver_id = $_SESSION['user_id'];
$driverName = htmlspecialchars($_SESSION['full_name'] ?? 'Driver1');

// Handle status update (Mark as Completed / Resolved)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'mark_collected') {
    $report_id = intval($_POST['report_id']);
    
    if ($report_id > 0) {
        // Update report status to 'resolved' for this assigned driver
        $update_sql = "UPDATE bin_reports SET status = 'resolved' WHERE id = '$report_id' AND assigned_driver_id = '$driver_id'";
        if (mysqli_query($conn, $update_sql)) {
            // Success: Redirect back to prevent infinite loading / form resubmission
            header("Location: driver_dashboard.php?status=success");
            exit();
        }
    }
}

// Fetch stats specific to this logged-in driver
$pending_count_res = mysqli_query($conn, "SELECT COUNT(*) as pending FROM bin_reports WHERE assigned_driver_id = '$driver_id' AND status = 'pending'");
$pending_count = ($pending_count_res) ? mysqli_fetch_assoc($pending_count_res)['pending'] : 0;

$completed_count_res = mysqli_query($conn, "SELECT COUNT(*) as completed FROM bin_reports WHERE assigned_driver_id = '$driver_id' AND status = 'resolved'");
$completed_count = ($completed_count_res) ? mysqli_fetch_assoc($completed_count_res)['completed'] : 0;

// Fetch tasks specifically assigned to THIS logged-in driver
$tasks_query = "SELECT * FROM bin_reports WHERE assigned_driver_id = '$driver_id' ORDER BY reported_at DESC";
$tasks_result = mysqli_query($conn, $tasks_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard | EcoPredict.ai</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #334155;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR STYLING */
        .dash-sidebar {
            width: 240px;
            background-color: #ffffff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
            z-index: 1000;
        }

        .dash-sidebar .logo {
            font-size: 20px;
            font-weight: 700;
            color: #16a34a;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 32px;
            padding-left: 8px;
        }

        .dash-sidebar .menu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .dash-sidebar .menu-list a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .dash-sidebar .menu-list a:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .dash-sidebar .menu-list a.active {
            background-color: #f0fdf4;
            color: #16a34a;
            font-weight: 600;
        }

        .logout-container {
            margin-top: auto;
        }

        .btn-logout-custom {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #ef4444;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .btn-logout-custom:hover {
            background-color: #dc2626;
        }

        /* TOP NAV STYLING */
        .dash-topnav {
            position: fixed;
            top: 0;
            right: 0;
            left: 240px;
            height: 70px;
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            z-index: 900;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            background-color: #ea580c;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        /* MAIN CONTENT STYLING */
        .dash-main {
            margin-left: 240px;
            margin-top: 70px;
            padding: 40px;
            width: calc(100% - 240px);
        }

        .welcome-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .welcome-header p {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 28px;
        }

        /* STATS CARDS */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 32px;
            max-width: 750px;
        }

        .card-stat {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
        }

        .card-stat .icon-box {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .bg-pending { background-color: #fef2f2; color: #ef4444; }
        .bg-completed { background-color: #f0fdf4; color: #16a34a; }

        .card-stat .val {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .card-stat .lbl {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
        }

        /* TABLE SECTION */
        .table-card {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
        }

        .table-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            text-align: left;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
        }

        .custom-table td {
            padding: 16px;
            border-bottom: 1px solid #f8fafc;
            font-size: 13px;
            color: #334155;
        }

        /* BADGES & BUTTONS */
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .badge-pending { background-color: #fef3c7; color: #d97706; }
        .badge-resolved { background-color: #dcfce7; color: #15803d; }

        .btn-collect {
            background-color: #16a34a;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.2s;
        }

        .btn-collect:hover {
            background-color: #15803d;
        }

        .btn-completed {
            background-color: #cbd5e1;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="dash-sidebar">
        <div class="logo">
            <i class="fa-solid fa-truck-pickup"></i>
            <span>EcoPredict<span style="color:#0f172a">.ai</span></span>
        </div>

        <ul class="menu-list">
            <li><a href="#" class="active"><i class="fa-solid fa-truck-pickup"></i> Driver Pickups</a></li>
            <li><a href="route_navigation.php"><i class="fa-solid fa-map-location-dot"></i> Route Navigation</a></li>
            <li><a href="pickup_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Pickup History</a></li>
        </ul>

        <div class="logout-container">
            <a href="../auth/logout.php" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- TOP NAV -->
    <header class="dash-topnav">
        <div style="font-weight: 600; font-size: 14px; color: #334155;">Collection Driver Portal</div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper($driverName[0]); ?></div>
            <span style="font-weight: 600; font-size: 14px; color: #0f172a;"><?php echo $driverName; ?></span>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <div class="welcome-header">
            <h1>Welcome back, <?php echo $driverName; ?>! 🚚</h1>
            <p>Manage bin collection routes and update pickup completion statuses.</p>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-cards">
            <div class="card-stat">
                <div class="icon-box bg-pending"><i class="fa-solid fa-trash"></i></div>
                <div>
                    <div class="val"><?php echo $pending_count; ?></div>
                    <div class="lbl">Pending Pickups</div>
                </div>
            </div>
            <div class="card-stat">
                <div class="icon-box bg-completed"><i class="fa-solid fa-check"></i></div>
                <div>
                    <div class="val"><?php echo $completed_count; ?></div>
                    <div class="lbl">Completed Pickups</div>
                </div>
            </div>
        </div>

        <!-- ASSIGNED TASKS TABLE -->
        <div class="table-card">
            <div class="table-title">
                <i class="fa-solid fa-location-dot" style="color: #ea580c;"></i> Assigned Bin Collection Tasks
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Bin Location</th>
                        <th>Reported Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($tasks_result && mysqli_num_rows($tasks_result) > 0) {
                        while ($task = mysqli_fetch_assoc($tasks_result)) {
                            $isResolved = (strtolower($task['status']) == 'resolved');
                            $statusClass = $isResolved ? 'badge-resolved' : 'badge-pending';
                            $formattedDate = date('M d, Y - h:i A', strtotime($task['reported_at']));
                            ?>
                            <tr>
                                <td style="font-weight: 500;">#TASK-<?php echo $task['id']; ?></td>
                                <td>
                                    <i class="fa-solid fa-location-dot" style="color: #ef4444; margin-right: 4px;"></i> 
                                    <?php echo htmlspecialchars($task['bin_location']); ?>
                                </td>
                                <td style="color: #64748b;"><?php echo $formattedDate; ?></td>
                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo strtoupper($task['status']); ?></span></td>
                                <td>
                                    <?php if (!$isResolved): ?>
                                        <form method="POST" style="display:inline;" onsubmit="this.querySelector('button').disabled = true;">
                                            <input type="hidden" name="action_type" value="mark_collected">
                                            <input type="hidden" name="report_id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="btn-collect"><i class="fa-solid fa-check"></i> Mark Collected</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn-completed" disabled><i class="fa-solid fa-check-double"></i> Completed</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">No bin collection tasks assigned to you yet.</td>
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