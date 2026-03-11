<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Tracker - M7 Admin</title>
    <link rel="stylesheet" href="M7shooping.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a);
            color: white;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            font-size: 42px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #fff 0%, #d96565 100%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            overflow: hidden;
        }
        
        th {
            background: rgba(217, 101, 101, 0.3);
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        tr:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .paid {
            color: #4CAF50;
            font-weight: 600;
        }
        
        .pending {
            color: #FF9800;
            font-weight: 600;
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 13px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .btn-success {
            background: #4CAF50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .btn-primary {
            background: #d96565;
            color: white;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            background: #b84343;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(217, 101, 101, 0.4);
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.1);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 800;
            color: #d96565;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #d96565;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php
require_once 'config.php';

// Simple admin check (you can implement proper admin role later)
// For now, just check if user is logged in and is the first user or has special email
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = getCurrentUser();
// Simple admin check - you can change this condition
$isAdmin = ($currentUser['email'] === 'admin@m7marketplace.com' || $currentUser['id'] == 1);

if (!$isAdmin) {
    header('Location: home.php');
    exit;
}

// Get all sellers
$stmt = $pdo->query("
    SELECT u.*, s.store_name 
    FROM users u
    LEFT JOIN seller_stores s ON u.id = s.seller_id
    WHERE u.role = 'seller'
    ORDER BY u.registration_date DESC
");
$sellers = $stmt->fetchAll();

// Calculate totals
$totalCommission = 0;
$totalPaid = 0;
$totalPending = 0;

// Get payment history
$payments = [];
foreach ($sellers as $seller) {
    // Get seller's products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE seller_id = ?");
    $stmt->execute([$seller['id']]);
    $products = $stmt->fetchAll();
    
    $revenue = 0;
    foreach ($products as $product) {
        $revenue += $product['sold'] * $product['price'];
    }
    
    $commission = round($revenue * 0.1);
    
    // Get payment status from session or database (you can create a payments table)
    $paidStatus = isset($_SESSION['paid_' . $seller['id']]) ? 'paid' : 'pending';
    
    $sellers[$seller['id']] = [
        'revenue' => $revenue,
        'commission' => $commission,
        'status' => $paidStatus
    ];
    
    $totalCommission += $commission;
    if ($paidStatus === 'paid') {
        $totalPaid += $commission;
    } else {
        $totalPending += $commission;
    }
}
?>

<div class="container">
    <a href="admin-panel.php" class="back-link">← Back to Admin Panel</a>
    
    <h1>💰 Payment Tracker - M7 Marketplace</h1>
    
    <div class="stats-summary">
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($totalCommission); ?> DZD</div>
            <div class="stat-label">Total Commission</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #4CAF50;"><?php echo number_format($totalPaid); ?> DZD</div>
            <div class="stat-label">Total Paid</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #FF9800;"><?php echo number_format($totalPending); ?> DZD</div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    
    <div id="payments-container">
        <table>
            <thead>
                <tr>
                    <th>Seller</th>
                    <th>Store</th>
                    <th>Revenue</th>
                    <th>Commission (10%)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sellers as $seller): 
                    $revenue = $seller['revenue'] ?? 0;
                    $commission = $seller['commission'] ?? 0;
                    $paidStatus = $seller['status'] ?? 'pending';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($seller['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($seller['store_name'] ?? 'N/A'); ?></td>
                    <td><?php echo number_format($revenue); ?> DZD</td>
                    <td><strong><?php echo number_format($commission); ?> DZD</strong></td>
                    <td class="<?php echo $paidStatus === 'paid' ? 'paid' : 'pending'; ?>">
                        <?php echo $paidStatus === 'paid' ? '✅ Paid' : '⏳ Pending'; ?>
                    </td>
                    <td>
                        <?php if ($paidStatus === 'pending'): ?>
                            <button onclick="markAsPaid(<?php echo $seller['id']; ?>)" class="btn-small btn-success">✓ Mark Paid</button>
                        <?php else: ?>
                            <span>✓ Paid</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <button onclick="generateReport()" class="btn-primary" style="margin-top: 20px;">📥 Download Report</button>
</div>

<footer>
    <p>© 2026 M7 Marketplace. All rights reserved. | <a href="about.php">About</a> | <a href="contact.php">Contact</a> | <a href="terms.php">Terms of Service</a> | <a href="privacy.php">Privacy Policy</a></p>
</footer>

<script>
function markAsPaid(sellerId) {
    if (confirm('Mark this seller as paid?')) {
        // Create a form to submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'mark-paid.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'seller_id';
        input.value = sellerId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function generateReport() {
    let report = "M7 MARKETPLACE - PAYMENT REPORT\n";
    report += "================================\n\n";
    report += `Date: ${new Date().toLocaleString()}\n\n`;
    
    let totalCommission = 0;
    
    <?php foreach ($sellers as $seller): 
        $commission = $seller['commission'] ?? 0;
        $paidStatus = $seller['status'] ?? 'pending';
    ?>
    report += `Seller: <?php echo addslashes($seller['full_name']); ?> (<?php echo addslashes($seller['store_name'] ?? 'No store'); ?>)\n`;
    report += `Revenue: <?php echo number_format($seller['revenue'] ?? 0); ?> DZD\n`;
    report += `Commission: <?php echo number_format($commission); ?> DZD\n`;
    report += `Status: <?php echo $paidStatus; ?>\n`;
    report += `Reference: M7-<?php echo $seller['id']; ?>\n`;
    report += "─".repeat(40) + "\n\n";
    <?php 
    $totalCommission += $commission;
    endforeach; 
    ?>
    
    report += `TOTAL COMMISSION: <?php echo number_format($totalCommission); ?> DZD\n`;
    
    // Create and download the report
    const blob = new Blob([report], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'payment_report_' + Date.now() + '.txt';
    a.click();
}
</script>

<script src="script.js"></script>
</body>
</html>