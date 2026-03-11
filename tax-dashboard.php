<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
$isAdmin = ($currentUser['role'] === 'admin');
$isSeller = ($currentUser['role'] === 'seller');

// Get date filters
$filter = $_GET['filter'] ?? 'all';
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Build date condition
$dateCondition = "";
if ($filter === 'today') {
    $dateCondition = "AND DATE(o.created_at) = CURDATE()";
} elseif ($filter === 'week') {
    $dateCondition = "AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter === 'month') {
    $dateCondition = "AND o.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
} elseif ($filter === 'custom' && $startDate && $endDate) {
    $dateCondition = "AND DATE(o.created_at) BETWEEN '$startDate' AND '$endDate'";
}

// Get tax and commission data
if ($isAdmin) {
    // Admin sees all data
    $stmt = $pdo->query("
        SELECT 
            o.id,
            o.order_number,
            o.created_at,
            o.subtotal,
            o.tax,
            o.total,
            o.status,
            u.full_name as seller_name,
            u.id as seller_id,
            s.store_name,
            oi.product_name,
            oi.quantity,
            oi.product_price,
            ROUND(oi.product_price * oi.quantity * 0.05) as customer_tax,
            ROUND(oi.product_price * oi.quantity * 0.05) as seller_commission
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN users u ON oi.seller_id = u.id
        LEFT JOIN seller_stores s ON u.id = s.seller_id
        WHERE o.status = 'delivered'
        $dateCondition
        ORDER BY o.created_at DESC
    ");
    $transactions = $stmt->fetchAll();
    
    // Calculate totals for admin
    $totalProductValue = 0;
    $totalTax = 0;
    $totalCommission = 0;
    $totalCustomerPaid = 0;
    $totalSellerReceives = 0;
    
    foreach ($transactions as $t) {
        $productTotal = $t['product_price'] * $t['quantity'];
        $totalProductValue += $productTotal;
        $totalTax += $t['customer_tax'];
        $totalCommission += $t['seller_commission'];
        $totalCustomerPaid += $productTotal + $t['customer_tax'];
        $totalSellerReceives += $productTotal + $t['customer_tax'];
    }
    
    $totalM7Receives = $totalTax + $totalCommission;
    $totalSellerKeeps = $totalSellerReceives - $totalM7Receives;
    
    // Get seller summaries
    $sellerSummaries = [];
    foreach ($transactions as $t) {
        $sellerId = $t['seller_id'];
        if (!isset($sellerSummaries[$sellerId])) {
            $sellerSummaries[$sellerId] = [
                'name' => $t['seller_name'],
                'store' => $t['store_name'] ?? $t['seller_name'],
                'product_sales' => 0,
                'customer_tax' => 0,
                'seller_commission' => 0,
                'customer_paid' => 0,
                'seller_received' => 0,
                'seller_keeps' => 0,
                'm7_receives' => 0,
                'orders' => 0
            ];
        }
        
        $productTotal = $t['product_price'] * $t['quantity'];
        $sellerSummaries[$sellerId]['product_sales'] += $productTotal;
        $sellerSummaries[$sellerId]['customer_tax'] += $t['customer_tax'];
        $sellerSummaries[$sellerId]['seller_commission'] += $t['seller_commission'];
        $sellerSummaries[$sellerId]['customer_paid'] += $productTotal + $t['customer_tax'];
        $sellerSummaries[$sellerId]['seller_received'] += $productTotal + $t['customer_tax'];
        $sellerSummaries[$sellerId]['m7_receives'] += $t['customer_tax'] + $t['seller_commission'];
        $sellerSummaries[$sellerId]['seller_keeps'] = $sellerSummaries[$sellerId]['seller_received'] - $sellerSummaries[$sellerId]['m7_receives'];
        $sellerSummaries[$sellerId]['orders']++;
    }
    
} elseif ($isSeller) {
    // Seller sees only their data
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_number,
            o.created_at,
            o.subtotal,
            o.tax,
            o.total,
            o.status,
            oi.product_name,
            oi.quantity,
            oi.product_price,
            ROUND(oi.product_price * oi.quantity * 0.05) as customer_tax,
            ROUND(oi.product_price * oi.quantity * 0.05) as seller_commission
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE oi.seller_id = ? AND o.status = 'delivered'
        $dateCondition
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$currentUser['id']]);
    $transactions = $stmt->fetchAll();
    
    // Calculate totals for seller
    $totalProductSales = 0;
    $totalTaxCollected = 0;
    $totalCommissionPaid = 0;
    $totalCustomerPaid = 0;
    $totalSellerReceived = 0;
    
    foreach ($transactions as $t) {
        $productTotal = $t['product_price'] * $t['quantity'];
        $totalProductSales += $productTotal;
        $totalTaxCollected += $t['customer_tax'];
        $totalCommissionPaid += $t['seller_commission'];
        $totalCustomerPaid += $productTotal + $t['customer_tax'];
        $totalSellerReceived += $productTotal + $t['customer_tax'];
    }
    
    $totalPaidToM7 = $totalTaxCollected + $totalCommissionPaid;
    $sellerKeeps = $totalSellerReceived - $totalPaidToM7;
}

