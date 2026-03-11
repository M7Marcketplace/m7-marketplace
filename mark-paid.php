<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
if ($currentUser['role'] !== 'admin') {
    header('Location: home.php');
    exit;
}

$message = '';
$messageType = '';

// Handle marking payment as paid
if (isset($_POST['mark_paid'])) {
    $sellerId = $_POST['seller_id'];
    $amount = $_POST['amount'];
    $paymentMethod = $_POST['payment_method'] ?? 'ccp';
    $reference = $_POST['reference'] ?? '';
    $period = $_POST['period'] ?? 'regular'; // 'free' or 'regular'
    
    // Insert into payments table
    $stmt = $pdo->prepare("
        INSERT INTO seller_payments (seller_id, amount, payment_method, payment_reference, status, paid_at, payment_period)
        VALUES (?, ?, ?, ?, 'paid', NOW(), ?)
    ");
    
    if ($stmt->execute([$sellerId, $amount, $paymentMethod, $reference, $period])) {
        // Update session
        $_SESSION['paid_' . $sellerId] = 'paid';
        $_SESSION['paid_date_' . $sellerId] = date('Y-m-d');
        $_SESSION['paid_period_' . $sellerId] = $period;
        
        $message = "Payment marked as paid successfully!";
        $messageType = "success";
    } else {
        $message = "Error marking payment as paid.";
        $messageType = "error";
    }
}

// Handle marking as unpaid
if (isset($_POST['mark_unpaid'])) {
    $sellerId = $_POST['seller_id'];
    
    $stmt = $pdo->prepare("UPDATE seller_payments SET status = 'cancelled' WHERE seller_id = ? AND status = 'paid' ORDER BY id DESC LIMIT 1");
    
    if ($stmt->execute([$sellerId])) {
        unset($_SESSION['paid_' . $sellerId]);
        unset($_SESSION['paid_date_' . $sellerId]);
        unset($_SESSION['paid_period_' . $sellerId]);
        
        $message = "Payment marked as unpaid.";
        $messageType = "warning";
    }
}

// Get all sellers
$sellers = $pdo->query("
    SELECT u.id, u.full_name, u.email, u.registration_date,
           s.store_name,
           (SELECT SUM(sold * price) FROM products WHERE seller_id = u.id) as total_revenue,
           (SELECT COUNT(*) FROM products WHERE seller_id = u.id) as product_count,
           (SELECT COUNT(*) FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE oi.seller_id = u.id AND o.status = 'delivered') as order_count
    FROM users u
    LEFT JOIN seller_stores s ON u.id = s.seller_id
    WHERE u.role = 'seller'
    ORDER BY u.registration_date DESC
")->fetchAll();

// Calculate commission based on 3-month free period
$now = new DateTime();
$totalCommissionDue = 0;
$totalCommissionPaid = 0;
$sellersInFreePeriod = 0;
$sellersInPaidPeriod = 0;

foreach ($sellers as &$seller) {
    $registrationDate = new DateTime($seller['registration_date']);
    $monthsSinceRegistration = $registrationDate->diff($now)->m + ($registrationDate->diff($now)->y * 12);
    $seller['in_free_period'] = $monthsSinceRegistration < 3;
    
    $revenue = $seller['total_revenue'] ?? 0;
    
    if ($seller['in_free_period']) {
        // Free period: Only customer tax (5%)
        $seller['commission_due'] = round($revenue * 0.05);
        $seller['commission_type'] = 'free';
        $sellersInFreePeriod++;
    } else {
        // Paid period: Customer tax + Seller commission (10% total)
        $seller['commission_due'] = round($revenue * 0.10);
        $seller['commission_type'] = 'regular';
        $sellersInPaidPeriod++;
    }
    
    // Check if paid (from database)
    $stmt = $pdo->prepare("SELECT * FROM seller_payments WHERE seller_id = ? AND status = 'paid' ORDER BY paid_at DESC LIMIT 1");
    $stmt->execute([$seller['id']]);
    $payment = $stmt->fetch();
    
    if ($payment) {
        $seller['paid'] = true;
        $seller['paid_date'] = $payment['paid_at'];
        $seller['paid_amount'] = $payment['amount'];
        $seller['payment_ref'] = $payment['payment_reference'];
        $seller['payment_period'] = $payment['payment_period'] ?? 'regular';
        $totalCommissionPaid += $payment['amount'];
    } else {
        $seller['paid'] = false;
        $totalCommissionDue += $seller['commission_due'];
    }
    
    // Calculate free period end date
    $freeEndDate = clone $registrationDate;
    $freeEndDate->modify('+3 months');
    $seller['free_end_date'] = $freeEndDate->format('d M Y');
    $seller['days_left'] = $seller['in_free_period'] ? $now->diff($freeEndDate)->days : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Payments - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .page-header h1 {
            font-size: 42px;
            margin: 0;
            background: linear-gradient(135deg, #fff, #d96565);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .summary-card .label {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 5px;
        }
        
        .summary-card .value {
            font-size: 32px;
            font-weight: 800;
        }
        
        .summary-card.due .value { color: #FF9800; }
        .summary-card.paid .value { color: #4CAF50; }
        .summary-card.free .value { color: #2196F3; }
        .summary-card.regular .value { color: #d96565; }
        
        .message {
            padding: 15px 20px;
            border-radius: 50px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: rgba(76,175,80,0.2);
            border: 1px solid #4CAF50;
            color: #4CAF50;
        }
        
        .message.error {
            background: rgba(217,101,101,0.2);
            border: 1px solid #d96565;
            color: #d96565;
        }
        
        .message.warning {
            background: rgba(255,152,0,0.2);
            border: 1px solid #FF9800;
            color: #FF9800;
        }
        
        .seller-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
        }
        
        .seller-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s ease;
        }
        
        .seller-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .seller-card.free-period {
            border-left: 5px solid #2196F3;
        }
        
        .seller-card.paid-period {
            border-left: 5px solid #d96565;
        }
        
        .seller-card.paid {
            border-left: 5px solid #4CAF50;
        }
        
        .seller-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .seller-header h3 {
            color: #d96565;
            margin: 0;
        }
        
        .period-badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .period-free {
            background: rgba(33,150,243,0.2);
            color: #2196F3;
            border: 1px solid #2196F3;
        }
        
        .period-paid {
            background: rgba(217,101,101,0.2);
            color: #d96565;
            border: 1px solid #d96565;
        }
        
        .payment-status {
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-paid {
            background: rgba(76,175,80,0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        
        .status-unpaid {
            background: rgba(255,152,0,0.2);
            color: #FF9800;
            border: 1px solid #FF9800;
        }
        
        .seller-details {
            margin: 15px 0;
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }
        
        .detail-row .label {
            opacity: 0.7;
        }
        
        .detail-row .value {
            font-weight: 600;
        }
        
        .commission-box {
            background: rgba(255,152,0,0.1);
            border: 1px solid #FF9800;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
        }
        
        .commission-label {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 5px;
        }
        
        .commission-amount {
            font-size: 36px;
            font-weight: 800;
            color: #FF9800;
            line-height: 1.2;
        }
        
        .commission-breakdown {
            font-size: 13px;
            margin-top: 10px;
            color: #FF9800;
        }
        
        .free-info {
            background: rgba(33,150,243,0.1);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            font-size: 14px;
            border: 1px solid #2196F3;
        }
        
        .free-info p {
            margin: 5px 0;
            color: #2196F3;
        }
        
        .paid-info {
            background: rgba(76,175,80,0.1);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            font-size: 14px;
            border: 1px solid #4CAF50;
        }
        
        .paid-info p {
            margin: 5px 0;
            color: #4CAF50;
        }
        
        .payment-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #d96565;
            font-size: 13px;
            font-weight: 600;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px 15px;
            border-radius: 10px;
            border: 2px solid transparent;
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #d96565;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-success {
            background: #4CAF50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: #FF9800;
            color: white;
        }
        
        .btn-warning:hover {
            background: #F57C00;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background: #2196F3;
            color: white;
        }
        
        .btn-info:hover {
            background: #1976D2;
            transform: translateY(-2px);
        }
        
        .back-btn {
            background: #666;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-btn:hover {
            background: #555;
            transform: translateY(-2px);
        }
        
        @media (max-width: 1024px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="payment-container">
        <div class="page-header">
            <h1>💰 Mark Seller Payments</h1>
            <a href="admin-panel.php" class="back-btn">← Back to Admin Panel</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card free">
                <div class="label">Sellers in Free Period</div>
                <div class="value"><?php echo $sellersInFreePeriod; ?></div>
                <div>Pay only customer tax (5%)</div>
            </div>
            <div class="summary-card regular">
                <div class="label">Sellers in Paid Period</div>
                <div class="value"><?php echo $sellersInPaidPeriod; ?></div>
                <div>Pay customer tax + commission (10%)</div>
            </div>
            <div class="summary-card due">
                <div class="label">Total Commission Due</div>
                <div class="value"><?php echo number_format($totalCommissionDue); ?> DZD</div>
            </div>
            <div class="summary-card paid">
                <div class="label">Total Commission Paid</div>
                <div class="value"><?php echo number_format($totalCommissionPaid); ?> DZD</div>
            </div>
        </div>
        
        <!-- Sellers Grid -->
        <div class="seller-grid">
            <?php foreach ($sellers as $seller): ?>
            <div class="seller-card 
                <?php echo $seller['paid'] ? 'paid' : ($seller['in_free_period'] ? 'free-period' : 'paid-period'); ?>">
                
                <div class="seller-header">
                    <h3><?php echo htmlspecialchars($seller['store_name'] ?? $seller['full_name']); ?></h3>
                    <span class="period-badge <?php echo $seller['in_free_period'] ? 'period-free' : 'period-paid'; ?>">
                        <?php echo $seller['in_free_period'] ? '🎁 Free Period' : '💰 Paid Period'; ?>
                    </span>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span><?php echo htmlspecialchars($seller['full_name']); ?></span>
                    <span class="payment-status <?php echo $seller['paid'] ? 'status-paid' : 'status-unpaid'; ?>">
                        <?php echo $seller['paid'] ? '✅ PAID' : '⏳ UNPAID'; ?>
                    </span>
                </div>
                
                <div class="seller-details">
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo htmlspecialchars($seller['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Registered:</span>
                        <span class="value"><?php echo date('d M Y', strtotime($seller['registration_date'])); ?></span>
                    </div>
                    <?php if ($seller['in_free_period']): ?>
                    <div class="detail-row">
                        <span class="label">Free period ends:</span>
                        <span class="value" style="color: #2196F3;"><?php echo $seller['free_end_date']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Days left:</span>
                        <span class="value" style="color: #2196F3;"><?php echo $seller['days_left']; ?> days</span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="label">Products:</span>
                        <span class="value"><?php echo $seller['product_count']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Orders:</span>
                        <span class="value"><?php echo $seller['order_count']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Revenue:</span>
                        <span class="value"><?php echo number_format($seller['total_revenue'] ?? 0); ?> DZD</span>
                    </div>
                </div>
                
                <div class="commission-box">
                    <div class="commission-label">Amount to Pay</div>
                    <div class="commission-amount"><?php echo number_format($seller['commission_due']); ?> DZD</div>
                    <?php if ($seller['in_free_period']): ?>
                        <div class="commission-breakdown">(5% customer tax only)</div>
                    <?php else: ?>
                        <div class="commission-breakdown">(5% customer tax + 5% seller commission)</div>
                    <?php endif; ?>
                </div>
                
                <?php if ($seller['in_free_period']): ?>
                    <div class="free-info">
                        <p>🎁 <strong>Free Period:</strong> Only customer tax (5%)</p>
                        <p>Example: 1000 DA product → Pay 50 DA</p>
                    </div>
                <?php else: ?>
                    <div class="free-info" style="background: rgba(217,101,101,0.1); border-color: #d96565;">
                        <p>💰 <strong>Paid Period:</strong> Customer tax + seller commission (10%)</p>
                        <p>Example: 1000 DA product → Pay 100 DA</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($seller['paid']): ?>
                    <div class="paid-info">
                        <p><strong>✅ Paid on:</strong> <?php echo date('d M Y', strtotime($seller['paid_date'])); ?></p>
                        <p><strong>Amount:</strong> <?php echo number_format($seller['paid_amount']); ?> DZD</p>
                        <p><strong>Period:</strong> <?php echo $seller['payment_period'] == 'free' ? 'Free Period (5%)' : 'Paid Period (10%)'; ?></p>
                        <?php if ($seller['payment_ref']): ?>
                            <p><strong>Reference:</strong> <?php echo htmlspecialchars($seller['payment_ref']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" class="payment-form">
                        <input type="hidden" name="seller_id" value="<?php echo $seller['id']; ?>">
                        <input type="hidden" name="amount" value="<?php echo $seller['commission_due']; ?>">
                        <button type="submit" name="mark_unpaid" class="btn btn-warning" onclick="return confirm('Mark this payment as unpaid?')">
                            ↩️ Mark as Unpaid
                        </button>
                    </form>
                    
                <?php else: ?>
                    <form method="POST" class="payment-form">
                        <input type="hidden" name="seller_id" value="<?php echo $seller['id']; ?>">
                        <input type="hidden" name="amount" value="<?php echo $seller['commission_due']; ?>">
                        <input type="hidden" name="period" value="<?php echo $seller['in_free_period'] ? 'free' : 'regular'; ?>">
                        
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" required>
                                <option value="ccp">CCP Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Reference / Transaction ID</label>
                            <input type="text" name="reference" placeholder="e.g., CCP-123456">
                        </div>
                        
                        <button type="submit" name="mark_paid" class="btn <?php echo $seller['in_free_period'] ? 'btn-info' : 'btn-success'; ?>" onclick="return confirm('Mark this payment as received?')">
                            <?php echo $seller['in_free_period'] ? '🎁 Mark Free Period Payment (5%)' : '💰 Mark Paid Period Payment (10%)'; ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved.</p>
    </footer>
    
    <script src="script.js"></script>
</body>
</html>