<?php
session_start();

// Check if user is logged in and if the role is 'authority' or 'admin'
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'authority' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Include database connection
require_once '../config/db.php';

// Handle Driver Assignment Submission
$assign_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_driver_submit'])) {
    $report_id = intval($_POST['report_id']);
    $driver_id = intval($_POST['driver_id']);

    if ($report_id > 0 && $driver_id > 0) {
        $update_sql = "UPDATE bin_reports SET assigned_driver_id = '$driver_id' WHERE id = '$report_id'";
        if (mysqli_query($conn, $update_sql)) {
            $assign_message = "Driver assigned successfully!";
        } else {
            $assign_message = "Error assigning driver: " . mysqli_error($conn);
        }
    }
}

// Fetch overall summary stats
$total_reports_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM bin_reports");
$total_reports = ($total_reports_res) ? mysqli_fetch_assoc($total_reports_res)['total'] : 0;

$pending_reports_res = mysqli_query($conn, "SELECT COUNT(*) as pending FROM bin_reports WHERE status = 'pending'");
$pending_reports = ($pending_reports_res) ? mysqli_fetch_assoc($pending_reports_res)['pending'] : 0;

$resolved_reports_res = mysqli_query($conn, "SELECT COUNT(*) as resolved FROM bin_reports WHERE status = 'resolved'");
$resolved_reports = ($resolved_reports_res) ? mysqli_fetch_assoc($resolved_reports_res)['resolved'] : 0;

$active_citizens_res = mysqli_query($conn, "SELECT COUNT(*) as citizens FROM users WHERE role = 'citizen'");
$active_citizens = ($active_citizens_res) ? mysqli_fetch_assoc($active_citizens_res)['citizens'] : 0;

// Fetch registered drivers for assignment dropdown
$drivers_query = "SELECT id, full_name FROM users WHERE role = 'driver' ORDER BY full_name ASC";
$drivers_result = mysqli_query($conn, $drivers_query);
$drivers_list = [];
if ($drivers_result) {
    while ($d = mysqli_fetch_assoc($drivers_result)) {
        $drivers_list[] = $d;
    }
}

// Fetch all bin reports along with Citizen and Assigned Driver details
$reports_query = "SELECT br.*, 
                         u.full_name as citizen_name, 
                         d.full_name as driver_name 
                  FROM bin_reports br 
                  LEFT JOIN users u ON br.user_id = u.id 
                  LEFT JOIN users d ON br.assigned_driver_id = d.id 
                  ORDER BY br.reported_at DESC";
$reports_result = mysqli_query($conn, $reports_query);

