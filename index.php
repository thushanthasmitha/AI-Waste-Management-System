<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoPredict - Smart Waste Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        html {
            scroll-behavior: smooth;
        }

        /* AI Pulse Wave Animation Styles */
        .hero-right-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .glass-sphere {
            position: relative;
            width: 260px;
            height: 260px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(8px);
            animation: floatAnim 4s ease-in-out infinite;
        }

        .visual-brain-icon {
            font-size: 80px;
            color: #10b981;
            z-index: 2;
        }

        @keyframes floatAnim {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        .pulse-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid #10b981;
            border-radius: 50%;
            animation: pulseWave 2.8s linear infinite;
            opacity: 0;
            z-index: 1;
        }

        .pulse-ring.delay {
            animation-delay: 1.4s;
        }

        @keyframes pulseWave {
            0% { transform: scale(0.95); opacity: 0.7; }
            50% { opacity: 0.4; }
            100% { transform: scale(1.45); opacity: 0; }
        }

        /* Section Layout Styles */
        .content-section {
            padding: 70px 10%;
            text-align: center;
        }
        .section-title {
            font-size: 2rem;
            color: #1b4332;
            margin-bottom: 15px;
        }
        .section-desc {
            color: #555;
            max-width: 700px;
            margin: 0 auto 40px auto;
            line-height: 1.6;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .info-card {
            background: #ffffff;
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #e0f2fe;
            transition: transform 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
        }
        .info-card i {
            font-size: 2.5rem;
            color: #10b981;
            margin-bottom: 15px;
        }
        .info-card h3 {
            color: #2d3748;
            margin-bottom: 10px;
        }
        .info-card p {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.5;
        }
    </style>
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

    <!-- Hero Section with AI Pulse Animation -->
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
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring delay"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="content-section" style="background-color: #f8fafc;">
        <h2 class="section-title">About EcoPredict.ai</h2>
        <p class="section-desc">Empowering Sri Lankan municipal authorities with predictive artificial intelligence to eliminate garbage overflow and streamline waste collection workflows.</p>
        
        <div class="grid-container">
            <div class="info-card">
                <i class="fa-solid fa-bullseye"></i>
                <h3>Our Mission</h3>
                <p>To digitize Western Province municipal operations by replacing fixed schedules with dynamic, data-driven collection plans.</p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-chart-line"></i>
                <h3>Data-Driven Analytics</h3>
                <p>Utilizing historical waste generation records from the Chief Secretary's Office to accurately forecast future waste patterns.</p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-users-gear"></i>
                <h3>Community Integration</h3>
                <p>Connecting citizens directly with waste management authorities for transparent, real-time reporting and task assignments.</p>
            </div>
        </div>
    </section>

    <!-- AI Features Section -->
    <section id="features" class="content-section">
        <h2 class="section-title">Core AI Features</h2>
        <p class="section-desc">Key capabilities powering our smart waste management ecosystem.</p>

        <div class="grid-container">
            <div class="info-card">
                <i class="fa-solid fa-robot"></i>
                <h3>Overflow Risk Prediction</h3>
                <p>Machine learning models calculate precise overflow probability percentages for designated bin locations.</p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-truck-fast"></i>
                <h3>Smart Driver Allocation</h3>
                <p>Dynamic route prioritization ensures collection vehicles target high-risk bins first, saving fuel and time.</p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-camera"></i>
                <h3>Citizen Reporting</h3>
                <p>Easy-to-use public portal allowing residents to instantly report overflowing bins with geographic tags.</p>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
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

    <!-- Contact Section -->
    <section id="contact" class="content-section" style="background-color: #f8fafc;">
        <h2 class="section-title">Get In Touch</h2>
        <p class="section-desc">Have questions or feedback? Contact the Western Province Municipal Waste Services team.</p>

        <div class="grid-container">
            <div class="info-card">
                <i class="fa-solid fa-location-dot"></i>
                <h3>Office Location</h3>
                <p>Western Province Chief Secretary's Office, Sri Lanka</p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-envelope"></i>
                <h3>Email Us</h3>
                <p>support@ecopredict.ai<br>info@westernprovince.gov.lk</p>
            </div>
            <div class="info-card">
                <i class="fa-solid fa-phone"></i>
                <h3>Call Support</h3>
                <p>+94 11 234 5678<br>Mon - Fri: 8:30 AM - 4:30 PM</p>
            </div>
        </div>
    </section>

    <script src="assets/js/script.js"></script>
</body>
</html>