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

// Get seller's store info
$stmt = $pdo->prepare("SELECT * FROM seller_stores WHERE seller_id = ?");
$stmt->execute([$currentUser['id']]);
$store = $stmt->fetch();

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

foreach ($products as $product) {
    $totalStock += $product['quantity'];
    $totalSold += $product['sold'];
    $totalRevenue += $product['sold'] * $product['price'];
}

$commission = round($totalRevenue * 0.05); // 5% commission
$netEarnings = $totalRevenue - $commission;

// Calculate commission status based on registration date
$registrationDate = new DateTime($currentUser['registration_date']);
$now = new DateTime();
$monthsSinceRegistration = $registrationDate->diff($now)->m + ($registrationDate->diff($now)->y * 12);
$isInFreePeriod = $monthsSinceRegistration < 3;

// Calculate when free period ends
$freePeriodEndDate = clone $registrationDate;
$freePeriodEndDate->modify('+3 months');
$daysUntilPaidPeriod = $now->diff($freePeriodEndDate)->days;

// Get latest order for deadline calculation (only if not in free period)
$latestOrderDate = null;
$daysLeft = 0;
$hasOverdue = false;
$overdueOrders = [];

if (!$isInFreePeriod && $totalSold > 0) {
    // Get all delivered orders that are overdue
    $stmt = $pdo->prepare("
        SELECT o.id, o.order_number, o.created_at, o.total,
               oi.product_name, oi.quantity, oi.product_price
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE oi.seller_id = ? AND o.status = 'delivered'
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$currentUser['id']]);
    $deliveredOrders = $stmt->fetchAll();
    
    $overdueOrders = [];
    foreach ($deliveredOrders as $order) {
        $orderDate = new DateTime($order['created_at']);
        $deadline = clone $orderDate;
        $deadline->modify('+10 days');
        
        if ($deadline < $now) {
            // This order is overdue
            $order['days_overdue'] = $now->diff($deadline)->days;
            $order['commission_due'] = round($order['product_price'] * $order['quantity'] * 0.05);
            $overdueOrders[] = $order;
        }
    }
    
    $hasOverdue = !empty($overdueOrders);
    
    // Get latest order for countdown
    $stmt = $pdo->prepare("
        SELECT o.created_at, o.status 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE oi.seller_id = ? AND o.status = 'delivered'
        ORDER BY o.created_at DESC LIMIT 1
    ");
    $stmt->execute([$currentUser['id']]);
    $latestOrder = $stmt->fetch();
    
    if ($latestOrder) {
        $latestOrderDate = new DateTime($latestOrder['created_at']);
        $deadline = clone $latestOrderDate;
        $deadline->modify('+10 days');
        
        if ($deadline > $now) {
            $daysLeft = $now->diff($deadline)->days;
        }
    }
}

