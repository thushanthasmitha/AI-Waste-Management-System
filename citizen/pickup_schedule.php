<?php
session_start();

// Check if user is logged in and if the role is 'citizen'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db.php';

$citizenName = htmlspecialchars($_SESSION['full_name'] ?? 'Citizen');

// Fetching collection schedules dynamically from existing bin_reports table
$query = "SELECT bin_location, waste_type, status, reported_at FROM bin_reports ORDER BY reported_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Schedule | EcoPredict.ai</title>
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
            background-color: #16a34a;
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

        /* SCHEDULE TABLE STYLING */
        .table-card {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
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

        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .badge-active { background-color: #dcfce7; color: #15803d; }
        .badge-scheduled { background-color: #e0f2fe; color: #0369a1; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="dash-sidebar">
        <div class="logo">
            <i class="fa-solid fa-leaf"></i>
            <span>EcoPredict<span style="color:#0f172a">.ai</span></span>
        </div>

        <ul class="menu-list">
            <li><a href="../dashboard/citizen_dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
            <li><a href="report_bin.php"><i class="fa-solid fa-trash"></i> Report Bin Full</a></li>
            <li><a href="pickup_schedule.php" class="active"><i class="fa-solid fa-calendar-days"></i> Pickup Schedule</a></li>
            <li><a href="#"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li><a href="#"><i class="fa-solid fa-user"></i> My Profile</a></li>
        </ul>

        <div class="logout-container">
            <a href="../auth/logout.php" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- TOP NAV -->
    <header class="dash-topnav">
        <div style="font-weight: 600; font-size: 14px; color: #334155;">Citizen Service Portal</div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper($citizenName[0]); ?></div>
            <span style="font-weight: 600; font-size: 14px; color: #0f172a;"><?php echo $citizenName; ?></span>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <div class="welcome-header">
            <h1>Municipal Waste Collection Schedule 🗓️</h1>
            <p>View regular waste collection days and times for your area.</p>
        </div>

        <!-- SCHEDULE TABLE -->
        <div class="table-card">
            <div class="table-title">
                <i class="fa-solid fa-clock" style="color: #16a34a;"></i> Area Collection Timetable
            </div>
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Area / Zone</th>
                        <th>Collection Days</th>
                        <th>Time Window</th>
                        <th>Waste Category</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $isResolved = (strtolower($row['status']) === 'resolved');
                            $badgeClass = $isResolved ? 'badge-active' : 'badge-scheduled';
                            $statusLabel = $isResolved ? 'CONFIRMED' : 'SCHEDULED';
                            
                            // Reported date එක පෙනෙන ආකාරයට format කිරීම
                            $pickupDay = date('l (M d, Y)', strtotime($row['reported_at']));
                            ?>
                            <tr>
                                <td style="font-weight: 600;">
                                    <i class="fa-solid fa-location-dot" style="color: #ef4444; margin-right: 6px;"></i>
                                    <?php echo htmlspecialchars($row['bin_location']); ?>
                                </td>
                                <td style="font-weight: 500; color: #0f172a;"><?php echo $pickupDay; ?></td>
                                <td style="color: #64748b;">08:00 AM - 12:00 PM</td>
                                <td>
                                    <i class="fa-solid fa-dumpster" style="color: #ea580c; margin-right: 4px;"></i>
                                    <?php echo htmlspecialchars($row['waste_type']); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 20px;">No pickup schedules available.</td>
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