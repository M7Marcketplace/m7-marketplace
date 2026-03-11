<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>M7 Shopping | Marketplace - Buy & Sell</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <link rel="icon" type="image/png" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .feature-box {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 30px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.15);
            border-color: #d96565;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 10px;
        }
        
        .category-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .category-card:hover {
            transform: translateY(-10px) scale(1.05);
            background: rgba(255,255,255,0.15);
            border-color: #d96565;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        
        .promo-banner {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            padding: 60px;
            border-radius: 30px;
            margin: 60px 0;
            text-align: center;
            box-shadow: 0 20px 40px rgba(217,101,101,0.3);
        }
        
        .hero-section {
            padding: 80px 20px;
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 30px;
            margin-bottom: 50px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '🏪';
            position: absolute;
            font-size: 200px;
            opacity: 0.1;
            bottom: -50px;
            right: -50px;
            transform: rotate(-15deg);
        }
        
        .how-it-works-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .how-it-works-card:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.15);
            border-color: #d96565;
        }
        
        .newsletter-section {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 60px;
            border-radius: 30px;
            margin: 40px 0;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .step-circle {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-weight: bold;
            font-size: 1.5rem;
            box-shadow: 0 10px 20px rgba(217,101,101,0.3);
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <!-- HERO SECTION -->
    <div class="hero-section">
        <h1 style="font-size: 56px; margin-bottom: 20px;">Welcome to <span class="gradient-text">M7 Marketplace</span> 🏪</h1>
        <p style="font-size: 20px; max-width: 700px; margin: 0 auto 30px; opacity: 0.9;">
            The newest online marketplace where <strong>sellers</strong> meet <strong>customers</strong>. 
            Start selling your products or find unique items from independent sellers!
        </p>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="products.php" class="btn" style="font-size: 18px; padding: 15px 40px;">🛍️ Start Shopping</a>
            
            <?php if (isLoggedIn()): ?>
                <?php if (isSeller()): ?>
                    <a href="seller-add-product.php" class="btn btn-success" style="font-size: 18px; padding: 15px 40px;">📦 Add Product</a>
                <?php else: ?>
                    <a href="register.php?role=seller" class="btn btn-success" style="font-size: 18px; padding: 15px 40px;">📦 Become a Seller</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="register.php?role=seller" class="btn btn-success" style="font-size: 18px; padding: 15px 40px;">📦 Start Selling</a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- STATISTICS - Dynamic from Database -->
    <?php
    // Get real statistics from database
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role='buyer'");
    $buyers = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role='seller'");
    $sellers = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active=1");
    $products = $stmt->fetch()['count'];

    // Fix for Happy Customers - Count buyers who have placed orders
    $stmt = $pdo->query("SELECT COUNT(DISTINCT buyer_id) as count FROM orders");
    $happyCustomers = $stmt->fetch()['count'];

    // If no orders yet, show 0
    if (!$happyCustomers) {
        $happyCustomers = 0;
    }
    ?>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 60px 0;">
        <div class="feature-box">
            <div class="stat-number"><?php echo $happyCustomers; ?></div>
            <div style="font-size: 16px; opacity: 0.9;">Happy Customers</div>
            <div style="font-size: 12px; opacity: 0.6;"><?php echo $happyCustomers > 0 ? 'People who bought' : 'Be our first!'; ?></div>
        </div>
        <div class="feature-box">
            <div class="stat-number"><?php echo $sellers; ?></div>
            <div style="font-size: 16px; opacity: 0.9;">Active Sellers</div>
            <div style="font-size: 12px; opacity: 0.6;">Join them today!</div>
        </div>
        <div class="feature-box">
            <div class="stat-number"><?php echo $products; ?></div>
            <div style="font-size: 16px; opacity: 0.9;">Products Listed</div>
            <div style="font-size: 12px; opacity: 0.6;">And counting...</div>
        </div>
        <div class="feature-box">
            <div class="stat-number">24/7</div>
            <div style="font-size: 16px; opacity: 0.9;">Support Ready</div>
            <div style="font-size: 12px; opacity: 0.6;">We're here for you</div>
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
    
    <!-- WHY JOIN US -->
    <h2 style="font-size: 36px; margin: 60px 0 30px; text-align: center;">✨ Why Join M7 Marketplace?</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 60px;">
        <div class="feature-box">
            <div style="font-size: 48px; margin-bottom: 20px; text-align: center;">💰</div>
            <h3 style="text-align: center;">Low Commission</h3>
            <p style="text-align: center;">Sellers pay only 5% per sale. Keep more of your profits!</p>
        </div>
        <div class="feature-box">
            <div style="font-size: 48px; margin-bottom: 20px; text-align: center;">🔒</div>
            <h3 style="text-align: center;">Secure Payments</h3>
            <p style="text-align: center;">Buyers pay securely via CCP or cash on delivery. Sellers receive payments directly from buyers.</p>
        </div>
        <div class="feature-box">
            <div style="font-size: 48px; margin-bottom: 20px; text-align: center;">📈</div>
            <h3 style="text-align: center;">Growing Fast</h3>
            <p style="text-align: center;">Be an early seller on our platform. Get more visibility!</p>
        </div>
        <div class="feature-box">
            <div style="font-size: 48px; margin-bottom: 20px; text-align: center;">🎯</div>
            <h3 style="text-align: center;">Easy to Start</h3>
            <p style="text-align: center;">Register as a seller in minutes. Start listing today!</p>
        </div>
        <div class="feature-box">
            <div style="font-size: 48px; margin-bottom: 20px; text-align: center;">📊</div>
            <h3 style="text-align: center;">Seller Dashboard</h3>
            <p style="text-align: center;">Track sales, manage products, see earnings in real-time.</p>
        </div>
        <div class="feature-box">
            <div style="font-size: 48px; margin-bottom: 20px; text-align: center;">🤝</div>
            <h3 style="text-align: center;">24/7 Support</h3>
            <p style="text-align: center;">We help both buyers and sellers with any issues.</p>
        </div>
    </div>
    
    <!-- PROMO BANNER -->
    <div class="promo-banner">
        <h2 style="font-size: 42px; margin-bottom: 20px; color: white;">🚀 BE THE FIRST SELLER!</h2>
        <p style="font-size: 20px; margin-bottom: 30px; color: white; opacity: 0.9;">Join now and get <strong>0% commission</strong> for your first 3 months!</p>
        <?php if (isLoggedIn() && isSeller()): ?>
            <a href="seller-add-product.php" class="btn" style="background: white; color: #d96565; font-size: 18px; padding: 15px 40px;">Add Product →</a>
        <?php else: ?>
            <a href="register.php?role=seller" class="btn" style="background: white; color: #d96565; font-size: 18px; padding: 15px 40px;">Claim Your Spot →</a>
        <?php endif; ?>
    </div>
    
    <!-- CATEGORIES -->
    <h2 style="font-size: 36px; margin: 60px 0 30px; text-align: center;">📦 Popular Categories</h2>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 60px;">
        <div class="category-card" onclick="window.location.href='products.php?category=electronics'">
            <div style="font-size: 48px; margin-bottom: 15px;">📱</div>
            <h4>Electronics</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=fashion'">
            <div style="font-size: 48px; margin-bottom: 15px;">👕</div>
            <h4>Fashion</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=clothing'">
            <div style="font-size: 48px; margin-bottom: 15px;">👕</div>
            <h4>Clothing</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=home'">
            <div style="font-size: 48px; margin-bottom: 15px;">🏠</div>
            <h4>Home</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=garden'">
            <div style="font-size: 48px; margin-bottom: 15px;">🌱</div>
            <h4>Garden</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=crafts'">
            <div style="font-size: 48px; margin-bottom: 15px;">🎨</div>
            <h4>Crafts</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=handmade'">
            <div style="font-size: 48px; margin-bottom: 15px;">✂️</div>
            <h4>Handmade</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=sports'">
            <div style="font-size: 48px; margin-bottom: 15px;">⚽</div>
            <h4>Sports</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=books'">
            <div style="font-size: 48px; margin-bottom: 15px;">📚</div>
            <h4>Books</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=food'">
            <div style="font-size: 48px; margin-bottom: 15px;">🍕</div>
            <h4>Food</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=bakery'">
            <div style="font-size: 48px; margin-bottom: 15px;">🥐</div>
            <h4>Bakery</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=desserts'">
            <div style="font-size: 48px; margin-bottom: 15px;">🍰</div>
            <h4>Desserts</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=beauty'">
            <div style="font-size: 48px; margin-bottom: 15px;">💄</div>
            <h4>Beauty</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=health'">
            <div style="font-size: 48px; margin-bottom: 15px;">💊</div>
            <h4>Health</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=toys'">
            <div style="font-size: 48px; margin-bottom: 15px;">🧸</div>
            <h4>Toys</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=games'">
            <div style="font-size: 48px; margin-bottom: 15px;">🎮</div>
            <h4>Games</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=automotive'">
            <div style="font-size: 48px; margin-bottom: 15px;">🚗</div>
            <h4>Automotive</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=pet'">
            <div style="font-size: 48px; margin-bottom: 15px;">🐶</div>
            <h4>Pet</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=music'">
            <div style="font-size: 48px; margin-bottom: 15px;">🎵</div>
            <h4>Music</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=art'">
            <div style="font-size: 48px; margin-bottom: 15px;">🖼️</div>
            <h4>Art</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=office'">
            <div style="font-size: 48px; margin-bottom: 15px;">📎</div>
            <h4>Office</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=baby'">
            <div style="font-size: 48px; margin-bottom: 15px;">👶</div>
            <h4>Baby</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=jewelry'">
            <div style="font-size: 48px; margin-bottom: 15px;">💍</div>
            <h4>Jewelry</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=shoes'">
            <div style="font-size: 48px; margin-bottom: 15px;">👟</div>
            <h4>Shoes</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=bags'">
            <div style="font-size: 48px; margin-bottom: 15px;">👜</div>
            <h4>Bags</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=watches'">
            <div style="font-size: 48px; margin-bottom: 15px;">⌚</div>
            <h4>Watches</h4>
        </div>
        <div class="category-card" onclick="window.location.href='products.php?category=other'">
            <div style="font-size: 48px; margin-bottom: 15px;">🔄</div>
            <h4>Other</h4>
        </div>
    </div>
    
    <!-- HOW TO START SELLING -->
    <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 50px; border-radius: 30px; margin: 60px 0; border: 1px solid rgba(255,255,255,0.1);">
        <h2 style="font-size: 36px; margin-bottom: 30px; text-align: center;">📝 Start Selling in 4 Easy Steps</h2>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <div style="text-align: center;">
                <div class="step-circle">1</div>
                <h4>Create Account</h4>
                <p style="opacity: 0.8;">Register as a seller (takes 2 minutes)</p>
            </div>
            <div style="text-align: center;">
                <div class="step-circle">2</div>
                <h4>Set Up Store</h4>
                <p style="opacity: 0.8;">Add your store name and details</p>
            </div>
            <div style="text-align: center;">
                <div class="step-circle">3</div>
                <h4>List Products</h4>
                <p style="opacity: 0.8;">Add photos, prices, descriptions</p>
            </div>
            <div style="text-align: center;">
                <div class="step-circle">4</div>
                <h4>Start Earning</h4>
                <p style="opacity: 0.8;">Get paid when customers buy</p>
            </div>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <?php if (isLoggedIn() && isSeller()): ?>
                <a href="seller-add-product.php" class="btn btn-success" style="font-size: 18px; padding: 15px 40px;">Add New Product →</a>
            <?php else: ?>
                <a href="register.php?role=seller" class="btn btn-success" style="font-size: 18px; padding: 15px 40px;">Become a Seller Now →</a>
            <?php endif; ?>
        </div>
    </div>
    
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
<script>
function subscribeNewsletter() {
    let email = document.getElementById('newsletter-email').value;
    if (email) {
        showNotification('✅ Thanks for subscribing!', 'success');
        document.getElementById('newsletter-email').value = '';
    } else {
        showNotification('❌ Please enter your email address', 'error');
    }
}
</script>
</body>
</html>