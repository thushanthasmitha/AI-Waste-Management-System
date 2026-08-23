<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoPredict - Smart Waste Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header class="navbar">
        <div class="logo">
            <i class="fa-solid fa-leaf"></i> EcoPredict<span>.ai</span>
        </div>
        <nav class="nav-links">
            <a href="#home" class="active">Home</a>
            <a href="#about">About</a>
            <a href="#features">AI Features</a>
            <a href="#stats">Statistics</a>
            <a href="#contact">Contact</a>
        </nav>
        <div class="nav-buttons">
            <a href="auth/login.php" class="btn-login">Login</a>
        </div>
    </header>

    <section id="home" class="hero-section">
        <div class="hero-container">
            <div class="hero-left-text">
                <span class="badge">Next-Gen Waste Analytics</span>
                <h1>AI-Powered Smart <br><span class="highlight">Waste Management</span></h1>
                <p>Optimizing municipal waste collection in the Western Province using advanced machine learning. Predict accumulation, reduce carbon footprint, and transform urban environments.</p>
                <div class="hero-btns">
                    <a href="#features" class="btn-primary">Explore AI Features</a>
                    <a href="#about" class="btn-secondary">Learn More</a>
                </div>
            </div>
            <div class="hero-right-image">
                <div class="glass-sphere">
                    <i class="fa-solid fa-brain visual-brain-icon"></i>
                </div>
            </div>
        </div>
    </section>

    <section id="stats" class="stats-section">
        <div class="stats-container">
            <div class="stat-box">
                <h2><span class="counter" data-target="92">92</span>%</h2>
                <p>Prediction Accuracy</p>
            </div>
            <div class="stat-box">
                <h2><span class="counter" data-target="35">35</span>%</h2>
                <p>Fuel Reduction</p>
            </div>
            <div class="stat-box">
                <h2><span class="counter" data-target="15">15</span>+</h2>
                <p>Localities Monitored</p>
            </div>
        </div>
    </section>

    <script src="assets/js/script.js"></script>
</body>
</html>