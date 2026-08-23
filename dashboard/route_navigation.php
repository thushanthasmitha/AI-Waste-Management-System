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

// Fetch only PENDING tasks assigned to this logged-in driver for navigation
$nav_query = "SELECT * FROM bin_reports 
              WHERE assigned_driver_id = '$driver_id' AND status = 'pending' 
              ORDER BY reported_at DESC";
$nav_result = mysqli_query($conn, $nav_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Navigation | EcoPredict.ai</title>
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

        /* NAVIGATION CARDS GRID */
        .routes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .route-card {
            background-color: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .route-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .task-tag {
            background-color: #eff6ff;
            color: #2563eb;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .waste-tag {
            background-color: #fef3c7;
            color: #d97706;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .location-details {
            margin-bottom: 20px;
        }

        .location-name {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .time-text {
            font-size: 12px;
            color: #64748b;
        }

        .btn-navigate {
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s;
        }

        .btn-navigate:hover {
            background-color: #1d4ed8;
        }

        .no-tasks {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            text-align: center;
            color: #64748b;
            grid-column: 1 / -1;
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
            <li><a href="driver_dashboard.php"><i class="fa-solid fa-truck-pickup"></i> Driver Pickups</a></li>
            <li><a href="route_navigation.php" class="active"><i class="fa-solid fa-map-location-dot"></i> Route Navigation</a></li>
            <li><a href="#"><i class="fa-solid fa-clock-rotate-left"></i> Pickup History</a></li>
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
            <h1>Active Pickup Routes 🗺️</h1>
            <p>Select a location below to start turn-by-turn navigation via Google Maps.</p>
        </div>

        <!-- ROUTES GRID -->
        <div class="routes-grid">
            <?php 
            if ($nav_result && mysqli_num_rows($nav_result) > 0) {
                while ($row = mysqli_fetch_assoc($nav_result)) {
                    $location = htmlspecialchars($row['bin_location']);
                    $wasteType = htmlspecialchars($row['waste_type'] ?? 'General Waste');
                    $formattedDate = date('M d, Y - h:i A', strtotime($row['reported_at']));
                    
                    // Encode location for Google Maps URL search query
                    $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($location . ", Sri Lanka");
                    ?>
                    <div class="route-card">
                        <div>
                            <div class="card-header">
                                <span class="task-tag">#TASK-<?php echo $row['id']; ?></span>
                                <span class="waste-tag"><i class="fa-solid fa-dumpster"></i> <?php echo $wasteType; ?></span>
                            </div>
                            <div class="location-details">
                                <div class="location-name">
                                    <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i>
                                    <?php echo $location; ?>
                                </div>
                                <div class="time-text">
                                    <i class="fa-regular fa-clock"></i> Reported: <?php echo $formattedDate; ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $googleMapsUrl; ?>" target="_blank" class="btn-navigate">
                            <i class="fa-solid fa-diamond-turn-right"></i> Navigate via Google Maps
                        </a>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="no-tasks">
                    <i class="fa-solid fa-circle-check" style="font-size: 40px; color: #16a34a; margin-bottom: 12px;"></i>
                    <h3>No Active Pending Routes</h3>
                    <p style="font-size: 13px; margin-top: 6px;">All assigned pickups have been completed or no pending routes available.</p>
                </div>
                <?php
            }
            ?>
        </div>
    </main>

</body>
</html>