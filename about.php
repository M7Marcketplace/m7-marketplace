<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .about-section {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .mission-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 60px;
            border-radius: 40px;
            margin-bottom: 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }
        
        .mission-card::before {
            content: '🏪';
            position: absolute;
            font-size: 200px;
            opacity: 0.1;
            bottom: -50px;
            right: -50px;
            transform: rotate(-15deg);
        }
        
        .mission-card::after {
            content: '🛍️';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            top: -50px;
            left: -50px;
            transform: rotate(15deg);
        }
        
        .mission-card h1 {
            font-size: 56px;
            margin-bottom: 20px;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .mission-card p {
            font-size: 20px;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.95;
            line-height: 1.8;
            position: relative;
            z-index: 1;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin: 60px 0;
        }
        
        .stat-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 35px 25px;
            border-radius: 25px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #d96565 0%, #4CAF50 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .stat-box:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .stat-box:hover::before {
            transform: scaleX(1);
        }
        
        .stat-number {
            font-size: 54px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 15px;
        }
        
        .stat-box p {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .stat-box small {
            opacity: 0.7;
            font-size: 14px;
        }
        
        .section-title {
            font-size: 42px;
            margin: 70px 0 40px;
            text-align: center;
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .section-title span {
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #d96565, transparent);
            z-index: 0;
        }
        
        .how-it-works-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .about-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 30px;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .about-card:hover {
            transform: translateY(-15px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.3);
        }
        
        .about-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #d96565 0%, #4CAF50 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .about-card:hover::before {
            transform: scaleX(1);
        }
        
        .about-icon {
            font-size: 80px;
            margin-bottom: 25px;
            text-align: center;
            animation: float 3s ease-in-out infinite;
        }
        
        .about-card h3 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .about-card.buyer h3 {
            color: #d96565;
        }
        
        .about-card.seller h3 {
            color: #4CAF50;
        }
        
        .about-card ul {
            text-align: left;
            line-height: 2.2;
            list-style: none;
            padding-left: 0;
        }
        
        .about-card li {
            margin-bottom: 12px;
            padding-left: 30px;
            position: relative;
        }
        
        .about-card li::before {
            content: '✅';
            position: absolute;
            left: 0;
            color: #4CAF50;
        }
        
        .step-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin: 50px 0 70px;
        }
        
        .step {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px 25px;
            border-radius: 25px;
            position: relative;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .step:hover {
            transform: translateY(-10px) scale(1.05);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .step-number {
            position: absolute;
            top: -15px;
            left: -15px;
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 22px;
            box-shadow: 0 5px 15px rgba(217, 101, 101, 0.4);
            border: 2px solid white;
        }
        
        .step-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .step h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #d96565;
        }
        
        .step p {
            opacity: 0.8;
            font-size: 15px;
            margin: 0;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin: 50px 0;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 35px 25px;
            border-radius: 25px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: #d96565;
        }
        
        .feature-icon {
            font-size: 56px;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #d96565;
        }
        
        .feature-card p {
            opacity: 0.8;
            line-height: 1.6;
            margin: 0;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 80px 60px;
            border-radius: 40px;
            margin: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        }
        
        .cta-section::before {
            content: '🛍️📦';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            bottom: -30px;
            right: -30px;
            transform: rotate(-10deg);
        }
        
        .cta-section h2 {
            font-size: 48px;
            margin-bottom: 20px;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .cta-section p {
            font-size: 20px;
            margin-bottom: 35px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .cta-buttons .btn {
            padding: 18px 45px;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        @media (max-width: 1024px) {
            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .mission-card {
                padding: 50px 30px;
            }
            
            .mission-card h1 {
                font-size: 38px;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .how-it-works-grid {
                grid-template-columns: 1fr;
            }
            
            .step-container {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .feature-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-section {
                padding: 50px 30px;
            }
            
            .cta-section h2 {
                font-size: 32px;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .step-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';

// Get real statistics from database
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role='seller'");
$sellers = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active=1");
$products = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role='buyer'");
$buyers = $stmt->fetch()['count'];
?>

<?php include 'navbar.php'; ?>

<main>
    <div class="about-section">
        <!-- Mission Section -->
        <div class="mission-card">
            <h1>About M7 Marketplace</h1>
            <p>
                We're building the future of online shopping in Algeria - a platform where anyone can sell and anyone can buy, with fair prices and low commissions. Our mission is to empower local entrepreneurs and provide shoppers with unique, quality products from trusted sellers across the country.
            </p>
        </div>
        
        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-box">
                <div class="stat-number"><?php echo $sellers; ?></div>
                <p>Active Sellers</p>
                <small>Growing every day</small>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $products; ?></div>
                <p>Products Listed</p>
                <small>And counting...</small>
            </div>
            <div class="stat-box">
                <div class="stat-number">5%</div>
                <p>Commission</p>
                <small>Lowest in market</small>
            </div>
            <div class="stat-box">
                <div class="stat-number">24/7</div>
                <p>Customer Support</p>
                <small>We're here</small>
            </div>
        </div>
        
    <!-- HOW IT WORKS -->
    <h2 style="font-size: 36px; margin: 60px 0 30px; text-align: center;">📋 How Our Marketplace Works</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; margin-bottom: 60px;">
        <!-- For Buyers -->
        <div class="how-it-works-card">
            <div style="font-size: 64px; margin-bottom: 20px; text-align: center;">🛍️</div>
            <h3 style="color: #d96565; text-align: center; margin-bottom: 20px;">For Customers</h3>
            <ul style="text-align: left; margin-top: 20px; line-height: 2; list-style: none; padding-left: 0;">
                <li style="margin-bottom: 10px;">Browse products from various sellers</li>
                <li style="margin-bottom: 10px;">Add items to cart and checkout securely</li>
                <li style="margin-bottom: 10px;">Pay only once - we handle seller payments</li>
                <li style="margin-bottom: 10px;">Track your orders in one place</li>
                <li style="margin-bottom: 10px;">Contact sellers directly</li>
            </ul>
            <div style="text-align: center; margin-top: 30px;">
                <a href="products.php" class="btn">Browse Products →</a>
            </div>
        </div>
        
        <!-- For Sellers -->
        <div class="how-it-works-card">
            <div style="font-size: 64px; margin-bottom: 20px; text-align: center;">📦</div>
            <h3 style="color: #4CAF50; text-align: center; margin-bottom: 20px;">For Sellers</h3>
            <ul style="text-align: left; margin-top: 20px; line-height: 2; list-style: none; padding-left: 0;">
                <li style="margin-bottom: 10px;">Create your seller account for free</li>
                <li style="margin-bottom: 10px;">List unlimited products to sell</li>
                <li style="margin-bottom: 10px;">We take just <strong>5% commission</strong> on sales</li>
                <li style="margin-bottom: 10px;">Get paid directly to your account</li>
                <li style="margin-bottom: 10px;">Manage your store dashboard</li>
            </ul>
            <div style="text-align: center; margin-top: 30px;">
                <a href="register.php?role=seller" class="btn btn-success">Start Selling Today →</a>
            </div>
        </div>
    </div>
    </
        
        <!-- Steps to Start Selling -->
        <h2 class="section-title"><span>🚀 Start Selling in 4 Steps</span></h2>
        
        <div class="step-container">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-icon">📝</div>
                <h3>Register</h3>
                <p>Create your seller account in minutes</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-icon">🏪</div>
                <h3>Set Up Store</h3>
                <p>Add your store name and details</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-icon">📸</div>
                <h3>List Products</h3>
                <p>Add photos, prices, descriptions</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-icon">💰</div>
                <h3>Start Earning</h3>
                <p>Get paid when customers buy</p>
            </div>
        </div>
        
        <!-- Why Choose Us -->
        <h2 class="section-title"><span>✨ Why Choose M7 Marketplace?</span></h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>5% Commission</h3>
                <p>One of the lowest rates in Algeria. Keep 95% of your sales!</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure Payments</h3>
                <p>Buyers pay securely via CCP or cash on delivery. Sellers receive payments directly from buyers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Easy to Use</h3>
                <p>Simple interface for both customers and sellers.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>Easy Communication</h3>
                <p>Contact sellers directly via WhatsApp, phone, or email in one click</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Seller Analytics</h3>
                <p>Track your sales, profits, and inventory.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🤝</div>
                <h3>24/7 Support</h3>
                <p>We're always here to help you succeed.</p>
            </div>
        </div>
        
        <!-- Call to Action -->
        <div class="cta-section">
            <h2>Ready to Start?</h2>
            <p>Join M7 Marketplace today - whether you want to buy or sell!</p>
            <div class="cta-buttons">
                <a href="register.php?role=buyer" class="btn btn-light">🛍️ Shop Now</a>
                <a href="register.php?role=seller" class="btn btn-success">📦 Start Selling</a>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>