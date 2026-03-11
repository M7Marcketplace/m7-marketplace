<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Terms of Service - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .terms-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .terms-container h1 {
            font-size: 42px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff, #d96565);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .terms-container h2 {
            color: #d96565;
            margin: 30px 0 15px;
            font-size: 24px;
        }
        
        .terms-container p {
            line-height: 1.8;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .terms-container ul {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        
        .terms-container li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        
        .last-updated {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-style: italic;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main>
        <div class="terms-container">
            <h1>📜 Terms of Service</h1>
            <p class="last-updated">Last Updated: March 2026</p>
            
            <h2>1. Acceptance of Terms</h2>
            <p>By accessing or using M7 Marketplace, you agree to be bound by these Terms of Service. If you do not agree to all the terms, you may not access or use our services.</p>
            
            <h2>2. User Accounts</h2>
            <p>When you create an account, you must provide accurate and complete information. You are responsible for maintaining the security of your account and for all activities that occur under your account.</p>
            
            <h2>3. Customer and Seller Responsibilities</h2>
            <p><strong>For Customers:</strong> You agree to pay for products you purchase and provide accurate shipping information.</p>
            <p><strong>For Sellers:</strong> You agree to accurately list your products, fulfill orders promptly, and communicate professionally with buyers.</p>
            
            <h2>4. Prohibited Items</h2>
            <p>The following items are prohibited on M7 Marketplace:</p>
            <ul>
                <li>Illegal items or services</li>
                <li>Counterfeit or replica products</li>
                <li>Weapons, drugs, or dangerous items</li>
                <li>Adult content or services</li>
                <li>Items that infringe on intellectual property rights</li>
            </ul>
            
            <h2>5. Payments and Fees</h2>
            <p>Sellers pay a 5% commission on sales. The first 3 months are commission-free for new sellers. Payments are processed securely through our platform.</p>
            
            <h2>6. Shipping and Delivery</h2>
            <p>Sellers are responsible for shipping products in a timely manner. M7 Marketplace is not responsible for shipping delays or issues once the item is shipped.</p>
            
            <h2>7. Disputes and Refunds</h2>
            <p>If you have an issue with an order, contact the seller directly. If the issue cannot be resolved, contact our support team for assistance.</p>
            
            <h2>8. Termination</h2>
            <p>We reserve the right to suspend or terminate accounts that violate these terms or engage in fraudulent activity.</p>
            
            <h2>9. Changes to Terms</h2>
            <p>We may modify these terms at any time. Continued use of the platform after changes constitutes acceptance of the new terms.</p>
            
            <h2>10. Contact Information</h2>
            <p>For questions about these Terms, contact us at: <a href="mailto:m7.contact.us@gmail.com" style="color: #d96565;">m7.contact.us@gmail.com</a></p>
            
            <div class="last-updated">
                <p>By using M7 Marketplace, you acknowledge that you have read and understood these Terms of Service.</p>
            </div>
        </div>
    </main>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>