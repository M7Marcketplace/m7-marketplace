<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$isSeller = ($currentUser['role'] === 'seller');

// Handle status update for sellers
if ($isSeller && isset($_POST['update_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['status'];
    
    // Verify this order contains products from this seller
    $check = $pdo->prepare("
        SELECT id FROM order_items 
        WHERE order_id = ? AND seller_id = ?
    ");
    $check->execute([$orderId, $currentUser['id']]);
    
    if ($check->fetch()) {
        $update = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update->execute([$newStatus, $orderId]);
        $success = "Order status updated to " . ucfirst($newStatus);
    }
}

// Get orders based on user role
if ($isSeller) {
    // Sellers see orders containing their products with customer tax included
    $stmt = $pdo->prepare("
        SELECT DISTINCT o.*, 
               u.full_name as buyer_name, u.phone as buyer_phone, u.email as buyer_email,
               oi.seller_id
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN users u ON o.buyer_id = u.id
        WHERE oi.seller_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$currentUser['id']]);
} else {
    // Buyers see their own orders
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name as buyer_name, u.phone as buyer_phone, u.email as buyer_email
        FROM orders o
        LEFT JOIN users u ON o.buyer_id = u.id
        WHERE o.buyer_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$currentUser['id']]);
}
$orders = $stmt->fetchAll();

// Get order items for each order
foreach ($orders as &$order) {
    if ($isSeller) {
        // Sellers only see their items in each order
        $stmt = $pdo->prepare("
            SELECT oi.*, p.image_url, u.full_name as seller_name
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN users u ON oi.seller_id = u.id
            WHERE oi.order_id = ? AND oi.seller_id = ?
        ");
        $stmt->execute([$order['id'], $currentUser['id']]);
    } else {
        // Buyers see all items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.image_url, u.full_name as seller_name
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN users u ON oi.seller_id = u.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order['id']]);
    }
    $order['items'] = $stmt->fetchAll();
}

// Calculate statistics
$totalOrders = count($orders);
$totalAmount = 0;
$totalBaseAmount = 0;
$totalTaxAmount = 0;
$totalCommissionAmount = 0;
$totalItems = 0;
$pendingOrders = 0;
$shippedOrders = 0;
$deliveredOrders = 0;

foreach ($orders as $order) {
    if ($isSeller) {
        // For sellers, calculate detailed amounts
        foreach ($order['items'] as $item) {
            $itemBaseTotal = $item['product_price'] * $item['quantity'];
            $itemTax = round($itemBaseTotal * 0.05); // 5% customer tax
            $itemCommission = round($itemBaseTotal * 0.05); // 5% seller commission
            
            $totalBaseAmount += $itemBaseTotal;
            $totalTaxAmount += $itemTax;
            $totalCommissionAmount += $itemCommission;
            $totalAmount += $itemBaseTotal + $itemTax; // Seller receives product + tax
            $totalItems += $item['quantity'];
        }
    } else {
        $totalAmount += $order['total'];
        $totalItems += array_sum(array_column($order['items'], 'quantity'));
    }
    
    if ($order['status'] === 'pending') $pendingOrders++;
    if ($order['status'] === 'shipped') $shippedOrders++;
    if ($order['status'] === 'delivered') $deliveredOrders++;
}

