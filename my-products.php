<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .products-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .notifications-section {
            margin-bottom: 30px;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .notification-header h3 {
            color: #d96565;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .clear-notifications {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #d96565;
            color: #d96565;
            padding: 8px 15px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .clear-notifications:hover {
            background: #d96565;
            color: white;
        }
        
        .notifications-list {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 15px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .notification-item {
            background: rgba(76, 175, 80, 0.1);
            border-left: 4px solid #4CAF50;
            padding: 12px 15px;
            margin-bottom: 8px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        .notification-item.unread {
            background: rgba(76, 175, 80, 0.2);
            border-left-color: #d96565;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            margin-bottom: 3px;
            color: #4CAF50;
        }
        
        .notification-time {
            font-size: 11px;
            opacity: 0.6;
        }
        
        .notification-badge {
            background: #d96565;
            color: white;
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 11px;
            margin-left: 10px;
        }
        
        .mark-read {
            color: #4CAF50;
            cursor: pointer;
            font-size: 18px;
            margin-left: 10px;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .mark-read:hover {
            opacity: 1;
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .products-header h1 {
            font-size: 42px;
            margin: 0;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .add-product-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .add-product-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(76, 175, 80, 0.4);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
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
        
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }
        
        .stat-small {
            font-size: 14px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .search-bar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 20px;
            border-radius: 60px;
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
        }
        
        .search-bar input {
            flex: 1;
            padding: 12px 20px;
            border-radius: 50px;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            font-size: 16px;
        }
        
        .search-bar input:focus {
            outline: 2px solid #d96565;
        }
        
        .search-icon {
            font-size: 20px;
            opacity: 0.7;
        }
        
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .filter-tab {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 600;
        }
        
        .filter-tab:hover {
            background: rgba(217, 101, 101, 0.3);
            transform: translateY(-2px);
        }
        
        .filter-tab.active {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            border-color: transparent;
        }
        
        .table-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            overflow-x: auto;
            padding: 5px;
        }
        
        .product-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .product-table th {
            background: rgba(217, 101, 101, 0.3);
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .product-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .product-table tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .product-image-small {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid rgba(217, 101, 101, 0.3);
            transition: all 0.3s ease;
        }
        
        .product-image-small:hover {
            transform: scale(1.1);
            border-color: #d96565;
        }
        
        .product-name-cell {
            font-weight: 600;
            color: #d96565;
        }
        
        .stock-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .stock-high {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .stock-medium {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        .stock-low {
            background: rgba(217, 101, 101, 0.2);
            color: #d96565;
            border: 1px solid #d96565;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        .action-btn.edit {
            background: #4CAF50;
            color: white;
        }
        
        .action-btn.edit:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        
        .action-btn.duplicate {
            background: #FF9800;
            color: white;
        }
        
        .action-btn.duplicate:hover {
            background: #F57C00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
        }
        
        .action-btn.delete {
            background: #d96565;
            color: white;
        }
        
        .action-btn.delete:hover {
            background: #b84343;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(217, 101, 101, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 30px;
            animation: fadeIn 0.6s ease-out;
        }
        
        .empty-icon {
            font-size: 100px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .empty-state h2 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #d96565;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.8;
        }
        
        .empty-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .empty-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(76, 175, 80, 0.4);
        }
        
        .loading-state {
            text-align: center;
            padding: 80px;
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
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .products-header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .product-table {
                font-size: 14px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .filter-tabs {
                justify-content: center;
            }
            
            .notifications-list {
                max-height: 150px;
            }
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';

// Check if user is logged in and is a seller
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
if ($currentUser['role'] !== 'seller') {
    header('Location: auth.php');
    exit;
}

// Get seller's products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.icon as category_icon
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$currentUser['id']]);
$products = $stmt->fetchAll();

// Calculate stats
$totalProducts = count($products);
$totalStock = 0;
$totalSold = 0;
$totalRevenue = 0;
$totalValue = 0;

foreach ($products as $product) {
    $totalStock += $product['quantity'];
    $totalSold += $product['sold'];
    $totalRevenue += $product['sold'] * $product['price'];
    $totalValue += $product['quantity'] * $product['price'];
}

// Get recent orders for this seller's products
$stmt = $pdo->prepare("
    SELECT o.*, oi.product_name, oi.quantity, oi.product_price
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE oi.seller_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->execute([$currentUser['id']]);
$recentOrders = $stmt->fetchAll();
?>

<?php include 'navbar.php'; ?>

<main>
    <div class="products-wrapper">
        <div id="myproducts-container">
            
            <?php if (!empty($recentOrders)): ?>
            <!-- Notifications Section -->
            <div class="notifications-section">
                <div class="notification-header">
                    <h3>🔔 Recent Sales (<?php echo count($recentOrders); ?>)</h3>
                    <span class="clear-notifications" onclick="markAllNotificationsRead()">Mark all as read</span>
                </div>
                <div class="notifications-list">
                    <?php foreach ($recentOrders as $order): ?>
                    <div class="notification-item unread">
                        <div class="notification-content">
                            <div class="notification-title">🛒 New Order! - <?php echo htmlspecialchars($order['product_name']); ?> x<?php echo $order['quantity']; ?></div>
                            <div class="notification-time"><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?> • Total: <?php echo number_format($order['product_price'] * $order['quantity']); ?> DZD</div>
                        </div>
                        <span class="notification-badge">New</span>
                        <span class="mark-read" onclick="markNotificationRead(this)">✓</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h2>No Products Yet</h2>
                <p>Start selling by adding your first product!</p>
                <a href="seller-add-product.php" class="empty-btn">Add Your First Product</a>
            </div>
            <?php else: ?>
            
            <div class="products-header">
                <h1>📋 My Products</h1>
                <a href="seller-add-product.php" class="add-product-btn">➕ Add New Product</a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo $totalProducts; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-label">Inventory Value</div>
                    <div class="stat-value"><?php echo number_format($totalValue); ?> DZD</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-label">Total Stock</div>
                    <div class="stat-value"><?php echo $totalStock; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-label">Items Sold</div>
                    <div class="stat-value"><?php echo $totalSold; ?></div>
                    <div class="stat-small">Revenue: <?php echo number_format($totalRevenue); ?> DZD</div>
                </div>
            </div>
            
            <div class="filter-tabs">
                <span class="filter-tab active" onclick="filterMyProducts('all')">All</span>
                <span class="filter-tab" onclick="filterMyProducts('in-stock')">In Stock</span>
                <span class="filter-tab" onclick="filterMyProducts('low-stock')">Low Stock (≤5)</span>
                <span class="filter-tab" onclick="filterMyProducts('out-of-stock')">Out of Stock</span>
            </div>
            
            <div class="search-bar">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchProduct" placeholder="Search products by name..." onkeyup="searchMyProducts()">
            </div>
            
            <div class="table-container">
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Sold</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body">
                        <?php foreach ($products as $product): 
                            $stockStatus = $product['quantity'] > 10 ? 'high' : ($product['quantity'] > 0 ? 'medium' : 'low');
                            $stockClass = $product['quantity'] > 10 ? 'stock-high' : ($product['quantity'] > 0 ? 'stock-medium' : 'stock-low');
                            $stockText = $product['quantity'] > 10 ? 'In Stock' : ($product['quantity'] > 0 ? 'Low Stock' : 'Out of Stock');
                        ?>
                        <tr data-stock="<?php echo $product['quantity']; ?>">
                            <td><img src="<?php echo $product['image_url'] ?? 'https://via.placeholder.com/60'; ?>" class="product-image-small" onerror="this.src='https://via.placeholder.com/60'"></td>
                            <td class="product-name-cell"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><strong><?php echo number_format($product['price']); ?> DZD</strong></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td><?php echo $product['sold']; ?></td>
                            <td><span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockText; ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="seller-add-product.php?edit=<?php echo $product['id']; ?>" class="action-btn edit">✏️ Edit</a>
                                    <a href="duplicate-product.php?id=<?php echo $product['id']; ?>" class="action-btn duplicate">📋 Copy</a>
                                    <a href="delete-product.php?id=<?php echo $product['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
</body>
</html>