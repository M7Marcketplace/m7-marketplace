<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .cart-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .cart-header h1 {
            font-size: 42px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .cart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }
        
        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 25px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 25px;
            border-radius: 20px;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .cart-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #d96565 0%, #4CAF50 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .cart-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .cart-item:hover::before {
            transform: scaleX(1);
        }
        
        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 15px;
            border: 2px solid rgba(217, 101, 101, 0.3);
            transition: all 0.3s ease;
        }
        
        .cart-item:hover .cart-item-image {
            transform: scale(1.05);
            border-color: #d96565;
        }
        
        .cart-item-details {
            text-align: left;
        }
        
        .cart-item-details h3 {
            font-size: 1.5rem;
            color: #d96565;
            margin-bottom: 10px;
        }
        
        .cart-item-seller {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .cart-item-price {
            font-size: 1.3rem;
            font-weight: 600;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        
        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 15px;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            background: #d96565;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quantity-btn:hover {
            background: #b84343;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(217, 101, 101, 0.4);
        }
        
        .quantity-btn:disabled {
            background: #666;
            cursor: not-allowed;
            opacity: 0.5;
            transform: none;
        }
        
        .quantity-value {
            font-size: 1.2rem;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
        }
        
        .item-total {
            font-size: 1.2rem;
            font-weight: 700;
            color: #d96565;
        }
        
        .remove-btn {
            background: rgba(217, 101, 101, 0.2);
            color: #d96565;
            border: 1px solid #d96565;
            padding: 8px 15px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .remove-btn:hover {
            background: #d96565;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(217, 101, 101, 0.4);
        }
        
        .summary-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 25px;
            position: sticky;
            top: 100px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .summary-card h2 {
            color: #d96565;
            margin-bottom: 25px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .summary-row.total {
            border-bottom: none;
            font-size: 1.3rem;
            font-weight: 700;
            color: #4CAF50;
            padding-top: 20px;
        }
        
        .summary-label {
            opacity: 0.8;
        }
        
        .summary-value {
            font-weight: 600;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 18px;
            font-size: 1.2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 25px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            border: none;
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(76, 175, 80, 0.4);
        }
        
        .checkout-btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            margin: 40px auto;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .empty-cart-icon {
            font-size: 120px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        .empty-cart h2 {
            font-size: 32px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .empty-cart p {
            font-size: 18px;
            margin: 20px auto;
            max-width: 400px;
            opacity: 0.8;
            line-height: 1.6;
        }
        
        .continue-shopping-btn {
            display: inline-block;
            padding: 15px 40px;
            font-size: 18px;
            margin-top: 30px;
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .continue-shopping-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(217, 101, 101, 0.4);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        @media (max-width: 768px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .cart-item-image {
                margin: 0 auto;
            }
            
            .cart-item-details {
                text-align: center;
            }
            
            .cart-item-actions {
                align-items: center;
            }
            
            .summary-card {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';
?>

<?php include 'navbar.php'; ?>

<main>
    <div style="max-width: 1200px; margin: 0 auto;">
        <h1 class="text-center">🛒 Your Shopping Cart</h1>
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="orders.php" class="btn" style="background: #2196F3; padding: 12px 25px;">
                📋 View My Orders
            </a>
        </div>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
            <!-- Cart Items -->
            <div id="cart-items-container">
                <!-- Cart items will be loaded by JavaScript -->
            </div>
            
            <!-- Order Summary -->
            <div>
                <div class="summary-card" id="order-summary">
                    <h2>Order Summary</h2>
                    <div id="summary-details">
                        <!-- Summary will be loaded by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
<script>
    // Initialize cart display
    document.addEventListener('DOMContentLoaded', function() {
        displayCart();
    });
</script>
</body>
</html>