// Calculate net after commission (what seller keeps after paying M7)
$netAfterCommission = $totalAmount - $totalCommissionAmount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .orders-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 48px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .role-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .role-badge.buyer {
            background: rgba(33, 150, 243, 0.2);
            color: #2196F3;
            border: 1px solid #2196F3;
        }
        
        .role-badge.seller {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        
        .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-note {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .filter-section {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 600;
        }
        
        .filter-btn:hover {
            background: rgba(217,101,101,0.3);
            transform: translateY(-2px);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #d96565, #b84343);
            border-color: transparent;
        }
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .order-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 25px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateX(10px) translateY(-5px);
            background: rgba(255,255,255,0.15);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .order-id {
            font-size: 18px;
            font-weight: 700;
            color: #d96565;
            background: rgba(217,101,101,0.1);
            padding: 5px 15px;
            border-radius: 50px;
            border: 1px solid #d96565;
        }
        
        .order-date {
            opacity: 0.7;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .order-status {
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: rgba(255,193,7,0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        .status-processing {
            background: rgba(0,123,255,0.2);
            color: #007bff;
            border: 1px solid #007bff;
        }
        
        .status-shipped {
            background: rgba(23,162,184,0.2);
            color: #17a2b8;
            border: 1px solid #17a2b8;
        }
        
        .status-delivered {
            background: rgba(40,167,69,0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .status-cancelled {
            background: rgba(217,101,101,0.2);
            color: #d96565;
            border: 1px solid #d96565;
        }
        
        .customer-info {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-icon {
            width: 35px;
            height: 35px;
            background: rgba(217,101,101,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .order-items {
            margin: 20px 0;
        }
        
        .items-title {
            font-size: 16px;
            font-weight: 600;
            color: #d96565;
            margin-bottom: 15px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .order-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .item-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid rgba(217,101,101,0.3);
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: #d96565;
            margin-bottom: 5px;
        }
        
        .item-meta {
            font-size: 13px;
            opacity: 0.7;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .item-price-info {
            font-size: 13px;
            color: #FF9800;
            margin-top: 5px;
        }
        
        .price-breakdown {
            font-size: 12px;
            color: #aaa;
            margin-top: 3px;
        }
        
        .item-total {
            font-weight: 600;
            color: #4CAF50;
            font-size: 16px;
            text-align: right;
        }
        
        .tax-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 11px;
            background: rgba(255,152,0,0.2);
            color: #FF9800;
            border: 1px solid #FF9800;
            margin-left: 5px;
        }
        
        .commission-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 11px;
            background: rgba(217,101,101,0.2);
            color: #d96565;
            border: 1px solid #d96565;
            margin-left: 5px;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .order-total {
            font-size: 20px;
            font-weight: 700;
        }
        
        .order-total.receive {
            color: #4CAF50;
        }
        
        .order-total small {
            font-size: 14px;
            opacity: 0.7;
            font-weight: normal;
            display: block;
        }
        
        .commission-summary {
            background: rgba(217,101,101,0.1);
            padding: 12px 20px;
            border-radius: 50px;
            font-size: 14px;
            border: 1px solid #d96565;
        }
        
        .commission-summary span {
            color: #d96565;
            font-weight: 600;
        }
        
        .status-update-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .status-select {
            padding: 10px 20px;
            border-radius: 50px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            font-size: 14px;
            cursor: pointer;
        }
        
        .status-select option {
            background: #333;
            color: white;
        }
        
        .update-btn {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76,175,80,0.4);
        }
        
        .empty-orders {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 40px;
            max-width: 500px;
            margin: 40px auto;
        }
        
        .empty-icon {
            font-size: 100px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%,100%{transform:translateY(0)}
            50%{transform:translateY(-20px)}
        }
        
        .empty-orders h2 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #d96565;
        }
        
        .shop-now-btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .shop-now-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(76,175,80,0.4);
        }
        
        .success-message {
            background: rgba(76,175,80,0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
            padding: 15px;
            border-radius: 50px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .example-box {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #4CAF50;
        }
        
        .example-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        
        @media (max-width:768px) {
            .stats-grid {
                grid-template-columns: repeat(2,1fr);
            }
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .status-update-form {
                flex-direction: column;
                width: 100%;
            }
            .status-select, .update-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <main>
        <div class="orders-wrapper">
            
            <?php if (isset($success)): ?>
                <div class="success-message">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <div class="empty-icon">📦</div>
                    <h2>No Orders Yet</h2>
                    <p><?php echo $isSeller ? 'When customers buy your products, they will appear here.' : 'Start shopping to place your first order!'; ?></p>
                    <a href="<?php echo $isSeller ? 'seller-add-product.php' : 'products.php'; ?>" class="shop-now-btn">
                        <?php echo $isSeller ? '➕ Add Products' : '🛍️ Start Shopping'; ?>
                    </a>
                </div>
            <?php else: ?>
            
            <div class="page-header">
                <h1><?php echo $isSeller ? '📦 Seller Orders' : '📋 My Orders'; ?></h1>
                <span class="role-badge <?php echo $isSeller ? 'seller' : 'buyer'; ?>">
                    <?php echo $isSeller ? '🛒 SELLER VIEW - Amounts include 5% customer tax' : '👤 BUYER VIEW - Total you paid'; ?>
                </span>
            </div>
            
            <?php if ($isSeller): ?>
            <!-- Example explanation for sellers -->
            <div class="example-box">
                <h4 style="color: #4CAF50; margin-bottom: 10px;">📊 How amounts are calculated:</h4>
                <div class="example-row">
                    <span>Product price (1000 DA):</span>
                    <span>1000 DA</span>
                </div>
                <div class="example-row">
                    <span>+ Customer tax (5%):</span>
                    <span style="color: #FF9800;">+50 DA</span>
                </div>
                <div class="example-row" style="font-weight: bold;">
                    <span>= You receive:</span>
                    <span style="color: #4CAF50;">1050 DA</span>
                </div>
                <div class="example-row">
                    <span>- Commission to M7 (5%):</span>
                    <span style="color: #d96565;">-50 DA</span>
                </div>
                <div class="example-row" style="font-weight: bold; border-top: 1px solid #4CAF50; margin-top: 5px; padding-top: 5px;">
                    <span>= You keep:</span>
                    <span style="color: #4CAF50;">1000 DA</span>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-value"><?php echo $totalOrders; ?></div>
                    <div>Total Orders</div>
                </div>
                
                <?php if ($isSeller): ?>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value"><?php echo number_format($totalAmount); ?> DZD</div>
                    <div>You Will Receive</div>
                    <div class="stat-note">(Includes <?php echo number_format($totalTaxAmount); ?> DZD tax)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo number_format($totalCommissionAmount); ?> DZD</div>
                    <div>Commission to M7</div>
                    <div class="stat-note">(5% of product price)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💵</div>
                    <div class="stat-value"><?php echo number_format($netAfterCommission); ?> DZD</div>
                    <div>You Keep</div>
                    <div class="stat-note">(After paying commission)</div>
                </div>
                <?php else: ?>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value"><?php echo number_format($totalAmount); ?> DZD</div>
                    <div>Total Spent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?php echo $totalItems; ?></div>
                    <div>Items Purchased</div>
                </div>
                <?php endif; ?>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-value"><?php echo $pendingOrders; ?></div>
                    <div>Pending</div>
                </div>
            </div>
            
            <!-- Filter Buttons -->
            <div class="filter-section">
                <button class="filter-btn active" onclick="filterOrders('all')">All</button>
                <button class="filter-btn" onclick="filterOrders('pending')">⏳ Pending</button>
                <button class="filter-btn" onclick="filterOrders('processing')">🔄 Processing</button>
                <button class="filter-btn" onclick="filterOrders('shipped')">🚚 Shipped</button>
                <button class="filter-btn" onclick="filterOrders('delivered')">✅ Delivered</button>
            </div>
            
            <!-- Orders List -->
            <div class="orders-list" id="orders-list">
                <?php foreach ($orders as $order): 
                    $orderTotal = 0;
                    $orderBaseTotal = 0;
                    $orderTaxTotal = 0;
                    $orderCommissionTotal = 0;
                    
                    foreach ($order['items'] as $item) {
                        $itemBaseTotal = $item['product_price'] * $item['quantity'];
                        $itemTax = round($itemBaseTotal * 0.05);
                        $itemCommission = round($itemBaseTotal * 0.05);
                        
                        $orderBaseTotal += $itemBaseTotal;
                        $orderTaxTotal += $itemTax;
                        $orderCommissionTotal += $itemCommission;
                        $orderTotal += $itemBaseTotal + $itemTax;
                    }
                ?>
                <div class="order-card" data-status="<?php echo $order['status']; ?>">
                    <div class="order-header">
                        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                            <span class="order-id">#<?php echo htmlspecialchars($order['order_number']); ?></span>
                            <span class="order-date">📅 <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <span class="order-status status-<?php echo $order['status']; ?>">
                            <?php echo strtoupper($order['status']); ?>
                        </span>
                    </div>
                    
                    <?php if ($isSeller): ?>
                    <!-- Seller View: Show Customer Info -->
                    <div class="customer-info">
                        <div class="info-item">
                            <span class="info-icon">👤</span>
                            <div>
                                <div style="font-size:12px; opacity:0.7;">Customer</div>
                                <div><?php echo htmlspecialchars($order['buyer_name'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">📞</span>
                            <div>
                                <div style="font-size:12px; opacity:0.7;">Phone</div>
                                <div><?php echo htmlspecialchars($order['shipping_phone'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <span class="info-icon">📍</span>
                            <div>
                                <div style="font-size:12px; opacity:0.7;">Address</div>
                                <div><?php echo htmlspecialchars($order['shipping_address'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Order Items -->
                    <div class="order-items">
                        <div class="items-title">
                            📋 <?php echo $isSeller ? 'Your Products in this Order' : 'Items'; ?>
                        </div>
                        <?php foreach ($order['items'] as $item): 
                            $itemBaseTotal = $item['product_price'] * $item['quantity'];
                            $itemTax = round($itemBaseTotal * 0.05);
                            $itemTotalWithTax = $itemBaseTotal + $itemTax;
                        ?>
                        <div class="order-item">
                            <img src="<?php echo $item['image_url'] ?? 'https://via.placeholder.com/70'; ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-meta">
                                    <span>Qty: <?php echo $item['quantity']; ?></span>
                                    <span>Price: <?php echo number_format($item['product_price']); ?> DZD</span>
                                    <?php if ($isSeller): ?>
                                        <span class="tax-badge">+5% tax</span>
                                        <span class="commission-badge">-5% commission</span>
                                    <?php endif; ?>
                                    <?php if (!$isSeller): ?>
                                        <span>Seller: <?php echo htmlspecialchars($item['seller_name'] ?? 'N/A'); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($isSeller): ?>
                                <div class="item-price-info">
                                    <div>Base price: <?php echo number_format($itemBaseTotal); ?> DZD</div>
                                    <div>+ Customer tax: <?php echo number_format($itemTax); ?> DZD</div>
                                    <div class="price-breakdown">You receive: <?php echo number_format($itemTotalWithTax); ?> DZD</div>
                                    <div class="price-breakdown" style="color: #d96565;">Commission to M7: -<?php echo number_format(round($itemBaseTotal * 0.05)); ?> DZD</div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="item-total">
                                <?php echo $isSeller ? number_format($itemTotalWithTax) : number_format($itemBaseTotal); ?> DZD
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-footer">
                        <div>
                            <?php if ($isSeller): ?>
                                <div class="order-total receive">
                                    You receive: <?php echo number_format($orderTotal); ?> DZD
                                    <small>(includes <?php echo number_format($orderTaxTotal); ?> DZD tax)</small>
                                </div>
                                <div style="margin-top: 5px; font-size: 14px; color: #d96565;">
                                    Commission to M7: <?php echo number_format($orderCommissionTotal); ?> DZD
                                </div>
                                <div style="margin-top: 5px; font-size: 16px; color: #4CAF50; font-weight: bold;">
                                    You keep: <?php echo number_format($orderTotal - $orderCommissionTotal); ?> DZD
                                </div>
                            <?php else: ?>
                                <div class="order-total">
                                    Total: <?php echo number_format($order['total']); ?> DZD
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($isSeller): ?>
                            <!-- Seller: Show commission info and status update -->
                            <?php if ($order['status'] === 'pending' || $order['status'] === 'processing' || $order['status'] === 'shipped'): ?>
                            <form method="POST" class="status-update-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="pending" <?php echo $order['status']=='pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                    <option value="processing" <?php echo $order['status']=='processing' ? 'selected' : ''; ?>>🔄 Processing</option>
                                    <option value="shipped" <?php echo $order['status']=='shipped' ? 'selected' : ''; ?>>🚚 Shipped</option>
                                    <option value="delivered" <?php echo $order['status']=='delivered' ? 'selected' : ''; ?>>✅ Delivered</option>
                                </select>
                                <button type="submit" name="update_status" class="update-btn">Update</button>
                            </form>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <!-- Buyer: Show Payment Method -->
                            <div style="background: rgba(255,255,255,0.05); padding: 10px 20px; border-radius: 50px;">
                                💳 <?php echo $order['payment_method'] === 'ccp' ? 'CCP Transfer' : 'Cash on Delivery'; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
    </main>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms</a> | <a href="privacy.php">Privacy</a></p>
    </footer>
    
    <script>
    function filterOrders(status) {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Filter orders
        const orders = document.querySelectorAll('.order-card');
        orders.forEach(order => {
            if (status === 'all') {
                order.style.display = 'block';
            } else {
                order.style.display = order.dataset.status === status ? 'block' : 'none';
            }
        });
    }
    </script>
    
    <script src="script.js"></script>
</body>
</html>