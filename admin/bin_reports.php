<?php
session_start();

// 1. Authentication Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'authority' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

$adminName = htmlspecialchars($_SESSION['full_name'] ?? 'Admin1');

// 2. Setup Search & Filter Logic
$filterStatus = strtolower($_GET['status'] ?? 'all');
$searchQuery = trim($_GET['search'] ?? '');

$whereConditions = [];

if ($filterStatus !== 'all') {
    $whereConditions[] = "LOWER(br.status) = '" . mysqli_real_escape_string($conn, $filterStatus) . "'";
}

if (!empty($searchQuery)) {
    $escapedSearch = mysqli_real_escape_string($conn, $searchQuery);
    $whereConditions[] = "(br.bin_location LIKE '%$escapedSearch%' OR u.full_name LIKE '%$escapedSearch%' OR br.waste_type LIKE '%$escapedSearch%')";
}

$whereSQL = "";
if (count($whereConditions) > 0) {
    $whereSQL = "WHERE " . implode(' AND ', $whereConditions);
}

// 3. Stats for Filter Header
$total_reports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM bin_reports"))['t'] ?? 0;
$pending_reports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as p FROM bin_reports WHERE LOWER(status) = 'pending'"))['p'] ?? 0;
$assigned_reports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as a FROM bin_reports WHERE LOWER(status) = 'assigned'"))['a'] ?? 0;
$resolved_reports = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as r FROM bin_reports WHERE LOWER(status) IN ('resolved', 'completed')"))['r'] ?? 0;

// 4. Fetch Reports with Filter
$reports_query = "SELECT br.*, 
                         u.full_name as citizen_name, 
                         d.full_name as driver_name 
                  FROM bin_reports br 
                  LEFT JOIN users u ON br.user_id = u.id 
                  LEFT JOIN users d ON br.assigned_driver_id = d.id 
                  $whereSQL 
                  ORDER BY br.reported_at DESC";

$reports_result = mysqli_query($conn, $reports_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bin Reports Management | EcoPredict.ai</title>
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

        /* FILTER BAR */
        .filter-container { background-color: #ffffff; padding: 16px 20px; border-radius: 12px; border: 1px solid #e1e1e1; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 15px; flex-wrap: wrap; }
        .filter-pills { display: flex; gap: 10px; }
        .pill-btn { padding: 8px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 600; color: #555; background-color: #f0f4f8; transition: all 0.2s; }
        .pill-btn:hover { background-color: #e2e8f0; }
        .pill-btn.active { background-color: #27ae60; color: #ffffff; }

        .search-box { display: flex; gap: 8px; }
        .search-box input { padding: 8px 14px; border: 1px solid #cccccc; border-radius: 6px; font-size: 13px; outline: none; width: 220px; }
        .search-box button { padding: 8px 16px; background-color: #2c3e50; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }

        /* TABLE STYLING */
        .table-card { background-color: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e1e1e1; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; color: #7f8c8d; font-size: 13px; font-weight: 600; }
        .custom-table td { padding: 14px 12px; border-bottom: 1px solid #edf2f7; font-size: 14px; }
        
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-resolved { background-color: #c8e6c9; color: #2e7d32; }
        .badge-assigned { background-color: #bbdefb; color: #1565c0; }
        .badge-pending { background-color: #ffe082; color: #b77a00; }
        .badge-type { background-color: #eceff1; color: #455a64; font-weight: 600; text-transform: capitalize; }
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
            <li><a href="bin_reports.php" class="active"><i class="fa-solid fa-dumpster"></i> Bin Reports Management</a></li>
            <li><a href="driver_assignments.php"><i class="fa-solid fa-truck"></i> Driver Assignments</a></li>
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
            <h1 style="font-size: 26px;">Bin Reports Detailed Archive 🗑️</h1>
            <p style="color: #7f8c8d;">Search, filter, and inspect detailed bin overflow reports submitted by citizens.</p>
        </div>

        <!-- FILTER & SEARCH BAR -->
        <div class="filter-container">
            <div class="filter-pills">
                <a href="bin_reports.php?status=all" class="pill-btn <?php echo $filterStatus === 'all' ? 'active' : ''; ?>">All Reports (<?php echo $total_reports; ?>)</a>
                <a href="bin_reports.php?status=pending" class="pill-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">Pending (<?php echo $pending_reports; ?>)</a>
                <a href="bin_reports.php?status=assigned" class="pill-btn <?php echo $filterStatus === 'assigned' ? 'active' : ''; ?>">Assigned (<?php echo $assigned_reports; ?>)</a>
                <a href="bin_reports.php?status=resolved" class="pill-btn <?php echo $filterStatus === 'resolved' ? 'active' : ''; ?>">Resolved (<?php echo $resolved_reports; ?>)</a>
            </div>

            <form class="search-box" method="GET" action="bin_reports.php">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
                <input type="text" name="search" placeholder="Search location or citizen..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>
        </div>

        <!-- DETAILED TABLE -->
        <div class="table-card">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Citizen Name</th>
                        <th>Bin Location</th>
                        <th>Waste Type</th>
                        <th>Description</th>
                        <th>Reported Date</th>
                        <th>Assigned Driver</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($reports_result && mysqli_num_rows($reports_result) > 0) {
                        while ($row = mysqli_fetch_assoc($reports_result)) {
                            $status = strtolower($row['status'] ?? 'pending');
                            
                            $badgeClass = 'badge-pending';
                            if ($status === 'resolved' || $status === 'completed') {
                                $badgeClass = 'badge-resolved';
                            } elseif ($status === 'assigned') {
                                $badgeClass = 'badge-assigned';
                            }

                            $formattedDate = date('M d, Y - h:i A', strtotime($row['reported_at']));
                            $driverName = !empty($row['driver_name']) ? htmlspecialchars($row['driver_name']) : "Not Assigned";
                            ?>
                            <tr>
                                <td style="font-weight: 700;">#REP-<?php echo $row['id']; ?></td>
                                <td><i class="fa-solid fa-user" style="color: #95a5a6; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['citizen_name'] ?? 'Unknown'); ?></td>
                                <td style="font-weight: 500;"><i class="fa-solid fa-location-dot" style="color: #e74c3c; margin-right: 5px;"></i> <?php echo htmlspecialchars($row['bin_location']); ?></td>
                                <td><span class="badge badge-type"><?php echo htmlspecialchars($row['waste_type'] ?? 'General'); ?></span></td>
                                <td style="color: #7f8c8d; font-size: 13px;"><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                                <td style="color: #7f8c8d; font-size: 13px;"><?php echo $formattedDate; ?></td>
                                <td><span style="font-weight: 600; font-size: 13px; color: #34495e;"><i class="fa-solid fa-truck" style="color: #3498db; margin-right: 4px;"></i> <?php echo $driverName; ?></span></td>
                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo strtoupper($status); ?></span></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #a0aec0; padding: 25px;">No matching bin reports found.</td>
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