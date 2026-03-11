<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Details - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .loading-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            margin: 40px auto;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid #d96565;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .product-detail-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease-out;
        }
        
        .product-gallery {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-bottom: 40px;
        }
        
        .main-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            border: 3px solid rgba(217, 101, 101, 0.3);
            transition: all 0.3s ease;
        }
        
        .main-image-container:hover {
            border-color: #d96565;
            transform: scale(1.02);
            box-shadow: 0 20px 40px rgba(217, 101, 101, 0.3);
        }
        
        .main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .main-image-container:hover .main-image {
            transform: scale(1.1);
        }
        
        .product-info h2 {
            font-size: 42px;
            color: #d96565;
            margin-top: 0;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .seller-badge {
            display: inline-block;
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #4CAF50;
        }
        
        .price-tag {
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 20px 0;
            display: inline-block;
            padding: 10px 20px;
            background: rgba(76, 175, 80, 0.1);
            border-radius: 20px;
            border: 1px solid #4CAF50;
        }
        
        .seller-info {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 25px;
            border-radius: 20px;
            margin: 30px 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .seller-info h3 {
            color: #d96565;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .seller-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        
        .seller-detail span:first-child {
            font-weight: 600;
            color: #d96565;
            min-width: 100px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #d96565;
        }
        
        .detail-value {
            font-size: 1.1rem;
        }
        
        /* Contact Section Styles */
        .contact-section {
            margin: 30px 0;
            padding: 30px;
            background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(33, 150, 243, 0.1));
            border-radius: 20px;
            border: 1px solid rgba(76, 175, 80, 0.3);
            text-align: center;
        }
        
        .contact-section h3 {
            color: #4CAF50;
            font-size: 1.8rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .contact-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        
        .contact-btn {
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 140px;
            justify-content: center;
        }
        
        .contact-btn.call {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }
        
        .contact-btn.whatsapp {
            background: #25D366;
        }
        
        .contact-btn.sms {
            background: #2196F3;
        }
        
        .contact-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .phone-display {
            margin-top: 15px;
            font-size: 1.2rem;
            background: rgba(0, 0, 0, 0.2);
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
        }
        
        .phone-display strong {
            color: #4CAF50;
            margin-right: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }
        
        .action-buttons .btn {
            flex: 1;
            padding: 18px;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 30px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
        }
        
        .quantity-selector label {
            font-weight: 600;
            color: #d96565;
            font-size: 1.1rem;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
            border-radius: 50px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: #d96565;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            background: #b84343;
            transform: scale(1.1);
        }
        
        .quantity-btn:disabled {
            background: #666;
            cursor: not-allowed;
            opacity: 0.5;
        }
        
        .quantity-value {
            font-size: 1.3rem;
            font-weight: 600;
            min-width: 40px;
            text-align: center;
        }
        
        .stock-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 20px;
        }
        
        .in-stock {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .low-stock {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        .out-of-stock {
            background: rgba(217, 101, 101, 0.2);
            color: #d96565;
            border: 1px solid #d96565;
        }
        
        .error-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 500px;
            margin: 40px auto;
        }
        
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        @media (max-width: 768px) {
            .product-gallery {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .main-image {
                height: 350px;
            }
            
            .product-info h2 {
                font-size: 32px;
            }
            
            .price-tag {
                font-size: 36px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .quantity-selector {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .contact-buttons {
                flex-direction: column;
            }
            
            .contact-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, u.full_name as seller_name, u.phone as seller_phone, 
           s.store_name, s.store_description, c.name as category_name, c.icon as category_icon
    FROM products p
    JOIN users u ON p.seller_id = u.id
    LEFT JOIN seller_stores s ON p.seller_id = s.seller_id
    JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    $product = null;
}
?>

<?php include 'navbar.php'; ?>

<main>
    <div id="details-container">
        <?php if (!$product): ?>
            <div class="error-state">
                <div class="error-icon">🔍</div>
                <h2>Product Not Found</h2>
                <p style="margin: 20px auto; max-width: 400px; opacity: 0.7;">
                    The product you're looking for doesn't exist or has been removed.
                </p>
                <a href="products.php" class="btn" style="margin-top: 20px;">Browse Products</a>
            </div>
        <?php else: 
            $whatsappNumber = preg_replace('/[^0-9]/', '', $product['seller_phone']);
            $stockStatus = $product['quantity'] > 10 ? 'in-stock' : ($product['quantity'] > 0 ? 'low-stock' : 'out-of-stock');
            $stockText = $product['quantity'] > 10 ? 'In Stock' : ($product['quantity'] > 0 ? 'Low Stock' : 'Out of Stock');
        ?>
        
        <div class="product-detail-container">
            <div class="product-gallery">
                <div class="main-image-container">
                    <img src="<?php echo $product['image_url'] ?? 'https://via.placeholder.com/600'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="main-image"
                         onerror="this.src='https://via.placeholder.com/600'">
                </div>
                
                <div class="product-info">
                    <h2><?php echo htmlspecialchars($product['name']); ?> <?php echo $product['category_icon']; ?></h2>
                    
                    <span class="seller-badge">
                        Sold by: <?php echo htmlspecialchars($product['store_name'] ?? $product['seller_name']); ?>
                    </span>
                    
                    <div class="price-tag"><?php echo number_format($product['price']); ?> DZD</div>
                    
                    <div class="stock-status <?php echo $stockStatus; ?>">
                        <?php echo $stockText; ?> (<?php echo $product['quantity']; ?> available)
                    </div>
                    
                    <div class="seller-info">
                        <h3>📋 Product Details</h3>
                        
                        <div class="detail-row">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value"><?php echo $product['category_icon']; ?> <?php echo $product['category_name']; ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Condition:</span>
                            <span class="detail-value"><?php echo ucfirst($product['condition']); ?></span>
                        </div>
                        
                        <div class="detail-row">
                            <span class="detail-label">Added:</span>
                            <span class="detail-value"><?php echo date('d M Y', strtotime($product['created_at'])); ?></span>
                        </div>
                        
                        <?php if ($product['seller_phone']): ?>
                        <div class="detail-row">
                            <span class="detail-label">📞 Contact:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($product['seller_phone']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($product['seller_phone']): ?>
                    <div class="contact-section">
                        <h3>📱 Contact Seller</h3>
                        <p style="margin-bottom: 20px;">Have questions? Reach out directly!</p>
                        
                        <div class="contact-buttons">
                            <a href="tel:<?php echo $product['seller_phone']; ?>" class="contact-btn call">📞 Call</a>
                            
                            <?php if ($whatsappNumber): ?>
                            <a href="https://wa.me/<?php echo $whatsappNumber; ?>" target="_blank" class="contact-btn whatsapp">💬 WhatsApp</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="phone-display">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($product['seller_phone']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <h3>📝 Description</h3>
                    <p style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: 15px;">
                        <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description provided.')); ?>
                    </p>
                    
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="decrementQuantity()" <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>−</button>
                            <span class="quantity-value" id="quantity-value">1</span>
                            <button class="quantity-btn" onclick="incrementQuantity(<?php echo $product['quantity']; ?>)" <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>+</button>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button onclick="addToCartWithQuantity(
                            <?php echo $product['id']; ?>,
                            '<?php echo addslashes($product['name']); ?>',
                            <?php echo $product['price']; ?>,
                            '<?php echo addslashes($product['image_url'] ?? 'https://via.placeholder.com/300'); ?>',
                            <?php echo $product['seller_id']; ?>,
                            '<?php echo addslashes($product['store_name'] ?? $product['seller_name']); ?>'
                        )" class="btn btn-success" <?php echo $product['quantity'] <= 0 ? 'disabled' : ''; ?>>
                            🛒 Add to Cart
                        </button>
                        <button onclick="history.back()" class="btn btn-secondary">← Back</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            let currentQuantity = 1;
            const maxQuantity = <?php echo $product['quantity']; ?>;
            
            function incrementQuantity(max) {
                if (currentQuantity < max) {
                    currentQuantity++;
                    document.getElementById('quantity-value').textContent = currentQuantity;
                }
            }
            
            function decrementQuantity() {
                if (currentQuantity > 1) {
                    currentQuantity--;
                    document.getElementById('quantity-value').textContent = currentQuantity;
                }
            }
            
            function addToCartWithQuantity(id, name, price, image, sellerId, sellerName) {
                for (let i = 0; i < currentQuantity; i++) {
                    // This will add the item multiple times or you can modify your addToCart function to accept quantity
                    if (i === 0) {
                        addToCart(id, name, price, image, sellerId, sellerName);
                    } else {
                        // For additional quantities, just increment the existing item
                        let cart = JSON.parse(localStorage.getItem('cart')) || [];
                        let existingItem = cart.find(item => item.id == id);
                        if (existingItem) {
                            existingItem.quantity += 1;
                            localStorage.setItem('cart', JSON.stringify(cart));
                        }
                    }
                }
                showNotification(`✅ Added ${currentQuantity} x ${name} to cart!`, 'success');
                updateCartCount();
            }
        </script>
        
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>