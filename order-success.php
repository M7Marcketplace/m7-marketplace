<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .success-container {
            max-width: 600px;
            margin: 60px auto;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 40px;
            padding: 60px 40px;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .success-icon {
            font-size: 120px;
            margin-bottom: 20px;
            animation: bounce 1s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .success-container h1 {
            font-size: 42px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .order-number {
            font-size: 24px;
            background: rgba(0, 0, 0, 0.3);
            padding: 15px 30px;
            border-radius: 50px;
            display: inline-block;
            margin: 20px 0;
            border: 1px solid #4CAF50;
            color: #4CAF50;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .action-buttons .btn {
            padding: 15px 30px;
            font-size: 16px;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';
$orderNumber = $_GET['order'] ?? '';
?>

<?php include 'navbar.php'; ?>

<main>
    <div class="success-container">
        <div class="success-icon">🎉</div>
        <h1>Order Placed Successfully!</h1>
        <p style="font-size: 18px; margin-bottom: 20px;">Thank you for shopping with M7 Marketplace</p>
        
        <div class="order-number"><?php echo htmlspecialchars($orderNumber); ?></div>
        
        <p style="margin: 30px 0; opacity: 0.8;">
            A confirmation email has been sent to your email address.<br>
            You can track your order status in your account.
        </p>
        
        <div class="action-buttons">
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            <a href="orders.php" class="btn btn-success">View Orders</a>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>