// Get seller's store info for seller view
if ($isSeller) {
    $stmt = $pdo->prepare("SELECT * FROM seller_stores WHERE seller_id = ?");
    $stmt->execute([$currentUser['id']]);
    $store = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax & Commission Dashboard - M7 Marketplace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="M7shooping.css">
    <!-- Simple round favicon with M7 text -->
    <link rel="icon" type="image/x-icon" href="M7shooping.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .tax-container {
            max-width: 1400px;
            margin: 0 auto;
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
        
        .explanation-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .explanation-card::before {
            content: '💰';
            position: absolute;
            font-size: 150px;
            opacity: 0.1;
            bottom: -30px;
            right: -20px;
            transform: rotate(-15deg);
        }
        
        .example-box {
            background: rgba(255,255,255,0.15);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        
        .example-title {
            font-size: 20px;
            margin-bottom: 15px;
            color: white;
        }
        
        .example-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .example-item {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
        }
        
        .example-item .label {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .example-item .value {
            font-size: 24px;
            font-weight: bold;
        }
        
        .example-breakdown {
            margin-top: 20px;
            padding: 15px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
        }
        
        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .breakdown-row:last-child {
            border-bottom: none;
        }
        
        .filter-section {
            background: rgba(255,255,255,0.05);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-form select, .filter-form input {
            padding: 12px 20px;
            border-radius: 50px;
            border: 2px solid transparent;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 14px;
        }
        
        .filter-form select option {
            background: #333;
        }
        
        .filter-form button {
            padding: 12px 30px;
            background: #d96565;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
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
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .stat-card .label {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: 800;
        }
        
        .stat-card.product .value { color: #4CAF50; }
        .stat-card.tax .value { color: #FF9800; }
        .stat-card.commission .value { color: #d96565; }
        .stat-card.m7 .value { color: #2196F3; }
        .stat-card.seller-keeps .value { color: #4CAF50; }
        
        .seller-summary {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .seller-summary h2 {
            color: #d96565;
            margin-bottom: 20px;
        }
        
        .seller-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .seller-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
        }
        
        .seller-card h3 {
            color: #4CAF50;
            margin-bottom: 5px;
        }
        
        .seller-card .store-name {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 15px;
        }
        
        .seller-card .stat-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }
        
        .seller-card .stat-row .label {
            opacity: 0.8;
        }
        
        .seller-card .stat-row .amount {
            font-weight: 600;
        }
        
        .seller-card .tax-amount {
            color: #FF9800;
        }
        
        .seller-card .commission-amount {
            color: #d96565;
        }
        
        .seller-card .keeps-amount {
            color: #4CAF50;
            font-size: 16px;
        }
        
        .seller-card .m7-amount {
            color: #2196F3;
            font-size: 16px;
        }
        
        .table-container {
            overflow-x: auto;
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 15px;
            background: rgba(217,101,101,0.2);
            color: #d96565;
            font-weight: 600;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .amount-positive {
            color: #4CAF50;
            font-weight: 600;
        }
        
        .amount-orange {
            color: #FF9800;
            font-weight: 600;
        }
        
        .amount-red {
            color: #d96565;
            font-weight: 600;
        }
        
        .amount-blue {
            color: #2196F3;
            font-weight: 600;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-tax {
            background: rgba(255,152,0,0.2);
            color: #FF9800;
            border: 1px solid #FF9800;
        }
        
        .badge-commission {
            background: rgba(217,101,101,0.2);
            color: #d96565;
            border: 1px solid #d96565;
        }
        
        .badge-m7 {
            background: rgba(33,150,243,0.2);
            color: #2196F3;
            border: 1px solid #2196F3;
        }
        
        .export-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .export-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .filter-form {
                flex-direction: column;
            }
            .filter-form select, .filter-form input, .filter-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="tax-container">
        <div class="page-header">
            <h1>💰 Tax & Commission Dashboard</h1>
            <div>
                <button onclick="exportToCSV()" class="export-btn">📥 Export to CSV</button>
            </div>
        </div>
        
        <!-- Explanation Card with Example -->
        <div class="explanation-card">
            <h2 style="margin-bottom: 20px;">📊 How Your Money Flows</h2>
            
            <div class="example-box">
                <div class="example-title">Example: Product priced at 1000 DA</div>
                
                <div class="example-grid">
                    <div class="example-item">
                        <div class="label">Product Price</div>
                        <div class="value">1000 DA</div>
                    </div>
                    <div class="example-item">
                        <div class="label">Customer Tax (5%)</div>
                        <div class="value">+50 DA</div>
                    </div>
                </div>
                
                <div class="example-breakdown">
                    <div class="breakdown-row">
                        <span>💰 Customer Pays:</span>
                        <span style="color: #4CAF50; font-weight: bold;">1050 DA</span>
                    </div>
                    <div class="breakdown-row">
                        <span>📦 Seller Receives:</span>
                        <span style="color: #4CAF50; font-weight: bold;">1050 DA</span>
                    </div>
                    <div class="breakdown-row" style="border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 15px;">
                        <span>━━━━━━━━━━━━━━━━━━</span>
                    </div>
                    <div class="breakdown-row">
                        <span>🧾 Customer Tax (to M7):</span>
                        <span style="color: #FF9800; font-weight: bold;">-50 DA</span>
                    </div>
                    <div class="breakdown-row">
                        <span>📊 Seller Commission (5%):</span>
                        <span style="color: #d96565; font-weight: bold;">-50 DA</span>
                    </div>
                    <div class="breakdown-row" style="border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 15px;">
                        <span>━━━━━━━━━━━━━━━━━━</span>
                    </div>
                    <div class="breakdown-row" style="font-size: 18px;">
                        <span><strong>🏦 M7 Receives:</strong></span>
                        <span style="color: #2196F3; font-weight: bold;">100 DA</span>
                    </div>
                    <div class="breakdown-row" style="font-size: 18px;">
                        <span><strong>👤 Seller Keeps:</strong></span>
                        <span style="color: #4CAF50; font-weight: bold;">950 DA</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" class="filter-form">
                <select name="filter" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Time</option>
                    <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="week" <?php echo $filter == 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="month" <?php echo $filter == 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="custom" <?php echo $filter == 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>
                
                <?php if ($filter == 'custom'): ?>
                    <input type="date" name="start_date" value="<?php echo $startDate; ?>">
                    <input type="date" name="end_date" value="<?php echo $endDate; ?>">
                    <button type="submit">Apply</button>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($isAdmin): ?>
            <!-- Admin View: Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card product">
                    <div class="label">Total Product Value</div>
                    <div class="value"><?php echo number_format($totalProductValue); ?> DZD</div>
                </div>
                <div class="stat-card tax">
                    <div class="label">Total Tax Collected</div>
                    <div class="value"><?php echo number_format($totalTax); ?> DZD</div>
                </div>
                <div class="stat-card commission">
                    <div class="label">Total Commission</div>
                    <div class="value"><?php echo number_format($totalCommission); ?> DZD</div>
                </div>
                <div class="stat-card m7">
                    <div class="label">M7 Receives</div>
                    <div class="value"><?php echo number_format($totalM7Receives); ?> DZD</div>
                </div>
            </div>
            
            <!-- Seller Summaries -->
            <div class="seller-summary">
                <h2>📋 Seller Summaries</h2>
                <div class="seller-grid">
                    <?php foreach ($sellerSummaries as $seller): ?>
                    <div class="seller-card">
                        <h3><?php echo htmlspecialchars($seller['store']); ?></h3>
                        <div class="store-name"><?php echo htmlspecialchars($seller['name']); ?></div>
                        
                        <div class="stat-row">
                            <span class="label">Product Sales:</span>
                            <span class="amount"><?php echo number_format($seller['product_sales']); ?> DZD</span>
                        </div>
                        <div class="stat-row">
                            <span class="label">Customer Tax (5%):</span>
                            <span class="amount tax-amount">+<?php echo number_format($seller['customer_tax']); ?> DZD</span>
                        </div>
                        <div class="stat-row">
                            <span class="label">Seller Received:</span>
                            <span class="amount"><?php echo number_format($seller['seller_received']); ?> DZD</span>
                        </div>
                        <div class="stat-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <span class="label">Paid to M7 (Tax):</span>
                            <span class="amount tax-amount">-<?php echo number_format($seller['customer_tax']); ?> DZD</span>
                        </div>
                        <div class="stat-row">
                            <span class="label">Paid to M7 (Commission):</span>
                            <span class="amount commission-amount">-<?php echo number_format($seller['seller_commission']); ?> DZD</span>
                        </div>
                        <div class="stat-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                            <span class="label"><strong>M7 Receives:</strong></span>
                            <span class="amount m7-amount"><?php echo number_format($seller['m7_receives']); ?> DZD</span>
                        </div>
                        <div class="stat-row">
                            <span class="label"><strong>Seller Keeps:</strong></span>
                            <span class="amount keeps-amount"><?php echo number_format($seller['seller_keeps']); ?> DZD</span>
                        </div>
                        <div class="stat-row">
                            <span class="label">Orders:</span>
                            <span><?php echo $seller['orders']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
        <?php elseif ($isSeller): ?>
            <!-- Seller View -->
            <div class="stats-grid">
                <div class="stat-card product">
                    <div class="label">Your Product Sales</div>
                    <div class="value"><?php echo number_format($totalProductSales); ?> DZD</div>
                </div>
                <div class="stat-card tax">
                    <div class="label">Tax Collected</div>
                    <div class="value"><?php echo number_format($totalTaxCollected); ?> DZD</div>
                </div>
                <div class="stat-card commission">
                    <div class="label">Commission Paid</div>
                    <div class="value"><?php echo number_format($totalCommissionPaid); ?> DZD</div>
                </div>
                <div class="stat-card seller-keeps">
                    <div class="label">You Keep</div>
                    <div class="value"><?php echo number_format($sellerKeeps); ?> DZD</div>
                </div>
            </div>
            
            <div style="background: rgba(76,175,80,0.1); padding: 20px; border-radius: 15px; margin-bottom: 30px;">
                <h3 style="color: #4CAF50; margin-bottom: 15px;">📝 Your Summary</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div>
                        <p><strong>Customers Paid You:</strong> <?php echo number_format($totalSellerReceived); ?> DZD</p>
                        <p><strong>Your Product Sales:</strong> <?php echo number_format($totalProductSales); ?> DZD</p>
                        <p><strong>Tax Collected for M7:</strong> <?php echo number_format($totalTaxCollected); ?> DZD</p>
                    </div>
                    <div>
                        <p><strong>Commission to M7:</strong> <?php echo number_format($totalCommissionPaid); ?> DZD</p>
                        <p><strong>Total Paid to M7:</strong> <?php echo number_format($totalPaidToM7); ?> DZD</p>
                        <p><strong style="color: #4CAF50;">You Keep:</strong> <?php echo number_format($sellerKeeps); ?> DZD</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Transactions Table -->
        <div class="table-container">
            <h2 style="color: #d96565; margin-bottom: 20px;">📊 Transaction Details</h2>
            <table>
                <thead>
                    <tr>
                        <?php if ($isAdmin): ?>
                            <th>Seller</th>
                            <th>Store</th>
                        <?php endif; ?>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Product Price</th>
                        <th>Customer Tax (5%)</th>
                        <th>Customer Pays</th>
                        <th>Seller Commission (5%)</th>
                        <th>Seller Keeps</th>
                        <th>M7 Receives</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="<?php echo $isAdmin ? 12 : 10; ?>" style="text-align: center; padding: 50px;">
                                No transactions found for this period
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): 
                            $productTotal = $t['product_price'] * $t['quantity'];
                            $customerPays = $productTotal + $t['customer_tax'];
                            $sellerKeeps = $customerPays - ($t['customer_tax'] + $t['seller_commission']);
                            $m7Receives = $t['customer_tax'] + $t['seller_commission'];
                        ?>
                        <tr>
                            <?php if ($isAdmin): ?>
                                <td><?php echo htmlspecialchars($t['seller_name']); ?></td>
                                <td><?php echo htmlspecialchars($t['store_name'] ?? $t['seller_name']); ?></td>
                            <?php endif; ?>
                            <td><?php echo $t['order_number']; ?></td>
                            <td><?php echo date('d M Y', strtotime($t['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($t['product_name']); ?></td>
                            <td><?php echo $t['quantity']; ?></td>
                            <td class="amount-positive"><?php echo number_format($productTotal); ?> DZD</td>
                            <td>
                                <span class="badge badge-tax">+<?php echo number_format($t['customer_tax']); ?> DZD</span>
                            </td>
                            <td class="amount-positive"><?php echo number_format($customerPays); ?> DZD</td>
                            <td>
                                <span class="badge badge-commission">-<?php echo number_format($t['seller_commission']); ?> DZD</span>
                            </td>
                            <td class="amount-positive"><?php echo number_format($sellerKeeps); ?> DZD</td>
                            <td>
                                <span class="badge badge-m7"><?php echo number_format($m7Receives); ?> DZD</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer>
        <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms</a> | <a href="privacy.php">Privacy</a></p>
    </footer>
    
    <script src="script.js"></script>
    <script>
    function exportToCSV() {
        let rows = [];
        let headers = [];
        
        <?php if ($isAdmin): ?>
            headers = ['Seller', 'Store', 'Order #', 'Date', 'Product', 'Qty', 'Product Price', 'Customer Tax', 'Customer Pays', 'Seller Commission', 'Seller Keeps', 'M7 Receives'];
        <?php else: ?>
            headers = ['Order #', 'Date', 'Product', 'Qty', 'Product Price', 'Customer Tax', 'Customer Pays', 'Seller Commission', 'Seller Keeps', 'M7 Receives'];
        <?php endif; ?>
        
        rows.push(headers.join(','));
        
        <?php foreach ($transactions as $t): 
            $productTotal = $t['product_price'] * $t['quantity'];
            $customerPays = $productTotal + $t['customer_tax'];
            $sellerKeeps = $customerPays - ($t['customer_tax'] + $t['seller_commission']);
            $m7Receives = $t['customer_tax'] + $t['seller_commission'];
        ?>
        let row = [];
        <?php if ($isAdmin): ?>
            row.push("<?php echo addslashes($t['seller_name']); ?>");
            row.push("<?php echo addslashes($t['store_name'] ?? $t['seller_name']); ?>");
        <?php endif; ?>
        row.push("<?php echo $t['order_number']; ?>");
        row.push("<?php echo date('d M Y', strtotime($t['created_at'])); ?>");
        row.push("<?php echo addslashes($t['product_name']); ?>");
        row.push("<?php echo $t['quantity']; ?>");
        row.push("<?php echo $productTotal; ?>");
        row.push("<?php echo $t['customer_tax']; ?>");
        row.push("<?php echo $customerPays; ?>");
        row.push("<?php echo $t['seller_commission']; ?>");
        row.push("<?php echo $sellerKeeps; ?>");
        row.push("<?php echo $m7Receives; ?>");
        
        rows.push(row.join(','));
        <?php endforeach; ?>
        
        let csvContent = rows.join('\n');
        let blob = new Blob([csvContent], { type: 'text/csv' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = 'tax_report_<?php echo date('Y-m-d'); ?>.csv';
        a.click();
    }
    </script>
</body>
</html>