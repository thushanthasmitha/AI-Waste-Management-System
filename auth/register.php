<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoPredict - Create Account</title>
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
            <h2>Create Account</h2>
            <p class="auth-subtitle">Join the smart waste management revolution</p>

            <form action="register_process.php" method="POST">
                <div class="input-group">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-shield"></i>
                    <select name="role" required>
                        <option value="" disabled selected>Select Your Role</option>
                        <option value="citizen">Citizen / Resident</option>
                        <option value="driver">Collection Driver</option>
                        <option value="authority">Municipal Authority</option>
                    </select>
                </div>

                <button type="submit" class="btn-auth">Sign Up</button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login Here</a></p>
            </div>
        </div>
    </div>

</body>
</html>