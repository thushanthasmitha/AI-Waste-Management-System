<?php
session_start();

// 1. Authentication Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'authority' && $_SESSION['role'] !== 'admin')) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

$adminName = htmlspecialchars($_SESSION['full_name'] ?? 'Admin1');

// 2. Search Logic
$searchQuery = trim($_GET['search'] ?? '');
$searchSQL = "";

if (!empty($searchQuery)) {
    $escapedSearch = mysqli_real_escape_string($conn, $searchQuery);
    $searchSQL = " AND (u.full_name LIKE '%$escapedSearch%' OR u.email LIKE '%$escapedSearch%') ";
}

// 3. Fetch Registered Citizens and their reported counts
$citizens_query = "SELECT u.id, u.full_name, u.email, u.created_at,
                   COUNT(br.id) as total_reports_submitted
                   FROM users u
                   LEFT JOIN bin_reports br ON u.id = br.user_id
                   WHERE LOWER(u.role) = 'citizen' $searchSQL
                   GROUP BY u.id, u.full_name, u.email, u.created_at
                   ORDER BY u.id DESC";

$citizens_result = mysqli_query($conn, $citizens_query);
$total_citizens = mysqli_num_rows($citizens_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Citizens | EcoPredict.ai</title>
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

        /* HEADER & SEARCH BAR */
        .action-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: #fff; padding: 15px 20px; border-radius: 12px; border: 1px solid #e1e1e1; }
        .search-box { display: flex; gap: 8px; }
        .search-box input { padding: 8px 14px; border: 1px solid #cccccc; border-radius: 6px; font-size: 13px; outline: none; width: 250px; }
        .search-box button { padding: 8px 16px; background-color: #2c3e50; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; }

        /* TABLE STYLING */
        .table-card { background-color: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #e1e1e1; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { text-align: left; padding: 12px; border-bottom: 2px solid #edf2f7; color: #7f8c8d; font-size: 13px; font-weight: 600; }
        .custom-table td { padding: 14px 12px; border-bottom: 1px solid #edf2f7; font-size: 14px; }

        .badge-count { background-color: #e8f5e9; color: #27ae60; padding: 4px 10px; border-radius: 12px; font-weight: 700; font-size: 12px; }
        .citizen-icon { width: 32px; height: 32px; background-color: #f0f4f8; color: #34495e; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 8px; font-size: 13px; }
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
            <li><a href="driver_assignments.php"><i class="fa-solid fa-truck"></i> Driver Assignments</a></li>
            <li><a href="registered_citizens.php" class="active"><i class="fa-solid fa-users"></i> Registered Citizens</a></li>
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
            <h1 style="font-size: 26px;">Registered Citizens 👥</h1>
            <p style="color: #7f8c8d;">View public users registered in the system and their bin report activity.</p>
        </div>

        <!-- ACTION HEADER -->
        <div class="action-header">
            <div style="font-weight: 600; font-size: 15px; color: #2c3e50;">
                Total Registered Citizens: <span style="color: #27ae60; font-size: 16px;"><?php echo $total_citizens; ?></span>
            </div>

            <form class="search-box" method="GET" action="registered_citizens.php">
                <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
            </form>
        </div>

        <!-- TABLE CARD -->
        <div class="table-card">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Citizen Name</th>
                        <th>Email Address</th>
                        <th>Registered Date</th>
                        <th>Reports Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($citizens_result && mysqli_num_rows($citizens_result) > 0) {
                        while ($row = mysqli_fetch_assoc($citizens_result)) {
                            $regDate = !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A';
                            ?>
                            <tr>
                                <td style="font-weight: 700;">#USR-<?php echo $row['id']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div class="citizen-icon"><i class="fa-solid fa-user"></i></div>
                                        <strong style="color: #2c3e50;"><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                    </div>
                                </td>
                                <td style="color: #555;"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td style="color: #7f8c8d; font-size: 13px;"><?php echo $regDate; ?></td>
                                <td>
                                    <span class="badge-count">
                                        <i class="fa-solid fa-file-lines" style="margin-right: 4px;"></i>
                                        <?php echo $row['total_reports_submitted']; ?> Reports
                                    </span>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #a0aec0; padding: 25px;">No registered citizens found.</td>
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