<?php
session_start();

// Check if user is logged in and if the role is 'citizen'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../auth/login.php");
    exit();
}

// Include database connection
require_once '../config/db.php';

// Get current user ID
$user_id = $_SESSION['user_id'];

// Fetch user stats from 'bin_reports' table
$total_reports_query = "SELECT COUNT(*) as total FROM bin_reports WHERE user_id = '$user_id'";
$total_reports_res = mysqli_query($conn, $total_reports_query);
$total_reports = ($total_reports_res) ? mysqli_fetch_assoc($total_reports_res)['total'] : 0;

// Fetch pending reports for this user
$pending_query = "SELECT COUNT(*) as pending FROM bin_reports WHERE user_id = '$user_id' AND LOWER(status) != 'resolved'";
$pending_res = mysqli_query($conn, $pending_query);
$pending_reports = ($pending_res) ? mysqli_fetch_assoc($pending_res)['pending'] : 0;

$resolved_query = "SELECT COUNT(*) as resolved FROM bin_reports WHERE user_id = '$user_id' AND LOWER(status) = 'resolved'";
$resolved_res = mysqli_query($conn, $resolved_query);
$resolved_reports = ($resolved_res) ? mysqli_fetch_assoc($resolved_res)['resolved'] : 0;

// Fetch recent reports from 'bin_reports' table
$reports_query = "SELECT * FROM bin_reports WHERE user_id = '$user_id' ORDER BY reported_at DESC LIMIT 5";
$reports_result = mysqli_query($conn, $reports_query);