$adminName = htmlspecialchars($_SESSION['full_name'] ?? 'Admin1');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authority Dashboard | EcoPredict.ai</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f8;
            color: #2c3e50;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR STYLING */
        .dash-sidebar {
            width: 260px;
            background-color: #ffffff;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid #e1e1e1;
            display: flex;
            flex-direction: column;
            padding: 25px 20px;
            z-index: 1000;
        }

        .dash-sidebar .logo {
            font-size: 22px;
            font-weight: 700;
            color: #27ae60;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 40px;
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
            gap: 15px;
            padding: 12px 16px;
            color: #555555;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .dash-sidebar .menu-list a:hover,
        .dash-sidebar .menu-list a.active {
            background-color: #e8f5e9;
            color: #27ae60;
            font-weight: 600;
        }

        .dash-sidebar .menu-list a i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .logout-container {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
        }

        .btn-logout-custom {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: #e74c3c;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-logout-custom:hover {
            background-color: #c0392b;
        }

        /* TOP NAV STYLING */
        .dash-topnav {
            position: fixed;
            top: 0;
            right: 0;
            left: 260px;
            height: 70px;
            background-color: #ffffff;
            border-bottom: 1px solid #e1e1e1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            z-index: 900;
        }

        .nav-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 38px;
            height: 38px;
            background-color: #2c3e50;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* MAIN CONTENT STYLING */
        .dash-main {
            margin-left: 260px;
            margin-top: 70px;
            padding: 40px;
            width: calc(100% - 260px);
        }

        .welcome-sec {
            margin-bottom: 30px;
        }

        .welcome-sec h1 {
            font-size: 26px;
            color: #2c3e50;
        }

        /* STATS CARDS */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        .card-stat {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e1e1e1;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .card-stat .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .bg-blue { background-color: #e3f2fd; color: #1976d2; }
        .bg-orange { background-color: #fff3e0; color: #f57c00; }
        .bg-green { background-color: #e8f5e9; color: #388e3c; }
        .bg-purple { background-color: #f3e5f5; color: #7b1fa2; }

        .card-stat .val {
            font-size: 22px;
            font-weight: bold;
        }

        .card-stat .lbl {
            font-size: 13px;
            color: #7f8c8d;
        }

        /* TABLE SECTION */
        .table-card {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e1e1e1;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-header h3 {
            font-size: 18px;
            color: #2c3e50;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #edf2f7;
            color: #7f8c8d;
            font-size: 13px;
            font-weight: 600;
        }

        .custom-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-success { background-color: #c8e6c9; color: #2e7d32; }
        .badge-warning { background-color: #ffe082; color: #b77a00; }

        .btn-assign {
            padding: 7px 14px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-assign:hover {
            background-color: #2980b9;
        }

        .driver-tag {
            display: inline-block;
            background-color: #f0f4f8;
            color: #34495e;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        /* MODAL STYLING */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
        }

        .modal-card {
            background: #ffffff;
            width: 420px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
        }

        .modal-card h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .modal-card select {
            width: 100%;
            padding: 10px;
            margin: 15px 0 20px 0;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-cancel {
            padding: 8px 16px;
            background: #e0e0e0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-save {
            padding: 8px 16px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
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
            <li><a href="admin_dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Authority Overview</a></li>
            <li><a href="../admin/bin_reports.php"><i class="fa-solid fa-dumpster"></i> Bin Reports Management</a></li>
            <li><a href="../admin/driver_assignments.php"><i class="fa-solid fa-truck"></i> Driver Assignments</a></li>
            <li><a href="../admin/registered_citizens.php"><i class="fa-solid fa-users"></i> Registered Citizens</a></li>
            <li><a href="../admin/ai_predictions.php"><i class="fa-solid fa-brain"></i> AI Predictions</a></li>
        </ul>

        <div class="logout-container">
            <a href="../auth/logout.php" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- TOP NAV -->
    <header class="dash-topnav">
        <div class="nav-title">Municipal Authority Control Panel</div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper($adminName[0]); ?></div>
            <span style="font-weight: 600; font-size: 14px;"><?php echo $adminName; ?></span>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <div class="welcome-sec">
            <h1>Municipal Overview Dashboard</h1>
            <p style="color: #7f8c8d;">Monitor real-time bin collection reports and route management.</p>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-cards">
            <div class="card-stat">
                <div class="icon-box bg-blue"><i class="fa-solid fa-dumpster"></i></div>
                <div>
                    <div class="val"><?php echo $total_reports; ?></div>
                    <div class="lbl">Total Bin Reports</div>
                </div>
            </div>
            <div class="card-stat">
                <div class="icon-box bg-orange"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <div class="val"><?php echo $pending_reports; ?></div>
                    <div class="lbl">Pending Pickups</div>
                </div>
            </div>
            <div class="card-stat">
                <div class="icon-box bg-green"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="val"><?php echo $resolved_reports; ?></div>
                    <div class="lbl">Resolved Pickups</div>
                </div>
            </div>
            <div class="card-stat">
                <div class="icon-box bg-purple"><i class="fa-solid fa-users"></i></div>
                <div>
                    <div class="val"><?php echo $active_citizens; ?></div>
                    <div class="lbl">Active Citizens</div>
                </div>
            </div>
        </div>

        <!-- CITIZEN REPORTS TABLE -->
        <div class="table-card">
            <div class="table-header">
                <h3><i class="fa-solid fa-list-check" style="color: #27ae60; margin-right: 8px;"></i> All Citizen Waste Reports</h3>
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Citizen Name</th>
                        <th>Bin Location</th>
                        <th>Reported Date</th>
                        <th>Assigned Driver</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($reports_result && mysqli_num_rows($reports_result) > 0) {
                        while ($row = mysqli_fetch_assoc($reports_result)) {
                            $isResolved = (strtolower($row['status']) == 'resolved');
                            $statusClass = $isResolved ? 'badge-success' : 'badge-warning';
                            $formattedDate = date('M d, Y - h:i A', strtotime($row['reported_at']));
                            $driverName = !empty($row['driver_name']) ? htmlspecialchars($row['driver_name']) : "Not Assigned";
                            ?>
                            <tr>
                                <td>#REP-<?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['citizen_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($row['bin_location']); ?></td>
                                <td><?php echo $formattedDate; ?></td>
                                <td><span class="driver-tag"><i class="fa-solid fa-user-gear"></i> <?php echo $driverName; ?></span></td>
                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td>
                                    <?php if (!$isResolved): ?>
                                        <button class="btn-assign" onclick="openAssignModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['bin_location'], ENT_QUOTES); ?>')">
                                            <i class="fa-solid fa-user-plus"></i> Assign Driver
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #27ae60; font-size: 13px; font-weight: 600;"><i class="fa-solid fa-check"></i> Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #a0aec0;">No bin reports found.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- ASSIGN DRIVER MODAL -->
    <div class="modal-overlay" id="assignModal">
        <div class="modal-card">
            <h3>Assign Collection Driver</h3>
            <p style="font-size: 13px; color: #7f8c8d;" id="modalLocationText">Location: </p>
            
            <form method="POST" action="admin_dashboard.php">
                <input type="hidden" name="report_id" id="modalReportId">
                
                <label style="font-size: 13px; font-weight: 600; margin-top: 15px; display: block;">Select Driver:</label>
                <select name="driver_id" required>
                    <option value="">-- Choose a Driver --</option>
                    <?php foreach ($drivers_list as $driver): ?>
                        <option value="<?php echo $driver['id']; ?>"><?php echo htmlspecialchars($driver['full_name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeAssignModal()">Cancel</button>
                    <button type="submit" name="assign_driver_submit" class="btn-save">Assign Now</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAssignModal(reportId, location) {
            document.getElementById('modalReportId').value = reportId;
            document.getElementById('modalLocationText').innerText = "Location: " + location;
            document.getElementById('assignModal').style.display = 'flex';
        }

        function closeAssignModal() {
            document.getElementById('assignModal').style.display = 'none';
        }
    </script>

</body>
</html>