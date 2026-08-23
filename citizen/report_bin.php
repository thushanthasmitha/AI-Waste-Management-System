<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Waste Bin Full | EcoPredict</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f8; padding: 40px; }
        .form-card { max-width: 500px; margin: auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { background: #27ae60; color: white; padding: 12px; border: none; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background: #219150; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #27ae60; text-decoration: none; }
    </style>
</head>
<body>

<div class="form-card">
    <a href="../dashboard/citizen_dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    <h2>Report Waste Bin Full 🗑️</h2>
    <p style="color: #666; margin-bottom: 20px;">Provide location and waste details below.</p>

    <form action="report_process.php" method="POST">
        <div class="form-group">
            <label>Bin Location / Address</label>
            <input type="text" name="bin_location" placeholder="e.g. Main Street, Corner 4" required>
        </div>

        <div class="form-group">
            <label>Waste Type</label>
            <select name="waste_type" required>
                <option value="Organic">Organic Waste</option>
                <option value="Plastic">Plastic / Metal</option>
                <option value="Paper">Paper / Cardboard</option>
                <option value="Mixed">Mixed Waste</option>
            </select>
        </div>

        <div class="form-group">
            <label>Additional Notes (Optional)</label>
            <textarea name="description" rows="4" placeholder="Any details like bin overflow..."></textarea>
        </div>

        <button type="submit" class="btn-submit">Submit Report</button>
    </form>
</div>

</body>
</html>