$fullName = htmlspecialchars($_SESSION['full_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Dashboard | EcoPredict</title>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* THEME VARIABLES (LIGHT & DARK MODE) */
        :root {
            --bg-color: #f4f6f8;
            --text-color: #2c3e50;
            --text-muted: #7f8c8d;
            --card-bg: #ffffff;
            --border-color: #e1e1e1;
            --sidebar-bg: #ffffff;
            --topnav-bg: #ffffff;
            --primary-color: #27ae60;
            --primary-light: #f1f8e9;
            --hover-bg: #f8f9fa;
            --table-border: #edf2f7;
            --table-header: #a0aec0;
        }

        [data-theme="dark"] {
            --bg-color: #12181f;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --card-bg: #1e293b;
            --border-color: #334155;
            --sidebar-bg: #1e293b;
            --topnav-bg: #1e293b;
            --primary-color: #2e7d32;
            --primary-light: #1b382b;
            --hover-bg: #334155;
            --table-border: #334155;
            --table-header: #64748b;
        }

        /* RESET & BASE STYLES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR STYLING */
        .dash-sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            padding: 25px 20px;
            z-index: 1000;
            overflow-y: auto;
        }

        .dash-sidebar .logo {
            font-size: 22px;
            font-weight: 700;
            color: #27ae60;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .dash-sidebar .menu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0;
            margin: 0;
        }

        .dash-sidebar .menu-list li {
            width: 100%;
        }

        .dash-sidebar .menu-list a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .dash-sidebar .menu-list a:hover,
        .dash-sidebar .menu-list a.active {
            background-color: var(--primary-light);
            color: #27ae60;
            font-weight: 600;
        }

        .dash-sidebar .menu-list a i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        /* SIDEBAR CALENDAR WIDGET */
        .sidebar-calendar {
            margin-top: 25px;
            background: var(--bg-color);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .cal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 13px;
            color: var(--text-color);
        }

        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            text-align: center;
            font-size: 11px;
        }

        .cal-day-name {
            font-weight: 700;
            color: var(--text-muted);
            padding-bottom: 4px;
        }

        .cal-day {
            padding: 5px 0;
            border-radius: 4px;
            color: var(--text-color);
        }

        .cal-day.today {
            background-color: #27ae60;
            color: #ffffff;
            font-weight: bold;
        }

        .cal-day.empty {
            visibility: hidden;
        }

        .logout-container {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
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
            background-color: var(--topnav-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 40px;
            z-index: 900;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .theme-toggle-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s, background-color 0.2s;
        }

        .theme-toggle-btn:hover {
            background-color: var(--hover-bg);
            color: var(--text-color);
        }

        .avatar {
            width: 38px;
            height: 38px;
            background-color: #27ae60;
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
            margin-bottom: 35px;
        }

        .welcome-sec h1 {
            font-size: 28px;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .welcome-sec p {
            color: var(--text-muted);
        }

        /* STATS GRID */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        .card-stat {
            background-color: var(--card-bg);
            padding: 22px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .card-stat .icon-box {
            width: 55px;
            height: 55px;
            background-color: var(--primary-light);
            color: #27ae60;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .card-stat .icon-box.pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        [data-theme="dark"] .card-stat .icon-box.pending {
            background-color: #451a03;
            color: #fbbf24;
        }

        .card-stat .val {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-color);
        }

        .card-stat .lbl {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* CONTENT GRID */
        .grid-two {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 25px;
        }

        .box-card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .box-card h3 {
            font-size: 17px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-color);
        }

        .box-card h3 i {
            color: #27ae60;
        }

        .action-btns {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .act-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background-color: var(--hover-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }

        .act-btn:hover {
            border-color: #27ae60;
            background-color: var(--primary-light);
        }

        .act-btn div {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .act-btn i.icon {
            color: #27ae60;
        }

        /* TABLE */
        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            text-align: left;
            padding: 12px 8px;
            border-bottom: 2px solid var(--table-border);
            color: var(--table-header);
            font-size: 13px;
            font-weight: 600;
        }

        .custom-table td {
            padding: 14px 8px;
            border-bottom: 1px solid var(--table-border);
            font-size: 14px;
            color: var(--text-color);
        }

        .badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-success { background-color: #c8e6c9; color: #2e7d32; }
        .badge-warning { background-color: #ffe082; color: #b77a00; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="dash-sidebar">
        <div class="logo">
            <i class="fa-solid fa-leaf"></i>
            <span>EcoPredict<span style="color:var(--text-color)">.ai</span></span>
        </div>

        <ul class="menu-list">
            <li><a href="#" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="../citizen/report_bin.php"><i class="fa-solid fa-trash-can"></i> Report Bin Full</a></li>
            <li><a href="../citizen/pickup_schedule.php"><i class="fa-solid fa-calendar-alt"></i> Pickup Schedule</a></li>
            <li><a href="../citizen/notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li><a href="../citizen/my_profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
        </ul>

        <!-- LIVE CALENDAR WIDGET -->
        <div class="sidebar-calendar">
            <div class="cal-header">
                <span id="calMonthYear"></span>
            </div>
            <div class="cal-grid" id="calGrid">
                <div class="cal-day-name">S</div>
                <div class="cal-day-name">M</div>
                <div class="cal-day-name">T</div>
                <div class="cal-day-name">W</div>
                <div class="cal-day-name">T</div>
                <div class="cal-day-name">F</div>
                <div class="cal-day-name">S</div>
            </div>
        </div>

        <div class="logout-container">
            <a href="../auth/logout.php" class="btn-logout-custom">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </aside>

    <!-- TOP NAV -->
    <header class="dash-topnav">
        <div class="user-profile">
            <!-- LIGHT / DARK MODE TOGGLE BUTTON -->
            <button class="theme-toggle-btn" id="themeToggleBtn" title="Toggle Light/Dark Mode">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>

            <i class="fa-solid fa-bell" style="color: var(--text-muted); font-size: 18px; cursor: pointer;"></i>
            <div class="avatar"><?php echo strtoupper($fullName[0]); ?></div>
            <span style="font-weight: 600; font-size: 14px; color: var(--text-color);"><?php echo $fullName; ?></span>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dash-main">
        <div class="welcome-sec">
            <h1>Welcome, <?php echo $fullName; ?>! 👋</h1>
            <p>Here is a summary of your local waste management activity.</p>
        </div>

        <!-- STATS -->
        <div class="stats-cards">
            <div class="card-stat">
                <div class="icon-box"><i class="fa-solid fa-trash"></i></div>
                <div>
                    <div class="val"><?php echo $total_reports; ?></div>
                    <div class="lbl">Full Bin Reports</div>
                </div>
            </div>
            <div class="card-stat">
                <div class="icon-box pending"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <div class="val"><?php echo $pending_reports; ?></div>
                    <div class="lbl">Pending Pickups</div>
                </div>
            </div>
            <div class="card-stat">
                <div class="icon-box"><i class="fa-solid fa-check-circle"></i></div>
                <div>
                    <div class="val"><?php echo $resolved_reports; ?></div>
                    <div class="lbl">Issues Resolved</div>
                </div>
            </div>
        </div>

        <!-- GRID SECTION -->
        <div class="grid-two">
            <!-- QUICK ACTIONS -->
            <div class="box-card">
                <h3><i class="fa-solid fa-rocket"></i> Citizen Quick Actions</h3>
                <div class="action-btns">
                    <a href="../citizen/report_bin.php" class="act-btn">
                        <div><i class="fa-solid fa-location-dot icon"></i> Report Waste Bin Full</div>
                        <i class="fa-solid fa-chevron-right" style="font-size: 12px; color: var(--text-muted);"></i>
                    </a>
                    <a href="../citizen/pickup_schedule.php" class="act-btn">
                        <div><i class="fa-solid fa-clock-rotate-left icon"></i> View Pickup Schedule</div>
                        <i class="fa-solid fa-chevron-right" style="font-size: 12px; color: var(--text-muted);"></i>
                    </a>
                    <a href="../citizen/my_profile.php" class="act-btn">
                        <div><i class="fa-solid fa-user-circle icon"></i> Update Profile Settings</div>
                        <i class="fa-solid fa-chevron-right" style="font-size: 12px; color: var(--text-muted);"></i>
                    </a>
                </div>
            </div>

            <!-- RECENT REPORTS -->
            <div class="box-card">
                <h3><i class="fa-solid fa-list-check"></i> Recent Full Bin Reports</h3>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Location / Bin ID</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($reports_result && mysqli_num_rows($reports_result) > 0) {
                            while ($row = mysqli_fetch_assoc($reports_result)) {
                                $statusClass = (strtolower($row['status']) == 'resolved') ? 'badge-success' : 'badge-warning';
                                $formattedDate = date('M d, Y', strtotime($row['reported_at']));
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['bin_location']); ?></td>
                                    <td><?php echo $formattedDate; ?></td>
                                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted);">No reports found.</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- JAVASCRIPT FOR THEME TOGGLE & LIVE CALENDAR -->
    <script>
        // 1. LIGHT / DARK MODE TOGGLE SCRIPT
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const currentTheme = localStorage.getItem('theme');

        if (currentTheme) {
            document.documentElement.setAttribute('data-theme', currentTheme);
            if (currentTheme === 'dark') {
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
        }

        themeToggleBtn.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('fa-sun', 'fa-moon');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
        });

        // 2. LIVE CALENDAR GENERATOR SCRIPT
        function renderCalendar() {
            const today = new Date();
            const year = today.getFullYear();
            const month = today.getMonth();
            const date = today.getDate();

            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            
            document.getElementById('calMonthYear').innerText = `${monthNames[month]} ${year}`;

            const firstDay = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();
            const calGrid = document.getElementById('calGrid');

            // Clear previous days (keep day names)
            const dayNamesHTML = `
                <div class="cal-day-name">S</div>
                <div class="cal-day-name">M</div>
                <div class="cal-day-name">T</div>
                <div class="cal-day-name">W</div>
                <div class="cal-day-name">T</div>
                <div class="cal-day-name">F</div>
                <div class="cal-day-name">S</div>
            `;
            calGrid.innerHTML = dayNamesHTML;

            // Empty cells before month starts
            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement('div');
                emptyCell.classList.add('cal-day', 'empty');
                calGrid.appendChild(emptyCell);
            }

            // Fill actual days
            for (let day = 1; day <= totalDays; day++) {
                const dayCell = document.createElement('div');
                dayCell.classList.add('cal-day');
                dayCell.innerText = day;
                if (day === date) {
                    dayCell.classList.add('today');
                }
                calGrid.appendChild(dayCell);
            }
        }

        renderCalendar();
    </script>
</body>
</html>