// Send email notification if there are overdue orders
if ($hasOverdue && isset($_SESSION['last_overdue_check']) && $_SESSION['last_overdue_check'] < date('Y-m-d')) {
    // Only send once per day to avoid spam
    $to = "m7.contact.us@gmail.com";
    $subject = "⚠️ Payment Overdue Alert - Seller: " . $currentUser['full_name'];
    
    $message = "
    <html>
    <head>
        <title>Payment Overdue Notification</title>
    </head>
    <body>
        <h2>⚠️ Payment Overdue Alert</h2>
        <p><strong>Seller:</strong> " . $currentUser['full_name'] . "</p>
        <p><strong>Store:</strong> " . ($store['store_name'] ?? 'No store') . "</p>
        <p><strong>Email:</strong> " . $currentUser['email'] . "</p>
        <p><strong>Phone:</strong> " . ($currentUser['phone'] ?? 'Not provided') . "</p>
        
        <h3>Overdue Orders:</h3>
        <table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
            <tr style='background: #d96565; color: white;'>
                <th>Order #</th>
                <th>Date</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Commission Due</th>
                <th>Days Overdue</th>
            </tr>";
    
    $totalCommissionDue = 0;
    foreach ($overdueOrders as $order) {
        $totalCommissionDue += $order['commission_due'];
        $message .= "
            <tr>
                <td>{$order['order_number']}</td>
                <td>" . date('d M Y', strtotime($order['created_at'])) . "</td>
                <td>{$order['product_name']} x{$order['quantity']}</td>
                <td>" . number_format($order['product_price'] * $order['quantity']) . " DZD</td>
                <td><strong>" . number_format($order['commission_due']) . " DZD</strong></td>
                <td style='color: #d96565; font-weight: bold;'>{$order['days_overdue']} days</td>
            </tr>";
    }
    
    $message .= "
        </table>
        
        <p><strong>Total Commission Due:</strong> " . number_format($totalCommissionDue) . " DZD</p>
        
        <h3>Payment Details:</h3>
        <p><strong>🏦 CCP Account:</strong> 88 0042745945</p>
        <p><strong>👤 Name:</strong> M7 Marketplace</p>
        <p><strong>📝 Reference:</strong> M7-{$currentUser['id']}</p>
        
        <p style='color: #d96565;'><strong>⚠️ This payment is overdue. Please contact the seller immediately.</strong></p>
    </body>
    </html>
    ";
    
    // Headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: M7 Marketplace <noreply@m7marketplace.com>" . "\r\n";
    
    // Send email
    mail($to, $subject, $message, $headers);
    
    // Update last check
    $_SESSION['last_overdue_check'] = date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .dashboard-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px;
            border-radius: 40px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .welcome-section::before {
            content: '📦';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            bottom: -30px;
            right: -30px;
            transform: rotate(-15deg);
        }
        
        .welcome-text h1 {
            font-size: 42px;
            margin-bottom: 10px;
            color: white;
        }
        
        .welcome-text p {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .logout-btn:hover {
            background: white;
            color: #764ba2;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 25px;
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
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .stat-label {
            font-size: 16px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 42px;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }
        
        .section-title {
            font-size: 32px;
            margin: 50px 0 30px;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100px;
            height: 4px;
            background: linear-gradient(135deg, #d96565 0%, #4CAF50 100%);
            border-radius: 2px;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 50px;
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 25px;
            padding: 35px 25px;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .action-card::before {
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
        
        .action-card:hover {
            transform: translateY(-15px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        
        .action-card:hover::before {
            transform: scaleX(1);
        }
        
        .action-icon {
            font-size: 64px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .action-card:hover .action-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .action-card h3 {
            color: #d96565;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .action-card p {
            opacity: 0.8;
            font-size: 14px;
            margin: 0;
        }
        
        .recent-products {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 30px;
            margin-top: 30px;
        }
        
        .recent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .recent-header h3 {
            color: #d96565;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .view-all {
            color: #d96565;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .view-all:hover {
            color: white;
            text-decoration: underline;
        }
        
        .product-item {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }
        
        .product-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
            border-color: #d96565;
        }
        
        .product-thumb {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid rgba(217, 101, 101, 0.3);
        }
        
        .product-info {
            flex: 1;
        }
        
        .product-info h4 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #d96565;
        }
        
        .product-info p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .product-price {
            font-weight: 600;
            color: #4CAF50;
            font-size: 16px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .product-btn {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
        }
        
        .product-btn.edit {
            background: #4CAF50;
            color: white;
        }
        
        .product-btn.edit:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        .product-btn.delete {
            background: #d96565;
            color: white;
        }
        
        .product-btn.delete:hover {
            background: #b84343;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 30px;
        }
        
        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .empty-state h3 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #d96565;
        }
        
        .empty-state p {
            font-size: 16px;
            opacity: 0.8;
            margin-bottom: 25px;
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
        
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: linear-gradient(135deg, #1a1a1a, #2a2a2a);
            padding: 40px;
            border-radius: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            animation: slideUp 0.4s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-content h2 {
            color: #d96565;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .modal-content h3 {
            color: #4CAF50;
            font-size: 20px;
            margin: 20px 0 10px;
        }
        
        .modal-content .instruction-box {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
        }
        
        .modal-content .instruction-box p {
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .modal-content .instruction-box strong {
            color: #d96565;
        }
        
        .modal-content .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        
        .modal-content .qr-code img {
            width: 200px;
            height: 200px;
            border-radius: 15px;
            border: 3px solid #4CAF50;
            background: white;
            padding: 10px;
        }
        
        .modal-content .copy-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px 5px;
        }
        
        .modal-content .copy-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(76,175,80,0.4);
        }
        
        .modal-content .close-btn {
            background: #666;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px 5px;
        }
        
        .modal-content .close-btn:hover {
            background: #555;
            transform: translateY(-2px);
        }
        
        .modal-content .payment-details {
            background: rgba(76,175,80,0.1);
            border: 1px solid #4CAF50;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .modal-content .payment-details p {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .modal-content .payment-details .reference {
            font-family: monospace;
            font-size: 18px;
            background: rgba(0,0,0,0.3);
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
            color: #4CAF50;
        }
        
        .modal-content .steps {
            list-style: none;
            padding: 0;
        }
        
        .modal-content .steps li {
            margin: 15px 0;
            padding-left: 30px;
            position: relative;
        }
        
        .modal-content .steps li:before {
            content: "📌";
            position: absolute;
            left: 0;
            color: #d96565;
        }
        
        .deadline-section {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            padding: 30px;
            border-radius: 20px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 152, 0, 0.3);
        }
        
        .deadline-section::before {
            content: '⏰';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            bottom: -30px;
            right: -20px;
            transform: rotate(-15deg);
        }
        
        .deadline-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .deadline-icon {
            font-size: 64px;
        }
        
        .deadline-title {
            flex: 1;
        }
        
        .deadline-title h3 {
            color: white;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .deadline-title p {
            color: white;
            opacity: 0.9;
        }
        
        .deadline-timer {
            background: rgba(255,255,255,0.2);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .deadline-timer .days {
            font-size: 48px;
            font-weight: 800;
            color: white;
            line-height: 1;
        }
        
        .deadline-timer .label {
            color: white;
            opacity: 0.8;
            font-size: 14px;
        }
        
        .deadline-rules {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        
        .rule-card {
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
        
        .rule-card .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .rule-card h4 {
            color: white;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .rule-card p {
            color: white;
            opacity: 0.8;
            font-size: 13px;
        }
        
        .deadline-payment-info {
            background: rgba(0,0,0,0.2);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
        }
        
        .deadline-payment-info p {
            color: white;
            margin-bottom: 10px;
        }
        
        .deadline-payment-info strong {
            color: white;
            font-weight: 600;
        }
        
        .deadline-warning {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            font-size: 13px;
            color: white;
            text-align: center;
        }
        
        .free-period-section {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            padding: 30px;
            border-radius: 20px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(76, 175, 80, 0.3);
        }
        
        .free-period-section::before {
            content: '🎁';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            bottom: -30px;
            right: -20px;
            transform: rotate(-15deg);
        }
        
        .overdue-section {
            background: linear-gradient(135deg, #d96565 0%, #b84343 100%);
            padding: 30px;
            border-radius: 20px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(217, 101, 101, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(217, 101, 101, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(217, 101, 101, 0); }
            100% { box-shadow: 0 0 0 0 rgba(217, 101, 101, 0); }
        }
        
        .overdue-section::before {
            content: '⚠️';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            bottom: -30px;
            right: -20px;
            transform: rotate(-15deg);
        }
        
        .overdue-list {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .overdue-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .overdue-item:last-child {
            border-bottom: none;
        }
        
        .overdue-item .order-info {
            flex: 1;
        }
        
        .overdue-item .order-info strong {
            color: white;
            font-size: 16px;
        }
        
        .overdue-item .order-info small {
            color: white;
            opacity: 0.8;
            display: block;
        }
        
        .overdue-item .amount {
            font-weight: bold;
            color: white;
            font-size: 18px;
        }
        
        .overdue-item .days {
            background: rgba(255,255,255,0.3);
            padding: 5px 15px;
            border-radius: 50px;
            color: white;
            font-weight: bold;
            margin-left: 15px;
        }
        
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .welcome-section {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                padding: 30px;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .product-item {
                flex-direction: column;
                text-align: center;
            }
            
            .product-actions {
                justify-content: center;
            }
            
            .deadline-rules {
                grid-template-columns: 1fr;
            }
            
            .deadline-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<main>
    <div class="dashboard-wrapper">
        <div id="dashboard-content">
            <div class="welcome-section">
                <div class="welcome-text">
                    <h1>📦 Welcome back, <?php echo htmlspecialchars($store['store_name'] ?? $currentUser['full_name']); ?>!</h1>
                    <p>Manage your products, track sales, and grow your business.</p>
                </div>
                <a href="logout.php" class="logout-btn">🚪 Logout</a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo $totalProducts; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-label">In Stock</div>
                    <div class="stat-value"><?php echo $totalStock; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-label">Revenue</div>
                    <div class="stat-value"><?php echo number_format($totalRevenue); ?> DZD</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-label">Items Sold</div>
                    <div class="stat-value"><?php echo $totalSold; ?></div>
                </div>
            </div>
            
            <!-- PAYMENT STATUS SECTION -->
            <?php if ($isInFreePeriod): ?>
                <!-- Free Period Message -->
                <div class="free-period-section">
                    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <div style="font-size: 64px;">🎁</div>
                        <div style="flex: 1;">
                            <h3 style="color: white; margin-bottom: 10px; font-size: 28px;">You're in your 3-month free period!</h3>
                            <p style="color: white; opacity: 0.9; margin-bottom: 15px;">No commission payments required until <?php echo $freePeriodEndDate->format('d M Y'); ?></p>
                            
                            <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 15px; display: inline-block;">
                                <span style="color: white;">⏳ Time left in free period: </span>
                                <strong style="color: white; font-size: 24px; margin-left: 10px;"><?php echo $daysUntilPaidPeriod; ?> days</strong>
                            </div>
                        </div>
                    </div>
                    
                    <p style="color: white; margin-top: 20px; opacity: 0.8; font-size: 14px;">
                        ⚠️ After your free period ends, you'll have 10 days to pay your 5% commission on each sale.
                    </p>
                </div>
            <?php elseif ($hasOverdue): ?>
                <!-- Overdue Payments Alert -->
                <div class="overdue-section">
                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                        <div style="font-size: 64px;">⚠️</div>
                        <div>
                            <h3 style="color: white; margin-bottom: 5px; font-size: 28px;">Payment Overdue!</h3>
                            <p style="color: white; opacity: 0.9;">You have <?php echo count($overdueOrders); ?> overdue payment(s)</p>
                        </div>
                    </div>
                    
                    <div class="overdue-list">
                        <?php 
                        $totalOverdue = 0;
                        foreach ($overdueOrders as $order): 
                            $totalOverdue += $order['commission_due'];
                        ?>
                        <div class="overdue-item">
                            <div class="order-info">
                                <strong><?php echo $order['order_number']; ?></strong>
                                <small><?php echo $order['product_name']; ?> x<?php echo $order['quantity']; ?> - <?php echo date('d M Y', strtotime($order['created_at'])); ?></small>
                            </div>
                            <div class="amount"><?php echo number_format($order['commission_due']); ?> DZD</div>
                            <div class="days"><?php echo $order['days_overdue']; ?> days overdue</div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid rgba(255,255,255,0.3); text-align: right;">
                            <strong style="color: white; font-size: 20px;">Total Due: <?php echo number_format($totalOverdue); ?> DZD</strong>
                        </div>
                    </div>
                    
                    <div class="deadline-payment-info" style="margin-top: 20px;">
                        <p><strong>🏦 CCP Account:</strong> 88 0042745945</p>
                        <p><strong>👤 Name:</strong> M7 Marketplace</p>
                        <p><strong>📝 Reference:</strong> M7-<?php echo $currentUser['id']; ?></p>
                        <button onclick="showPaymentInstructions()" class="btn" style="margin-top: 10px; background: white; color: #d96565;">📋 View Payment Instructions</button>
                    </div>
                    
                    <div class="deadline-warning" style="background: rgba(255,255,255,0.3);">
                        ⚠️ Immediate payment required to avoid account suspension. An email has been sent to the administrator.
                    </div>
                </div>
            <?php elseif (!$isInFreePeriod && $totalSold > 0): ?>
                <!-- Normal Deadline Section -->
                <div class="deadline-section">
                    <div class="deadline-header">
                        <div class="deadline-icon">⏰</div>
                        <div class="deadline-title">
                            <h3>10-Day Payment Deadline</h3>
                            <p>Please pay your 5% commission within 10 days of order delivery</p>
                        </div>
                    </div>
                    
                    <?php if ($latestOrderDate): ?>
                        <div class="deadline-timer">
                            <div class="days"><?php echo $daysLeft; ?></div>
                            <div class="label">Days remaining to pay for most recent order</div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="deadline-rules">
                        <div class="rule-card">
                            <div class="icon">📅</div>
                            <h4>10 Days</h4>
                            <p>Pay within 10 days of delivery</p>
                        </div>
                        <div class="rule-card">
                            <div class="icon">💰</div>
                            <h4>5% Commission</h4>
                            <p>Of your total revenue</p>
                        </div>
                        <div class="rule-card">
                            <div class="icon">📧</div>
                            <h4>Reminders</h4>
                            <p>Email reminder 3 days before</p>
                        </div>
                    </div>
                    
                    <div class="deadline-payment-info">
                        <p><strong>🏦 CCP Account:</strong> 88 0042745945</p>
                        <p><strong>👤 Name:</strong> M7 Marketplace</p>
                        <p><strong>📝 Reference:</strong> M7-<?php echo $currentUser['id']; ?></p>
                        <button onclick="showPaymentInstructions()" class="btn" style="margin-top: 10px; background: white; color: #FF9800;">📋 View Payment Instructions</button>
                    </div>
                    
                    <div class="deadline-warning">
                        ⚠️ Late payments may result in account suspension. Please pay on time.
                    </div>
                </div>
            <?php endif; ?>
            
            <h2 class="section-title">⚡ Quick Actions</h2>
            
            <div class="quick-actions">
                <a href="seller-add-product.php" class="action-card">
                    <div class="action-icon">➕</div>
                    <h3>Add Product</h3>
                    <p>List a new item for sale</p>
                </a>
                <a href="my-products.php" class="action-card">
                    <div class="action-icon">📋</div>
                    <h3>My Products</h3>
                    <p>View and manage listings</p>
                </a>
                <a href="orders.php" class="action-card">
                    <div class="action-icon">📦</div>
                    <h3>My Orders</h3>
                    <p>View customer orders</p>
                </a>
                <a href="tax-dashboard.php" class="action-card">
                    <div class="action-icon">💰</div>
                    <h3>Tax & Commission</h3>
                    <p>View your earnings and taxes</p>
                </a>
            </div>
            
            <?php if (count($products) > 0): ?>
            <div class="recent-products">
                <div class="recent-header">
                    <h3>🆕 Recent Products</h3>
                    <a href="my-products.php" class="view-all">View All →</a>
                </div>
                
                <?php 
                $recentProducts = array_slice($products, 0, 5);
                foreach ($recentProducts as $product): 
                ?>
                <div class="product-item">
                    <img src="<?php echo $product['image_url'] ?? 'https://via.placeholder.com/60'; ?>" class="product-thumb" onerror="this.src='https://via.placeholder.com/60'">
                    <div class="product-info">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p>Stock: <?php echo $product['quantity']; ?> units | Price: <?php echo number_format($product['price']); ?> DZD</p>
                    </div>
                    <div class="product-price"><?php echo number_format($product['price']); ?> DZD</div>
                    <div class="product-actions">
                        <a href="seller-add-product.php?edit=<?php echo $product['id']; ?>" class="product-btn edit">✏️ Edit</a>
                        <a href="delete-product.php?id=<?php echo $product['id']; ?>" class="product-btn delete" onclick="return confirm('Are you sure?')">🗑️</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Products Yet</h3>
                <p>Start selling by adding your first product!</p>
                <a href="seller-add-product.php" class="empty-btn">Add Your First Product</a>
            </div>
            <?php endif; ?>
            
            <!-- Payment Section -->
            <div style="margin-top: 40px; background: rgba(217, 101, 101, 0.1); padding: 30px; border-radius: 20px;">
                <h3 style="color: #d96565; margin-bottom: 20px;">🏦 Payment to M7 Marketplace</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <p><strong>Total Revenue:</strong> <span id="totalRevenue"><?php echo number_format($totalRevenue); ?> DZD</span></p>
                        <p><strong>Commission (5%):</strong> <span id="commissionDue"><?php echo number_format($commission); ?> DZD</span></p>
                        <p><strong>Net Earnings:</strong> <span id="netEarnings"><?php echo number_format($netEarnings); ?> DZD</span></p>
                        <p><strong>Status:</strong> 
                            <?php if ($hasOverdue): ?>
                                <span style="color: #d96565; font-weight: bold;">⚠️ Overdue</span>
                            <?php elseif ($isInFreePeriod): ?>
                                <span style="color: #4CAF50; font-weight: bold;">✅ Free Period</span>
                            <?php else: ?>
                                <span style="color: #FF9800;">Pending</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <h4 style="color: #4CAF50;">🏦 CCP Account Details</h4>
                        <p><strong>Account:</strong> 88 0042745945</p>
                        <p><strong>Name:</strong> M7 Marketplace</p>
                        <p><strong>Reference:</strong> <span id="sellerRef">M7-<?php echo $currentUser['id']; ?></span></p>
                        <button onclick="showPaymentInstructions()" class="btn btn-sm">📋 View Instructions</button>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; text-align: center;">
                <button onclick="showCCPPayment()" class="btn" style="background: #4CAF50; padding: 15px 30px;">
                    💳 Pay via CCP Mobile
                </button>
            </div>
            
            <div style="background: rgba(76, 175, 80, 0.1); padding: 30px; border-radius: 20px; margin-top: 30px;">
                <h3 style="color: #4CAF50;">💰 Pay via CCP</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <p><strong>Our CCP Account:</strong></p>
                        <p style="background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px; font-family: monospace; font-size: 18px;">
                            0042745945
                        </p>
                        <p><strong>Key:</strong> 88</p>
                    </div>
                    
                    <div>
                        <p><strong>Amount Due:</strong> <span id="commissionAmount"><?php echo number_format($commission); ?> DZD</span></p>
                        <p><strong>Reference:</strong> <span id="paymentRef">M7-<?php echo $currentUser['id']; ?></span></p>
                        
                        <button onclick="calculateAndCopy()" class="btn btn-sm" style="margin-top: 10px;">
                            📋 Copy Payment Details
                        </button>
                        <button onclick="showPaymentInstructions()" class="btn btn-sm" style="margin-top: 10px; background: #FF9800;">
                            📋 View Full Instructions
                        </button>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 20px; justify-content: center; margin-top: 20px;">
                <a href="https://play.google.com/store/apps/details?id=ru.bpc.mobilebank.bpc" target="_blank" class="btn" style="background: #4CAF50;">
                    📲 Download BaridiMob on Google Play
                </a>
                <a href="https://apps.apple.com/fr/app/baridimob/id1481839638" target="_blank" class="btn" style="background: #4CAF50;">
                    📲 Download BaridiMob on App Store
                </a>
            </div>
        </div>
    </div>
</main>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script src="script.js"></script>
<script>
function showPaymentInstructions() {
    let amount = document.getElementById('commissionDue')?.textContent || '0 DZD';
    let reference = document.getElementById('sellerRef')?.textContent || 'M7-XXX';
    
    // Create modal
    let modal = document.createElement('div');
    modal.className = 'modal';
    
    modal.innerHTML = `
        <div class="modal-content">
            <h2>📝 Payment Instructions</h2>
            
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=CCP:${amount} - Ref:${reference}" alt="QR Code">
            </div>
            
            <div class="payment-details">
                <p><strong>🏦 CCP Account:</strong> 88 0042745945</p>
                <p><strong>👤 Name:</strong> M7 Marketplace</p>
                <p><strong>💰 Amount:</strong> ${amount}</p>
                <p><strong>📝 Reference:</strong> <span class="reference">${reference}</span></p>
            </div>
            
            <h3>📱 BaridiMob Instructions</h3>
            <div class="instruction-box">
                <ul class="steps">
                    <li><strong>Open BaridiMob</strong> app on your phone</li>
                    <li>Click on <strong>"Paiement"</strong> tab</li>
                    <li>Select <strong>"Ajouter un bénéficiaire"</strong> if first time</li>
                    <li>Enter our CCP: <strong>88 0042745945</strong></li>
                    <li>Enter Amount: <strong>${amount}</strong></li>
                    <li>Enter Reference: <strong>${reference}</strong></li>
                    <li>Enter your OTP code</li>
                    <li>Save the receipt</li>
                </ul>
            </div>
            
            <h3>💻 CCP Web Instructions</h3>
            <div class="instruction-box">
                <ul class="steps">
                    <li>Log in to your CCP account</li>
                    <li>Go to <strong>"Virements"</strong> section</li>
                    <li>Select <strong>"Nouveau virement"</strong></li>
                    <li>Enter beneficiary: <strong>88 0042745945</strong></li>
                    <li>Amount: <strong>${amount}</strong></li>
                    <li>Reference: <strong>${reference}</strong></li>
                    <li>Confirm with your OTP</li>
                </ul>
            </div>
            
            <h3>📧 After Payment</h3>
            <div class="instruction-box">
                <p>Send screenshot of payment to:</p>
                <p style="font-size: 18px; color: #4CAF50;"><strong>m7.contact.us@gmail.com</strong></p>
                <p>We'll confirm within 24 hours</p>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <button onclick="copyPaymentDetails()" class="copy-btn">📋 Copy All Details</button>
                <button onclick="closePaymentModal()" class="close-btn">Close</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function showCCPPayment() {
    let amount = document.getElementById('commissionAmount')?.textContent || '0 DZD';
    let reference = document.getElementById('paymentRef')?.textContent || 'M7-XXX';
    
    let modal = document.createElement('div');
    modal.className = 'modal';
    
    modal.innerHTML = `
        <div class="modal-content">
            <h2 style="color: #d96565;">💳 CCP Mobile Payment</h2>
            
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=CCP:${amount} - Ref:${reference}" alt="QR Code">
            </div>
            
            <p style="font-size: 18px; margin: 20px 0; text-align: center;">
                <strong>Amount:</strong> ${amount}
            </p>
            
            <div class="instruction-box">
                <h3 style="color: #4CAF50;">📋 BaridiMob Instructions</h3>
                <ul class="steps">
                    <li>Open <strong>BaridiMob</strong> app</li>
                    <li>Click on <strong>"Paiement"</strong> tab</li>
                    <li>Select <strong>"Ajouter un bénéficiaire"</strong></li>
                    <li>Enter our CCP: <strong>88 0042745945</strong></li>
                    <li>Amount: <strong>${amount}</strong></li>
                    <li>Reference: <strong>${reference}</strong></li>
                    <li>Enter your OTP code</li>
                    <li>Save the receipt</li>
                </ul>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <button onclick="copyPaymentDetails()" class="copy-btn">📋 Copy Details</button>
                <button onclick="closePaymentModal()" class="close-btn">Close</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function copyPaymentDetails() {
    let amount = document.getElementById('commissionAmount')?.textContent || '0 DZD';
    let reference = document.getElementById('paymentRef')?.textContent || 'M7-XXX';
    
    let text = `M7 MARKETPLACE PAYMENT
━━━━━━━━━━━━━━━━━━━
CCP: 0042745945
Key: 88
Amount: ${amount}
Reference: ${reference}
Date: ${new Date().toLocaleDateString()}

BARIDIMOB INSTRUCTIONS:
1. Open BaridiMob app
2. Click on "Paiement" tab
3. Select "Ajouter un bénéficiaire"
4. Enter CCP: 88 0042745945
5. Amount: ${amount}
6. Reference: ${reference}
7. Enter OTP code
8. Save receipt

After payment, send screenshot to: m7.contact.us@gmail.com`;

    navigator.clipboard.writeText(text);
    alert('✅ Payment details copied to clipboard!');
}

function closePaymentModal() {
    let modal = document.querySelector('.modal');
    if (modal) modal.remove();
}

function calculateAndCopy() {
    copyPaymentDetails();
}
</script>
</body>
</html>