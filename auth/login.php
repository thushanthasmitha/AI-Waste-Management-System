<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoPredict - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="fa-solid fa-leaf"></i> EcoPredict<span>.ai</span>
            </div>
            <h2>Welcome Back</h2>
            <p class="auth-subtitle">Log in to access your smart dashboard</p>

            <form action="login_process.php" method="POST">
                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn-auth">Login</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign Up Here</a></p>
                <p style="margin-top: 10px;"><a href="../index.php" style="color: var(--text-light); font-size: 13px;"><i class="fa-solid fa-arrow-left"></i> Back to Home</a></p>
            </div>
        </div>
    </div>

</body>
</html>